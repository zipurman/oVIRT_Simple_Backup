# oVIRT_Simple_Backup

### A REST API backup from BASH for oVirt 4.2.x

The cron creation on the oVirt Engine was to re-start the backup appliance after it was shutdown. oVirt 4.2.0 was creating a situation where the backup appliance VM was not releasing the /dev/sdX when the disks were removed. The only way to fix it was to fully shutdown the backup appliance once all backups were done. Then this cron would start the backup VM again for the next scheduled backup.

It worked to resolve the issue in 4.2.0 but the issue seems to be fixed in 4.2.1 so I would recommend upgrading rather than using this crazy workaround.

#### Requirements

oVirt Engine (if using cron to restart Backup_VM_Appliance)
 - expect
 - If using cron, place scripts in /root/ and adjust scripts as required with user/pass server info

#### Install

**On the oVirt Engine**  (if using cron to keep the Backup_VM_Appliance alive)
 - As root
    ```bash
    crontab -e
    ```
 - Add the following
    ```bash
    */15 * * * * /root/restart_backup_vm.sh > /dev/null 2>&1
    ```