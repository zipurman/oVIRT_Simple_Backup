<?php

	$diskname      = varcheck( "diskname", '' );
	$disknamecheck = preg_replace( '/[0-9a-zA-Z\-_]/i', '', $diskname );

	$status    = 0;
	$reason    = 'None';
	$newvmuuid = '';
	sleep( 2 );

	if ( ! empty( $disknamecheck ) ) {

		$status = 0;
		$reason = 'Invalid Disk Name';

	} else {

		$diskbytes = $disksize * 1024 * 1024 * 1024;
		$xml       = '<vm> 
				         <name>' . $diskname . '</name>
				         <cluster>
				          <name>' . $settings['cluster'] . '</name>
				         </cluster>
				         <template>
				          <name>Blank</name>
				         </template>
				        </vm>';

		$newvm = ovirt_rest_api_call( 'POST', 'vms/', $xml );

		$newvmuuid = (string) $newvm['id'];

		sb_cache_set( $settings['uuid_backup_engine'], '', 'Restore - New VM Created ' . $disksize . ' GB Disk', $diskname, 'write' );

		$status = 1;
		$reason = 'VM Created';

	}

	if ( empty( $status ) ) {
		sb_cache_set( $vmuuid, $snapshotname, 'Restore - Creating VM Failure - ' . $reason, $diskname, 'write' );
	}

	$jsonarray = array(
		"status"    => $status,
		"reason"    => $reason,
		"newvmuuid" => $newvmuuid,
	);

	echo json_encode( $jsonarray );