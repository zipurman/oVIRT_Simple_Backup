<?php

	set_time_limit( 6 * 60 * 60 );//6 hours

	$projectpath = '/var/www/html/';

	$snapshotcheck = ovirt_rest_api_call( 'GET', 'vms/' . $settings['uuid_backup_engine'] . '/snapshots' );

	if ($snapshotcheck != 1){
		sb_email( 'oVirt SimpleBackup Error', 'Backup Engine Configuration Issue. Multiple Snapshots on BackupEngine.' );
	}

	if ( file_exists( $projectpath . 'config.php' ) ) {
		require( $projectpath . 'allowed_ips.php' );
		require( $projectpath . 'functions.php' );
		require( $projectpath . 'config.php' );
		require( $projectpath . 'reg.php' );
		require( $projectpath . 'tz.php' );

		$backupoktorun = 0;

		sb_pagetitle( 'Automated Backup' );

		if ( ! file_exists( $vmbackupinprocessfile ) ) {
			exec( 'touch ' . $vmbackupinprocessfile );
		}

		$backuplist    = file_get_contents( $vmbackupinprocessfile );
		$backuplisttmp = explode( "\n", $backuplist );
		$backuplist    = array();
		$backuplist2   = array();

		if ( empty( $backuplist[0] ) ) {
			//Create Backup UUIDs File for Backup Routines
			if ( file_exists( $vmconfigfile ) ) {
				$configdata = file_get_contents( $vmconfigfile );

				if ( empty( $configdata ) ) {
					echo 'No VMs selected to backup';
				} else {

					//get VM list
					$vms = ovirt_rest_api_call( 'GET', 'vms' );

					if ( ! empty( $vms ) ) {
						echo '<ul>';
						//prep VMs To Backup
						foreach ( $vms AS $vm ) {

							if ( $vm['id'] != $settings['uuid_backup_engine'] && $vm->name != 'HostedEngine' ) {

								if ( strpos( $configdata, '[' . $vm->name . ']' ) !== false ) {

									echo '<li>' . $vm->name . ' (UUID=' . $vm['id'] . ')</li>';

									exec( 'echo ' . $vm['id'] . ' >> ' . $vmbackupinprocessfile );

									$backuplist[]  = (string) $vm['id'];
									$backuplist2[] = (string) $vm->name;

								}
							}
						}
						echo '</ul>';

						$backupoktorun = 1;
					} else {
						echo 'No matching VMs found to backup';

					}
				}
			} else {
				echo 'No VMs selected to backup';
			}

		} else {
			echo 'Backup Already In Process...';

		}
	}

	if ( ! empty( $backupoktorun ) ) {


		if ( empty( $backuplist ) ) {

			sb_email( 'oVirt SimpleBackup Skipped', 'Nothing selected to be backed up.' );

		} else {

			sb_log( '-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*' );
			sb_log( 'Automated Backup Starting ....' );
			sb_log( '-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*' );

			sb_email( 'oVirt SimpleBackup Starting', 'Backup Starting ... A log will be emailed upon completion.' );

			exec( 'rm ' . $vmbackupemaillog . ' -f' );

			sb_email_log( '<b>Automated Backup Starting ....</b><br/>' );
			$itemnum = 0;

			foreach ( $backuplist as $item ) {

				sb_log( 'Backing up VM UUID: ' . $item );
				sb_email_log( 'Backing up VM UUID: ' . $item . ' - ' . $backuplist2[ $itemnum ] . '<br/>' );

				$status = 0;
				$vmuuid = $item;
				while ( $status < 1 ) {
					sleep( 2 );
					require( $projectpath . 'comm/snapshot_status.php' );
					if ( ! file_exists( $vmconfigfile ) ) {
						die();
					}
				}
				sb_email_log( $reason . '<br/>' );

				$status = 0;
				while ( $status < 1 ) {
					sleep( 2 );
					require( $projectpath . 'comm/backup_create_path.php' );
					if ( ! file_exists( $vmconfigfile ) ) {
						die();
					}
				}
				sb_email_log( $reason . '<br/>' );

				$status = 0;
				while ( $status < 1 ) {
					sleep( 2 );
					require( $projectpath . 'comm/backup_attach_image.php' );
					if ( ! file_exists( $vmconfigfile ) ) {
						die();
					}
				}
				sb_email_log( $reason . '<br/>' );

				$status = 0;
				while ( $status < 3 ) {
					sleep( 2 );
					require( $projectpath . 'comm/backup_imaging.php' );
					if ( ! file_exists( $vmconfigfile ) ) {
						die();
					}
				}
				sb_email_log( $reason . '<br/>' );

				$status = 0;
				while ( $status < 1 ) {
					sleep( 2 );
					require( $projectpath . 'comm/backup_detatch_image.php' );
					if ( ! file_exists( $vmconfigfile ) ) {
						die();
					}
				}
				sb_email_log( $reason . '<br/>' );

				$status = 0;
				while ( $status < 1 ) {
					sleep( 2 );
					require( $projectpath . 'comm/snapshot_delete.php' );
					if ( ! file_exists( $vmconfigfile ) ) {
						die();
					}
				}
				sb_email_log( $reason . '<br/>' );

				$backuppath = $settings['mount_backups'] . '/' . $backuplist2[ $itemnum ] . '/' . $item;
				exec( 'ls ' . $backuppath, $files );
				rsort( $files );
				$numsofar = 1;
				foreach ( $files as $file ) {
					echo '' . $file . '<br/>';

					if ( $numsofar > $settings['retention'] ) {
						exec( 'rm ' . $backuppath . '/' . $file . ' -r -f' );
						sb_log( '** Removing ' . $backuplist2[ $itemnum ] . ' Backup ' . $file . ' based on retention of ' . $settings['retention'] );
						sb_email_log( '** Removing ' . $backuplist2[ $itemnum ] . ' Backup ' . $file . ' based on retention of ' . $settings['retention'] . ' backups.<br/>' );
					}
					$numsofar ++;

				}

				$itemnum ++;

			}
			sb_log
			( '-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*' );
			sb_log( '---- Automated Backup Done' );
			sb_log( '-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*' );

			sb_email_log( '<b>...Automated Backup Done</b><br/>' );

			$vmbackupemaillog = file_get_contents( $vmbackupemaillog );

			sb_email( 'oVirt SimpleBackup Completed', $vmbackupemaillog );

		}

		if ( ! empty( $vmbackupinprocessfile ) ) {
			if ( file_exists( $vmbackupinprocessfile ) ) {
				exec( 'rm ' . $vmbackupinprocessfile . ' -f' );
			}
		}
	}