<?php


	sb_pagetitle( 'Running Log' );


	if ( !file_exists($settings['backup_log'])) {

		sb_pagedescription( 'Log not found : ' . $settings['backup_log']);

	} else {

		sb_pagedescription( 'Log : ' . $settings['backup_log']);
		sb_pagedescription( 'The last 1000 lines of the log will be loaded below in reverse order. This page also has a 3 second refresh so you can watch the progress of running tasks.');

		exec( 'tail ' . $settings['backup_log'] .  ' -n 1000', $output );
		rsort($output);
		$output =  implode("\n",$output);
		echo '<pre>';
		echo htmlentities( $output );
		echo '</pre>';
		?>
        <script>
            function reloadMe() {
                window.location.reload(true);
            }

            setTimeout(reloadMe, 10 * 1000);

        </script>
		<?php

	}