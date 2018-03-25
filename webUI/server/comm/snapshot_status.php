<?php

	$vmuuid       = varcheck( "vmuuid", '' );

	$sb_status = sb_status_fetch();
	$status     = - 1;

	//check if this is a new snapshot
	if ( $sb_status['status'] == 'ready' ) {


		$vm   = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid );

		//create new snapshot

		$snapshotname = $settings['label'] . $thedatetime;

		sb_log('------------------------------------------------------');
		sb_log('Starting Backup ... ' . $vmuuid . ' ' . $vm->name);


		$xml  = '<snapshot><description>' . $snapshotname . '</description></snapshot>';

		$snap = ovirt_rest_api_call( 'POST', 'vms/' . $vm['id'] . '/snapshots', $xml );

		sb_status_set('backup', 'snapshot', 1, $vm['id'], $snapshotname);

		sb_cache_set( $vmuuid, '', 'Backup - Creating Snapshot', $vm->name, 'write', "?area=2&action=select&vm={$vmuuid}&recovery=2", "sb_check_snapshot_progress('{$vm['id']}', '{$snapshotname}', 10);" );

		sb_log('Creating Snapshot');


	} else if ($sb_status['status'] == 'backup' && $sb_status['stage'] == 'snapshot' ) {

		//check status of running snapshot

		$snapshots = ovirt_rest_api_call( 'GET', 'vms/' . $sb_status['setting1'] . '/snapshots' );

		$snapshotid = '';
		foreach ( $snapshots as $snapshot ) {
			if ( $snapshot->description == $sb_status['setting2'] ) {
				if ( $snapshot->snapshot_status == 'locked' ) {
					$status = 0;
					sb_status_set('backup', 'snapshot', 2, $sb_status['setting1'], $sb_status['setting2']);
				} else if ( $snapshot->snapshot_status == 'ok' ) {
					$status = 1;
					$vm   = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid );
					sb_status_set('backup', 'create_path', 1, $sb_status['setting1'], $sb_status['setting2'], $snapshot['id'],  $vm->name  );
				}
			}
		}

	} else {
		$status = 0;
	}

	$jsonarray = array(
		'status'     => $status,
	);


	sleep( 1 );

	echo json_encode( $jsonarray );