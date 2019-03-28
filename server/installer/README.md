# oVirtSimpleBackup - WebGUI - Installer - Debian

## Instructions for using this installer

 1. If you are planning on using oVirtSimpleBackup for Xen Migration - Install a new VM in your Xen Environment named VMMIGRATE using the [Instructions Here](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/server/installer/ovirt-simple-backup-xenvm/README.md) before installing the script below. The script below will require the VMMIGRATE VM but running and available while the script is installing. Again, this is only for Xen Server Migrations, if you are not migrating from Xen, then you can run the install script below without worrying about VMMIGRATE.
 
 2. Create a new VM in oVirt called SimpleBackup ** USE DISK TYPE virtio (recommended) **
     * INSTALL DEBIAN 9 using the following settings:
         * NAME: SimpleBackup
         * RAM: >=4GB
         * DISK: 8GB using automated partitioning
         * MAKE SURE YOU INSTALL USING "US ENGLISH" FOR LANGUAGE AS SOME SCRIPTING MAY NOT WORK IF USING OTHER LANGUAGES
         * MINIMAL INSTALL WITH SSH SERVER
         
     * ONCE SimpleBackup VM INSTALLED
         * Login as root and do the following:
            * Setup a STATIC IP address, gateway, dns to what you will require.
            * ``apt-get install curl sshpass nfs-common``
            * ``curl https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/server/installer/install.sh | bash``
            * Follow the onscreen instructions
 
 3. Once install script is complete:
    * Navigate to your new install at https://FQDNofYouroVirtSimpleBackupVM and accept the SSL certificate
    * Go to settings tab and enter in the UUID, FQDN, USER, PASS and save
    * Then set the DOMAIN and CLUSTER and save again
    * Then enter any additional info and save one more time
    * If you will be using Xen Server Migration, add the Xen Server IP to your configuration page and save. Then add the rest of the Xen Server settings and save again.
    * You will also need to delete any snapshots you created on your SimpleBackup VM during install before you try using it as snapshots on the SimpleBackup VM will not allow any additional disks to be attached.
    * You should now be able to test your new install, do backups, migrate xen (if configured).
 
#### Scheduling Backups

*  In simpleBackup, open Settings and confirm the email address and retention
*  In simpleBackup, open Scheduled Backups and select the VMs you want to backup on the schedule and click SAVE
*  Add a cronjob to the simpleBackup VM as follows (to run every 5 minutes - will check backup scheduled times within 6 minute buffer time. So a backup scheduled in the UI for 8:30 will fire any where between 8:30 and 8:35):
```bash
*/5 * * * * www-data php /var/www/html/automatedbackup.php >/dev/null 2>&1
```