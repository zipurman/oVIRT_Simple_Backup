# oVirtSimpleBackup - Xen Migrate Tool

#### Outline

This script needs to be used on a linux VM (VM_Migrate_Appliance) in your Citrix Xen Server Environment. 

Then using this script on that linux VM you do the following:
 - Shutdown a VM in Xen that you want to clone
 - Attach the disk of the VM that you want to clone to the VM_Migrate_Appliance running this script using Xen Center or xen cli
 - check your disks using the following command on VM_Migrate_Appliance
    ```shell
    fdisk -l
    ```
 - run script
```shell
./xen_migrate.sh <disk_device> <migrate_nfs_path> <vmname> <sizeofpart_in_gb>
```
    
---

#### Requirements

Create a VM in Xen (Example: 20GB HDD, 8GB RAM, Debian8)

This VM will be used as the VM_Migrate_Appliance and will be the manager for all xen migration images

VM_Migrate_Appliance
 - pv
 - dialog
 - fsarchiver
 - chroot
 - wget

#### Install

**On VM_Migrate_Appliance**

 - Download the file xen_migrate.sh 
 
  ```
    wget https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/xen_migrate/xen_migrate.sh
    chmod +e xen_migrate.sh 
  ```
  
 - create a mount to your NFS migrate storage (This will be the same path you map on your oVirt migrate nfs)
    ```bash
    mkdir /mnt/migrate
    
    #vi /etc/fstab 
    #add the following line with your IP and PATH info
 
    192.168.1.123:/path/to/folder/on/nfs /mnt/migrate nfs rw,async,hard,intr,noexec 0 0
    ```
 - mount your migrate folder
    ```bash
    mount /mnt/migrate
    ```
    
#### Usage
    ```
    ./xen_migrate.sh <disk_device> <migrate_nfs_path> <vmname> <sizeofpart_in_gb>
    
    ./xen_migrate.sh /dev/xvdb /mnt/migrate MyFavoriteVM 60
    ```

After the image is created, simply use your oVirt Simple Backup Manager in your oVirt Environment to restore the image using option 6.

#### Author

You can reach zipur on the IRC SERVER irc.oftc.net CHANNEL #ovirt

http://zipur.ca

aka (Preston Lord)

