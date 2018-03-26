<?php

	die( 'Turned Off' );
	$vmuuid = varcheck( "vmuuid", '' );
	$option = varcheck( "option", '' );
	$ovalue = varcheck( "ovalue", '' );

	$status = 0;
	$reason = 'None';
	if ( ! preg_match( $UUIDv4, $vmuuid ) ) {
		$status = 0;
		$reason = 'Invalid VM UUID';
	} else {

		$xml   = '<vm><' . $option . '>' . $ovalue . '</' . $option . '></vm>';
		$newvm = ovirt_rest_api_call( 'PUT', 'vms/' . $vmuuid, $xml );

		sb_cache_set( $vmuuid, $snapshotname, 'Updating ' . $option, $vmuuid, 'write' );
		sleep( 1 );
		$status = 1;
		$reason = 'Option ' . $option . ' Set To:' . $ovalue;

	}

	if ( empty( $status ) ) {
		sb_cache_set( $vmuuid, $snapshotname, 'Option ' . $option . ' Set Failure - ' . $reason, $diskuuid, 'write' );
	}

	$jsonarray = array(
		"status" => $status,
		"reason" => $reason,
	);

	echo json_encode( $jsonarray );