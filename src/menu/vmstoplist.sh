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
if [ "${_return}" = "1" ] && [ "${menuposition}" = "frombase" ]
then
    menuposition="stopavm"
    obuapicall "GET" "vms"
    vmslist="${obuapicallresult}"
    vmlist=`echo $vmslist | xmlstarlet sel -T -t -m /vms/vm -s D:N:- "@id" -v "concat(@id,'|',name,'|',status,';')"`
    optionstext=""
    optionid="1"
    hasavm=0
    for i in ${vmlist//\;/ }
        do
            vmdataarray=(${i//|/ })
            vmname="${vmdataarray[1]}"
            vmstatus="${vmdataarray[2]}"
            vmuuid="${vmdataarray[0]}"
            if [ "${vmname}" != "HostedEngine" ];then

                if [[ $vmstatus == "up" ]]; then
                    showvmname="${vmname}"
                    checkuuid=$( obusettings_get 4 )
                    if [ "${checkuuid}" = "${vmuuid}" ];then showvmname="${showvmname}-\Z1!!THIS_VM!!\Zn"; fi
                    optionstext="${optionstext} ${optionid} ${showvmname} off"
                    optionid=$((optionid + 1))
                    hasavm=1
                fi

            fi
    done

    if [ $hasavm -eq 0 ]; then
        dialog --colors --backtitle "${obutitle}" --title " ALERT! " --cr-wrap --msgbox '\n\nThere are no running VMs available to shutdown.'  10 40
        ./$(basename $0) && exit;
    fi

    dialog --colors --backtitle "${obutitle}" --title "Running VMs List" --ok-label "SHUTDOWN" --cancel-label "Main Menu" --extra-label "Refresh" --extra-button --cr-wrap --radiolist "Choose a VM to shutdown:" 25 50 50 $optionstext 2> $menutmpfile; nav_value=$? ; _return=$(cat $menutmpfile)

fi
###################################################################


if [ "${nav_value}" = "3" ] && [ "${menuposition}" = "stopavm" ];then ./$(basename $0) nav 1 frombase && exit; fi
if [ "${nav_value}" = "1" ] && [ "${menuposition}" = "stopavm" ];then ./$(basename $0) && exit; fi

#save selected VMs to a file
if [ "${nav_value}" = "0" ] && [ "${menuposition}" = "stopavm" ]
then
    idnum="1"
    vmlistsave=""
    for i in ${vmlist//\;/ }
    do
        vmdataarray=(${i//|/ })
        vmname="${vmdataarray[1]}"
        vmuuid="${vmdataarray[0]}"
        vmstatus="${vmdataarray[2]}"

        if [ $vmname != "HostedEngine" ];then
            if [[ $vmstatus == "up" ]]; then
                for z in $(echo $_return)
                do

                    if [ $z -eq  $idnum ]
                    then
                        #stop VM selected
                        obuapicall "POST" "vms/${vmuuid}/shutdown/" "<action/>"
                        obualert "\n\nVM Name: ${vmname}\n\n(${vmuuid})\n\nRequested: SHUTDOWN"
                    fi
                done
                idnum=$((idnum + 1))
            fi
        fi
    done

    ./$(basename $0) && exit

fi