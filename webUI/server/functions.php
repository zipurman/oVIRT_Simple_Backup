<?php

	/**
	 * @param     $arraytoshow
	 * @param int $showfullscreen
	 */
	function showme( $arraytoshow, $showfullscreen = 0 ) {

		if ( $showfullscreen == 1 ) {
			echo '<div style="position: fixed; top: 0px; left: 0px; width: 2000px; height: 1000px; overflow: auto;">';
		}
		$newarray = $arraytoshow;
		echo '<pre>';
		var_dump( $newarray );
		echo '</pre>';
		if ( $showfullscreen == 1 ) {
			echo '</div>';
		}
	}

	/**
	 * @param        $var_to_check
	 * @param        $default_value
	 * @param string $checkformat
	 * @param string $if_invalid_value
	 * @param null   $current_val
	 *
	 * @return int|null
	 */
	function varcheck( $var_to_check, $default_value, $checkformat = "", $if_invalid_value = "", $current_val = null ) {

		if ( $current_val != null && $default_value == $current_val ) {
			$y = $current_val;
		} else if ( $current_val != "" && $current_val != null ) {
			$y = $current_val;
		} else {
			if ( isset( $_GET[ $var_to_check ] ) ) {
				$y = $_GET[ $var_to_check ];
			} elseif ( isset( $_POST[ $var_to_check ] ) ) {
				$y = $_POST[ $var_to_check ];
			} elseif ( isset( $_COOKIE[ $var_to_check ] ) ) {
				$y = $_COOKIE[ $var_to_check ];
			} elseif ( isset( $_SESSION[ $var_to_check ] ) ) {
				$y = $_SESSION[ $var_to_check ];
			} else {
				$y = $default_value;
			}
		}
		$checkformat = (string) $checkformat;

		if ( strpos( "$checkformat", "FILTER_VALIDATE_INT" ) !== false ) {
			if ( filter_var( $y, FILTER_VALIDATE_INT ) === 0 || ! filter_var( $y, FILTER_VALIDATE_INT ) === false ) {
				//all okay with $y
			} else {
				$y = $if_invalid_value;
			}
		} else if ( strpos( "$checkformat", "FILTER_VALIDATE_PIN" ) !== false ) {
			$opt = array( "options" => array( "regexp" => "/^[0-9]{4,8}$/" ) );
			if ( filter_var( $y, FILTER_VALIDATE_REGEXP, $opt ) === false ) {
				$y = $if_invalid_value;
			}
		} else if ( strpos( "$checkformat", "FILTER_VALIDATE_FLOAT" ) !== false ) {
			$y = (float) $y;
			if ( ! filter_var( $y, FILTER_VALIDATE_FLOAT ) ) {
				$y = $if_invalid_value;
			}
		} else if ( strpos( "$checkformat", "FILTER_VALIDATE_DATE" ) !== false ) {
			if ( ! validateDate( $y, 'm/d/Y' ) ) {
				$y = $if_invalid_value;
			}
		} else if ( strpos( "$checkformat", "FILTER_VALIDATE_EMAIL" ) !== false ) {
			if ( ! filter_var( $y, FILTER_VALIDATE_EMAIL ) ) {
				$y = $if_invalid_value;
			}
		} else if ( strpos( "$checkformat", "FILTER_VALIDATE_URL" ) !== false ) {
			if ( ! filter_var( $y, FILTER_VALIDATE_URL ) ) {
				$y = $if_invalid_value;
			}
		} else if ( strpos( "$checkformat", "FILTER_VALIDATE_ARRAY" ) !== false ) {
			if ( ! is_array( $y ) ) {
				if ( is_array( $if_invalid_value ) ) {
					$y = $if_invalid_value;
				} else {
					$y = array( $if_invalid_value );
				}
			}
		}

		return $y;
	}

	function sb_form_start() {

		echo '<form method="POST" action="index.php">';
	}

	function sb_form_end() {

		echo '</form>';
	}

	function sb_table_start() {

		echo '<table>';
	}

	function sb_table_end() {

		echo '</tbody>';
		echo '</table>';
	}

	function sb_table_heading( $rowdata ) {

		echo '<thead>';
		echo '<tr>';
		foreach ( $rowdata as $rowdatum ) {
			if ( empty( $rowdatum['text'] ) ) {
				$rowdatum['text'] = '';
			}
			if ( empty( $rowdatum['class'] ) ) {
				$rowdatum['class'] = '';
			}
			if ( empty( $rowdatum['width'] ) ) {
				$rowdatum['width'] = '';
			}
			echo '<th';
			if ( ! empty( $rowdatum['class'] ) ) {
				echo ' class="' . $rowdatum['class'] . '"';
			}
			if ( ! empty( $rowdatum['width'] ) ) {
				echo ' width="' . $rowdatum['width'] . '"';
			}
			echo '>';
			echo $rowdatum['text'];
			echo '</th>';
		}
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
	}

	function sb_table_row( $rowdata ) {

		echo '<tr>';
		foreach ( $rowdata as $rowdatum ) {
			if ( empty( $rowdatum['text'] ) ) {
				$rowdatum['text'] = '';
			}
			if ( empty( $rowdatum['class'] ) ) {
				$rowdatum['class'] = '';
			}
			echo '<td';
			if ( ! empty( $rowdatum['class'] ) ) {
				echo ' class="' . $rowdatum['class'] . '"';
			}
			echo '>';
			echo $rowdatum['text'];
			echo '</td>';
		}
		echo '</tr>';
	}

	function sb_input( $inputdata ) {

		$returndata = '';

		if ( $inputdata['type'] == 'text' ) {
			$returndata .= '<input autocomplete="off" type="text" id="' . $inputdata['name'] . '" name="' . $inputdata['name'] . '" size="' . $inputdata['size'] . '" maxlength="' . $inputdata['maxlength'] . '" value="' . $inputdata['value'] . '" />';
		} else if ( $inputdata['type'] == 'password' ) {
			$returndata .= '<input autocomplete="off" type="password" id="' . $inputdata['name'] . '" name="' . $inputdata['name'] . '" size="' . $inputdata['size'] . '" maxlength="' . $inputdata['maxlength'] . '" value="' . $inputdata['value'] . '" />';
		} else if ( $inputdata['type'] == 'checkbox' ) {
			$returndata .= '<input autocomplete="off" type="checkbox" id="' . $inputdata['id'] . '" name="' . $inputdata['name'] . '" size="' . $inputdata['size'] . '" maxlength="' . $inputdata['maxlength'] . '" value="' . $inputdata['value'] . '" ';
			$returndata .= ($inputdata['checked']) ? ' CHECKED' : '';
			$returndata .= '/>';
		} else if ( $inputdata['type'] == 'hidden' ) {
			$returndata .= '<input autocomplete="off "type="hidden" id="' . $inputdata['name'] . '" name="' . $inputdata['name'] . '" value="' . $inputdata['value'] . '" />';
		} else if ( $inputdata['type'] == 'submit' ) {
			$returndata .= '<input type="submit" value="' . $inputdata['value'] . '" />';
		} else if ( $inputdata['type'] == 'select' ) {
			$returndata .= '<select autocomplete="off" id="' . $inputdata['name'] . '" name="' . $inputdata['name'] . '" >';
			foreach ( $inputdata['list'] as $returndatum ) {
				$returndata .= '<option value="' . $returndatum['id'] . '"';
				if ( $returndatum['id'] == $inputdata['value'] ) {
					$returndata .= ' SELECTED';
				}
				$returndata .= '>';
				$returndata .= $returndatum['name'];
				$returndata .= '</option>';
			}
			$returndata .= '</select>';
		}

		if ( ! empty( $inputdata['dataafter'] ) ) {
			$returndata .= '' . $inputdata['dataafter'];
		}

		return $returndata;
	}

	function ovirt_rest_api_call( $type = 'POST', $path = 'vms', $data = '', $allcontent = false ) {

		GLOBAL $settings;

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, "https://{$settings['ovirt_url']}/ovirt-engine/api/{$path}" );
		if ( $type == 'POST' ) {
			curl_setopt( $ch, CURLOPT_POST, 1 );
		} else if ( $type == 'DELETE' ) {
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE" );
		} else if ( $type == 'PUT' ) {
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
		}
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_USERPWD, "{$settings['ovirt_user']}:{$settings['ovirt_pass']}" );
		if ( ! empty( $data ) ) {
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		}
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		if ( $allcontent ) {
			$headers = [
				'All-Content: true',
				'Accept: application/xml',
				'Content-Type: application/xml',
			];
		} else {
			$headers = [
				'Accept: application/xml',
				'Content-Type: application/xml',
			];
		}
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		$server_output = curl_exec( $ch );
		curl_close( $ch );
		$server_output = new SimpleXMLElement( $server_output );

		return $server_output;
	}

	function sb_pagetitle( $title ) {

		echo '<h1 class="pagetitle">' . $title . '</h1>';
	}

	function sb_pagedescription( $desc ) {

		echo '<div class="pagedescription">' . $desc . '</div>';
	}

	function sb_gobutton( $buttontext, $link, $js = '' ) {

		$jstext = '';

		if ( ! empty( $link ) ) {
			$jstext = ' onClick="window.location=\'' . $link . '\'"';
		} else if ( ! empty( $js ) ) {
			$jstext = ' onClick="' . $js . '"';
		}
		echo '<div class="gobutton" ' . $jstext . '>' . $buttontext . '</div>';
	}

	function sb_progress_bar( $domid ) {

		echo '<div class="progressbar" id="' . $domid . '"><div class="progressbarinner"></div></div>';

	}

	function sb_status_box( $domid ) {

		echo '<div class="statusbox" id="' . $domid . '"></div>';

	}

	function sb_cache_set( $vmuuid, $backupname, $status, $vmname, $op = 'write', $recoveryurl = '', $recoveryjs = '' ) {

		if ( $op == 'write' ) {
			if ( $cachefile = fopen( '../cache/' . $vmuuid, "w" ) ) {
				fwrite( $cachefile, $vmuuid . "\n" );
				fwrite( $cachefile, $backupname . "\n" );
				fwrite( $cachefile, $status . "\n" );
				fwrite( $cachefile, $vmname . "\n" );
				fwrite( $cachefile, $recoveryurl . "\n" );
				fwrite( $cachefile, $recoveryjs . "\n" );
				fclose( $cachefile );
			}
		} else if ( $op == 'delete' ) {
			unlink( '../cache/' . $vmuuid );
		}
	}

	function sb_check_disks( $alloweddisks ) {

		GLOBAL $settings, $area;

		exec( 'fdisk -l | grep "Disk /dev" | awk \'{ print $2}\'', $output );
		$disks              = 0;
		$avaliabledisks     = array();
		$avaliablediskstext = '';
		$lastdev            = 'nodiskselected';
		foreach ( $output as $item ) {
			$item = str_replace( ':', '', $item );
			$item = str_replace( '/dev/', '', $item );

			if ( $item != $settings['drive_type'] . 'a' ) {
				$disks ++;
				$avaliabledisks[ $item ] = $item;
				$avaliablediskstext      .= '(' . $item . ')';
				$lastdev                 = $item;
			}
		}

		$return = array(
			"disks"              => $disks,
			"avaliabledisks"     => $avaliabledisks,
			"avaliablediskstext" => $avaliablediskstext,
			"lastdev"            => $lastdev,
		);

		if ( $return['disks'] > $alloweddisks ) {
			die( 'You must disconnect extra disks from the Backup VM. ' . $return['avaliablediskstext'] . '<br/><a href="?area=' . $area . '&disconnectdisks=1">Click Here to have the script disconnect the disk.</a>' );
		}

		return $return;

	}

	function sb_xen_check_disks( $alloweddisks ) {

		GLOBAL $settings, $area;

		exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' fdisk -l | grep "Disk /dev" | awk \'{ print $2}\'', $output );
		$disks              = 0;
		$avaliabledisks     = array();
		$avaliablediskstext = '';
		$lastdev            = '';
		foreach ( $output as $item ) {
			$item = str_replace( ':', '', $item );
			$item = str_replace( '/dev/', '', $item );

			if ( $item != 'xvda' ) {
				$disks ++;
				$avaliabledisks[ $item ] = $item;
				$avaliablediskstext      .= '(' . $item . ')';
				$lastdev                 = $item;
			}
		}

		$return = array(
			"disks"              => $disks,
			"avaliabledisks"     => $avaliabledisks,
			"avaliablediskstext" => $avaliablediskstext,
			"lastdev"            => $lastdev,
		);

		return $return;

	}

	function sb_oslist() {

		$oslist = array(
			array( 'id' => 'debian_7', 'name' => 'debian_7' ),
			array( 'id' => 'other', 'name' => 'Other OS', ),
			array( 'id' => 'other_linux', 'name' => 'Linux', ),
			//			array( 'id' => 'freebsd_9_2', 'name' => 'FreeBSD 9.2', ),
			//			array( 'id' => 'freebsd_9_2x64', 'name' => 'FreeBSD 9.2 x64', ),
			//			array( 'id' => 'rha7x64', 'name' => 'Red Hat Atomic 7.x x64', ),
			array( 'id' => 'rhel_3', 'name' => 'Red Hat Enterprise Linux 3.x', ),
			array( 'id' => 'rhel_3x64', 'name' => 'Red Hat Enterprise Linux 3.x x64', ),
			array( 'id' => 'rhel_4', 'name' => 'Red Hat Enterprise Linux 4.x', ),
			array( 'id' => 'rhel_4x64', 'name' => 'Red Hat Enterprise Linux 4.x x64', ),
			array( 'id' => 'rhel_5', 'name' => 'Red Hat Enterprise Linux 5.x', ),
			array( 'id' => 'rhel_5x64', 'name' => 'Red Hat Enterprise Linux 5.x x64', ),
			array( 'id' => 'rhel_6', 'name' => 'Red Hat Enterprise Linux 6.x', ),
			array( 'id' => 'rhel_6x64', 'name' => 'Red Hat Enterprise Linux 6.x x64', ),
			array( 'id' => 'rhel_7x64', 'name' => 'Red Hat Enterprise Linux 7.x x64', ),
			//			array( 'id' => 'suse_linux_11', 'name' => 'SUSE Linux Enterprise Server 11 +', ),
			//			array( 'id' => 'ubuntu_precise_pangolin_lts', 'name' => 'Ubuntu Precise Pangolin LTS', ),
			//			array( 'id' => 'ubuntu_quantzal_quetzal', 'name' => 'Ubuntu Quantal Quetzal', ),
			//			array( 'id' => 'ubuntu_raring_ringtails', 'name' => 'Ubuntu Raring Ringtails', ),
			//			array( 'id' => 'ubuntu_saucy_salamander', 'name' => 'Ubuntu Saucy Salamander', ),
			//			array( 'id' => 'ubuntu_trusty_tahr_lts', 'name' => 'Ubuntu Trusty Tahr LTS', ),
			array( 'id' => 'unassigned', 'name' => 'Unassigned', ),
			array( 'id' => 'windows_2003', 'name' => 'Windows 2003', ),
			array( 'id' => 'windows_2003x64', 'name' => 'Windows 2003 x64', ),
			array( 'id' => 'windows_2008', 'name' => 'Windows 2008', ),
			array( 'id' => 'windows_2008r2x64', 'name' => 'Windows 2008 R2 x64', ),
			array( 'id' => 'windows_2008x64', 'name' => 'Windows 2008 x64', ),
			array( 'id' => 'windows_2012x64', 'name' => 'Windows 2012 x64', ),
			array( 'id' => 'windows_2012r2x64', 'name' => 'Windows 2012 R2 x64', ),
			array( 'id' => 'windows_2016x64', 'name' => 'Windows 2016 x64', ),
			array( 'id' => 'windows_7', 'name' => 'Windows 7', ),
			array( 'id' => 'windows_7x64', 'name' => 'Windows 7 x64', ),
			array( 'id' => 'windows_8', 'name' => 'Windows 8', ),
			array( 'id' => 'windows_8x64', 'name' => 'Windows 8 x64', ),
			array( 'id' => 'windows_10', 'name' => 'Windows 10', ),
			array( 'id' => 'windows_10x64', 'name' => 'Windows 10 x64', ),
			array( 'id' => 'windows_xp', 'name' => 'Windows XP', ),
		);

		return $oslist;
	}

	function sb_clusterlist() {

		$list     = array();
		$clusters = ovirt_rest_api_call( 'GET', 'clusters/' );
		foreach ( $clusters as $cluster ) {
			$list[ (string) $cluster->name ] = array( 'id' => $cluster->name, 'name' => $cluster->name, );

		}

		return $list;

	}

	function sb_domainlist() {

		$list = array();

		$domains = ovirt_rest_api_call( 'GET', 'storagedomains/' );
		foreach ( $domains as $domain ) {
			if ( $domain->type == 'data' ) {
				$list[ (string) $domain->name ] = array( 'id' => $domain->name, 'name' => $domain->name, );
			}
		}

		return $list;

	}

	function sb_niclist() {

		$list = array();
		$list[ 0 ] = array( 'id' => 'none', 'name' => 'none', );

		$nics = ovirt_rest_api_call( 'GET', 'networks/' );
		foreach ( $nics as $nic ) {
			if ( $nic->usages->usage == 'vm' ) {
				$vnics = ovirt_rest_api_call( 'GET', 'networks/' .  $nic['id'] . '/vnicprofiles' );
				foreach ( $vnics as $vnic ) {
					$list[ (string) $nic->name ] = array( 'id' => $vnic['id'], 'name' => $nic->name, );
				}
			}
		}

		return $list;

	}

	function sb_consolelist() {

		return array(
			array( 'id' => 'spice', 'name' => 'spice', ),
			array( 'id' => 'vnc', 'name' => 'vnc', ),
		);
	}

	function sb_vmtypelist() {

		return array(
			array( 'id' => 'server', 'name' => 'server', ),
			array( 'id' => 'workstation', 'name' => 'workstation', ),
		);
	}

	function sb_check_swapgrub() {

		exec( 'cat /etc/crontab', $crontab );
		$fixswapok = 0;
		$fixgrubok = 0;
		foreach ( $crontab as $item ) {
			if ( strpos( $item, 'fixgrub.sh' ) !== false ) {
				$fixgrubok = 1;
			}
			if ( strpos( $item, 'fixswap.sh' ) !== false ) {
				$fixswapok = 1;
			}
		}

		return array(
			'fixgrubok' => $fixgrubok,
			'fixswapok' => $fixswapok,
		);
	}

	function sb_new_vm_settings( $disksize, $memory, $memorymax ) {

		GLOBAL $settings;

		echo "<br/><h3>New Vm Settings:</h3>";
		sb_table_start();

		$rowdata = array(
			array( "text" => "", "width" => "10%", ),
			array( "text" => "", "width" => "40%", ),
			array( "text" => "", "width" => "10%", ),
			array( "text" => "", "width" => "40%", ),
		);
		sb_table_heading( $rowdata );

		$rowdata = array(
			array( "text" => 'Image Size:', ),
			array( "text" => $disksize . ' GB', ),
			array( "text" => 'New VM Name:', ),
			array(
				"text" => sb_input( array(
					'type'      => 'text',
					'name'      => 'restorenewname',
					'size'      => '36',
					'maxlength' => '36',
					'value'     => '',
				) ),
			),
		);
		sb_table_row( $rowdata );

		$fixes = sb_check_swapgrub();

		if ( ! empty( $fixes['fixgrubok'] ) ) {
			$gruboption = sb_input( array(
				'type'  => 'select',
				'name'  => 'option_fixgrub',
				'list'  => array(
					array( 'id' => '0', 'name' => 'No', ),
					array( 'id' => '1', 'name' => 'Yes', ),
				),
				'value' => 0,
			) );
		} else {
			$gruboption = sb_input( array(
				'type'      => 'hidden',
				'name'      => 'option_fixgrub',
				'value'     => 0,
				'dataafter' => 'You must add the fixgrub.sh to your crontab for this option.',
			) );
		}

		if ( ! empty( $fixes['fixswapok'] ) ) {
			$swapoption = sb_input( array(
				'type'  => 'select',
				'name'  => 'option_fixswap',
				'list'  => array(
					array( 'id' => '0', 'name' => 'No', ),
					array( 'id' => '1', 'name' => 'Yes', ),
				),
				'value' => 0,
			) );
		} else {
			$swapoption = sb_input( array(
				'type'      => 'hidden',
				'name'      => 'option_fixswap',
				'value'     => 0,
				'dataafter' => 'You must add the fixswap.sh to your crontab for this option.',
			) );
		}

		$rowdata = array(
			array( "text" => 'Fix Grub:', ),
			array(
				"text" => $gruboption,
			),
			array( "text" => 'Fix Swap:', ),
			array(
				"text" => $swapoption,
			),
		);
		sb_table_row( $rowdata );

		$oslist   = sb_oslist();
		$consoles = sb_consolelist();
		$rowdata  = array(
			array( "text" => 'OS:', ),
			array(
				"text" => sb_input( array(
					'type'  => 'select',
					'name'  => 'os',
					'list'  => $oslist,
					'value' => $settings['restore_os'],
				) ),
			),
			array( "text" => 'Console:', ),
			array(
				"text" => sb_input( array(
					'type'  => 'select',
					'name'  => 'console',
					'list'  => $consoles,
					'value' => $settings['restore_console'],
				) ),
			),
		);
		sb_table_row( $rowdata );

		$clusters = sb_clusterlist();
		$domains  = sb_domainlist();
		$rowdata  = array(
			array( "text" => 'Restore to Cluster:', ),
			array(
				"text" => sb_input( array(
					'type'  => 'select',
					'name'  => 'cluster',
					'list'  => $clusters,
					'value' => $settings['cluster'],
				) ),
			),
			array( "text" => 'Restore to Domain:', ),
			array(
				"text" => sb_input( array(
					'type'  => 'select',
					'name'  => 'domain',
					'list'  => $domains,
					'value' => $settings['storage_domain'],
				) ),
			),
		);
		sb_table_row( $rowdata );

		$vmtypes = sb_vmtypelist();
		$rowdata = array(
			array( "text" => 'VM Type:', ),
			array(
				"text" => sb_input( array(
					'type'  => 'select',
					'name'  => 'vmtype',
					'list'  => $vmtypes,
					'value' => $settings['restore_vm_type'],
				) ),
			),
			array( "text" => 'CPU Sockets:', ),
			array(
				"text" => sb_input( array(
					'type'      => 'text',
					'name'      => 'sockets',
					'size'      => '2',
					'maxlength' => '2',
					'value'     => $settings['restore_cpu_sockets'],
				) ),
			),
		);
		sb_table_row( $rowdata );

		$nics = sb_niclist();
		$rowdata = array(
			array( "text" => 'Network Card:', ),
			array(
				"text" => sb_input( array(
					'type'  => 'select',
					'name'  => 'nic1',
					'list'  => $nics,
					'value' => '',
				) ),
			),
			array( "text" => '', ),
			array( "text" => '', ),
		);
		sb_table_row( $rowdata );

		$rowdata = array(
			array( "text" => 'CPU Cores:', ),
			array(
				"text" => sb_input( array(
					'type'      => 'text',
					'name'      => 'cores',
					'size'      => '2',
					'maxlength' => '2',
					'value'     => $settings['restore_cpu_cores'],
				) ),
			),
			array( "text" => 'CPU Threads:', ),
			array(
				"text" => sb_input( array(
					'type'      => 'text',
					'name'      => 'threads',
					'size'      => '1',
					'maxlength' => '1',
					'value'     => $settings['restore_cpu_threads'],
				) ),
			),
		);
		sb_table_row( $rowdata );

		$rowdata = array(
			array( "text" => 'Memory:', ),
			array(
				"text" => sb_input( array(
					'type'      => 'text',
					'name'      => 'memory',
					'size'      => '4',
					'maxlength' => '4',
					'value'     => $memory,
					"dataafter" => " GB",
				) ),
			),
			array( "text" => 'Max Memory:', ),
			array(
				"text" => sb_input( array(
					'type'      => 'text',
					'name'      => 'memory_max',
					'size'      => '4',
					'maxlength' => '4',
					'value'     => $memorymax,
					"dataafter" => " GB",
				) ),
			),
		);
		sb_table_row( $rowdata );
		sb_table_end();
	}


