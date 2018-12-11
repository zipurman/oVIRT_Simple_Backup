<?php

	//check if version file exists and create if missing
	if ( ! file_exists( $projectpath . 'version.php' ) ) {
		exec( 'echo "' . $sb_version . '" > /var/www/html/version.php' );
	}
	exec( 'cat /var/www/html/version.php', $version_check );

	//check if version upgrades required
	if ( $version_check[0] != $sb_version ) {
		$oldversion = explode( '.', $version_check[0] );
		$newversion = explode( '.', $sb_version );

		$old_major = (integer) $oldversion[0];
		$old_minor = (integer) $oldversion[1];
		$old_patch = (integer) $oldversion[2];

		$new_major = (integer) $newversion[0];
		$new_minor = (integer) $newversion[1];
		$new_patch = (integer) $newversion[2];

		echo 'Version upgrades ... ';
		echo 'from ' . $version_check[0] . ' to ' . $sb_version;

		if ( $old_major == 0 && $new_major == 0 ) {
			if ( $old_minor < 6 && $new_minor == 6 ) {
				//example logic for upgrade from < 0.6 to 0.6.x

			} else if ( $old_minor == 6 && $new_minor == 6 ) {
				//example logic for upgrade from == 0.6 to 0.6.x

				if ( $old_patch < 6 && $new_patch >= 6 ) {
					//0.6.0 -> 0.6.1
					$diskx   = sb_check_disks();

					if ($settings['drive_type'] != $diskx['disktype']){
						echo '<br/>Patching Disk Setup to use: ' . $diskx['disktype'];
						sb_setting_update('drive_type', $diskx['disktype']);
					}

					if ($settings['drive_interface'] != $diskx['driveinterface']){
						echo '<br/>Patching Drive Interface to use: ' . $diskx['driveinterface'];
						sb_setting_update('drive_interface', $diskx['driveinterface']);
					}



				} else if ( $old_patch < 15 && $new_patch >= 15 ) {

					//remove old backup file
					unlink( $projectpath . '.automated_backups_vmlist' );

				} else if ( $old_patch < 16 && $new_patch >= 16 ) {

					echo '<br/>Patching compression options added';
					sb_setting_update('compress', 0);

                } else if ( $old_patch < 30 && $new_patch >= 30 ) {

                    echo '<br/>Patching firstbackupdisk options added';
                    sb_setting_update('firstbackupdisk', 'b');
				}
			}
		}

		exec( 'echo "' . $sb_version . '" > /var/www/html/version.php' );
	}