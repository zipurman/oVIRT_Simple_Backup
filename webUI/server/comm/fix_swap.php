<?php

	$sb_status = sb_status_fetch();

	$status = 0;
	$reason = 'Off';
	if ( $sb_status['status'] == 'restore' && $sb_status['stage'] == 'fixes' ) {

		if ( strpos( $sb_status['setting6'], 'fixswap' ) !== false ) {

			if ($sb_status['setting3'] == '-XEN-') {
				$filepath     = $settings['mount_migrate']  . '/';
			} else {
				$filepath     = $settings['mount_backups'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $sb_status['setting3'] . '/';
			}
			$disktypeget    = sb_check_disks();
			$numberofimages = count( $disktypeget['avaliabledisks'] );
			$processdisks   = sb_disk_array_fetch( $filepath );

			foreach ( $disktypeget['avaliabledisks'] as $avaliabledisk ) {
				foreach ( $processdisks as $processdisk ) {
					if ( $processdisk['path'] == $avaliabledisk && empty( $dev ) && $processdisk['bootable'] == 'true' ) {
						$disknumberfile = $processdisk['disknumber'];
						$dev            = $avaliabledisk;
					}
				}
			}

			if ( $sb_status['step'] == 0 ) {
				if ( $cronsfile = fopen( '../crons/fixswap.dat', "w" ) ) {
					fwrite( $cronsfile, '1' );
					fclose( $cronsfile );
				}
				if ( $cronsfile = fopen( '../crons/fixswaptarget.dat', "w" ) ) {
					fwrite( $cronsfile, $dev );
					fclose( $cronsfile );
				}
				sb_status_set( 'restore', 'fixes', 1 );

			}

			if ( file_exists( '../crons/fixswap.dat' ) ) {
				$cronsetting = file_get_contents( '../crons/fixswap.dat' );
				if ( $cronsetting == 1 ) {
					$status = 1;
					$reason = 'Waiting for cron job';
				} else if ( $cronsetting == 2 ) {
					$status = 2;
					$reason = 'Completed';
					if ( $cronsfile = fopen( '../crons/fixswap.dat', "w" ) ) {
						fwrite( $cronsfile, 0 );
						fclose( $cronsfile );
					}
					$sb_status['setting6'] = str_replace( 'fixswap', '', $sb_status['setting6'] );
					sb_status_set( 'restore', 'fixes', 0, '', '', '', '', '', $sb_status['setting6'] );

				} else if ( $cronsetting == 0 ) {
					$status = 0;
					$reason = 'Off';

				}
			}
		} else {

			$status = 2;
			$reason = 'Completed';
		}
	}

	sleep( 2 );

	$jsonarray = array(
		"status" => $status,
		"reason" => $reason,
	);
	sb_log('Fix Swap - ' . $reason);

	echo json_encode( $jsonarray );