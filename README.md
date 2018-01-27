# oVIRT_Simple_Backup

### A REST API backup from BASH for oVirt 4.2.x

#### Outline

This script has the following functionality in a linux command line GUI:
 - Settings Manager to setup your environment and allow script to connect and backup/restore
 - Backup all selected VMs on a cron schedule to a specified folder or share
 - Backup a single VM from within the GUI
 - Restore a single VM from your backed up VMs in the GUI
 - Stop/Start VMs from within the GUI
 
#### Items Not Yet Completed
 - Better docblocks in code to make contributors possible ;)
 - Restore currently creates a clone which needs NICS, CPU, MEM, etc adjusted after complete. Working on getting a full restore to work but API is being fussy and cryptic.
 - Add retention period into headless mode so that older backups are removed

---

###### THIS SCRIPT IS CURRENTLY BETA and should only be used by those who understand the risks. 

---

#### Requirements

Create a VM in oVirt (Example: 20GB HDD (virtio), 8GB RAM, Debian8)

This VM will be used as the Backup_VM_Appliance and will be the manager for all backups

Backup_VM_Appliance
 - scsitools
 - curl
 - xmlstarlet
 - lsscsi
 - pv
 - dialog
 - sendmail (exim or other) for emailing logs/alerts
 - uuid-runtime (used to generate unique uuid)

#### Install

**On Backup_VM_Appliance**

 - Download the files backup.sh and src folder to your backup script directory
 
 - chmod +e backup.sh
  
 - create a mount to your NFS backup storage
    ```bash
    mkdir /mnt/backups
    
    #vi /etc/fstab 
    #add the following line with your IP and PATH info
 
    192.168.1.123:/path/to/folder/on/nfs /mnt/backups nfs rw,async,hard,intr,noexec 0 0
    ```
 - mount your backup folder<br>
    ```bash
    mount /mnt/backups
    ```

 - .\backup.sh 
    - [S]ettings -> configure for your environment
    - [0] Select your VMs to backup

 - Create a cron job to run your backups on a schedule using 
    ```
    /path/to/backup.sh --headless
    ```


#### Author

You can reach zipur on the IRC SERVER irc.oftc.net CHANNEL #ovirt

http://zipur.ca

aka (Preston Lord)

