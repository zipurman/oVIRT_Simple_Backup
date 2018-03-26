<?php

	$sb_status = sb_status_fetch();

	$status = 0;
	$reason = 'None';

	if ( $sb_status['status'] == 'backup' && $sb_status['stage'] == 'snapshot_delete' ) {



		if ( preg_match( $UUIDv4, $sb_status['setting1'] ) ) {


			if ( empty( $sb_status['setting3'] ) ) {

				$status = 0;
				$reason = 'Snapshot Not Found';

			} else if ( ! empty( $sb_status['setting4'] ) ) {

				$snap = ovirt_rest_api_call( 'DELETE', 'vms/' . $sb_status['setting1'] . '/snapshots/' . $sb_status['setting3'] );

				$status = 1;
				$reason = 'Deleting Snapshot';
				sb_cache_set( $sb_status['setting1'], $sb_status['setting2'], '', '', 'delete' );
				sb_status_set( 'ready', '', 0 );

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
			sb_cache_set( $sb_status['setting1'], $sb_status['setting2'], 'Delete Snapshot Failure - ' . $reason, $sb_status['setting4'], 'write' );
		}
	}
	
	$jsonarray = array(
		"status"     => $status,
		"reason"     => $reason,
		"snapshotid" => $sb_status['setting3'],
	);

	sb_log('Deleting Snapshot - ' . $reason);
	sb_log('Backup Completed -  ' . $sb_status['setting2']);


	echo json_encode( $jsonarray );