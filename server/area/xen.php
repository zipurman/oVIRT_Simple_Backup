<?php

    sb_pagetitle( 'XenServer(Citrix) Migrate' );

    $sb_status = sb_status_fetch();

    $disktypeget    = sb_check_disks();
    $numberofimages = count( $disktypeget['avaliabledisks'] );

    if ( $numberofimages > 1 ) {
        sb_pagedescription( 'The backup VM has too many disks attached. Please remove all but the OS disk in order to preform a migration.' );
    } else if ( $sb_status['status'] == 'ready' || ! empty( $recovery ) ) {

        $checkdisk = sb_check_disks( 0 );

        sb_pagedescription( 'This tool requires all pieces to be configured correctly. <a href="https://github.com/zipurman/oVIRT_Simple_Backup/blob/master/webUI/README.md" target="_blank">Click Here</a> for the list of what is required for a successful migration.<br/><br/><b><i>NOTE: This utility will ONLY image the disks from the VM from Xen to oVirt. It DOES NOT transfer settings from NICs, MAC Addresses, MEMORY, or ANY settings from Xen Server. You can choose those settings below as required. Once the VM images are migrated, you can then change the require settings in oVirt before launching your newly migrated VM.</i></b>' );

        if ( empty( $action ) ) {

            function sortXenArrayByName($a, $b)
            {
                $a = $a['vmname'];
                $b = $b['vmname'];

                if ($a == $b) return 0;
                return ($a < $b) ? -1 : 1;
            }

            sb_pagedescription( 'The following VMs are listed from Xen on ' . $settings['xen_ip'] . '' );

            sb_table_start();

            $rowdata = array(
                array(
                    "text"  => "VM",
                    "width" => "50%",
                ),
                array(
                    "text"  => "Status",
                    "width" => "10%",
                ),
                array(
                    "text"  => "UUID",
                    "width" => "40%",
                ),
            );
            sb_table_heading( $rowdata );

            //is-control-domain=false (exclude servers)
            exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vm-list is-control-domain=false', $output );

            $xenservervms = array();
            $thisvmarray  = array();
            $rowitem      = 1;
            foreach ( $output as $item ) {

                if ( ! empty( $item ) ) {
                    if ( $rowitem == 1 ) {
                        $uuid                = str_replace( 'uuid ( RO)', '', $item );
                        $uuid                = preg_replace( '/[^0-9a-zA-Z\-]/i', '', $uuid );
                        $thisvmarray['uuid'] = $uuid;
                    } else if ( $rowitem == 2 ) {
                        $vmname                = preg_replace( '/^.*:/i', '', $item );
                        $thisvmarray['vmname'] = $vmname;
                    } else if ( $rowitem == 3 ) {
                        $status                = str_replace( 'power-state ( RO): ', '', $item );
                        $status                = trim( $status );
                        $thisvmarray['status'] = $status;
                        $xenservervms[]        = $thisvmarray;
                        $rowitem = 0;
                    }

                    if ( $rowitem < 3 ) {
                        $rowitem ++;
                    }
                }
            }

            usort($xenservervms, 'sortXenArrayByName');

            foreach ( $xenservervms as $xenservervm ) {

                $vmextratag = ( $xenservervm['uuid'] == $settings['xen_migrate_uuid'] ) ? ' <b>* Migration VM *</b>' : '';

                if ( $xenservervm['status'] == 'running' ) {
                    $status = '<span class="statusup">Running</span>';
                } else {
                    $status = '<span class="statusdown">' . $xenservervm['status'] . '</span>';
                }
                $rowdata = array(
                    array(
                        "text" => '<a href="?area=5&action=xenvmname&vmname=' . $xenservervm['vmname'] . '&xenuuid=' . $xenservervm['uuid'] . '">' . $xenservervm['vmname'] . '</a>' . $vmextratag,
                    ),
                    array(
                        "text" => $status,
                    ),
                    array(
                        "text" => $xenservervm['uuid'],
                    ),
                );
                sb_table_row( $rowdata );
                $rowitem = 0;

            }

            sb_table_end();

        } else if ( $action == 'xenvmname' ) {

            $xenuuid = varcheck( "xenuuid", '' );
            $vmname  = varcheck( "vmname", '' );

            echo '<a href="?area=5">&lt;-- BACK</a><br/><br/>';

            exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vm-disk-list vm=' . $xenuuid, $output );

            $size = 0;
            foreach ( $output as $item ) {
                if ( strpos( $item, 'virtual-size' ) !== false ) {
                    $size = preg_replace( '/^.*:/i', '', $item );
                }
            }

            //		showme( $output );5

            exec( 'ssh root@' . $settings['xen_ip'] . $extrasshsettings . ' xe vm-list is-control-domain=false uuid=' . $xenuuid, $output2 );
            $status = str_replace( 'power-state ( RO): ', '', $output2['2'] );
            $status = trim( $status );
            if ( $status == 'running' ) {
                $status = '<span class="statusup">Running</span>';
            } else {
                $status = '<span class="statusdown">' . $status . '</span>';
            }

            sb_table_start();

            $rowdata = array(
                array( "text" => "", "width" => "15%", ),
                array( "text" => "", "width" => "35%", ),
                array( "text" => "", "width" => "15%", ),
                array( "text" => "", "width" => "35%", ),
            );
            sb_table_heading( $rowdata );

            $rowdata = array(
                array( "text" => 'VM Name:', ),
                array( "text" => $vmname, ),
                array( "text" => 'UUID:', ),
                array( "text" => $xenuuid, ),
            );
            sb_table_row( $rowdata );

            $xendisks  = sb_vm_disk_array_create( $diskfile, 0, $xenuuid );
            $itemcount = 1;
            $sizegbtxt = '';
            $vbdtext   = '';
            $vditext   = '';
            foreach ( $xendisks as $xendisk ) {
                if ( $itemcount == 11 ) {
                    $size                         = preg_replace( '/ .* virtual-size .*\( [A-Z][A-Z]\).*: /i', '', $xendisk );
                    $diskitem['vdi-virtual-size'] = (integer) $xendisk; //bytes
                    $sizegb                       = $size / 1024 / 1024 / 1024;
                    $sizegbtxt                    = ' ' . $sizegb . 'GB ' . $sizegbtxt;
                } else if ( $itemcount == 2 ) {
                    $xendisk = preg_replace( '/uuid \( [A-Z][A-Z]\).*: /i', '', $xendisk );
                    $vbdtext .= $xendisk . '<br/>';
                } else if ( $itemcount == 8 ) {
                    $xendisk = preg_replace( '/uuid \( [A-Z][A-Z]\).*: /i', '', $xendisk );
                    $vditext .= $xendisk . '<br/>';
                } else if ( $itemcount == 13 ) {
                    $itemcount = 0;
                }
                $itemcount ++;
            }
            $rowdata = array(
                array( "text" => 'Disk Size(s):', ),
                array( "text" => $sizegbtxt . '', ),
                array( "text" => 'Status:', ),
                array( "text" => $status, ),
            );
            sb_table_row( $rowdata );

            if ( empty( $output['1'] ) ) {
                $output['1'] = '???';
            }
            if ( empty( $output['7'] ) ) {
                $output['7'] = '???';
            }
            $vbd_uuid = $output['1'];
            $vdi_uuid = $output['7'];
            $vbd_uuid = preg_replace( '/^.*:/i', '', $vbd_uuid );
            $vdi_uuid = preg_replace( '/^.*:/i', '', $vdi_uuid );
            $vbd_uuid = str_replace( ' ', '', $vbd_uuid );
            $vdi_uuid = str_replace( ' ', '', $vdi_uuid );
            $rowdata  = array(
                array( "text" => 'VBD UUID(s):', ),
                array( "text" => $vbdtext, ),
                array( "text" => 'VDI UUID(s):', ),
                array( "text" => $vditext, ),
            );
            sb_table_row( $rowdata );

            $rowdata = array(
                array( "text" => 'Re-Start Xen VM After Imaging:', ),
                array(
                    "text" => sb_input( array(
                        'type'  => 'select',
                        'name'  => 'option_restartxenyn',
                        'list'  => array(
                            array( 'id' => '0', 'name' => 'No', ),
                            array( 'id' => '1', 'name' => 'Yes', ),
                        ),
                        'value' => 0,
                    ) ),
                ),
                array( "text" => '', ),
                array( "text" => '', ),
            );
            sb_table_row( $rowdata );

            sb_table_end();

            sb_new_vm_settings( 'Same Sizes in ', 2, 2 );

            echo sb_input( array( 'type' => 'hidden', 'name' => 'disksize', 'value' => '0', ) );
            echo sb_input( array( 'type' => 'hidden', 'name' => 'vmname', 'value' => trim( $vmname ), ) );
            echo sb_input( array( 'type' => 'hidden', 'name' => 'xenuuid', 'value' => $xenuuid, ) );
            echo sb_input( array( 'type' => 'hidden', 'name' => 'vbd_uuid', 'value' => $vbd_uuid, ) );
            echo sb_input( array( 'type' => 'hidden', 'name' => 'vdi_uuid', 'value' => $vdi_uuid, ) );
            echo sb_input( array( 'type' => 'hidden', 'name' => 'buname', 'value' => '-migrate-', ) );
            echo sb_input( array(
                'type'  => 'hidden',
                'name'  => 'vmuuid',
                'value' => $settings['uuid_backup_engine'],
            ) );//not used - just for validation areas
            echo sb_input( array( 'type' => 'hidden', 'name' => 'sb_area', 'value' => 4, ) );

            sb_pagedescription( '<b><i>Migration process. The script will do the following:</i></b>
				<ol>
				<li>Shutdown Xen VM</li>
				<li>Remove Disk From Xen VM</li>
				<li>Shutdown Xen SimpleBackup VM</li>
				<li>Attach Xen Disk to Xen SimpleBackup VM</li>
				<li>Start Xen SimpleBackup VM</li>
				<li>Image disk to ' . $settings['mount_migrate'] . '</li>
				<li>Shutdown Xen SimpleBackup VM</li>
				<li>Remove Disk From Xen SimpleBackup VM</li>
				<li>Re-Attach Xen Disk to original Xen VM</li>
				<li>Optionally Start Xen  VM</li>
				<li>Restore Disk Image to New oVirt VM</li>
				</ol>
				<b>NOTE: If the migrate fails you may have to manually re-attach disks to your original VM.<br/> * * * * Make note of the UUIDs above to assist with that operation if required. * * * *</b>
				' );

            if ( empty( $vbd_uuid ) || empty( $vdi_uuid ) ) {
                echo 'No Attached Disks';
            } else {


                if ( empty( $recovery ) ) {

                    sb_gobutton( 'Migrate This Xen VM Now', '', 'sb_migrateXenStart();' );

                } else {

                    //set values

                    $jsstring = '';

                    if ( $sb_status['status'] == 'xen_migrate' ) {

                        $jsstring .= 'sb_update_statusbox(\'restorestatus\', \'Resuming Migration ...\');';

                        if ( $sb_status['stage'] == 'xen_shutdown' && $sb_status['step'] == '0' ) {
                            $jsstring .= 'sb_xen_shutdown();';
                        } else if ( $sb_status['stage'] == 'xen_remove_vbd' && $sb_status['step'] == '0' ) {
                            $jsstring .= 'sb_xen_migrate_progress = 1;';
                            $jsstring .= 'sb_xen_removedisk();';
                        } else if ( $sb_status['stage'] == 'xen_shutdown' && $sb_status['step'] == 2 ) {
                            $jsstring .= 'sb_xen_migrate_progress = 2;';
                            $jsstring .= 'sb_xen_shutdown();';
                        } else if ( $sb_status['stage'] == 'xen_add_vbd' && $sb_status['step'] == 0 ) {
                            $jsstring .= 'sb_xen_migrate_progress = 2;';
                            $jsstring .= 'sb_xen_attachdisk();';
                        } else if ( $sb_status['stage'] == 'xen_start' && $sb_status['step'] < 2 ) {
                            $jsstring .= 'sb_xen_migrate_progress = 2;';
                            $jsstring .= 'sb_xen_startup();';
                        } else if ( $sb_status['stage'] == 'xen_imaging' ) {
                            $jsstring .= 'sb_xen_migrate_progress = 2;';
                            $jsstring .= 'sb_xen_imagedisk();';
                        } else if ( $sb_status['stage'] == 'xen_shutdown' && $sb_status['step'] == 4 ) {
                            $jsstring .= 'sb_xen_migrate_progress = 3;';
                            $jsstring .= 'sb_xen_shutdown();';
                        } else if ( $sb_status['stage'] == 'xen_remove_vbd' && $sb_status['step'] == 3 ) {
                            $jsstring .= 'sb_xen_migrate_progress = 3;';
                            $jsstring .= 'sb_xen_removedisk();';
                        } else if ( $sb_status['stage'] == 'xen_add_vbd' && $sb_status['step'] == 3 ) {
                            $jsstring .= 'sb_xen_migrate_progress = 3;';
                            $jsstring .= 'sb_xen_attachdisk();';
                        } else if ( $sb_status['stage'] == 'xen_start' && $sb_status['step'] == 2 && $sb_status['setting20'] == 1 ) {
                            $jsstring .= 'sb_xen_migrate_progress = 3;';
                            $jsstring .= 'sb_xen_startup();';
                        } else if ( $sb_status['status'] == 'xen_migrate' && $sb_status['stage'] == 'xen_start' && $sb_status['step'] == 2 ) {
                            $jsstring .= 'sb_xen_migrate_progress = 3;';
                            $jsstring .= 'checkRestoreNow(0);';
                        } else if ( $sb_status['status'] == 'xen_restore' && $sb_status['stage'] == 'start' ) {
                            $jsstring .= 'sb_xen_migrate_progress = 3;';
                            $jsstring .= 'checkRestoreNow(0);';
                        }
                    } else if ( $sb_status['status'] == 'restore' ) {

                        $sb_status['setting20'] = 0;
                        $jsstring               .= 'sb_xen_migrate_progress = 3;';

                        $jsstring .= 'sb_update_statusbox(\'restorestatus\', \'Resuming Migration Restore ...\');';
                        if ( $sb_status['stage'] == 'disk_create' ) {
                            $jsstring .= '    sb_restore_disk_create();';
                        } else if ( $sb_status['stage'] == 'restore_imaging' ) {
                            $jsstring .= 'sb_restore_imaging();';
                        } else if ( $sb_status['stage'] == 'fixes' ) {
                            $jsstring .= 'sb_restore_run_options();';
                        } else if ( $sb_status['stage'] == 'disk_detatch' ) {
                            $jsstring .= 'sb_disk_detatch();';
                        } else if ( $sb_status['stage'] == 'disk_attach' ) {
                            $jsstring .= 'sb_disk_attach();';
                        }
                    }

                    ?>

                    <script>
                        $(function () {

                            $("#restorenewname").val("<?php echo $sb_status['setting4']; ?>");
                            $("#domain").val("<?php echo $sb_status['setting8']; ?>");
                            $("#os").val("<?php echo $sb_status['setting10']; ?>");
                            $("#nic1").val("<?php echo $sb_status['setting11']; ?>");
                            $("#vmtype").val("<?php echo $sb_status['setting12']; ?>");
                            $("#cluster").val("<?php echo $sb_status['setting13']; ?>");
                            $("#console").val("<?php echo $sb_status['setting14']; ?>");
                            $("#memory").val("<?php echo round( $sb_status['setting15'] / 1024 / 1024 / 1024 ); ?>");
                            $("#memory_max").val("<?php echo round( $sb_status['setting16'] / 1024 / 1024 / 1024 ); ?>");
                            $("#sockets").val("<?php echo $sb_status['setting17']; ?>");
                            $("#cores").val("<?php echo $sb_status['setting18']; ?>");
                            $("#threads").val("<?php echo $sb_status['setting19']; ?>");
                            $("#option_restartxenyn").val("<?php echo $sb_status['setting20']; ?>");
                            $("#thinprovision").val("<?php echo $sb_status['setting21']; ?>");
                            $("#passdiscard").val("<?php echo $sb_status['setting22']; ?>");
                            <?php
                            if ( strpos( $sb_status['setting6'], 'fixgrub' ) !== false ) {
                            ?>
                            $("#option_fixgrub").val(1);
                            <?php
                            } else {
                            ?>
                            $("#option_fixgrub").val(0);
                            <?php
                            }

                            if ( strpos( $sb_status['setting6'], 'fixswap' ) !== false ) {
                            ?>
                            $("#option_fixswap").val(1);
                            <?php
                            } else {
                            ?>
                            $("#option_fixswap").val(0);
                            <?php
                            }

                            echo $jsstring;

                            ?>

                        });
                    </script>
                    <?php
                }

            }
            sb_progress_bar( 'xenbar' );
            sb_progress_bar( 'xenimagingbar' );
            sb_progress_bar( 'creatediskstatus' );
            sb_progress_bar( 'imagingbar' );

            sb_status_box( 'restorestatus' );

        }

    } else {
        sb_not_ready();
    }