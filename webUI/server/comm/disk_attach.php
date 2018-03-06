<?php

	$vmuuid   = varcheck( "vmuuid", '' );
	$diskuuid = varcheck( "diskuuid", '' );

	if ( $vmuuid == 'this' ) {
		$vmuuid = $settings['uuid_backup_engine'];
	}

	sleep( 2 );//wait in case detatch of same disk not ready
	$status    = 0;
	$reason    = 'None';
	$newvmuuid = '';

	if ( ! preg_match( $UUIDv4, $diskuuid ) ) {

		$status = 0;
		$reason = 'Invalid Disk UUID ';

	} else if ( ! preg_match( $UUIDv4, $vmuuid ) ) {

		$status = 0;
		$reason = 'Invalid VM UUID';

	} else {

		$xml = '<disk_attachment>
				        <disk id="' . $diskuuid . '">
				        </disk>
				        <active>true</active>
				        <bootable>true</bootable>
				        <interface>' . $settings['drive_interface'] . '</interface>
				        </disk_attachment>';

		$attachdisk = ovirt_rest_api_call( 'POST', 'vms/' . $vmuuid . '/diskattachments/', $xml );

		//		showme( $attachdisk );

		sb_cache_set( $settings['uuid_backup_engine'], '', '', '', 'delete' );

		$status = 1;
		$reason = 'Disk Attached';

	}

	if ( empty( $status ) ) {
		sb_cache_set( $vmuuid, $snapshotname, 'Restore - Attach Disk to New VM Failure - ' . $reason, $diskname, 'write' );
	}

	$jsonarray = array(
		"status"    => $status,
		"reason"    => $reason,
		"newvmuuid" => $newvmuuid,
	);

	echo json_encode( $jsonarray );