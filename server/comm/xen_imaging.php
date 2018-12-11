<?php

	$sb_status = sb_status_fetch();

	$status       = 0;
	$progress     = 0;
	$reason       = 'Disk Not Attached';
	$vmuuid       = 'XEN';
	$snapshotname = 'XEN';

	//get xen migrate disk profiles
	$diskarray      = sb_vm_disk_array_fetch( $diskfile );
	$numberofimages = count( $diskarray );
	$disknumber     = 0;

	if ( $sb_status['status'] == 'xen_migrate' && $sb_status['stage'] == 'xen_imaging' ) {


		$chkdisk        = sb_xen_check_disks();
		$disktoimage    = $chkdisk['lastdev'];
		$avaliabledisks = $chkdisk['avaliabledisks'];

		if ( ! empty( $disktoimage ) ) {

			$status = 1;
			$reason = 'Disk Attached';

			$progressfilename = $settings['mount_migrate'] . '/xen_progress.dat';

			if ( $sb_status['step'] == 2 ) {
				$status = 2;

				$lastvalue = empty( $lastvalue ) ? 0 : (integer) $lastvalue[0];

				$reason = 'Imaging in progress.';

				exec( 'ssh root@' . $settings['xen_migrate_ip'] . $extrasshsettings . ' ls ' . $settings['mount_migrate'] . '/xen_progress.dat', $progressoffile );


				if ( ! empty( $progressoffile ) ) {

					exec( 'ssh root@' . $settings['xen_migrate_ip'] . $extrasshsettings . ' cat ' . $settings['mount_migrate'] . '/xen_progress.dat  | tail -n-1', $lastvalue2 );

					$lastvalue2 = empty( $lastvalue2[0] ) ? 0 : (integer) $lastvalue2[0];

					$progress = (int) $lastvalue2;

					if ( $progress >= 100 && $numberofimages > $disknumber ) {
						$progress = 0;
						sb_status_set( 'xen_migrate', 'xen_imaging', 1 );
					}
					sb_cache_set( $vmuuid, $snapshotname, 'Imaging ' . $progress . '%', 'Xen VM', 'write' );

				}

				sleep( 2 );

			} else {

				$disknumber = 0;

				//setting5 = disknumber
				if ( empty( $sb_status['setting5'] ) ) {
					$disknumber = 1;
				} else {
					$disknumber = (integer) $sb_status['setting5'] + 1;
				}

				if ( $disknumber > $numberofimages ) {

					$status = 3;
					$reason = 'Imaging Disk(s) Completed';
					sb_status_set( 'xen_migrate', 'xen_shutdown', 4 );

				} else {

					if ( $disknumber == 1 ) {
						//remove previous images
						exec( 'ssh root@' . $settings['xen_migrate_ip'] . $extrasshsettings . ' rm ' . $settings['mount_migrate'] . '/xen*.img -f', $statusoffile );
						exec( 'ssh root@' . $settings['xen_migrate_ip'] . $extrasshsettings . ' rm ' . $settings['mount_migrate'] . '/Disk*.dat -f', $statusoffile );
					}

					$reason = 'Start Imaging';

					$dev = $avaliabledisks[ $disknumber ];

//					exec( 'partprobe' );

					if ( empty( $settings['compress'] ) ) {

						$command = 'ssh root@' . $settings['xen_migrate_ip'] . $extrasshsettings . ' ' . '\'' . '(pv -n /dev/' . $dev . ' | dd of="' . $settings['mount_migrate'] . '/xen' . $disknumber . '.img" bs=1M conv=notrunc,noerror status=none) > ' . $progressfilename . ' 2>&1 &' . '\'';//trailing & sends to background

					} else if ( $settings['compress'] == '1' ) {

						$command = 'ssh root@' . $settings['xen_migrate_ip'] . $extrasshsettings . ' ' . '\'' . '(pv -n /dev/' . $dev . '  | gzip -c | dd of="' . $settings['mount_migrate'] . '/xen' . $disknumber . '.img.gz" bs=1M conv=notrunc,noerror status=none) > ' . $progressfilename . ' 2>&1 &' . '\'';//trailing & sends to background

					} else if ( $settings['compress'] == '2' ) {

						$command = 'ssh root@' . $settings['xen_migrate_ip'] . $extrasshsettings . ' ' . '\'' . '(pv -n /dev/' . $dev . '  | lzop --fast -c | dd of="' . $settings['mount_migrate'] . '/xen' . $disknumber . '.img.lzo" bs=1M conv=notrunc,noerror status=none) > ' . $progressfilename . ' 2>&1 &' . '\'';//trailing & sends to background

					} else if ( $settings['compress'] == '3' ) {

						$command = 'ssh root@' . $settings['xen_migrate_ip'] . $extrasshsettings . ' ' . '\'' . '(pv -n /dev/' . $dev . '  | bzip2 -c | dd of="' . $settings['mount_migrate'] . '/xen' . $disknumber . '.img.bzip2" bs=1M conv=notrunc,noerror status=none) > ' . $progressfilename . ' 2>&1 &' . '\'';//trailing & sends to background

					} else if ( $settings['compress'] == '4' ) {

						$command = 'ssh root@' . $settings['xen_migrate_ip'] . $extrasshsettings . ' ' . '\'' . '(pv -n /dev/' . $dev . '  | pbzip2 -c | dd of="' . $settings['mount_migrate'] . '/xen' . $disknumber . '.img.pbzip2" bs=1M conv=notrunc,noerror status=none) > ' . $progressfilename . ' 2>&1 &' . '\'';//trailing & sends to background

					}

					sb_log( 'Xen - Imaging - /dev/' . $dev );

					exec( $command, $statusoffile );
					sb_cache_set( $vmuuid, $snapshotname, 'Imaging Xen VM', 'write' );

					sb_status_set( 'xen_migrate', 'xen_imaging', 2, '', '', '', '', $disknumber );
					$sb_status['setting5'] = $disknumber;

				}
			}

		} else {

			//no additional disks attached to backup vm so clean up
			exec( 'ssh root@' . $settings['xen_migrate_ip'] . $extrasshsettings . ' rm ' . $settings['mount_migrate'] . '/xen*.img -f', $statusoffile );
			exec( 'ssh root@' . $settings['xen_migrate_ip'] . $extrasshsettings . ' rm ' . $settings['mount_migrate'] . '/xen_progress.dat -f', $statusoffile );
			sleep( 2 );
			$progress = 0;
			$reason   = 'No Expected Disk Available';
		}

		if ( empty( $status ) ) {
			sb_cache_set( $vmuuid, $snapshotname, 'Imaging Failure - ' . $reason, 'XEN', 'write' );
		}
	}

	$jsonarray = array(
		"status"         => $status,
		"reason"         => $reason,
		"progress"       => $progress,
		"numberofimages" => $numberofimages,
		"thisdisk"       => $sb_status['setting5'],
	);

	sb_log( 'Xen - Imaging - ' . $sb_status['setting5'] . '/' . $numberofimages . ' - ' . $progress . '% ' . $reason );

	echo json_encode( $jsonarray );