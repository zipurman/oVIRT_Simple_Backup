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

############################ CHOOSE A VM #######################################
#select VMs for backup
if [ "${_return}" = "6" ] && [ "${menuposition}" = "frombase" ]
then

    #check to make sure disk is available
    if [ -e "/dev/${second_disk_device}${extradiskdev}${diskletter}" ]
    then
        obualert "\n\nYou must restart your VM before doing a restore. Disks (/dev/${second_disk_device}${extradiskdev}${diskletter}) are locked from previous backup or restore."
        ./$(basename $0) && exit;
    fi

    dialog --cr-wrap --colors --backtitle "${obutitle}" --title "${obutitle}" --yesno "\nThis migrate utility assumes that you have imaged VMs to the same directory that you have outlined in your settings as your migrate path. Continue?" 10 70
    response=$?
    case $response in
       0) menuposition="";;#YES
       1) ./$(basename $0) && exit;;#NO
       255) ./$(basename $0) && exit;;#CANCEL
    esac

    restorepath="${migrate_nfs_mount_path}"
    menuposition="migratesinglevm3"
    optionstext=""
    optionid="1"
    for filename in ${migrate_nfs_mount_path}/*;
        do
            file_size_kb=`du -k "${filename}" | cut -f1`
            file_size_gb=$((file_size_kb / 1024 / 1024))

            filename=${filename//$migrate_nfs_mount_path\//}
            vmname="${filename}"



            optionstext="${optionstext} ${vmname} ${file_size_gb}GB off"
            optionid=$((optionid + 1))
    done

    dialog --colors --column-separator "|" --backtitle "${obutitle}" --title "Migrate List" --ok-label "SELECT" --cancel-label "Main Menu" --cr-wrap --radiolist "Choose an Image to restore:" 25 50 50 $optionstext 2> $menutmpfile; nav_value=$? ; _return=$(cat $menutmpfile)
    #catch nothing chosen
    if [ "${_return}" = "" ] && [ $nav_value -eq 0 ]; then ./$(basename $0) nav 6 migratesinglevm2 && exit; fi

fi


############################ CONFIRM VM #######################################


if [ "${nav_value}" = "1" ] && [ "${menuposition}" = "migratesinglevm3" ];then ./$(basename $0) && exit; fi
if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "migratesinglevm3" ]
then
    obucheckoktostart
    obuloadsettings
    menuposition="migratesinglevm4"
    restorepath="${restorepath}/${_return}"
    restoreokay=1
    errortext=""

        xml=`echo $xmldata | xmlstarlet sel -T -t -m /ovf:Envelope/Content -s D:N:- "@ovf:id" -v "concat(Name,'|',ExportDate,'|',CreationDate,'|',ClusterCompatibilityVersion,'|',MinAllocatedMem,'|',PredefinedProperties,'|',MaxMemorySizeMb,';')"`
        SAVEIFS=$IFS
        IFS="|"
        xmlarr=($xml)
        IFS=$SAVEIFS

        originaluuid=`echo $xmldata | xmlstarlet sel -T -t -m /ovf:Envelope/Content/Section -s D:N:- "@ovf:id" -v "@ovf:id"`

        file_size_kb=`du -k "${restorepath}" | cut -f1`
        file_size_gb=$((file_size_kb / 1024 / 1024))

        showdatatext="Please confirm the following matches what you are wanting to restore:\n\n"
        showdatatext="${showdatatext}Name: \Z4${_return}\Zn\n"
        showdatatext="${showdatatext}Disk Size: \Z4${file_size_gb} GB\Zn\n"
        showdatatext="${showdatatext}\n\nYou will be prompted for a new VM Name to restore this image to after you confirm."

        restoreorclone=2

        dialog --cr-wrap --colors --backtitle "${obutitle}" --title "${obutitle}" --yesno "${showdatatext}" 26 70
        response=$?
        case $response in
           0) menuposition="migratesinglevm4";;#YES
           1) bp=$(dirname $restorepath);./$(basename $0) nav 6 frombase && exit;;#NO
           255) ./$(basename $0) && exit;;#CANCEL
        esac

fi

############################ CHOOSE A VM NEW NAME #######################################

if [ "${response}" = "0" ] && [ "${menuposition}" = "migratesinglevm4" ]
then

    dialog --colors --backtitle "${obutitle}" --title "Choose New VM Name" --ok-label "MIGRATE NOW" --cancel-label "CANCEL" --cr-wrap --inputbox "Enter a new VM Name for Migrated VM.\n\nNote: Must not contain any spaces or punctuation!\n\n"  25 60 ""  2> $menutmpfile; nav_value=$?;_return=$(cat $menutmpfile);

    #MAIN MENU
    if [ $nav_value -eq 1 ]; then bp=$(dirname $restorepath);./$(basename $0) nav 6 migratesinglevm2 $bp && exit; fi

    #EMPTY VM NAME
    if [ "${_return}" = "" ]; then
        obualert "\n\nYou must supply a new name for the restored VM"
        bp=$(dirname $restorepath);./$(basename $0) nav 6 migratesinglevm2 $bp && exit;
    fi
    #ZERO SIZED IMAGE
    if [ $file_size_gb -eq 0 ]; then
        obualert "\n\nThe file you chose has a zero size. Cannot restore."
        bp=$(dirname $restorepath);./$(basename $0) nav 6 migratesinglevm2 $bp && exit;
    fi

    #NEXT
    if [ $nav_value -eq 0 ]; then
        file_size_bytes=$(($file_size_gb * 1024 * 1024 * 1024))

        donetext=""
        fixgrub=0
        fixswap=0

        dialog --colors --column-separator "|" --backtitle "${obutitle}" --title "Extra Operationst" --ok-label "Next" --cancel-label "Main Menu" --cr-wrap --checklist "Choose POST Image restore options:" 25 50 50 \
         1 FixGrub off \
         2 FixSwap\(/dev/xdX2\) off \
         2> $menutmpfile; nav_value2=$? ; _return2=$(cat $menutmpfile)
        if [ $nav_value2 -eq 1 ]; then ./$(basename $0) && exit; fi
        if [[ $_return2 = *"1"* ]];then fixgrub=1; fi
        if [[ $_return2 = *"2"* ]];then fixswap=1; fi

        newvmname="${_return}"
        newvmname=${newvmname//[^a-zA-Z0-9]/}
        obdiskcreate $newvmname $file_size_bytes ${restorepath}
        obucreaterestoredvm $newvmname "${xmldata}" $restoreorclone
        obuattachadisk $newvmuuid $newdiskuuid
        file_size_bytes=$((file_size_kb * 1024))
        obuupdatevmsetting $newvmuuid memory $file_size_bytes
        obualert "\n\nThe VM should now be restored, You will have to verify the memory, NICs, CPUs etc to make sure all is as expected.\n\nAlso make sure that you install xe-guest-utilities and check your swap file and disk alignment to make sure all looks as it should. You can check zipur.ca for tips on how to correct issues.${donetext}"
        bp=$(dirname $restorepath);./$(basename $0) && exit;
    fi

fi
