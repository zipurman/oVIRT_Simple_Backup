<?php

	$sb_status = sb_status_fetch();

	$status     = 0;
	$reason     = 'None';
	$snapshotid = '';

	if ( $sb_status['status'] == 'backup' && $sb_status['stage'] == 'create_path' ) {

		if ( preg_match( $UUIDv4, $sb_status['setting1'] ) ) {

			$snapshotid = $sb_status['setting3'];

			if ( empty( $snapshotid ) ) {

				$status = 0;
				$reason = 'Snapshot Not Found';

			} else if ( ! empty( $sb_status['setting4'] ) ) {

				//create directories to match backup
				exec( "mkdir " . $settings['mount_backups'] . '/' . $sb_status['setting4']. '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . ' -p' );

				$dirokay = 0;
				if ( file_exists( $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] ) ) {
					$dirokay = 1;

					$snapshotdata = ovirt_rest_api_call( 'GET', 'vms/' . $sb_status['setting1']. '/snapshots/' . $snapshotid, '', true );

					sb_cache_set( $sb_status['setting1'], $sb_status['setting2'], 'Creating Path', $sb_status['setting4'], 'write' );

					if ( $xmlfile = fopen( $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/data.xml', "w" ) ) {
						fwrite( $xmlfile, $snapshotdata->initialization->configuration->data );
						fclose( $xmlfile );

						sb_status_set('backup', 'snapshot_attach', 1, $sb_status['setting1'], $sb_status['setting2'], $sb_status['setting3'] );

					} else {
						$status = 0;
						$reason = 'Cannot Create data.xml - Check Permissions to ' . $settings['mount_backups'];
					}

				}

				if ( empty( $dirokay ) ) {
					$status = 0;
                    $free_bu_mnt_space = sb_check_backup_space();
                    if ($free_bu_mnt_space > 99){
                        $reason = 'Error Creating Backup Directories - Your disk ' . $settings['mount_backups'] . ' is ' . $free_bu_mnt_space .'% full.';
                    } else {
                        $reason = 'Error Creating Backup Directories - Check Permissions to ' . $settings['mount_backups'];

                    }
				} else {
					$status = 1;
					$reason = 'Directories Available';
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
			sb_cache_set( $sb_status['setting1'], $sb_status['setting2'], 'Create Paths Failure - ' . $reason, $sb_status['setting4'], 'write' );
		}

	}


	$jsonarray = array(
		"status"     => $status,
		"reason"     => $reason,
	);

	sb_log('Creating Backup Paths - ' . $reason);

	echo json_encode( $jsonarray );