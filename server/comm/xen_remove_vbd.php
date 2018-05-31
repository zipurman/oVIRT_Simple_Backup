<?php

	$sb_status = sb_status_fetch();

	$status = 0;
	$reason = 'None';

	if ( $sb_status['status'] == 'xen_migrate' && $sb_status['stage'] == 'xen_remove_vbd' ) {

		if ( $sb_status['step'] == '3' ) {

			//remove disks from MIGRATE VM
			$vmuuid = $settings['xen_migrate_uuid'];

			//get disks from  vm
			$output    = sb_vm_disk_array_create( $diskfile2, 1, $vmuuid );
			$diskarray = sb_vm_disk_array_fetch( $diskfile2 );

			foreach ( $diskarray as $item ) {

				//do not remove main disk if migrate appliance
				if (
					$item['vbd-userdevice'] > 0
				) //ask for detatch disk
				{
					exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vbd-destroy uuid=' . $item['vbd-uuid'], $output );
				}
			}

			sb_status_set( 'xen_migrate', 'xen_add_vbd', 3 );

			$status = 3;
			$reason = 'Removed.';

		} else {
			//remove disks from ORIGINAL VM
			$diskarray = sb_vm_disk_array_fetch( $diskfile );

			//cleanup
			if ( $sb_status['step'] == 0 ) {
				//				exec( 'rm ' . $settings['mount_migrate'] . '/xen_*.dat -f', $statusoffile );
			}
			$vmuuid = $diskarray[0]['vmuuid'];

			//get disks from  vm
			$output = sb_vm_disk_array_create( $diskfile, 0, $vmuuid );

			if ( count( $output ) / 13 >= 1 || $settings['xen_migrate_uuid'] == $vmuuid ) {
				if ( $sb_status['step'] == 0 ) {
					foreach ( $diskarray as $item ) {
						//ask for detatch disk
						exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vbd-destroy uuid=' . $item['vbd-uuid'], $output );
						sleep( 5 );
					}

					$status = 1;
					$reason = 'Removing';

					sb_status_set( 'xen_migrate', 'xen_remove_vbd', 1 );

				} else {

					if ( empty( $output ) ) {
						$status = 3;
						$reason = 'Removed.';
						sb_status_set( 'xen_migrate', 'xen_shutdown', 2 );
					} else {
						$status = 2;
						$reason = 'Waiting';

					}

				}

			} else {

				$status = 3;
				$reason = 'Removed';
				sb_status_set( 'xen_migrate', 'xen_shutdown', 2 );

			}

		}
	}
	sleep( 1 );

	$jsonarray = array(
		"status" => $status,
		"reason" => $reason,
	);

	sb_log( 'Xen - Remove VBD - ' . $reason );

	echo json_encode( $jsonarray );