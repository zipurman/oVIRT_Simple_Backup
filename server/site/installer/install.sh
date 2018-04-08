#!/bin/bash

# Written by Preston Lord
# oVirt Simple Backup (WebGUI) Installer Version 0.1.0
# Last Updated April 07, 2018
# written for Debian Only

#requires
#   - curl to be installed
#   - rem out any cdrom/dvd median in /etc/apt/sources.list
#   - apt-get update

#Run as root using:
# curl https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/server/site/installer/install.sh | bash

#Config Options
SERVERROOT="/var/www/html/"

apt-get install wget -y

#cleanup if reinstalling
rm /opt/oVirtSimpleInstaller -Rf

#make install directories
mkdir /opt/oVirtSimpleInstaller -p
cd /opt/oVirtSimpleInstaller

wget https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/server/site/installer/installer.sh

chmod 700 /opt/oVirtSimpleInstaller/installer.sh

clear

echo "You can now run the following script to install oVirt Simple Backup"
echo ""
echo "              - rem out any cdrom/dvd median in /etc/apt/sources.list"
echo "              - apt-get update"
echo "              - make sure you have your NFS ready and have all user/pass info available"
echo "              - on oVirt ENGINE - make sure root user has a ~/.ssh folder"
echo "              - on oVirt ENGINE - make sure /etc/ssh/sshd.conf is set to allow root login (restart sshd)"
echo "              - MAKE SURE this BackupEngineVM is configured for the static IP address you will use."
echo ""
echo "              - RECOMMENDED - create a snapshot now so if you have to reinstall"
echo ""
echo "              Run installer:"
echo ""
echo "              /opt/oVirtSimpleInstaller/installer.sh"
echo ""


#( exec /opt/oVirtSimpleInstaller/installer.sh )

