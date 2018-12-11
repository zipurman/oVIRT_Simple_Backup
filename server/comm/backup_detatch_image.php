<?php

	$sb_status = sb_status_fetch();

	$status = 0;
	$reason = 'None';

	if ( $sb_status['status'] == 'backup' && $sb_status['stage'] == 'backup_detatch_image' ) {

		if ( preg_match( $UUIDv4, $sb_status['setting1'] ) ) {


			if ( empty( $sb_status['setting3'] ) ) {

				$status = 0;
				$reason = 'Snapshot Not Found';

			} else if ( ! empty( $sb_status['setting4'] ) ) {


				$disks        = ovirt_rest_api_call( 'GET', 'vms/' . $sb_status['setting1'] . '/snapshots/' . $sb_status['setting3'] . '/disks' );
				$morediskdata = ovirt_rest_api_call( 'GET', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/' );
				$disktypeget  = sb_check_disks();
				$disktype     = $disktypeget['disktype'];
				$disknumber   = 1;
				foreach ( $disks->disk as $disk ) {

					$morediskdatathis = array();
					foreach ( $morediskdata as $morediskdatum ) {
						if ( (string) $morediskdatum['id'] == (string) $disk['id'] ) {
							$morediskdatathis = $morediskdatum;
						}
					}
					if ( ! empty( $morediskdatathis ) ) {
						if ( (string) $morediskdatathis->bootable == 'false' ) {
							$snap = ovirt_rest_api_call( 'DELETE', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/' . $disk['id'] );
							sb_cache_set( $sb_status['setting1'], $sb_status['setting2'], 'Detatching Image', $sb_status['setting4'], 'write' );
						}
					}
				}

				sb_status_set( 'backup', 'snapshot_delete', 1 );

				$status = 1;
				$reason = 'Disk(s) Detatched';

				//TEST backup
                exec( 'ls ' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . 'Disk*.img*', $files );

                foreach ( $files as $file ) {
                    if ( empty( sb_test_image_file( $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $file , 1) ) ) {
                        sb_log('!! Error !! Image File Corrupted - ' . $file . ' !! Error !!');
                    } else {
                        sb_log('Image File Valid - ' . $file);
                    }
                }



                sleep( 2 );

			} else {
				$status = 0;
				$reason = 'Unmatched UUID';
			}

		} else {
			$status = 0;
			$reason = 'Invalid UUID';

		}

		if ( empty( $status ) ) {
			sb_cache_set( $sb_status['setting1'], $sb_status['setting2'], 'Detatch Image Failure - ' . $reason, $sb_status['setting4'], 'write' );
		}
	}

	$jsonarray = array(
		"status"     => $status,
		"reason"     => $reason,
		"snapshotid" => $sb_status['setting3'],
	);

	sb_log('Detatching Image - ' . $reason);


	echo json_encode( $jsonarray );