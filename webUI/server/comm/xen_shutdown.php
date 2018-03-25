<?php

	$sb_status = sb_status_fetch();

	$xenuuid = varcheck( "xenuuid", '' );

	$status = 0;
	$reason = 'None';

	if ( $sb_status['status'] == 'ready' || $sb_status['status'] == 'xen_migrate' && $sb_status['stage'] == 'xen_shutdown' ) {



		if ( $sb_status['step'] == '0' ) {

			sb_log('------------------------------------------------------');
			sb_log('Starting Xen Migrate ... ' . $xenuuid);

			sb_status_set( 'xen_migrate', 'xen_shutdown', 1,
				'',
				$xenuuid,
				'-XEN-',
				'',
				0,
				''
			);
			$sb_status['setting2'] = $xenuuid;
		}

		if ( $sb_status['step'] == 2  || $sb_status['step'] == 4 ) {
			$xenuuid = $settings['xen_migrate_uuid'];
		} else {
			$xenuuid = $sb_status['setting2'];
		}

		if ( preg_match( $UUIDxen, $xenuuid ) ) {

			exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vm-list is-control-domain=false uuid=' . $xenuuid, $output2 );
			if ( ! empty( $output2['2'] ) ) {
				$status = str_replace( 'power-state ( RO): ', '', $output2['2'] );
				$status = trim( $status );
			} else {
				$status = 'unknown';
			}

			if ( $status == 'halted' ) {
				$status = 3;
				$reason = 'Halted';
				if ( $sb_status['step'] == 0 ) {
					if ( $xenuuid != $settings['uuid_backup_engine'] && $xenuuid != $settings['xen_migrate_uuid'] ) {
						//create xen disks file
						$output = sb_vm_disk_array_create( $diskfile, 1, $xenuuid );
					}
					sb_status_set( 'xen_migrate', 'xen_remove_vbd', 0 );
				} else if ( $sb_status['step'] == 2 ) {
					sb_status_set( 'xen_migrate', 'xen_add_vbd', 0 );
				} else {
					sb_status_set( 'xen_migrate', 'xen_remove_vbd', 3 );
				}
			} else {

				if ( $sb_status['step'] == 0 || $sb_status['step'] == 2 || $sb_status['step'] == 4 ) {

					if ( $xenuuid != $settings['uuid_backup_engine'] && $xenuuid != $settings['xen_migrate_uuid'] ) {
						//create xen disks file
						$output = sb_vm_disk_array_create( $diskfile, 1, $xenuuid );
					}

					//ask for shutdown
					exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vm-shutdown uuid=' . $xenuuid, $output );
					$status = 1;
					$reason = 'Shutting Down';

					if ( $xenuuid != $settings['uuid_backup_engine'] ) {
						exec( 'rm ' . $settings['mount_migrate'] . '/xen_progress.dat -f', $statusoffile );
					}
					if ( $sb_status['step'] == 0 ) {
						sb_status_set( 'xen_migrate', 'xen_shutdown', 1 );
						//remove images to make room for new images
						exec( 'rm ' . $settings['mount_migrate'] . '/xen*.img -f', $statusoffile );
						exec( 'rm ' . $settings['mount_migrate'] . '/Disk*.dat -f', $statusoffile );
					}  else if ( $sb_status['step'] == 2 ) {
						sb_status_set( 'xen_migrate', 'xen_add_vbd', 0 );
					} else {
						sb_status_set( 'xen_migrate', 'xen_remove_vbd', 3 );
					}

				} else {
					//check if status is shutdown
					exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vm-list is-control-domain=false uuid=' . $xenuuid, $output );
					$statusis = str_replace( 'power-state ( RO): ', '', $output['2'] );
					$statusis = trim( $statusis );
					if ( $status == 'halted' ) {
						$status = 3;
						$reason = 'Halted';
						if ( $sb_status['step'] == 1 ) {
							sb_status_set( 'xen_migrate', 'xen_add_vbd', 0 );
						}  else if ( $sb_status['step'] == 2 ) {
							sb_status_set( 'xen_migrate', 'xen_add_vbd', 0 );
						} else {
							sb_status_set( 'xen_migrate', 'xen_remove_vbd', 3 );
						}
					} else {
						$status = 2;
						$reason = 'Waiting';

					}

				}
			}
		} else {
			$status = 0;
			$reason = 'Invalid UUID';

		}
	} else {
		$status = 3;
		$reason = 'Halted';
	}
	sleep( 1 );

	$jsonarray = array(
		"status" => $status,
		"reason" => $reason,
	);

	sb_log('Xen - Shutdown - ' . $reason);

	echo json_encode( $jsonarray );