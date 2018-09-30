<div id="sb_menu">

    <?php

        if ( ( empty( ! $settings['uuid_backup_engine'] ) && ! empty( $settings['ovirt_pass'] ) ) || ! empty( $savestep ) ) {
        //check version
        if ( file_exists( $lastversioncheckfile ) ) {
            if ( time() - filemtime( $lastversioncheckfile ) > 3600 ) {
                $checkmyversion = 1;//60 minutes old
                exec( 'rm ' . $lastversioncheckfile );
            } else {
                $checkmyversion = 0;
            }
        } else {
            $checkmyversion = 1;
        }
        if ( ! empty( $checkmyversion ) ) {
            $newversion = sb_check_upgrade_version();

            if ( $newversion != $sb_version ) {
                exec( 'echo 1 > ' . $lastversioncheckfile );

            } else {
                exec( 'echo 0 > ' . $lastversioncheckfile );

            }
        }

        exec( 'cat ' . $lastversioncheckfile, $versionoutput );

        $upgradeclass = ! empty( $versionoutput[0] ) ? 'sm_upgrade_on' : 'sm_upgrade_off';

        $menuspace = 0;
        if ( empty( $settings['uuid_backup_engine'] ) ) {
            $snapshotcheck = array();
        } else {
            $snapshotcheck = ovirt_rest_api_call( 'GET', 'vms/' . $settings['uuid_backup_engine'] . '/snapshots' );
        }

        //        showme( $snapshotcheck );

        if ( count( $snapshotcheck ) > 1 ) {
            $showmsg = 0;
            if ( strpos( ' ' . $snapshotcheck->body, 'access_denied' ) !== false ) {
                echo '<div style="line-height: 1.2em; background: #555; width: 80%; padding: 10px; color: yellow; float: left;">';
                echo '' . $snapshotcheck->body;
                echo '</div>';
                $showmsg = 1;
            }
            if ( empty( $showmsg ) ) {
                echo '<div style="line-height: 1.2em; background: #555; width: 80%; padding: 10px; color: yellow; float: left;">YOU CANNOT HAVE SNAPSHOTS ON YOUR BACKUP ENGINE OR IT CANNOT MOUNT DISKS DYNAMICALLY.</div>';
            }
            $menuspace = 1;
        } else if ( empty( $settings['storage_domain'] ) ) {
            if ( $area != 99 ) {
                echo '<div style="line-height: 1.2em; background: #555; width: 50%; padding: 10px; color: yellow; float: left;">OVIRT STORAGE DOMAIN NOT SET.</div>';
                $menuspace = 1;
            }
        } else {

            if (! empty( $settings['drive_type'] )) {
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
        }
    ?>

    <div class="sm_menu_item sm_menu_item_right<?php if ( $area == 99 ) {
        echo ' sm_menu_active';
    } ?>"><a href="?area=99">Settings</a></div>
    <div class="sm_menu_item sm_menu_item_right<?php if ( $area == 98 ) {
        echo ' sm_menu_active';
    } ?>"><a href="?area=98">
            <div class="<?php echo $upgradeclass; ?>" title="Updates Available">!</div>
            Updates</a></div>


    <?php
        if ( ! empty( $menuspace ) ) {
            ?>
            <div class="clear" style="height: 30px;"></div>
            <?php
        }
    ?>
</div>
<?php
    } else {
    ?>
    <form method="POST" action="index.php">
        FQDN of oVirt Engine: <input name="ovirt_url" type="text" size="40"
                                     value="<?php echo $settings['ovirt_url']; ?>"><br/><br/>
        BACKUP ENGINE UUID (This VM): <input name="uuid_backup_engine" type="text" size="40"
                                             value="<?php echo $settings['uuid_backup_engine']; ?>"><br/><br/>
        Username: <input name="ovirt_user" type="text" size="20" value="<?php echo $settings['ovirt_user']; ?>">
        <br/><br/>
        Password: <input name="ovirt_pass" type="password" size="20" value="<?php echo $settings['ovirt_pass']; ?>">
        <br/><br/>
        <input name="area" type="hidden" size="16" value="99">
        <input name="savestep" type="hidden" value="1">
        <input name="savestepstart" type="hidden" value="1">
        <input type="submit" style="width: 100px;" value="SAVE">

    </form>
    <?php

} ?>
