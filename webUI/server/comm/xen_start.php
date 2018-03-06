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
			sleep( 2 );
			//ask for start
			exec( 'ssh root@' . $settings['xen_ip'] . ' xe vm-start uuid=' . $xenuuid, $output );
			$status = 1;
			$reason = 'Starting';

		} else {
			//check if status is shutdown
			exec( 'ssh root@' . $settings['xen_ip'] . ' xe vm-list is-control-domain=false uuid=' . $xenuuid, $output );
			$statusis = str_replace( 'power-state ( RO): ', '', $output['2'] );
			$statusis = trim( $statusis );
			if ( $status == 'running' ) {
				$status = 3;
				$reason = 'Started';
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