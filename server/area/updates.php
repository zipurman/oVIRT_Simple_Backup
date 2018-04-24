<?php

	$projectpath = '/var/www/html/';

	function sb_check_upgrade_version() {

		$regcheck          = file_get_contents( 'https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/server/reg.php' );
		$checkversion      = strpos( $regcheck, 'sb_version =' );
		$checkversionstart = strpos( $regcheck, '\'', $checkversion );
		$checkversionend   = strpos( $regcheck, '\'', $checkversionstart + 1 );
		$newversion        = substr( $regcheck, $checkversionstart + 1, $checkversionend - ( $checkversionstart + 1 ) );

		return $newversion;
	}

	function sb_fetch_upgrade_versioning( $updatenow = 0 ) {

		GLOBAL $projectpath;

		$filearray            = array();
		$filearray['files']   = array();
		$filearray['folders'] = array();
		exec( 'ls ' . $projectpath, $updatefileslevel1 );
		foreach ( $updatefileslevel1 as $updatefileslevel1_item ) {
			if (
				$updatefileslevel1_item != 'version.php' &&
				$updatefileslevel1_item != 'allowed_ips.php' &&
				strpos( " {$updatefileslevel1_item}", 'config' ) == false &&
				strpos( "{$updatefileslevel1_item}", '.dat' ) == false &&
				$updatefileslevel1_item != 'cache'
			) {
				if ( strpos( $updatefileslevel1_item, '.php' ) !== false ) {
					$filearray['files'][] = $updatefileslevel1_item;
				} else {
					$filearray['folders'][ $updatefileslevel1_item ] = $updatefileslevel1_item;
				}
			}
		}

		foreach ( $filearray['folders'] as $folder ) {

			$filearray['folders'][ $folder ]            = array();
			$filearray['folders'][ $folder ]['files']   = array();
			$filearray['folders'][ $folder ]['folders'] = array();

			$updatefileslevel2 = null;
			exec( 'ls ' . $projectpath . '/' . $folder, $updatefileslevel2 );
			foreach ( $updatefileslevel2 as $updatefileslevel2_item ) {
				if (
					strpos( "{$updatefileslevel2_item}", '.dat' ) == false
				) {
					if ( strpos( $updatefileslevel2_item, '.php' ) !== false ) {
						$filearray['folders'][ $folder ]['files'][] = $updatefileslevel2_item;
					} else {
						$filearray['folders'][ $folder ]['folders'][ $updatefileslevel2_item ] = $updatefileslevel2_item;
					}
				}
			}
		}

		//css and js folders
		foreach ( $filearray['folders']['site']['folders'] as $folder ) {

			$filearray['folders']['site']['folders'][ $folder ]          = array();
			$filearray['folders']['site']['folders'][ $folder ]['files'] = array();

			$updatefileslevel2 = null;
			exec( 'ls ' . $projectpath . '/site/' . $folder, $updatefileslevel2 );
			foreach ( $updatefileslevel2 as $updatefileslevel2_item ) {

				if (
					strpos( "{$updatefileslevel2_item}", '.dat' ) == false
				) {
					$filearray['folders']['site']['folders'][ $folder ]['files'][] = $updatefileslevel2_item;
				}
			}
		}

		sb_process_updated_files( $filearray, '', $updatenow );

	}

	function sb_process_updated_files( $filearray, $folder = '', $updatenow ) {

		GLOBAL $projectpath;

		if (!empty($filearray['files'])) {
			foreach ( $filearray['files'] as $item ) {

				$remotefile = file_get_contents( 'https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/server' . $folder . '/' . $item );

				$remotehash = hash( 'md5', $remotefile );

				$localhash = hash_file( 'md5', substr( $projectpath, 0, - 1 ) . $folder . '/' . $item );

				$filematch = ( $remotehash == $localhash ) ? '<span class="statusup">UP TO DATE</span>' : '<span class="statusdown">NEEDS UPDATE</span>';

				if ( $updatenow == 1 ) {
					if ( $remotehash != $localhash ) {
						file_put_contents( substr( $projectpath, 0, - 1 ) . $folder . '/' . $item, $remotefile );
						$filematch = '<span class="statusupdated">!UPDATED!</span>';
					}
				}

				$rowdata = array(
					array(
						"text" => $filematch,
					),
					array(
						"text" => substr( $projectpath, 0, - 1 ) . $folder . '/' . $item,
					),
					array(
						"text" => $localhash,
					),
					array(
						"text" => $remotehash,
					),
				);
				sb_table_row( $rowdata );

			}
			if (!empty($filearray['folders'])) {
				foreach ( $filearray['folders'] as $key => $item ) {
					sb_process_updated_files( $item, $folder . '/' . $key, $updatenow );

				}
			}
		}
	}

	sb_pagetitle( 'oVirt Simple Backup Updater' );

	$sb_status = sb_status_fetch();

	sb_table_start();


	if ( $sb_status['status'] == 'ready' ) {
		sb_pagedescription( 'This area will allow you to update to the latest version.' );

		$newversion = sb_check_upgrade_version();


		$rowdata = array(
			array(
				"text" => 'Your Version:',
			),
			array(
				"text" => $sb_version,
			),
			array(
				"text" => "Latest Version:",
			),
			array(
				"text" => $newversion,
			),
		);
		sb_table_row( $rowdata );

		if ( $sb_version == $newversion ) {

			$rowdata = array(
				array(
					"text" => 'Status:',
				),
				array(
					"text" => '<b>VERSION UP TO DATE</b>',
				),
				array(
					"text" => "",
				),
				array(
					"text" => '',
				),
			);
			sb_table_row( $rowdata );



		}

		sb_table_end();



		if ( empty( $action ) ) {
			sb_gobutton( 'Check For Updated Files', '', 'sbCheckSoftwareUpdates();' );
		} else if ( $action == 'update' ) {
			sb_gobutton( 'Update Files Now', '', 'sbProcessSoftwareUpdates();' );
		}

		if ( $action == 'update' ) {
			sb_table_start();

			$rowdata = array(
				array(
					"text"  => "Status",
					"width" => "20%",
				),
				array(
					"text"  => "File",
					"width" => "30%",
				),
				array(
					"text"  => "Local Hash",
					"width" => "25%",
				),
				array(
					"text"  => "Remote Hash",
					"width" => "25%",
				),
			);
			sb_table_heading( $rowdata );
			sb_fetch_upgrade_versioning();
			sb_table_end();
		} else if ( $action == 'updatenow' ) {
			sb_table_start();

			$rowdata = array(
				array(
					"text"  => "Status",
					"width" => "20%",
				),
				array(
					"text"  => "File",
					"width" => "30%",
				),
				array(
					"text"  => "Local Hash",
					"width" => "25%",
				),
				array(
					"text"  => "Remote Hash",
					"width" => "25%",
				),
			);
			sb_table_heading( $rowdata );
			sb_fetch_upgrade_versioning( 1 );
			sb_table_end();
		}

	} else {
		sb_not_ready();
	}