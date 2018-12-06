
###### READ THE FOLLOWING EXPLAINATION BEFORE INSTALLING TO MAKE SURE YOU UNDERSTAND WHAT THE SCRIPT DOES AND DOESN'T DO


If just installing oVirtSimpleBackup for backing up your VMs in oVirt, the following explains how this works.

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
   
* LVM - If using LVM in linux VMs, make sure the LVM names are unique otherwise you may have issues with mounting/unmounting disks during backups. Another solution for LVM would be to  edit /etc/lvm/lvm.conf add the following:
``global_filter = [ "a|/dev/sda|", "r|/dev/sd*|" ]`` (Thanks to doe-cu for this tip!)
---
        
You can proceed to the installer [here](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/server/installer/README.md)