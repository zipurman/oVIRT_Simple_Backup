<?php

    $sb_status = sb_status_fetch();

    $numberofdisks = 0;
    $statusofimage = 0;

    $status         = 0;
    $progress       = 0;
    $statusfilename = '';
    $reason         = 'Disk Not Attached';

    if ( $sb_status['status'] == 'restore' && $sb_status['stage'] == 'restore_imaging' ) {

        sleep( 1 );
        $disktypeget    = sb_check_disks();
        $numberofimages = count( $disktypeget['avaliabledisks'] );

        if ( preg_match( $UUIDv4, $sb_status['setting2'] ) ) {

            $status = 1;
            $reason = 'Disk Attached';

            if ( $sb_status['setting3'] == '-XEN-' ) {
                $filepath = $settings['mount_migrate'] . '/';
            } else {
                $filepath = $settings['mount_backups'] . '/' . $sb_status['setting1'] . '/' . $sb_status['setting2'] . '/' . $sb_status['setting3'] . '/';
            }

            if ( file_exists( $filepath ) ) {
                if ( $sb_status['step'] == 2 ) {

                    $status = 2;
                    $reason = 'Imaging in progress.';

                    if ( file_exists( $filepath . 'progress.dat' ) ) {

                        exec( 'tail ' . $filepath . 'progress.dat' . ' -n 1', $filedata );
                        $progress = (int) $filedata[0];

                        sb_cache_set( $settings['uuid_backup_engine'], '', 'Restore Imaging ' . $progress . '%', 'Disk' . $sb_status['setting5'], 'write' );
                        sleep( 1 );

                        if ( $progress >= 100 ) {
                            sb_status_set( 'restore', 'restore_imaging', 1 );
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
                        sb_status_set( 'restore', 'fixes', 0 );
                        sb_log( $reason );

                    } else {

                        exec( 'echo "0" > ' . $filepath . 'progress.dat' );
                        $reason       = 'Imaging Disk ' . $filepath . 'progress.dat';
                        $processdisks = sb_disk_array_fetch( $filepath );
                        $diskloop     = 1;
                        foreach ( $disktypeget['avaliabledisks'] as $avaliabledisk ) {
                            foreach ( $processdisks as $processdisk ) {
                                if ( $diskloop == $disknumber && empty( $dev ) && $processdisk['disknumber'] == 'Disk' . $disknumber ) {
                                    $disknumberfile = $processdisk['disknumber'];
                                    $dev            = $avaliabledisk;

                                    exec( 'cat ' . $filepath . $disknumberfile . '.dat', $datfiledata );
                                    $disksize = $datfiledata[5];

                                }
                            }
                            $diskloop ++;
                        }
                        if ( ! empty( $dev ) ) {

                            $fileext = '';
                            if ( $sb_status['setting3'] == '-XEN-' ) {
                                $disknumberfile = 'xen' . str_replace( 'Disk', '', $disknumberfile );
                            }

                            if ( file_exists( $filepath . $disknumberfile . '.img.gz' ) ) {

                                $fileext = '.img.gz';
                                $command = '(pv -n ' . $filepath . $disknumberfile . '.img.gz | gunzip -c | dd of="' . '/dev/' . $dev . '" bs=1M conv=notrunc,noerror status=none)   > ' . $filepath . 'progress.dat' . ' 2>&1 &';//trailing & sends to background

                            } else if ( file_exists( $filepath . $disknumberfile . '.img.lzo' ) ) {

                                $fileext = '.img.lzo';
                                $command = '(pv -n ' . $filepath . $disknumberfile . '.img.lzo | lzop -d | dd of="' . '/dev/' . $dev . '" bs=1M conv=notrunc,noerror status=none)   > ' . $filepath . 'progress.dat' . ' 2>&1 &';//trailing & send to background

                            } else if ( file_exists( $filepath . $disknumberfile . '.img.bzip2' ) ) {

                                $fileext = '.img.bzip2';
                                $command = '(pv -n ' . $filepath . $disknumberfile . '.img.bzip2 | bzip2 -d | dd of="' . '/dev/' . $dev . '" bs=1M conv=notrunc,noerror status=none)   > ' . $filepath . 'progress.dat' . ' 2>&1 &';//trailing & send to background

                            } else if ( file_exists( $filepath . $disknumberfile . '.img.pbzip2' ) ) {

                                $fileext = '.img.pbzip2';
                                $command = '(pv -n ' . $filepath . $disknumberfile . '.img.pbzip2 | pbzip2 -d | dd of="' . '/dev/' . $dev . '" bs=1M conv=notrunc,noerror status=none)   > ' . $filepath . 'progress.dat' . ' 2>&1 &';//trailing & send to background

                            } else {

                                $fileext = '.img';
                                $command = '(pv -n ' . $filepath . $disknumberfile . '.img | dd of="' . '/dev/' . $dev . '" bs=1M conv=notrunc,noerror status=none)   > ' . $filepath . 'progress.dat' . ' 2>&1 &';//trailing & sends to background

                            }

                            sb_log( 'Command :' . $command );

                            exec( $command, $output );

                            sb_log( 'Restore - Imaging - /dev/' . $dev );

                            sb_log( 'Imaging Disk - ' . $filepath . $disknumberfile );

                            sb_cache_set( $sb_status['setting1'], $sb_status['setting2'], 'Imaging', $sb_status['setting4'], 'write' );
                            sb_status_set( 'restore', 'restore_imaging', 2, '', '', '', '', $disknumber );
                            $sb_status['setting5'] = $disknumber;

                            $status = 1;
                            $reason = 'Started Imaging ' . $disknumberfile . $fileext . ' to ' . '/dev/' . $dev;

                        } else {
                            //							$reason .= ' (BROKEN)';
                            $status = 3;
                            $reason = 'Imaging Disk(s) Completed';
                        }
                    }

                    sleep( 1 );

                }
            } else {
                $status = 0;
                $reason = 'Files Not Found';
            }

        } else {
            $status = 0;
            $reason = 'Invalid UUID';

        }

        if ( empty( $status ) ) {
            sb_cache_set( $settings['uuid_backup_engine'], '', 'Restore Imaging Failure - ' . $reason, $diskname, 'write' );
        }

    }

    $jsonarray = array(
        "status"         => $status,
        "reason"         => $reason,
        "progress"       => $progress,
        "statusfilename" => $statusfilename,
        "numberofdisks"  => $numberofimages,
        "thisdisk"       => $sb_status['setting5'],
    );

    sb_log( 'Restore Imaging: ' . $sb_status['setting5'] . '/' . $numberofimages . ' - ' . $progress . '% ' . $reason );

    echo json_encode( $jsonarray );