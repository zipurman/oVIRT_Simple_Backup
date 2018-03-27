<?php

	$sb_status = sb_status_fetch();

	$vmuuid = $sb_status['setting9'];

	sleep( 2 );//wait in case detatch of same disk not ready
	$status    = 0;
	$reason    = 'None';
	$newvmuuid = '';

	if ( $sb_status['status'] == 'restore' && $sb_status['stage'] == 'disk_attach' ) {

		if ( $sb_status['setting3'] == '-XEN-' ) {
			$filepath = $settings['mount_migrate'] . '/';
		} else {
			$filepath = $settings['mount_backups'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $sb_status['setting3'] . '/';
		}
		$processdisks = sb_disk_array_fetch( $filepath );
		$bootabledisk = 0;

		foreach ( $processdisks as $processdisk ) {
			if ( (string) $processdisk['bootable'] == (string) 'true' ) {
				$disknumber = str_replace( 'Disk', '', $processdisk['disknumber'] );
				sb_log( 'BOOTABLE - ' . $disknumber );
			}
		}

		$attachments     = file_get_contents( $projectpath . 'cache/diskattachitems.dat' );
		$attachments     = explode( "\n", $attachments );
		$disknumbercheck = 1;
		foreach ( $attachments as $attachment ) {
			if ( ! empty( $attachment ) ) {

				$attachmentdata = explode( " ", $attachment );

				$bootable = ( strpos( $attachmentdata[0], 'Disk' . $disknumber ) !== false ) ? 'true' : 'false';

				sb_log( '-- DISK ATTACH -- ' . $disknumbercheck . ' bootable: ' . $bootable );

				$xml = '<disk_attachment>
				        <disk id="' . $attachmentdata[1] . '">
				        </disk>
				        <active>true</active>
				        <bootable>' . $bootable . '</bootable>
				        <interface>' . $settings['drive_interface'] . '</interface>
				        </disk_attachment>';

				sb_log( $xml );

				$attachdisk = ovirt_rest_api_call( 'POST', 'vms/' . $vmuuid . '/diskattachments/', $xml );
				$disknumbercheck ++;
			}
		}

		sb_cache_set( $settings['uuid_backup_engine'], '', '', '', 'delete' );
		sb_status_set( 'ready', '', 0 );
		sb_log( 'VM Restore Completed' );

		$status = 1;
		$reason = 'Disk Attached';

	}

	if ( empty( $status ) ) {
		sb_cache_set( $vmuuid, $snapshotname, 'Restore - Attach Disk to New VM Failure - ' . $reason, $vmuuid, 'write' );
	}

	$jsonarray = array(
		"status" => $status,
		"reason" => $reason,
	);


	echo json_encode( $jsonarray );