<?php

	$vdi_uuid = varcheck( "vdi_uuid", '' );
	$xenuuid  = varcheck( "xenuuid", '' );
	$xstatus  = varcheck( "xstatus", 0, "FILTER_VALIDATE_INT", 0 );

	if ( preg_match( $UUIDxen, $xenuuid ) ) {
		$vmuuid = $xenuuid;
	} else {
		$vmuuid = $settings['xen_migrate_uuid'];
	}

	$status = 0;
	$reason = 'None';

	if ( preg_match( $UUIDxen, $vdi_uuid ) ) {

		if ( empty( $xstatus ) ) {
			//ask for shutdown
			if ( $vmuuid != $settings['xen_migrate_uuid'] ) {
				exec( 'ssh root@' . $settings['xen_ip'] . ' xe vbd-create vm-uuid=' . $vmuuid . ' device=0 bootable=true mode=RW type=Disk vdi-uuid=' . $vdi_uuid, $output );
			} else {
				exec( 'ssh root@' . $settings['xen_ip'] . ' xe vbd-create vm-uuid=' . $vmuuid . ' device=50 bootable=false mode=RW type=Disk vdi-uuid=' . $vdi_uuid, $output );
			}
			$status = 1;
			$reason = 'Adding';

		} else {

			exec( 'ssh root@' . $settings['xen_ip'] . ' xe vm-disk-list vm=' . $vmuuid, $outputx );
			$hasdisk = 0;
			foreach ( $outputx as $item ) {
				if ( strpos( $item, $vdi_uuid ) !== false ) {
					$hasdisk = 1;
				}
			}

			//check  status
			if ( ! empty( $hasdisk ) ) {
				$status = 3;
				$reason = 'Disk Attached';
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