<?php

	$projectpath           = '/var/www/html/';

	require( $projectpath . 'header.php' );

	if (empty($comm)) {
		require( $projectpath . 'area/' . $areafile . '.php' );
	} else {
		require( $projectpath . 'comm/' . $comm . '.php' );
	}
	if (empty($comm)) {
		require( $projectpath . 'footer.php' );
	}
