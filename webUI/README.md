# oVIRT_Simple_Backup - WebGUI (0.5.4)

### A REST API backup from PHP for oVirt 4.2.x

#### Screenshots
![ ](screenshots/SS01.png?raw=true)

![ ](screenshots/SS02.png?raw=true)

![ ](screenshots/SS03.png?raw=true)

![ ](screenshots/SS04.png?raw=true)

![ ](screenshots/SS05.png?raw=true)

![ ](screenshots/SS06.png?raw=true)

![ ](screenshots/SS07.png?raw=true)

![ ](screenshots/SS08.png?raw=true)

![ ](screenshots/SS09.png?raw=true)

![ ](screenshots/SS10.png?raw=true)

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
    *.  As Root:
        *.  mkdir /root/.ssh
        *.  chmod 700 /root/.ssh

2.  On Xen Server
    *.  Create a Debian Linux VM named VMMIGRATE
        *.  Install the following packages:
            *.  pv
            *.  dialog (only if using bash script)
            *.  fsarchiver
            *.  chroot
            *.  wget

        *.  As root:
            *.  vi /etc/ssh/sshd\_config
                -   \#rem out this line:\
                    \#PermitRootLogin without-password
                -   \#add this line:\
                    PermitRootLogin yes

            *.  /etc/init.d/ssh restart
            *.  cd /root
            *.  wget
                https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/xen_migrate/xen_migrate.sh
            *.  chmod +e xen\_migrate.sh
            *.  mkdir .ssh
            *.  chmod 700 .ssh
            *.  mkdir /mnt/migrate
            *.  vi /etc/fstab

            *. mount /mnt/migrate
            *. chmod 777 /mnt/migrate/

3.  On oVirt Server
    *.  Create a Debian Linux VM named BackupEngine and login to that VM to do the following commands.
    *.  Install the following packages:
        *.  pv
        *.  curl
        *.  xmlstarlet
        *.  lsscsi
        *.  dialog
        *.  exim4 (requires config of /etc/exim4/update-exim4.conf.conf
            & /etc/init.d/exim4 restart)
        *.  uuid-runtime
        *.  fsarchiver
        *.  php5
        *. php5-curl

    *.  As root:
        *.  vi /etc/ssh/sshd\_config
            -   \#rem out this line:\
                \#PermitRootLogin without-password
            -   \#add this line:\
                PermitRootLogin yes

        *.  /etc/init.d/ssh restart
        *.  mkdir /mnt/backups
        *.  mkdir /mnt/migrate
        *.  mkdir /mnt/linux
        *.  vi /etc/fstab

        *.  mount /mnt/backups
        *.  mount /mnt/migrate
        *.  mkdir /root/.ssh
        *. chmod 700 /root/.ssh
        *. usermod -a -G disk www-data
        *. chown root:disk /bin/dd
        *. chown www-data:disk /dev/vdb
        *. a2enmod ssl
        *. service apache2 restart
        *. mkdir /etc/apache2/ssl
        *. openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout
            /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt
            **Do Not Add Pass Phrase**
        *. chmod 600 /etc/apache2/ssl/\*
        *. vi /etc/apache2/sites-available/default-ssl.conf\
            1.  ServerName backupengine.**yourdomain**.com:443
            2.  DocumentRoot /var/www/html/site
            3.  SSLCertificateFile /etc/apache2/ssl/apache.crt
            4.  

        *. a2ensite default-ssl.conf
        *. service apache2 reload
        *. chsh -s /bin/bash www-data
        *. chmod 777 /mnt
        *. chmod 777 /mnt/migrate/
        *. chmod 777 /mnt/backup
        *. chmod 777 /mnt/linux
        *. chmod 777 /dev/sr0
        *. mkdir /var/www/.ssh
        *. chown www-data:www-data /var/www/.ssh
        *. chmod 700 /var/www/.ssh
        *. su www-data
        *. ssh-keygen -t rsa
        *. ssh-copy-id root@**ip.of.VMMIGRATE.VM**
        *. ssh-copy-id root@**ip.of.XEN.HOST**
        *. cd /var/www/html/
        *. **Download the files and folders from
            https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/webUI/server
            into this folder**
        *. touch /var/www/html/config.php
        *. chown www-data:root /var/www -R
        *. vi /var/www/html/allowed\_ips.php (And change allowed IP
            addresses)
        *. vi /etc/crontab
            ```*. * * * * * root /var/www/html/crons/fixgrub.sh >>/var/log/fixgrub.log 2>&1
            *. * * * * * root /var/www/html/crons/fixswap.sh >>/var/log/fixswap.log 2>&1```

4.  on oVirtEngine VM
    *.  As Root:
        *.  engine-config -s CORSSupport=true
        *.  engine-config -s CORSAllowedOrigins=\*

    *.  cd /usr/share/ovirt-engine/ui-plugins
    *.  **Download the files and folders from
        https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/webUI/plugin
        into this directory.**
    *.  vi simpleBackup.json
        -   Change IP Address in simpleBackup.json to match your oVirt
            BackupEngine VM

    *.  service ovirt-engine restart

5.  You should now be able to login to your oVirt Web UI and see the
    SimpleBackup menu item on the left.




#### Author

You can reach zipur on the IRC SERVER irc.oftc.net CHANNEL #ovirt

http://zipur.ca

aka (Preston Lord)

