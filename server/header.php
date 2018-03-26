<?php

	if ( ! file_exists( '../config.php' ) ) {
		exec( 'echo "" > /var/www/html/config.php' );
	}

	require( '../allowed_ips.php' );
	require( '../functions.php' );
	require( '../config.php' );
	require( '../reg.php' );
	require( '../tz.php' );

	if ( empty( $comm ) ) {
		?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>oVirt Simple Backup WebGUI</title>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <link rel="stylesheet"
                  href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
            <script type="text/javascript" src="js/script.min.js?ver=<?php echo $sb_version; ?>"></script>
            <link rel="stylesheet" href="css/style.min.css?ver=<?php echo $sb_version; ?>">
        </head>
    <body>
        <div id="sb_head">
            oVirt Simple Backup (WebGUI) - Version: <?php echo $sb_version; ?>
        </div>
		<?php require( '../menu.php' ); ?>
        <div id="sb_outerbox">
		<?php
	}