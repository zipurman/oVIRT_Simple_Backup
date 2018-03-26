<?php

	$sb_status = sb_status_fetch();

	$status = 0;
	$reason = 'None';

	if ( $sb_status['status'] == 'xen_migrate'  && $sb_status['stage'] == 'xen_start' ) {


		if ( $sb_status['step'] < 2 ) {
			$xenuuid = $settings['xen_migrate_uuid'];
		} else {
			$xenuuid = $sb_status['setting2'];
		}

		if ( preg_match( $UUIDxen, $xenuuid ) ) {

			if ( $sb_status['step'] == 0 || $sb_status['step'] == 2 ) {

				//ask for start
				exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vm-start uuid=' . $xenuuid, $output );
				$status = 1;
				$reason = 'Starting';
				if ($sb_status['step'] == 0) {
					sb_status_set( 'xen_migrate', 'xen_start', 1 );
				} else {
					sb_status_set( 'xen_migrate', 'xen_start', 3 );
				}

			} else {

				//check if status is shutdown
				exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vm-list is-control-domain=false uuid=' . $xenuuid, $output );
				$statusis = str_replace( 'power-state ( RO): ', '', $output['2'] );
				$statusis = trim( $statusis );
				if ( $status == 'running' ) {
					$status = 3;
					$reason = 'Started';
					if ($sb_status['step'] == 1){
						sb_status_set( 'xen_migrate', 'xen_imaging', 0 );
					} else {
						sb_log('Xen - Export Completed');
						sb_status_set( 'xen_restore', 'start', 0 );
					}
				} else {
					$status = 2;
					$reason = 'Waiting';
				}
			}

		} else {
			$status = 0;
			$reason = 'Invalid UUID';

		}
	}

	sleep( 1 );

	$jsonarray = array(
		"status" => $status,
		"reason" => $reason,
	);

	sb_log('Xen - Start - ' . $reason);

	echo json_encode( $jsonarray );