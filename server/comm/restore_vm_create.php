<?php

	$sb_status = sb_status_fetch();

	$status    = 0;
	$reason    = 'None';
	$newvmuuid = '';
	sleep( 2 );

	if ( $sb_status['status'] == 'restore' && ($sb_status['stage'] == 'fixes'  || $sb_status['stage'] == 'restore_imaging' )) {


		$os          = $sb_status['setting10'];
		$nic1        = $sb_status['setting11'];
		$vmtype      = $sb_status['setting12'];
		$cluster     = $sb_status['setting13'];
		$console     = $sb_status['setting14'];
		$memory      = $sb_status['setting15'];
		$memory_max  = $sb_status['setting16'];
		$cpu_sockets = $sb_status['setting17'];
		$cpu_cores   = $sb_status['setting18'];
		$cpu_threads = $sb_status['setting19'];

		$xml = '<vm> 
				        <name>' . $sb_status['setting4'] . '</name>
				        <cluster>
				          <name>' . $cluster . '</name>
				        </cluster>
				        <template>
				          <name>Blank</name>
				        </template>
				        <memory>' . $memory . '</memory>
						<memory_policy>
							<max>' . $memory_max . '</max>
						</memory_policy>
						<cpu>
						  <topology>
						      <sockets>' . $cpu_sockets . '</sockets>
						      <cores>' . $cpu_cores . '</cores>
						      <threads>' . $cpu_threads . '</threads>
						  </topology>
						</cpu>
						<os>
							<boot> 
								<devices>
									<device>hd</device> 
								</devices>
							</boot>
							<type>' . $os . '</type> 
						</os>
						<type>' . $vmtype . '</type>
				      </vm>';
		sb_log('Create New VM - ' . $xml);
		$newvm     = ovirt_rest_api_call( 'POST', 'vms/', $xml );
		$newvmuuid = (string) $newvm['id'];

		sb_status_set( 'restore', 'disk_detatch', 0, '', '', '', '', '', '', '', '', $newvmuuid );

		//Create new console
		if ( ! empty( $console ) ) {
			$consoles = ovirt_rest_api_call( 'GET', 'vms/' . $newvmuuid . '/graphicsconsoles/' );
			foreach ( $consoles as $consoleitem ) {
				$consoledelete = ovirt_rest_api_call( 'DELETE', 'vms/' . $newvmuuid . '/graphicsconsoles/' . $consoleitem['id'] );
			}
			$newconsole = ovirt_rest_api_call( 'POST', 'vms/' . $newvmuuid . '/graphicsconsoles/', '<graphics_console> <protocol>' . $console . '</protocol> </graphics_console>' );
			sb_log('Update Graphic Console - ' . $console);
		}

		if ( ! empty( $nic1 ) ) {
			if ( $nic1 != 'none' ) {
				$xml = '<nic>
								  <interface>virtio</interface>
								  <name>NIC</name>
								<vnic_profile id="' . $nic1 . '"/>
								</nic>';
				sb_log('Create new NIC - ' . $xml);
				$nicdone = ovirt_rest_api_call( 'POST', 'vms/' . $newvmuuid . '/nics/', $xml );
			}
		}

		sb_cache_set( $settings['uuid_backup_engine'], '', 'Restore - New VM Created ', $sb_status['setting4'], 'write' );

		$status = 1;
		$reason = 'VM Created';

	}

	if ( empty( $status ) ) {
		sb_cache_set( $vmuuid, $snapshotname, 'Restore - Creating VM Failure - ' . $reason, $sb_status['setting4'], 'write' );
	}

	$jsonarray = array(
		"status"    => $status,
		"reason"    => $reason,
	);

	sb_log('VM Create - ' . $reason);

	echo json_encode( $jsonarray );