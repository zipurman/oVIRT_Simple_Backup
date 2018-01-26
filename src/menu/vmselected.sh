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
if [ "${_return}" = "0" ] && [ "${menuposition}" = "frombase" ]
then
    menuposition="selectedvms"
    obuapicall "GET" "vms"
    vmslist="${obuapicallresult}"
    vmlist=`echo $vmslist | xmlstarlet sel -T -t -m /vms/vm -s D:N:- "@id" -v "concat(@id,'|',name,'|',status,';')"`
    optionstext=""
    optionid="1"
    for i in ${vmlist//\;/ }
        do
            vmdataarray=(${i//|/ })
            vmname="${vmdataarray[1]}"
            vmstatus="${vmdataarray[2]}"
            vmuuid="${vmdataarray[0]}"
            if [ "${vmname}" != "HostedEngine" ];then
                if [[ $vmlisttobackup == *"[$vmname]"* ]]; then
                    optionis="on"
                else
                    optionis="off"
                fi
                if [[ $vmstatus == "up" ]]; then
                    vmstatustxt="\Zr\Z2up\ZR\Z0|\Zn"
                else
                    vmstatustxt="\Zr\Z1${vmstatus}\ZR\Z0|\Zn"
                fi

                showvmname="${vmstatustxt}${vmname}"

                optionstext="${optionstext} ${optionid} ${showvmname} ${optionis} "
                optionid=$((optionid + 1))
            fi
    done
    dialog --colors --column-separator "|" --backtitle "${obutitle}" --title "Targeted VMs List" --cancel-label "Main Menu" --cr-wrap --checklist "VMs currently targeted for backup are:" 25 50 50 $optionstext 2> $menutmpfile
    _return=$(cat $menutmpfile)
fi
###################################################################

#save selected VMs to a file
if [ "${_return}" != "0" ] && [ "${menuposition}" = "selectedvms" ]
then
    idnum="1"
    vmlistsave=""
    for i in ${vmlist//\;/ }
    do
        vmdataarray=(${i//|/ })
        vmname="${vmdataarray[1]}"
        vmuuid="${vmdataarray[0]}"

        if [ $vmname != "HostedEngine" ];then
            echo "${vmname}"
            for z in $(echo $_return)
            do
                if [ $z -eq  $idnum ]
                then
                    vmlistsave="${vmlistsave}[${vmname}]"
                fi
            done
            idnum=$((idnum + 1))
        fi
    done

    obusettings_write "${vmlistsave}" 2

    ./$(basename $0) && exit

fi