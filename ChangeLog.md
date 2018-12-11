
#### Change Log
 
 [Back To ReadMe](https://github.com/zipurman/oVIRT_Simple_Backup/)
 
  - 0.6.30 - 2018/12/11 
      - Add ability to choose first backup disk which allows for connected storage on BackupVM.
      - Added pbzip2 and bzip2 compression options. If using pbzip2, ``apt-get install pbzip2`` 
        - buggy for now recommend sticking with LZO until fixed
      - Adjusted log so that multiple lines of the same value do not log
      - Check to make sure disks writeable before allowing backup
      - Verify disk images are valid after backup and prior to restore to make sure errors are thrown
      - When restoring a backup, the disk type (virtio/scsi/ide) will now match the original backed up VM
      - Minor adjustments to lists, prompts, logs, etc
      
  - 0.6.29 - 2018/12/6 
      - Add disk space alerts to code for /mnt/backups
      - Hide lost+found directory on restore list
      
  - 0.6.28 - 2018/12/5 
      - Minor Docs Updates
      - Adjust UUID to be more flexible for importing from KVM UUIDs (Thanks doe-cu)
      - Add cluster name to VM list when choosing a VM for backup
      
  - 0.6.27 - 2018/10/12 
      - Add extra setting for backing up memory state (yes/no)
      
  - 0.6.26 - 2018/09/29
      - Add extra logic for troubleshooting initial user/pass entry
            
  - 0.6.25 - 2018/09/20
      - Xen Migrate VMS - sort list by name
      - Single Backup - show VM Comment if set
      
  - 0.6.24 - 2018/09/1
      - Fix a couple issues with installer script, manual install how-to
      - Fix issue with "desktop" version of restore process
      
  - 0.6.23 - 2018/08/29
      - Fix code to be better with other languages, but "US English" is still recommended to make sure all scripts function as intended
      
  - 0.6.22 - 2018/05/31
      - Fix some issues with Xen migration logic
      - [x] Allow for grub fix on CentOS7 VMs (requires installing package "lvm2") ** Still buggy
      - [x] Alert "!" will now show in top right menu if version update available
  
  - 0.6.21 - 2018/05/15
      - Fix bug with random emails coming from automatedbackup routine
      
  - 0.6.20 - 2018/05/11
      - [x] Added "from" email address option to settings and email alerts
  
  - 0.6.19 - 2018/05/04
      - Misc bug fixes
      
  - 0.6.18 - 2018/04/23
      - [x] Added lzo encryption (Thanks to Squeekz)
      - Bug fixes on multi-disk backup
      - Misc bug fixes
  
  - 0.6.17 - 2018/04/10
      - [x] Minor tweaks
  
  - 0.6.16 - 2018/04/06
      - [x] Add a scripted installer for Debian 9
      - [x] Compression on backup images added as an option

      
  - 0.6.15 - 2018/04/06
      - [x] Added Multi Schedule Manager for Scheduled Backups
      
  - 0.6.14 - 2018/04/06
      - [x] Update/Upgrade area to fetch new files etc.

  - 0.6.13 - 2018/04/06
      - Fixed issues with Restores not showing in list if they contained a '.' in their VMName
      - Minor fixes to automated backup logic
      - Extra logic added to detect if oVirtSimpleBackupVM has OS mounted as something other than expected /dev/*da
  
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
    - [x] Revise disk mount process to adapt to /dev/?d* to avoid issues with disks not being freed up in time by the oVirtSimpleBackupVM OS
 
 - 0.6.6 - 2018/04/04
    - [x] Auto detect virtio/virtio_scsi from /dev/*da to avoid misconfigurations
    - [x] Versioning patches will now auto apply
 
 - 0.6.5 - 2018/03/30
    - [x] oVirtSimpleBackup now checks for any snapshots on oVirtSimpleBackupVM as this will disallow attaching disks dynamically. Warnings will now show if snapshots exist on the oVirtSimpleBackupVM.
    - [x] oVirtSimpleBackup will now detect if Storage Domain is not yet set and hide the main menu (other than settings) if Storage Domain has not yet been set.
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
    