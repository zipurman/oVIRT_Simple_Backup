<?php

	$vmuuid       = varcheck( "vmuuid", '' );
	$snapshotname = varcheck( "snapshotname", '' );

	$status   = 0;
	$progress = 0;
	$dev = '';
	$reason   = 'Disk Not Attached';

	if ( empty( $snapshotname ) ) {
		$status = 0;
		$reason = 'Invalid Snapshot';
	} else if ( preg_match( $UUIDv4, $vmuuid ) ) {

		$vm = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid );

		$snapshots = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid . '/snapshots' );

		foreach ( $snapshots as $snapshot ) {
			if ( $snapshot->description == $snapshotname ) {
				$snapshotid = (string) $snapshot['id'];
			}
		}

		if ( empty( $snapshotid ) ) {

			$status = 0;
			$reason = 'Snapshot Not Found';

		} else if ( ! empty( $vm ) ) {

			$disks = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid . '/snapshots/' . $snapshotid . '/disks' );

			$diskid       = $disks->disk['id'];
			$extradiskdev = '';//needed any more?
			$diskletter   = 'b';

			$checkdiskattached = ovirt_rest_api_call( 'GET', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/' );

			foreach ( $checkdiskattached as $attacheddisk ) {
				if ( (string) $attacheddisk['id'] == (string) $diskid ) {
					$status = 1;
					$reason = 'Disk Attached';

					$checkdisk = sb_check_disks(10);
					$dev = $checkdisk['lastdev'];

					$statusfilename = $settings['mount_backups'] . '/' . $vm->name . '/' . $vm['id'] . '/' . $snapshotname . '/status.dat';

					$progressfilename = $settings['mount_backups'] . '/' . $vm->name . '/' . $vm['id'] . '/' . $snapshotname . '/progress.dat';

					if ( file_exists( $statusfilename ) ) {
						$status     = 2;
						$statusfile = fopen( $statusfilename, "r" );
						fseek( $statusfile, - 1, SEEK_END );
						$lastvalue = fgetc( $statusfile );
						if ( $lastvalue == 1 ) {
							$reason = 'Imaging already in progress.';

							if ( file_exists( $progressfilename ) ) {
								$line         = '';
								$cursor       = - 1;
								$progressfile = fopen( $progressfilename, "r" );
								fseek( $progressfile, $cursor, SEEK_END );
								$progress = fgetc( $progressfile );
								/**
								 * Trim trailing newline chars of the file
								 */
								while ( $progress === "\n" || $progress === "\r" ) {
									fseek( $progressfile, $cursor --, SEEK_END );
									$progress = fgetc( $progressfile );
								}
								/**
								 * Read until the start of file or first newline char
								 */
								while ( $progress !== false && $progress !== "\n" && $progress !== "\r" ) {
									$line = $progress . $line;
									fseek( $progressfile, $cursor --, SEEK_END );
									$progress = fgetc( $progressfile );
								}
								$progress = (int) $line;
								sb_cache_set( $vmuuid, $snapshotname, 'Imaging ' . $progress . '%', $vm->name, 'write', '?area=2&action=select&backupnow=1&vm=' . $vmuuid . '&recovery=1', 'sb_snapshot_imaging(\'' . $vmuuid .'\', \'' . $snapshotname .'\');' );
								sleep( 2 );
							}
						}

					} else {

						if ( $statusfile = fopen( $statusfilename, "w" ) ) {
							fwrite( $statusfile, '1' );
							fclose( $statusfile );
						}

						$command = '(pv -n /dev/' . $dev . ' | dd of="' . $settings['mount_backups'] . '/' . $vm->name . '/' . $vm['id'] . '/' . $snapshotname . '/image.img" bs=1M conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background
						exec( $command, $output );

						sb_cache_set( $vmuuid, $snapshotname, 'Imaging', $vm->name, 'write' );

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
		sb_cache_set( $vmuuid, $snapshotname, 'Imaging Failure - ' . $reason, $vm->name, 'write' );
	}

	$jsonarray = array(
		"status"     => $status,
		"reason"     => $reason,
		"snapshotid" => $snapshotid,
		"progress"   => $progress,
		"dev"   => $dev,
	);

	echo json_encode( $jsonarray );