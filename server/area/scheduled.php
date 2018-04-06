<?php

	$sb_status = sb_status_fetch();


	if ( ! file_exists( $vmconfigfile ) ) {
		exec( 'echo "" > ' . $vmconfigfile );
	}

	sb_pagetitle( 'Scheduled Backups' );

	if ( $sb_status['status'] == 'ready') {
		if ( $action == 2 ) {

			$vm_items = varcheck( "vm_items", '' );
			if ( ! is_array( $vm_items ) ) {
				$vm_items = array();
			}

			$newfiletext = '';
			foreach ( $vm_items as $vm_item ) {
				$newfiletext .= '[' . $vm_item . ']';
			}

			$configfile = fopen( $vmconfigfile, "w" ) or die( "Unable to open config file.<br/><br/>Check permissions on /var/www/html/.automated_backups_vmlist!" );
			fwrite( $configfile, $newfiletext );
			fclose( $configfile );

			$action = 0;
		}

		if ( empty( $action ) ) {


			$configdata = file_get_contents( $vmconfigfile );

			sb_pagedescription( 'Select which VMs should be included in the scheduled backup.' );

			sb_form_start();

			$vms = ovirt_rest_api_call( 'GET', 'vms' );

			sb_table_start();

			$rowdata = array(
				array(
					"text"  => "VM",
					"width" => "20%",
				),
				array(
					"text"  => "Status",
					"width" => "10%",
				),
				array(
					"text"  => "Memory",
					"width" => "10%",
				),
				array(
					"text"  => "Disk",
					"width" => "10%",
				),
				array(
					"text"  => "UUID",
					"width" => "50%",
				),
			);
			sb_table_heading( $rowdata );

			foreach ( $vms AS $vm ) {

				$disks = ovirt_rest_api_call( 'GET', 'vms/' . $vm['id'] . '/diskattachments' );

				$disk = ovirt_rest_api_call( 'GET', 'disks/' . $disks->disk_attachment['id'] );

				if ( $vm->status == 'up' ) {
					$status = '<span class="statusup">Running</span>';
				} else {
					$status = '<span class="statusdown">Down</span>';
				}

				if ( $vm['id'] == $settings['uuid_backup_engine'] ) {
					$rowlink = $vm->name . ' (This VM)';
				} else {

					if ( strpos( $configdata, '[' . $vm->name . ']' ) !== false ) {
						$checked = true;
					} else {
						$checked = false;
					}

					$rowlink = sb_input( array(
							'type'    => 'checkbox',
							'name'    => 'vm_items[]',
							'id'      => 'vm_item_' . $vm->name,
							'value'   => $vm->name,
							'checked' => $checked,
						) ) . $vm->name;

				}

				if ( $vm->name != 'HostedEngine' ) {

					$rowdata = array(
						array(
							"text" => $rowlink,
						),
						array(
							"text" => $status,
						),
						array(
							"text" => round( $vm->memory / 1024 / 1024 / 1024 ) . 'GB',
						),
						array(
							"text" => round( $disk->provisioned_size / 1024 / 1024 / 1024 ) . 'GB',
						),
						array(
							"text" => $vm['id'],
						),
					);
					sb_table_row( $rowdata );
				}

			}

			sb_table_end();

			echo '<br/><br/>' . sb_input( array(
					'type'  => 'submit',
					'value' => 'Save Changes',
				) );

			echo sb_input( array(
				'type'  => 'hidden',
				'name'  => 'area',
				'value' => '1',
			) );

			echo sb_input( array(
				'type'  => 'hidden',
				'name'  => 'action',
				'value' => '2',
			) );

			sb_form_end();
		}
	} else {
		sb_not_ready();
	}