<?php

	$status   = 0;
	$progress = 0;
	$reason   = 'Disk Not Attached';
	$vmuuid = 'XEN';
	$snapshotname = 'XEN';

/*	exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' fdisk -l', $output );
	$disktoimage = '';
	foreach ( $output as $item ) {
		if ( strpos( $item, 'xvdb1' ) !== false ) {
			$disktoimage = 'xvdb';
		} else if ( strpos( $item, 'xvday1' ) !== false ) {
			$disktoimage = 'xvday';
		}else if ( strpos( $item, 'xvdd2' ) !== false ) {
			$disktoimage = 'xvdd';
		}
	}*/

	$chkdisk = sb_xen_check_disks(10);
	$disktoimage = $chkdisk['lastdev'];

	if ( ! empty( $disktoimage ) ) {

		$status = 1;
		$reason = 'Disk Attached';

		$dev              = $disktoimage;
		$statusfilename   = $settings['mount_migrate'] . '/xen_status.dat';
		$progressfilename = $settings['mount_migrate'] . '/xen_progress.dat';

		//check if file exists
		exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' ls ' . $settings['mount_migrate'] . '/xen_status.dat', $statusoffile );

		if ( ! empty( $statusoffile ) ) {
			$status = 2;

			exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' cat ' . $settings['mount_migrate'] . '/xen_status.dat  | tail -n-1', $lastvalue );

			if ( empty( $lastvalue ) ) {
				$lastvalue = 0;
			} else {
				$lastvalue = (integer) $lastvalue[0];
			}

			if ( $lastvalue == 1 ) {
				$reason = 'Imaging already in progress.';

				exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' ls ' . $settings['mount_migrate'] . '/xen_progress.dat', $progressoffile );

				if ( ! empty( $progressoffile ) ) {

					exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' cat ' . $settings['mount_migrate'] . '/xen_progress.dat  | tail -n-1', $lastvalue2 );
					if ( empty( $lastvalue ) ) {
						$lastvalue2 = 0;
					} else {
						$lastvalue2 = (integer) $lastvalue2[0];
					}

					$progress = (int) $lastvalue2;
					sb_cache_set( $vmuuid, $snapshotname, 'Imaging ' . $progress . '%', 'Xen VM', 'write' );

				}
			}

			sleep( 2 );

		} else {

			sleep( 2 );

			$reason = 'Start Imaging';

			exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' echo "1" > ' . $settings['mount_migrate'] . '/xen_status.dat', $statusoffile );


			$command = 'ssh root@' . $settings['xen_migrate_ip'] . ' ' . '\'' . '(pv -n /dev/' . $dev . ' | dd of="' . $settings['mount_migrate'] . '/xen.img" bs=1M conv=notrunc,noerror status=none) > ' . $progressfilename . ' 2>&1 &' . '\'';//trailing & sends to background

			exec( $command, $statusoffile );
			sb_cache_set( $vmuuid, $snapshotname, 'Imaging Xen VM', 'write' );
		}

	} else {

		exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' rm ' . $settings['mount_migrate'] . '/xen.img', $statusoffile );
		exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' rm ' . $settings['mount_migrate'] . '/xen_progress.dat', $statusoffile );
		exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' rm ' . $settings['mount_migrate'] . '/xen_status.dat', $statusoffile );
		sleep( 2 );
		$progress = 0;
		$reason   = 'No Expected Disk Available';
	}

	if ( empty( $status ) ) {
		sb_cache_set( $vmuuid, $snapshotname, 'Imaging Failure - ' . $reason, 'XEN', 'write' );
	}

	$jsonarray = array(
		"status"     => $status,
		"reason"     => $reason,
		"progress"   => $progress,
	);

	echo json_encode( $jsonarray );