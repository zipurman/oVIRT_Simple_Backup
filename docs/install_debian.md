#### Install Steps using Debian as your VM OS for the oVirtSimpleBackupVM and XenMigrateVM
 
 [Back To ReadMe](https://github.com/zipurman/oVIRT_Simple_Backup/)
 
 

---

### Steps to setting up oVirtSimpleBackupVM 

oVirtSimpleBackupVM is required for both Backups of oVirt VMs as well as migration of XenServer VMs. This VM will be the primary interface on any functionality and is required.

* STEP 1 - SETUP THE oVirtSimpleBackupVM On oVirt Server
    * Create a Debian Linux VM
        * name: oVirtSimpleBackup
        * 4GB ram
        * 2GB single disk (using manual partitions (80% /)()20% swap) OR 8GB single disk using (using automated partitioning as automated has a minimum of 8GB)
        * MINIMAL INSTALL ONLY
        * MAKE SURE YOU INSTALL USING "US ENGLISH" FOR LANGUAGE AS SOME SCRIPTING MAY NOT WORK IF USING OTHER LANGUAGES
    * Once installed do the following as root on the oVirtSimpleBackupVM:
        * ``sed -i '2,5 s/^/#/' /etc/apt/sources.list`` will rem out the line with the CD ROM
        * ``apt-get update``
        * ``apt-get install pv curl lzop gzip xmlstarlet lsscsi exim4 uuid-runtime fsarchiver parted nfs-common php7.0 php7.0-curl php7.0-xml``
        * ``vi /etc/exim4/update-exim4.conf.conf`` change setting to suit your email needs
        * ``/etc/init.d/exim4 restart``
        * ``sed -i "s/PermitRootLogin without-password/#PermitRootLogin without-password/g" /etc/ssh/sshd_config`` 
        * ``echo "PermitRootLogin yes" >> /etc/ssh/sshd_config``
        * ``echo "UseDNS no" >> /etc/ssh/sshd_config``
        * ``service ssh restart``
        * ``mkdir /mnt/backups && mkdir /mnt/migrate && mkdir /mnt/linux``
        * ``vi /etc/fstab`` (setup mount point for NFS - /mnt/backups and /mnt/migrate if using XenMigration) Example: 10.50.90.195:/volume1/OVIRT/BACKUPS /mnt/backups nfs4 rw,async,hard,intr,noexec 0 0
        * ``mount /mnt/backups``
        * ``mount /mnt/migrate`` (if using XenMigration)
        * ``mkdir /root/.ssh && chmod 700 /root/.ssh && usermod -a -G disk www-data && chown root:disk /bin/dd``
        * ``a2enmod ssl && service apache2 restart``
        * ``mkdir /etc/apache2/ssl``
        * ``openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt`` (**Do Not Add Pass Phrase**)
        * ``chmod 600 /etc/apache2/ssl/\*``
        * ``vi /etc/apache2/sites-available/default-ssl.conf`` and set the following:
        * *  ServerName backups.**yourdomain**.com:443
        * *  DocumentRoot /var/www/html/site
        * *  SSLCertificateFile /etc/apache2/ssl/apache.crt
        * *  SSLCertificateKeyFile /etc/apache2/ssl/apache.key
        * ``a2ensite default-ssl.conf && service apache2 reload && chsh -s /bin/bash www-data``
        * ``chmod 777 /mnt && chmod 777 /mnt/migrate/ && chmod 777 /mnt/backups && chmod 777 /mnt/linux``
        * ``mkdir /var/www/.ssh && chown www-data:www-data /var/www/.ssh && chmod 700 /var/www/.ssh``
        * ``su www-data`` This switches user to www-data. As www-data issue the following commands:
        * ``ssh-keygen -t rsa``
        * ``ssh-copy-id root@**ip.of.VMMIGRATE.VM**``
        * ``exit`` returns you to root user
        * ``cd /var/www/``
        * ``wget -N --retry-connrefused https://github.com/zipurman/oVIRT_Simple_Backup/archive/master.zip``
        * ``unzip master.zip``
        * ``rm /var/www/html -R``
        * ``mv /var/www/oVIRT_Simple_Backup-master/server /var/www/html``
        * ``mv /var/www/oVIRT_Simple_Backup-master/plugin /opt/oVirtSimpleInstaller/``
        * ``rm master.zip``
        * ``rm /var/www/oVIRT_Simple_Backup-master/ -R``
        * ``chown www-data:root /var/www -R``
        * ``vi /var/www/html/allowed_ips.php`` (change allowed IP addresses)
        * ``vi /etc/crontab`` and add the following:
        * ``* * * * * root /var/www/html/crons/fixgrub.sh >>/var/log/fixgrub.log 2>&1``
        * ``* * * * * root /var/www/html/crons/fixswap.sh >>/var/log/fixswap.log 2>&1``
        * ``usermod -a -G cdrom www-data``
 
        
* STEP 2 - Configure the oVirtEngine
    * ssh to the oVirtEngine VM as root and do the following:
        * ``engine-config -s CORSSupport=true``
        * ``engine-config -s CORSAllowedOrigins=\*``
        * ``cd /usr/share/ovirt-engine/ui-plugins``
        * ``mkdir simpleBackup``
        * ``wget -N --retry-connrefused https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/plugin/simpleBackup.json``
        * ``cd simpleBackup``
        * ``wget -N --retry-connrefused https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/plugin/simpleBackup/start.html``
        * ``cd /usr/share/ovirt-engine/ui-plugins``
        * ``vi simpleBackup.json`` Change IP Address in simpleBackup.json to match your oVirtSimpleBackupVM
        * ``chmod 755 /usr/share/ovirt-engine/ui-plugins/simpleBackup* -R``
        * ``service ovirt-engine restart``
        * You should now be able to login to your oVirt Web UI and see the SimpleBackup menu item on the left.


* STEP 3 - Only required if you want to use XEN SERVER MIGRATION
    * ssh as root to one of your *XenServer Hosts* and issue the following commands as root:
        * ``mkdir /root/.ssh``
        * ``chmod 700 /root/.ssh``
        * ``echo "UseDNS no" >> /etc/ssh/sshd_config``
        * ``service sshd restart``
    * Create a new VM in your XEN ENVIRONMENT
        * name: VMMIGRATE
        * 4GB ram
        * 2GB single disk
        * MINIMAL INSTALL ONLY
        * MAKE SURE YOU INSTALL USING "US ENGLISH" FOR LANGUAGE AS SOME SCRIPTING MAY NOT WORK IF USING OTHER LANGUAGES
    * Once installed do the following as root on the VMMIGRATE VM:
        * ``sed -i '2,5 s/^/#/' /etc/apt/sources.list`` will rem out the line with the CD ROM
        * ``apt-get update``
        * ``apt-get install pv lzop gzip dialog fsarchiver chroot wget``
        * ``sed -i "s/PermitRootLogin without-password/#PermitRootLogin without-password/g" /etc/ssh/sshd_config`` 
        * ``echo "PermitRootLogin yes" >> /etc/ssh/sshd_config``
        * ``echo "UseDNS no" >> /etc/ssh/sshd_config``
        * ``service ssh restart``
        * ``cd /root && mkdir .ssh && chmod 700 .ssh``
        * ``mkdir /mnt/migrate``
        * ``vi /etc/fstab`` (setup mount point for NFS - /mnt/migrate) Example: 10.50.90.195:/volume1/OVIRT/MIGRATE /mnt/migrate nfs4 rw,async,hard,intr,noexec 0 0
        * ``mount /mnt/migrate && chmod 777 /mnt/migrate/``
    * You now have to login to the oVirt oVirtSimpleBackupVM as root and do the following:
        * ``su www-data`` This switches user to www-data. As www-data issue the following commands:
            * ``ssh-copy-id root@**ip.of.XEN.HOST.SERVER**`` NOT THE VMMIGRATE VM BUT THE XEN HOST SERVER!
            * ``exit`` returns you to root user
 
---

#### Scheduling Backups

*  In simpleBackup, open Settings and confirm the email address and retention
*  In simpleBackup, open Scheduled Backups and select the VMs you want to backup on the schedule and click SAVE
*  Add a cronjob to the simpleBackup VM as follows (to run every 5 minutes - will check backup scheduled times within 6 minute buffer time. So a backup scheduled in the UI for 8:30 will fire any where between 8:30 and 8:35):
```bash
*/5 * * * * www-data php /var/www/html/automatedbackup.php >/dev/null 2>&1
```