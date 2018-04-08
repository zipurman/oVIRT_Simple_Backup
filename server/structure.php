<?php
	/**
	 * This file is just for function/comm structure - used for dev reference only
	 *
	 *
	 * backup single vm
	 *  comm/snapshot_status[vmuuid] - Create Snapshot
	 *      comm/backup_create_path - Create Path and XML Snapshot File
	 *          comm/backup_attach_image - Attach Disk(s) to backup VM
	 *              comm/backup_imaging - Image Disk(s)
	 *                  comm/backup_detatch_image - Detatch Disk(s)
	 *                      comm/snapshot_delete - Delete Snapshot
	 *
	 *
	 * restore single vm
	 *  comm/restore_disk_create[restorevmuuid, restorename, vmname] - Create Required Disks
	 *      comm/(restore_imaging) - Image Disk(s) - targeting primary and secondary disks
	 *          ?comm/fix_grub (primary disk only)
	 *          ?comm/fix_swap (primary disk only)
	 *              comm/restore_vm_create (Create VM)
	 *                  comm/disk_detatch (Detatch Disks from Backup VM)
	 *                      comm/disk_attach (Attach Disks to New VM)
	 *
	 *
	 * xen_migrate vm
	 *  comm/xen_shutdown (Shutdown XEN VM being migrated)
	 *      comm/xen_remove_vbd (Remove disks from XEN VM being migrated)
	 *          comm/xen_shutdown (Shutdown XEN MigrateEngineVM)
	 *              comm/xen_add_vbd (Add disks to XEN MigrateEngineVM)
	 *                  comm/xen_start (Start XEN MigrateEngineVM)
	 *                      comm/xen_imaging (Image disk images to migrate path)
	 *                          comm/xen_shutdown (Shutdown XEN MigrateEngineVM)
	 *                              comm/xen_remove_vbd (Remove Disks from  XEN MigrateEngineVM)
	 *                                  comm/xen_add_vbd (Add Disks back to XEN VM being migrated)
	 *                                  ?comm/xen_start (Optionally Start XEN VM being migrated)
	 *                                  -> restore single vm (Call Restore with Xen switches for paths to images)
	 *
	 *
	 *
	 *
	 * dd if=/dev/hda conv=sync,noerror bs=64K | gzip -c  > /mnt/sda1/hda.img.gz
		http://www.linuxweblog.com/dd-image
	 */