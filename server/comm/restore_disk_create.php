<?php

	$sb_status = sb_status_fetch();

	exec( 'partprobe' );

	$setxen   = varcheck( "setxen", 0, "FILTER_VALIDATE_INT", 0 );
	$status   = 0;
	$reason   = 'None';
	$diskuuid = '';
	sleep( 2 );

	if ( $sb_status['status'] == 'xen_migrate' && $sb_status['stage'] == 'xen_start' && $sb_status['step'] == 2 ) {
		//not starting xen VM
		sb_status_set( 'ready', '', 0 );
		sleep( 1 );
		$sb_status = sb_status_fetch();
		$setxen    = 1;
		sb_log('Restoring Xen Images - Skipping Xen Startup');
	} else if ( $sb_status['status'] == 'xen_restore' && $sb_status['stage'] == 'start' ) {
		//after starting XEN Original VM
		sb_status_set( 'ready', '', 0 );
		sleep( 1 );
		$sb_status = sb_status_fetch();
		$setxen    = 1;
		sb_log('Restoring Xen Images');

	}

	if ( $setxen == 1 ) {
		sb_log('Restoring Xen Images - Migrating Disk Data');
		//MIGRATE DISK DATA
		$diskletter = 'a';
		$disknumber = 1;
		$diskarray  = sb_vm_disk_array_fetch( $diskfile );
		foreach ( $diskarray as $item ) {

			$diskletter = sb_next_drive_letter( $diskletter );

			$bootablex = ( $item['vbd-userdevice'] == 0 ) ? 'true' : 'false';
			sb_disk_file_write( $disknumber, $item['vdi-label'], $item['vmuuid'], $item['vdi-uuid'], $bootablex, $settings['drive_interface'], $item['vdi-virtual-size'], $settings['drive_type'] . $diskletter, $item['vbd-label'], '-XEN-' );
			sb_log('Disk ' . $disknumber . ' - ' . $settings['drive_type'] . $diskletter . ' Bootable: ' . $bootablex . ' Size: ' . $item['vdi-virtual-size'] . ' Label: ' . $item['vdi-label'] . '(;(' . $item['vbd-label'] . ')');

			$disknumber ++;
		}
		$sb_status['step'] = 44;
	}

	//check if this is a new snapshot
	if ( $sb_status['status'] == 'ready' ) {



		$newvmname      = varcheck( "newvmname", '' );
		$vmuuid         = varcheck( "vmuuid", '' );
		$vmname         = varcheck( "vmname", '' );
		$buname         = varcheck( "buname", '' );
		$fixestext      = varcheck( "fixestext", '' );
		$newvmnamecheck = preg_replace( '/[0-9a-zA-Z\-_]/i', '', $newvmname );

		$os          = varcheck( "os", '' );
		$nic1        = varcheck( "nic1", '' );
		$vmtype      = varcheck( "vmtype", '' );
		$cluster     = varcheck( "cluster", '' );
		$domain     = varcheck( "domain", '' );
		$console     = varcheck( "console", '' );
		$memory      = varcheck( "memory", 1, "FILTER_VALIDATE_INT", 1 );
		$memory_max  = varcheck( "memory_max", 1, "FILTER_VALIDATE_INT", 1 );
		$cpu_sockets = varcheck( "cpu_sockets", 1, "FILTER_VALIDATE_INT", 1 );
		$cpu_cores   = varcheck( "cpu_cores", 1, "FILTER_VALIDATE_INT", 1 );
		$cpu_threads = varcheck( "cpu_threads", 1, "FILTER_VALIDATE_INT", 1 );
		$memory      = $memory * 1024 * 1024 * 1024;
		$memory_max  = $memory_max * 1024 * 1024 * 1024;

		if ( $setxen == 1 ) {
			$vmname = '-XEN-';
			$buname = '-XEN-';
		}

		sb_log('------------------------------------------------------');
		sb_log('Starting Restore ... ' . $vmname . ' - ' . $buname . ' --> ' . $newvmname);


		if ( ! empty( $newvmnamecheck ) ) {

			$status = 0;
			$reason = 'Invalid Disk Name';


		} else if ( $sb_status['step'] == 44 ) {

			$disks  = ovirt_rest_api_call( 'GET', 'disks' );
			$diskok = 1;
			foreach ( $disks as $disk ) {
				if ( (string) $disk->name == $newvmname . '_RDISK_Disk1' ) {
					$diskok = 0;
				}
			}
			if ( $diskok == 1 ) {
				foreach ( $diskarray as $item ) {

					$diskbytes = $item['vdi-virtual-size'];
					$xml       = '<disk_attachment>
		            <bootable>false</bootable>
		            <interface>' . $settings['drive_interface'] . '</interface>
		            <active>true</active>
		            <disk>
		                <description></description>
		                <format>raw</format>
		                <sparse>false</sparse>
		                <name>' . $newvmname . '_RDISK_' . $item['vbd-userdevice'] . '</name>
		                <provisioned_size>' . $diskbytes . '</provisioned_size>
		                <storage_domains>
		                    <storage_domain>
		                        <name>' . $domain . '</name>
		                    </storage_domain>
		                </storage_domains>
		            </disk>
		        </disk_attachment>';

					sb_log('Adding Disk - ' . $xml);

					$snap = ovirt_rest_api_call( 'POST', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/', $xml );
					sb_cache_set( $settings['uuid_backup_engine'], '', 'Restore - Creating ' . $item['vbd-userdevice'] . '', $newvmname, 'write' );
					//SLOW DOWN TO MAKE SURE VM MOUNTS DISKS IN ORDER!
					sleep( 10 );
				}

				sb_status_set( 'restore', 'disk_create', 1,
					$vmname,
					$vmuuid,
					$buname,
					$newvmname,
					0,
					$fixestext,
					'',
					$domain,
					'',
					$os,
					$nic1,
					$vmtype,
					$cluster,
					$console,
					$memory,
					$memory_max,
					$cpu_sockets,
					$cpu_cores,
					$cpu_threads
				);

				$status = 1;
				$reason = 'Disks Created';

			} else {
				$status = 0;
				$reason = 'Disks With That Name Already Exist';
			}

		} else if ( $sb_status['step'] == 0 ) {

			$filepath = $settings['mount_backups'] . '/' . $vmname . '/' . $vmuuid . '/' . $buname . '/';

			if ( file_exists( $filepath ) ) {

				$diskdata = sb_disk_array_fetch( $filepath );
				foreach ( $diskdata as $diskdatum ) {

					$xml = '<disk_attachment>
			            <bootable>false</bootable>
			            <interface>' . $settings['drive_interface'] . '</interface>
			            <active>true</active>
			            <disk>
			                <description></description>
			                <format>raw</format>
			                <sparse>false</sparse>
			                <name>' . $newvmname . '_RDISK_' . $diskdatum['disknumber'] . '</name>
			                <provisioned_size>' . $diskdatum['size'] . '</provisioned_size>
			                <storage_domains>
			                    <storage_domain>
			                        <name>' . $domain . '</name>
			                    </storage_domain>
			                </storage_domains>
			            </disk>
		        	</disk_attachment>';

					sb_log('Adding Disk - ' . $xml);

					//SLOW DOWN TO MAKE SURE VM MOUNTS DISKS IN ORDER!
					sleep( 10 );

					$snap = ovirt_rest_api_call( 'POST', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/', $xml );

					sb_cache_set( $settings['uuid_backup_engine'], '', 'Restore - Creating Disk - ' . $diskdatum['disknumber'], $newvmname, 'write' );
				}

				sb_status_set( 'restore', 'disk_create', 1,
					$vmname,
					$vmuuid,
					$buname,
					$newvmname,
					0,
					$fixestext,
					'',
					$domain,
					'',
					$os,
					$nic1,
					$vmtype,
					$cluster,
					$console,
					$memory,
					$memory_max,
					$cpu_sockets,
					$cpu_cores,
					$cpu_threads
				);

				$status = 1;
				$reason = 'Disk Created';

			} else {
				$status = 0;
				$reason = 'Backup Path Does Not Exist ' . $filepath;
			}

		}

		if ( empty( $status ) ) {
			sb_cache_set( $vmuuid, $buname, 'Restore - Creating Disk Failure - ' . $reason, $newvmname, 'write' );
		}

	} else if ( $sb_status['status'] == 'restore' && $sb_status['stage'] == 'disk_create' && $sb_status['step'] == '1' ) {

		$allok = 1;
		//check if disks done
		$morediskdata = ovirt_rest_api_call( 'GET', 'vms/' . $sb_status['setting2'] . '/diskattachments/' );
		foreach ( $morediskdata as $morediskdatum ) {
			$deepdiskdata = ovirt_rest_api_call( 'GET', 'disks/' . $morediskdatum['id'] );
			sleep( 1 );
			if ( $deepdiskdata->status != 'ok' ) {
				$allok = 0;
			}

		}

		if ( $allok == 1 ) {
			$status = 3;
			$reason = 'Disk Done';
			sb_status_set( 'restore', 'restore_imaging', 0 );
			sleep( 10 );

		} else {
			$status = 2;
			$reason = 'Disk Waiting';
		}

	}

	$jsonarray = array(
		"status" => $status,
		"reason" => $reason,
	);
	sb_log('Creating Disk(s) - ' . $reason);
	echo json_encode( $jsonarray );

