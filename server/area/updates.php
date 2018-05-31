<?php

	$projectpath = '/var/www/html/';


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