<?php

	require( '../header.php' );

	if (empty($comm)) {
		require( '../area/' . $areafile . '.php' );
	} else {
		require( '../comm/' . $comm . '.php' );
	}
	if (empty($comm)) {
		require( '../footer.php' );
	}
