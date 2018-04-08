#!/bin/bash

# Written by Preston Lord
# oVirt Simple Backup (WebGUI) Installer Version 0.1.0
# Last Updated April 07, 2018
# written for Debian Only

clear

echo ""
echo "======================================"
echo "oVirt Simple Backup (WebGUI) Installer"
echo "======================================"
echo ""

who=`whoami`
echo $who

if [[ $who == 'root' ]]
then

    read -e -p "Do you wish to install oVirt Simple Backup and all of its required packages and settings? [Y/n]: " -i "Y" oktoinstall

    if [[ $oktoinstall == 'Y' ]]
    then
        echo "Installing...."
        apt-get install pv curl zip exim4 uuid-runtime fsarchiver parted nfs-common php5 php5-curl libapache2-mod-php5 -y

        echo "Updating SSH Settings"
        sed -i "s/PermitRootLogin without-password/#PermitRootLogin without-password/g" /etc/ssh/sshd_config
        echo "PermitRootLogin yes" >> /etc/ssh/sshd_config
        echo "Use DNS no" >> /etc/ssh/sshd_config
        service ssh restart

        echo "Creating Mount Directories"
        mkdir /mnt/backups
        mkdir /mnt/migrate
        mkdir /mnt/linux

        echo ""
        echo "Now we have to add your NFS shares so they can mount."
        read -e -p "Path of the NFS for /mnt/backups [x.x.x.x:/path/to/share]: " backupip
        read -e -p "Path of the NFS for /mnt/migrate [x.x.x.x:/path/to/share]: " migrateip
        echo "Saving to /etc/fstab and mounting"

        if [ ! -z "$backupip" ]
        then
            echo "${backupip} /mnt/backups nfs rw,async,hard,intr,noexec 0 0" >> /etc/fstab
            mount /mnt/backups
        fi
        if [ ! -z "migrateip" ]
        then
            echo "${migrateip} /mnt/migrate nfs rw,async,hard,intr,noexec 0 0" >> /etc/fstab
            mount /mnt/migrate
        fi

        mkdir /root/.ssh -f
        chmod 700 /root/.ssh
        usermod -a -G disk www-data
        usermod -a -G cdrom www-data
        chown root:disk /bin/dd
        a2enmod ssl
        service apache2 restart
        mkdir /etc/apache2/ssl

        read -e -p "What is the FQDN for your oVirtSimpleBackupVM? [backupengine.mydomain.com]: " backupengine
        read -e -p "What is the IP Address for your oVirtSimpleBackupVM? [backupengine.mydomain.com]: " backupengineip

        openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt -subj "/C=CA/ST=Saskatchewan/L=SwiftCurrent/O=Global Security/OU=IT Department/CN=${backupengine}"

        chmod 600 /etc/apache2/ssl/*

        echo "Updating Apache"
        sed -i "s/ServerName 1.2.3.4:443/ServerName ${backupengine}:443/g" /etc/apache2/sites-available/default-ssl.conf
        sed -i "s/\/var\/www\/html/\/var\/www\/html\/site/g" /etc/apache2/sites-available/default-ssl.conf
        a2ensite default-ssl.conf
        service apache2 reload

        chsh -s /bin/bash www-data
        chmod 777 /mnt
        chmod 777 /mnt/migrate
        chmod 777 /mnt/backup
        chmod 777 /mnt/linux
        mkdir /var/www/.ssh
        chown www-data:www-data /var/www/.ssh
        chmod 700 /var/www/.ssh
        su www-data


        read -e -p "Are you wanting to migrate from Xen Server? [y/N]: " -i "N" xen

        if [[ $xen == 'Y' ]]
        then

            read -e -p "IP Address of Xen Server? [x.x.x.x]: " xenserver
            read -e -p "IP Address of Xen Server Migrate VM? [x.x.x.x]: " xenservermigrate
            su - www-data -c 'ssh-keygen -t rsa'
            echo ""
            echo ""
            echo "Password is for root user of Xen Server"
            su - www-data -c 'ssh-copy-id root@${xenserver}'
            echo ""
            echo ""
            echo "Password is for root user of Xen Server Migrate VM"
            su - www-data -c 'ssh-copy-id root@${xenservermigrate}'
        fi

        cd /var/www/html/
        wget https://github.com/zipurman/oVIRT_Simple_Backup/archive/master.zip
        unzip master.zip
        chown www-data:root /var/www -R

        echo "\$allowed_ips = array();"

        echo "Updating Cron Jobs"
        echo "* * * * * root /var/www/html/crons/fixgrub.sh >>/var/log/fixgrub.log 2>&1" >> /etc/crontab
        echo "* * * * * root /var/www/html/crons/fixswap.sh >>/var/log/fixswap.log 2>&1" >> /etc/crontab

        echo "Setting Default Configs"
        echo "<?php" > /var/www/html/config.php
        echo "\$settings = array(" >> /var/www/html/config.php
        echo '"vms_to_backup" => array(""),' >> /var/www/html/config.php
        echo '"label" => "BU_",' >> /var/www/html/config.php
        echo '"uuid_backup_engine" => "",' >> /var/www/html/config.php
        echo '"ovirt_url" => "",' >> /var/www/html/config.php
        echo '"ovirt_user" => "admin@internal",' >> /var/www/html/config.php
        echo '"ovirt_pass" => "",' >> /var/www/html/config.php
        echo '"mount_backups" => "/mnt/backups",' >> /var/www/html/config.php
        echo '"drive_type" => "",' >> /var/www/html/config.php
        echo '"drive_interface" => "",' >> /var/www/html/config.php
        echo '"backup_log" => "/var/log/simplebackup.log",' >> /var/www/html/config.php
        echo '"email" => "",' >> /var/www/html/config.php
        echo '"retention" => 2,' >> /var/www/html/config.php
        echo '"storage_domain" => "",' >> /var/www/html/config.php
        echo '"cluster" => "",' >> /var/www/html/config.php
        echo '"mount_migrate" => "/mnt/migrate",' >> /var/www/html/config.php
        echo '"xen_ip" => "",' >> /var/www/html/config.php
        echo '"xen_migrate_uuid" => "",' >> /var/www/html/config.php
        echo '"xen_migrate_ip" => "",' >> /var/www/html/config.php
        echo '"restore_console" => "vnc",' >> /var/www/html/config.php
        echo '"restore_os" => "other_linux",' >> /var/www/html/config.php
        echo '"restore_vm_type" => "server",' >> /var/www/html/config.php
        echo '"restore_cpu_sockets" => "2",' >> /var/www/html/config.php
        echo '"restore_cpu_cores" => "2",' >> /var/www/html/config.php
        echo '"restore_cpu_threads" => "1",' >> /var/www/html/config.php
        echo '"tz" => "America/Regina",' >> /var/www/html/config.php
        echo ');' >> /var/www/html/config.php

        chown www-data:www-data /var/log/simplebackup.log
        echo ""

        read -e -p "What is the IP Address for your oVirt Engine? [x.x.x.x]: " ovirtengine
        echo "Enter password for root on oVirtEngine"
        ssh "root@${ovirtengine} -c 'engine-config -s CORSSupport=true' && engine-config -s CORSAllowedOrigins=*"

        cp /var/www/html/plugin /opt/oVirtSimpleInstaller/ -R
        echo "{" > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"name": "simpleBackup",' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"url": "/ovirt-engine/webadmin/plugin/simpleBackup/start.html",' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"config": {' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"mainBackupPage": "//${backupengineip}/index.php"' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '},' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"resourcePath": "simpleBackup"' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '}' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json

        echo "Enter password for root on oVirtEngine"
        scp -r /opt/oVirtSimpleInstaller/plugin/simpleBackup* root@${ovirtengine}:/usr/share/ovirt-engine/ui-plugins/
        echo "Enter password for root on oVirtEngine"
        ssh "root@${ovirtengine} -c 'chmod 755 /usr/share/ovirt-engine/ui-plugins/simpleBackup* -R && service ovirt-engine restart"

        echo ""
        echo ""
        echo "You should now be able to login to your oVirt WebbUI and see the Simple Backup tab in the menu."
        echo ""

    else
        echo "Cancelled."
    fi
else
    echo "Script must be run as root"
fi
#TODO - backupengineip networking setup
#TODO - Xen Server installs
#TODO - Xen Server VM installs
