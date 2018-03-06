var sb_completedtasks = 0;
var sb_newdiskcreateprogress = 0;
var sb_completedtasks = [];
var sb_currenttask = '';
var sb_alerttext = '';
var sb_xen_migrate_progress = 0;


function sb_update_statusbox(domid, statustext) {
    if (statustext.toLowerCase().indexOf('complete') != -1) {
        $("#" + domid).html('(' + statustext + ')');
    } else {
        $("#" + domid).html('<i class="fa fa-refresh fa-spin" style="font-size:14px; margin-right: 10px;"></i> ' + statustext);
    }
}

function sb_check_snapshot(vmuuid, snapshotname) {
    var queryx = {};
    queryx['comm'] = 'snapshot_status';
    queryx['vmuuid'] = vmuuid;
    queryx['snapshotname'] = snapshotname;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 0 || json.status == -1) {
                sb_check_snapshot_progress(vmuuid, snapshotname);
                sb_update_statusbox('backupstatus', 'Creating Snapshot');
            } else {
                $("#snapshotbar .progressbarinner").html('Snapshot 100% (COMPLETED)').css('width', '100%');
                sb_update_statusbox('backupstatus', 'Snapshot Created');
                sb_create_backup_directories(vmuuid, snapshotname);

            }
        }
    });
}

function sb_check_snapshot_progress(vmuuid, snapshotname, progress) {
    $("#snapshotbar").css('display', 'block');
    if (progress == 0) sb_completedtasks = 10;
    sb_completedtasks++;
    // alert(sb_completedtasks);
    sb_check_snapshot(vmuuid, snapshotname);
    if (sb_completedtasks <= 100) {
        $("#snapshotbar .progressbarinner").html('Snapshot ' + sb_completedtasks + '%').css('width', sb_completedtasks + '%');
    } else {
        $("#snapshotbar .progressbarinner").html('Snapshot 100% (WAITING)');
    }
}

