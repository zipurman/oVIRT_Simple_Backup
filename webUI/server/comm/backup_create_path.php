<?php

	$vmuuid       = varcheck( "vmuuid", '' );
	$snapshotname = varcheck( "snapshotname", '' );

	$status     = 0;
	$reason     = 'None';
	$snapshotid = '';

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

			if ( ! file_exists( $settings['mount_backups'] . '/' . $vm->name ) ) {
				exec( "mkdir " . $settings['mount_backups'] . '/' . $vm->name );
			}

			if ( ! file_exists( $settings['mount_backups'] . '/' . $vm->name . '/' . $vm['id'] ) ) {
				exec( "mkdir " . $settings['mount_backups'] . '/' . $vm->name . '/' . $vm['id'] );
			}

			if ( ! file_exists( $settings['mount_backups'] . '/' . $vm->name . '/' . $vm['id'] . '/' . $snapshotname ) ) {
				exec( "mkdir " . $settings['mount_backups'] . '/' . $vm->name . '/' . $vm['id'] . '/' . $snapshotname );
			}

			$dirokay = 0;
			if ( file_exists( $settings['mount_backups'] . '/' . $vm->name ) ) {
				if ( file_exists( $settings['mount_backups'] . '/' . $vm->name . '/' . $vm['id'] ) ) {
					if ( file_exists( $settings['mount_backups'] . '/' . $vm->name . '/' . $vm['id'] . '/' . $snapshotname ) ) {
						$dirokay = 1;

						$snapshotdata = ovirt_rest_api_call( 'GET', 'vms/' . $vmuuid . '/snapshots/' . $snapshotid, '', true );
						sb_cache_set( $vmuuid, $snapshotname, 'Creating Path', $vm->name, 'write' );
						if ( $xmlfile = fopen( $settings['mount_backups'] . '/' . $vm->name . '/' . $vm['id'] . '/' . $snapshotname . '/data.xml', "w" ) ) {
							fwrite( $xmlfile, $snapshotdata->initialization->configuration->data );
							fclose( $xmlfile );
						} else {
							$status = 0;
							$reason = 'Cannot Create data.xml - Check Permissions to ' . $settings['mount_backups'];
						}

					}
				}
			}

			if ( empty( $dirokay ) ) {
				$status = 0;
				$reason = 'Error Creating Backup Directories - Check Permissions to ' . $settings['mount_backups'];
			} else {
				$status = 1;
				$reason = 'Directories Available';
			}

		} else {
			$status = 0;
			$reason = 'Unmatched UUID';
		}

	} else {
		$status = 0;
		$reason = 'Invalid UUID';

	}

	if ( empty( $status ) ) {
		sb_cache_set( $vmuuid, $snapshotname, 'Create Paths Failure - ' . $reason, $vm->name, 'write' );
	}

	$jsonarray = array(
		"status"     => $status,
		"reason"     => $reason,
		"snapshotid" => $snapshotid,
	);

	echo json_encode( $jsonarray );