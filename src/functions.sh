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

obusettings_write(){
    sed -i "${2}s/.*/${1}/" $menusettings
}
obusettings_get(){
    sed $1'!d' $menusettings
}
chr () {
  [ ${1} -lt 256 ] || return 1
  printf \\$(($1/64*100+$1%64/8*10+$1%8))
}
ord() {
  LC_CTYPE=C printf '%d' "'$1"
}
obudialog() {
    clear
    dialog --colors --backtitle "${1}" --title " ${3} " --cr-wrap --infobox "${2}"  20 80
}
obualert() {
    clear
    dialog --colors --backtitle "${obutitle}" --title " ALERT! " --cr-wrap --msgbox "${1}"  20 80
}
obuapicall() {
    defargs=('-X' "$1" '-s' '-k' '-u'  "${user}:${password}" '-H' 'Accept: application/xml'  '-H' 'Content-Type: application/xml')
    if [ -n "$3" ] && [ "$3" != "" ]; then defargs+=('-d' "${3}"); fi
    if [ -n "$4" ]; then defargs+=("-H" "All-Content: true"); fi
    obuapicallresult=`curl "${defargs[@]}" "${url}/${2}"`
}
obulog() {
    if [ -n "$backuplog" ] && [ "$backuplog" != "" ]
    then
        #init the log
        if [ -n "$2" ]; then echo "" > $backuplog; fi
        backupdata="${1}"
        backupdata=${backupdata//\\Z[a-zA-Z0-9]/}
        echo -e $backupdata >> $backuplog;
    fi
}
obusettings(){
    if [ -n "$3" ] && [ "$3" != "" ]; then backpage=$3; else backpage="settings"$(($2 - 2)); fi
    if [ -n "$4" ] && [ "$4" != "" ]; then backpagereturn=$4; else backpagereturn="0"$(($2 - 2)); fi
    clear
    dialog --colors --backtitle "${obutitle}" --title "Settings" --ok-label "Next" --cancel-label "Main Menu" --extra-label "Previous" --extra-button --cr-wrap --inputbox "${1}"  25 60 $( obusettings_get $2)  2> $menutmpfile; nav_value=$?;_return=$(cat $menutmpfile);
    _return=${_return//\//\\/}
    if [ "${nav_value}" = "0" ]; then obusettings_write "${_return}" $2; fi; menuposition="settings${2}";
    if [ "${nav_value}" = "3" ]; then ./$(basename $0) nav $backpagereturn $backpage && exit; fi;
    if [ "${nav_value}" = "1" ]; then ./$(basename $0) && exit; fi;
}
obubackup(){
    # $1 = vmname
    # $2 = vmuuid
    DATEIS=`date "+%Y%m%d_%H%M%S"`
    obutext="${obutext}Backing Up \Zb\Z4${vmbackupname}${DATEIS}\ZB\Zn\n"
    obudialog "${obutitle}" "${obutext}" "${1}"
    obulog "${obutext}"
    obusnapshot $1 $2
}
obusnapshot(){
    # $1 = vmname
    # $2 = vmuuid
    obuapicall "POST" "vms/${2}/snapshots" "<snapshot><description>${vmbackupname}${DATEIS}</description></snapshot>"
    snapshot="${obuapicallresult}"
    #wait for snapshot to be queued
    sleep 3
    snapshotdone="0"
    snapshotpercent="0"
    while [ $snapshotdone -eq 0 ]
    do
        ### CHECK SNAPSHOT STATUS AND WAIT FOR COMPLETION
        obuapicall "GET" "vms/${2}/snapshots"
        snapshotstatus="${obuapicallresult}"
        snapshotlistlock=`echo $snapshotstatus | xmlstarlet sel -T -t -m /snapshots/snapshot -s D:N:- "@id" -v "concat(@id,'|',description,'|',snapshot_status,';')"`
        snapshotcomplete="0"
        for ss in ${snapshotlistlock//\;/ }
        do
            snapshotarraylock=(${ss//|/ })
            ssuuidlock="${snapshotarraylock[0]}"
            ssnamelock="${snapshotarraylock[1]}"
            ssstatuslock="${snapshotarraylock[2]}"
            if [[ $ssnamelock == "${vmbackupname}${DATEIS}" ]]
            then
                if [[ $ssstatuslock == "locked" ]]
                then
                    snapshotcomplete="0"
                else
                    snapshotcomplete="1"
                fi
            fi
        done
        #IF SNAPSHOT NOT READY ITERATE
        if [ $snapshotcomplete -eq 0 ]; then
            clear
            echo $snapshotpercent | dialog --colors --backtitle "${obutitle}" --title "${1}" --gauge "${obutext} Creating Stapshot " 20 80 0
            snapshotpercent=$((snapshotpercent + 1))
            if [ $snapshotpercent -gt 100 ]
            then
                snapshotpercent=0
            fi
            sleep .03
        else
            snapshotdone="1"
            obutext="${obutext}\nDONE SNAPSHOT\nLoading Data\n"
            obudialog "${obutitle}" "${obutext}" "${1}"
            obulog "${obutext}"
            ### LIST SNAPSHOTS FROM VM
            obuapicall "GET" "vms/${2}/snapshots"
            snapshots="${obuapicallresult}"
            snapshotlist=`echo $snapshots | xmlstarlet sel -T -t -m /snapshots/snapshot -s D:N:- "@id" -v "concat(@id,'|',description,';')"`
            for s in ${snapshotlist//\;/ }
            do
                snapshotarray=(${s//|/ })
                ssuuid="${snapshotarray[0]}"
                ssname="${snapshotarray[1]}"
                if [[ $ssname == "${vmbackupname}${DATEIS}" ]]
                then
                    obutext="${obutext}SNAPSHOT NAME: $ssname\n"
                    obutext="${obutext}SNAPSHOT UUID: $ssuuid\n"
                    obudialog "${obutitle}" "${obutext}" "${1}"
                    obulog "${obutext}"
                    #Make backup directory
                    mkdir -p "${backup_nfs_mount_path}/${1}"
                    mkdir -p "${backup_nfs_mount_path}/${1}/${2}"
                    mkdir -p "${backup_nfs_mount_path}/${1}/${2}/${ssname}"
                    ### DATA FROM SNAPSHOT FOR BACKUP
                    obuapicall "GET" "vms/${2}/snapshots/${ssuuid}" "" 1
                    snapshotdatagrab="${obuapicallresult}"
                    snapshotdatagrabfile=`echo $snapshotdatagrab | xmlstarlet sel -T -t -m /snapshot/initialization/configuration -s D:N:- "@id" -v "data"`
                    obutext="${obutext}SAVING XML DATA FILE\n"
                    obudialog "${obutitle}" "${obutext}" "${1}"
                    obulog "${obutext}"
                    echo  "${snapshotdatagrabfile}" > "${backup_nfs_mount_path}/${1}/${2}/${ssname}/data.xml"
                    obusnapshotattach $1 $2 $ssuuid $ssname
                fi
            done

        fi
    done
}
obusnapshotattach(){
    # $1 = vmname
    # $2 = vmuuid
    # $3 = ssuuid
    # $4 = ssname
   ### SNAPSHOT DISK DATA
    obuapicall "GET" "vms/${2}/snapshots/${3}/disks"
    diskxmldata="${obuapicallresult}"
    diskuuidlist=`echo $diskxmldata | xmlstarlet sel -T -t -m /disks/disk -s D:N:- "@id" -v "concat(@id,'|',snapshot/@id,'|',image_id,';')"`
    for s in ${diskuuidlist//\;/ }
    do
        diskarray=(${s//|/ })
        diskid="${diskarray[0]}"
        diskuuid="${diskarray[1]}"
        diskimageid="${diskarray[2]}"
        #allow ovirt time to finish before attach
        sleep 8
        obutext="${obutext}Attaching Disk: ${diskid}\n"
        obudialog "${obutitle}" "${obutext}" "${1}"
        obulog "${obutext}"
        ### ATTACH DISK TO BACKUP VM
        obuapicall "POST" "vms/${thisbackupvmuuid}/diskattachments/" "<disk_attachment> \
        <disk id=\"${diskid}\"> \
        <snapshot id=\"${diskuuid}\"/> \
        </disk> \
        <active>true</active> \
        <bootable>false</bootable> \
        <interface>${diskinterface}</interface> \
        <logical_name>/dev/${second_disk_device}${extradiskdev}${diskletter}</logical_name> \
        </disk_attachment>"
        attachdisk="${obuapicallresult}"
        obutext="${obutext}Waiting for disk...\n"
        obudialog "${obutitle}" "${obutext}" "${1}"
        obulog "${obutext}"
        sleep 4
        obuimagecreate $1 $2 $4
        obudiskdetach $1 $2 $diskid
        #allow detatch disk to complete before deleting snapshot
        sleep 5
        obusnapshotdelete $1 $2 $3 $4
        sleep 3
        if [ $incrementdiskdevices -eq 1 ];then
            if [ "${diskletter}" = "z" ];then disknumberx=-1; extradiskdev="${extradiskdev}a";fi
            disknumberx="$(($disknumberx + 1))"; diskletter=$(chr $(($disknumberx + $disknumber)));
        fi
    done
}
obuimagecreate(){
    # $1 = vmname
    # $2 = vmuuid
    # $3 = ssname
    sizeofpart=`awk '/'${second_disk_device}${extradiskdev}${diskletter}'$/{printf "%s %8.2f GiB\n", $NF, $(NF-1) / 1024 / 1024}' /proc/partitions`
    sizeofpart=${sizeofpart//${second_disk_device}${extradiskdev}${diskletter}/}
    (pv -n /dev/${second_disk_device}${extradiskdev}${diskletter} | dd of="${backup_nfs_mount_path}/${1}/${2}/${3}/image.img" bs=1M conv=notrunc,noerror) 2>&1 | dialog --colors --backtitle "${obutitle}" --title "${1}" --gauge "${obutext}Size: ${sizeofpart}  Device: /dev/${second_disk_device}${extradiskdev}${diskletter}" 20 80 0
}
obudiskdetach(){
    # $1 = vmname
    # $2 = vmuuid
    # $3 = diskid
    obutext="${obutext}\nCleaning up \Zb\Z4${1}\ZB\Zn Snapshot and Disk ....\n"
    obudialog "${obutitle}" "${obutext}" "${1}"
    obulog "${obutext}"
    ### DETACH DISK FROM BACKUP VM
    obuapicall "DELETE" "vms/${thisbackupvmuuid}/diskattachments/${3}"
    detatchdisk="${obuapicallresult}"
    obutext="${obutext}Detaching Disk ....\n"
    obudialog "${obutitle}" "${obutext}" "${1}"
    obulog "${obutext}"
}
obusnapshotdelete(){
    # $1 = vmname
    # $2 = vmuuid
    # $3 = ssuuid
    # $4 = ssname
    obuapicall "DELETE" "vms/${2}/snapshots/${3}"
    deletesnapshot="${obuapicallresult}"
    obutext="${obutext}Deleting Snapshot ${4} (${3})\n\n"
    obudialog "${obutitle}" "${obutext}" "${1}"
    obulog "${obutext}"
}