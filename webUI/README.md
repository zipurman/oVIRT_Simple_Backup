# oVIRT_Simple_Backup - WebGUI (0.5.3)

### A REST API backup from PHP for oVirt 4.2.x

#### Outline

This code has the following functionality in a web GUI:
 - oVirt Engine Web UI Plugin
 - Settings Manager
 - Backup a single VM
 - Restore a single VM
 - Import a single RAW.img
 - Migrate from XEN SERVER (Citrix)
 
#### Items Not Yet Completed
 - Automated Schedules of backups
---

###### THIS SCRIPT IS CURRENTLY BETA and should only be used by those who understand the risks. 

---

### Steps to setting up Xen Migration and backup Scripts

1.  On one of your Xen Hosts to allow remote ssh for xe commands
    1.  As Root:
        1.  mkdir /root/.ssh
        2.  chmod 700 /root/.ssh

2.  On Xen Server
    1.  Create a Debian Linux VM named VMMIGRATE
        1.  Install the following packages:
            1.  pv
            2.  dialog (only if using bash script)
            3.  fsarchiver
            4.  chroot
            5.  wget

        2.  As root:
            1.  vi /etc/ssh/sshd\_config
                -   \#rem out this line:\
                    \#PermitRootLogin without-password
                -   \#add this line:\
                    PermitRootLogin yes

            2.  /etc/init.d/ssh restart
            3.  cd /root
            4.  wget
                https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/xen_migrate/xen_migrate.sh
            5.  chmod +e xen\_migrate.sh
            6.  mkdir .ssh
            7.  chmod 700 .ssh
            8.  mkdir /mnt/migrate
            9.  vi /etc/fstab

            10. mount /mnt/migrate
            11. chmod 777 /mnt/migrate/

3.  On oVirt Server
    1.  Create a Debian Linux VM named BackupEngine and login to that VM to do the following commands.
    2.  Install the following packages:
        1.  pv
        2.  curl
        3.  xmlstarlet
        4.  lsscsi
        5.  dialog
        6.  exim4 (requires config of /etc/exim4/update-exim4.conf.conf
            & /etc/init.d/exim4 restart)
        7.  uuid-runtime
        8.  fsarchiver
        9.  php5
        10. php5-curl

    3.  As root:
        1.  vi /etc/ssh/sshd\_config
            -   \#rem out this line:\
                \#PermitRootLogin without-password
            -   \#add this line:\
                PermitRootLogin yes

        2.  /etc/init.d/ssh restart
        3.  mkdir /mnt/backups
        4.  mkdir /mnt/migrate
        5.  mkdir /mnt/linux
        6.  vi /etc/fstab

        7.  mount /mnt/backups
        8.  mount /mnt/migrate
        9.  mkdir /root/.ssh
        10. chmod 700 /root/.ssh
        11. usermod -a -G disk www-data
        12. chown root:disk /bin/dd
        13. chown www-data:disk /dev/vdb
        14. a2enmod ssl
        15. service apache2 restart
        16. mkdir /etc/apache2/ssl
        17. openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout
            /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt
            **Do Not Add Pass Phrase**
        18. chmod 600 /etc/apache2/ssl/\*
        19. vi /etc/apache2/sites-available/default-ssl.conf\
            1.  ServerName backupengine.**yourdomain**.com:443
            2.  DocumentRoot /var/www/html/site
            3.  SSLCertificateFile /etc/apache2/ssl/apache.crt
            4.  

        20. a2ensite default-ssl.conf
        21. service apache2 reload
        22. chsh -s /bin/bash www-data
        23. chmod 777 /mnt
        24. chmod 777 /mnt/migrate/
        25. chmod 777 /mnt/backup
        26. chmod 777 /mnt/linux
        27. mkdir /var/www/.ssh
        28. chown www-data:www-data /var/www/.ssh
        29. chmod 700 /var/www/.ssh
        30. su www-data
        31. ssh-keygen -t rsa
        32. ssh-copy-id root@**ip.of.VMMIGRATE.VM**
        33. ssh-copy-id root@**ip.of.XEN.HOST**
        34. cd /var/www/html/
        35. **Download the files and folders from
            https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/webUI/server
            into this folder**
        36. touch /var/www/html/config.php
        37. chown www-data:root /var/www -R
        38. vi /var/www/html/allowed\_ips.php (And change allowed IP
            addresses)
        39. vi /etc/crontab
            1. * * * * * root /var/www/html/crons/fixgrub.sh >>/var/log/fixgrub.log 2>&1
            2. * * * * * root /var/www/html/crons/fixswap.sh >>/var/log/fixswap.log 2>&1

4.  on oVirtEngine VM
    1.  As Root:
        1.  engine-config -s CORSSupport=true
        2.  engine-config -s CORSAllowedOrigins=\*

    2.  cd /usr/share/ovirt-engine/ui-plugins
    3.  **Download the files and folders from
        https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/webUI/plugin
        into this directory.**
    4.  vi simpleBackup.json
        -   Change IP Address in simpleBackup.json to match your oVirt
            BackupEngine VM

    5.  service ovirt-engine restart

5.  You should now be able to login to your oVirt Web UI and see the
    SimpleBackup menu item on the left.




#### Author

You can reach zipur on the IRC SERVER irc.oftc.net CHANNEL #ovirt

http://zipur.ca

aka (Preston Lord)

