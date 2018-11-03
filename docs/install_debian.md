#### Install Steps using Debian as your VM OS for the oVirtSimpleBackupVM and XenMigrateVM
 
 [Back To ReadMe](https://github.com/zipurman/oVIRT_Simple_Backup/)
 
 

---

### Steps to setting up oVirtSimpleBackupVM 

oVirtSimpleBackupVM is required for both Backups of oVirt VMs as well as migration of XenServer VMs. This VM will be the primary interface on any functionality and is required.

* STEP 1 - SETUP THE oVirtSimpleBackupVM On oVirt Server
    1. Create a Debian Linux VM
        1. name: oVirtSimpleBackup
        2. 4GB ram
        3. 2GB single disk (using manual partitions (80% /)()20% swap) OR 8GB single disk using (using automated partitioning as automated has a minimum of 8GB)
        4. MINIMAL INSTALL ONLY
        5. MAKE SURE YOU INSTALL USING "US ENGLISH" FOR LANGUAGE AS SOME SCRIPTING MAY NOT WORK IF USING OTHER LANGUAGES
    2. Once installed do the following as root on the oVirtSimpleBackupVM:
        1. ``sed -i '2,5 s/^/#/' /etc/apt/sources.list`` will rem out the line with the CD ROM
        2. ``apt-get update``
        3. ``apt-get install pv curl lzop gzip xmlstarlet lsscsi exim4 uuid-runtime fsarchiver parted nfs-common php7.0 php7.0-curl php7.0-xml``
        4. ``vi /etc/exim4/update-exim4.conf.conf`` change setting to suit your email needs
        5. ``/etc/init.d/exim4 restart``
        6. ``sed -i "s/PermitRootLogin without-password/#PermitRootLogin without-password/g" /etc/ssh/sshd_config`` 
        7. ``echo "PermitRootLogin yes" >> /etc/ssh/sshd_config``
        8. ``echo "UseDNS no" >> /etc/ssh/sshd_config``
        9. ``service ssh restart``
        10. ``mkdir /mnt/backups && mkdir /mnt/migrate && mkdir /mnt/linux``
        11. ``vi /etc/fstab`` (setup mount point for NFS - /mnt/backups and /mnt/migrate if using XenMigration) Example: 10.50.90.195:/volume1/OVIRT/BACKUPS /mnt/backups nfs4 rw,async,hard,intr,noexec 0 0
        12. ``mount /mnt/backups``
        13. ``mount /mnt/migrate`` (if using XenMigration)
        14. ``mkdir /root/.ssh && chmod 700 /root/.ssh && usermod -a -G disk www-data && chown root:disk /bin/dd``
        15. ``a2enmod ssl && service apache2 restart``
        16. ``mkdir /etc/apache2/ssl``
        17. ``openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt`` (**Do Not Add Pass Phrase**)
        18. ``chmod 600 /etc/apache2/ssl/\*``
        19. ``vi /etc/apache2/sites-available/default-ssl.conf`` and set the following:
            *  ServerName backups.**yourdomain**.com:443
            *  DocumentRoot /var/www/html/site
            *  SSLCertificateFile /etc/apache2/ssl/apache.crt
            *  SSLCertificateKeyFile /etc/apache2/ssl/apache.key
        20. ``a2ensite default-ssl.conf && service apache2 reload && chsh -s /bin/bash www-data``
        21. ``chmod 777 /mnt && chmod 777 /mnt/migrate/ && chmod 777 /mnt/backups && chmod 777 /mnt/linux``
        22. ``mkdir /var/www/.ssh && chown www-data:www-data /var/www/.ssh && chmod 700 /var/www/.ssh``
        23. ``su www-data`` This switches user to www-data. As www-data issue the following commands:
            * ``ssh-keygen -t rsa``
            * ``ssh-copy-id root@**ip.of.VMMIGRATE.VM**``
            * ``exit`` returns you to root user
        24. ``cd /var/www/``
        25. ``wget -N --retry-connrefused https://github.com/zipurman/oVIRT_Simple_Backup/archive/master.zip``
        26. ``unzip master.zip``
        27. ``rm /var/www/html -R``
        28. ``mv /var/www/oVIRT_Simple_Backup-master/server /var/www/html``
        29. ``mv /var/www/oVIRT_Simple_Backup-master/plugin /opt/oVirtSimpleInstaller/``
        30. ``rm master.zip``
        31. ``rm /var/www/oVIRT_Simple_Backup-master/ -R``
        32. ``chown www-data:root /var/www -R``
        33. ``vi /var/www/html/allowed_ips.php`` (change allowed IP addresses)
        34. ``vi /etc/crontab`` and add the following:
            * ``* * * * * root /var/www/html/crons/fixgrub.sh >>/var/log/fixgrub.log 2>&1``
            * ``* * * * * root /var/www/html/crons/fixswap.sh >>/var/log/fixswap.log 2>&1``
        35. ``usermod -a -G cdrom www-data``
 
        
* STEP 2 - Configure the oVirtEngine
    1. ssh to the oVirtEngine VM as root and do the following:
        1. ``engine-config -s CORSSupport=true``
        2. ``engine-config -s CORSAllowedOrigins=\*``
        3. ``cd /usr/share/ovirt-engine/ui-plugins``
        4. ``mkdir simpleBackup``
        5. ``wget -N --retry-connrefused https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/plugin/simpleBackup.json``
        6. ``cd simpleBackup``
        7. ``wget -N --retry-connrefused https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/plugin/simpleBackup/start.html``
        8. ``cd /usr/share/ovirt-engine/ui-plugins``
        9. ``vi simpleBackup.json`` Change IP Address in simpleBackup.json to match your oVirtSimpleBackupVM
        10. ``chmod 755 /usr/share/ovirt-engine/ui-plugins/simpleBackup* -R``
        11. ``service ovirt-engine restart``
        12. You should now be able to login to your oVirt Web UI and see the SimpleBackup menu item on the left.


* STEP 3 - Only required if you want to use XEN SERVER MIGRATION
    1. ssh as root to one of your *XenServer Hosts* and issue the following commands as root:
        1. ``mkdir /root/.ssh``
        2. ``chmod 700 /root/.ssh``
        3. ``echo "UseDNS no" >> /etc/ssh/sshd_config``
        4. ``service sshd restart``
    2. Create a new VM in your XEN ENVIRONMENT
        1. name: VMMIGRATE
        2. 4GB ram
        3. 2GB single disk
        4. MINIMAL INSTALL ONLY
        5. MAKE SURE YOU INSTALL USING "US ENGLISH" FOR LANGUAGE AS SOME SCRIPTING MAY NOT WORK IF USING OTHER LANGUAGES
    3. Once installed do the following as root on the VMMIGRATE VM:
        1. ``sed -i '2,5 s/^/#/' /etc/apt/sources.list`` will rem out the line with the CD ROM
        2. ``apt-get update``
        3. ``apt-get install pv lzop gzip dialog fsarchiver chroot wget``
        4. ``sed -i "s/PermitRootLogin without-password/#PermitRootLogin without-password/g" /etc/ssh/sshd_config`` 
        5. ``echo "PermitRootLogin yes" >> /etc/ssh/sshd_config``
        6. ``echo "UseDNS no" >> /etc/ssh/sshd_config``
        7. ``service ssh restart``
        8. ``cd /root && mkdir .ssh && chmod 700 .ssh``
        9. ``mkdir /mnt/migrate``
        10. ``vi /etc/fstab`` (setup mount point for NFS - /mnt/migrate) Example: 10.50.90.195:/volume1/OVIRT/MIGRATE /mnt/migrate nfs4 rw,async,hard,intr,noexec 0 0
        11. ``mount /mnt/migrate && chmod 777 /mnt/migrate/``
    4. You now have to login to the oVirt oVirtSimpleBackupVM as root and do the following:
        1. ``su www-data`` This switches user to www-data. As www-data issue the following commands:
            * ``ssh-copy-id root@**ip.of.XEN.HOST.SERVER**`` NOT THE VMMIGRATE VM BUT THE XEN HOST SERVER!
            * ``exit`` returns you to root user
 

This how-to was re-written on 10-25-2018. Let zipur know if I missed anything on IRC SERVER irc.oftc.net CHANNEL #ovirt

---

#### Scheduling Backups

*  In the WebUI, open Settings and confirm the email address and retention
*  In the WebUI, open Scheduled Backups and select the VMs you want to backup on the schedule and click SAVE
*  Add a cronjob as follows (to run every 5 minutes - will check backup scheduled times within 6 minute buffer time. So a backup scheduled in the UI for 8:30 will fire any where between 8:30 and 8:35):
```bash
*/5 * * * * www-data php /var/www/html/automatedbackup.php >/dev/null 2>&1
```