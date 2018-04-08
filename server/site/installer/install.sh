#!/bin/bash

# Written by Preston Lord
# oVirt Simple Backup (WebGUI) Installer Version 0.1.0
# Last Updated April 07, 2018
# written for Debian Only

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
echo "              /opt/oVirtSimpleInstaller/installer.sh"
echo ""

#( exec /opt/oVirtSimpleInstaller/installer.sh )

