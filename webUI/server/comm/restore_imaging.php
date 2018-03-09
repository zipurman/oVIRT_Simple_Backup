<?php

	$buname        = varcheck( "buname", '' );
	$vmname        = varcheck( "vmname", '' );
	$vmuuid        = varcheck( "vmuuid", '' );
	$diskname      = varcheck( "diskname", '' );
	$diskuuid      = varcheck( "diskuuid", '' );
	$disksize      = varcheck( "disksize", 0, "FILTER_VALIDATE_INT", 0 );
	$progress      = varcheck( "progress", 0, "FILTER_VALIDATE_INT", 0 );
	$disknamecheck = preg_replace( '/[0-9a-zA-Z\-_]/i', '', $diskname );

	$status         = 0;
	$progress       = 0;
	$statusfilename = '';
	$reason         = 'Disk Not Attached';

	if ( ! empty( $disknamecheck ) ) {

		$status = 0;
		$reason = 'Invalid Disk Name';

	} else if ( preg_match( $UUIDv4, $diskuuid ) ) {

		$diskok = 0;
		$disks  = ovirt_rest_api_call( 'GET', 'disks' );
		foreach ( $disks as $disk ) {
			if ( (string) $disk->name == $diskname . '_RDISK' ) {
				if ( (string) $disk->status == 'ok' ) {
					$diskok   = 1;
					$diskuuid = (string) $disk['id'];
				}
			}
		}

		if ( $diskok == 0 ) {
			$status = 2;
			$reason = 'Disk Waiting';
		} else {
			$status = 3;
			$reason = 'Disk Done';
		}

		if ( empty( $diskok ) ) {

			$status = 0;
			$reason = 'Disk Not Ready';

		} else {

			$extradiskdev = '';//needed any more?
			$diskletter   = 'b';

			$status = 1;
			$reason = 'Disk Attached';

			$checkdisk = sb_check_disks(10);
			$dev = $checkdisk['lastdev'];


			if ( $buname == '-migrate-' ) {
				$imagefile        = $settings['mount_migrate'] . '/' . $vmname;
				$statusfilename   = $settings['mount_migrate'] . '/' . $vmname . '.status.dat';
				$progressfilename = $settings['mount_migrate'] . '/' . $vmname . '.progress.dat';

			} else {

				$restorefrompath = $settings['mount_backups'] . '/' . $vmname . '/' . $vmuuid . '/' . $buname;

				$imagefile = $restorefrompath . '/image.img';

				$statusfilename = $restorefrompath . '/status.dat';

				$progressfilename = $restorefrompath . '/progress.dat';

			}

			if ( file_exists( $imagefile ) ) {
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
							sb_cache_set( $settings['uuid_backup_engine'], '', 'Restore Imaging ' . $progress . '%', $diskname, 'write' );
							sleep( 2 );

							if ( $progress >= 100 ) {
								unlink( $statusfilename );
							}
						}
					}

				} else {

					if ( $statusfile = fopen( $statusfilename, "w" ) ) {
						fwrite( $statusfile, '1' );
						fclose( $statusfile );
					}
					sleep( 2 );
					$command = '(pv -n ' . $imagefile . ' | dd of="' . '/dev/' . $dev . '" bs=1M conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background

					$status = 1;
					$reason = 'Started Imaging ' . $imagefile . ' to ' . '/dev/' . $dev;

					exec( $command, $output );
					sb_cache_set( $settings['uuid_backup_engine'], '', 'Restore Imaging', $diskname, 'write' );
				}
			} else {
				$status = 0;
				$reason = 'Files Not Found';
			}

		}

	} else {
		$status = 0;
		$reason = 'Invalid UUID';

	}

	if ( empty( $status ) ) {
		sb_cache_set( $settings['uuid_backup_engine'], '', 'Restore Imaging Failure - ' . $reason, $diskname, 'write' );
	}


	$jsonarray = array(
		"status"         => $status,
		"reason"         => $reason,
		"progress"       => $progress,
		"statusfilename" => $statusfilename,
	);

	echo json_encode( $jsonarray );