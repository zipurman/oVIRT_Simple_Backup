#!/bin/bash

#####################################
#
# oVIRT_Simple_Backup
# Version: 0.1.0
# Date: 01/21/2018
#
# Simple Script to backup VMs running on oVirt to Export Storage
#
# Author: zipur
# IRC: irc.oftc.net #ovirt
#
# Required Packages: scsitools, curl, xmlstarlet, lsscsi, pv, dialog
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
# *** NOTE: You cannot take snapshots of disks that are marked as shareable or that are based on direct LUN disks. 
# TODO: check to see if VM busy before attempting to snapshot it and wait
# TODO: add support for sdz, sdaa, sdab, sdaz, sdaaa, sdaaz
# TODO: restore manager using dialog
# TODO: Write to log and email log before shutdown, also email at start of script
#####################################

#backup.cfg is required for this script and will hold all of your custom settings
source backup.cfg

obuversion="0.1.0"
obutitle="oVirt - Simple Backup - Version ${obuversion}"
obutext=""
url="${url}/ovirt-engine/api"

##FUNCTIONS START############################################

chr () {
  [ ${1} -lt 256 ] || return 1
  printf \\$(($1/64*100+$1%64/8*10+$1%8))
}
ord() {
  LC_CTYPE=C printf '%d' "'$1"
}
obudialog() {
    dialog --title "${1}" --infobox "${2}" 20 80
}
obuapicall() {
    defargs=('-X' "$1" '-s' '-k' '-u'  "${user}:${password}" '-H' 'Accept: application/xml'  '-H' 'Content-Type: application/xml')
    if [ -n "$3" ] && [ "$3" != "" ]; then defargs+=('-d' "${3}"); fi
    if [ -n "$4" ]; then defargs+=("-H" "All-Content: true"); fi
    obuapicallresult=`curl "${defargs[@]}" "${url}/${2}"`
}

##FUNCTIONS END##############################################

#this is to start at disk b, where a is the OS of the backup appliance
disknumber=98 #98(ASCII)=b

obutext="\n\nStarting Backup Process ...\n\n"
obudialog "${obutitle}" "${obutext}"

### CURL - GET - VM LIST
obuapicall "GET" "vms"
vmslist="${obuapicallresult}"

#Count total VMs in oVirt
countedvms=`echo $vmslist | xmlstarlet sel -t -v "count(/vms/vm)"`

obutext="${obutext}There are currently $countedvms VMs in your environment\n\n"
obudialog "${obutitle}" "${obutext}"

#Get List of all VMs
vmlist=`echo $vmslist | xmlstarlet sel -T -t -m /vms/vm -s D:N:- "@id" -v "concat(@id,'|',name,';')"`

#Get number of backups targeted by script
numofbackups=`echo $vmlisttobackup | sed 's/\[/\n&\n/g' | grep -cx '\['`

obutext="${obutext}You are targeting a total of $numofbackups VMs for backup\n\n"
obudialog "${obutitle}" "${obutext}"

#stop if first device already exists
if [ -f "/sys/block/${second_disk_device}${diskletter}" ]
then
    obutext="Disk devices already exist.\n\n"
    obutext="${obutext}Shutdown the Backup Appliance VM and then Start it again.\n\n"
    obutext="${obutext}Once re-started, try backup script again.\n\n"
    obudialog "${obutitle}" "${obutext}"
    exit 0
fi

