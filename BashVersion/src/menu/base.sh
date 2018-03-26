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

    if [ -n "$1" ] && [ "$1" = "nav" ]
    then
        _return="$2"
        menuposition="$3"
        nav_value="0"
    else
        menuposition="base"
    fi

    numofbackups=`echo $vmlisttobackup | sed 's/\[/\n&\n/g' | grep -cx '\['`

    if [ "${menuposition}" = "base" ]
    then
        menuposition="frombase"
        dialog --cr-wrap --colors --backtitle "${obutitle}" --title "${obutitle}" --cancel-label "Exit" \
        --menu "   " 25 50 50 \
        "0" "Select VMs to Backup (${numofbackups})" \
        "-" "----------------------------" \
        "1" "Shutdown a VM" \
        "2" "Start a VM" \
        "-" "----------------------------" \
        "3" "Backup a Single VM" \
        "4" "Restore a Single VM" \
        "-" "----------------------------" \
        "5" "Run Backup of (${numofbackups}) Selected VMs " \
        "-" "----------------------------" \
        "6" "Migrate VM Images" \
        "-" "----------------------------" \
        "S" "Settings" \
         2> $menutmpfile
         _return=$(cat $menutmpfile)
         nav_value=$?

         if [ "${_return}" = "-" ]; then ./$(basename $0) && exit; fi
         if [ "${_return}" = "" ] && [ "$?" = "0" ]; then clear; echo -e "\n\nSee you next time ;)\n\n"; fi

    fi

    if [ "$nav_value" = "0" ] || [ "$1" = "nav" ]
    then

        source src/menu/vmselected.sh
        source src/menu/vmstoplist.sh
        source src/menu/vmstartlist.sh
        source src/menu/vmbackupsingle.sh
        source src/menu/vmrestoresingle.sh
        source src/menu/vmmigrate.sh
        source src/menu/settings.sh

        if [ "${_return}" = "5" ] && [ "${menuposition}" = "frombase" ];
        then
            dialog --cr-wrap --colors --backtitle "${obutitle}" --title "${obutitle}" --yesno "\nAre you sure you want to backup these ${numofbackups} VMs now?" 7 60
            response=$?
            case $response in
               0) ./$(basename $0) --headless && exit;;
               1) ./$(basename $0) && exit;;
               255) ./$(basename $0) && exit;;
            esac
        fi
    fi