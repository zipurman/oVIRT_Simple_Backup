<?php

	$sb_version = '0.5.3';

	$area     = varcheck( "area", 0, "FILTER_VALIDATE_INT", 0 );
	$savestep = varcheck( "savestep", 0, "FILTER_VALIDATE_INT", 0 );
	$disconnectdisks = varcheck( "disconnectdisks", 0, "FILTER_VALIDATE_INT", 0 );
	$recovery = varcheck( "recovery", 0, "FILTER_VALIDATE_INT", 0 );
	$action   = varcheck( "action", '' );
	$comm     = varcheck( "comm", '' );

	$UUIDv4  = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
	$UUIDxen = '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i';

	if ( $area == 1 ) {
		$areafile = 'automated';
	} else if ( $area == 2 ) {
		$areafile = 'backup';
	} else if ( $area == 3 ) {
		$areafile = 'restore';
	} else if ( $area == 4 ) {
		$areafile = 'migrate';
	} else if ( $area == 5 ) {
		$areafile = 'xen';
	} else if ( $area == 99 ) {
		$areafile = 'settings';
	} else {
		$areafile = 'home';
	}

	$thedatetime = strftime( "%Y%m%d_%H%M%S" );

	$allowsite = -1;
	if ( ! empty( $allowed_ips ) ) {
		if ( is_array( $allowed_ips ) ) {
			foreach ( $allowed_ips as $allowed_ip ) {
				$ip = $_SERVER['REMOTE_ADDR'];
				if ( preg_match( $allowed_ip['ip'], $ip ) ) {
					if ( ! $allowed_ip['allow'] ) {
						$allowsite = 0;
					} else {
						if (!empty($allowsite)) $allowsite = 1;
					}
				}

			}
			if ($allowsite == -1) $allowsite = 0;
		}

	}
	if ( empty( $allowsite ) ) {
		die( 'Access Denied ' .$_SERVER['REMOTE_ADDR'] );
	}

	if ($disconnectdisks == 1){
		$attacheddisks         = ovirt_rest_api_call( 'GET', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/' );
		foreach ( $attacheddisks as $attacheddisk ) {
			if ($attacheddisk->logical_name != '/dev/' . $settings['drive_type'] . 'a'){
				$attacheddisks         = ovirt_rest_api_call( 'DELETE', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/' .  $attacheddisk['id'] );
			}
		}
	}