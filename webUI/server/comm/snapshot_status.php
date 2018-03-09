<?php

	$vmuuid       = varcheck( "vmuuid", '' );
	$snapshotname = varcheck( "snapshotname", '' );

	$newsnapshot = varcheck( "newsnapshot", 0, "FILTER_VALIDATE_INT", 0 );
	if (!empty($newsnapshot)) {
		$vm   = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid );
		$xml  = '<snapshot><description>' . $snapshotname . '</description></snapshot>';
		$snap = ovirt_rest_api_call( 'POST', 'vms/' . $vm['id'] . '/snapshots', $xml );

		sb_cache_set( $vmuuid, '', 'Backup - Creating Snapshot', $vm->name, 'write', "?area=2&action=select&vm={$vmuuid}&recovery=2", "sb_check_snapshot_progress('{$vm['id']}', '{$snapshotname}', 10);" );
	}

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