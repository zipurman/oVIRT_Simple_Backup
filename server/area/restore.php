<?php

    sb_pagetitle( 'Restore' );

    $sb_status = sb_status_fetch();

    $disktypeget    = sb_check_disks();
    $numberofimages = count( $disktypeget['avaliabledisks'] );

    if ( $numberofimages > 1 ) {
        sb_pagedescription( 'The backup VM has too many disks attached. Please remove all but the OS disk in order to preform a restore.' );
    } else if ( $sb_status['status'] == 'ready' || ! empty( $recovery ) ) {


        if ( empty( $action ) ) {

            sb_pagedescription( 'The following backups folders are available in ' . $settings['mount_backups'] . '.' );

            sb_table_start();

            $rowdata = array(
                array(
                    "text"  => "VM Folder Name",
                    "width" => "100%",
                ),
            );
            sb_table_heading( $rowdata );

            exec( 'ls ' . $settings['mount_backups'] . '', $files );

            foreach ( $files as $file ) {
                if ( $file != 'lost+found' && $file != '#recycle' ) {
                    $rowdata = array(
                        array(
                            "text" => '<a href="?area=3&action=vmbackups&vmname=' . $file . '">' . $file . '</a>',
                        ),
                    );
                    sb_table_row( $rowdata );
                }
            }

            sb_table_end();

        } else if ( $action == 'vmbackups' ) {

            $vmname = varcheck( "vmname", '' );
            $vmname = preg_replace( '/[^0-9a-zA-Z\-_\.]/i', '', $vmname );

            sb_pagedescription( 'The following backups UUID folders are available in ' . $settings['mount_backups'] . '/' . $vmname . '.' );

            sb_table_start();

            $rowdata = array(
                array(
                    "text"  => "VM Folder Name",
                    "width" => "100%",
                ),
            );
            sb_table_heading( $rowdata );

            exec( 'ls ' . $settings['mount_backups'] . '/' . $vmname, $files );

            $rowdata = array(
                array(
                    "text" => '<a href="?area=3' . '">..</a>',
                ),
            );
            sb_table_row( $rowdata );

            foreach ( $files as $file ) {
                $rowdata = array(
                    array(
                        "text" => '<a href="?area=3&action=uuidbackups&vmname=' . $vmname . '&uuid=' . $file . '">' . $file . '</a>',
                    ),
                );
                sb_table_row( $rowdata );
            }

            sb_table_end();

        } else if ( $action == 'uuidbackups' ) {

            $vmname = varcheck( "vmname", '' );
            $vmname = preg_replace( '/[^0-9a-zA-Z\-_\.]/i', '', $vmname );
            $uuid   = varcheck( "uuid", '' );

            sb_pagedescription( 'The following backup dates are available in ' . $settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '.' );

            if ( preg_match( $UUIDv4, $uuid ) ) {
                sb_table_start();

                $rowdata = array(
                    array(
                        "text"  => "VM Folder Name",
                        "width" => "100%",
                    ),
                );
                sb_table_heading( $rowdata );

                $rowdata = array(
                    array(
                        "text" => '<a href="?area=3&action=vmbackups&vmname=' . $vmname . '">..</a>',
                    ),
                );
                sb_table_row( $rowdata );

                exec( 'ls ' . $settings['mount_backups'] . '/' . $vmname . '/' . $uuid, $files );


                foreach ( $files as $file ) {

                    $fileshow  = '';
                    $fileshowx = str_replace( $settings['label'], '', $file );
                    $fileshow  .= substr( $fileshowx, 4, 2 ) . '/' . substr( $fileshowx, 6, 2 ) . '/' . substr( $fileshowx, 0, 4 );
                    $fileshow  .= ' ' . substr( $fileshowx, 9, 2 ) . ':' . substr( $fileshowx, 11, 2 ) . ':' . substr( $fileshowx, 13, 2 );
                    $fileage = date ("F d Y H:i:s.", filemtime($settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '/' . $file));

                    $minutesago = dateDifference( date ("Y-m-d H:i:s", filemtime($settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '/' . $file)), $thetimefull, 'minutes');
                    $timeago = '';
                    if ($minutesago < 60){
                        $timeago = $minutesago . ' Minutes Ago';
                    } else if ($minutesago < 1440){
                        $timeago = round($minutesago / 60, 1) . ' Hours Ago';
                    } else if ($minutesago < 1440 * 365){
                        $daysago = dateDifference( date ("Y-m-d H:i:s", filemtime($settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '/' . $file)), $thetimefull, 'days');
                        $timeago = $daysago . ' Day(s) Ago';
                    } else {
                        $timeago = '';
                        $fileage = '';
                    }


                    $rowdata = array(
                        array(
                            "text" => '(' . $fileage . ') <a href="?area=3&action=selectedbackup&vmname=' . $vmname . '&uuid=' . $uuid . '&buname=' . $file . '">' . 'BACKUP ' . $fileshow . ' (' . $file . ')</a> (' . $timeago . ')',
                        ),
                    );
                    sb_table_row( $rowdata );
                }

                sb_table_end();

            } else {
                echo 'Invalid UUID';
            }

        } else if ( $action == 'selectedbackup' ) {

            $vmname = varcheck( "vmname", '' );
            $vmname = preg_replace( '/[^0-9a-zA-Z\-_\.]/i', '', $vmname );
            $buname = varcheck( "buname", '' );
            $buname = preg_replace( '/[^0-9a-zA-Z\-_\.]/i', '', $buname );
            $uuid   = varcheck( "uuid", '' );

            sb_pagedescription( 'The following is the selected backup to restore from ' . $settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '/' . $buname . '.' );

            if ( preg_match( $UUIDv4, $uuid ) ) {

                $xmlfile   = $settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '/' . $buname . '/data.xml';
                $imagefile = $settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '/' . $buname . '/Disk1.img';

                $compressionname = '';
                $compressiontext = '';

                if ( file_exists( $imagefile . '.gz' ) ) {
                    $imagefile       .= '.gz';
                    $compressionname .= '.gz';
                    $compressiontext = ' (gz compressed)';
                } else if ( file_exists( $imagefile . '.lzo' ) ) {
                    $imagefile       .= '.lzo';
                    $compressionname .= '.lzo';
                    $compressiontext = ' (lzo compressed)';
                } else if ( file_exists( $imagefile . '.bzip2' ) ) {
                    $imagefile       .= '.bzip2';
                    $compressionname .= '.bzip2';
                    $compressiontext = ' (bzip2 compressed)';
                } else if ( file_exists( $imagefile . '.pbzip2' ) ) {
                    $imagefile       .= '.pbzip2';
                    $compressionname .= '.pbzip2';
                    $compressiontext = ' (pbzip2 compressed)';
                }

                sb_test_image_file( $imagefile );


                if ( file_exists( $xmlfile ) && file_exists( $imagefile ) ) {
                    $xmldata = file_get_contents( $xmlfile );
                    $xml     = simplexml_load_string( $xmldata );

                    $diskslisttext = '';
                    $disksleft     = 1;
                    for ( $i = 1; $disksleft == 1; $i ++ ) {
                        $imagefile = $settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '/' . $buname . '/Disk' . $i . '.img' . $compressionname;

                        if ( file_exists( $imagefile ) ) {

                            $diskdata = sb_disk_array_fetch( $settings['mount_backups'] . '/' . $vmname . '/' . $uuid . '/' . $buname, '/Disk' . $i . '.dat' );

                            foreach ( $diskdata as $diskdatum ) {
                                $vmsize = round( ( $diskdatum['size'] / 1024 / 1024 / 1024 ), 2 );
                            }

                            $compressiontextx = ' ';
                            if ( ! empty( $compressiontext ) ) {
                                $compressedsize  = round( filesize( $imagefile ) / 1024 / 1024 / 1024, 2 );
                                $comprate        = 100 - round( ( ( $compressedsize / $vmsize ) * 100 ), 2 );
                                $compressiontextx = '(' . $comprate . '% ' . $compressiontext . ' = ';
                                if (empty($compressedsize)){
                                    if (empty(round(filesize( $imagefile ) / 1024 / 1024, 2))){
                                        $compressiontextx .= ( round(filesize( $imagefile ) / 1024 ,2) ) . ' KB)';
                                    } else {
                                        $compressiontextx .= ( round(filesize( $imagefile ) / 1024 / 1024 , 2)) . ' MB)';
                                    }
                                } else {
                                    $compressiontextx .= $compressedsize . ' GB)';
                                }
                            }


                            $diskslisttext .= ( empty( $diskslisttext ) ) ? '' : '<br/>';
                            $diskslisttext .= 'Disk' . $i . ' (' . $vmsize . 'GB) ' . $compressiontextx;

                            foreach ( $diskdata as $diskdatum ) {
                                if ( $diskdatum['bootable'] == 'true' ) {
                                    $diskslisttext .= ' (bootable) ';
                                }
                            }

                        } else {
                            $disksleft = 0;
                        }

                    }

                    //				showme( $xml );
                    sb_table_start();

                    $rowdata = array(
                        array( "text" => "", "width" => "10%", ),
                        array( "text" => "", "width" => "40%", ),
                        array( "text" => "", "width" => "10%", ),
                        array( "text" => "", "width" => "40%", ),
                    );
                    sb_table_heading( $rowdata );

                    $rowdata = array(
                        array( "text" => 'VM Name:', ),
                        array( "text" => $xml->Content->Name . ' (' . $buname . ')', ),
                        array( "text" => 'Creation Date:', ),
                        array( "text" => $xml->Content->CreationDate, ),
                    );
                    sb_table_row( $rowdata );

                    $rowdata = array(
                        array( "text" => 'Disk(s):', ),
                        array( "text" => $diskslisttext, ),
                        array( "text" => '', ),
                        array( "text" => '', ),
                    );
                    sb_table_row( $rowdata );

                    $rowdata = array(
                        array( "text" => 'Min Memory:', ),
                        array( "text" => $xml->Content->MinAllocatedMem / 1024 . ' GB', ),
                        array( "text" => 'Max Memory:', ),
                        array( "text" => $xml->Content->MaxMemorySizeMb / 1024 . ' GB', ),
                    );
                    sb_table_row( $rowdata );

                    $rowdata = array(
                        array( "text" => 'Cluster:', ),
                        array( "text" => $xml->Content->ClusterName, ),
                        array( "text" => 'Template:', ),
                        array( "text" => $xml->Content->TemplateName, ),
                    );
                    sb_table_row( $rowdata );

                    sb_table_end();

                    if ( ! empty( sb_test_image_file( $imagefile, 1 ) ) ) {

                        sb_new_vm_settings( 0, $xml->Content->MinAllocatedMem / 1024, $xml->Content->MaxMemorySizeMb / 1024 );

                        echo sb_input( array(
                            'type'  => 'hidden',
                            'name'  => 'vmname',
                            'value' => $xml->Content->Name,
                        ) );
                        echo sb_input( array( 'type' => 'hidden', 'name' => 'buname', 'value' => $buname, ) );
                        echo sb_input( array( 'type' => 'hidden', 'name' => 'vmuuid', 'value' => $uuid, ) );
                        echo sb_input( array( 'type' => 'hidden', 'name' => 'sb_area', 'value' => 3, ) );

                        if ( empty( $recovery ) ) {

                            sb_gobutton( 'Restore This VM Now', '', 'checkRestoreNow(1);' );

                        } else {

                            //set values

                            $jsstring = '';

                            if ( $sb_status['status'] == 'restore' ) {

                                $jsstring .= 'sb_update_statusbox(\'restorestatus\', \'Resuming Restore ...\');';

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

                        sb_progress_bar( 'creatediskstatus' );
                        sb_progress_bar( 'imagingbar' );
                        sb_status_box( 'restorestatus' );
                    }
                } else {
                    echo 'No data.xml or Disk Images found.';
                }

            } else {
                echo 'Invalid UUID';
            }

        }
    } else {
        sb_not_ready();
    }