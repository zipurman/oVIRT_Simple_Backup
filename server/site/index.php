<?php

	$projectpath           = '/var/www/html/';

	require( $projectpath . 'header.php' );

	if (empty($comm)) {
	    if ( (!empty( $settings['uuid_backup_engine'] ) &&  ! empty($settings['ovirt_pass'])) || ! empty($savestep)){
            require( $projectpath . 'area/' . $areafile . '.php' );
        }
	} else {
		require( $projectpath . 'comm/' . $comm . '.php' );
	}
	if (empty($comm)) {
		require( $projectpath . 'footer.php' );
	}