#loop VM list
for i in ${vmlist//\;/ }
do
    vmdataarray=(${i//|/ })
    vmname="${vmdataarray[1]}"
    vmuuid="${vmdataarray[0]}"

    if [ $vmname = "HostedEngine" ]
    then

        obutext="${obutext}VM: HostedEnginge (SKIPPING)\n\n"
        obudialog "${obutitle}" "${obutext}"

    else

        obutext="${obutext}VM: $vmname ($vmuuid)\n\n"
        obudialog "${obutitle}" "${obutext}"

        if [[ $vmlisttobackup == *"[$vmname]"* ]]; then

            DATEIS=`date "+%Y%m%d_%H%M%S"`
            obutext="${obutext}Backing Up ${vmbackupname}${DATEIS}\n\n"
            obudialog "${obutitle}" "${obutext}"

            ### CREATE SNAPSHOT
            obuapicall "POST" "vms/${vmuuid}/snapshots" "<snapshot><description>${vmbackupname}${DATEIS}</description></snapshot>"
            snapshot="${obuapicallresult}"

            #wait for snapshot to be queued
            sleep 3

            snapshotdone="0"
            while [ $snapshotdone -eq 0 ]
            do
                ### CHECK SNAPSHOT STATUS AND WAIT FOR COMPLETION
                obuapicall "GET" "vms/${vmuuid}/snapshots"
                snapshotstatus="${obuapicallresult}"
                snapshotlistlock=`echo $snapshotstatus | xmlstarlet sel -T -t -m /snapshots/snapshot -s D:N:- "@id" -v "concat(@id,'|',description,'|',snapshot_status,';')"`

                snapshotcomplete="0"
                for ss in ${snapshotlistlock//\;/ }
                do
                    snapshotarraylock=(${ss//|/ })
                    ssnamelock="${snapshotarraylock[1]}"
                    ssuuidlock="${snapshotarraylock[0]}"
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

                    obutext="${obutext}#"
                    obudialog "${obutitle}" "${obutext}"

                    sleep 2
                else
                    snapshotdone="1"

                    obutext="${obutext}\nDONE SNAPSHOT\n\nLoading Data\n\n"
                    obudialog "${obutitle}" "${obutext}"

                    ### LIST SNAPSHOTS FROM VM
                    obuapicall "GET" "vms/${vmuuid}/snapshots"
                    snapshots="${obuapicallresult}"
                    snapshotlist=`echo $snapshots | xmlstarlet sel -T -t -m /snapshots/snapshot -s D:N:- "@id" -v "concat(@id,'|',description,';')"`


                    for s in ${snapshotlist//\;/ }
                    do
                        snapshotarray=(${s//|/ })
                        ssname="${snapshotarray[1]}"
                        ssuuid="${snapshotarray[0]}"

                        if [[ $ssname == "${vmbackupname}${DATEIS}" ]]
                        then

                            obutext="${obutext}SNAPSHOT NAME: $ssname\n\n"
                            obutext="${obutext}SNAPSHOT UUID: $ssuuid\n\n"
                            obudialog "${obutitle}" "${obutext}"

                            #Make backup directory
                            mkdir -p "${backup_nfs_mount_path}/${vmname}"
                            mkdir -p "${backup_nfs_mount_path}/${vmname}/${vmuuid}"
                            mkdir -p "${backup_nfs_mount_path}/${vmname}/${vmuuid}/${ssname}"

                            ### DATA FROM SNAPSHOT FOR BACKUP
                            obuapicall "GET" "vms/${vmuuid}/snapshots/${ssuuid}" "" 1
                            snapshotdatagrab="${obuapicallresult}"
                            snapshotdatagrabfile=`echo $snapshotdatagrab | xmlstarlet sel -T -t -m /snapshot/initialization/configuration -s D:N:- "@id" -v "data"`

                            obutext="${obutext}SAVING XML DATA FILE\n\n"
                            obudialog "${obutitle}" "${obutext}"

                            echo  "${snapshotdatagrabfile}" > "${backup_nfs_mount_path}/${vmname}/${vmuuid}/${ssname}/data.xml"

                            ### SNAPSHOT DISK DATA
                            obuapicall "GET" "vms/${vmuuid}/snapshots/${ssuuid}/disks"
                            diskxmldata="${obuapicallresult}"
                            diskuuidlist=`echo $diskxmldata | xmlstarlet sel -T -t -m /disks/disk -s D:N:- "@id" -v "concat(@id,'|',snapshot/@id,'|',image_id,';')"`
                            for s in ${diskuuidlist//\;/ }
                            do
                                diskarray=(${s//|/ })
                                diskid="${diskarray[0]}"
                                diskuuid="${diskarray[1]}"
                                diskimageid="${diskarray[2]}"
                                diskletter=$(chr $disknumber)

                                #remove device from VM to make room
                                if [ -f "/sys/block/${second_disk_device}${diskletter}/device/delete" ]
                                then
                                    echo 1 > /sys/block/${second_disk_device}${diskletter}/device/delete
                                fi

                                #allow ovirt time to finish before attach
                                sleep 8
                                #rescan-scsi-bus
                                obutext="${obutext}Disk UUID: $diskid\n\n"
                                obutext="${obutext}Attaching Disk: ${diskid} Snapshot-UUID: ${diskuuid} to OS\n\n"
                                obudialog "${obutitle}" "${obutext}"

                                ### ATTACH DISK TO BACKUP VM
                                obuapicall "POST" "vms/${thibackupvmuuid}/diskattachments/" "<disk_attachment> \
                                <disk id=\"${diskid}\"> \
                                <snapshot id=\"${diskuuid}\"/> \
                                </disk> \
                                <active>true</active> \
                                <bootable>false</bootable> \
                                <interface>${diskinterface}</interface> \
                                <logical_name>/dev/${second_disk_device}${diskletter}</logical_name> \
                                </disk_attachment>"
                                attachdisk="${obuapicallresult}"

                            done

                            obutext="${obutext}Waiting for disk...\n\n"
                            obudialog "${obutitle}" "${obutext}"

                            sleep 4

                            sizeofpart=`awk '/'${second_disk_device}${diskletter}'$/{printf "%s %8.2f GiB\n", $NF, $(NF-1) / 1024 / 1024}' /proc/partitions`
                            sizeofpart=${sizeofpart//${second_disk_device}${diskletter}/}

                            clear

                            (pv -n /dev/${second_disk_device}${diskletter} | dd of="${backup_nfs_mount_path}/${vmname}/${vmuuid}/${ssname}/image.img" bs=1M conv=notrunc,noerror) 2>&1 | dialog --gauge "Imaging VM: ${vmname}\n\nImage Size: ${sizeofpart} \n\nDisk From /dev/${second_disk_device}${diskletter}, please wait..." 10 70 0

                            clear

                            oktodelete=1

                            if [ $oktodelete -eq 1 ]
                            then

                                obutext="${obutext}Cleaning up ....\n\n"
                                obudialog "${obutitle}" "${obutext}"

                                ### DETACH DISK FROM BACKUP VM
                                obuapicall "DELETE" "vms/${thibackupvmuuid}/diskattachments/${diskid}"
                                detatchdisk="${obuapicallresult}"
                                obutext="${obutext}Detaching Disk ....\n\n"
                                obudialog "${obutitle}" "${obutext}"

                                #allow detatch disk to complete before deleting snapshot
                                sleep 10

                                obuapicall "DELETE" "vms/${vmuuid}/snapshots/${ssuuid}"
                                deletesnapshot="${obuapicallresult}"
                                obutext="${obutext}Deleting Snapshot ${ssname} (${ssuuid})\n\n"
                                obudialog "${obutitle}" "${obutext}"

                            fi

                            #next disk id
                            disknumber="$(($disknumber+1))"
                            sleep 3
                        fi
                    done

                fi
            done

        else
            obutext="${obutext}Skipping - (Not in list)\n\n"
            obudialog "${obutitle}" "${obutext}"
        fi
    fi

    obutext="${obutext}--- ALL DONE ---\n\n"
    obudialog "${obutitle}" "${obutext}"

done

obutext="*** Rebooting Backup Appliance in 10 seconds *** (ctrl-c to cancel)\n\n"
obudialog "${obutitle}" "${obutext}"

for number in {10..1}
do
    obutext="${obutext} $number "
    obutext="*** Rebooting Backup Appliance in 10 seconds *** (ctrl-c to cancel)\n\n"
    obudialog "${obutitle}" "${obutext}"
    sleep 1
done

#reboot must come from API call or drives are not released

obuapicall "POST" "vms/${thibackupvmuuid}/shutdown/" "<action/>"

exit 0
