<?php

	$xenuuid = varcheck( "xenuuid", '' );
	$xstatus = varcheck( "xstatus", 0, "FILTER_VALIDATE_INT", 0 );

	$status = 0;
	$reason = 'None';

	if ( $xenuuid == '-migrate-' ) {
		$xenuuid = $settings['xen_migrate_uuid'];
	}

	if ( preg_match( $UUIDxen, $xenuuid ) ) {

		if ( empty( $xstatus ) ) {
			//ask for shutdown
			exec( 'ssh root@' . $settings['xen_ip'] . ' xe vm-shutdown uuid=' . $xenuuid, $output );
			$status = 1;
			$reason = 'Shutting Down';

			if ( $xenuuid != $settings['uuid_backup_engine'] ) {
				exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' rm ' . $settings['mount_migrate'] . '/xen_progress.dat', $statusoffile );
				exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' rm ' . $settings['mount_migrate'] . '/xen_status.dat', $statusoffile );
			}

		} else {
			//check if status is shutdown
			exec( 'ssh root@' . $settings['xen_ip'] . ' xe vm-list is-control-domain=false uuid=' . $xenuuid, $output );
			$statusis = str_replace( 'power-state ( RO): ', '', $output['2'] );
			$statusis = trim( $statusis );
			if ( $status == 'halted' ) {
				$status = 3;
				$reason = 'Halted';
			} else {
				$status = 2;
				$reason = 'Waiting';
			}

		}

	} else {
		$status = 0;
		$reason = 'Invalid UUID';

	}

	sleep( 1 );

	$jsonarray = array(
		"status" => $status,
		"reason" => $reason,
	);

	echo json_encode( $jsonarray );