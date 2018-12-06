<?php

	$sb_version = '0.6.29';
	$mediaverstion = '.101';

	$area            = varcheck( "area", 0, "FILTER_VALIDATE_INT", 0 );
	$savestep        = varcheck( "savestep", 0, "FILTER_VALIDATE_INT", 0 );
	$disconnectdisks = varcheck( "disconnectdisks", 0, "FILTER_VALIDATE_INT", 0 );
	$recovery        = varcheck( "recovery", 0, "FILTER_VALIDATE_INT", 0 );
	$action          = varcheck( "action", '' );
	$comm            = varcheck( "comm", '' );

	//used for xen migration tracking disk data
	$diskfile              = $projectpath . 'cache/xendisks.dat';
	$diskfile2             = $projectpath . 'cache/xendisks2.dat';
	$statusfile            = $projectpath . 'cache/statusfile.dat';
	$vmconfigfile          = $projectpath . '.automated_backups_vmlist';
	$vmbackupinprocessfile = $projectpath . '.automated_backups_inprocess';
	$lastversioncheckfile = $projectpath . '.versioncheck';
	$vmbackupemaillog = $projectpath . '.automated_backups_emaillog';

	//change the follow if you like. Used for password obscurity.
	$salt   = 'ch8g7e2g2g';
	$pepper = '27cge297ch2hUGJfg';
	$mykey  = '&*iv^Fv79';

//	$UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
    $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i';
	$UUIDxen = '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i';

	//version checking
	require( $projectpath . 'versioning.php' );

	if ( ! empty( $settings['tz'] ) ) {
		date_default_timezone_set( $settings['tz'] );
	}

	if ( $area == 1 ) {
		$areafile = 'scheduled';
	} else if ( $area == 2 ) {
		$areafile = 'backup';
	} else if ( $area == 3 ) {
		$areafile = 'restore';
	} else if ( $area == 4 ) {
		$areafile = 'migrate';
	} else if ( $area == 5 ) {
		$areafile = 'xen';
	} else if ( $area == 10 ) {
		$areafile = 'logs';
	} else if ( $area == 98 ) {
		$areafile = 'updates';
	} else if ( $area == 99 ) {
		$areafile = 'settings';
	} else {
		$areafile = 'home';
	}

	$thedatetime = strftime( "%Y%m%d_%H%M%S" );
	$thetimefull = strftime("%Y-%m-%d %H:%M:%S");

	if ( ! isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	}

	$allowsite = - 1;
	if ( ! empty( $allowed_ips ) ) {
		if ( is_array( $allowed_ips ) ) {
			foreach ( $allowed_ips as $allowed_ip ) {
				$ip = $_SERVER['REMOTE_ADDR'];
				if ( preg_match( $allowed_ip['ip'], $ip ) ) {
					if ( ! $allowed_ip['allow'] ) {
						$allowsite = 0;
					} else {
						if ( ! empty( $allowsite ) ) {
							$allowsite = 1;
						}
					}
				}
			}
			if ( $allowsite == - 1 ) {
				$allowsite = 0;
			}
		}

	}
	if ( empty( $allowsite ) ) {
		die( 'Access Denied ' . $_SERVER['REMOTE_ADDR'] .  ' Check your allowed_ips for oVirt Simple Backup.' );
	}

	if ( $disconnectdisks == 1 ) {
		$attacheddisks = ovirt_rest_api_call( 'GET', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/' );
		foreach ( $attacheddisks as $attacheddisk ) {
			if ( $attacheddisk->logical_name != '/dev/' . $settings['drive_type'] . 'a' ) {
				$attacheddisks = ovirt_rest_api_call( 'DELETE', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/' . $attacheddisk['id'] );
			}
		}
	}

	$extrasshsettings = ' -x -C ';