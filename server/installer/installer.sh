#!/bin/bash

# Written by Preston Lord aka Zipur
# Last Updated Nov 1, 2018
# written for Debian ONLY!!

if [ ! -f "/etc/debian_version" ]; then
    echo -e "This script will only work on Debian"
    exit 0
fi

echo -e "Checking to see if anything needs to be installed ..."
apt-get install sshpass nfs-common -y

defaulty="y"
defaultn="n"

clear

echo -e "\n    __      ___      _      _____ _                 _        ____             _                ";
echo -e "    \ \    / (_)    | |    / ____(_)               | |      |  _ \           | |               ";
echo -e "   __\ \  / / _ _ __| |_  | (___  _ _ __ ___  _ __ | | ___  | |_) | __ _  ___| | ___   _ _ __  ";
echo -e "  / _ \ \/ / | | '__| __|  \___ \| | '_ \` _ \| '_ \| |/ _ \ |  _ < / _\` |/ __| |/ / | | | '_ \ ";
echo -e " | (_) \  /  | | |  | |_   ____) | | | | | | | |_) | |  __/ | |_) | (_| | (__|   <| |_| | |_) |";
echo -e "  \___/ \/   |_|_|   \__| |_____/|_|_| |_| |_| .__/|_|\___| |____/ \__,_|\___|_|\_\\__,_| .__/ ";
echo -e "                                             | |                                        | |    ";
echo -e "                                             |_|                                        |_|    ";
echo -e "\n                 oVirt Simple Backup (WebGUI) Installer Version 0.1.4"
echo -e "                          Written for Debian 8/9 Installs Only\n\n"
echo -e "It is recommended that your read the following linked page and understand it as best as you can prior to continuing.\n\n"
echo -e "\thttps://github.com/zipurman/oVIRT_Simple_Backup/blob/master/docs/install_debian.md\n\n"

while [[ $readthelinkyn == '' ]] || [[ ! $(fgrep -ix $readthelinkyn <<< 'Y') ]]
do
    read -e -p "Have you read the page linked above? [y/N]: "  readthelinkyn
    readthelinkyn="${readthelinkyn:-${defaultn}}"
    if [[ $(fgrep -ix $readthelinkyn <<< 'N') ]]
    then
        echo -e "\nPlease go read it before continuing.\n"
    fi
done

echo -e ""

read -e -p "Are you running this script on a Debian VM in your oVirt Environment? [Y/n]: " ovirtenviroyn
ovirtenviroyn="${ovirtenviroyn:-${defaulty}}"
if [[ ! $(fgrep -ix $ovirtenviroyn <<< 'Y') ]]
then
    echo -e "\n\nThis script is only intended to be run on a Debian VM on your oVirt Environment!\n\n"
    echo -e "Please read the information provided in the link above.\n\n"
    exit 0
fi

echo -e ""

read -e -p "Will you be using this script to migrate VMs from a Xen Server Environment? [y/N]: " xen
    xen="${xen:-${defaultn}}"
    if [[ $(fgrep -ix $xen <<< 'Y') ]]
    then
        echo -e "\nThe way that oVirtSimpleBackup is able to migrate from xenserver is as follows:"
        echo -e "\n\t- a Debian VM is manually installed in your XenServer Environment and called VMMIGRATE."
        echo -e "\n\t- VMMIGRATE will do all of the imaging on the Xenserver VMs to /mnt/migrate."
        echo -e "\n\t- a Debian VM is also created in your oVirt Environment (THIS VM) and called oVirtSimpleBackupEngine."
        echo -e "\n\t- oVirtSimpleBackupEngine will do all of the overhead to command XenServer, VMMIGRATE, and oVirtEngine to do what is required."
        echo -e "\n\t- oVirtSimpleBackupEngine will also image the new VMs in oVirt from the XenServer images created in /mnt/migrate.\n\n"
        read -e -p "Have you already created a Debian VM in your XenServer Environment and called it VMMIGRATE? [y/N]: " xenvmmigrate

        xenvmmigrate="${xenvmmigrate:-${defaultn}}"
        if [[ ! $(fgrep -ix $xenvmmigrate <<< 'Y') ]]
        then
            echo -e "\n\t** You must create the VMMIGRATE VM in your Xen Server Environment before using this script.\n"
            echo -e "\n\tGo to your XenServer Environment and follow the instructions in STEP 3 of this page:\n"
            echo -e "\n\t- https://github.com/zipurman/oVIRT_Simple_Backup/blob/master/docs/install_debian.md\n"
            echo -e "\n\t- After installing VMMIGRATE and following the other settings for Xen in the above link, return to running this script on this VM.\n\n"
            exit 0
        fi
    fi

