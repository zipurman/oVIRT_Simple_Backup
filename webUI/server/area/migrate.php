<?php

    die('This area not currently supported');

	sb_pagetitle( 'Restore' );

	$checkdisk = sb_check_disks( 0 );

	if ( empty( $action ) ) {

		sb_pagedescription( 'This migrate utility allows you to try to import a RAW image file.img into oVirt. This may work, but may not depending on your image type. Only single disk is available. ' . $settings['mount_migrate'] . '.' );

		sb_table_start();

		$rowdata = array(
			array(
				"text"  => "Image Name",
				"width" => "50%",
			),
			array(
				"text"  => "Size",
				"width" => "50%",
			),
		);
		sb_table_heading( $rowdata );

		exec( 'ls ' . $settings['mount_migrate'] . '/*.img', $files );

		foreach ( $files as $file ) {

			$imagesize = filesize( $file ) / 1024 / 1024 / 1024;

			$file    = str_replace( $settings['mount_migrate'] . '/', '', $file );
			$rowdata = array(
				array(
					"text" => '<a href="?area=4&action=selectedbackup&vmname=' . $file . '">' . $file . '</a>',
				),
				array(
					"text" => $imagesize . ' GB',
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

			sb_table_end();

			$imagesize = filesize( $imagefile ) / 1024 / 1024 / 1024;
			sb_new_vm_settings( $imagesize, 2, 2 );

			echo sb_input( array( 'type' => 'hidden', 'name' => 'disksize', 'value' => $imagesize, ) );
			echo sb_input( array( 'type' => 'hidden', 'name' => 'vmname', 'value' => $vmname, ) );
			echo sb_input( array( 'type' => 'hidden', 'name' => 'buname', 'value' => '-migrate-', ) );
			echo sb_input( array(
				'type'  => 'hidden',
				'name'  => 'vmuuid',
				'value' => $settings['uuid_backup_engine'],
			) );//not used - just for validation areas
			echo sb_input( array( 'type' => 'hidden', 'name' => 'sb_area', 'value' => 4, ) );

			?>
            <script>
                sb_restore_area = 2;
            </script>
			<?php
			sb_gobutton( 'Restore This VM Now', '', 'checkRestoreNow(1);' );

			sb_progress_bar( 'creatediskstatus' );
			sb_progress_bar( 'imagingbar' );
			sb_status_box( 'restorestatus' );

		} else {
			echo 'No matching ' . $imagefile . ' found.';
		}

	}