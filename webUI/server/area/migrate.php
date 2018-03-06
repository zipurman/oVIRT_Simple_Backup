<?php

	sb_pagetitle( 'Restore' );

	$checkdisk = sb_check_disks(0);

	if ( empty( $action ) ) {

		sb_pagedescription( 'This migrate utility assumes that you have imaged VMs to the same directory that you have outlined in your settings as your migrate path. ' . $settings['mount_migrate'] . '.' );

		sb_table_start();

		$rowdata = array(
			array(
				"text"  => "Images",
				"width" => "100%",
			),
		);
		sb_table_heading( $rowdata );

		exec( 'ls ' . $settings['mount_migrate'] . '/*.img', $files );

		foreach ( $files as $file ) {

			$file    = str_replace( $settings['mount_migrate'] . '/', '', $file );
			$rowdata = array(
				array(
					"text" => '<a href="?area=4&action=selectedbackup&vmname=' . $file . '">' . $file . '</a>',
				),
			);
			sb_table_row( $rowdata );
		}

		sb_table_end();

	} else if ( $action == 'selectedbackup' ) {

		$vmname = varcheck( "vmname", '' );

		sb_pagedescription( 'The following is the selected image to migrate/restore from ' . $settings['mount_migrate'] . '/' . $vmname );

		$imagefile = $settings['mount_migrate'] . '/' . $vmname;

		if ( file_exists( $imagefile ) ) {
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
				array( "text" => $vmname, ),
				array( "text" => 'Creation Date:', ),
				array( "text" => date( "F d Y H:i:s.", filemtime( $imagefile ) ), ),
			);
			sb_table_row( $rowdata );

			$imagesize = filesize( $imagefile ) / 1024 / 1024 / 1024;
			$rowdata   = array(
				array( "text" => 'Image Size:', ),
				array( "text" => $imagesize . ' GB', ),
				array( "text" => 'New VM Name:', ),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'restorenewname',
						'size'      => '36',
						'maxlength' => '36',
						'value'     => '',
					) ),
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array( "text" => 'Fix Grub:', ),
				array(
					"text" => sb_input( array(
						'type'  => 'select',
						'name'  => 'option_fixgrub',
						'list'  => array(
							array( 'id' => '0', 'name' => 'No', ),
							array( 'id' => '1', 'name' => 'Yes', ),
						),
						'value' => 0,
					) ),
				),
				array( "text" => 'Fix Swap:', ),
				array(
					"text" => sb_input( array(
						'type'  => 'select',
						'name'  => 'option_fixswap',
						'list'  => array(
							array( 'id' => '0', 'name' => 'No', ),
							array( 'id' => '1', 'name' => 'Yes', ),
						),
						'value' => 0,
					) ),
				),
			);
			sb_table_row( $rowdata );

			echo sb_input( array( 'type' => 'hidden', 'name' => 'disksize', 'value' => $imagesize, ) );
			echo sb_input( array( 'type' => 'hidden', 'name' => 'vmname', 'value' => $vmname, ) );
			echo sb_input( array( 'type' => 'hidden', 'name' => 'buname', 'value' => '-migrate-', ) );
			echo sb_input( array( 'type'  => 'hidden',
			                      'name'  => 'vmuuid',
			                      'value' => $settings['uuid_backup_engine'],
			) );//not used - just for validation areas
			echo sb_input( array( 'type' => 'hidden', 'name' => 'sb_area', 'value' => 4, ) );

			sb_table_end();

			sb_gobutton( 'Restore This VM Now', '', 'checkRestoreNow(1);' );

			sb_progress_bar( 'creatediskstatus' );
			sb_progress_bar( 'imagingbar' );
			sb_status_box( 'restorestatus' );

		} else {
			echo 'No matching ' . $imagefile . ' found.';
		}

	}