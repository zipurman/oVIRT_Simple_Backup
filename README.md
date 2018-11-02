#oVIRT_Simple_Backup - WebGUI (0.6.27) 

### A REST API backup from PHP for oVirt 4.2.x
   
#### To Do
 - [ ] Detect VMs that have issues and disallow backing up with warnings/alerts


   
[ChangeLog](https://github.com/zipurman/oVIRT_Simple_Backup/blob/master/ChangeLog.md)

---

#### Features

 - [x] oVirt Engine Web UI Plugin
 - [x] Multiple Backup Schedules Manager
 - [x] Settings Manager
 - [x] Disk Image Compression (gzip/lzo)
 - [x] Updates Manager for oVIRT_Simple_Backup
 - [x] Backup a single VM
 - [x] Restore a single VM
 - [x] Migrate a single VM from XEN SERVER (Citrix)
 - [x] Scheduled VMs Backup Retention and Email Alerts
 - [x] Multi-Disk VM now supported
 - [x] Log viewer

---

#### Install Script

This script is recommended to be used to setup the oVirt BackupEngine VM.

!!!! Zipur, consider removing the xenAinstaller section from the script. I used the script to setup my VM its much easier than the manual steps !!!!!
[Debian Instructions](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/server/installer)

*** ONLY DEBIAN IS SUPPORTED ***
The Debian version tested is 9.5.0.

---


#### Manual Install
The manual steps will walk you through the setup of the BackupEngine VM.
If you want to use the Citrix Xen migration tools then you must follow the appropiate section in the manual install.
You can choose to setup the BackupEngine VM with the script and then manually setup the Xen Migration.

*It is HIGHLY recommended you go through the manual steps first to understand what you need to do*

[Debian Install Instructions](https://github.com/zipurman/oVIRT_Simple_Backup/blob/master/docs/install_debian.md)

*** ONLY DEBIAN IS SUPPORTED ***
The Debian version tested is 9.5.0.

---

#### Install oVirt Client on Debian VM

[Client Install Instructions](http://zipur.ca/knowledgebase/debian-8-jessie-ovirt-guest-agent/)

---


#### Frequently Asked Questions

[FAQ](https://github.com/zipurman/oVIRT_Simple_Backup/blob/master/FAQ.md)


---
 
![ ](screenshots/SS.0.6.14.00.png?raw=true)

[More Screenshots](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/screenshots)

---

#### Author

You can reach zipur on the IRC SERVER irc.oftc.net CHANNEL #ovirt

http://zipur.ca

aka (Preston Lord)

