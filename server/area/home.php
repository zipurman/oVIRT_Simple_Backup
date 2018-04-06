<?php

	$automatedinprocess = ( file_exists( $vmbackupinprocessfile ) ) ? 1 : 0;


	if ( ! empty( $automatedinprocess ) ) {
	    echo '<div style="position: absolute; bottom: 10px;">You can clear the automated backup if it has failed by deleting the file: ' . $vmbackupinprocessfile . '</div>';
	}
?>

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


		if ( $sb_status['status'] != 'ready' && empty( $automatedinprocess ) ) {

			sb_pagetitle( 'Status of Running Processes (Simple Backup)' );
			sb_pagedescription( 'After 5 minutes of inactivity, you will be able to clear failed jobs.' );

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
		} else if ( ! empty( $automatedinprocess ) ) {

			?>
            <div style="width: 100%; position: absolute; z-index: 0; font-size: 4em; text-align: center; padding: 2em; color: #777; opacity: 0.3;">
                AUTOMATED BACKUP IN PROCESS<br/>
                PLEASE WAIT
            </div>

			<?php

		} else {
			?>
            <div style="width: 100%; position: absolute; z-index: 0; font-size: 4em; text-align: center; padding: 2em; color: #777; opacity: 0.1;">
                oVirt Simple Backup
            </div>
			<?php
		}

		$lasttimechanged = time() - filemtime( $statusfile );

		$statuslink = $sb_status['status'] . ' (' . $sb_status['stage'] . ') ';

		if ( $sb_status['status'] != 'ready' && empty( $automatedinprocess ) ) {


			if ( $lasttimechanged > 300 ) {
				$statuslink .= ' (<a href="javascript: if (confirm(\'Clear/Cancel this process?\')){window.location=\'?area=0&clearitem=1\'}">Clear</a>) ';
			}

			//figure out where to go
			if ( $sb_status['status'] == 'backup' && $sb_status['setting1'] != '-XEN-' ) {
				$statuslink .= ' <a href="?area=2&recovery=1&action=select&vm=' . $sb_status['setting1'] . '">View Progress</a>';
			} else if ( $sb_status['status'] == 'restore' && $sb_status['setting1'] != '-XEN-' ) {
				$statuslink .= ' <a href="?area=3&recovery=1&action=selectedbackup&vmname=' . $sb_status['setting1'] . '&uuid=' . $sb_status['setting2'] . '&buname=' . $sb_status['setting3'] . '">View Progress</a>';
			} else if ( $sb_status['status'] == 'xen_migrate' || $sb_status['status'] == 'xen_restore' || $sb_status['status'] == 'restore' && $sb_status['setting1'] == '-XEN-' ) {
				$statuslink .= ' <a href="?area=5&recovery=1&action=xenvmname&vmname=' . trim( $sb_status['setting1'] ) . '&xenuuid=' . $sb_status['setting2'] . '">View Progress</a>';
			}

			$rowdata = array(
				array(
					"text" => $sb_status['setting3'],
				),
				array(
					"text" => $statuslink,
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

        setTimeout(reloadMe, 30 * 1000);

    </script>

</div>
