<?php

	$vmuuid       = varcheck( "vmuuid", '' );
	$snapshotname = varcheck( "snapshotname", '' );

	$status = 0;
	$reason = 'None';
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

			$snap = ovirt_rest_api_call( 'DELETE', 'vms/' . $vmuuid . '/snapshots/' . $snapshotid );

			$status = 1;
			$reason = 'Snapshot Deleted';
			sb_cache_set( $vmuuid, $snapshotname, '', '', 'delete' );

			sleep( 2 );

		} else {
			$status = 0;
			$reason = 'Unmatched UUID';
		}

	} else {
		$status = 0;
		$reason = 'Invalid UUID';

	}

	if ( empty( $status ) ) {
		sb_cache_set( $vmuuid, $snapshotname, 'Delete Snapshot Failure - ' . $reason, $vm->name, 'write' );
	}

	$jsonarray = array(
		"status"     => $status,
		"reason"     => $reason,
		"snapshotid" => $snapshotid,
	);

	echo json_encode( $jsonarray );