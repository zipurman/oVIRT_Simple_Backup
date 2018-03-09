<?php

	sb_pagetitle( 'Restore' );

	$checkdisk = sb_check_disks( 0 );

	if ( empty( $action ) ) {

		sb_pagedescription( 'The following backups folders are available in ' . $settings['mount_backups'] . '.' );

		sb_table_start();

		$rowdata = array(
			array(
				"text"  => "VM Folder Name",
				"width" => "100%",
			),
		);
		sb_table_heading( $rowdata );

		exec( 'ls ' . $settings['mount_backups'] . '', $files );

		foreach ( $files as $file ) {
			$rowdata = array(
				array(
					"text" => '<a href="?area=3&action=vmbackups&vmname=' . $file . '">' . $file . '</a>',
				),
			);
			sb_table_row( $rowdata );
		}

		sb_table_end();

	} else if ( $action == 'vmbackups' ) {

		$vmname = varcheck( "vmname", '' );
		$vmname = preg_replace( '/[^0-9a-zA-Z\-_]/i', '', $vmname );

		sb_pagedescription( 'The following backups UUID folders are available in ' . $settings['mount_backups'] . '/' . $vmname . '.' );

		sb_table_start();

		$rowdata = array(
			array(
				"text"  => "VM Folder Name",
				"width" => "100%",
			),
		);
		sb_table_heading( $rowdata );

		exec( 'ls ' . $settings['mount_backups'] . '/' . $vmname, $files );

		$rowdata = array(
			array(
				"text" => '<a href="?area=3' . '">..</a>',
			),
		);
		sb_table_row( $rowdata );

		foreach ( $files as $file ) {
			$rowdata = array(
				array(
					"text" => '<a href="?area=3&action=uuidbackups&vmname=' . $vmname . '&uuid=' . $file . '">' . $file . '</a>',
				),
			);
			sb_table_row( $rowdata );
		}

		sb_table_end();

	} else if ( $action == 'uuidbackups' ) {

		$vmname = varcheck( "vmname", '' );
		$vmname = preg_replace( '/[^0-9a-zA-Z\-_]/i', '', $vmname );
		$uuid   = varcheck( "uuid", '' );

		sb_pagedescription( 'The following backup dates are available in ' . $settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '.' );

		if ( preg_match( $UUIDv4, $uuid ) ) {
			sb_table_start();

			$rowdata = array(
				array(
					"text"  => "VM Folder Name",
					"width" => "100%",
				),
			);
			sb_table_heading( $rowdata );

			$rowdata = array(
				array(
					"text" => '<a href="?area=3&action=vmbackups&vmname=' . $vmname . '">..</a>',
				),
			);
			sb_table_row( $rowdata );

			exec( 'ls ' . $settings['mount_backups'] . '/' . $vmname . '/' . $uuid, $files );

			foreach ( $files as $file ) {
				$rowdata = array(
					array(
						"text" => '<a href="?area=3&action=selectedbackup&vmname=' . $vmname . '&uuid=' . $uuid . '&buname=' . $file . '">' . $file . '</a>',
					),
				);
				sb_table_row( $rowdata );
			}

			sb_table_end();

		} else {
			echo 'Invalid UUID';
		}

	} else if ( $action == 'selectedbackup' ) {

		$vmname = varcheck( "vmname", '' );
		$vmname = preg_replace( '/[^0-9a-zA-Z\-_]/i', '', $vmname );
		$buname = varcheck( "buname", '' );
		$buname = preg_replace( '/[^0-9a-zA-Z\-_]/i', '', $buname );
		$uuid   = varcheck( "uuid", '' );

		sb_pagedescription( 'The following is the selected backup to restore from ' . $settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '/' . $buname . '.' );

		if ( preg_match( $UUIDv4, $uuid ) ) {

			$xmlfile   = $settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '/' . $buname . '/data.xml';
			$imagefile = $settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '/' . $buname . '/image.img';

			if ( file_exists( $xmlfile ) && file_exists( $imagefile ) ) {
				$xmldata = file_get_contents( $xmlfile );
				$xml     = simplexml_load_string( $xmldata );

				//				showme( $xml );
				sb_table_start();

				$rowdata = array(
					array( "text" => "", "width" => "10%", ),
					array( "text" => "", "width" => "40%", ),
					array( "text" => "", "width" => "10%", ),
					array( "text" => "", "width" => "40%", ),
				);
				sb_table_heading( $rowdata );

				$rowdata = array(
					array( "text" => 'VM Name:', ),
					array( "text" => $xml->Content->Name . ' (' . $buname . ')', ),
					array( "text" => 'Creation Date:', ),
					array( "text" => $xml->Content->CreationDate, ),
				);
				sb_table_row( $rowdata );

				$rowdata = array(
					array( "text" => 'Min Memory:', ),
					array( "text" => $xml->Content->MinAllocatedMem / 1024 . ' GB', ),
					array( "text" => 'Max Memory:', ),
					array( "text" => $xml->Content->MaxMemorySizeMb / 1024 . ' GB', ),
				);
				sb_table_row( $rowdata );

				$rowdata = array(
					array( "text" => 'Cluster:', ),
					array( "text" => $xml->Content->ClusterName, ),
					array( "text" => 'Template:', ),
					array( "text" => $xml->Content->TemplateName, ),
				);
				sb_table_row( $rowdata );

				sb_table_end();

				$imagesize = filesize( $imagefile ) / 1024 / 1024 / 1024;
				sb_new_vm_settings($imagesize,$xml->Content->MinAllocatedMem / 1024, $xml->Content->MaxMemorySizeMb / 1024);

				echo sb_input( array( 'type' => 'hidden', 'name' => 'disksize', 'value' => $imagesize, ) );
				echo sb_input( array( 'type' => 'hidden', 'name' => 'vmname', 'value' => $xml->Content->Name, ) );
				echo sb_input( array( 'type' => 'hidden', 'name' => 'buname', 'value' => $buname, ) );
				echo sb_input( array( 'type' => 'hidden', 'name' => 'vmuuid', 'value' => $uuid, ) );
				echo sb_input( array( 'type' => 'hidden', 'name' => 'sb_area', 'value' => 3, ) );


				sb_gobutton( 'Restore This VM Now', '', 'checkRestoreNow(1);' );

				sb_progress_bar( 'creatediskstatus' );
				sb_progress_bar( 'imagingbar' );
				sb_status_box( 'restorestatus' );


			} else {
				echo 'No data.xml or image.img found.';
			}

		} else {
			echo 'Invalid UUID';
		}

	}