
###### READ THE FOLLOWING EXPLAINATION BEFORE INSTALLING TO MAKE SURE YOU UNDERSTAND WHAT THE SCRIPT DOES AND DOESN'T DO


If you are installing oVirtSimpleBackup for Xen Server Migration, the following explains how it works. You can then continue to use oVirtSimpleBackup to backup your new oVirt VMs if you choose.

## How the Xen to oVirt Migration Works:

* Requires the oVirtSimpleBackupVM, so make sure you install the oVirtSimpleBackupVM in the oVirt environment
    
* A new VM must be created in the XenServer environment as well. We will call this VMMIGRATE.
    
* A NFS share must be added on oVirtSimpleBackup and VMMIGRATE to /mnt/migrate. This is where the migration files will be written and read.
    
* VMMIGRATE is a simple VM that does the following:
    * Receives ssh commands from oVirtSimpleBackup to image attached disks when migrating VMs
        
* oVirtSimpleBackup will also ssh to your specified XenHost Server and issue commands to shutdown, start, attach disks, remove disks, etc. for VMs that will be involved in the migration (VMMIGRATE and TargetVM)
    
* When running migrations oVirtSimpleBackup will:
    
    * Call to specified XenHost Server 
        * shutdown the target VM
        * shutdown VMMIGRATE
        * detatch the disk(s) from the target VM
        * attach the disk(s) as an additional disk to VMMIGRATE
        * start VMMIGRATE
    * Call to VMMIGRATE to image the disk(s) to /mnt/migrate
    * Call to specified XenHost Server 
        * shutdown VMMIGRATE
        * detatch the disk(s) from VMMIGRATE
        * re-attach the disk(s) to the target VM
        * optionally restart the target VM on Xen (not recommended)
    * connect to oVirtEngine
        * create a new VM
        * create new Disk(s) for new VM
        * attach the new disk(s) to oVirtSimpleBackupVM
    * oVirtSimpleBackup will then
        * image the disk(s) from /mnt/migrate
        * Any other selected tasks (fix grub, fix swap) (requires /mnt/linux for chroot activities)
    * connect to oVirtEngine
        * disconnect the disk(s) from oVirtEngine 
        * attach to new VM
            
## How the oVirt Backup works:
* A new VM must be created in the oVirt environment. We will call this oVirtSimpleBackup.

* A NFS share must be added on oVirtSimpleBackupVM to /mnt/backups. This is where the backups will be saved.

* When running backups oVirtSimpleBackup will:

    * connect to oVirtEngine
    * snapshot the target VM
    * attach the snapshot to oVirtSimpleBackupVM as an additional disk
    * image the disk to /mnt/backups/VMNAME/UUID/DATESTAMP/
    * remove the attached snapshot
    * delete the snapshot
    
---
## Notes:
   
* LVM - If using LVM in linux VMs, make sure the LVM names are unique otherwise you may have issues with mounting/unmounting disks during backups.

---


You can proceed to the VMMIGRATE setup [here](https://github.com/zipurman/oVIRT_Simple_Backup/blob/master/server/installer/ovirt-simple-backup-xenvm/README.md)