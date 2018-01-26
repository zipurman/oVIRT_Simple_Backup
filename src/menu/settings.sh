#!/bin/bash
#####################################
#
# oVIRT_Simple_Backup
#
# Simple Script to backup VMs running on oVirt to Export Storage
#
# Script on github.com
# https://github.com/zipurman/oVIRT_Simple_Backup
#
# Author: zipur (www.zipur.ca)
# IRC: irc.oftc.net #ovirt

 # /settings is selected -
if [ "${_return}" = "S" ] && [ "${menuposition}" = "frombase" ];then
    obusettings "Enter the backup PRE-LABEL for each of your backups. \n\nFor example:\n\n BU_ \n\nwould result in a backup in the format of \n\n\Z1BU_20180124_123000\Zn\n\n" 3 "base" "0"
fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "settings3" ];then
    obusettings "Enter the UUID of your backup VM\n\n(This VM)\n\n(xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxx)\n\n" 4 "frombase" "S"
fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "settings4" ];then
    obusettings "Enter the FQDN of the oVirt Engine\n\n(ovirtmgmt.mydomain.com)\n\n" 5
fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "settings5" ];then
    obusettings "Enter the username for admin of your oVirt Engine\n\n(admin@internal)\n\n" 6
fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "settings6" ];then
    obusettings "Enter the password for admin of your oVirt Engine\n\n" 7
fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "settings7" ];then
    obusettings "Enter the path to your mounted NFS backup storage\n\n(/mnt/backups)\n\n" 8
fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "settings8" ];then
    obusettings "Enter the device type\n\n(vd for vdb, vdc, vdd, etc)\n\n(sd for sdb, sdc, sdd, etc)\n\nThis is controlled by how you connect your VMs to oVirt disks. You need to match that setting here.\n\n" 9
fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "settings9" ];then
    obusettings "Enter the disk interface type\n\n (virtio or virtio_scsi)\n\n" 10
fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "settings10" ];then
    obusettings "Increment disk devices \n\n0=no(recommended) \n\n1=yes \n\n(This is only required if your Backup Appliance VM does not release devices after each VM is backed up)\n\n" 11
fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "settings11" ];then
    obusettings "Enter the location for your backup log \n\n(/var/log/lastbackup.log)\n\n" 12
fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "settings12" ];then
    obusettings "Enter the email address for backup notifications \n\n(empty for none)\n\n" 13
fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "settings13" ];then
    obusettings "Enter the number of backups for each VM to keep (1)\n\n" 14
fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "settings14" ];then
    ./$(basename $0) && exit;
fi