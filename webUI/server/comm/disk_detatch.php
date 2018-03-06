<?php

	$vmuuid   = varcheck( "vmuuid", '' );
	$diskuuid = varcheck( "diskuuid", '' );

	if ( $vmuuid == 'this' ) {
		$vmuuid = $settings['uuid_backup_engine'];
	}

	$status = 0;
	$reason = 'None';
	if ( ! preg_match( $UUIDv4, $vmuuid ) ) {
		$status = 0;
		$reason = 'Invalid VM UUID';
	} else if ( ! preg_match( $UUIDv4, $diskuuid ) ) {
		$status = 0;
		$reason = 'Invalid Disk UUID';
	} else {


		$extradiskdev = '';//needed any more?
		$diskletter   = 'b';
		$snap         = ovirt_rest_api_call( 'DELETE', 'vms/' . $vmuuid . '/diskattachments/' . $diskuuid );

		sb_cache_set( $vmuuid, $snapshotname, 'Detatching Image', $diskuuid, 'write' );
		sleep( 1 );
		$status = 1;
		$reason = 'Disk Detatched';

	}

	if ( empty( $status ) ) {
		sb_cache_set( $vmuuid, $snapshotname, 'Detatch Image Failure - ' . $reason, $diskuuid, 'write' );
	}

	$jsonarray = array(
		"status"     => $status,
		"reason"     => $reason,
		"snapshotid" => $snapshotid,
	);

	echo json_encode( $jsonarray );