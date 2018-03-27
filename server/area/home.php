<div style="width: 100%; z-index: 1000; font-size: 1em;  color: #333; ">

	<?php

		$sb_status = sb_status_fetch();

		$clearitem  = varcheck( "clearitem", '' );
		$clearimage = varcheck( "clearimage", '' );

		if ( preg_match( $UUIDv4, $clearimage ) ) {
			$vmname = varcheck( "vmname", '' );
			$buname = varcheck( "buname", '' );
			unlink( $settings['mount_backups'] . '/' . $vmname . '/' . $clearimage . '/' . $buname . '/image.img' );
			unlink( $settings['mount_backups'] . '/' . $vmname . '/' . $clearimage . '/' . $buname . '/*.dat' );
			$clearitem = $clearimage;
		}

		if ( ! empty( $clearitem ) ) {
			unlink( $projectpath . 'cache/statusfile.dat' );
		}
		if ( ! file_exists( $projectpath . 'cache' ) ) {
			exec( 'mkdir ../cache' );
		}
		if ( ! file_exists( $projectpath . 'cache' ) ) {
			echo 'Error creating cache directory.';
		}

		$sb_status = sb_status_fetch();

		if ( $sb_status['status'] != 'ready' ) {

			sb_pagetitle( 'Status of Running Processes (Simple Backup)' );
			echo '<br/>';

			sb_table_start();

			$rowdata = array(
				array(
					"text"  => "VM",
					"width" => "20%",
				),
				array(
					"text"  => "Process Status",
					"width" => "30%",
				),
				array(
					"text"  => "UUID",
					"width" => "30%",
				),
				array(
					"text"  => "Stage",
					"width" => "20%",
				),
			);
			sb_table_heading( $rowdata );
		} else {
			?>
            <div style="width: 100%; position: absolute; z-index: 0; font-size: 4em; text-align: center; padding: 2em; color: #777; opacity: 0.1;">
                oVirt Simple Backup
            </div>
			<?php
		}

		if ( $sb_status['status'] != 'ready' ) {
			$sb_status['status'] .= ' (<a href="?area=0&clearitem=1">Clear</a>) ';

			/*if (!empty($recoveryurl)){
				$status .= ' (<a href="' . $recoveryurl . '">Recover</a>) ';
			}*/

			$rowdata = array(
				array(
					"text" => $sb_status['setting3'],
				),
				array(
					"text" => $sb_status['status'],
				),
				array(
					"text" => $sb_status['setting1'],
				),
				array(
					"text" => $sb_status['stage'],
				),

			);
			sb_table_row( $rowdata );

			sb_table_end();
		}
	?>
    <script>
        function reloadMe() {
            window.location.reload(true);
        }

        setTimeout(reloadMe, 10 * 1000);

    </script>
	<?php

	?>

</div>