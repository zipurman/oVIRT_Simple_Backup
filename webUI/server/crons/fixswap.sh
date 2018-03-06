#!/bin/bash

#This file can be added to the /etc/crontab for run every 1 minute
# * * * * * root /var/www/html/crons/fixswap.sh >>/var/log/fixswap.log 2>&1
#File will look for /var/www/html/crons/fixswap.dat file
#If file had "1" as contents - this script will run and then will set the value to "2"
#Web Interface will then set 2 to 0 or 0 to 1 depending on requirements
#This allows root access to correct issues with web interface permission limits

#required for cronjob
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR
export TERM=xterm

if [ -f "/var/www/html/crons/fixswap.dat" ]; then
    if [ -f "/var/www/html/crons/fixswaptarget.dat" ]; then

    fixswapyn=`cat /var/www/html/crons/fixswap.dat`
    fixswaptarget=`cat /var/www/html/crons/fixswaptarget.dat`

        if [[ $fixswapyn == 1 ]]
        then

        mkdir /mnt/linux -p
        mount /dev/${fixswaptarget}1 /mnt/linux

        #create new script to pass to chroot
        echo "#!/bin/bash" > /tmp/fixswap1.sh
        echo "echo -e \"p\nd\n2\nn\np\n2\n\n\np\nt\n2\n82\nw\n\" | fdisk /dev/vda" >> /tmp/fixswap1.sh
        echo "shutdown 0 -r" >> /tmp/fixswap1.sh

        echo "#!/bin/bash" > /tmp/fixswap2.sh
        echo "newuuid=\`mkswap /dev/vda2\`" >> /tmp/fixswap2.sh
        echo "swapon /dev/vda2" >> /tmp/fixswap2.sh
        echo "newuuid=\"$(echo \$newuuid | cut -d ' ' -f 12 | tail -1 | sed 's/UUID=//')\"" >> /tmp/fixswap2.sh
        echo "sed -i 's/UUID=[a-zA-Z0-9\-]*.*swap.*$/UUID='\${newuuid}' none            swap    sw              0   /g' /etc/fstab" >> /tmp/fixswap2.sh
        echo "echo \"New UUID SET: \${newuuid}\"" >> /tmp/fixswap2.sh
        echo "cat /etc/fstab" >> /tmp/fixswap2.sh
        echo "fdisk -l" >> /tmp/fixswap2.sh
        echo "free" >> /tmp/fixswap2.sh
        echo "echo \"Make sure the above shows correctly\"" >> /tmp/fixswap2.sh

        /bin/chmod 700 /tmp/fixswap1.sh
        /bin/chmod 700 /tmp/fixswap2.sh

        /bin/cp /tmp/fixswap1.sh /mnt/linux/root/fixswap1.sh
        /bin/cp /tmp/fixswap2.sh /mnt/linux/root/fixswap2.sh

        umount /mnt/linux/

            echo "2" > /var/www/html/crons/fixswap.dat
            /bin/rm /var/www/html/crons/fixswaptarget.dat
        fi
    fi

fi