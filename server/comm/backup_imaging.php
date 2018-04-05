<?php

	$sb_status = sb_status_fetch();

	$status   = 0;
	$progress = 0;
	$numberofimages = 0;
	$dev      = '';
	$reason   = 'Disk Not Attached';

	if ( $sb_status['status'] == 'backup' && $sb_status['stage'] == 'backup_imaging' ) {

//		exec( 'partprobe' );

		$disktypeget    = sb_check_disks();
		$numberofimages = count( $disktypeget['avaliabledisks'] );

		if ( preg_match( $UUIDv4, $sb_status['setting1'] ) ) {

			if ( empty( $sb_status['setting3'] ) ) {

				$status = 0;
				$reason = 'Snapshot Not Found';

			} else if ( ! empty( $sb_status['setting4'] ) ) {

				$disks = ovirt_rest_api_call( 'GET', 'vms/' . $sb_status['setting1'] . '/snapshots/' . $sb_status['setting3'] . '/disks' );

				$diskid       = $disks->disk['id'];
				$extradiskdev = '';//needed any more?
				$diskletter   = 'b';

				$checkdiskattached = ovirt_rest_api_call( 'GET', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/' );

				foreach ( $checkdiskattached as $attacheddisk ) {
					if ( (string) $attacheddisk['id'] == (string) $diskid ) {

						$status = 1;

						$progressfilename = $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/progress.dat';

						if ( $sb_status['step'] > 1 ) {

							$status = 2;

							if ( $sb_status['step'] == 2 ) {

								$filedata = null;

								$reason = 'Imaging in progress.';

								if ( file_exists( $progressfilename ) ) {

									exec( 'tail ' . $progressfilename . ' -n 1', $filedata );

									$progress = (int) $filedata[0];

									sb_cache_set( $sb_status['setting1'], $sb_status['setting2'], 'Imaging ' . $progress . '%', $sb_status['setting4'], 'write', '?area=2&action=select&backupnow=1&vm=' . $sb_status['setting1'] . '&recovery=1', 'sb_snapshot_imaging(\'' . $sb_status['setting1'] . '\', \'' . $sb_status['setting2'] . '\');' );
									sleep( 1 );

									if ($progress >= 100){
										sb_status_set( 'backup', 'backup_imaging', 1 );
									}
								} else {
									$reason .= ' - MISSING - ' .$progressfilename;
								}
							}

						} else {

							$disknumber     = 0;

							//setting5 = disknumber
							if ( empty( $sb_status['setting5'] ) ) {
								$disknumber = 1;
							} else {
								$disknumber = (integer) $sb_status['setting5'] + 1;
							}

							if ( $disknumber > $numberofimages ) {

								$status = 3;
								$reason = 'Imaging Disk(s) Completed';
								sb_status_set( 'backup', 'backup_detatch_image', 1 );

							} else {

								exec( 'echo "0" > ' . $progressfilename );
								$reason = 'Imaging Disk ' . $progressfilename;

								if ($sb_status['setting3'] == '-XEN-') {
									$processdisks = sb_disk_array_fetch( $settings['mount_migrate'] );
								} else {
									$processdisks = sb_disk_array_fetch( $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] );
								}

								foreach ( $disktypeget['avaliabledisks'] as $avaliabledisk ) {
									foreach ( $processdisks as $processdisk ) {
										if ( empty($dev) && $processdisk['disknumber'] == 'Disk' . $disknumber){
											$disknumberfile = $processdisk['disknumber'];
											$dev = $avaliabledisk;
										}
									}
								}

								if ( ! empty( $dev ) ) {

									$command = '(pv -n /dev/' . $dev . ' | dd of="' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $disknumberfile . '.img" bs=1M conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background
									$output = null;
									exec( $command, $output );

									sb_log('Backup - Imaging - /dev/' . $dev);

									sb_cache_set( $sb_status['setting1'], $sb_status['setting2'], 'Imaging', $sb_status['setting4'], 'write' );
									sb_status_set( 'backup', 'backup_imaging', 2, '', '', '', '', $disknumber );
									$sb_status['setting5'] = $disknumber;

								}
							}
						}

					}
				}

			} else {
				$status = 0;
				$reason = 'Unmatched UUID';
			}

		} else {
			$status = 0;
			$reason = 'Invalid UUID';

		}

		if ( empty( $status ) ) {
			sb_cache_set( $sb_status['setting1'], $snapshotname, 'Imaging Failure - ' . $reason, $sb_status['setting4'], 'write' );
		}
	}

	$jsonarray = array(
		"status"   => $status,
		"reason"   => $reason,
		"progress" => $progress,
		"numberofdisks"   => $numberofimages,
		"thisdisk"   => $sb_status['setting5'],
	);

	sb_log('Backup Imaging - ' . $sb_status['setting5'] . '/' .$numberofimages. ' - ' . $progress . '% ' . $reason);

	echo json_encode( $jsonarray );

	unset( $progress );
