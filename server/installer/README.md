# oVIRT_Simple_Backup - WebGUI - Installer - Debian

## Instructions for using this installer

 1. If you are planning on using oVirtSimpleBackup for Xen Migration - Install a new VM in your Xen Environment named VMMIGRATE using the [Instructions Here](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/server/installer/ovirt-simple-backup-xenvm/README.md) before installing the script below. The script below will require the VMMIGRATE VM but running and available while the script is installing. Again, this is only for Xen Server Migrations, if you are not migrating from Xen, then you can run the install script below without worrying about VMMIGRATE.
 
 2. Create a new VM in oVirt called SimpleBackup
     * INSTALL DEBIAN 9 using the following settings:
         * NAME: SimpleBackup
         * RAM: >=4GB
         * DISK: 8GB using automated partitioning
         * MAKE SURE YOU INSTALL USING "US ENGLISH" FOR LANGUAGE AS SOME SCRIPTING MAY NOT WORK IF USING OTHER LANGUAGES
         * MINIMAL INSTALL WITH SSH SERVER
         
     * ONCE XEN SimpleBackup VM INSTALLED
         * Login as root and do the following:
            * Setup a STATIC IP address, gateway, dns to what you will require.
            * ``apt-get install curl sshpass``
            * ``curl https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/server/installer/install.sh | bash``
            * Follow the onscreen instructions
 
 3. Once install script is complete:
    * Navigate to your new install at https://FQDNofYouroVirtSimpleBackupVM and accept the SSL certificate
    * Go to settings tab and enter in the UUID, FQDN, USER, PASS and save
    * Then set the DOMAIN and CLUSTER and save again
    * Then enter any additional info and save one more time
    * You will also need to delete any snapshots you created on your SimpleBackup VM during install before you try using it as snapshots on the SimpleBackup VM will not allow any additional disks to be attached.
    * You should now be able to test your new install and do a backups.
 
 