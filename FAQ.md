#### Frequently Asked Questions
 
[Back To ReadMe](https://github.com/zipurman/oVIRT_Simple_Backup/)
 
 
- [x] What is the best way to figure out why my backups are not working
    
    - Make sure you don't have any snapshots on your BackupEngiveVM as this will stop your VM and the script from allowing attachments of additional disks to be backed up.
    
    - Before running a backup do the following
        - On BackupEngineVM 
            - tail /var/log/simplebackup.log -f
        - On oVirtEngine
            - tail /var/log/ovirt-\*/*.log -f
        - Once the above logs are being tailed, then try a backup and watch for errors to see where the issue is.
        
        
*Will add more FAQ items as they arise*