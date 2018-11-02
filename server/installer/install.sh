#!/bin/bash

# Written by Preston Lord
# oVirt Simple Backup (WebGUI) Installer Version 0.1.1
# Last Updated Aug 31, 2018
# written for Debian Only

#requires
#   - curl to be installed
#   - rem out any cdrom/dvd median in /etc/apt/sources.list
#   - apt-get update

#Run as root using:
# curl https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/server/installer/install.sh | bash

if [ ! -f "/etc/debian_version" ]; then
    echo "This script will only work on Debian"
    exit 0
fi

#Config Options
SERVERROOT="/var/www/html/"

apt-get install wget -y

#cleanup if reinstalling
rm /opt/oVirtSimpleInstaller -Rf

#make install directories
mkdir /opt/oVirtSimpleInstaller -p
cd /opt/oVirtSimpleInstaller

wget https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/server/installer/installer.sh

chmod 700 /opt/oVirtSimpleInstaller/installer.sh

clear

echo "MAKE SURE YOU ARE RUNNING THIS ON A FRESHLY INSTALLED DEBIAN VM IN YOUR OVIRT ENVIRONMENT"
echo ""
echo "!!! DO NOT RUN THIS SCRIPT ON A VM IN THE XENSERVER ENVIRONMENT !!!"
echo ""
echo "You can now run the following script to install oVirt Simple Backup"
echo ""
echo "              - rem out any cdrom/dvd median in /etc/apt/sources.list"
echo "              - apt-get update"
echo "              - make sure you have your NFS ready and have all user/pass info available"
echo "              - on oVirt ENGINE - make sure root user has a ~/.ssh folder"
echo "              - on oVirt ENGINE - make sure /etc/ssh/sshd.conf is set to allow root login (restart sshd)"
echo "              - MAKE SURE this oVirtSimpleBackupVM is configured for the static IP address you will use."
echo ""
echo "              - RECOMMENDED - create a snapshot now so if you have to reinstall"
echo ""
echo "              Run installer:"
echo ""
echo "              /opt/oVirtSimpleInstaller/installer.sh"
echo ""


#( exec /opt/oVirtSimpleInstaller/installer.sh )

