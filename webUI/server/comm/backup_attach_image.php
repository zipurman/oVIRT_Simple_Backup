<?php

	$vmuuid       = varcheck( "vmuuid", '' );
	$snapshotname = varcheck( "snapshotname", '' );

	$status = 0;
	$reason = 'None';
	sleep( 2 );
	if ( empty( $snapshotname ) ) {
		$status = 0;
		$reason = 'Invalid Snapshot';
	} else if ( preg_match( $UUIDv4, $vmuuid ) ) {

		$vm = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid );

		$snapshots = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid . '/snapshots' );

		foreach ( $snapshots as $snapshot ) {
			if ( $snapshot->description == $snapshotname ) {
				$snapshotid = (string) $snapshot['id'];
			}
		}

		if ( empty( $snapshotid ) ) {

			$status = 0;
			$reason = 'Snapshot Not Found';

		} else if ( ! empty( $vm ) ) {

			$disks = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid . '/snapshots/' . $snapshotid . '/disks' );
			$diskid       = $disks->disk['id'];
			$extradiskdev = '';//needed any more?
			$diskletter   = 'b';

			$xml  = '<disk_attachment>
			        <disk id="' . $diskid . '">
			        <snapshot id="' . $snapshotid . '"/>
			        </disk>
			        <active>true</active>
			        <bootable>false</bootable>
			        <interface>' . $settings['drive_interface'] . '</interface>
			        <logical_name>/dev/' . $settings['drive_type'] . $extradiskdev . $diskletter . '</logical_name>
			        </disk_attachment>';
			$snap = ovirt_rest_api_call( 'POST', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/', $xml );
			sb_cache_set( $vmuuid, $snapshotname, 'Attaching Image', $vm->name, 'write' );
			$status = 1;
			$reason = 'Disk Attached';
		} else {
			$status = 0;
			$reason = 'Unmatched UUID';
		}

	} else {
		$status = 0;
		$reason = 'Invalid UUID';

	}

	if ( empty( $status ) ) {
		sb_cache_set( $vmuuid, $snapshotname, 'Attaching Image Failure - ' . $reason, $vm->name, 'write' );
	}

	$jsonarray = array(
		"status"     => $status,
		"reason"     => $reason,
		"snapshotid" => $snapshotid,
	);

	echo json_encode( $jsonarray );