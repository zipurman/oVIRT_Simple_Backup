# oVIRT_Simple_Backup

### A REST API backup from BASH for oVirt 4.2.x

I have been working on a backup shell script for oVirt 4.2/4.2.1 using the new API changes. I am close to having the script working as I need it. I am sharing it here early just in case with the migration of Xen users to oVirt that someone is trying to do the same.

THIS SCRIPT IS CURRENTLY ALPHA and should only be used by those who understand the risks. It is NOT ready for production … YET.

Also, I had to do some >>CRAZY<< things to make this thing work with 4.2, and then 4.2.1 seems to have corrected some issues that make it less crazy. If running 4.2.1 make sure you set your settings to use incrementdiskdevices="0"

#### Requirements

Create a VM in oVirt (Example: 20GB HDD (virtio), 8GB RAM, Debian8)<br>
This VM will be used as the Backup_VM_Appliance and will be the manager for all backups

Backup_VM_Appliance<br>
 - scsitools
 - curl
 - xmlstarlet
 - lsscsi
 - pv
 - dialog
 - sendmail (exim or other) for emailing logs/alerts

oVirt Engine (if using cron to restart Backup_VM_Appliance) (ONLY REQUIRED FOR 4.2 NOT 4.2.1)
 - expect
 - If using cron, place scripts in /root/ and adjust scripts as required with user/pass server info

#### Install

**On Backup_VM_Appliance**

 - Download the files backup.sh and src folder to your backup script directory
 
 - chmod +e backup.sh
  
 - create a mount to your NFS backup storage
    ```bash
    mkdir /mnt/backups
    
    #vi /etc/fstab and add the following line with your IP and PATH info
    192.168.1.123:/path/to/folder/on/nfs /mnt/backups nfs rw,async,hard,intr,noexec 0 0
    ```
 - mount your backup folder<br>
    ```bash
    mount /mnt/backups
    ```

 - execute .\backup.sh and configure for your environment under [S]ettings and setup/tag [0] your VMs for backup


**On the oVirt Engine**  (if using cron to keep the Backup_VM_Appliance alive)  (ONLY REQUIRED FOR 4.2 NOT 4.2.1)
 - As root
    ```bash
    crontab -e
    ```
 - Add the following
    ```bash
    */15 * * * * /root/restart_backup_vm.sh > /dev/null 2>&1
    ```



#### Running the Script from a CRON job

```bash
    ./backup.sh --headless
```


#### Author

You can reach zipur on the IRC SERVER irc.oftc.net CHANNEL #ovirt

http://zipur.ca

aka (Preston Lord)

