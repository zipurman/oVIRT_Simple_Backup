# oVIRT_Simple_Backup - WebGUI - Installer - Debian

## Instructions for using this installer

 - [x] Install a new VM in oVirt as Debian 9 Command Line Only with ssh
 
 - [x] Setup the VM IP address/gateway to what you will use on your BackupEngineVM
 
 - [x] apt-get install curl
 
 - [x] as root run the following command
 ```bash
curl https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/server/installer/install.sh | bash
```

 - [x] Follow the onscreen instructions
 
 - [x] Navigate to your new install at https://FQDNofYourBackupEngine and accept the SSL certificate
 
 - [x] Go to settings tab and enter in the UUID, FQDN, USER, PASS and save
 
 - [x] Then set the DOMAIN and CLUSTER and save again
 
 - [x] Then enter any additional info and save one more time
 
 - [x] You will also need to delete any snapshots you created on youur BackupEngineVM during install
 
 - [x] You should now be able to test your new install and do a backup