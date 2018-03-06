<?php

	if ( $savestep == 1 ) {

		$backup_log         = varcheck( "backup_log", '' );
		$cluster            = varcheck( "cluster", '' );
		$drive_interface    = varcheck( "drive_interface", 0, "FILTER_VALIDATE_INT", 0 );
		$drive_type         = varcheck( "drive_type", 0, "FILTER_VALIDATE_INT", 0 );
		$increment_disks_yn = varcheck( "increment_disks_yn", 0, "FILTER_VALIDATE_INT", 0 );
		$retention          = varcheck( "retention", 0, "FILTER_VALIDATE_INT", 0 );
		$label              = varcheck( "label", '' );
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

		$drive_interface = ( $drive_interface == 0 ) ? 'virtio' : 'virtio_scsi';
		$drive_type      = ( $drive_type == 0 ) ? 'vd' : 'sd';

		if ( ! file_exists( '../config.php' ) ) {
			exec( 'echo "" > /var/www/html/config.php' );
		}

		$configfile = fopen( "../config.php", "w" ) or die( "Unable to open config file.<br/><br/>Check permissions on config.php!" );
		fwrite( $configfile, '<?php' . "\n" );
		fwrite( $configfile, '$settings = array(' . "\n" );
		fwrite( $configfile, '"vms_to_backup" => array(""),' . "\n" );
		fwrite( $configfile, '"label" => "' . $label . '",' . "\n" );
		fwrite( $configfile, '"uuid_backup_engine" => "' . $uuid_backup_engine . '",' . "\n" );
		fwrite( $configfile, '"ovirt_url" => "' . $ovirt_url . '",' . "\n" );
		fwrite( $configfile, '"ovirt_user" => "' . $ovirt_user . '",' . "\n" );
		fwrite( $configfile, '"ovirt_pass" => "' . $ovirt_pass . '",' . "\n" );
		fwrite( $configfile, '"mount_backups" => "' . $mount_backups . '",' . "\n" );
		fwrite( $configfile, '"drive_type" => "' . $drive_type . '",' . "\n" );
		fwrite( $configfile, '"drive_interface" => "' . $drive_interface . '",' . "\n" );
		fwrite( $configfile, '"increment_disks_yn" => ' . $increment_disks_yn . ',' . "\n" );
		fwrite( $configfile, '"backup_log" => "' . $backup_log . '",' . "\n" );
		fwrite( $configfile, '"email" => "' . $email . '",' . "\n" );
		fwrite( $configfile, '"retention" => ' . $retention . ',' . "\n" );
		fwrite( $configfile, '"storage_domain" => "' . $storage_domain . '",' . "\n" );
		fwrite( $configfile, '"cluster" => "' . $cluster . '",' . "\n" );
		fwrite( $configfile, '"mount_migrate" => "' . $mount_migrate . '",' . "\n" );
		fwrite( $configfile, '"xen_ip" => "' . $xen_ip . '",' . "\n" );
		fwrite( $configfile, '"xen_migrate_uuid" => "' . $xen_migrate_uuid . '",' . "\n" );
		fwrite( $configfile, '"xen_migrate_ip" => "' . $xen_migrate_ip . '",' . "\n" );
		fwrite( $configfile, ');' . "\n" );
		fclose( $configfile );

		$settings = array(
			"vms_to_backup"      => array( "" ),
			"label"              => $label,
			"uuid_backup_engine" => $uuid_backup_engine,
			"ovirt_url"          => $ovirt_url,
			"ovirt_user"         => $ovirt_user,
			"ovirt_pass"         => $ovirt_pass,
			"mount_backups"      => $mount_backups,
			"drive_type"         => $drive_type,
			"drive_interface"    => $drive_interface,
			"increment_disks_yn" => $increment_disks_yn,
			"backup_log"         => $backup_log,
			"email"              => $email,
			"retention"          => $retention,
			"storage_domain"     => $storage_domain,
			"cluster"            => $cluster,
			"mount_migrate"      => $mount_migrate,
			"xen_ip"             => $xen_ip,
			"xen_migrate_uuid"   => $xen_migrate_uuid,
			"xen_migrate_ip"     => $xen_migrate_ip,
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

	$rowdata = array(
		array(
			"text" => "UUID of Backup Engine VM:",
		),
		array(
			"text" => sb_input( array(
				'type'      => 'text',
				'name'      => 'uuid_backup_engine',
				'size'      => '36',
				'maxlength' => '36',
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
				'size'      => '36',
				'maxlength' => '36',
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
				'size'      => '36',
				'maxlength' => '36',
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
			"text" => "oVirt Engine User:",
		),
		array(
			"text" => sb_input( array(
				'type'      => 'password',
				'name'      => 'ovirt_pass',
				'size'      => '36',
				'maxlength' => '36',
				'value'     => $settings['ovirt_pass'],
			) ),
		),
		array(
			"text" => "Admin Password on oVirt Engine.",
		),
	);
	sb_table_row( $rowdata );

	$rowdata = array(
		array(
			"text" => "Path To Backups:",
		),
		array(
			"text" => sb_input( array(
				'type'      => 'text',
				'name'      => 'mount_backups',
				'size'      => '36',
				'maxlength' => '36',
				'value'     => $settings['mount_backups'],
			) ),
		),
		array(
			"text" => "Usually a NFS share mapped to a mount point.",
		),
	);
	sb_table_row( $rowdata );

	$rowdata = array(
		array(
			"text" => "Drive Type:",
		),
		array(
			"text" => sb_input( array(
				'type'  => 'select',
				'name'  => 'drive_type',
				'value' => $settings['drive_type'],
				'list'  => array(
					array( 'id' => '0', 'name' => 'vd (Recommended)', ),
					array( 'id' => '1', 'name' => 'sd', ),
				),
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
				'type'  => 'select',
				'name'  => 'drive_interface',
				'value' => $settings['drive_interface'],
				'list'  => array(
					array( 'id' => '0', 'name' => 'virtio (Recommended)', ),
					array( 'id' => '1', 'name' => 'virtio_scsi', ),
				),
			) ),
		),
		array(
			"text" => "Used to mount disks for imaging.",
		),
	);
	sb_table_row( $rowdata );

	$rowdata = array(
		array(
			"text" => "Increment Disks:",
		),
		array(
			"text" => sb_input( array(
				'type'  => 'select',
				'name'  => 'increment_disks_yn',
				'value' => $settings['increment_disks_yn'],
				'list'  => array(
					array( 'id' => '0', 'name' => 'No (Recommended)', ),
					array( 'id' => '1', 'name' => 'Yes', ),
				),
			) ),
		),
		array(
			"text" => "Yes if VM wont release disk devices after each backup.",
		),
	);
	sb_table_row( $rowdata );

	$rowdata = array(
		array(
			"text" => "Path To Backup Log:",
		),
		array(
			"text" => sb_input( array(
				'type'  => 'text',
				'name'  => 'backup_log',
				'size'  => '36',
				'value' => $settings['backup_log'],
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
				'type'  => 'text',
				'name'  => 'email',
				'size'  => '36',
				'value' => $settings['email'],
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
			) ),
		),
		array(
			"text" => "Number of backups to keep for each VM. (Automated Backups Only)",
		),
	);
	sb_table_row( $rowdata );

	$rowdata = array(
		array(
			"text" => "Storage Domain Name:",
		),
		array(
			"text" => sb_input( array(
				'type'  => 'text',
				'name'  => 'storage_domain',
				'size'  => '36',
				'value' => $settings['storage_domain'],
			) ),
		),
		array(
			"text" => "Name of Storage Domain on oVirt.",
		),
	);
	sb_table_row( $rowdata );

	$rowdata = array(
		array(
			"text" => "Cluster Name:",
		),
		array(
			"text" => sb_input( array(
				'type'  => 'text',
				'name'  => 'cluster',
				'size'  => '36',
				'value' => $settings['cluster'],
			) ),
		),
		array(
			"text" => "Name of Cluster on oVirt.",
		),
	);
	sb_table_row( $rowdata );

	$rowdata = array(
		array(
			"text" => "Path To Migrate Images:",
		),
		array(
			"text" => sb_input( array(
				'type'  => 'text',
				'name'  => 'mount_migrate',
				'size'  => '36',
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
			"text" => "Xen Server IP:",
		),
		array(
			"text" => sb_input( array(
				'type'  => 'text',
				'name'  => 'xen_ip',
				'size'  => '36',
				'value' => $settings['xen_ip'],
			) ),
		),
		array(
			"text" => "If this is set, you will have xen server migration options available.",
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
				'size'  => '36',
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
				'size'  => '36',
				'value' => $settings['xen_migrate_uuid'],
			) ),
		),
		array(
			"text" => "The UUID of the migration VM running in xen environment.",
		),
	);
	sb_table_row( $rowdata );

	$rowdata = array(
		array(
			"text" => "",
		),
		array(
			"text" => sb_input( array(
				'type'  => 'submit',
				'value' => 'Save Changes',
			) ),
		),
		array(
			"text" => "",
		),
	);
	sb_table_row( $rowdata );

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

	sb_table_end();
	sb_form_end();