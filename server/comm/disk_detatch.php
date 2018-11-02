<?php

	$sb_status = sb_status_fetch();

	$vmuuid = $settings['uuid_backup_engine'];

	$status = 0;
	$reason = 'None';

	if ( $sb_status['status'] == 'restore' && $sb_status['stage'] == 'disk_detatch' ) {

		exec( 'rm  ../cache/diskattachitems.dat' );

		$disks = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid . '/diskattachments/' );
		foreach ( $disks as $disk ) {
			if ( (string) $disk->bootable == 'false' ) {

				$diskdata = ovirt_rest_api_call( 'GET', 'disks/' . $disk['id'] );

				sb_log('Detatching Disk From oVirtSimpleBackupVM - ' . $disk['id']);

				$snap = ovirt_rest_api_call( 'DELETE', 'vms/' . $vmuuid . '/diskattachments/' . $disk['id'] );
				exec( 'echo  ' . $diskdata->name . ' ' . $disk['id'] . ' >> ../cache/diskattachitems.dat' );
			}
		}

		sb_cache_set( $vmuuid, '', 'Detatching Disks', $vmuuid, 'write' );
		sb_status_set( 'restore', 'disk_attach', 0 );

		sleep( 1 );

		$status = 1;
		$reason = 'Disk Detatched';

	}

	if ( empty( $status ) ) {
		sb_cache_set( $vmuuid, '', 'Detatch Image Failure - ' . $reason, $sb_status['setting4'], 'write' );
	}

	$jsonarray = array(
		"status" => $status,
		"reason" => $reason,
	);

	echo json_encode( $jsonarray );