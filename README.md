# oVIRT_Simple_Backup - WebGUI (0.6.10)

### A REST API backup from PHP for oVirt 4.2.x


 - Coming features
    - [ ] multiple schedules with targeted vms and time on each managed in the UI
    - [ ] Allow thin provisioned disks
    - [ ] If using virtio_scsi driver to have the option to enable discard checkbox
      
[ChangeLog](https://github.com/zipurman/oVIRT_Simple_Backup/blob/master/ChangeLog.md)

---

![ ](screenshots/SS01.png?raw=true)

[More Snapshots](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/screenshots)


#### Outline

This code has the following functionality in a web GUI:
 - [x] oVirt Engine Web UI Plugin
 - [x] Settings Manager
 - [x] Backup a single VM
 - [x] Restore a single VM
 - [x] Migrate from XEN SERVER (Citrix)
 - [x] Automated Schedules of backups
 - [x] Log viewer
 - [x] Multi-Disk VM now supported
 - [x] Scheduled VMs backup now supported with retention and email alerts

###### THIS SCRIPT IS CURRENTLY IN BETA and should only be used for testing 

---

### Steps to setting up Xen Migration and backup Scripts

1.  On one of your Xen Hosts to allow remote ssh for xe commands
    *  As Root:
        *  mkdir /root/.ssh
        *  chmod 700 /root/.ssh

2.  On Xen Server
    * On a Xen HOST (recommended for faster ssh sessions to avoid possible delays with script timings due to DNS issues. Xen's OpenSSH is ancient and is still too slow most of the time!)
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
        *  parted
        *  nfs-common
        *  php5
            *  php5
            *  php5-curl
            *  php-libxml php-xml php-simplexml (CentOS/RH)
            *  libapache2-mod-php5 (Debian)
        *  php7
            *  php7.0
            *  php7.0-curl
            *  php7.0-xml

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
            https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/server
            into this folder**
        * touch /var/www/html/config.php
        * chown www-data:root /var/www -R
        * vi /var/www/html/allowed_ips.php (And change allowed IP addresses)
        * vi /etc/crontab
            ```
            * * * * * root /var/www/html/crons/fixgrub.sh >>/var/log/fixgrub.log 2>&1
            * * * * * root /var/www/html/crons/fixswap.sh >>/var/log/fixswap.log 2>&1
            ```
        * touch /path/to/mylog/file.log
        * chown www-data:www-data /path/to/mylog/file.log
        * usermod -a -G cdrom www-data
        
4.  on oVirtEngine VM
    *  As Root:
        *  engine-config -s CORSSupport=true
        *  engine-config -s CORSAllowedOrigins=\*

    *  cd /usr/share/ovirt-engine/ui-plugins
    *  **Download the files and folders from
        https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/plugin
        into this directory.**
    *  vi simpleBackup.json
        -   Change IP Address in simpleBackup.json to match your oVirt
            BackupEngine VM
    *  chmod 755 /usr/share/ovirt-engine/ui-plugins/simpleBackup* -R
    *  service ovirt-engine restart

5.  You should now be able to login to your oVirt Web UI and see the
    SimpleBackup menu item on the left.

---

#### Scheduling Backups

*  In the WebUI, open Settings and confirm the email address and retention
*  In the WebUI, open Scheduled Backups and select the VMs you want to backup on the schedule and click SAVE
*  Add a cronjob as follows (to run daily at 12:01 am):
```bash
1 0 * * * www-data php /var/www/html/automatedbackup.php >/dev/null 2>&1
```


#### Author

You can reach zipur on the IRC SERVER irc.oftc.net CHANNEL #ovirt

http://zipur.ca

aka (Preston Lord)

