# oVirtSimpleBackup - WebGUI (0.6.32) 

### A REST API backup from PHP for oVirt 4.2.x
   
#### NOTE

_**I no longer use oVirt, so I wont be furthering this project. oVirt is >>Awesome<< however, I decided to move all of my VMs into a large managed datacentre that uses vmware.

I want to thank the oVirt community and all of the people over on IRC for thier awesome support.

Feel free to use this code for your own ovirt backups or future oVirt backup software.**_


   
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

#### Install Steps if using Xen Migration with oVirtSimpleBackup

 1. [Read and Understand how it all works here](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/server/installer/XENHOWITWORKS.md)
 
 2. [Create the VMMIGRATE VM in Xen Server Environment](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/server/installer/ovirt-simple-backup-xenvm/README.md)
 
 3. [Run the oVirtSimpleBackup Install Script](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/server/installer/README.md)

 4. [Post Migration Troubleshooting Tips](https://github.com/zipurman/oVIRT_Simple_Backup/blob/master/docs/POST_XEN_MIGRATION.md)

---

#### Install Steps if only using oVirtSimpleBackup for backing up oVirt VMs

 1. [Read and Understand how it all works here](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/server/installer/HOWITWORKS.md)
 
 2. [Run the oVirtSimpleBackup Install Script](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/server/installer/README.md)


---


#### Manual Install (for reference)

The manual steps will explain how everything works and allow you to adjust if you require. The recommended installation method is using the script above.

[Debian Install Instructions](https://github.com/zipurman/oVIRT_Simple_Backup/blob/master/docs/install_debian.md)

---



#### Frequently Asked Questions

[FAQ](https://github.com/zipurman/oVIRT_Simple_Backup/blob/master/FAQ.md)


---
 
![ ](screenshots/SS.0.6.14.00.png?raw=true)

[More Screenshots](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/screenshots)

---

#### Author

Zipur


