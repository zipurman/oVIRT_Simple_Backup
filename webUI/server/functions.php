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

	function sb_check_disks($alloweddisks) {

		GLOBAL $settings, $area;

		exec( 'fdisk -l | grep "Disk /dev" | awk \'{ print $2}\'', $output );
		$disks = 0;
		$avaliabledisks = array();
		$avaliablediskstext = '';
		$lastdev = 'nodiskselected';
		foreach ( $output as $item ) {
			$item = str_replace( ':', '', $item );
			$item = str_replace( '/dev/', '', $item );

			if ( $item != $settings['drive_type'] . 'a' ) {
				$disks ++;
				$avaliabledisks[$item] = $item;
				$avaliablediskstext .= '(' . $item . ')';
				$lastdev = $item;
			}
		}

		$return = array(
			"disks" => $disks,
			"avaliabledisks" => $avaliabledisks,
			"avaliablediskstext" => $avaliablediskstext,
			"lastdev" => $lastdev,
		);

		if ($return['disks'] > $alloweddisks){
			die('You must disconnect extra disks from the Backup VM. ' . $return['avaliablediskstext'] . '<br/><a href="?area=' . $area . '&disconnectdisks=1">Click Here to have the script disconnect the disk.</a>');
		}

		return $return;

	}

	function sb_xen_check_disks($alloweddisks) {

		GLOBAL $settings, $area;

		exec( 'ssh root@' . $settings['xen_migrate_ip'] . ' fdisk -l | grep "Disk /dev" | awk \'{ print $2}\'', $output );
		$disks = 0;
		$avaliabledisks = array();
		$avaliablediskstext = '';
		$lastdev = '';
		foreach ( $output as $item ) {
			$item = str_replace( ':', '', $item );
			$item = str_replace( '/dev/', '', $item );

			if ( $item != 'xvda' ) {
				$disks ++;
				$avaliabledisks[$item] = $item;
				$avaliablediskstext .= '(' . $item . ')';
				$lastdev = $item;
			}
		}

		$return = array(
			"disks" => $disks,
			"avaliabledisks" => $avaliabledisks,
			"avaliablediskstext" => $avaliablediskstext,
			"lastdev" => $lastdev,
		);

		return $return;

	}