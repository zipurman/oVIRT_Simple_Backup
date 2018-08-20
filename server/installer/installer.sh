#!/bin/bash

# Written by Preston Lord
# oVirt Simple Backup (WebGUI) Installer Version 0.1.2
# Last Updated April 20, 2018
# written for Debian Only

clear

echo ""
echo "======================================"
echo "oVirt Simple Backup (WebGUI) Installer"
echo "======================================"
echo ""

who=`whoami`

if [[ $who == 'root' ]]
then

    echo "OVIRT ENGINE INFORMATION"
    echo ""

    ok=0
    while [ $ok -eq 0 ]
    do

        read -e -p "What is the FQDN for your oVirt ENGINE? [engine.mydomain.com]: " ovirtenginefqdn
        echo ""
        read -e -p "What is the IP Address for your oVirt ENGINE? [x.x.x.x]: " ovirtengine
        echo ""
        read -s -e -p "What is the ROOT password for your oVirt ENGINE?: " ovirtenginepass
        echo ""
        echo ""

        SSHPASS="${ovirtenginepass}" sshpass -e ssh -o StrictHostKeyChecking=no root@${ovirtengine} exit
        if [ $? -eq 0 ]
        then
            echo "Confirmed ssh connection to ${ovirtengine}"
            ok=1
        else
            echo "SSH Connection Failed to ${ovirtengine}. Please try again."
        fi

    done

    echo ""
    echo "NFS INFORMATION FOR BACKUPS"
    echo ""

    mkdir /mnt/backups -p
    mkdir /mnt/migrate -p

    ok=0
    while [ $ok -eq 0 ]
    do

        read -e -p "Path of the NFS for /mnt/backups [x.x.x.x:/path/to/share]: " backupip
        echo ""

        read -e -p "NFS Version [3 or 4]: " -i "4" backupnfsversion
        echo ""

        if [[ "${backupnfsversion}" == "3" ]]
        then
            backupnfsversion="nfs"
            ok=1
        elif [[ "${backupnfsversion}" == "4" ]]
        then
             backupnfsversion="nfs4"
             ok=1
        else
            ok=0
        fi

        if [ $ok -eq 1 ]
        then


            mount -t $backupnfsversion -o soft,retry=0 ${backupip} /mnt/backups > /dev/null
            testnfs=`df -P -T /mnt/backups/ | tail -n +2 | awk '{print $2}'`

            if [[ "${testnfs}" == "nfs3" ]] || [[ "${testnfs}" == "nfs4" ]]  || [[ "${testnfs}" == "nfs" ]]
            then
                echo "NFS Connection Passed"
                umount /mnt/backups
            else
                ok=0
                echo "NFS Connection Failed"

            fi

        fi
    done

    echo ""
    echo ""
    echo "THIS BACKUP VM INFORMATION"
    echo ""
    read -e -p "What is the FQDN for your oVirtSimpleBackupVM? [backupengine.mydomain.com]: " backupengine
    echo ""
    read -e -p "What is the IP Address for your oVirtSimpleBackupVM (${backupengine})? [x.x.x.x]: " backupengineip
    echo ""
    echo ""
    echo "XEN SERVER INFORMATION"
    echo ""
    read -e -p "Are you wanting this script to setup migrate from Xen Server? You will need to have your ROOT password for a Xen Server Host as well as the ROOT password for a Xen VM running a FRESH Install of Debian with SSH enabled for root that will act as your VMMIGRATE [y/N]: " -i "N" xen
    if [[ $xen == 'Y' ]]
    then

        ok=0
        while [ $ok -eq 0 ]
        do

            read -e -p "Path of the NFS for /mnt/migrate [x.x.x.x:/path/to/share]: " migrateip
            echo ""

            read -e -p "NFS Version [3 or 4]: " -i "4" migratenfsversion
            echo ""

            if [[ "${migratenfsversion}" == "3" ]]
            then
                migratenfsversion="nfs"
                ok=1
            elif [[ "${migratenfsversion}" == "4" ]]
            then
                 migratenfsversion="nfs4"
                 ok=1
            else
                ok=0
            fi

            if [ $ok -eq 1 ]
            then


                mount -t $backupnfsversion -o soft,retry=0 ${migrateip} /mnt/migrate > /dev/null
                testnfs=`df -P -T /mnt/migrate/ | tail -n +2 | awk '{print $2}'`

                if [[ "${testnfs}" == "nfs3" ]] || [[ "${testnfs}" == "nfs4" ]]  || [[ "${testnfs}" == "nfs" ]]
                then
                    echo "NFS Migrate Connection Passed"
                    umount /mnt/migrate
                else
                    ok=0
                    echo "NFS Migrate Connection Failed"

                fi

            fi
        done


        ok=0
        while [ $ok -eq 0 ]
        do

            echo ""
            read -e -p "IP Address of Xen Server? [x.x.x.x]: " xenserver
            echo ""
            read -s -e -p "ROOT password for your XEN Server Host?: " xenserverpass
            echo ""
            echo ""

            SSHPASS="${xenserverpass}" sshpass -e ssh -o StrictHostKeyChecking=no root@${xenserver} exit
            if [ $? -eq 0 ]
            then
                echo "Confirmed ssh connection to ${xenserver}"
                ok=1
            else
                echo "SSH Connection Failed to ${xenserver}. Please try again."
            fi

        done



        ok=0
        while [ $ok -eq 0 ]
        do

            echo ""
            read -e -p "IP Address of Xen Server Migrate VM? [x.x.x.x]: " xenservermigrate
            echo ""
            read -s -e -p "ROOT password for your Xen Server Migrate VM?: " xenservermigratepass
            echo ""

            SSHPASS="${xenservermigratepass}" sshpass -e ssh -o StrictHostKeyChecking=no root@${xenservermigrate} exit
            if [ $? -eq 0 ]
            then
                echo "Confirmed ssh connection to ${xenservermigrate}"
                ok=1
            else
                echo "SSH Connection Failed to ${xenservermigrate}. Please try again."
            fi

        done


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
    echo "  ${backupnfsversion}"
    echo ""
    echo "oVirtSimpleBackupVM FQDN: ${backupengine}"
    echo ""
    echo "oVirtSimpleBackupVM IP: ${backupengineip}"
    echo ""
    echo "Xen Server Migration Tools: ${xen}"
    echo ""
    if [[ $xen == 'Y' ]]
    then
        echo "Path of the NFS for /mnt/migrate:"
        echo "  ${migrateip}"
        echo "  ${migratenfsversion}"
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
        apt-get -o Dpkg::Progress-Fancy="1" install pv curl zip exim4 fsarchiver parted nfs-common php7.0 php7.0-curl php7.0-xml sshpass lzop gzip -y

        echo "Updating SSH Settings"
        sed -i "s/PermitRootLogin without-password/#PermitRootLogin without-password/g" /etc/ssh/sshd_config
        echo "PermitRootLogin yes" >> /etc/ssh/sshd_config
        echo "UseDNS no" >> /etc/ssh/sshd_config
        service ssh restart

        echo "Creating Mount Directories"
        mkdir /mnt/linux -p

        echo ""
        echo "Now we have to add your NFS shares so they can mount."
        echo "Saving to /etc/fstab and mounting"

        if [ ! -z "$backupip" ]
        then
            echo "${backupip} /mnt/backups ${backupnfsversion} rw,async,hard,intr,noexec 0 0" >> /etc/fstab
            mount /mnt/backups
        fi

        if [[ $xen == 'Y' ]]
        then
            if [ ! -z "$migrateip" ]
            then
                mkdir /mnt/migrate -p
                echo "${migrateip} /mnt/migrate ${migratenfsversion} rw,async,hard,intr,noexec 0 0" >> /etc/fstab
                mount /mnt/migrate
                chmod 777 /mnt/migrate
            fi
        fi

        mkdir /root/.ssh -p
        chmod 700 /root/.ssh
        usermod -a -G disk www-data
        usermod -a -G cdrom www-data
        chown root:disk /bin/dd
        a2enmod ssl
        service apache2 restart
        mkdir /etc/apache2/ssl -p


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
        chmod 777 /mnt/backups
        chmod 777 /mnt/linux
        mkdir /var/www/.ssh -p
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
        wget -N --retry-connrefused https://github.com/zipurman/oVIRT_Simple_Backup/archive/master.zip
        unzip master.zip
        rm /var/www/html -R
        mv /var/www/oVIRT_Simple_Backup-master/server /var/www/html
        rm master.zip
        rm /var/www/oVIRT_Simple_Backup-master/ -R
        chown www-data:root /var/www -R

        echo ""  >> /var/www/html/allowed_ips.php
        echo "\$allowed_ips = array(); //REMOVE THIS LINE IF USING ARRAY ABOVE!!"  >> /var/www/html/allowed_ips.php

        echo "Updating Cron Jobs"
        echo "* * * * * root /var/www/html/crons/fixgrub.sh >>/var/log/fixgrub.log 2>&1" >> /etc/crontab
        echo "* * * * * root /var/www/html/crons/fixswap.sh >>/var/log/fixswap.log 2>&1" >> /etc/crontab
        echo "*/5 * * * * www-data php /var/www/html/automatedbackup.php >/dev/null 2>&1" >> /etc/crontab

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

        mkdir /var/www/html/plugin -p
        cp /var/www/html/plugin /opt/oVirtSimpleInstaller/ -R
        echo "{" > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"name": "simpleBackup",' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"url": "/ovirt-engine/webadmin/plugin/simpleBackup/start.html",' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"config": {' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"mainBackupPage": "//${backupengineip}/index.php"' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '},' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"resourcePath": "simpleBackup"' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '}' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json

        echo ""
        echo "*** Setting Up Plugin for oVirt Web UI ***"
        echo ""

        echo "Updating oVirtEngine"
        echo ""



        SSHPASS="${ovirtenginepass}" sshpass -e scp -r -o StrictHostKeyChecking=no /opt/oVirtSimpleInstaller/plugin/simpleBackup* root@${ovirtengine}:/usr/share/ovirt-engine/ui-plugins/

        SSHPASS="${ovirtenginepass}" sshpass -e ssh -o StrictHostKeyChecking=no root@${ovirtengine} 'chmod 755 /usr/share/ovirt-engine/ui-plugins/simpleBackup* -R && engine-config -s CORSSupport=true && engine-config -s CORSAllowedOrigins=*  && service ovirt-engine restart'


        echo "${ovirtengine} ${ovirtenginefqdn}" >> /etc/hosts
        echo "${backupengineip} ${backupengine}" >> /etc/hosts
        echo "${backupengine}" > /etc/hostname
        echo "${backupengine}" > /etc/mailname


        if [[ $xen == 'Y' ]]
        then

            echo "Updating Xen Server Host"
            echo ""
            SSHPASS="${xenserverpass}" sshpass -e ssh -o StrictHostKeyChecking=no root@${xenserver} 'mkdir /root/.ssh -p && chmod 700 /root/.ssh && echo "UseDNS no" >>  /etc/ssh/sshd_config && service sshd restart'

            echo ""
            echo "Updating Xen VMMIGRATE"
            echo ""
            SSHPASS="${xenservermigratepass}" sshpass -e ssh -o StrictHostKeyChecking=no root@${xenservermigrate} 'apt-get install pv wget lzop gzip fsarchiver chroot -y && mkdir /root/.ssh -p && chmod 700 /root/.ssh && mkdir /mnt/migrate -p && echo "${migrateip} /mnt/migrate ${backupnfsversion} rw,async,hard,intr,noexec 0 0" >> /etc/fstab && mount /mnt/migrate && chmod 777 /mnt/migrate/'

        fi

        clear
        echo ""
        echo ""
        echo "You should now be able to login to your oVirt WebUI and see the Simple Backup tab in the menu."
        echo ""
        echo "      - Navigate to oVirtWebUI: https://${ovirtengine}"
        echo "      - Navigate Directly to oVirtBackupEngineVM: https://${backupengine}"
        echo ""
        echo "      - You may also want to edit /var/www/html/allowed_ips.php to suit your needs."
        echo ""

    else
        echo "Cancelled."
    fi
else
    echo "Script must be run as root"
fi
