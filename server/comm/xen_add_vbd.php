<?php

	$sb_status = sb_status_fetch();

	$status = 0;
	$reason = 'None';
	if ( $sb_status['status'] == 'xen_migrate'  && $sb_status['stage'] == 'xen_add_vbd' ) {


		if ( $sb_status['step'] == 0 ) {
			$vmuuid = $settings['xen_migrate_uuid'];
		} else {
			$vmuuid = $sb_status['setting2'];
		}

		//get xen migrate disk profiles
		$diskarray = sb_vm_disk_array_fetch( $diskfile );

		//check how many disks attached to backup vm
		exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vm-disk-list vm=' . $vmuuid, $buvmdisks );

		if ( count( $buvmdisks ) / 13 == count( $diskarray ) + 1 || $vmuuid != $settings['xen_migrate_uuid'] && count( $buvmdisks ) / 13 == count( $diskarray ) ) {//13 lines per disk in output

			$status = 3;
			$reason = 'Disks Attached';

			if ( $sb_status['step'] == 0 ) {
				sb_status_set( 'xen_migrate', 'xen_start', 0 );
			} else {
				sb_status_set( 'xen_migrate', 'xen_start', 2 );
			}

		} else {

			//attach disks
			$diskid     = 50;
//			$diskletter = 'a'; not required
			$bootablex  = 1;
			$disktypeget    = sb_check_disks();

			if ( count( $buvmdisks ) < 14 ) {//13 lines per disk in output
				foreach ( $diskarray as $item ) {
					//ask for attach disk
					if ( $vmuuid != $settings['xen_migrate_uuid'] ) {

						//if re-attaching to original VM
						$bootstring = ( $item['vbd-userdevice'] == 0 ) ? 'bootable=true mode=RW type=Disk' : 'bootable=false mode=RW type=Disk';

						exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vbd-create vm-uuid=' . $vmuuid . ' device=' . $item['vbd-userdevice'] . ' ' . $bootstring . ' vdi-uuid=' . $item['vdi-uuid'], $output );

					} else {
						exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vbd-create vm-uuid=' . $vmuuid . ' device=' . $diskid . ' bootable=false mode=RW type=Disk vdi-uuid=' . $item['vdi-uuid'], $output );

					}

					$diskid ++;

				}
			}
			$status = 1;
			$reason = 'Adding';

		}
	}

	sleep( 2 );

	$jsonarray = array(
		"status" => $status,
		"reason" => $reason,
	);

	sb_log('Xen - Add VBD - ' . $reason);


	echo json_encode( $jsonarray );