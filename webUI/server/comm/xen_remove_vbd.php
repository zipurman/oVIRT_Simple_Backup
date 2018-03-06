<?php

	$vbd_uuid = varcheck( "vbd_uuid", '' );
	$xstatus  = varcheck( "xstatus", 0, "FILTER_VALIDATE_INT", 0 );

	if ( $vbd_uuid == '-migrate-' ) {
		exec( 'ssh root@' . $settings['xen_ip'] . ' xe vm-disk-list vm=' . $settings['xen_migrate_uuid'], $output );
		$grabnext = 0;
		foreach ( $output as $item ) {

			if ( $grabnext == 3 ) {

				if ( strpos( $item, 'userdevice ( RW): 50' ) !== false ) {
					$grabnext = 4;
				} else {
					$grabnext = 0;
					$vbd_uuid = 'none';
				}
			}
			if ( $grabnext == 2 ) {

				$grabnext = 3;
			}
			if ( $grabnext == 1 ) {

				$vbd_uuid = preg_replace( '/^.*:/i', '', $item );
				$vbd_uuid = str_replace( ' ', '', $vbd_uuid );
				$grabnext = 2;
			}
			if ( strpos( $item, 'VBD' ) !== false ) {
				$grabnext = 1;
			}
		}
		//cleanup
		exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' rm ' . $settings['mount_migrate'] . '/xen_*.dat', $statusoffile );
	}

	$status = 0;
	$reason = 'None';

	if ( preg_match( $UUIDxen, $vbd_uuid ) || $xstatus == 1 ) {

		if ( empty( $xstatus ) ) {
			//ask for shutdown
			exec( 'ssh root@' . $settings['xen_ip'] . ' xe vbd-destroy uuid=' . $vbd_uuid, $output );
			$status = 1;
			$reason = 'Removing';

		} else {
			//check  status
			exec( 'ssh root@' . $settings['xen_ip'] . ' xe  vbd-list uuid=' . $vbd_uuid, $output );
			if ( empty( $output ) || $vbd_uuid == 'none' ) {
				$status = 3;
				$reason = 'Removed';
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