function sb_create_backup_directories(vmuuid, snapshotname) {
    sb_update_statusbox('backupstatus', 'Creating Backup Directories');
    var queryx = {};
    queryx['comm'] = 'backup_create_path';
    queryx['vmuuid'] = vmuuid;
    queryx['snapshotname'] = snapshotname;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('backupstatus', 'Backup Directories Created');
                sb_snapshot_attach(vmuuid, snapshotname);
            } else {
                sb_update_statusbox('backupstatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_snapshot_attach(vmuuid, snapshotname) {
    sb_update_statusbox('backupstatus', 'Attaching Snapshot To Backup Appliance For Imaging');
    var queryx = {};
    queryx['comm'] = 'backup_attach_image';
    queryx['vmuuid'] = vmuuid;
    queryx['snapshotname'] = snapshotname;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('backupstatus', 'Snapshot Attached');
                sb_snapshot_imaging(vmuuid, snapshotname);
            } else {
                sb_update_statusbox('backupstatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_snapshot_imaging(vmuuid, snapshotname) {
    sb_update_statusbox('backupstatus', 'Imaging Disk');
    $("#imagingbar").css('display', 'block');
    var queryx = {};
    queryx['comm'] = 'backup_imaging';
    queryx['vmuuid'] = vmuuid;
    queryx['snapshotname'] = snapshotname;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('backupstatus', 'Imaging Started');
                sb_snapshot_imaging(vmuuid, snapshotname);
            } else if (json.status == 2) {
                sb_update_statusbox('backupstatus', 'Imaging In Progress');
                $("#imagingbar .progressbarinner").html('Imaging ' + json.progress + '%').css('width', json.progress + '%');
                if (json.progress == 100) {
                    sb_update_statusbox('backupstatus', 'Imaging Complete');
                    sb_snapshot_detatch(vmuuid, snapshotname);
                } else {
                    sb_snapshot_imaging(vmuuid, snapshotname);
                }
            } else {
                sb_update_statusbox('backupstatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_snapshot_detatch(vmuuid, snapshotname) {
    sb_update_statusbox('backupstatus', 'Detaching Snapshot From Backup Appliance');
    var queryx = {};
    queryx['comm'] = 'backup_detatch_image';
    queryx['vmuuid'] = vmuuid;
    queryx['snapshotname'] = snapshotname;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('backupstatus', 'Snapshot Detached');
                sb_snapshot_delete(vmuuid, snapshotname);
            } else {
                sb_update_statusbox('backupstatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_snapshot_delete(vmuuid, snapshotname) {
    sb_update_statusbox('backupstatus', 'Detaching Snapshot From Backup Appliance');
    var queryx = {};
    queryx['comm'] = 'snapshot_delete';
    queryx['vmuuid'] = vmuuid;
    queryx['snapshotname'] = snapshotname;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('backupstatus', 'Snapshot Deleted');
                sb_update_statusbox('backupstatus', 'Backup Completed');
            } else {
                sb_update_statusbox('backupstatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function checkRestoreNow(askforokyn) {

    var newvmname = $("#restorenewname").val();
    var disksize = $("#disksize").val();
    var newvmnamecheck = newvmname.replace(/[0-9a-zA-Z\-_]/g, "");
    if (newvmname == '') {
        alert('You must enter a New VM Name to restore this image to.')
    } else if (newvmnamecheck != '') {
        alert('You must enter a New VM Name that does not contain anything other than a-zA-Z0-9-_')
    } else {
        if (askforokyn == 1) {
            if (confirm('Restore this VM Now?')) {
                $(".gobutton").css('display', 'none');
                sb_check_disk_progress(newvmname, disksize, 0);
            }
        } else {
            $(".gobutton").css('display', 'none');
            sb_check_disk_progress(newvmname, disksize, 0);
        }
    }
}


function sb_restore_disk_create(diskname, disksize) {

    var queryx = {};
    queryx['comm'] = 'restore_disk_create';
    queryx['diskname'] = diskname;
    queryx['disksize'] = disksize;
    queryx['progress'] = sb_newdiskcreateprogress;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            sb_newdiskcreateprogress += 5;
            if (json.status == 0) {
                sb_update_statusbox('restorestatus', 'Failed - ' + json.reason);
                $("#creatediskstatus").html('').css('display', 'none');
            } else if (json.status == 1) {
                sb_check_disk_progress(diskname, disksize);
                sb_update_statusbox('restorestatus', 'Creating Disk');
            } else if (json.status == 2) {
                sb_check_disk_progress(diskname, disksize);
                sb_update_statusbox('restorestatus', 'Waiting for Disk');
            } else if (json.status == 3) {
                $("#creatediskstatus .progressbarinner").html('Disk 100% (COMPLETED)').css('width', '100%');
                sb_update_statusbox('restorestatus', 'Disk Ready');
                sb_restore_imaging(diskname, disksize, json.diskuuid);
            }

        }
    });
}

function sb_check_disk_progress(diskname, disksize, progress) {
    $("#creatediskstatus").css('display', 'block');
    if (progress == 0) sb_newdiskcreateprogress = 0;
    sb_restore_disk_create(diskname, disksize);
    if (sb_newdiskcreateprogress <= 100) {
        $("#creatediskstatus .progressbarinner").html('Disk ' + sb_newdiskcreateprogress + '%').css('width', sb_newdiskcreateprogress + '%');
    } else {
        $("#creatediskstatus .progressbarinner").html('Disk 100% (WAITING)');
    }
}

function sb_restore_imaging(diskname, disksize, diskuuid) {
    sb_update_statusbox('restorestatus', 'Imaging Disk');
    $("#imagingbar").css('display', 'block');
    var buname = $("#buname").val();
    var vmname = $("#vmname").val();
    var vmuuid = $("#vmuuid").val();
    var queryx = {};
    queryx['comm'] = 'restore_imaging';
    queryx['diskname'] = diskname;
    queryx['disksize'] = disksize;
    queryx['diskuuid'] = diskuuid;
    queryx['vmuuid'] = vmuuid;
    queryx['buname'] = buname;
    queryx['vmname'] = vmname;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('restorestatus', 'Imaging Started');
                sb_restore_imaging(diskname, disksize, diskuuid);
            } else if (json.status == 2) {
                sb_update_statusbox('restorestatus', 'Imaging In Progress');
                $("#imagingbar .progressbarinner").html('Imaging ' + json.progress + '%').css('width', json.progress + '%');
                if (json.progress == 100) {
                    sb_update_statusbox('restorestatus', 'Imaging Complete');
                    sb_restore_run_options(diskname, diskuuid);
                } else {
                    sb_restore_imaging(diskname, disksize, diskuuid);
                }
            } else {
                sb_update_statusbox('restorestatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_restore_run_options(diskname, diskuuid){

    var setstatus = 0;
    if (sb_currenttask == '') {
        sb_update_statusbox('restorestatus', 'Checking Additional Tasks Selected');
        setstatus = 1;
    }

    var option_fixgrub = $("#option_fixgrub").val();
    var option_fixswap = $("#option_fixswap").val();

    if (typeof sb_completedtasks['fix_grub'] !== 'undefined'){
        option_fixgrub = 0;
    }

    if (typeof sb_completedtasks['fix_swap'] !== 'undefined'){
        option_fixswap = 0;
    }

    var runtask = 0;

    if (option_fixgrub == 1){
        runtask = 1;
        sb_currenttask = 'fix_grub';
    } else if (option_fixswap == 1){
        runtask = 1;
        sb_currenttask = 'fix_swap';
    }

    if (runtask == 0 && sb_currenttask == ''){
        sb_update_statusbox('restorestatus', 'All Optional Tasks Completed');
        sb_vm_create(diskname, diskuuid);
    } else if (runtask == 1){
        var queryx = {};
        queryx['comm'] = sb_currenttask;
        queryx['setstatus'] = setstatus;
        $.ajax({
            type: "GET",
            url: "index.php",
            data: queryx,
            dataType: 'json',
            success: function (json) {
                if (json.status == 0) {
                    sb_restore_run_options(diskname, diskuuid);
                } else if (json.status == 1) {
                    sb_update_statusbox('restorestatus', 'Running: ' + sb_currenttask + ' Please Wait...');
                    sb_restore_run_options(diskname, diskuuid);
                } else if (json.status == 2) {
                    sb_update_statusbox('restorestatus', 'Completed: ' + sb_currenttask + '');
                    sb_completedtasks[sb_currenttask] = 1;
                    if (sb_currenttask == 'fix_swap'){
                        sb_alerttext += '\n\nSwap Repair Configured. \n\nYou will have to run:\n\n/root/fixswap1.sh *will auto restart*\n\n/root/fixswap2.sh\n\non the new VM once you start it.';
                    }
                    sb_currenttask = '';
                    sb_restore_run_options(diskname, diskuuid);
                }
            }
        });
    }

}

function sb_vm_create(diskname, diskuuid) {

    var queryx = {};
    queryx['comm'] = 'restore_vm_create';
    queryx['diskname'] = diskname;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 0) {
                sb_update_statusbox('restorestatus', 'Failed - ' + json.reason);
            } else if (json.status == 1) {
                sb_update_statusbox('restorestatus', 'VM Created');
                sb_disk_detatch('this', diskuuid);
                setTimeout(sb_disk_attach(json.newvmuuid, diskuuid), 2000);
                setTimeout(alert('The VM should now be restored, You will have to manually set the memory, NICs, CPUs etc to make sure all is as required.' + sb_alerttext), 5000);
                sb_update_statusbox('restorestatus', 'VM Restore Completed');
            }
        }
    });
}

