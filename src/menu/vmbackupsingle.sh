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

###################################################################
#select VMs for backup
if [ "${_return}" = "3" ] && [ "${menuposition}" = "frombase" ]
then
    menuposition="backupsinglevm"
    obuapicall "GET" "vms"
    vmslist="${obuapicallresult}"
    vmlist=`echo $vmslist | xmlstarlet sel -T -t -m /vms/vm -s D:N:- "@id" -v "concat(@id,'|',name,'|',status,';')"`
    optionstext=""
    optionid="1"
    checkuuid=$( obusettings_get 4 )
    for i in ${vmlist//\;/ }
        do
            vmdataarray=(${i//|/ })
            vmname="${vmdataarray[1]}"
            vmstatus="${vmdataarray[2]}"
            vmuuid="${vmdataarray[0]}"
            if [ "${vmname}" != "HostedEngine" ] && [ "${checkuuid}" != "${vmuuid}" ];then
                if [[ $vmstatus == "up" ]]; then
                    vmstatustxt="\Zr\Z2up\ZR\Z0|\Zn"
                else
                    vmstatustxt="\Zr\Z1${vmstatus}\ZR\Z0|\Zn"
                fi
                showvmname="${vmstatustxt}${vmname}"
                optionstext="${optionstext} ${optionid} ${showvmname} off"
                optionid=$((optionid + 1))
            fi
    done
    dialog --colors --column-separator "|" --backtitle "${obutitle}" --title "VMs List" --ok-label "BACKUP" --cancel-label "Main Menu" --extra-label "Refresh" --extra-button --cr-wrap --radiolist "Choose a VM to backup:" 25 50 50 $optionstext 2> $menutmpfile; nav_value=$? ; _return=$(cat $menutmpfile)

fi
###################################################################


if [ "${nav_value}" = "3" ] && [ "${menuposition}" = "backupsinglevm" ];then ./$(basename $0) nav 1 frombase && exit; fi
if [ "${nav_value}" = "1" ] && [ "${menuposition}" = "backupsinglevm" ];then ./$(basename $0) && exit; fi

#save selected VMs to a file
if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "backupsinglevm" ]
then

    obucheckoktostart
    obuloadsettings
    idnum="1"
    vmlistsave=""
    for i in ${vmlist//\;/ }
    do
        vmdataarray=(${i//|/ })
        vmname="${vmdataarray[1]}"
        vmuuid="${vmdataarray[0]}"
        vmstatus="${vmdataarray[2]}"

        if [ $vmname != "HostedEngine" ] && [ "${checkuuid}" != "${vmuuid}" ];then
                for z in $(echo $_return)
                do

                    if [ $z -eq  $idnum ]
                    then
                        #backup VM selected
                        obubackup $vmname $vmuuid 1
                    fi
                done
                idnum=$((idnum + 1))
        fi
    done

    ./$(basename $0) && exit


fi