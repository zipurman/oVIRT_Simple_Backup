<?php

	sb_pagetitle( 'Single Backup' );

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

			$rowdata = array(
				array(
					"text" => $rowlink,
				),
				array(
					"text" => $status,
				),
				array(
					"text" => $vm->memory / 1024 / 1024 / 1024 . 'GB',
				),
				array(
					"text" => $disk->actual_size / 1024 / 1024 / 1024 . 'GB',
				),
				array(
					"text" => $vm['id'],
				),
			);
			sb_table_row( $rowdata );

		}

		sb_table_end();
	} else if ( $action == 'select' ) {

		$vmuuid    = varcheck( "vm", '' );
		$backupnow = varcheck( "backupnow", '' );

		$vm = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid );

		$disks = ovirt_rest_api_call( 'GET', 'vms/' . $vm['id'] . '/diskattachments' );

		$disk = ovirt_rest_api_call( 'GET', 'disks/' . $disks->disk_attachment['id'] );

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
				"text" => "Disk:",
			),
			array(
				"text" => $disk->actual_size / 1024 / 1024 / 1024 . 'GB',
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

		if ( empty( $backupnow ) ) {
			sb_gobutton( 'Backup This VM Now', '', 'checkBackupNow();' );
			?>
            <script>
                function checkBackupNow() {
                    if (confirm('Backup this VM Now?')) {
                        window.location = '?area=2&action=select&backupnow=1&vm=<?php echo $vm['id'];?>';
                    }
                }
            </script>
			<?php
		} else {


			$xml  = '<snapshot><description>' . $settings['label'] . $thedatetime . '</description></snapshot>';
			$snap = ovirt_rest_api_call( 'POST', 'vms/' . $vm['id'] . '/snapshots', $xml );
			sb_cache_set( $vmuuid, '', 'Backup - Creating Snapshot', $vm->name, 'write' );

			?>
            <script>
                //start checking for status of snapshot
                sb_update_statusbox('backupstatus', 'Starting Backup ...');
                sb_check_snapshot_progress('<?php echo $vm['id']; ?>', '<?php echo $settings['label'] . $thedatetime; ?>', 0);
            </script>
			<?php

			sb_progress_bar( 'snapshotbar' );
			sb_progress_bar( 'imagingbar' );

			sb_status_box( 'backupstatus' );

		}

	}
