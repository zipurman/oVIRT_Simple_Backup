<?php

	$vmuuid       = varcheck( "vmuuid", '' );
	$snapshotname = varcheck( "snapshotname", '' );

	$snapshots = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid . '/snapshots' );

	$status     = - 1;
	$snapshotid = '';
	foreach ( $snapshots as $snapshot ) {
		if ( $snapshot->description == $snapshotname ) {
			if ( $snapshot->snapshot_status == 'locked' ) {
				$status = 0;
			} else if ( $snapshot->snapshot_status == 'ok' ) {
				$status = 1;
			}
			$snapshotid = $snapshot['id'];
		}
	}

	$jsonarray = array(
		'status'     => $status,
		'snapshotid' => "{$snapshotid}",
	);
	sleep( 1 );
	echo json_encode( $jsonarray );