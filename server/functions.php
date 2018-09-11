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

		if (empty($inputdata['id'])){
			$inputdata['id'] = $inputdata['name'];
		}

		if ( $inputdata['type'] == 'text' ) {
			if ( empty( $inputdata['maxlength'] ) ) {
				$inputdata['maxlength'] = $inputdata['size'];
			}
			$returndata .= '<input autocomplete="off" type="text" id="' . $inputdata['name'] . '" name="' . $inputdata['name'] . '" size="' . $inputdata['size'] . '" maxlength="' . $inputdata['maxlength'] . '" value="' . $inputdata['value'] . '" />';

		} else if ( $inputdata['type'] == 'password' ) {

			$returndata .= '<input autocomplete="off" type="password" id="' . $inputdata['name'] . '" name="' . $inputdata['name'] . '" size="' . $inputdata['size'] . '" maxlength="' . $inputdata['maxlength'] . '" value="' . $inputdata['value'] . '" />';

		} else if ( $inputdata['type'] == 'checkbox' ) {

			$returndata .= '<input autocomplete="off" type="checkbox" id="' . $inputdata['id'] . '" name="' . $inputdata['name'] . '" value="' . $inputdata['value'] . '" ';
			$returndata .= ( $inputdata['checked'] ) ? ' CHECKED' : '';
			$returndata .= '/>';

		} else if ( $inputdata['type'] == 'hidden' ) {

			$returndata .= '<input autocomplete="off "type="hidden" id="' . $inputdata['name'] . '" name="' . $inputdata['name'] . '" value="' . $inputdata['value'] . '" />';

		} else if ( $inputdata['type'] == 'submit' ) {

			$returndata .= '<input type="submit" value="' . $inputdata['value'] . '" />';

		} else if ( $inputdata['type'] == 'select' ) {

			$returndata .= '<select autocomplete="off" id="' . $inputdata['name'] . '" name="' . $inputdata['name'] . '"';
			if ( ! empty( $inputdata['onchange'] ) ) {
				$returndata .= ' onChange="' . $inputdata['onchange'] . '"';
			}
			$returndata .= ' >';
			foreach ( $inputdata['list'] as $returndatum ) {
				$returndata .= '<option value="' . $returndatum['id'] . '"';
				if ( $returndatum['id'] == $inputdata['value'] ) {
					$returndata .= ' SELECTED';
				}

				foreach ( $returndatum as $dataitem => $datavalue ) {
					if ( $dataitem != 'id' && $dataitem != 'value' ) {
						$returndata .= ' data-' . $dataitem . '="' . $datavalue . '"';
					}
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

		GLOBAL $settings, $salt, $pepper, $mykey;

		if ( ! empty( $settings['uuid_backup_engine'] ) ) {
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
			$ovirt_pass = sb_decrypt3( $settings['ovirt_pass'], $salt, $pepper, $mykey );
			curl_setopt( $ch, CURLOPT_USERPWD, "{$settings['ovirt_user']}:{$ovirt_pass}" );
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

			try {
				$server_output = new SimpleXMLElement( $server_output );
			} catch ( Exception $e ) {
				$server_output = array();
			}

			return $server_output;
		} else {
			return array();
        }
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

		GLOBAL $projectpath;

		if ( $op == 'write' ) {
			if ( $cachefile = fopen( $projectpath . 'cache/' . $vmuuid, "w" ) ) {
				fwrite( $cachefile, $vmuuid . "\n" );
				fwrite( $cachefile, $backupname . "\n" );
				fwrite( $cachefile, $status . "\n" );
				fwrite( $cachefile, $vmname . "\n" );
				fwrite( $cachefile, $recoveryurl . "\n" );
				fwrite( $cachefile, $recoveryjs . "\n" );
				fclose( $cachefile );
			}
		} else if ( $op == 'delete' ) {
			unlink( $projectpath . 'cache/' . $vmuuid );
		}
	}

	function sb_check_disks() {

		GLOBAL $settings;

		exec( '/sbin/fdisk -l | grep " /dev" | awk \'{ print $2}\'', $output );
		$disks              = 0;
		$avaliabledisks     = array();
		$avaliablediskstext = '';
		$lastdev            = 'nodiskselected';
		$disktype           = '';
		$newbootdisk        = '';
		$hassdavda          = 0;
		foreach ( $output as $item ) {
			if ( strpos( $item, 'mapper' ) == false ) {
				$item = str_replace( ':', '', $item );
				$item = str_replace( '/dev/', '', $item );
				if ( $item != 'vda' && $item != 'sda' ) {
					$disks ++;
					if ( empty( $newbootdisk ) ) {
						$newbootdisk = $item;
					}
					$avaliabledisks[ $item ] = $item;
					$avaliablediskstext      .= '(' . $item . ')';
					$lastdev                 = $item;
				} else {
					$disktype  = substr( $item, 0, 2 );
					$hassdavda = 1;
				}
			}
		}

		if ( empty( $hassdavda ) ) {
			die( 'Reboot your BackupEngineVM - Disks Out Of Order' );
		}

		if ( $disktype == 'vd' ) {
			$driveinterface = 'virtio';
		} else {
			$driveinterface = 'virtio_scsi';
		}

		ksort( $avaliabledisks );

		$return = array(
			"disks"              => $disks,
			"avaliabledisks"     => $avaliabledisks,
			"avaliablediskstext" => $avaliablediskstext,
			"lastdev"            => $lastdev,
			"disktype"           => $disktype,
			"driveinterface"     => $driveinterface,
			"newbootdisk"        => $newbootdisk,
		);

		return $return;

	}

	function sb_xen_check_disks() {

		GLOBAL $settings, $area, $extrasshsettings;

		exec( 'ssh root@' . $settings['xen_migrate_ip'] . $extrasshsettings . ' /sbin/fdisk -l | grep " /dev" | awk \'{ print $2}\'', $output );
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

		//alpha sort disks
		ksort( $avaliabledisks );

		//numeric disks after sort
		$avaliabledisks_new = array();
		$diskid             = 1;
		foreach ( $avaliabledisks as $avaliabledisk ) {
			$avaliabledisks_new[ $diskid ] = $avaliabledisk;
			$diskid ++;
		}

		$return = array(
			"disks"              => $disks,
			"avaliabledisks"     => $avaliabledisks_new,
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
				$list[ (string) $domain->name ] = array(
					'id'              => $domain->name,
					'name'            => $domain->name,
					'supportsdiscard' => $domain->supports_discard,
				);
			}
		}

		return $list;

	}

	function sb_niclist() {

		$list    = array();
		$list[0] = array( 'id' => 'none', 'name' => 'none', );

		$nics = ovirt_rest_api_call( 'GET', 'networks/' );
		foreach ( $nics as $nic ) {
			if ( $nic->usages->usage == 'vm' ) {
				$vnics = ovirt_rest_api_call( 'GET', 'networks/' . $nic['id'] . '/vnicprofiles' );
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
			array( 'id' => 'desktop', 'name' => 'desktop', ),
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
			array( "text" => 'Same As Backup Disk(s)', ),
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
				"text" => $gruboption . ' Tested on Debian 8+ and CentOS 7+',
			),
			array( "text" => 'Fix Swap:', ),
			array(
				"text" => $swapoption . ' Tested on Debian 8+',
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
					'type'     => 'select',
					'name'     => 'domain',
					'onchange' => 'sb_domain_check();',
					'list'     => $domains,
					'value'    => $settings['storage_domain'],
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

		$disksizeoption = sb_input( array(
			'type'  => 'select',
			'name'  => 'thinprovision',
			'list'  => array(
				array( 'id' => '0', 'name' => 'No', ),
				array( 'id' => '1', 'name' => 'Yes', ),
			),
			'value' => 0,
		) );

		$nics    = sb_niclist();
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
			array( "text" => 'Thin Provisioning:', ),
			array(
				"text" => $disksizeoption,
			),
		);
		sb_table_row( $rowdata );

		$discardoptions = sb_input( array(
			'type'  => 'select',
			'name'  => 'passdiscard',
			'list'  => array(
				array( 'id' => '0', 'name' => 'No', ),
				array( 'id' => '1', 'name' => 'Yes', ),
			),
			'value' => 0,
		) );
		$rowdata        = array(
			array( "text" => 'Pass Discard:', ),
			array(
				"text" => $discardoptions . ' <span id="passdiscardtext"></span>',
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

		?>
        <script>
            $(function () {
                sb_domain_check();
            });
        </script>
		<?php

	}

	function sb_attach_disk( $diskid, $snapshotid, $device ) {

		GLOBAL $settings;

		$xml = '<disk_attachment>
			        <disk id="' . $diskid . '">
			        <snapshot id="' . $snapshotid . '"/>
			        </disk>
			        <active>true</active>
			        <bootable>false</bootable>
			        <interface>' . $settings['drive_interface'] . '</interface>
			        <logical_name>/dev/' . $device . '</logical_name>
			        </disk_attachment>';

		//attach disk to backup vm
		$snap = ovirt_rest_api_call( 'POST', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/', $xml );
		//SLOW DOWN TO MAKE SURE VM MOUNTS DISKS IN ORDER!
		sleep( 2 );

	}

	function sb_vm_disk_array_create( $diskfile, $removeold = 0, $vmuid ) {

		GLOBAL $settings, $extrasshsettings;

		exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vm-disk-list vm=' . $vmuid, $output );

		if ( ! empty( $removeold ) ) {

			if ( file_exists( $diskfile ) ) {
				unlink( $diskfile );
			}
			$itemcount  = 1;
			$diskarray  = array();
			$diskitem   = array();
			$itemnumber = 0;
			foreach ( $output as $item ) {
				if ( $itemcount == 1 ) {
					//Disk 0 VBD:
					$diskitem        = array();
					$diskitem['vbd'] = $item;
				} else if ( $itemcount == 2 ) {
					//uuid ( RO)             : d72329cb-6de0-ef25-18d7-73ccfcc8784c
					$item                 = preg_replace( '/uuid \( [A-Z][A-Z]\).*: /i', '', $item );
					$diskitem['vbd-uuid'] = $item;
				} else if ( $itemcount == 3 ) {
					//    vm-name-label ( RO): Backup of VSRV01 - PDNS
					$item                  = preg_replace( '/ .* \( [A-Z][A-Z]\).*: /i', '', $item );
					$diskitem['vbd-label'] = $item;
				} else if ( $itemcount == 4 ) {
					//       userdevice ( RW): 2
					$item                       = preg_replace( '/ .* userdevice .*\( [A-Z][A-Z]\).*: /i', '', $item );
					$diskitem['vbd-userdevice'] = (integer) $item;
					$itemnumber                 = (integer) $item;
				} else if ( $itemcount == 7 ) {
					//Disk 0 VDI:
					$diskitem['vdi'] = $item;
				} else if ( $itemcount == 8 ) {
					//uuid ( RO)             : d72329cb-6de0-ef25-18d7-73ccfcc8784c
					$item                 = preg_replace( '/uuid \( [A-Z][A-Z]\).*: /i', '', $item );
					$diskitem['vdi-uuid'] = $item;
				} else if ( $itemcount == 9 ) {
					//    vm-name-label ( RO): Backup of VSRV01 - PDNS
					$item                  = preg_replace( '/ .* \( [A-Z][A-Z]\).*: /i', '', $item );
					$diskitem['vdi-label'] = $item;
				} else if ( $itemcount == 10 ) {
					//       userdevice ( RW): 2
					$item                          = preg_replace( '/ .* sr-name-label .*\( [A-Z][A-Z]\).*: /i', '', $item );
					$diskitem['vdi-sr-userdevice'] = $item;
				} else if ( $itemcount == 11 ) {
					//       userdevice ( RW): 2
					$item                         = preg_replace( '/ .* virtual-size .*\( [A-Z][A-Z]\).*: /i', '', $item );
					$diskitem['vdi-virtual-size'] = (integer) $item; //bytes
				} else if ( $itemcount == 13 ) {
					$itemcount                = 0;
					$diskarray[ $itemnumber ] = $diskitem;
				}
				$itemcount ++;
			}
			ksort( $diskarray );

			if ( $cachefiledata = fopen( $diskfile, "w" ) ) {
				$numberofdisks = count( $diskarray );
				fwrite( $cachefiledata, $numberofdisks . "\n" );

				foreach ( $diskarray as $item ) {


					fwrite( $cachefiledata, "-----\n" );
					fwrite( $cachefiledata, $item['vbd-userdevice'] . "\n" );
					fwrite( $cachefiledata, $item['vbd-uuid'] . "\n" );
					fwrite( $cachefiledata, $item['vbd-label'] . "\n" );
					fwrite( $cachefiledata, $item['vdi-uuid'] . "\n" );
					fwrite( $cachefiledata, $item['vdi-label'] . "\n" );
					fwrite( $cachefiledata, $item['vdi-virtual-size'] . "\n" );
					fwrite( $cachefiledata, $vmuid . "\n" );

				}

				fclose( $cachefiledata );

			}
		}

		return $output;
	}

	function sb_vm_disk_array_fetch( $diskfile ) {

		$disk_settings = file_get_contents( $diskfile );

		$disk_settings = explode( "\n", $disk_settings );

		if ( count( $disk_settings ) > 2 ) {
			$diskarray  = array();
			$itemcount  = 1;
			$itemnumber = 0;
			foreach ( $disk_settings as $item ) {
				if ( $itemcount == 3 ) {
					$diskitem                   = array();
					$diskitem['vbd-userdevice'] = (integer) $item;
					$itemnumber                 = $item;
				} else if ( $itemcount == 4 ) {
					$diskitem['vbd-uuid'] = $item;
				} else if ( $itemcount == 5 ) {
					$diskitem['vbd-label'] = $item;
				} else if ( $itemcount == 6 ) {
					$diskitem['vdi-uuid'] = $item;
				} else if ( $itemcount == 7 ) {
					$diskitem['vdi-label'] = $item;
				} else if ( $itemcount == 8 ) {
					$diskitem['vdi-virtual-size'] = (integer) $item;
				} else if ( $itemcount == 9 ) {
					$diskitem['vmuuid']       = $item;
					$itemcount                = 1;
					$diskarray[ $itemnumber ] = $diskitem;
				}
				$itemcount ++;
			}
		} else {
			$diskarray = array();
		}

		return $diskarray;
	}

	function sb_status_set( $status, $stage, $step, $setting1 = '', $setting2 = '', $setting3 = '', $setting4 = '', $setting5 = '', $setting6 = '', $setting7 = '', $setting8 = '', $setting9 = '', $setting10 = '', $setting11 = '', $setting12 = '', $setting13 = '', $setting14 = '', $setting15 = '', $setting16 = '', $setting17 = '', $setting18 = '', $setting19 = '', $setting20 = '', $setting21 = '', $setting22 = '' ) {

		GLOBAL $statusfile;

		//status: ready, backup, restore, xen_migrate

		//if settings left blank, use previous value
		if ( $status != 'ready' ) {
			$oldsettings = sb_status_fetch();
			for ( $i = 1; $i <= 22; $i ++ ) {
				if ( ${"setting$i"} == '' && $oldsettings[ 'setting' . $i ] != '' ) {
					${"setting$i"} = $oldsettings[ 'setting' . $i ];
				}
			}
		}

		if ( $cachefiledata = fopen( $statusfile, "w" ) ) {
			fwrite( $cachefiledata, $status . "\n" );
			fwrite( $cachefiledata, $stage . "\n" );
			fwrite( $cachefiledata, $step . "\n" );
			for ( $i = 1; $i <= 22; $i ++ ) {
				fwrite( $cachefiledata, ${"setting$i"} . "\n" );
			}
			fclose( $cachefiledata );
		}

		sleep( 1 );//allow writes to complete

	}

	function sb_status_fetch() {

		GLOBAL $statusfile;

		if ( ! file_exists( $statusfile ) ) {
			sb_status_set( 'ready', '', 0 );
		}

		$status = file_get_contents( $statusfile );

		$status = explode( "\n", $status );

		//if file is missing lines (old version) then correct
		for ( $i = count( $status ); $i <= 24; $i ++ ) {
			$status[ $i ] = '';
		}

		$statusarray = array(
			'status'    => $status[0],
			'stage'     => $status[1],
			'step'      => $status[2],
			'setting1'  => $status[3],
			'setting2'  => $status[4],
			'setting3'  => $status[5],
			'setting4'  => $status[6],
			'setting5'  => $status[7],
			'setting6'  => $status[8],
			'setting7'  => $status[9],
			'setting8'  => $status[10],
			'setting9'  => $status[11],
			'setting10' => $status[12],
			'setting11' => $status[13],
			'setting12' => $status[14],
			'setting13' => $status[15],
			'setting14' => $status[16],
			'setting15' => $status[17],
			'setting16' => $status[18],
			'setting17' => $status[19],
			'setting18' => $status[20],
			'setting19' => $status[21],
			'setting20' => $status[22],
			'setting21' => $status[23],
			'setting22' => $status[24],
		);

		return $statusarray;

	}

	function sb_next_drive_letter( $driveletter, $output = null ) {

		GLOBAL $settings;

		$thisascii = ord( $driveletter );
		$thisascii ++;
		$nextletter = chr( $thisascii );

		if ( empty( $output ) ) {
			//$settings['drive_type']
			exec( '/sbin/fdisk -l | grep " /dev" | awk \'{ print $2}\'', $output );
		}
		$diskok = 0;
		foreach ( $output as $item ) {
			if ( strpos( $item, 'mapper' ) == false ) {
				$item = str_replace( ':', '', $item );
				$item = str_replace( '/dev/', '', $item );
				if ( $item == $settings['drive_type'] . $nextletter ) {
					$diskok = 1;
				}
			}
		}

		if ( $diskok == 1 || $nextletter == 'z' ) {
			return $nextletter;
		} else {
			return sb_next_drive_letter( $nextletter, $output );
		}
	}

	function sb_not_ready() {

		echo '<br/><br/>Simple Backup is Not Ready. <ul><li><a href="?area=0">Click Here</a> to go to the Status page.<br/><br/></li><li><a href="?area=10">Click Here</a> to go to the Log page to watch current progress.<br/><br/></li></ul>';
	}

	function sb_disk_file_write( $disknumber, $diskname, $vmuuid, $uuid, $bootable, $interface, $size, $path, $vmname, $backupname ) {

		GLOBAL $settings;

		if ( $backupname == '-XEN-' ) {
			$filepath = $settings['mount_migrate'] . '/' . '/Disk' . $disknumber . '.dat';
		} else {
			$filepath = $settings['mount_backups'] . '/' . $vmname . '/' . $vmuuid . '/' . $backupname . '/Disk' . $disknumber . '.dat';
		}

		if ( $diskfile = fopen( $filepath, "w" ) ) {
			fwrite( $diskfile, 'Disk' . $disknumber . "\n" );
			fwrite( $diskfile, $uuid . "\n" );
			fwrite( $diskfile, $diskname . "\n" );
			fwrite( $diskfile, $bootable . "\n" );
			fwrite( $diskfile, $interface . "\n" );
			fwrite( $diskfile, $size . "\n" );
			fwrite( $diskfile, $path . "\n" );
			fwrite( $diskfile, $vmname . "\n" );
			fwrite( $diskfile, $backupname . "\n" );
			fwrite( $diskfile, $vmuuid . "\n" );
			fwrite( $diskfile, ' ' . "\n" );
			fwrite( $diskfile, ' ' . "\n" );
			fwrite( $diskfile, ' ' . "\n" );
			fwrite( $diskfile, ' ' . "\n" );
			fwrite( $diskfile, ' ' . "\n" );
			fwrite( $diskfile, ' ' . "\n" );
			fwrite( $diskfile, ' ' . "\n" );
			fclose( $diskfile );
		}

	}

	function sb_disk_array_fetch( $pathtodisks, $filename = '' ) {

		$diskseek  = 1;
		$diskarray = array();
		if ( file_exists( $pathtodisks ) ) {
			for ( $i = 1; $diskseek == 1; $i ++ ) {

				if ( empty( $filename ) ) {
					$filetograb = '/Disk' . $i . '.dat';
				} else {
					$filetograb = '/' . $filename;
				}

				if ( file_exists( $pathtodisks . $filetograb ) ) {

					$diskdata = file_get_contents( $pathtodisks . $filetograb );

					$diskdata                    = explode( "\n", $diskdata );
					$diskarray["{$diskdata[1]}"] = array(
						'disknumber' => $diskdata[0],
						'uuid'       => $diskdata[1],
						'diskname'   => $diskdata[2],
						'bootable'   => $diskdata[3],
						'interface'  => $diskdata[4],
						'size'       => $diskdata[5],
						'path'       => $diskdata[6],
						'vmname'     => $diskdata[7],
						'backupname' => $diskdata[8],
						'vmuuid'     => $diskdata[9],
						'datapath'   => $pathtodisks,
					);

					if ( ! empty( $filename ) ) {
						$diskseek = 0;
					}

				} else {
					$diskseek = 0;
				}
			}
		}

		return $diskarray;

	}

	function sb_log( $logtext ) {

		GLOBAL $settings;
		if ( ! empty( $settings['backup_log'] ) ) {
			$logtext = str_replace( '"', '', $logtext );

			$logtime = strftime( "[%Y-%m-%d:%H:%M:%S]" );

			sleep( 0.5 );
			exec( 'echo "' . $logtime . ' ' . $logtext . '" >> ' . $settings['backup_log'] );
			sleep( 0.5 );
		}
	}

	function sb_email_log( $logtext ) {

		GLOBAL $vmbackupemaillog;

		$logtext = str_replace( '"', '', $logtext );
//		$logtime = strftime( "[%Y-%m-%d:%H:%M:%S]" );
		exec( 'echo "' . $logtext . '" >> ' . $vmbackupemaillog );
	}

	// This encryption is simply to make password non-clear text.
	// If the system is compromised, the password can easily be decrypted using functions below
	// Not intended to keep password protected from compromised system, just obscured from view
	/**
	 * @param $mykey
	 *
	 * @return string
	 */
	function sb_crypto_iv_key3( $mykey ) {

		$outkey = $mykey . 'SBSimpleEncrypt';
		$outkey = hash( 'md5', $outkey );
		$outkey = substr( $outkey, - 16 );

		return $outkey;
	}

	/**
	 * @param        $string
	 * @param        $salt
	 * @param        $pepper
	 * @param        $mykey
	 * @param string $method
	 *
	 * @return string
	 */
	function sb_encrypt3( $string, $salt, $pepper, $mykey, $method = 'aes256' ) {

		$static_iv = sb_crypto_iv_key3( $mykey );
		$salt      = hash( 'md5', $salt );
		$x         = openssl_encrypt( $string, $method, $salt, 0, $static_iv );
		$pepper    = hash( 'md5', $pepper );
		$x         = openssl_encrypt( $x, $method, $pepper, 0, $static_iv );

		return $x;
	}

	/**
	 * @param        $string
	 * @param        $salt
	 * @param        $pepper
	 * @param        $mykey
	 * @param string $method
	 *
	 * @return string
	 */
	function sb_decrypt3( $string, $salt, $pepper, $mykey, $method = 'aes256' ) {

		$static_iv = sb_crypto_iv_key3( $mykey );
		$pepper    = hash( 'md5', $pepper );
		$x         = openssl_decrypt( $string, $method, $pepper, 0, $static_iv );
		$salt      = hash( 'md5', $salt );
		$x         = openssl_decrypt( $x, $method, $salt, 0, $static_iv );

		return $x;
	}

	function sb_email( $subject, $message ) {

		GLOBAL $settings;

		if ( ! empty( $settings['email'] ) ) {

			if ( empty( $settings['emailfrom'] ) ) {
				$settings['emailfrom'] = $settings['email'];
			}

			$to = $settings['email'];

			$from = $settings['emailfrom'];

			$message = '
<html>
<head>
<title>' . $subject . '</title>
</head>
<body>
' . $message . '
</body>
</html>
';

			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$headers .= 'From: oVirt Simple Backup <' . $from . '>' . "\r\n";
			//		$headers .= 'Cc: myboss@example.com' . "\r\n";

			mail( $to, $subject, $message, $headers );

		}
	}

	function sb_setting_update( $setting, $value ) {

		GLOBAL $settings;

		$settings[ $setting ] = $value;

		$configfile = fopen( "../config.php", "w" ) or die( "Unable to open config file.<br/><br/>Check permissions on config.php!" );
		fwrite( $configfile, '<?php' . "\n" );
		fwrite( $configfile, '$settings = array(' . "\n" );
		fwrite( $configfile, '"vms_to_backup" => array(' );

		foreach ( $settings['vms_to_backup'] as $vm ) {
			fwrite( $configfile, '"' . $vm . '", ' );
		}
		fwrite( $configfile, '),' . "\n" );
		fwrite( $configfile, '"label" => "' . $settings['label'] . '",' . "\n" );
		fwrite( $configfile, '"uuid_backup_engine" => "' . $settings['uuid_backup_engine'] . '",' . "\n" );
		fwrite( $configfile, '"ovirt_url" => "' . $settings['ovirt_url'] . '",' . "\n" );
		fwrite( $configfile, '"ovirt_user" => "' . $settings['ovirt_user'] . '",' . "\n" );
		fwrite( $configfile, '"ovirt_pass" => "' . $settings['ovirt_pass'] . '",' . "\n" );
		fwrite( $configfile, '"mount_backups" => "' . $settings['mount_backups'] . '",' . "\n" );
		fwrite( $configfile, '"drive_type" => "' . $settings['drive_type'] . '",' . "\n" );
		fwrite( $configfile, '"drive_interface" => "' . $settings['drive_interface'] . '",' . "\n" );
		fwrite( $configfile, '"backup_log" => "' . $settings['backup_log'] . '",' . "\n" );
		fwrite( $configfile, '"email" => "' . $settings['email'] . '",' . "\n" );
		fwrite( $configfile, '"emailfrom" => "' . $settings['emailfrom'] . '",' . "\n" );
		fwrite( $configfile, '"retention" => ' . $settings['retention'] . ',' . "\n" );
		fwrite( $configfile, '"storage_domain" => "' . $settings['storage_domain'] . '",' . "\n" );
		fwrite( $configfile, '"cluster" => "' . $settings['cluster'] . '",' . "\n" );
		fwrite( $configfile, '"mount_migrate" => "' . $settings['mount_migrate'] . '",' . "\n" );
		fwrite( $configfile, '"xen_ip" => "' . $settings['xen_ip'] . '",' . "\n" );
		fwrite( $configfile, '"xen_migrate_uuid" => "' . $settings['xen_migrate_uuid'] . '",' . "\n" );
		fwrite( $configfile, '"xen_migrate_ip" => "' . $settings['xen_migrate_ip'] . '",' . "\n" );
		fwrite( $configfile, '"restore_console" => "' . $settings['restore_console'] . '",' . "\n" );
		fwrite( $configfile, '"restore_os" => "' . $settings['restore_os'] . '",' . "\n" );
		fwrite( $configfile, '"restore_vm_type" => "' . $settings['restore_vm_type'] . '",' . "\n" );
		fwrite( $configfile, '"restore_cpu_sockets" => "' . $settings['restore_cpu_sockets'] . '",' . "\n" );
		fwrite( $configfile, '"restore_cpu_cores" => "' . $settings['restore_cpu_cores'] . '",' . "\n" );
		fwrite( $configfile, '"restore_cpu_threads" => "' . $settings['restore_cpu_threads'] . '",' . "\n" );
		fwrite( $configfile, '"tz" => "' . $settings['tz'] . '",' . "\n" );
		fwrite( $configfile, ');' . "\n" );
		fclose( $configfile );

		sleep( .5 );

	}

	/**
	 * @param        $odate
	 * @param string $ifblank
	 *
	 * @param int    $dashesnotslashes
	 *
	 * @return bool|string
	 */
	function odbc_date_format( $odate, $ifblank = "", $dashesnotslashes = 0 ) {

		if ( $dashesnotslashes == 0 ) {
			$odate = date( 'm/d/Y', strtotime( $odate ) );
		} else {
			$odate = date( 'm-d-Y', strtotime( $odate ) );

		}
		if ( "$odate" == "12/31/1969" ) {
			$odate = $ifblank;
		} else if ( "$odate" == "01/01/1970" ) {
			$odate = $ifblank;
		}

		return $odate;
	}

	/**
	 * @param        $odate
	 * @param string $ifblank
	 *
	 * @param int    $noseconds
	 * @param int    $clock24
	 *
	 * @return bool|string
	 */
	function odbc_time_format( $odate, $ifblank = "", $noseconds = 0, $clock24 = 1 ) {

		if ( "{$odate}" == '' ) {
			$odate = $ifblank;
		} else {
			if ( $clock24 == 1 ) {
				if ( $noseconds == 1 ) {
					$odate = date( 'H:i', strtotime( $odate ) );
				} else {
					$odate = date( 'H:i:s', strtotime( $odate ) );
				}
			} else {
				if ( $noseconds == 1 ) {
					$odate = date( 'h:i A', strtotime( $odate ) );
				} else {
					$odate = date( 'h:i:s A', strtotime( $odate ) );
				}
			}
		}

		return $odate;
	}

	function ordinal_suffix( $num ) {

		$num = $num % 100; // protect against large numbers
		if ( $num < 11 || $num > 13 ) {
			switch ( $num % 10 ) {
				case 1:
					return 'st';
				case 2:
					return 'nd';
				case 3:
					return 'rd';
			}
		}

		return 'th';
	}

	function sb_schedule_write( $startdatetime, $enddatetime, $days, $numday, $dom, $schedulename, $vmstobackup, $starttime ) {

		GLOBAL $projectpath;

		$schedulename = preg_replace( '/[^0-9a-zA-Z\-_]/i', '_', $schedulename );

		$filepath = $projectpath . '.automated_backups_schedule_' . $schedulename;

		if ( $diskfile = fopen( $filepath, "w" ) ) {
			fwrite( $diskfile, $startdatetime . "\n" );
			fwrite( $diskfile, $enddatetime . "\n" );
			fwrite( $diskfile, $days . "\n" );
			fwrite( $diskfile, $numday . "\n" );
			fwrite( $diskfile, $dom . "\n" );
			fwrite( $diskfile, $schedulename . "\n" );
			fwrite( $diskfile, $vmstobackup . "\n" );
			fwrite( $diskfile, $starttime . "\n" );
			fwrite( $diskfile, ' ' . "\n" );
			fwrite( $diskfile, ' ' . "\n" );
			fwrite( $diskfile, ' ' . "\n" );
			fwrite( $diskfile, ' ' . "\n" );
			fclose( $diskfile );
		}

	}

	function sb_schedule_fetch( $filename ) {

		GLOBAL $projectpath;

		if ( strpos( $filename, '..' ) !== false ) {
			die();
		}

		if ( strpos( " {$filename}", $projectpath ) == false ) {
			die();
		}

		$schedulex = array();

		if ( file_exists( $filename ) ) {


			$schedule = file_get_contents( $filename );

			$schedule = explode( "\n", $schedule );

			$schedulex = array(
				'startdatetime' => $schedule[0],
				'enddatetime'   => $schedule[1],
				'days'          => $schedule[2],
				'numday'        => $schedule[3],
				'dom'           => $schedule[4],
				'schedulename'  => $schedule[5],
				'vmstobackup'   => $schedule[6],
				'starttime'     => $schedule[7],
			);

		}

		return $schedulex;

	}





	function dateDifference($startdate, $enddate, $returntype = 'days')
	{

		if ($returntype == 'days') {
			$since_start = date_diff(date_create($startdate), date_create($enddate));
			$days = $since_start->days;
			$days += round($since_start->h / 24, 2);
			if ($since_start->invert) $days = -$days;

			return (float)$days;
		} else if ($returntype == 'minutes') {
			$since_start = date_diff(date_create($startdate), date_create($enddate));
			$minutes = $since_start->days * 24 * 60;
			$minutes += $since_start->h * 60;
			$minutes += $since_start->i;
			if ($since_start->invert) $minutes = -$minutes;

			return (float)$minutes;
		} else if ($returntype == 'seconds') {
			$since_start = date_diff(date_create($startdate), date_create($enddate));
			$seconds = $since_start->days * 24 * 60 * 60;
			$seconds += $since_start->h * 60 * 60;
			$seconds += $since_start->i * 60;
			$seconds += $since_start->s;
			if ($since_start->invert) $seconds = -$seconds;

			return (int)$seconds;
		} else if ($returntype == 'hours') {
			$since_start = date_diff(date_create($startdate), date_create($enddate));
			$hours = $since_start->days * 24;
			$hours += $since_start->h;
			$hours += round(($since_start->i / 60), 2);
			if ($since_start->invert) $hours = -$hours;

			return (float)$hours;
		}

	}

	function sb_check_upgrade_version() {

		$regcheck          = file_get_contents( 'https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/server/reg.php' );
		$checkversion      = strpos( $regcheck, 'sb_version =' );
		$checkversionstart = strpos( $regcheck, '\'', $checkversion );
		$checkversionend   = strpos( $regcheck, '\'', $checkversionstart + 1 );
		$newversion        = substr( $regcheck, $checkversionstart + 1, $checkversionend - ( $checkversionstart + 1 ) );

		return $newversion;
	}




	function sb_fetch_upgrade_versioning( $updatenow = 0 ) {

		GLOBAL $projectpath;

		$filearray            = array();
		$filearray['files']   = array();
		$filearray['folders'] = array();
		exec( 'ls ' . $projectpath, $updatefileslevel1 );
		foreach ( $updatefileslevel1 as $updatefileslevel1_item ) {
			if (
				$updatefileslevel1_item != 'version.php' &&
				$updatefileslevel1_item != 'allowed_ips.php' &&
				strpos( " {$updatefileslevel1_item}", 'config' ) == false &&
				strpos( "{$updatefileslevel1_item}", '.dat' ) == false &&
				$updatefileslevel1_item != 'cache'
			) {
				if ( strpos( $updatefileslevel1_item, '.php' ) !== false ) {
					$filearray['files'][] = $updatefileslevel1_item;
				} else {
					$filearray['folders'][ $updatefileslevel1_item ] = $updatefileslevel1_item;
				}
			}
		}

		foreach ( $filearray['folders'] as $folder ) {

			$filearray['folders'][ $folder ]            = array();
			$filearray['folders'][ $folder ]['files']   = array();
			$filearray['folders'][ $folder ]['folders'] = array();

			$updatefileslevel2 = null;
			exec( 'ls ' . $projectpath . '/' . $folder, $updatefileslevel2 );
			foreach ( $updatefileslevel2 as $updatefileslevel2_item ) {
				if (
					strpos( "{$updatefileslevel2_item}", '.dat' ) == false
				) {
					if ( strpos( $updatefileslevel2_item, '.php' ) !== false ) {
						$filearray['folders'][ $folder ]['files'][] = $updatefileslevel2_item;
					} else {
						$filearray['folders'][ $folder ]['folders'][ $updatefileslevel2_item ] = $updatefileslevel2_item;
					}
				}
			}
		}

		//css and js folders
		foreach ( $filearray['folders']['site']['folders'] as $folder ) {

			$filearray['folders']['site']['folders'][ $folder ]          = array();
			$filearray['folders']['site']['folders'][ $folder ]['files'] = array();

			$updatefileslevel2 = null;
			exec( 'ls ' . $projectpath . '/site/' . $folder, $updatefileslevel2 );
			foreach ( $updatefileslevel2 as $updatefileslevel2_item ) {

				if (
					strpos( "{$updatefileslevel2_item}", '.dat' ) == false
				) {
					$filearray['folders']['site']['folders'][ $folder ]['files'][] = $updatefileslevel2_item;
				}
			}
		}

		sb_process_updated_files( $filearray, '', $updatenow );

	}

	function sb_process_updated_files( $filearray, $folder = '', $updatenow ) {

		GLOBAL $projectpath;

		if (!empty($filearray['files'])) {
			foreach ( $filearray['files'] as $item ) {

				$remotefile = file_get_contents( 'https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/server' . $folder . '/' . $item );

				$remotehash = hash( 'md5', $remotefile );

				$localhash = hash_file( 'md5', substr( $projectpath, 0, - 1 ) . $folder . '/' . $item );

				$filematch = ( $remotehash == $localhash ) ? '<span class="statusup">UP TO DATE</span>' : '<span class="statusdown">NEEDS UPDATE</span>';

				if ( $updatenow == 1 ) {
					if ( $remotehash != $localhash ) {
						file_put_contents( substr( $projectpath, 0, - 1 ) . $folder . '/' . $item, $remotefile );
						$filematch = '<span class="statusupdated">!UPDATED!</span>';
					}
				}

				$rowdata = array(
					array(
						"text" => $filematch,
					),
					array(
						"text" => substr( $projectpath, 0, - 1 ) . $folder . '/' . $item,
					),
					array(
						"text" => $localhash,
					),
					array(
						"text" => $remotehash,
					),
				);
				sb_table_row( $rowdata );

			}
			if (!empty($filearray['folders'])) {
				foreach ( $filearray['folders'] as $key => $item ) {
					sb_process_updated_files( $item, $folder . '/' . $key, $updatenow );

				}
			}
		}
	}