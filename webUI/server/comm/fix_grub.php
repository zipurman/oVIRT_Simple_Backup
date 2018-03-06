<?php

	$setstatus = varcheck( "setstatus", 0, "FILTER_VALIDATE_INT", 0 );

	$dev = $settings['drive_type'] . '' . 'b';

	$status = 0;
	$reason = 'Off';

	if ( $setstatus == 1 ) {
		if ( $cronsfile = fopen( '../crons/fixgrub.dat', "w" ) ) {
			fwrite( $cronsfile, '1' );
			fclose( $cronsfile );
		}
		if ( $cronsfile = fopen( '../crons/fixgrubtarget.dat', "w" ) ) {
			fwrite( $cronsfile, $dev );
			fclose( $cronsfile );
		}
	}

	if ( file_exists( '../crons/fixgrub.dat' ) ) {
		$cronsetting = file_get_contents( '../crons/fixgrub.dat' );
		if ( $cronsetting == 1 ) {
			$status = 1;
			$reason = 'Waiting';
		} else if ( $cronsetting == 2 ) {
			$status = 2;
			$reason = 'Completed';
			if ( $cronsfile = fopen( '../crons/fixgrub.dat', "w" ) ) {
				fwrite( $cronsfile, 0 );
				fclose( $cronsfile );
			}
		} else if ( $cronsetting == 0 ) {
			$status = 0;
			$reason = 'Off';

		}
	}

	$jsonarray = array(
		"status" => $status,
		"reason" => $reason,
	);
	sleep( 2 );
	echo json_encode( $jsonarray );


