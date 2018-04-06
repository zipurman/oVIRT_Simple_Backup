<?php

	sb_pagetitle( 'Single Backup' );

	$sb_status = sb_status_fetch();

	$disktypeget    = sb_check_disks();
	$numberofimages = count( $disktypeget['avaliabledisks'] );

	if ( $numberofimages > 1 ) {
		sb_pagedescription( 'The backup VM has too many disks attached. Please remove all but the OS disk in order to preform a backup.' );
	} else if ( $sb_status['status'] == 'ready' || $recovery == 1) {

		if ( empty( $action ) ) {

			sb_pagedescription( 'Select a VM from the list below.' );

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

				//		showme( $disks->disk_attachment );

				$disk = ovirt_rest_api_call( 'GET', 'disks/' . $disks->disk_attachment['id'] );

				if ( $vm->status == 'up' ) {
					$status = '<span class="statusup">Running</span>';
				} else {
					$status = '<span class="statusdown">Down</span>';
				}

				$rowlink = ( $vm['id'] == $settings['uuid_backup_engine'] ) ? '<a href="javascript: alert(\'You cannot backup the Backup Appliance VM using oVirt Simple Backup.\n\nThe best way to backup the appliance is to export it using the Web GUI to your export domain.\');">' . $vm->name . '</a> (This VM)' : '<a href="?area=2&action=select&vm=' . $vm['id'] . '">' . $vm->name . '</a>';

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

		} else if ( $action == 'select' ) {

			$vmuuid    = varcheck( "vm", '' );

			$vm = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid );

			$disks = ovirt_rest_api_call( 'GET', 'vms/' . $vm['id'] . '/diskattachments' );

            $diskstring = '';

			foreach ( $disks->disk_attachment as $disk ) {
				$diskx = ovirt_rest_api_call( 'GET', 'disks/' . $disk['id'] );
				$diskdetails = ovirt_rest_api_call( 'GET', 'vms/' . $vm['id'] . '/diskattachments/' . $disk['id'] );

				$boottext = ($diskdetails->bootable == 'true') ? ' (bootable)' : '';
				$diskstring .= $disk['id'] . ' (' . ( $diskx->provisioned_size / 1024 / 1024 / 1024). ' GB) ' . $boottext . '<br/>';
			}

			sb_table_start();

			$rowdata = array(
				array(
					"text"  => "",
					"width" => "10%",
				),
				array(
					"text"  => "",
					"width" => "40%",
				),
				array(
					"text"  => "",
					"width" => "10%",
				),
				array(
					"text"  => "",
					"width" => "40%",
				),
			);
			sb_table_heading( $rowdata );

			$rowdata = array(
				array(
					"text" => 'VM Name:',
				),
				array(
					"text" => '' . $vm->name . '',
				),
				array(
					"text" => "Memory:",
				),
				array(
					"text" => $vm->memory / 1024 / 1024 / 1024 . 'GB',
				),
			);
			sb_table_row( $rowdata );

			if ( $vm->status == 'up' ) {
				$status = '<span class="statusup">Running</span>';
			} else {
				$status = '<span class="statusdown">Down</span>';
			}
			$rowdata = array(
				array(
					"text" => 'Status:',
				),
				array(
					"text" => '' . $status . '',
				),
				array(
					"text" => "Disk(s):",
				),
				array(
					"text" => $diskstring,
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array(
					"text" => 'UUID:',
				),
				array(
					"text" => $vm['id'],
				),
				array(
					"text" => "",
				),
				array(
					"text" => '',
				),
			);
			sb_table_row( $rowdata );

			sb_table_end();


			if (empty($recovery)) {
				sb_gobutton( 'Backup This VM Now', '', 'checkBackupNow();' );

				?>
                <script>
                    function checkBackupNow() {
                        if (confirm('Backup this VM Now?')) {
                            sb_newsnapshot = 1;
                            $(".gobutton").css('display', 'none');
                            //start checking for status of snapshot
                            sb_update_statusbox('backupstatus', 'Starting Backup ...');
                            sb_check_snapshot_progress('<?php echo $vm['id']; ?>', 0);
                        }
                    }
                </script>
				<?php
			} else {

			    $jsstring = '';

			    if ($sb_status['status'] == 'backup'){

				    $jsstring .= 'sb_update_statusbox(\'backupstatus\', \'Resuming Backup ...\');';

				    if ($sb_status['stage'] == 'snapshot'){
					    $jsstring .= 'sb_check_snapshot_progress(\'' . $vmuuid . '\');';
				    } else if ($sb_status['stage'] == 'create_path'){
					    $jsstring .= 'sb_create_backup_directories();';
				    } else if ($sb_status['stage'] == 'snapshot_attach'){
					    $jsstring .= 'sb_snapshot_attach();';
				    } else if ($sb_status['stage'] == 'backup_imaging'){
					    $jsstring .= 'sb_snapshot_imaging();';
				    }else if ($sb_status['stage'] == 'backup_detatch_image'){
					    $jsstring .= 'sb_snapshot_detatch();';
				    }else if ($sb_status['stage'] == 'snapshot_delete'){
					    $jsstring .= 'sb_snapshot_delete();';
				    }
                }

				?>
                <script>
                    <?php echo $jsstring; ?>
                </script>
				<?php
            }
			sb_progress_bar( 'snapshotbar' );
			sb_progress_bar( 'imagingbar' );
			sb_status_box( 'backupstatus' );

		}
	} else {
		sb_not_ready();
	}
