# oVIRT_Simple_Backup - WebGUI (0.6.0)

### A REST API backup from PHP for oVirt 4.2.x

#### Version History

 - Coming features
    - [ ] recover running tasks if browser is closed and re-opened. Right now you have to manually re-attach disks etc if browser is closed prior to completing backup.restore.
    - [ ] Headless scheduled backups with retention periods.
    
 - 0.6.0 - 2018/03/25
    - [x] Obscured admin password for ovirt in code and config.php. This will require a re-config of your settings.
    - [x] Added a log viewer
    - [x] Added logging to all areas
    - [x] Complete JS re-work to move all state into php to allow for future recover processes
    - [x] Complete re-work of php processes and functions for xen migrations, backups, and restores to allow for better tracking and state.
    - [x] Added support for multi-disk VM backup/restore
    - [x] Added timezone support so that logs will be reported in correct date/time and so that future scheduling features will have correct date/time
    
  - Prior Versions were not tracked for version history as project was just getting started.
    

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
 - [x] oVirt Engine Web UI Plugin
 - [x] Settings Manager
 - [x] Backup a single VM
 - [x] Restore a single VM
 - [x] Import a single RAW.img
 - [x] Migrate from XEN SERVER (Citrix)
 - [x] Automated Schedules of backups
 
#### Items Not Yet Completed
 - [ ] VMs with Multiple Disks - Backup/Restore Not Yet Supported
---

###### THIS SCRIPT IS CURRENTLY BETA and should only be used by those who understand the risks. 

---

### Steps to setting up Xen Migration and backup Scripts

1.  On one of your Xen Hosts to allow remote ssh for xe commands
    *  As Root:
        *  mkdir /root/.ssh
        *  chmod 700 /root/.ssh

2.  On Xen Server
    * On a Xen HOST (required for faster ssh sessions to avoid large delays with script timings due to DNS issues. Xen's OpenSSH is ancient and is still too slow most of the time!)
        *  vi /etc/ssh/sshd\_config
           ```bash
           #add the following to the end of the file
            UseDNS no
           ```
        * exit all ssh sessions and kill any remaining PIDs of sshd
        * service sshd restart
    *  Create a Debian Linux VM named VMMIGRATE
        *  Install the following packages:
            *  pv
            *  dialog (only if using bash script)
            *  fsarchiver
            *  chroot
            *  wget

        *  As root:
            *  vi /etc/ssh/sshd\_config
            ```
                #rem out this line:
                #PermitRootLogin without-password
                
                #add this line:
                PermitRootLogin yes
            ```

            *  /etc/init.d/ssh restart
            *  cd /root
            *  wget https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/xen_migrate/xen_migrate.sh
            *  chmod +e xen\_migrate.sh
            *  mkdir .ssh
            *  chmod 700 .ssh
            *  mkdir /mnt/migrate
            *  vi /etc/fstab (setup your mount points for your NFS - /mnt/migrate)
            * mount /mnt/migrate
            * chmod 777 /mnt/migrate/

3.  On oVirt Server
    *  Create a Debian Linux VM named BackupEngine and login to that VM to do the following commands.
    *  Install the following packages:
        *  pv
        *  curl
        *  xmlstarlet
        *  lsscsi
        *  dialog
        *  exim4 (requires config of /etc/exim4/update-exim4.conf.conf & /etc/init.d/exim4 restart)
        *  uuid-runtime
        *  fsarchiver
        *  php5
        *  php5-curl
        *  parted

    *  As root:
        *  vi /etc/ssh/sshd\_config
            ```
            #rem out this line:
            #PermitRootLogin without-password
                            
            #add these lines:
            PermitRootLogin yes
            Use DNS no
            ```

        *  /etc/init.d/ssh restart
        *  mkdir /mnt/backups
        *  mkdir /mnt/migrate
        *  mkdir /mnt/linux
        *  vi /etc/fstab (setup mount point for NFS - /mnt/backups)
        *  mount /mnt/backups
        *  mount /mnt/migrate
        *  mkdir /root/.ssh
        * chmod 700 /root/.ssh
        * usermod -a -G disk www-data
        * chown root:disk /bin/dd
        * chown www-data:disk /dev/vdb
        * a2enmod ssl
        * service apache2 restart
        * mkdir /etc/apache2/ssl
        * openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt
        * (**Do Not Add Pass Phrase**)
        * chmod 600 /etc/apache2/ssl/\*
        * vi /etc/apache2/sites-available/default-ssl.conf
            *  ServerName backupengine.**yourdomain**.com:443
            *  DocumentRoot /var/www/html/site
            *  SSLCertificateFile /etc/apache2/ssl/apache.crt
        * a2ensite default-ssl.conf
        * service apache2 reload
        * chsh -s /bin/bash www-data
        * chmod 777 /mnt
        * chmod 777 /mnt/migrate/
        * chmod 777 /mnt/backup
        * chmod 777 /mnt/linux
        * chmod 777 /dev/sr0
        * mkdir /var/www/.ssh
        * chown www-data:www-data /var/www/.ssh
        * chmod 700 /var/www/.ssh
        * su www-data
            * ssh-keygen -t rsa
            * ssh-copy-id root@**ip.of.VMMIGRATE.VM**
            * ssh-copy-id root@**ip.of.XEN.HOST**
            * exit
        * cd /var/www/html/
        * **Download the files and folders from
            https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/webUI/server
            into this folder**
        * touch /var/www/html/config.php
        * chown www-data:root /var/www -R
        * vi /var/www/html/allowed\_ips.php (And change allowed IP
            addresses)
        * vi /etc/crontab
            ```
            * * * * * * root /var/www/html/crons/fixgrub.sh >>/var/log/fixgrub.log 2>&1
            * * * * * * root /var/www/html/crons/fixswap.sh >>/var/log/fixswap.log 2>&1
            ```

4.  on oVirtEngine VM
    *  As Root:
        *  engine-config -s CORSSupport=true
        *  engine-config -s CORSAllowedOrigins=\*

    *  cd /usr/share/ovirt-engine/ui-plugins
    *  **Download the files and folders from
        https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/webUI/plugin
        into this directory.**
    *  vi simpleBackup.json
        -   Change IP Address in simpleBackup.json to match your oVirt
            BackupEngine VM

    *  service ovirt-engine restart

5.  You should now be able to login to your oVirt Web UI and see the
    SimpleBackup menu item on the left.




#### Author

You can reach zipur on the IRC SERVER irc.oftc.net CHANNEL #ovirt

http://zipur.ca

aka (Preston Lord)

