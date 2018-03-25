<div id="sb_menu">
	<div class="sm_menu_item<?php if ($area == 0) echo ' sm_menu_active'; ?>"><a href="?area=0">Status</a></div>
	<div class="sm_menu_item<?php if ($area == 2) echo ' sm_menu_active'; ?>"><a href="?area=2">Single Backup</a></div>
    <div class="sm_menu_item<?php if ($area == 3) echo ' sm_menu_active'; ?>"><a href="?area=3">Restore</a></div>

    <?php
    if (!empty($settings['xen_ip'])){
        ?>
        <div class="sm_menu_item<?php if ($area == 5) echo ' sm_menu_active'; ?>"><a href="?area=5">Xen Migrate</a></div>
	    <?php
    }

?>
    <div class="sm_menu_item<?php if ($area == 10) echo ' sm_menu_active'; ?>"><a href="?area=10">Logs</a></div>


    <div class="sm_menu_item<?php if ($area == 1) echo ' sm_menu_active'; ?>"><a href="?area=1">Scheduled Backups</a></div>
    <div class="sm_menu_item sm_menu_item_right<?php if ($area == 99) echo ' sm_menu_active'; ?>"><a href="?area=99">Settings</a></div>
</div>