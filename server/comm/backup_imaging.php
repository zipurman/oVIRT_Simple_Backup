<?php

    $sb_status = sb_status_fetch();
    $status         = 0;
    $progress       = 0;
    $numberofimages = 0;
    $dev            = '';
    $errortext            = '';
    $reason         = 'Disk Not Attached';

    if ( $sb_status['status'] == 'backup' && $sb_status['stage'] == 'backup_imaging' ) {

        $disktypeget    = sb_check_disks();
        $numberofimages = count( $disktypeget['avaliabledisks'] );

        if ( preg_match( $UUIDv4, $sb_status['setting1'] ) ) {

            if ( empty( $sb_status['setting3'] ) ) {

                $status = 0;
                $reason = 'Snapshot Not Found';

            } else if ( ! empty( $sb_status['setting4'] ) ) {

                $disks = ovirt_rest_api_call( 'GET', 'vms/' . $sb_status['setting1'] . '/snapshots/' . $sb_status['setting3'] . '/disks' );

                $diskid       = $disks->disk['id'];
                $extradiskdev = '';//needed any more?
                $diskletter   = $settings['firstbackupdisk'];

                $checkdiskattached = ovirt_rest_api_call( 'GET', 'vms/' . $settings['uuid_backup_engine'] . '/diskattachments/' );

                foreach ( $checkdiskattached as $attacheddisk ) {
                    if ( (string) $attacheddisk['id'] == (string) $diskid ) {

                        $status = 1;

                        $progressfilename = $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/progress.dat';

                        if ( $sb_status['step'] > 1 ) {

                            $status = 2;

                            if ( $sb_status['step'] == 2 ) {

                                $filedata = null;

                                $reason = 'Imaging in progress.';

                                if ( file_exists( $progressfilename ) ) {
                                    $filedata = null;
                                    exec( 'tail ' . $progressfilename . ' -n 1', $filedata );

                                    if ( strpos( ' ' . $filedata[0] , 'dd' ) !== false ) {
                                        $errortext .= ' - Error: ' . $filedata[0];
                                        $reason .= ' - Error: ' . $filedata[0];
                                        $progress = 100;
                                    } else {
                                        $progress = (int) $filedata[0];
                                    }



                                    sb_cache_set( $sb_status['setting1'], $sb_status['setting2'], 'Imaging ' . $progress . '%', $sb_status['setting4'], 'write', '?area=2&action=select&backupnow=1&vm=' . $sb_status['setting1'] . '&recovery=1', 'sb_snapshot_imaging(\'' . $sb_status['setting1'] . '\', \'' . $sb_status['setting2'] . '\');' );
                                    sleep( 1 );

                                    if ( $progress >= 100 ) {
                                        sb_status_set( 'backup', 'backup_imaging', 1 );
                                    }
                                } else {
                                    $reason .= ' - MISSING - ' . $progressfilename;
                                }
                            }

                        } else {

                            $disknumber = 0;

                            //setting5 = disknumber
                            if ( empty( $sb_status['setting5'] ) ) {
                                $disknumber = 1;
                            } else {
                                $disknumber = (integer) $sb_status['setting5'] + 1;
                            }

                            if ( $disknumber > $numberofimages ) {

                                $status = 3;
                                $reason = 'Imaging Disk(s) Completed';
                                $progress = 100;
                                sb_status_set( 'backup', 'backup_detatch_image', 1 );

                            } else {

                                exec( 'echo "0" > ' . $progressfilename );
                                $reason = 'Imaging Disk ' . $progressfilename;

                                if ( $sb_status['setting3'] == '-XEN-' ) {
                                    $processdisks = sb_disk_array_fetch( $settings['mount_migrate'] );
                                } else {
                                    $processdisks = sb_disk_array_fetch( $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] );
                                }

                                $dev = '';
                                foreach ( $processdisks as $processdisk ) {

                                    if ( empty( $dev ) && $processdisk['disknumber'] == 'Disk' . $disknumber ) {
                                        $disknumberfile = $processdisk['disknumber'];
                                        $dev            = $processdisk['path'];

                                        exec( 'cat ' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $disknumberfile . '.dat', $datfiledata );
                                        $disksize = $datfiledata[5];

                                    }
                                }

                                $backupmpde = 1;//set this to 0 to use pv inline with disksize (buggy)

                                if ( ! empty( $dev ) ) {

                                    if ( empty( $settings['compress'] ) ) {

                                        if (!empty($backupmpde)) {
                                            $command = '(pv -n /dev/' . $dev . ' | dd of="' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $disknumberfile . '.img" bs=1M conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background
                                        } else {
                                            $command = '(dd iflag=direct bs=1M status=none if=/dev/' . $dev . ' | pv -n -s ' . $disksize . ' | dd of="' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $disknumberfile . '.img" bs=1M oflag=direct conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background
                                        }

                                    } else if ( $settings['compress'] == '1' ) {

                                        if (!empty($backupmpde)) {
                                            $command = '(pv -n /dev/' . $dev . ' | gzip -c | dd of="' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $disknumberfile . '.img.gz" bs=1M conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background
                                        } else {
                                            $command = '(dd iflag=direct bs=1M status=none if=/dev/' . $dev . ' | pv -n -s ' . $disksize . ' | gzip -c | dd of="' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $disknumberfile . '.img.gz" bs=1M oflag=direct conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background
                                        }

                                    } else if ( $settings['compress'] == '2' ) {

                                        if (!empty($backupmpde)) {
                                            $command = '(pv -n /dev/' . $dev . ' | lzop -c | dd of="' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $disknumberfile . '.img.lzo" bs=1M conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background
                                        } else {
                                            $command = '(dd iflag=direct bs=1M status=none if=/dev/' . $dev . ' | pv -n -s ' . $disksize . ' | lzop -c | dd of="' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $disknumberfile . '.img.lzo" bs=1M oflag=direct conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background
                                        }

                                    } else if ( $settings['compress'] == '3' ) {

                                        if (!empty($backupmpde)) {
                                            $command = '(pv -n /dev/' . $dev . ' | bzip2 -c | dd of="' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $disknumberfile . '.img.bzip2" bs=1M conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background
                                        } else {
                                            $command = '(dd iflag=direct bs=1M status=none if=/dev/' . $dev . ' | pv -n -s ' . $disksize . ' | bzip2 -c | dd of="' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $disknumberfile . '.img.bzip2" bs=1M oflag=direct conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background
                                        }

                                    } else if ( $settings['compress'] == '4' ) {

                                        if (!empty($backupmpde)) {
                                            $command = '(pv -n /dev/' . $dev . ' | pbzip2 -c | dd of="' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $disknumberfile . '.img.pbzip2" bs=1M conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background
                                        } else {
                                            $command = '(dd iflag=direct bs=1M status=none if=/dev/' . $dev . ' | pv -n -s ' . $disksize . ' | pbzip2 -c |dd of="' . $settings['mount_backups'] . '/' . $sb_status['setting4'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $disknumberfile . '.img.pbzip2" bs=1M oflag=direct conv=notrunc,noerror status=none)   > ' . $progressfilename . ' 2>&1 &';//trailing & sends to background
                                        }

                                    }
                                    $output = null;
                                    exec( $command, $output );

                                    sb_log( 'Backup - Command: ' . $command );
                                    sb_log( 'Backup - Imaging - /dev/' . $dev );

                                    sb_cache_set( $sb_status['setting1'], $sb_status['setting2'], 'Imaging', $sb_status['setting4'], 'write' );
                                    sb_status_set( 'backup', 'backup_imaging', 2, '', '', '', '', $disknumber );
                                    $sb_status['setting5'] = $disknumber;

                                }
                            }
                        }

                    }
                }

            } else {
                $status = 0;
                $reason = 'Unmatched UUID';
            }

        } else {
            $status = 0;
            $reason = 'Invalid UUID';

        }

        if ( empty( $status ) ) {
            sb_cache_set( $sb_status['setting1'], $snapshotname, 'Imaging Failure - ' . $reason, $sb_status['setting4'], 'write' );
        }
    }

    $jsonarray = array(
        "status"        => $status,
        "reason"        => $reason,
        "progress"      => $progress,
        "numberofdisks" => $numberofimages,
        "thisdisk"      => $sb_status['setting5'],
    );

    sb_log( 'Backup Imaging - ' . $sb_status['setting5'] . '/' . $numberofimages . ' - ' . $progress . '% ' . $reason . $errortext);

    echo json_encode( $jsonarray );

    unset( $progress );
