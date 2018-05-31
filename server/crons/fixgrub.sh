#!/bin/bash

#This file can be added to the /etc/crontab for run every 1 minute
# * * * * * root /var/www/html/crons/fixgrub.sh >>/var/log/fixgrub.log 2>&1
#File will look for /var/www/html/crons/fixgrub.dat file
#If file had "1" as contents - this script will run and then will set the value to "2"
#Web Interface will then set 2 to 0 or 0 to 1 depending on requirements
#This allows root access to correct issues with web interface permission limits

#required for cronjob
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR
export TERM=xterm

if [ -f "/var/www/html/crons/fixgrub.dat" ]; then
    if [ -f "/var/www/html/crons/fixgrubtarget.dat" ]; then

    fixgrubyn=`cat /var/www/html/crons/fixgrub.dat`
    fixgrubtarget=`cat /var/www/html/crons/fixgrubtarget.dat`

        if [[ $fixgrubyn == 1 ]]
        then

            mkdir /mnt/linux -p

            if [ -f "/mnt/linux/etc/centos-release" ]; then
            ######CENTOS --- TODO List doesnt work as Debian to CENTOS creates issues
                lvscan
                vgchange -ay
                mount /dev/centos/root /mnt/linux
                mount /dev/${fixgrubtarget}1 /mnt/linux/boot
                mount -o bind /proc /mnt/linux/proc
                mount -o bind /dev /mnt/linux/dev
                mount -o bind /sys /mnt/linux/sys

                cat << EOF | chroot /mnt/linux /bin/bash
cd /boot
dracut -f
grub2-mkconfig -o /boot/grub2/grub.cfg
exit
EOF

                umount /mnt/linux/boot

            else
            ######DEBIAN

                mount /dev/${fixgrubtarget}1 /mnt/linux
                mount -o bind /proc /mnt/linux/proc
                mount -o bind /dev /mnt/linux/dev
                mount -o bind /sys /mnt/linux/sys
                cat << EOF | chroot /mnt/linux /bin/bash
sed -i 's/console=hvc0//g' /etc/default/grub
update-grub
update-initramfs -u
grub-install ${fixgrubtarget}
exit
EOF

            fi

            umount /mnt/linux/dev/
            umount /mnt/linux/proc/
            umount /mnt/linux/sys/
            umount /mnt/linux/


            echo "2" > /var/www/html/crons/fixgrub.dat
            rm /var/www/html/crons/fixgrubtarget.dat
        fi
    fi

fi