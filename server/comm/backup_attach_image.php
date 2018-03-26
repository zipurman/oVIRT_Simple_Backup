<?php

	$sb_status = sb_status_fetch();
	$status    = 0;
	$reason    = 'None';

	if ( $sb_status['status'] == 'backup' && $sb_status['stage'] == 'snapshot_attach' ) {

		if ( preg_match( $UUIDv4, $sb_status['setting1'] ) ) {

			if ( empty( $sb_status['setting3'] ) ) {

				$status = 0;
				$reason = 'Snapshot Not Found';

			} else if ( ! empty( $sb_status['setting4'] ) ) {

				$disks        = ovirt_rest_api_call( 'GET', 'vms/' . $sb_status['setting1'] . '/snapshots/' . $sb_status['setting3'] . '/disks' );
				$diskid       = $disks->disk['id'];
				$diskletter   = 'a';
				$morediskdata = ovirt_rest_api_call( 'GET', 'vms/' . $sb_status['setting1'] . '/diskattachments/' );
				$disktypeget  = sb_check_disks();
				$disktype     = $disktypeget['disktype'];
				$disknumber   = 1;
				$setdisk1     = 0;
				if ( $sb_status['setting3'] != '-XEN-' ) {
					//force boot disk to be adopted first regardless of how ovirt presents the list
					foreach ( $disks->disk as $disk ) {
						$morediskdatathis = array();
						foreach ( $morediskdata as $morediskdatum ) {
							if ( (string) $morediskdatum['id'] == (string) $disk['id'] ) {
								$morediskdatathis = $morediskdatum;
							}
						}
						if ( ! empty( $morediskdatathis ) && $setdisk1 == 0 && $morediskdatathis->bootable == 'true' ) {
							$diskletter = sb_next_drive_letter( $diskletter );
							sb_disk_file_write( 1, (string) $disk->name, $sb_status['setting1'], (string) $disk['id'], (string) $morediskdatathis->bootable, (string) $morediskdatathis->interface, (integer) $disk->provisioned_size, $disktype . $diskletter, (string) $sb_status['setting4'], $sb_status['setting2'] );
							$disknumber ++;
							$setdisk1 = 1;
						}
					}
					//non-boot disks
					foreach ( $disks->disk as $disk ) {
						$morediskdatathis = array();
						foreach ( $morediskdata as $morediskdatum ) {
							if ( (string) $morediskdatum['id'] == (string) $disk['id'] ) {
								$morediskdatathis = $morediskdatum;
							}
						}
						if ( ! empty( $morediskdatathis ) && $morediskdatathis->bootable == 'false' ) {
							$diskletter = sb_next_drive_letter( $diskletter );
							sb_disk_file_write( $disknumber, (string) $disk->name, $sb_status['setting1'], (string) $disk['id'], (string) $morediskdatathis->bootable, (string) $morediskdatathis->interface, (integer) $disk->provisioned_size, $disktype . $diskletter, (string) $sb_status['setting4'], $sb_status['setting2'] );
							$disknumber ++;
						}
					}
				}
				if ( $sb_status['setting3'] == '-XEN-' ) {
					$processdisks = sb_disk_array_fetch( $settings['mount_migrate'] );
				} else {
					$processdisks = sb_disk_array_fetch( $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] );
				}

				foreach ( $processdisks as $processdisk ) {

					sb_attach_disk( $processdisk['uuid'], $sb_status['setting3'], $processdisk['path'] );

				}

				$status = 1;
				$reason = 'Disk(s) Attached';
				sb_status_set( 'backup', 'backup_imaging', 1, $sb_status['setting1'], $sb_status['setting2'], $sb_status['setting3'] );

			} else {
				$status = 0;
				$reason = 'Unmatched UUID';
			}

		} else {
			$status = 0;
			$reason = 'Invalid UUID';

		}

		if ( empty( $status ) ) {
			sb_cache_set( $sb_status['setting1'], $sb_status['setting2'], 'Attaching Image Failure - ' . $reason, $sb_status['setting4'], 'write' );
		}
	}

	$jsonarray = array(
		"status" => $status,
		"reason" => $reason,
	);
	sb_log('Attaching Image - ' . $reason);

	echo json_encode( $jsonarray );