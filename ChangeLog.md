
#### Change Log
 
 [Back To ReadMe](https://github.com/zipurman/oVIRT_Simple_Backup/)
 
  - 0.6.14 - 2018/04/06
      - [x] Update/Upgrade area to fetch new files etc.

  - 0.6.13 - 2018/04/06
      - Fixed issues with Restores not showing in list if they contained a '.' in their VMName
      - Minor fixes to automated backup logic
      - Extra logic added to detect if BackupEngineVM has OS mounted as something other than expected /dev/*da
  
  - 0.6.12 - 2018/04/05
      - [x] Allow option for thin provisioned (sparse) disks in restore/migrate
      - [x] When restoring or migrating, if choosing a storage domain that supports discard, then you can optionally pass_discard=true to the VM disk(s)
      
  - 0.6.11 - 2018/04/05
     - Additional bug fixes with automated backups and errors with fdisk not being found
     - [x] Added extra logic to WebUI if Automated Backup is running
   
  - 0.6.10 - 2018/04/05
     - [x] recover running tasks if browser is closed and re-opened.  Just visit status page and click the link to continue the running job.
  
  - 0.6.9 - 2018/04/05
     - Additional Bug fixes in Restore of Multiple Disk VMs
     - [x] Moved changelog to its own file
     
 - 0.6.8 - 2018/04/04
    - Bug fixes in Restore of Multiple Disk VMs
    
 - 0.6.7 - 2018/04/04
    - [x] Revise disk mount process to adapt to /dev/?d* to avoid issues with disks not being freed up in time by the BackupEngineVM OS
 
 - 0.6.6 - 2018/04/04
    - [x] Auto detect virtio/virtio_scsi from /dev/*da to avoid misconfigurations
    - [x] Versioning patches will now auto apply
 
 - 0.6.5 - 2018/03/30
    - [x] BackupEngine now checks for any snapshots on BackupEngine VM as this will disallow attaching disks dynamically. Warnings will now show if snapshots exist on the BackupEngine VM.
    - [x] BackupEngine will now detect if Storage Domain is not yet set and hide the main menu (other than settings) if Storage Domain has not yet been set.
    - [x] Settings will now fix bad log file path.
    
 - 0.6.4 - 2018/03/27
    - Minor fixes
    - Added docs: nfs-common required (on BackupVM)
    - Added docs: chmod 755 /usr/share/ovirt-engine/ui-plugins/simpleBackup* -R (On Engine VM)
    - [x] Added a "Click Here" to the top of the settings page to test config with Engine.

 - 0.6.3 - 2018/03/26
    - [x] Scheduled backups with retention periods and email alerts have been added. Instructions below.

 - 0.6.2 - 2018/03/26
    - [x] Added version detection on upgrade to clear caches and any other required version adjustments using a versioning function
    - [x] Retired old BASH Scripts and promoted PHP version to root of project

 - 0.6.1 - 2018/03/25
    - Minor code adjustments and bug fixes
    
 - 0.6.0 - 2018/03/25
    - [x] Obscured admin password for ovirt in code and config.php. This will require a re-config of your settings.
    - [x] Added a log viewer
    - [x] Added logging to all areas
    - [x] Complete JS re-work to move all state into php to allow for future recover processes
    - [x] Complete re-work of php processes and functions for xen migrations, backups, and restores to allow for better tracking and state.
    - [x] Added support for multi-disk VM backup/restore
    - [x] Added timezone support so that logs will be reported in correct date/time and so that future scheduling features will have correct date/time
    
  - Prior Versions were not tracked for version history as project was just getting started.
    