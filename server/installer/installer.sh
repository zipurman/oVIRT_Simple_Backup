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

#todo - ask all questions and then show a preview/confirm
#todo - ask for root password for ovirt engine and then pass to ssh and scp?

if [[ $who == 'root' ]]
then

    read -e -p "What is the FQDN for your oVirt ENGINE? [engine.mydomain.com]: " ovirtenginefqdn
    echo ""
    read -e -p "What is the IP Address for your oVirt ENGINE? [x.x.x.x]: " ovirtengine
    echo ""
    read -e -p "Path of the NFS for /mnt/backups [x.x.x.x:/path/to/share]: " backupip
    echo ""
    read -e -p "What is the FQDN for your oVirtSimpleBackupVM? [backupengine.mydomain.com]: " backupengine
    echo ""
    read -e -p "What is the IP Address for your oVirtSimpleBackupVM (${backupengine})? [x.x.x.x]: " backupengineip
    echo ""
    read -e -p "Are you wanting to migrate from Xen Server? [y/N]: " -i "N" xen
    if [[ $xen == 'Y' ]]
    then
        read -e -p "Path of the NFS for /mnt/migrate [x.x.x.x:/path/to/share]: " migrateip
        read -e -p "IP Address of Xen Server? [x.x.x.x]: " xenserver
        read -e -p "IP Address of Xen Server Migrate VM? [x.x.x.x]: " xenservermigrate
    fi

    clear

    echo ""
    echo "======================================"
    echo "oVirt Simple Backup (WebGUI) Installer"
    echo "======================================"
    echo ""
    echo "FQDN for your oVirt ENGINE: ${ovirtenginefqdn}"
    echo ""
    echo "IP Address for your oVirt ENGINE: ${ovirtengine}"
    echo ""
    echo "Path of the NFS for /mnt/backups:"
    echo "  ${backupip}"
    echo ""
    echo "oVirtSimpleBackupVM FQDN: ${backupengine}"
    echo ""
    echo "oVirtSimpleBackupVM IP: ${backupengineip}"
    echo ""
    echo "Xen Server Migration Tools: ${xen}"
    echo ""
    if [[ $xen == 'Y' ]]
    then
        echo "Path of the NFS for /mnt/migrate: ${migrateip}"
        echo ""
        echo "IP Address of Xen Server: ${xenserver}"
        echo ""
        echo "IP Address of Xen Server Migrate VM: ${xenservermigrate}"
        echo ""
        echo "IP Address for your oVirt Engine: ${ovirtengine}"
    fi
    echo ""
    echo ""



    read -e -p "Do these settings look correct? [Y/n]: " -i "Y" oktoinstall


    if [[ $oktoinstall == 'Y' ]]
    then
        clear
        echo ""
        echo "======================================"
        echo "oVirt Simple Backup (WebGUI) Installer"
        echo "======================================"
        echo ""
        echo "Installing...."
        apt-get -o Dpkg::Progress-Fancy="1" install pv curl zip exim4 fsarchiver parted nfs-common php7.0 php7.0-curl php7.0-xml  -y

        echo "Updating SSH Settings"
        sed -i "s/PermitRootLogin without-password/#PermitRootLogin without-password/g" /etc/ssh/sshd_config
        echo "PermitRootLogin yes" >> /etc/ssh/sshd_config
        echo "UseDNS no" >> /etc/ssh/sshd_config
        service ssh restart

        echo "Creating Mount Directories"
        mkdir /mnt/backups
        mkdir /mnt/migrate
        mkdir /mnt/linux

        echo ""
        echo "Now we have to add your NFS shares so they can mount."
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

        mkdir /root/.ssh
        chmod 700 /root/.ssh
        usermod -a -G disk www-data
        usermod -a -G cdrom www-data
        chown root:disk /bin/dd
        a2enmod ssl
        service apache2 restart
        mkdir /etc/apache2/ssl


        openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt -subj "/C=CA/ST=Saskatchewan/L=SwiftCurrent/O=Global Security/OU=IT Department/CN=${backupengine}"


        chmod 600 /etc/apache2/ssl/*

        echo "Updating Apache"
        sed -i "s/\/var\/www\/html/\/var\/www\/html\/site\nServerName ${backupengine}:443/g" /etc/apache2/sites-available/default-ssl.conf
        sed -i "s/\/var\/www\/html/\/var\/www\/html\/site\nServerName ${backupengine}:80/g" /etc/apache2/sites-available/000-default.conf
        sed -i "s/SSLEngine on/SSLEngine on\nSSLCertificateFile \/etc\/apache2\/ssl\/apache.crt\nSSLCertificateKeyFile \/etc\/apache2\/ssl\/apache.key/g" /etc/apache2/sites-available/default-ssl.conf
        a2ensite default-ssl.conf
        service apache2 reload

        chsh -s /bin/bash www-data
        chmod 777 /mnt
        chmod 777 /mnt/migrate
        chmod 777 /mnt/backups
        chmod 777 /mnt/linux
        mkdir /var/www/.ssh
        chown www-data:www-data /var/www/.ssh
        chmod 700 /var/www/.ssh

        if [[ $xen == 'Y' ]]
        then
            echo ""
            echo ""
            echo "****** XEN AREA OF INSTALLER IS NOT COMPLETED YET - JUST BACKUP/RESTORE FOR NOW ********"
            echo ""
            echo ""
            $xen="N"
        fi

        if [[ $xen == 'Y' ]]
        then


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

        cd /var/www/
        wget https://github.com/zipurman/oVIRT_Simple_Backup/archive/master.zip
        unzip master.zip
        rm /var/www/html -R
        mv /var/www/oVIRT_Simple_Backup-master/server /var/www/html
        rm master.zip
        rm /var/www/oVIRT_Simple_Backup-master/ -R
        chown www-data:root /var/www -R

        echo "\$allowed_ips = array(); //REMOVE THIS LINE IF USING ARRAY ABOVE!!"  >> /var/www/html/allowed_ips.php

        echo "Updating Cron Jobs"
        echo "* * * * * root /var/www/html/crons/fixgrub.sh >>/var/log/fixgrub.log 2>&1" >> /etc/crontab
        echo "* * * * * root /var/www/html/crons/fixswap.sh >>/var/log/fixswap.log 2>&1" >> /etc/crontab

        chmod 700 /var/www/html/crons/fixgrub.sh
        chmod 700 /var/www/html/crons/fixswap.sh

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
        chown www-data:www-data /var/www/html/config.php
        touch /var/log/simplebackup.log
        chown www-data:www-data /var/log/simplebackup.log
        echo ""

        cp /var/www/html/plugin /opt/oVirtSimpleInstaller/ -R
        echo "{" > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"name": "simpleBackup",' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"url": "/ovirt-engine/webadmin/plugin/simpleBackup/start.html",' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"config": {' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"mainBackupPage": "//${backupengineip}/index.php"' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '},' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"resourcePath": "simpleBackup"' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '}' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json

       echo ""
       echo ""
        echo "Enter password for root on oVirtEngine"
        echo ""
        scp -r -o StrictHostKeyChecking=no /opt/oVirtSimpleInstaller/plugin/simpleBackup* root@${ovirtengine}:/usr/share/ovirt-engine/ui-plugins/

        echo ""
        echo ""
        echo "Enter password AGAIN for root on oVirtEngine"
        echo ""
        ssh -o StrictHostKeyChecking=no root@${ovirtengine} 'chmod 755 /usr/share/ovirt-engine/ui-plugins/simpleBackup* -R && engine-config -s CORSSupport=true && engine-config -s CORSAllowedOrigins=*  && service ovirt-engine restart'


        echo "${ovirtengine} ${ovirtenginefqdn}" >> /etc/hosts
        echo "${backupengineip} ${backupengine}" >> /etc/hosts
        echo "${backupengine}" > /etc/hostname
        echo "${backupengine}" > /etc/mailname


        clear
        echo ""
        echo ""
        echo "You should now be able to login to your oVirt WebUI and see the Simple Backup tab in the menu."
        echo ""
        echo "      - Navigate to oVirtWebUI: https://${ovirtengine}"
        echo "      - Navigate Directly to oVirtBackupEngineVM: https://${backupengine}"
        echo ""

    else
        echo "Cancelled."
    fi
else
    echo "Script must be run as root"
fi
#TODO - Xen Server installs
#TODO - Xen Server VM installs
