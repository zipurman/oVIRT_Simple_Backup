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
if [ "${_return}" = "4" ] && [ "${menuposition}" = "frombase" ]
then

    #check to make sure disk is available
    if [ -e "/dev/${second_disk_device}${extradiskdev}${diskletter}" ]
    then
        obualert "\n\nYou must restart your VM before doing a restore. Disks are locked from previous backup or restore."
        ./$(basename $0) && exit;
    fi

    restorepath="${backup_nfs_mount_path}"
    menuposition="restoresinglevm"
    optionstext=""
    optionid="1"
    if [ "$(ls -A $backup_nfs_mount_path)" ]; then
        for filenamex in "${backup_nfs_mount_path}/*";
            do
                    filenamey=${filenamex//$backup_nfs_mount_path\//}
                    vmname="${filenamey}_"
                    optionstext="${optionstext} ${vmname} VM off"
                    optionid=$((optionid + 1))
        done
    else
        dialog --colors --backtitle "${obutitle}" --title " ALERT! " --cr-wrap --msgbox "\n\nThere are no VMs backups available to restore in ${backup_nfs_mount_path}."  10 40
        ./$(basename $0) && exit;
    fi


    dialog --colors --column-separator "|" --backtitle "${obutitle}" --title "VM Backups List" --ok-label "LIST BACKUPS" --cancel-label "Main Menu"  --cr-wrap --radiolist "Choose a VM to restore from ${backup_nfs_mount_path}:" 25 50 50 $optionstext 2> $menutmpfile; nav_value=$? ; _return=$(cat $menutmpfile)
    #catch nothing chosen
    if [ "${_return}" = "" ] && [ $nav_value -eq 0 ]; then ./$(basename $0) nav 4 frombase && exit; fi

fi

############################ CHOOSE A UUID #######################################

if [ "${nav_value}" = "1" ] && [ "${menuposition}" = "restoresinglevm" ];then ./$(basename $0) && exit; fi

if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "restoresinglevm" ]
then
    obucheckoktostart
    obuloadsettings
    menuposition="restoresinglevm2"
    restorepath="${restorepath}/${_return}"
    if [ "${4}" != "" ] && [ "$3" = "restoresinglevm" ]; then restorepath="${4}"; fi
    optionstext=""
    for filename in ${restorepath}/*;
    do
        filename=${filename//$restorepath\//}
        vmname="${filename}"
        optionstext="${optionstext} ${vmname} VM off"
    done

    dialog --colors --column-separator "|" --backtitle "${obutitle}" --title "Backups UUID List for ${vmname}" --ok-label "SELECT" --extra-label "Previous" --extra-button  --cancel-label "Main Menu" --cr-wrap --radiolist "Choose a VM to restore:" 25 50 50 $optionstext 2> $menutmpfile; nav_value=$? ; _return=$(cat $menutmpfile)
    #catch nothing chosen
    if [ "${_return}" = "" ] && [ $nav_value -eq 0 ]; then ./$(basename $0) nav 4 restoresinglevm $restorepath && exit; fi
fi

############################ CHOOSE A RESTORE DATE #######################################

if [ "${nav_value}" = "1" ] && [ "${menuposition}" = "restoresinglevm2" ];then ./$(basename $0) && exit; fi
if [ "${nav_value}" = "3" ] && [ "${menuposition}" = "restoresinglevm2" ];then ./$(basename $0) nav 4 frombase && exit; fi
if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "restoresinglevm2" ]
then
    obucheckoktostart
    obuloadsettings
    menuposition="restoresinglevm3"
    restorepath="${restorepath}/${_return}"
    if [ "${4}" != "" ] && [ "$3" = "restoresinglevm2" ]; then restorepath="${4}"; fi
    optionstext=""
    for filename in ${restorepath}/*;
    do
        filename=${filename//$restorepath\//}
        vmuuid="${filename}"
        optionstext="${optionstext} ${vmuuid} VM off"
    done
    dialog --colors --column-separator "|" --backtitle "${obutitle}" --title "Backups Dates List for ${vmname}" --ok-label "SELECT" --extra-label "Previous" --extra-button --cancel-label "Main Menu" --cr-wrap --radiolist "Choose a VM to restore:" 25 50 50 $optionstext 2> $menutmpfile; nav_value=$? ; _return=$(cat $menutmpfile)
    #catch nothing chosen
    if [ "${_return}" = "" ] && [ $nav_value -eq 0 ]; then ./$(basename $0) nav 4 restoresinglevm2 $restorepath && exit; fi
fi

############################ CONFIRM VM #######################################


if [ "${nav_value}" = "1" ] && [ "${menuposition}" = "restoresinglevm3" ];then ./$(basename $0) && exit; fi
if [ "${nav_value}" = "3" ] && [ "${menuposition}" = "restoresinglevm3" ];then bp=$(dirname $restorepath);./$(basename $0) nav 4 restoresinglevm $bp && exit; fi
if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "restoresinglevm3" ]
then
    obucheckoktostart
    obuloadsettings
    menuposition="restoresinglevm4"
    restorepath="${restorepath}/${_return}"
    restoreokay=1
    errortext=""
    if [ ! -f "${restorepath}/data.xml" ]
    then
        #allow no xml and create a new xml here
#        restoreokay=0;
#        errortext="${errortext}\nMissing: ${restorepath}/data.xml"
        xmldata=`cat src/nodata.xml`
    else
        xmldata=`cat ${restorepath}/data.xml`
    fi
    if [ ! -f "${restorepath}/image.img" ]; then restoreokay=0; errortext="${errortext}\nMissing: ${restorepath}/image.img"; fi
    if [ $restoreokay -eq 0 ]
    then
        dialog --colors --backtitle "${obutitle}" --title " CONFIRM " --cr-wrap --msgbox "\n\nThis backup set is broken. Cannot Restore Data.\n\n${errortext}"  20 40
        ./$(basename $0) && exit;
    else

        xml=`echo $xmldata | xmlstarlet sel -T -t -m /ovf:Envelope/Content -s D:N:- "@ovf:id" -v "concat(Name,'|',ExportDate,'|',CreationDate,'|',ClusterCompatibilityVersion,'|',MinAllocatedMem,'|',PredefinedProperties,'|',MaxMemorySizeMb,';')"`
        SAVEIFS=$IFS
        IFS="|"
        xmlarr=($xml)
        IFS=$SAVEIFS

        originaluuid=`echo $xmldata | xmlstarlet sel -T -t -m /ovf:Envelope/Content/Section -s D:N:- "@ovf:id" -v "@ovf:id"`

        file_size_kb=`du -k "${restorepath}/image.img" | cut -f1`
        file_size_gb=$((file_size_kb / 1024 / 1024))

        showdatatext="Please confirm the following matches what you are wanting to restore:\n\n"
        showdatatext="${showdatatext}Name: \Z4${xmlarr[0]}\Zn\n"
        showdatatext="${showdatatext}ExportDate: \Z4${xmlarr[1]}\Zn\n"
        showdatatext="${showdatatext}CreationDate: \Z4${xmlarr[2]}\Zn\n"
        showdatatext="${showdatatext}ClusterCompatibilityVersion: \Z4${xmlarr[3]}\Zn\n"
        showdatatext="${showdatatext}MinAllocatedMem: \Z4${xmlarr[4]}\Zn\n"
        showdatatext="${showdatatext}MaxMemorySizeMb: \Z4${xmlarr[6]}\Zn\n"
        showdatatext="${showdatatext}PredefinedProperties: \Z4${xmlarr[5]}\Zn\n"
        showdatatext="${showdatatext}VM UUID: \Z4${originaluuid}\Zn\n"
        showdatatext="${showdatatext}Disk Size: \Z4${file_size_gb} GB\Zn\n"
        showdatatext="${showdatatext}\n\nYou will be prompted for a new VM Name to restore this image to after you confirm."

        obuapicall "GET" "vms/${originaluuid}"
        checkvm="${obuapicallresult}"
        newvmuuid=`echo $checkvm | xmlstarlet sel -T -t -m /vm -s D:N:- "@id" -v "@id"`
        #TODO - Full Restore still not working so force clone with 1=1 for now
        if [ "${newvmuuid}" = "${originaluuid}" ] || [ 1 -eq 1 ]
        then
            showdatatext="${showdatatext}\n\n\Z1 !! VM STILL IN CLUSTER - THIS WILL CREATE A CLONE !!\n\n"
            restoreorclone=2
        else
            showdatatext="${showdatatext}\n\n\Z1 !! THIS WILL CREATE A FULL RESTORE OR ORIGINAL !!\n\n"
            restoreorclone=1
        fi


        dialog --cr-wrap --colors --backtitle "${obutitle}" --title "${obutitle}" --yesno "${showdatatext}" 26 70
        response=$?
        case $response in
           0) menuposition="restoresinglevm4";;#YES
           1) bp=$(dirname $restorepath);./$(basename $0) nav 4 restoresinglevm2 $bp && exit;;#NO
           255) ./$(basename $0) && exit;;#CANCEL
        esac
    fi
fi

############################ CHOOSE A VM NEW NAME #######################################

if [ "${response}" = "0" ] && [ "${menuposition}" = "restoresinglevm4" ]
then

    dialog --colors --backtitle "${obutitle}" --title "Choose New VM Name" --ok-label "RESTORE NOW" --cancel-label "CANCEL" --cr-wrap --inputbox "Enter a new VM Name for Restored VM.\n\nNote: Must not contain any spaces or punctuation!\n\n"  25 60 ""  2> $menutmpfile; nav_value=$?;_return=$(cat $menutmpfile);

    #MAIN MENU
    if [ $nav_value -eq 1 ]; then bp=$(dirname $restorepath);./$(basename $0) nav 4 restoresinglevm2 $bp && exit; fi

    #EMPTY VM NAME
    if [ "${_return}" = "" ]; then
        obualert "\n\nYou must supply a new name for the restored VM"
        bp=$(dirname $restorepath);./$(basename $0) nav 4 restoresinglevm2 $bp && exit;
    fi
    #ZERO SIZED IMAGE
    if [ $file_size_gb -eq 0 ]; then
        obualert "\n\nThe backup you chose has a zero size. Cannot restore."
        bp=$(dirname $restorepath);./$(basename $0) nav 4 restoresinglevm2 $bp && exit;
    fi

    #NEXT
    if [ $nav_value -eq 0 ]; then
        donetext=""
        fixgrub=0
        fixswap=0
        file_size_bytes=$(($file_size_gb * 1024 * 1024 * 1024))
        #remove any non-alpha/num from name
        newvmname=${_return//[^a-zA-Z0-9]/}
        obdiskcreate $newvmname $file_size_bytes ${restorepath}/image.img
        obucreaterestoredvm $newvmname "${xmldata}" $restoreorclone
        obuattachadisk $newvmuuid $newdiskuuid
        file_size_bytes=$((file_size_kb * 1024))
        obuupdatevmsetting $newvmuuid memory $file_size_bytes
        obualert "\n\nThe VM should now be restored, You will have to verify the memory, NICs, CPUs etc to make sure all is as expected.${donetext}"
        bp=$(dirname $restorepath);./$(basename $0) && exit;
    fi

fi