who=`whoami`

if [[ $who == 'root' ]]
then

    echo -e "\n\nOVIRT ENGINE INFORMATION\n"

    ok=0
    while [ $ok -eq 0 ]
    do
        echo -e "The following questions are about your oVirt ENGINE (Hypervisor)."
        echo -e "The oVirt ENGINE is the master over your entire oVirt environment."
        echo -e "The following is required to allow oVirtSimpleBackup to talk to the oVirt Engine. Answer Carefully.\n\n"

        while [[ $ovirtenginefqdn == '' ]]
        do
            read -e -p "What is the FQDN for your oVirtENGINE? [engine.mydomain.com]: " ovirtenginefqdn
        done

        echo -e ""

        while [[ $ovirtengine == '' ]]
        do
            read -e -p "What is the IP Address for your oVirtENGINE? $ovirtenginefqdn [x.x.x.x]: " ovirtengine
        done

        echo -e ""

        while [[ $ovirtenginepass == '' ]]
        do
            read -s -e -p "What is the ROOT password for your oVirtENGINE?: " ovirtenginepass
        done

        echo -e "\n"

        SSHPASS="${ovirtenginepass}" sshpass -e ssh -o StrictHostKeyChecking=no root@${ovirtengine} exit

        if [ $? -eq 0 ]
        then
            echo -e "\nConfirmed ssh connection to ${ovirtengine}"
            ok=1
        else
            echo -e "\nSSH Connection Failed to ${ovirtengine}. Please try again."
        fi

    done

    echo -e "\nNFS INFORMATION FOR BACKUPS\n"

    mkdir /mnt/backups -p
    mkdir /mnt/migrate -p

    ok=0
    while [ $ok -eq 0 ]
    do

        echo -e "\nThe following questions are for your NFS setup. You cannot use local storage on this simpleBackupVM for your /mnt/backup or /mnt/migrate paths. These should be stored on a good NFS share accessible by all of your VMs.\n\n"

        while [[ $backupip == '' ]]
        do
            read -e -p "Path to the NFS for /mnt/backups [x.x.x.x:/path/to/share]: " backupip
        done

        echo -e ""

        while [[ $backupnfsversion == '' ]]
        do
            read -e -p "NFS Version [3 or 4]: " backupnfsversion
        done

        echo -e ""

        if [[ "${backupnfsversion}" == "3" ]]
        then
            backupnfsversion="nfs"
            ok=1
        elif [[ "${backupnfsversion}" == "4" ]]
        then
             backupnfsversion="nfs4"
             ok=1
        else
            echo -e "\nYou must choose either 3 or 4\n"
            ok=0
        fi

        if [ $ok -eq 1 ]
        then

            mount -t $backupnfsversion -o soft,retry=0 ${backupip} /mnt/backups > /dev/null
            testnfs=`df -P -T /mnt/backups/ | tail -n +2 | awk '{print $2}'`

            if [[ "${testnfs}" == "nfs3" ]] || [[ "${testnfs}" == "nfs4" ]]  || [[ "${testnfs}" == "nfs" ]]
            then
                echo -e "NFS Connection Passed"
                umount /mnt/backups
            else
                ok=0
                echo -e "NFS Connection Failed"

            fi

        fi
    done

    echo -e "\n\nTHIS BACKUP VM INFORMATION\n\n"
    echo -e "The following questions are regarding THIS VM oVirtSimpleBackupVM:\n\n"

    while [[ $backupengine == '' ]]
    do
        read -e -p "What is the FQDN for this oVirtSimpleBackupVM? [backupengine.mydomain.com]: " backupengine
    done

    echo -e ""

    while [[ $backupengineip == '' ]]
    do
        read -e -p "What is the IP Address for this oVirtSimpleBackupVM (${backupengine})? [x.x.x.x]: " backupengineip
    done

    if [[ $(fgrep -ix $xen <<< 'Y') ]]
    then
    echo -e "\n\nXEN SERVER INFORMATION\n\n"
    echo -e "For the XenServer integration, you MUST make sure all of the following is available:\n\n";
    echo -e "\t- You will need to have your ROOT password for one of your Xen Server Hosts.\n"
    echo -e "\t- You will need to have your ROOT password for your VMMIGRATE.\n"
    echo -e "\t- Your VMMIGRATE VM MUST be running and ready for this script to interact with before proceeding.\n"
    echo -e "\t- Your selected Xen Server Hosts MUST be running and ready for this script to interact with before proceeding.\n\n"

    while [[ $oktokeepgoing == '' ]] || [[ ! $(fgrep -ix $oktokeepgoing <<< 'Y') ]]
    do
        read -e -p "Is the above Xen Server Information and Status ready to go? [Y/n]: "  oktokeepgoing
        oktokeepgoing="${oktokeepgoing:-${defaulty}}"
        if [[ $(fgrep -ix $oktokeepgoing <<< 'N') ]]
        then
            echo -e "\n\nYou will have to re-run this script after you have the required information and status to continue.\n\n"
            exit 0
        fi
    done

        ok=0
        while [ $ok -eq 0 ]
        do

            while [[ $migrateip == '' ]]
            do
                read -e -p "Path of the NFS for /mnt/migrate [x.x.x.x:/path/to/share]: " migrateip
            done

            echo -e ""

            while [[ $migratenfsversion == '' ]]
            do
                read -e -p "NFS Version [3 or 4]: " -i "4" migratenfsversion
            done

            echo -e ""

            if [[ "${migratenfsversion}" == "3" ]]
            then
                migratenfsversion="nfs"
                ok=1
            elif [[ "${migratenfsversion}" == "4" ]]
            then
                 migratenfsversion="nfs4"
                 ok=1
            else
                echo -e "\nYou must choose either 3 or 4\n"
                ok=0
            fi

            if [ $ok -eq 1 ]
            then

                mount -t $backupnfsversion -o soft,retry=0 ${migrateip} /mnt/migrate > /dev/null
                testnfs=`df -P -T /mnt/migrate/ | tail -n +2 | awk '{print $2}'`

                if [[ "${testnfs}" == "nfs3" ]] || [[ "${testnfs}" == "nfs4" ]]  || [[ "${testnfs}" == "nfs" ]]
                then
                    echo -e "NFS Migrate Connection Passed"
                    umount /mnt/migrate
                else
                    ok=0
                    echo -e "NFS Migrate Connection Failed"
                fi

            fi
        done


        ok=0
        while [ $ok -eq 0 ]
        do

            echo -e ""

            while [[ $xenserver == '' ]]
            do
                read -e -p "IP Address of Xen Server? [x.x.x.x]: " xenserver
            done


            echo -e ""

            while [[ $xenserverpass == '' ]]
            do
                read -s -e -p "ROOT password for your XEN Server Host?: " xenserverpass
            done

            echo -e "\n"

            SSHPASS="${xenserverpass}" sshpass -e ssh -o StrictHostKeyChecking=no root@${xenserver} exit

            if [ $? -eq 0 ]
            then
                echo -e "Confirmed ssh connection to ${xenserver}"
                ok=1
            else
                echo -e "SSH Connection Failed to ${xenserver}. Please try again."
            fi

        done



        ok=0
        while [ $ok -eq 0 ]
        do

            echo -e ""

            while [[ $xenservermigrate == '' ]]
            do
                read -e -p "IP Address of VMMIGRATE? [x.x.x.x]: " xenservermigrate
            done

            echo -e ""

            while [[ $xenservermigratepass == '' ]]
            do
                read -s -e -p "ROOT password for VMMIGRATE: " xenservermigratepass
            done

            echo -e ""

            SSHPASS="${xenservermigratepass}" sshpass -e ssh -o StrictHostKeyChecking=no root@${xenservermigrate} exit

            if [ $? -eq 0 ]
            then
                echo -e "Confirmed ssh connection to ${xenservermigrate}"
                ok=1
            else
                echo -e "SSH Connection Failed to ${xenservermigrate}. Please try again."
            fi

        done


    fi

    echo -e "\n======================================"
    echo -e "oVirt Simple Backup (WebGUI) Installer"
    echo -e "======================================\n"
    echo -e "FQDN for your oVirt ENGINE: ${ovirtenginefqdn}\n"
    echo -e "IP Address for your oVirt ENGINE: ${ovirtengine}\n"
    echo -e "Path of the NFS for /mnt/backups:"
    echo -e "\t${backupip}"
    echo -e "\t${backupnfsversion}\n"
    echo -e "oVirtSimpleBackupVM FQDN: ${backupengine}\n"
    echo -e "oVirtSimpleBackupVM IP: ${backupengineip}\n"
    echo -e "Xen Server Migration Tools: ${xen}\n"

    if [[ $(fgrep -ix $xen <<< 'Y') ]]
    then
        echo -e "Path of the NFS for /mnt/migrate:"
        echo -e "\t${migrateip}"
        echo -e "\t${migratenfsversion}\n"
        echo -e "IP Address of Xen Server: ${xenserver}\n"
        echo -e "IP Address of Xen Server Migrate VM: ${xenservermigrate}\n"
        echo -e "IP Address for your oVirt Engine: ${ovirtengine}"
    fi
    echo -e "\n"

    while [[ $oktoinstall == '' ]] || [[ ! $(fgrep -ix $oktoinstall <<< 'Y') ]]
    do
        read -e -p "Do these settings look correct? [Y/n]: "  oktoinstall
        oktoinstall="${oktoinstall:-${defaulty}}"
        if [[ $(fgrep -ix $oktoinstall <<< 'N') ]]
        then
            echo -e "\n\nYou will have to re-run this script.\n\n"
            exit 0
        fi
    done

    if [[ $(fgrep -ix $oktoinstall <<< 'Y') ]]
    then
        echo -e "\n======================================"
        echo -e "oVirt Simple Backup (WebGUI) Installer"
        echo -e "======================================"
        echo -e "\nInstalling...."
        apt-get -o Dpkg::Progress-Fancy="1" install pv curl zip exim4 fsarchiver parted nfs-common php7.0 php7.0-curl php7.0-xml sshpass lzop gzip apache2 -y

        echo -e "Updating SSH Settings"
        sed -i "s/PermitRootLogin without-password/#PermitRootLogin without-password/g" /etc/ssh/sshd_config
        echo -e "PermitRootLogin yes" >> /etc/ssh/sshd_config
        echo -e "UseDNS no" >> /etc/ssh/sshd_config
        service ssh restart

        echo -e "Creating Mount Directories"
        mkdir /mnt/linux -p

        echo -e "\nNow we have to add your NFS shares so they can mount."
        echo -e "Saving to /etc/fstab and mounting"

        if [ ! -z "$backupip" ]
        then
            echo -e "${backupip} /mnt/backups ${backupnfsversion} rw,async,hard,intr,noexec 0 0" >> /etc/fstab
            mount /mnt/backups
            chmod 777 /mnt/backups
            echo -e "\t- /mnt/backups added to /etc/fstab and mounted"
        fi

        if [[ $(fgrep -ix $xen <<< 'Y') ]]
        then
            if [ ! -z "$migrateip" ]
            then
                echo -e "${migrateip} /mnt/migrate ${migratenfsversion} rw,async,hard,intr,noexec 0 0" >> /etc/fstab
                mount /mnt/migrate
                chmod 777 /mnt/migrate
                echo -e "\t- /mnt/migrate added to /etc/fstab and mounted"
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

        echo -e "Creating SSL Certificate for oVirtSimpleBackup"
        openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt -subj "/C=CA/ST=Saskatchewan/L=SwiftCurrent/O=Global Security/OU=IT Department/CN=${backupengine}"

        chmod 600 /etc/apache2/ssl/*

        echo -e "Updating Apache"
        sed -i "s/\/var\/www\/html/\/var\/www\/html\/site\nServerName ${backupengine}:443/g" /etc/apache2/sites-available/default-ssl.conf
        sed -i "s/\/var\/www\/html/\/var\/www\/html\/site\nServerName ${backupengine}:80/g" /etc/apache2/sites-available/000-default.conf
        sed -i "s/SSLEngine on/SSLEngine on\nSSLCertificateFile \/etc\/apache2\/ssl\/apache.crt\nSSLCertificateKeyFile \/etc\/apache2\/ssl\/apache.key/g" /etc/apache2/sites-available/default-ssl.conf
        a2ensite default-ssl.conf
        service apache2 reload

        chsh -s /bin/bash www-data
        chmod 777 /mnt
        chmod 777 /mnt/linux
        mkdir /var/www/.ssh -p
        chown www-data:www-data /var/www/.ssh
        chmod 700 /var/www/.ssh
        su - www-data -c 'ssh-keygen -t rsa'

        if [[ $(fgrep -ix $xen <<< 'Y') ]]
        then
            echo -e "\n\nPassword is for root user of Xen Server"
            su - www-data -c "ssh-copy-id root@${xenserver}"
            echo -e "\n\nPassword is for root user of Xen Server Migrate VM"
            su - www-data -c "ssh-copy-id root@${xenservermigrate}"
        fi

        cd /var/www/
        wget -N --retry-connrefused https://github.com/zipurman/oVIRT_Simple_Backup/archive/master.zip
        unzip master.zip

        rm /var/www/html -R
        mv /var/www/oVIRT_Simple_Backup-master/server /var/www/html
        mv /var/www/oVIRT_Simple_Backup-master/plugin /opt/oVirtSimpleInstaller/
        rm master.zip
        rm /var/www/oVIRT_Simple_Backup-master/ -R
        chown www-data:root /var/www -R

        echo -e ""  >> /var/www/html/allowed_ips.php
        echo -e "\$allowed_ips = array(); //REMOVE THIS LINE IF USING ARRAY ABOVE!!"  >> /var/www/html/allowed_ips.php

        echo -e "Updating Cron Jobs"
        echo -e "* * * * * root /var/www/html/crons/fixgrub.sh >>/var/log/fixgrub.log 2>&1" >> /etc/crontab
        echo -e "* * * * * root /var/www/html/crons/fixswap.sh >>/var/log/fixswap.log 2>&1" >> /etc/crontab
        echo -e "*/5 * * * * www-data php /var/www/html/automatedbackup.php >/dev/null 2>&1" >> /etc/crontab

        chmod 700 /var/www/html/crons/fixgrub.sh
        chmod 700 /var/www/html/crons/fixswap.sh

        echo -e "Setting Default Configs"
        echo -e "<?php" > /var/www/html/config.php
        echo -e "\$settings = array(" >> /var/www/html/config.php
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
        echo '"compress" => "0",' >> /var/www/html/config.php
        echo '"withoutmemory" => "0",' >> /var/www/html/config.php
        echo ');' >> /var/www/html/config.php
        chown www-data:www-data /var/www/html/config.php
        touch /var/log/simplebackup.log
        chown www-data:www-data /var/log/simplebackup.log
        echo -e ""

        echo '{' > /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"name": "simpleBackup",' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"url": "/ovirt-engine/webadmin/plugin/simpleBackup/start.html",' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"config": {' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"mainBackupPage": '"\"//${backupengine}/index.php\"" >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '},' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '"resourcePath": "simpleBackup"' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json
        echo '}' >> /opt/oVirtSimpleInstaller/plugin/simpleBackup.json

        echo -e "\n*** Setting Up Plugin for oVirt Web UI ***\n"

        echo -e "Updating oVirtEngine\n"

        SSHPASS="${ovirtenginepass}" sshpass -e scp -r -o StrictHostKeyChecking=no /opt/oVirtSimpleInstaller/plugin/simpleBackup* root@${ovirtengine}:/usr/share/ovirt-engine/ui-plugins/

        SSHPASS="${ovirtenginepass}" sshpass -e ssh -o StrictHostKeyChecking=no root@${ovirtengine} 'chmod 755 /usr/share/ovirt-engine/ui-plugins/simpleBackup* -R && engine-config -s CORSSupport=true && engine-config -s CORSAllowedOrigins=*  && service ovirt-engine restart'

        echo -e "${ovirtengine} ${ovirtenginefqdn}" >> /etc/hosts
        echo -e "${backupengineip} ${backupengine}" >> /etc/hosts
        echo -e "${backupengine}" > /etc/hostname
        echo -e "${backupengine}" > /etc/mailname


        #if [[ $(fgrep -ix $xen <<< 'Y') ]]
        #then
            #THESE STEPS SHOULD ALREADY BE DONE IF INSTRUCTIONS FOLLOWED TO REFER TO https://github.com/zipurman/oVIRT_Simple_Backup/blob/master/docs/install_debian.md
            #echo -e "Updating Xen Server Host\n"
            #SSHPASS="${xenserverpass}" sshpass -e ssh -o StrictHostKeyChecking=no root@${xenserver} 'mkdir /root/.ssh -p && chmod 700 /root/.ssh && echo -e "UseDNS no" >>  /etc/ssh/sshd_config && service sshd restart'

            #echo -e "\nUpdating Xen VMMIGRATE\n"
            #SSHPASS="${xenservermigratepass}" sshpass -e ssh -o StrictHostKeyChecking=no root@${xenservermigrate} 'apt-get install pv wget lzop gzip fsarchiver chroot -y && mkdir /root/.ssh -p && chmod 700 /root/.ssh && mkdir /mnt/migrate -p && echo '"${migrateip} /mnt/migrate ${backupnfsversion} rw,async,hard,intr,noexec 0 0"' >> /etc/fstab && mount /mnt/migrate && chmod 777 /mnt/migrate/'

        #fi

        echo -e "\n\nYou should now be able to login to your oVirt WebUI and see the Simple Backup tab in the menu.\n"
        echo -e "\t- Navigate to oVirtWebUI: https://${ovirtenginefqdn}"
        echo -e "\t- Navigate Directly to oVirtBackupEngineVM: https://${backupengine}\n\n"
        echo -e "\t- You may also want to edit /var/www/html/allowed_ips.php to suit your needs.\n"

    else
        echo -e "Cancelled."
    fi
else
    echo -e "Script must be run as root"
fi
