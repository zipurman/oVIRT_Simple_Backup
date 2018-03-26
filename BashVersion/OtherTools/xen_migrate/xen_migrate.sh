#!/bin/bash

#####################################
#
# Xen Migrate
# Version: 0.1.1
#
# Built as a tool for oVIRT_Simple_Backup
#
# Date: 01/27/2018
#
# Simple Script to image a disk from Xen and migrate to NFS
# Then to use oVIRT_Simple_Backup to restore migration
#
# Script on github.com
# https://github.com/zipurman/oVIRT_Simple_Backup
#
# Author: zipur (www.zipur.ca)
# IRC: irc.oftc.net #ovirt
#
# Required Packages: pv, dialog, fsarchiver, chroot
#
# Tested on: Debian8
#
# Warning !!! Use script at your own risk !!!
# There is no guarantee the script will work
# for your environment. It is recommended that
# you test this script in a NON PRODUCTION
# environment with your setup to make sure
# it works as expected.
#
#####################################

# Usage: xen_migrate.sh <disk_device> <migrate_nfs_path> <vmname> <sizeofpart_in_gb>
# See # https://github.com/zipurman/oVIRT_Simple_Backup for more details on setup and usage

#####################################


xbuversion="0.1.1"
xbutitle="\Zb\Z3Xen Migrate Tool for oVirt\ZB\Zn - \Zb\Z1Simple Backup\ZB\Zn - \Zb\Z0Version ${xbuversion}\ZB\Zn"

#start check packages
packagesokay=1
if ! [ -x "$(command -v pv)" ]; then
    echo "Package: pv - missing and is required for this script."
    packagesokay=0
fi
if ! [ -x "$(command -v dialog)" ]; then
    echo "Package: dialog - missing and is required for this script."
    packagesokay=0
fi
if ! [ -x "$(command -v fsarchiver)" ]; then
    echo "Package: fsarchiver - missing and is required for this script."
    packagesokay=0
fi
if ! [ -x "$(command -v chroot)" ]; then
    echo "Package: chroot - missing and is required for this script."
    packagesokay=0
fi
if [ $packagesokay -eq 0 ];then
    exit 0
fi
#end check packages

while test $# -gt 0; do
    case "$1" in
    --headless)
        headless="1"
        break
        ;;
    -h|--help)
        shift
        echo -e "\n\tXen Migrate Tool for oVirt: ${xbuversion}\n\n\t./xen_migrate.sh <disk_device> <migrate_nfs_path> <vmname> <sizeofpart_in_gb>\n\n\toptions:\n"
        echo -e "\t\t-h, --help\t\tshow brief help\n"
        shift
        exit
        ;;
    *)
        break
        ;;
    esac
done

#/dev/xvdb
disk_device="${1}"

#/mnt/migrate
migrate_nfs_path="${2}"

#MyVMName
vmname="${3}"

sizeofpart="${4}"

echo $sizeofpart


echo $sizeofpart


(pv -n ${disk_device} | dd of="${migrate_nfs_path}/${vmname}.img" bs=1M conv=notrunc,noerror) 2>&1 | dialog --cr-wrap --colors --backtitle "${xbutitle}" --title "Exporting Image from Xen" --gauge "Size: ${sizeofpart}GB\n\n  Device: ${disk_device}\n\nExporting to: ${migrate_nfs_path}/${vmname}.img" 22 80 0

clear
echo "Image Done"
