<?php

	//check if version file exists and create if missing
	if ( ! file_exists( $projectpath . 'version.php' ) ) {
		exec( 'echo "' . $sb_version . '" > /var/www/html/version.php' );
	}
	exec( 'cat /var/www/html/version.php', $version_check );

	//check if version upgrades required
	if ($version_check[0] != $sb_version){
		$oldversion = explode('.', $version_check[0]);
		$newversion = explode('.', $sb_version);


		$old_major = $oldversion[0];
		$old_minor = $oldversion[1];
		$old_patch = $oldversion[2];

		$new_major = $newversion[0];
		$new_minor = $newversion[1];
		$new_patch = $newversion[2];

		echo 'Version upgrades ... ';
		echo 'from ' . $version_check[0] . ' to ' . $sb_version;

		if ($old_major == 0 && $new_major == 0){
			if ($old_minor < 6 && $new_minor == 6){
				//example logic

			} else if ($old_minor == 6 && $new_minor == 6){
				if ($old_patch == 0 && $new_patch == 1){
					//example logic
					//		echo '<br/>Patching 123';
					//		exec( 'rm /var/www/html/cache/statusfile.dat -f' );

				}
			}
		}

		exec( 'echo "' . $sb_version . '" > /var/www/html/version.php' );
	}