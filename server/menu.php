<div id="sb_menu">

	<?php

		$menuspace = 0;
		if ( empty( $settings['uuid_backup_engine'] ) ) {
			$snapshotcheck = array();
		} else {
			$snapshotcheck = ovirt_rest_api_call( 'GET', 'vms/' . $settings['uuid_backup_engine'] . '/snapshots' );
		}
		if ( count( $snapshotcheck ) > 1 ) {
			echo '<div style="line-height: 1.2em; background: #555; width: 80%; padding: 10px; color: yellow; float: left;">YOU CANNOT HAVE SNAPSHOTS ON YOUR BACKUP ENGINE OR IT CANNOT MOUNT DISKS DYNAMICALLY. PLEASE DELETE ALL SNAPSHOTS AND TRY AGAIN.</div>';
			$menuspace = 1;
		} else if ( empty( $settings['storage_domain'] ) ) {
			if ( $area != 99 ) {
				echo '<div style="line-height: 1.2em; background: #555; width: 50%; padding: 10px; color: yellow; float: left;">OVIRT STORAGE DOMAIN NOT SET.</div>';
				$menuspace = 1;
			}
		} else {
			?>

            <div class="sm_menu_item<?php if ( $area == 0 ) {
				echo ' sm_menu_active';
			} ?>"><a href="?area=0">Status</a></div>
            <div class="sm_menu_item<?php if ( $area == 2 ) {
				echo ' sm_menu_active';
			} ?>"><a href="?area=2">Single Backup</a></div>
            <div class="sm_menu_item<?php if ( $area == 3 ) {
				echo ' sm_menu_active';
			} ?>"><a href="?area=3">Restore</a></div>

			<?php
			if ( ! empty( $settings['xen_ip'] ) ) {
				?>
                <div class="sm_menu_item<?php if ( $area == 5 ) {
					echo ' sm_menu_active';
				} ?>"><a href="?area=5">Xen Migrate</a></div>
				<?php
			}

			?>
            <div class="sm_menu_item<?php if ( $area == 10 ) {
				echo ' sm_menu_active';
			} ?>"><a href="?area=10">Logs</a></div>


            <div class="sm_menu_item<?php if ( $area == 1 ) {
				echo ' sm_menu_active';
			} ?>"><a href="?area=1">Scheduled Backups</a></div>

			<?php
		}
	?>
    <div class="sm_menu_item sm_menu_item_right<?php if ( $area == 99 ) {
		echo ' sm_menu_active';
	} ?>"><a href="?area=99">Settings</a></div>
	<?php
		if ( ! empty( $menuspace ) ) {
			?>
            <div class="clear" style="height: 30px;"></div>
			<?php
		}
	?>
</div>
