#!/bin/bash

black=`tput setaf 0`
red=`tput setaf 1`
green=`tput setaf 2`
yellow=`tput setaf 3`
white=`tput setaf 7`
reset=`tput sgr0`
host=`hostname`
uptime=`uptime`

# trap ctrl-c and call ctrl_c()
trap ctrl_c INT

function ctrl_c() {
        clear
        echo "${reset}"
        exit 0
}

#clear screen to start script fresh
clear

counter=1
countericon=1
while [ $counter -le 1800 ]
do

    if [ $countericon -eq 1 ]
    then
        countericon=2
        countericontext='-'
    elif  [ $countericon -eq 2 ]
    then
        countericon=3
        countericontext='\'
    elif  [ $countericon -eq 3 ]
    then
        countericon=4
        countericontext='|'
    elif  [ $countericon -eq 4 ]
    then
        countericon=1
        countericontext='/'
    fi

    #top title
    echo -e -n "\e[1m"
    echo -e -n "${green} ${host} ${white} oVirt Monitor - Version: 0.0.1 - By: ZIPUR \n${green} ${uptime}"
    echo -e -n "\e[0m"
    echo -e -n "${white}\n"
    printf '%*s\n' "${COLUMNS:-$(tput cols)}" '' | tr ' ' -  #horiz line
    echo ""

    enginestatus=`hosted-engine --vm-status | grep "Engine status"`
    echo -n -e "Engine Status: "
    if [[ $enginestatus = *"good"* ]]; then
        echo -n -e "${green}GOOD"
    else
        echo -n -e "${red}BAD ${enginestatus}"
    fi

    echo -n -e "\n${white}\n"

    #MEMORY --------------------------------------------------------------
    memtotal=$(free | awk '/^Mem:/{print $2}')
    memused=$(free | awk '/^Mem:/{print $3}')
    (( mempercent = ( (memused * 100) / memtotal ) ))
    echo -n -e "Memory: ${mempercent}% \t["
    memcounter=0
    echo -n "${green}"
    while [ $memcounter -le 100 ]
    do
        if [ $memcounter -eq 50 ]
        then
            echo -n  "${yellow}"
        fi
        if [ $memcounter -eq 75 ]
        then
            echo -n  "${red}"
        fi
        if [ $mempercent -eq $memcounter ]
        then
            echo -n -e "\e[2m" #dim text
            echo -n " ${countericontext} "
        else
            echo -n '|'
        fi
        ((memcounter++))
    done
    echo -n "${white}"
    echo -n -e "\e[22m" #un dim text
    echo -n -e "]"
    echo ""

    #SWAP --------------------------------------------------------------
    swaptotal=$(free | awk '/^Swap:/{print $2}')
    swapused=$(free | awk '/^Swap:/{print $3}')
    (( swappercent = ( (swapused * 100) / swaptotal ) ))
    echo -n -e "Swap: ${swappercent}% \t["
    memcounter=0
    echo -n "${green}"
    while [ $memcounter -le 100 ]
    do
        if [ $memcounter -eq 50 ]
        then
            echo -n  "${yellow}"
        fi
        if [ $memcounter -eq 75 ]
        then
            echo -n  "${red}"
        fi
        if [ $swappercent -eq $memcounter ]
        then
            echo -n -e "\e[2m" #dim text
            echo -n " ${countericontext} "
        else
            echo -n '|'
        fi
        ((memcounter++))
    done

    echo -n "${white}"
    echo -n -e "\e[22m" #un dim text
    echo -n -e "]"
    echo ""
    echo ""
    echo "DISKS"
    echo "-----"

    #DISKS --------------------------------------------------------------
    for i in $(df | awk '{print $6}'); do
        if [[ $i != 'Mounted' ]]
        then

            dfdata=`df $i | sed -sn 2p`

            disktype=`echo $dfdata | awk '{print $1}'`
            disksize=`echo $dfdata | awk '{print $2}'`
            diskuse=`echo $dfdata | awk '{print $3}'`
            diskmountpoint=`echo $dfdata | awk '{print $6}'`
            (( diskpercent = ( (diskuse * 100) / disksize ) ))
            (( diskpercentgraph = ( (diskuse * 25) / disksize ) ))

            if [[ $disktype != 'tmpfs' ]] && [[ $disktype != 'udev' ]] && [ $disksize -gt 10000000 ]
            then

                diskmountpoint=`echo $diskmountpoint  | sed 's/\/rhev\/data-center\/mnt\//-> /g'`
                echo -n -e "Disk: ${diskmountpoint}"
                stringpathsize=${#diskmountpoint}
                if [ $stringpathsize -lt 2 ];then echo -n -e "\t"; fi
                if [ $stringpathsize -lt 9 ];then echo -n -e "\t"; fi
                if [ $stringpathsize -lt 18 ];then echo -n -e "\t"; fi
                if [ $stringpathsize -lt 27 ];then echo -n -e "\t"; fi
                if [ $stringpathsize -lt 34 ];then echo -n -e "\t"; fi
                echo -n -e "\t\t["
                memcounter=0
                echo -n "${green}"
                while [ $memcounter -le 25 ]
                do
                    if [ $memcounter -eq 12 ]
                    then
                        echo -n  "${yellow}"
                    fi
                    if [ $memcounter -eq 19 ]
                    then
                        echo -n  "${red}"
                    fi
                    if [ $diskpercentgraph -eq $memcounter ]
                    then
                        echo -n -e "\e[2m" #dim text
                        echo -n " ${countericontext} "
                    else
                        echo -n '|'
                    fi
                    ((memcounter++))
                done

                echo -n "${white}"
                (( diskuse = ( diskuse / 1024 / 1024) ))
                (( disksize = ( disksize / 1024 / 1024) ))

                echo -n -e "\e[22m" #un dim text
                echo -n -e "] ${diskpercent}% \t ${diskuse}/${disksize}\n" #un dim text
            fi

        fi

    done

    ips=`hostname -I`
    echo -n -e  "\n${white}IPS: ${green} ${ips}"

    echo -e "\n"
    echo -n -e  "${white}VMs: "
    result=0
    linenum=1
    for i in $(virsh -r -c qemu:///system list --all | awk '{print $0}'); do

        if [[ $i == '1' ]] && [ $result -eq 0 ];then
            result=1
        fi

        if [[ $result -eq 1 ]];then
            if [ $linenum -eq 1 ];then
                vmnum=`echo $i`
                linenum=2
            elif [ $linenum -eq 2 ];then
                vmname=`echo $i`
                linenum=3
            elif [ $linenum -eq 3 ];then
                vmstatus=`echo $i`
                echo -n -e  "${white}["
                if [[ $vmstatus == 'running' ]]; then
                    echo -n -e  "${green}"
                else
                    echo -n -e  "${red}"
                fi
                echo -n -e "${vmname}"
                echo -n -e  "${white}] "
                linenum=1
            fi
        fi

    done

    echo -n -e  "\n\n${white}NICS: ${green}"
    needcomma=0
    for i in $(ip a | grep "state UP" | grep -v "vdsmdummy" | awk '{print $2}'); do
        if [ $needcomma -eq 1 ];then
                echo -n -e ", "
        fi
        echo -n -e "${i}" | sed 's/:$//g'
        needcomma=1
    done

    echo -n -e  "${red}"
    for i in $(ip a | grep "state DOWN" | grep -v "vdsmdummy" | awk '{print $2}'); do
        if [ $needcomma -eq 1 ];then
                echo -n -e ", "
        fi
        echo -n -e "${i}" | sed 's/:$//g'
        needcomma=1
    done

    echo -n "${white}"
    echo -e "\n\nMulti Paths\n------------"
    multipath -ll

    echo -n "${white}"
    echo -e "\niSCSI Sessions\n---------------"
    iscsiadm --m session --op show


    #mast counter and delay
    ((counter++))
    sleep 1

    #clear screen from top down so repaint can happen (faster than clear)
    tput civis
    clearcounter=1
    while [ $clearcounter -le 100 ]
    do
        tput cuu1
        ((clearcounter++))
    done

done

#reset color for terminal
echo "${reset}"
