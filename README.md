# oVIRT_Simple_Backup
A REST API backup from BASH for oVirt 4.2

I have been working on a backup shell script for oVirt 4.2 using the new API changes. I am close to having the script working as I need it. I am sharing it here early just in case with the migration of Xen users to oVirt that someone is trying to do the same.

THIS SCRIPT IS CURRENTLY ALPHA and should only be used by those who understand the risks. It is NOT ready for production … YET.

Also, I had to do some >>CRAZY<< things to make this thing work. I hope in the future some of the issues are resolved so I can adjust to more sane solutions ;/

**Requirements**

Create a VM in oVirt (Example: 20GB HDD (virtio), 8GB RAM, Debian8)<br>
This VM will be used as the Backup_VM_Appliance and will be the manager for all backups

Backup_VM_Appliance<br>
(scsitools, curl, xmlstarlet, lsscsi, pv, dialog)

oVirt Engine (if using cron)
(expect)<br>
-If using cron, place scripts in /root/ or adjust scripts as required


**Install**

On Backup_VM_Appliance

 - Download the files backup.sh and example_backup.cfg to your backup script directory

 - chmod +e backup.sh

 - copy the example_backup.cfg to backup.cfg and reconfigure for your environment


On the oVirt Engine  (if using cron to keep the Backup_VM_Appliance alive)

 - crontab -e (oVirt Engine as root)<br>
`*/15 * * * * /root/restart_backup_vm.sh > /dev/null 2>&1`

Currently you need to set your VMs to run off of NFS with virtio. I am working on getting it to work with iSCSI-MPIO.

**Running the Script**

`./backup.sh`



