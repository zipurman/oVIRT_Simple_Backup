<?php

	$sb_status     = sb_status_fetch();
	$status        = 0;
	$reason        = 'None';
	$totaldisksize = 0;

	if ( $sb_status['status'] == 'backup' && $sb_status['stage'] == 'snapshot_attach' ) {

		if ( preg_match( $UUIDv4, $sb_status['setting1'] ) ) {

			if ( empty( $sb_status['setting3'] ) ) {

				$status = 0;
				$reason = 'Snapshot Not Found';

			} else if ( ! empty( $sb_status['setting4'] ) ) {

				$disks        = ovirt_rest_api_call( 'GET', 'vms/' . $sb_status['setting1'] . '/snapshots/' . $sb_status['setting3'] . '/disks' );
				$diskid       = $disks->disk['id'];
				if ($settings['firstbackupdisk'] == 'b') {
                    $diskletter = 'a';
                } else if ($settings['firstbackupdisk'] == 'c') {
                    $diskletter = 'b';
                } else if ($settings['firstbackupdisk'] == 'd') {
                    $diskletter = 'c';
                }
				$morediskdata = ovirt_rest_api_call( 'GET', 'vms/' . $sb_status['setting1'] . '/diskattachments/' );
				$disktypeget  = sb_check_disks();
				$disktype     = $disktypeget['disktype'];
				$disknumber   = 1;
				$diskarray    = array();

				//parse disk data
				//DISK 1 - boot disk
				foreach ( $disks->disk as $disk ) {
					foreach ( $morediskdata as $morediskdatum ) {
						if ( (string) $morediskdatum['id'] == (string) $disk['id'] ) {
							$morediskdatathis = $morediskdatum;

							if ( (string) $morediskdatathis->bootable == 'true' ) {
								$diskarray[ $disknumber ]                     = array();
								$diskarray[ $disknumber ]['bootable']         = $morediskdatathis->bootable;
								$diskarray[ $disknumber ]['interface']        = $morediskdatathis->interface;
								$diskarray[ $disknumber ]['provisioned_size'] = $disk->provisioned_size;
								$diskarray[ $disknumber ]['name']             = $disk->name;
								$diskarray[ $disknumber ]['id']               = $disk['id'];
								$diskarray[ $disknumber ]['disknumber']       = $disknumber;
								$disknumber ++;

							}

						}
					}

				}

				//REMAINING DISKS
				foreach ( $disks->disk as $disk ) {
					foreach ( $morediskdata as $morediskdatum ) {
						if ( (string) $morediskdatum['id'] == (string) $disk['id'] ) {
							$morediskdatathis = $morediskdatum;

							if ( (string) $morediskdatathis->bootable != 'true' ) {
								$diskarray[ $disknumber ]                     = array();
								$diskarray[ $disknumber ]['bootable']         = $morediskdatathis->bootable;
								$diskarray[ $disknumber ]['interface']        = $morediskdatathis->interface;
								$diskarray[ $disknumber ]['provisioned_size'] = $disk->provisioned_size;
								$diskarray[ $disknumber ]['name']             = $disk->name;
								$diskarray[ $disknumber ]['id']               = $disk['id'];
								$diskarray[ $disknumber ]['disknumber']       = $disknumber;
								$disknumber ++;
							}

						}
					}

				}

				foreach ( $diskarray as $disk ) {
					sb_attach_disk( $disk['id'], $sb_status['setting3'], '' );
					sb_log( 'Attach Disk ' . $disk['id'] );
				}

				sleep( 5 );

				if ( $sb_status['setting3'] != '-XEN-' ) {
					foreach ( $diskarray as $disk ) {

						$diskletter = sb_next_drive_letter( $diskletter );
						sb_disk_file_write( $disk['disknumber'], $disk['name'], $sb_status['setting1'], $disk['id'], $disk['bootable'], $disk['interface'], $disk['provisioned_size'], $disktype . $diskletter, (string) $sb_status['setting4'], $sb_status['setting2'] );
						sb_log( 'Disk Dat Write ' . $disk['name'] . ' - ' . $disk['id'] . ' - ' . $disk['bootable'] . ' - ' . $disk['interface'] . ' - ' . $disk['provisioned_size'] . ' - ' . $disktype . $diskletter );
						$totaldisksize += $disk['provisioned_size'];
					}
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
	sb_log( 'Attaching Image - ' . $reason );

	echo json_encode( $jsonarray );