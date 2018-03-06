<?php

	$diskname      = varcheck( "diskname", '' );
	$disksize      = varcheck( "disksize", 0, "FILTER_VALIDATE_INT", 0 );
	$progress      = varcheck( "progress", 0, "FILTER_VALIDATE_INT", 0 );
	$disknamecheck = preg_replace( '/[0-9a-zA-Z\-_]/i', '', $diskname );

	$status   = 0;
	$reason   = 'None';
	$diskuuid = '';
	sleep( 2 );

	if ( ! empty( $disknamecheck ) ) {

		$status = 0;
		$reason = 'Invalid Disk Name';

	} else if ( empty( $disksize ) ) {

		$status = 0;
		$reason = 'Invalid Disk Size';

	} else if ( $progress <= 1 ) {

		$disks  = ovirt_rest_api_call( 'GET', 'disks' );
		$diskok = 1;
		foreach ( $disks as $disk ) {
			if ( (string) $disk->name == $diskname . '_RDISK' ) {
				$diskok = 0;
			}
		}

		if ( $diskok == 1 ) {

			$diskbytes = $disksize * 1024 * 1024 * 1024;
			$xml       = '<disk_attachment>
		            <bootable>false</bootable>
		            <interface>' . $settings['drive_interface'] . '</interface>
		            <active>true</active>
		            <disk>
		                <description></description>
		                <format>raw</format>
		                <sparse>false</sparse>
		                <name>' . $diskname . '_RDISK</name>
		                <provisioned_size>' . $diskbytes . '</provisioned_size>
		                <storage_domains>
		                    <storage_domain>
		                        <name>' . $settings['storage_domain'] . '</name>
		                    </storage_domain>
		                </storage_domains>
		            </disk>
		        </disk_attachment>';

			$snap = ovirt_rest_api_call( 'POST', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/', $xml );

			sb_cache_set( $settings['uuid_backup_engine'], '', 'Restore - Creating ' . $disksize . ' GB Disk', $diskname, 'write' );

			$status = 1;
			$reason = 'Disk Created';
		} else {
			$status = 0;
			$reason = 'Disk With That Name Already Exists';
		}
	} else {


		$diskok = 0;
		$disks  = ovirt_rest_api_call( 'GET', 'disks' );
		foreach ( $disks as $disk ) {
			if ( (string) $disk->name == $diskname . '_RDISK' ) {
				if ( (string) $disk->status == 'ok' ) {
					$diskok   = 1;
					$diskuuid = (string) $disk['id'];
				}
			}
		}

		if ( $diskok == 0 ) {
			$status = 2;
			$reason = 'Disk Waiting';
		} else {
			$status = 3;
			$reason = 'Disk Done';
		}

	}

	if ( empty( $status ) ) {
		sb_cache_set( $vmuuid, $snapshotname, 'Restore - Creating Disk Failure - ' . $reason, $diskname, 'write' );
	}

	$jsonarray = array(
		"status"   => $status,
		"reason"   => $reason,
		"diskuuid" => $diskuuid,
	);

	echo json_encode( $jsonarray );