function sb_disk_detatch(vmuuid, diskuuid) {
    var queryx = {};
    queryx['comm'] = 'disk_detatch';
    queryx['vmuuid'] = vmuuid;
    queryx['diskuuid'] = diskuuid;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 0) {
                sb_update_statusbox('restorestatus', 'Failed - ' + json.reason);
            } else if (json.status == 1) {
                sb_update_statusbox('restorestatus', 'Disk Detatched');
            }
        }
    });
}

function sb_disk_attach(vmuuid, diskuuid) {
    var queryx = {};
    queryx['comm'] = 'disk_attach';
    queryx['vmuuid'] = vmuuid;
    queryx['diskuuid'] = diskuuid;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 0) {
                sb_update_statusbox('restorestatus', 'Failed - ' + json.reason);
            } else if (json.status == 1) {
                sb_update_statusbox('restorestatus', 'Disk Attached');
            }
        }
    });
}


function sb_vm_option(vmuuid, option, ovalue) {
    var queryx = {};
    queryx['comm'] = 'vm_update_settings';
    queryx['vmuuid'] = vmuuid;
    queryx['option'] = option;
    queryx['ovalue'] = ovalue;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 0) {
                sb_update_statusbox('restorestatus', 'Failed - ' + json.reason);
            } else if (json.status == 1) {
                sb_update_statusbox('restorestatus', 'Option ' + option + ' Set to ' + ovalue);
            }
        }
    });
}


