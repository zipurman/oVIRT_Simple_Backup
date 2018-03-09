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

		$os         = varcheck( "os", '' );
		$nic1         = varcheck( "nic1", '' );
		$vmtype         = varcheck( "vmtype", '' );
		$cluster         = varcheck( "cluster", '' );
		$console         = varcheck( "console", '' );
		$memory     = varcheck( "memory", 1, "FILTER_VALIDATE_INT", 1 );
		$memory_max = varcheck( "memory_max", 1, "FILTER_VALIDATE_INT", 1 );

		$cpu_sockets = varcheck( "cpu_sockets", 1, "FILTER_VALIDATE_INT", 1 );
		$cpu_cores = varcheck( "cpu_cores", 1, "FILTER_VALIDATE_INT", 1 );
		$cpu_threads = varcheck( "cpu_threads", 1, "FILTER_VALIDATE_INT", 1 );

		$memory     = $memory * 1024 * 1024 * 1024;
		$memory_max = $memory_max * 1024 * 1024 * 1024;

//		$diskbytes = $disksize * 1024 * 1024 * 1024;
		$xml       = '<vm> 
				        <name>' . $diskname . '</name>
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
							<type>' . $os .'</type> 
						</os>
						<type>' . $vmtype . '</type>
				      </vm>';

		$newvm = ovirt_rest_api_call( 'POST', 'vms/', $xml );
		$newvmuuid = (string) $newvm['id'];

		//Create new console
		if (!empty($console)){
			$consoles = ovirt_rest_api_call( 'GET', 'vms/' . $newvmuuid . '/graphicsconsoles/' );
			foreach ( $consoles as $consoleitem ) {
				$consoledelete = ovirt_rest_api_call( 'DELETE', 'vms/' . $newvmuuid . '/graphicsconsoles/'.$consoleitem['id'] );
			}
			$newconsole = ovirt_rest_api_call( 'POST', 'vms/' . $newvmuuid . '/graphicsconsoles/', '<graphics_console> <protocol>' . $console . '</protocol> </graphics_console>' );
		}

		if (!empty($nic1)){
			if ($nic1 != 'none'){
				$xml       = '<nic>
								  <interface>virtio</interface>
								  <name>NIC</name>
								<vnic_profile id="' . $nic1 . '"/>
								</nic>';

				$nicdone = ovirt_rest_api_call( 'POST', 'vms/' . $newvmuuid . '/nics/', $xml );
			}
		}

		sb_cache_set( $settings['uuid_backup_engine'], '', 'Restore - New VM Created Disk', $diskname, 'write' );

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