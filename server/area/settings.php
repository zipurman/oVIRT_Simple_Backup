<?php

	$configcheck        = varcheck( "configcheck", 0, "FILTER_VALIDATE_INT", 0 );
	$automatedinprocess = ( file_exists( $vmbackupinprocessfile ) ) ? 1 : 0;

	if ( ! empty( $automatedinprocess ) ) {
		echo 'Automated Backup In Process<br/><br/>';
	} else if ( ! empty( $configcheck ) ) {

		echo 'Connecting to Ovirt Engine...<br/><br/>';

		$vms = ovirt_rest_api_call( 'GET', 'vms' );
		echo '<b>VMs: ' . count( $vms ) . '</b><br/><br/>';
		foreach ( $vms as $vm ) {
			echo ' - ' . $vm->name . '<br/>';
		}
		echo '<br/><br/>';

		$clusters = sb_clusterlist();
		echo '<b>Clusters: ' . count( $clusters ) . '</b><br/><br/>';
		foreach ( $clusters as $cluster ) {
			echo ' - ' . $cluster['name'] . '<br/>';
		}
		echo '<br/><br/>';

		$domains = sb_domainlist();
		echo '<b>Domains: ' . count( $domains ) . '</b><br/><br/>';
		foreach ( $domains as $domain ) {
			echo ' - ' . $domain['name'] . '<br/>';
		}
		echo '<br/><br/>';

		$disks = ovirt_rest_api_call( 'GET', 'disks' );
		echo '<b>Disks: ' . count( $disks ) . '</b><br/><br/>';

		sb_pagedescription( '<a href="?area=99">Click Here</a> to return to settings.' );

	} else {
		sb_pagedescription( 'Note: If you lock your admin account out due to misconfigurations below, run the command (ovirt-aaa-jdbc-tool user unlock admin) on your oVirt Engine VM as root to unlock the admin account. A locked admin account or misconfigured account will make this UI very slow and options below for Domain and Cluster will be empty. <a href="?area=99&configcheck=1">Click Here</a> for an output of what we can connect to on oVirt Engine.' );

		if ( $savestep == 1 || empty( $settings ) ) {

			$backup_log         = varcheck( "backup_log", '' );
			$cluster            = varcheck( "cluster", '' );
			$drive_interface    = varcheck( "drive_interface", 0, "FILTER_VALIDATE_INT", 0 );
			$drive_type         = varcheck( "drive_type", 0, "FILTER_VALIDATE_INT", 0 );
			$retention          = varcheck( "retention", 0, "FILTER_VALIDATE_INT", 0 );
			$label              = varcheck( "label", '' );
			$tz                 = varcheck( "tz", 'America/Regina' );
			$mount_backups      = varcheck( "mount_backups", '' );
			$mount_migrate      = varcheck( "mount_migrate", '' );
			$ovirt_pass         = varcheck( "ovirt_pass", '' );
			$ovirt_url          = varcheck( "ovirt_url", '' );
			$ovirt_user         = varcheck( "ovirt_user", '' );
			$email              = varcheck( "email", '' );
			$storage_domain     = varcheck( "storage_domain", '' );
			$uuid_backup_engine = varcheck( "uuid_backup_engine", '' );
			$xen_migrate_uuid   = varcheck( "xen_migrate_uuid", '' );
			$xen_migrate_ip     = varcheck( "xen_migrate_ip", '' );
			$xen_ip             = varcheck( "xen_ip", '' );

			$restore_console     = varcheck( "restore_console", '' );
			$restore_os          = varcheck( "restore_os", '' );
			$restore_vm_type     = varcheck( "restore_vm_type", '' );
			$restore_cpu_sockets = varcheck( "restore_cpu_sockets", 0, "FILTER_VALIDATE_INT", 0 );
			$restore_cpu_cores   = varcheck( "restore_cpu_cores", 0, "FILTER_VALIDATE_INT", 0 );
			$restore_cpu_threads = varcheck( "restore_cpu_threads", 0, "FILTER_VALIDATE_INT", 0 );
			$compress            = varcheck( "compress", 0, "FILTER_VALIDATE_INT", 0 );


			$diskx   = sb_check_disks();
			$drive_type = $diskx['disktype'];
			$drive_interface = $diskx['driveinterface'];

			//make sure log file is valid if defined
			if ( ! empty( $backup_log ) ) {
				if ( strpos( $backup_log, '.log' ) == false ) {
					if ( substr( $backup_log, - 1 ) == '/' ) {
						$backup_log .= 'simplebackup.log';
					} else if ( substr( $backup_log, - 4, 4 ) != '.log' ) {
						$backup_log .= '_simplebackup.log';
					}
				}
			}

			$configfile = fopen( "../config.php", "w" ) or die( "Unable to open config file.<br/><br/>Check permissions on config.php!" );
			fwrite( $configfile, '<?php' . "\n" );
			fwrite( $configfile, '$settings = array(' . "\n" );
			fwrite( $configfile, '"vms_to_backup" => array(' );

			foreach ( $settings['vms_to_backup'] as $vmz ) {
				fwrite( $configfile, '"' . $vmz . '", ' );
			}
			fwrite( $configfile, '),' . "\n" );
			fwrite( $configfile, '"label" => "' . $label . '",' . "\n" );
			fwrite( $configfile, '"uuid_backup_engine" => "' . $uuid_backup_engine . '",' . "\n" );
			fwrite( $configfile, '"ovirt_url" => "' . $ovirt_url . '",' . "\n" );
			fwrite( $configfile, '"ovirt_user" => "' . $ovirt_user . '",' . "\n" );
			if ( $ovirt_pass != '********' && ! empty( $ovirt_pass ) ) {
				$passenc = sb_encrypt3( $ovirt_pass, $salt, $pepper, $mykey );
				fwrite( $configfile, '"ovirt_pass" => "' . $passenc . '",' . "\n" );
				$settings['ovirt_pass'] = $passenc;
			} else {
				fwrite( $configfile, '"ovirt_pass" => "' . $settings['ovirt_pass'] . '",' . "\n" );
			}
			fwrite( $configfile, '"mount_backups" => "' . $mount_backups . '",' . "\n" );
			fwrite( $configfile, '"drive_type" => "' . $drive_type . '",' . "\n" );
			fwrite( $configfile, '"drive_interface" => "' . $drive_interface . '",' . "\n" );
			fwrite( $configfile, '"backup_log" => "' . $backup_log . '",' . "\n" );
			fwrite( $configfile, '"email" => "' . $email . '",' . "\n" );
			fwrite( $configfile, '"retention" => ' . $retention . ',' . "\n" );
			fwrite( $configfile, '"storage_domain" => "' . $storage_domain . '",' . "\n" );
			fwrite( $configfile, '"cluster" => "' . $cluster . '",' . "\n" );
			fwrite( $configfile, '"mount_migrate" => "' . $mount_migrate . '",' . "\n" );
			fwrite( $configfile, '"xen_ip" => "' . $xen_ip . '",' . "\n" );
			fwrite( $configfile, '"xen_migrate_uuid" => "' . $xen_migrate_uuid . '",' . "\n" );
			fwrite( $configfile, '"xen_migrate_ip" => "' . $xen_migrate_ip . '",' . "\n" );
			fwrite( $configfile, '"restore_console" => "' . $restore_console . '",' . "\n" );
			fwrite( $configfile, '"restore_os" => "' . $restore_os . '",' . "\n" );
			fwrite( $configfile, '"restore_vm_type" => "' . $restore_vm_type . '",' . "\n" );
			fwrite( $configfile, '"restore_cpu_sockets" => "' . $restore_cpu_sockets . '",' . "\n" );
			fwrite( $configfile, '"restore_cpu_cores" => "' . $restore_cpu_cores . '",' . "\n" );
			fwrite( $configfile, '"restore_cpu_threads" => "' . $restore_cpu_threads . '",' . "\n" );
			fwrite( $configfile, '"tz" => "' . $tz . '",' . "\n" );
			fwrite( $configfile, '"compress" => "' . $compress . '",' . "\n" );
			fwrite( $configfile, ');' . "\n" );
			fclose( $configfile );

			$settings = array(
				"vms_to_backup"       => array( "" ),
				"label"               => $label,
				"uuid_backup_engine"  => $uuid_backup_engine,
				"ovirt_url"           => $ovirt_url,
				"ovirt_user"          => $ovirt_user,
				"ovirt_pass"          => $settings['ovirt_pass'],
				"mount_backups"       => $mount_backups,
				"drive_type"          => $drive_type,
				"drive_interface"     => $drive_interface,
				"backup_log"          => $backup_log,
				"email"               => $email,
				"retention"           => $retention,
				"storage_domain"      => $storage_domain,
				"cluster"             => $cluster,
				"mount_migrate"       => $mount_migrate,
				"xen_ip"              => $xen_ip,
				"xen_migrate_uuid"    => $xen_migrate_uuid,
				"xen_migrate_ip"      => $xen_migrate_ip,
				"restore_console"     => $restore_console,
				"restore_os"          => $restore_os,
				"restore_vm_type"     => $restore_vm_type,
				"restore_cpu_sockets" => $restore_cpu_sockets,
				"restore_cpu_cores"   => $restore_cpu_cores,
				"restore_cpu_threads" => $restore_cpu_threads,
				"tz"                  => $tz,
				"compress"     => $compress,
			);

		}

		sb_form_start();
		sb_table_start();

		$rowdata = array(
			array(
				"text"  => "Setting",
				"width" => "20%",
			),
			array(
				"text"  => "Value",
				"width" => "30%",
			),
			array(
				"text"  => "Description",
				"width" => "50%",
			),
		);
		sb_table_heading( $rowdata );

		if ( empty( $allowed_ips ) ) {
			$rowdata = array(
				array(
					"text" => "Warning(s):",
				),
				array(
					"text" => '<span style="font-weight: bold; color: red;">/var/www/html/allowed_ips.php is set to allow any connection to this script. It is recommended that you edit that file and adjust accordingly.</span>',
				),
				array(
					"text" => "",
				),
			);
			sb_table_row( $rowdata );
		}

		if ( ! empty( $settings['uuid_backup_engine'] ) ) {

			$rowdata = array(
				array(
					"text" => "Timezone:",
				),
				array(
					"text" => sb_input( array(
						'type'  => 'select',
						'name'  => 'tz',
						'value' => $settings['tz'],
						'list'  => $tzlist,
					) ),
				),
				array(
					"text" => "This text will be prepended to all backup files.",
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array(
					"text" => "Pre Label Backup Files:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'label',
						'size'      => '10',
						'maxlength' => '10',
						'value'     => $settings['label'],
					) ),
				),
				array(
					"text" => "This text will be prepended to all backup files.",
				),
			);
			sb_table_row( $rowdata );

		}

		$rowdata = array(
			array(
				"text" => "UUID of Backup Engine VM:",
			),
			array(
				"text" => sb_input( array(
					'type'      => 'text',
					'name'      => 'uuid_backup_engine',
					'size'      => '40',
					'maxlength' => '40',
					'value'     => $settings['uuid_backup_engine'],
				) ),
			),
			array(
				"text" => "Identifies the VM running these scripts.",
			),
		);
		sb_table_row( $rowdata );

		$rowdata = array(
			array(
				"text" => "FQDN of oVirt Engine:",
			),
			array(
				"text" => sb_input( array(
					'type'      => 'text',
					'name'      => 'ovirt_url',
					'size'      => '40',
					'maxlength' => '40',
					'value'     => $settings['ovirt_url'],
				) ),
			),
			array(
				"text" => "Identifies the oVirt Engine.",
			),
		);
		sb_table_row( $rowdata );

		$rowdata = array(
			array(
				"text" => "oVirt Engine User:",
			),
			array(
				"text" => sb_input( array(
					'type'      => 'text',
					'name'      => 'ovirt_user',
					'size'      => '40',
					'maxlength' => '40',
					'value'     => $settings['ovirt_user'],
				) ),
			),
			array(
				"text" => "Admin User on oVirt Engine.",
			),
		);
		sb_table_row( $rowdata );

		$rowdata = array(
			array(
				"text" => "oVirt Engine Password:",
			),
			array(
				"text" => sb_input( array(
					'type'      => 'password',
					'name'      => 'ovirt_pass',
					'size'      => '40',
					'maxlength' => '40',
					'value'     => '********',
				) ),
			),
			array(
				"text" => "Admin Password on oVirt Engine.",
			),
		);
		sb_table_row( $rowdata );

		if ( ! empty( $settings['uuid_backup_engine'] ) ) {
			$rowdata = array(
				array(
					"text" => "Path To Backups:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'mount_backups',
						'size'      => '40',
						'maxlength' => '40',
						'value'     => $settings['mount_backups'],
					) ),
				),
				array(
					"text" => "Usually a NFS share mapped to a mount point.",
				),
			);
			sb_table_row( $rowdata );

			$diskx   = sb_check_disks();
			$rowdata = array(
				array(
					"text" => "Drive Type:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'hidden',
						'name'      => 'drive_type',
						'value'     => $diskx['disktype'],
						'dataafter' => 'Auto detected as: ' . $diskx['disktype'],
					) ),
				),
				array(
					"text" => "Used to mount disks for imaging.",
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array(
					"text" => "Drive Interface:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'hidden',
						'name'      => 'drive_interface',
						'value'     => $diskx['driveinterface'],
						'dataafter' => 'Auto detected as: ' . $diskx['driveinterface'],
					) ),
				),
				array(
					"text" => "Used to mount disks for imaging.",
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array(
					"text" => "Path To Backup Log:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'backup_log',
						'size'      => '40',
						'value'     => $settings['backup_log'],
						'dataafter' => '',
					) ),
				),
				array(
					"text" => "Path to backup log.",
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array(
					"text" => "Email:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'email',
						'size'      => '40',
						'value'     => $settings['email'],
						'dataafter' => '',
					) ),
				),
				array(
					"text" => "Email for alerts.",
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array(
					"text" => "Retention:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'retention',
						'size'      => '3',
						'maxlength' => '3',
						'value'     => $settings['retention'],
						'dataafter' => '',
					) ),
				),
				array(
					"text" => "Number of backups to keep for each VM. (Scheduled Backups Only)",
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array(
					"text" => "Backup Compression:",
				),
				array(
					"text" => sb_input( array(
						'type'  => 'select',
						'name'  => 'compress',
						'list'  => array(
							array('id'=>'0', 'name'=>'None',),
							array('id'=>'1', 'name'=>'gzip',),
							array('id'=>'2', 'name'=>'lzo',),
						),
						'value' => $settings['compress'],
					) ),
				),
				array(
					"text" => "",
				),
			);
			sb_table_row( $rowdata );


			sb_table_end();

			echo '<br/><h3>Xen Migration Settings</h3>';
			sb_table_start();

			$rowdata = array(
				array(
					"text"  => "",
					"width" => "20%",
				),
				array(
					"text"  => "",
					"width" => "30%",
				),
				array(
					"text"  => "",
					"width" => "50%",
				),
			);
			sb_table_heading( $rowdata );

			$rowdata = array(
				array(
					"text" => "Xen Server IP:",
				),
				array(
					"text" => sb_input( array(
						'type'  => 'text',
						'name'  => 'xen_ip',
						'size'  => '40',
						'value' => $settings['xen_ip'],
					) ),
				),
				array(
					"text" => "If this is set, you will have xen server migration options available.",
				),
			);
			sb_table_row( $rowdata );

			if ( ! empty( $settings['xen_ip'] ) ) {

				$rowdata = array(
					array(
						"text" => "Path To Migrate Images:",
					),
					array(
						"text" => sb_input( array(
							'type'  => 'text',
							'name'  => 'mount_migrate',
							'size'  => '40',
							'value' => $settings['mount_migrate'],
						) ),
					),
					array(
						"text" => "Usually a NFS share mapped to a mount point for RAW migration images from Xen etc.",
					),
				);
				sb_table_row( $rowdata );

				$rowdata = array(
					array(
						"text" => "Xen Migration VM IP:",
					),
					array(
						"text" => sb_input( array(
							'type'  => 'text',
							'name'  => 'xen_migrate_ip',
							'size'  => '40',
							'value' => $settings['xen_migrate_ip'],
						) ),
					),
					array(
						"text" => "The IP of the migration VM running in xen environment.",
					),
				);
				sb_table_row( $rowdata );

				$rowdata = array(
					array(
						"text" => "Xen Migration VM UUID:",
					),
					array(
						"text" => sb_input( array(
							'type'  => 'text',
							'name'  => 'xen_migrate_uuid',
							'size'  => '40',
							'value' => $settings['xen_migrate_uuid'],
						) ),
					),
					array(
						"text" => "The UUID of the migration VM running in xen environment.",
					),
				);
				sb_table_row( $rowdata );
			}

			sb_table_end();

			echo '<br/><h3>Default Restore Options</h3>';
			sb_table_start();
			$rowdata = array(
				array(
					"text"  => "",
					"width" => "20%",
				),
				array(
					"text"  => "",
					"width" => "30%",
				),
				array(
					"text"  => "",
					"width" => "50%",
				),
			);
			sb_table_heading( $rowdata );

			$oslist   = sb_oslist();
			$consoles = sb_consolelist();

			$rowdata = array(
				array(
					"text" => "Console:",
				),
				array(
					"text" => sb_input( array(
						'type'  => 'select',
						'name'  => 'restore_console',
						'list'  => $consoles,
						'value' => $settings['restore_console'],
					) ),
				),
				array(
					"text" => "",
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array(
					"text" => "OS:",
				),
				array(
					"text" => sb_input( array(
						'type'  => 'select',
						'name'  => 'restore_os',
						'list'  => $oslist,
						'value' => $settings['restore_os'],
					) ),
				),
				array(
					"text" => "",
				),
			);
			sb_table_row( $rowdata );

			$clusters = sb_clusterlist();
			$domains  = sb_domainlist();
			$rowdata  = array(
				array(
					"text" => "Domain:",
				),
				array(
					"text" => sb_input( array(
						'type'  => 'select',
						'name'  => 'storage_domain',
						'list'  => $domains,
						'value' => $settings['storage_domain'],
					) ),
				),
				array(
					"text" => "",
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array(
					"text" => "Cluster:",
				),
				array(
					"text" => sb_input( array(
						'type'  => 'select',
						'name'  => 'cluster',
						'list'  => $clusters,
						'value' => $settings['cluster'],
					) ),
				),
				array(
					"text" => "",
				),
			);
			sb_table_row( $rowdata );

			$vmtypes = sb_vmtypelist();
			$rowdata = array(
				array(
					"text" => "VM Type:",
				),
				array(
					"text" => sb_input( array(
						'type'  => 'select',
						'name'  => 'restore_vm_type',
						'list'  => $vmtypes,
						'value' => $settings['restore_vm_type'],
					) ),
				),
				array(
					"text" => "",
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array(
					"text" => "CPU Sockets:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'restore_cpu_sockets',
						'size'      => '3',
						'maxlength' => '3',
						'value'     => $settings['restore_cpu_sockets'],
					) ),
				),
				array(
					"text" => "",
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array(
					"text" => "CPU Cores:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'restore_cpu_cores',
						'size'      => '3',
						'maxlength' => '3',
						'value'     => $settings['restore_cpu_cores'],
					) ),
				),
				array(
					"text" => "",
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array(
					"text" => "CPU Threads:",
				),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'restore_cpu_threads',
						'size'      => '3',
						'maxlength' => '3',
						'value'     => $settings['restore_cpu_threads'],
					) ),
				),
				array(
					"text" => "",
				),
			);
			sb_table_row( $rowdata );

		}

		sb_table_end();

		echo '<br/><br/>' . sb_input( array(
				'type'  => 'submit',
				'value' => 'Save Changes',
			) );

		echo sb_input( array(
			'type'  => 'hidden',
			'name'  => 'area',
			'value' => '99',
		) );

		echo sb_input( array(
			'type'  => 'hidden',
			'name'  => 'savestep',
			'value' => '1',
		) );

		sb_form_end();
	}