function sb_migrateXenStart() {

    var newvmname = $("#restorenewname").val();
    var disksize = $("#disksize").val();
    var newvmnamecheck = newvmname.replace(/[0-9a-zA-Z\-_]/g, "");
    if (newvmname == '') {
        alert('You must enter a New VM Name to restore this image to.')
    } else if (newvmnamecheck != '') {
        alert('You must enter a New VM Name that does not contain anything other than a-zA-Z0-9-_')
    } else {
        if (confirm('Make sure you understand this will shutdown the original VM on Xen as listed in the Migration Process.\n\nMigrate this VM Now?')) {
            $(".gobutton").css('display', 'none');
            sb_xen_start_migrate();
        }
    }
}


function sb_xen_start_migrate() {
    sb_xen_migrate_progress = 1;
    sb_xen_shutdown(0);
}


function sb_xen_shutdown(xstatus) {
    if (sb_xen_migrate_progress == 1) {
        var xenuuid = $("#xenuuid").val();
        var vmname = 'Xen VM';

    } else {
        var xenuuid = '-migrate-';
        var vmname = 'Xen BackupVM';
    }

    sb_update_statusbox('restorestatus', 'Shutting Down ' + vmname);
    $("#xenbar").css('display', 'block');
    var queryx = {};
    queryx['comm'] = 'xen_shutdown';
    queryx['xenuuid'] = xenuuid;
    queryx['xstatus'] = xstatus;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('restorestatus', vmname + ' Shutting Down');
                $("#xenbar .progressbarinner").html('Shutting Down 25%').css('width', '25%');
                sb_xen_shutdown(1);
            } else if (json.status == 2) {
                sb_update_statusbox('restorestatus', vmname + ' Shutting Down - Waiting');
                $("#xenbar .progressbarinner").html('Shutting Down 50%').css('width', '50%');
                sb_xen_shutdown(1);
            } else if (json.status == 3) {
                sb_update_statusbox('restorestatus', vmname + ' Shutdown - Completed');
                $("#xenbar .progressbarinner").html('Shutting Down 100%').css('width', '100%');
                if (sb_xen_migrate_progress == 1) {
                    sb_xen_removedisk(0);
                } else if (sb_xen_migrate_progress == 2) {
                    sb_xen_attachdisk(0);
                } else if (sb_xen_migrate_progress == 3) {
                    sb_xen_removedisk(0);
                }
            } else {
                sb_update_statusbox('restorestatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_xen_removedisk(xstatus) {
    if (sb_xen_migrate_progress < 3) {
        sb_xen_migrate_progress = 2;
        var vbd_uuid = $("#vbd_uuid").val();

    } else {
        var vbd_uuid = '-migrate-';
    }

    $("#xenbar .progressbarinner").html('').css('width', '0%');
    sb_update_statusbox('restorestatus', 'Disconnecting Disk from VM');
    $("#xenbar").css('display', 'block');
    var queryx = {};
    queryx['comm'] = 'xen_remove_vbd';
    queryx['vbd_uuid'] = vbd_uuid;
    queryx['xstatus'] = xstatus;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('restorestatus', 'Xen VM Removing Disk');
                $("#xenbar .progressbarinner").html('Removing Disk 25%').css('width', '25%');
                sb_xen_removedisk(1);
            } else if (json.status == 2) {
                sb_update_statusbox('restorestatus', 'Xen VM Removing Disk - Waiting');
                $("#xenbar .progressbarinner").html('Removing Disk 50%').css('width', '50%');
                sb_xen_removedisk(1);
            } else if (json.status == 3) {
                sb_update_statusbox('restorestatus', 'Xen VM Disk Removed');
                $("#xenbar .progressbarinner").html('Removing Disk 100%').css('width', '100%');
                if (sb_xen_migrate_progress < 3) {
                    sb_xen_shutdown(0);
                } else {
                    sb_xen_attachdisk(0);
                }

            } else {
                sb_update_statusbox('restorestatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_xen_attachdisk(xstatus) {

    var vdi_uuid = $("#vdi_uuid").val();
    $("#xenbar .progressbarinner").html('').css('width', '0%');
    if (sb_xen_migrate_progress < 3) {
        sb_update_statusbox('restorestatus', 'Attaching Disk to BackupVM on Xen');
    } else {
        sb_update_statusbox('restorestatus', 'Re-Attaching Disk to VM on Xen');
    }
    $("#xenbar").css('display', 'block');
    var queryx = {};
    queryx['comm'] = 'xen_add_vbd';
    queryx['vdi_uuid'] = vdi_uuid;
    if (sb_xen_migrate_progress == 3) {
        var xenuuid = $("#xenuuid").val();
        queryx['xenuuid'] = xenuuid;
    }
    queryx['xstatus'] = xstatus;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('restorestatus', 'Xen VM Attaching Disk');
                $("#xenbar .progressbarinner").html('Attaching Disk 25%').css('width', '25%');
                sb_xen_attachdisk(1);
            } else if (json.status == 2) {
                sb_update_statusbox('restorestatus', 'Xen VM Attaching Disk - Waiting');
                $("#xenbar .progressbarinner").html('Attaching Disk 50%').css('width', '50%');
                sb_xen_attachdisk(1);
            } else if (json.status == 3) {
                sb_update_statusbox('restorestatus', 'Xen VM Disk Attached');
                $("#xenbar .progressbarinner").html('Attaching Disk 100%').css('width', '100%');
                if (sb_xen_migrate_progress < 3) {
                    sb_xen_startup(0);
                } else {
                    var option_restartxenyn = $("#option_restartxenyn").val();
                    if (option_restartxenyn == 1) {
                        sb_xen_startup(0);
                    } else {
                        sb_xen_launch_restore();
                    }
                }
            } else {
                sb_update_statusbox('restorestatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_xen_startup(xstatus) {
    if (sb_xen_migrate_progress == 2) {
        var xenuuid = '-migrate-';
        var vmname = 'Xen BackupVM';
    } else {
        var xenuuid = $("#xenuuid").val();
        var vmname = 'Xen VM';
    }

    sb_update_statusbox('restorestatus', 'Starting ' + vmname);
    $("#xenbar").css('display', 'block');
    var queryx = {};
    queryx['comm'] = 'xen_start';
    queryx['xenuuid'] = xenuuid;
    queryx['xstatus'] = xstatus;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('restorestatus', vmname + ' Starting');
                $("#xenbar .progressbarinner").html('Starting 25%').css('width', '25%');
                sb_xen_startup(1);
            } else if (json.status == 2) {
                sb_update_statusbox('restorestatus', vmname + ' Shutting Down - Waiting');
                $("#xenbar .progressbarinner").html('Starting 50%').css('width', '50%');
                sb_xen_startup(1);
            } else if (json.status == 3) {
                sb_update_statusbox('restorestatus', vmname + ' Starting - Completed');
                $("#xenbar .progressbarinner").html('Starting 100%').css('width', '100%');
                if (sb_xen_migrate_progress == 2) {
                    sb_xen_imagedisk(0);
                } else if (sb_xen_migrate_progress == 3) {
                    sb_xen_launch_restore();
                }
            } else {
                sb_update_statusbox('restorestatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_xen_imagedisk() {
    sb_update_statusbox('restorestatus', 'Imaging Xen Disk');
    $("#xenimagingbar").css('display', 'block');
    var queryx = {};
    queryx['comm'] = 'xen_imaging';
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('restorestatus', 'Imaging Xen Started');
                sb_xen_imagedisk();
            } else if (json.status == 2) {
                sb_update_statusbox('restorestatus', 'Imaging Xen In Progress');
                $("#xenimagingbar .progressbarinner").html('Imaging Xen VM ' + json.progress + '%').css('width', json.progress + '%');
                if (json.progress == 100) {
                    sb_update_statusbox('restorestatus', 'Imaging Xen Complete');
                    sb_xen_migrate_progress = 3;
                    sb_xen_shutdown(0);
                } else {
                    sb_xen_imagedisk();
                }
            } else {
                sb_update_statusbox('restorestatus', 'Failed: ' + json.reason);
                sb_xen_imagedisk();
            }
        }
    });
}

function sb_xen_launch_restore() {
    checkRestoreNow(0);
}


