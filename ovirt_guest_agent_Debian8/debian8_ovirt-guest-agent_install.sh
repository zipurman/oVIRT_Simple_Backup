#!/bin/bash

#####################################
#
# Debian 8 ovirt-guest-agent install
# Version: 0.1.0
#
# Built as a tool for oVIRT_Simple_Backup
#
# Date: 02/09/2018
#
# Simple Script to setup ovirt-guest-agent  on a fresh install of Debian 8
#
# Script on github.com
# https://github.com/zipurman/oVIRT_Simple_Backup
#
# Author: zipur (www.zipur.ca)
# IRC: irc.oftc.net #ovirt
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
#####################################

xbuversion="0.1.0"

echo "Updating Apt with sources..."
echo "deb http://download.opensuse.org/repositories/home:/evilissimo:/deb/Debian_7.0/ ./" >> /etc/apt/sources.list
gpg -v -a --keyserver http://download.opensuse.org/repositories/home:/evilissimo:/deb/Debian_7.0/Release.key --recv-keys D5C7F7C373A1A299
gpg --export --armor 73A1A299 | apt-key add -
apt-get update
apt-get install ovirt-guest-agent

sed -i 's/# device = \/dev\/virtio-ports\/com.redhat.rhevm.vdsm/device = \/dev\/virtio-ports\/ovirt-guest-agent\.0/g' /etc/ovirt-guest-agent.conf

echo "SYMLINK==\"virtio-ports/ovirt-guest-agent.0\", OWNER=\"ovirtagent\",GROUP=\"ovirtagent\"" >> /etc/udev/rules.d/55-ovirt-guest-agent.rules
udevadm trigger --subsystem-match="virtio-ports"

service ovirt-guest-agent stop
service ovirt-guest-agent start
service ovirt-guest-agent status

echo "ovirt-guest-agent install should now be running"