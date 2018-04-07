<?php

	$sb_status = sb_status_fetch();

	if ( ! file_exists( $vmconfigfile ) ) {
		exec( 'echo "" > ' . $vmconfigfile );
	}

	sb_pagetitle( 'Scheduled Backups' );

	if ( $sb_status['status'] == 'ready' ) {
		if ( $action == 3 ) {
			//delete
			$schedname = varcheck( "schedname", '' );
			if ( strpos( " {$schedname}", '..' ) == false ) {
				if ( strpos( " {$schedname}", $projectpath ) !== false ) {
					unlink( $schedname );
				}
			}
			$action = 0;

		} else if ( $action == 2 ) {

			$vm_items        = varcheck( "vm_items", '' );
			$startdate       = varcheck( "startdate", '' );
			$enddate         = varcheck( "enddate", '' );
			$starthour       = varcheck( "starthour", '' );
			$startminutes    = varcheck( "startminutes", '' );
			$days            = varcheck( "days", '' );
			$numday          = varcheck( "numday", '' );
			$monthday        = varcheck( "monthday", '' );
			$schedulename    = varcheck( "schedulename", '' );
			$oldschedulename = varcheck( "oldschedulename", '' );
			$starttime       = $starthour . ':' . $startminutes;

			if ( ! empty( $oldschedulename ) ) {
				$oldschedulename = preg_replace( '/[^0-9a-zA-Z\-_]/i', '_', $oldschedulename );
				//if renaming file - delete old
				if ( file_exists( $projectpath . '.automated_backups_schedule_' . $oldschedulename ) ) {
					unlink( $projectpath . '.automated_backups_schedule_' . $oldschedulename );
				}
			}

			if ( is_array( $days ) ) {
				$daytext = '';
				foreach ( $days as $day ) {
					$daytext .= $day;
				}
			} else {
				$daytext = '';
			}

			if ( ! is_array( $vm_items ) ) {
				$vm_items = array();
			}

			$newfiletext = '';
			foreach ( $vm_items as $vm_item ) {
				$newfiletext .= '[' . $vm_item . ']';
			}

			sb_schedule_write( $startdate, $enddate, $daytext, $numday, $monthday, $schedulename, $newfiletext, $starttime );

			$action = 0;
		}

		if ( empty( $action ) ) {

			sb_gobutton( 'New Schedule', '?area=1&action=editschedule', '' );

			sb_table_start();

			$rowdata = array(
				array( "text" => "Scheduled Backup Name", "width" => "30%", ),
				array( "text" => "Start Date", "width" => "10%", ),
				array( "text" => "Start Time", "width" => "10%", ),
				array( "text" => "End Date", "width" => "10%", ),
				array( "text" => "DOW", "width" => "5%", ),
				array( "text" => "Day", "width" => "5%", ),
				array( "text" => "DOM", "width" => "5%", ),
				array( "text" => "VMS", "width" => "25%", ),
			);
			sb_table_heading( $rowdata );

			$files = null;
			exec( 'ls ' . $projectpath . '.automated_backups_schedule_*', $files );

			foreach ( $files as $file ) {

				if ( strpos( $file, '.swp' ) == false ) {

					$filedata = sb_schedule_fetch( $file );

					$daytext = ( $filedata['numday'] > 0 ) ? $filedata['numday'] . ordinal_suffix( $filedata['numday'] ) : '';
					$domtext = ( $filedata['dom'] > 0 ) ? $filedata['dom'] . ordinal_suffix( $filedata['dom'] ) : '';
					if ( $filedata['dom'] == 32 ) {
						$domtext = 'Last';
					}
					$rowdata = array(
						array( "text" => '<a href="?area=1&action=editschedule&schedname=' . $file . '">' . str_replace( '_', ' ', $filedata['schedulename'] ) . '</a>', ),
						array( "text" => $filedata['startdatetime'], ),
						array( "text" => $filedata['starttime'], ),
						array( "text" => $filedata['enddatetime'], ),
						array( "text" => $filedata['days'], ),
						array( "text" => $daytext, ),
						array( "text" => $domtext, ),
						array( "text" => $filedata['vmstobackup'], ),
					);
					sb_table_row( $rowdata );
				}
			}

			sb_table_end();

		} else if ( $action == 'editschedule' ) {


			sb_form_start();
			sb_table_start();

			$schedname = varcheck( "schedname", '' );

			if ( empty( $schedname ) ) {
				$schedulename  = '';
				$startdatetime = $thetimefull;
				$enddatetime   = $thetimefull;
				$daysofweek    = '';
				$numday        = '0';
				$monthday      = '0';
				$vmstobackup   = '';
				$starttime     = '00:00';
			} else {
				$filedata      = sb_schedule_fetch( $schedname );
				$schedulename  = str_replace( '_', ' ', $filedata['schedulename'] );
				$startdatetime = $filedata['startdatetime'];
				$enddatetime   = $filedata['enddatetime'];
				$daysofweek    = $filedata['days'];
				$numday        = $filedata['numday'];
				$monthday      = $filedata['dom'];
				$vmstobackup   = $filedata['vmstobackup'];
				$starttime     = $filedata['starttime'];

			}

			$rowdata = array(
				array( "text" => "", "width" => "20%", ),
				array( "text" => "", "width" => "30%", ),
				array( "text" => "", "width" => "20%", ),
				array( "text" => "", "width" => "30%", ),
			);
			sb_table_heading( $rowdata );

			$rowdata = array(
				array(
					"text" => "Schedule Name:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'schedulename',
						'size'      => '20',
						'maxlength' => '20',
						'value'     => $schedulename,
					) ),
				),
				array( "text" => "", ),
				array( "text" => "", ),

			);
			sb_table_row( $rowdata );

			$startdate = odbc_date_format( $startdatetime );
			$enddate   = odbc_date_format( $enddatetime );
			$rowdata   = array(
				array(
					"text" => "Start Date:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'startdate',
						'size'      => '12',
						'maxlength' => '12',
						'value'     => $startdate,
					) ),
				),
				array( "text" => "End Date:", ),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'enddate',
						'size'      => '12',
						'maxlength' => '12',
						'value'     => $enddate,
					) ),
				),

			);
			sb_table_row( $rowdata );

			$days      = '';
			$daysarray = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
			for ( $i = 0; $i < 7; $i ++ ) {

				$days .= sb_input( array(
						'type'    => 'checkbox',
						'name'    => 'days[]',
						'value'   => $i,
						'checked' => ( strpos( " {$daysofweek}", "{$i}" ) !== false ) ? true : false,
					) ) . $daysarray[ $i ] . ' ';
			}

			$starttime_h = substr( $starttime, 0, 2 );
			$starttime_m = substr( $starttime, - 2 );

			$timehours = array();
			for ( $i = 0; $i < 24; $i ++ ) {
				$timehours[] = array(
					"id"   => str_pad( $i, 2, '0', STR_PAD_LEFT ),
					'name' => str_pad( $i, 2, '0', STR_PAD_LEFT ),
				);
			}
			$starthour = sb_input( array(
				'type'  => 'select',
				'name'  => 'starthour',
				'value' => $starttime_h,
				'list'  => $timehours,
			) );

			$timeminutes = array();
			for ( $i = 0; $i < 60; $i ++ ) {
				$timeminutes[] = array(
					"id"   => str_pad( $i, 2, '0', STR_PAD_LEFT ),
					'name' => str_pad( $i, 2, '0', STR_PAD_LEFT ),
				);
			}
			$startminutes = sb_input( array(
				'type'  => 'select',
				'name'  => 'startminutes',
				'value' => $starttime_m,
				'list'  => $timeminutes,
			) );

			$rowdata = array(
				array(
					"text" => "Backup Start Time:",
				),
				array(
					"text" => "{$starthour}:{$startminutes}",
				),
				array( "text" => "Days:", ),
				array( "text" => $days, ),

			);
			sb_table_row( $rowdata );

			//ordinal_suffix
			$everydays   = array();
			$everydays[] = array( "id" => '0', 'name' => 'N/A', );
			for ( $i = 1; $i < 5; $i ++ ) {
				$everydays[] = array( "id" => $i, 'name' => $i . ordinal_suffix( $i ), );
			}

			$everydays2   = array();
			$everydays2[] = array( "id" => '0', 'name' => 'N/A', );
			for ( $i = 1; $i < 32; $i ++ ) {
				$everydays2[] = array( "id" => $i, 'name' => $i . ordinal_suffix( $i ), );
			}
			$everydays2[] = array( "id" => '32', 'name' => 'Last Day', );
			$rowdata      = array(
				array(
					"text" => "Every:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'select',
						'name'      => 'numday',
						'value'     => $numday,
						'list'      => $everydays,
						'dataafter' => ' (Days) of the month.',
					) ),
				),
				array(
					"text" => "Day of the month:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'select',
						'name'      => 'monthday',
						'value'     => $monthday,
						'list'      => $everydays2,
						'dataafter' => ' of the month.',
					) ),
				),

			);
			sb_table_row( $rowdata );

			sb_table_end();

			?>
            <script>
                $(function () {

                    $("#startdate").datepicker({
                        defaultDate: "+1w",
                        changeMonth: true,
                        changeYear: true,
                        numberOfMonths: 2,
                        showButtonPanel: true,
                        onClose: function (selectedDate) {
                            $("#enddate").datepicker("option", "minDate", selectedDate);
                        }
                    });
                    $("#enddate").datepicker({
                        defaultDate: "+1w",
                        changeMonth: true,
                        changeYear: true,
                        numberOfMonths: 2,
                        showButtonPanel: true,
                        onClose: function (selectedDate) {
                            $("#startdate").datepicker("option", "maxDate", selectedDate);
                        }
                    });

                });

            </script>
			<?php

			//			$configdata = file_get_contents( $vmconfigfile );
			$configdata = $vmstobackup;

			sb_pagedescription( 'Select which VMs should be included in the scheduled backup.' );

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
				'name'  => 'oldschedulename',
				'value' => $schedulename,
			) );

			echo sb_input( array(
				'type'  => 'hidden',
				'name'  => 'action',
				'value' => '2',
			) );

			sb_form_end();

			if ( ! empty( $schedname ) ) {
				echo '<div align="right">';
				sb_gobutton( 'Delete Schedule', '', 'if (confirm(\'Delete This Schedule?\')){window.location=\'?area=1&action=3&schedname=' . $schedname . '\'};' );
				echo '</div>';
			}

		}
	} else {
		sb_not_ready();
	}