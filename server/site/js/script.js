var sb_completedtasks = 0;
var sb_newdiskcreateprogress = 0;
var sb_completedtasks = [];
var sb_currenttask = '';
var sb_alerttext = '';
var sb_xen_migrate_progress = 0;
var sb_restore_area = 0;
var sb_newsnapshot = 0;
var sb_imaging_numberofdisks = 0;

//used to monitor changes to forms
var formwatchsomethingChanged = false;

$(window).bind('beforeunload', function (e) {
    if (formwatchsomethingChanged) {
        return true;
    }
    e = null;
});

function sb_update_statusbox(domid, statustext) {
    if (statustext.toLowerCase().indexOf('complete') != -1) {
        formwatchsomethingChanged = false;
        $("#" + domid).html('(' + statustext + ')');
    } else {
        $("#" + domid).html('<i class="fa fa-refresh fa-spin" style="font-size:14px; margin-right: 10px;"></i> ' + statustext);
        formwatchsomethingChanged = true;
    }
}

function sb_check_snapshot(vmuuid) {
    var queryx = {};
    queryx['comm'] = 'snapshot_status';
    queryx['vmuuid'] = vmuuid;
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            sb_newsnapshot = 0;
            if (json.status == 0 || json.status == -1) {
                sb_check_snapshot_progress(vmuuid);
                sb_update_statusbox('backupstatus', 'Creating Snapshot');
            } else {
                $("#snapshotbar .progressbarinner").html('Snapshot 100% (COMPLETED)').css('width', '100%');
                sb_update_statusbox('backupstatus', 'Snapshot Created');
                sb_create_backup_directories();
            }
        }
    });
}

function sb_check_snapshot_progress(vmuuid, progress) {
    $("#snapshotbar").css('display', 'block');
    if (progress == 0) sb_completedtasks = 10;
    sb_completedtasks++;
    // alert(sb_completedtasks);
    sb_check_snapshot(vmuuid);
    if (sb_completedtasks <= 100) {
        $("#snapshotbar .progressbarinner").html('Snapshot ' + sb_completedtasks + '%').css('width', sb_completedtasks + '%');
    } else {
        $("#snapshotbar .progressbarinner").html('Snapshot 100% (WAITING)');
    }
}

function sb_create_backup_directories() {
    sb_update_statusbox('backupstatus', 'Creating Backup Directories');
    var queryx = {};
    queryx['comm'] = 'backup_create_path';
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('backupstatus', 'Backup Directories Created');
                sb_snapshot_attach();
            } else {
                sb_update_statusbox('backupstatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_snapshot_attach() {
    sb_update_statusbox('backupstatus', 'Attaching Snapshot To Backup Appliance For Imaging');
    var queryx = {};
    queryx['comm'] = 'backup_attach_image';
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('backupstatus', 'Snapshot Attached');
                sb_snapshot_imaging();
            } else {
                sb_update_statusbox('backupstatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_snapshot_imaging() {
    sb_update_statusbox('backupstatus', 'Imaging Disk(s)');
    $("#imagingbar").css('display', 'block');
    var queryx = {};
    queryx['comm'] = 'backup_imaging';
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('backupstatus', 'Imaging Started - Disk ' + json.thisdisk);
                sb_snapshot_imaging();
            } else if (json.status == 2) {
                sb_update_statusbox('backupstatus', 'Imaging In Progress - Disk ' + json.thisdisk);
                $("#imagingbar .progressbarinner").html('Imaging ' + json.progress + '% - Disk ' + +json.thisdisk).css('width', json.progress + '%');
                sb_snapshot_imaging();
            } else if (json.status == 3) {
                sb_update_statusbox('backupstatus', 'Imaging Complete - Disk ' + json.thisdisk);
                sb_snapshot_detatch();
            } else {
                sb_update_statusbox('backupstatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_snapshot_detatch() {
    sb_update_statusbox('backupstatus', 'Detaching Snapshot From Backup Appliance');
    var queryx = {};
    queryx['comm'] = 'backup_detatch_image';
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('backupstatus', 'Snapshot Detached');
                sb_snapshot_delete();
            } else {
                sb_update_statusbox('backupstatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_snapshot_delete() {
    sb_update_statusbox('backupstatus', 'Detaching Snapshot From Backup Appliance');
    var queryx = {};
    queryx['comm'] = 'snapshot_delete';
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('backupstatus', 'Backup Completed');
                alert('Backup Completed!');
            } else {
                sb_update_statusbox('backupstatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function checkRestoreNow(askforokyn) {

    var newvmname = $("#restorenewname").val();
    var newvmnamecheck = newvmname.replace(/[0-9a-zA-Z\-_]/g, "");
    if (newvmname == '') {
        alert('You must enter a New VM Name to restore this image to.')
    } else if (newvmnamecheck != '') {
        alert('You must enter a New VM Name that does not contain anything other than a-zA-Z0-9-_')
    } else {
        if (askforokyn == 1) {
            if (confirm('Restore this VM Now?')) {
                $(".gobutton").css('display', 'none');
                sb_check_disk_progress(newvmname);
            }
        } else {
            $(".gobutton").css('display', 'none');
            sb_check_disk_progress(newvmname);
        }
    }
}


function sb_restore_disk_create() {
    var newvmname = $("#restorenewname").val();
    var vmuuid = $("#vmuuid").val();
    var vmname = $("#vmname").val();
    var buname = $("#buname").val();
    var option_fixgrub = $("#option_fixgrub").val();
    var option_fixswap = $("#option_fixswap").val();
    var fixestext = '';
    if (option_fixgrub == 1) fixestext = fixestext + 'fixgrub';
    if (option_fixswap == 1) fixestext = fixestext + 'fixswap';
    var os = $("#os").val();
    var console = $("#console").val();
    var memory = $("#memory").val();
    var memory_max = $("#memory_max").val();
    var nic1 = $("#nic1").val();
    var cpu_sockets = $("#sockets").val();
    var cpu_cores = $("#cores").val();
    var cpu_threads = $("#threads").val();
    var cluster = $("#cluster").val();
    var domain = $("#domain").val();
    var vmtype = $("#vmtype").val();

    var queryx = {};
    queryx['comm'] = 'restore_disk_create';
    queryx['newvmname'] = newvmname;
    queryx['vmuuid'] = vmuuid;
    queryx['vmname'] = vmname;
    queryx['buname'] = buname;
    queryx['fixestext'] = fixestext;
    queryx['os'] = os;
    queryx['console'] = console;
    queryx['memory'] = memory;
    queryx['memory_max'] = memory_max;
    queryx['nic1'] = nic1;
    queryx['cpu_sockets'] = cpu_sockets;
    queryx['cpu_cores'] = cpu_cores;
    queryx['cpu_threads'] = cpu_threads;
    queryx['cluster'] = cluster;
    queryx['domain'] = domain;
    queryx['vmtype'] = vmtype;
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
                sb_check_disk_progress();
                sb_update_statusbox('restorestatus', 'Creating Disk(s)');
            } else if (json.status == 2) {
                sb_check_disk_progress();
                sb_update_statusbox('restorestatus', 'Waiting for Disk(s)');
            } else if (json.status == 3) {
                $("#creatediskstatus .progressbarinner").html('Disk Create (COMPLETED)').css('width', '100%');
                sb_update_statusbox('restorestatus', 'Disk(s) Ready');
                sb_restore_imaging();
            }

        }
    });
}

function sb_check_disk_progress(diskname, disksize, progress) {
    $("#creatediskstatus").css('display', 'block');
    if (progress == 0) sb_newdiskcreateprogress = 0;
    sb_restore_disk_create();
    if (sb_newdiskcreateprogress <= 100) {
        $("#creatediskstatus .progressbarinner").html('Creating Disk(s) ' + sb_newdiskcreateprogress + '%').css('width', sb_newdiskcreateprogress + '%');
    } else {
        $("#creatediskstatus .progressbarinner").html('Creating Disk(s) 100% (WAITING)');
    }
}

function sb_restore_imaging() {
    sb_update_statusbox('restorestatus', 'Imaging Disk(s)');
    $("#imagingbar").css('display', 'block');
    var queryx = {};
    queryx['comm'] = 'restore_imaging';
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_imaging_numberofdisks = json.numberofdisks;
                sb_update_statusbox('restorestatus', 'Imaging Started - Disk ' + json.thisdisk);
                sb_restore_imaging();
            } else if (json.status == 2) {
                sb_update_statusbox('restorestatus', 'Imaging In Progress - Disk ' + json.thisdisk);
                if (json.progress == 0) {
                    $("#imagingbar .progressbarinner").html('Imaging ' + json.progress + '% ').css('width', json.progress + '%');
                } else {
                    $("#imagingbar .progressbarinner").html('Imaging ' + json.progress + '% - Disk ' + json.thisdisk).css('width', json.progress + '%');
                }
                sb_restore_imaging();
            } else if (json.status == 3) {
                if (json.numberofimages == json.thisdisk){
                    sb_update_statusbox('restorestatus', 'Imaging Complete - All Disks');
                }else {
                    sb_update_statusbox('restorestatus', 'Imaging Complete - Disk ' + json.thisdisk);
                }
                sb_restore_run_options();
            } else {
                sb_update_statusbox('restorestatus', 'Failed: ' + json.reason);
            }
        }
    });
}

function sb_restore_run_options() {

    var setstatus = 0;
    if (sb_currenttask == '') {
        sb_update_statusbox('restorestatus', 'Checking Additional Tasks Selected');
        setstatus = 1;
    }

    var option_fixgrub = $("#option_fixgrub").val();
    var option_fixswap = $("#option_fixswap").val();

    if (typeof sb_completedtasks['fix_grub'] !== 'undefined') {
        option_fixgrub = 0;
    }

    if (typeof sb_completedtasks['fix_swap'] !== 'undefined') {
        option_fixswap = 0;
    }

    var runtask = 0;

    if (option_fixgrub == 1) {
        runtask = 1;
        sb_currenttask = 'fix_grub';
    } else if (option_fixswap == 1) {
        runtask = 1;
        sb_currenttask = 'fix_swap';
    }

    if (runtask == 0 && sb_currenttask == '') {
        sb_update_statusbox('restorestatus', 'All Optional Tasks Completed');
        sb_vm_create();
    } else if (runtask == 1) {
        var queryx = {};
        queryx['comm'] = sb_currenttask;
        $.ajax({
            type: "GET",
            url: "index.php",
            data: queryx,
            dataType: 'json',
            success: function (json) {
                if (json.status == 0) {
                    sb_restore_run_options();
                } else if (json.status == 1) {
                    sb_update_statusbox('restorestatus', 'Running: ' + sb_currenttask + ' Please Wait...');
                    sb_restore_run_options();
                } else if (json.status == 2) {
                    sb_update_statusbox('restorestatus', 'Completed: ' + sb_currenttask + '');
                    sb_completedtasks[sb_currenttask] = 1;
                    if (sb_currenttask == 'fix_swap') {
                        sb_alerttext += '\n\nSwap Repair Configured. \n\nYou will have to run:\n\n/root/fixswap1.sh *will auto restart*\n\n/root/fixswap2.sh\n\non the new VM once you start it.';
                    }
                    sb_currenttask = '';
                    sb_restore_run_options();
                }
            }
        });
    }

}

function sb_vm_create() {

    var queryx = {};
    queryx['comm'] = 'restore_vm_create';
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
                sb_disk_detatch();
            }
        }
    });
}

function sb_disk_detatch(vmuuid, diskuuid) {
    var queryx = {};
    queryx['comm'] = 'disk_detatch';
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
                setTimeout(sb_disk_attach(), 3000);
                sb_update_statusbox('restorestatus', 'VM Restore Completed');
                setTimeout(alert('The VM should now be restored, You will have to manually confirm the memory, NICs, CPUs etc to make sure all is as required.' + sb_alerttext), 5000);

            }
        }
    });
}

function sb_disk_attach() {
    var queryx = {};
    queryx['comm'] = 'disk_attach';
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 0) {
                sb_update_statusbox('restorestatus', 'Failed - ' + json.reason);
            } else if (json.status == 1) {
                if (sb_restore_area == 0) {
                    sb_update_statusbox('restorestatus', 'Disk Attached Completed');
                } else {
                    sb_update_statusbox('restorestatus', 'Disk Attached & VM Completed');
                }

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
    }

    $("#xenbar .progressbarinner").html('').css('width', '0%');
    sb_update_statusbox('restorestatus', 'Disconnecting Disk from VM');
    $("#xenbar").css('display', 'block');
    var queryx = {};
    queryx['comm'] = 'xen_remove_vbd';
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('restorestatus', 'Xen VM Removing Disk(s)');
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
                sb_xen_removedisk(1);
            }
        }
    });
}

function sb_xen_attachdisk(xstatus) {

    $("#xenbar .progressbarinner").html('').css('width', '0%');
    if (sb_xen_migrate_progress < 3) {
        sb_update_statusbox('restorestatus', 'Attaching Disk to BackupVM on Xen');
    } else {
        sb_update_statusbox('restorestatus', 'Re-Attaching Disk to VM on Xen');
    }
    $("#xenbar").css('display', 'block');
    var queryx = {};
    queryx['comm'] = 'xen_add_vbd';
    $.ajax({
        type: "GET",
        url: "index.php",
        data: queryx,
        dataType: 'json',
        success: function (json) {
            if (json.status == 1) {
                sb_update_statusbox('restorestatus', 'Xen VM Attaching Disk(s)');
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
    sb_update_statusbox('restorestatus', 'Imaging Xen Disk(s)');
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
                sb_imaging_numberofdisks = json.numberofdisks;
                sb_update_statusbox('restorestatus', 'Imaging Xen Started - ' + sb_imaging_numberofdisks + ' Disk(s)');
                sb_xen_imagedisk();
            } else if (json.status == 2) {
                sb_update_statusbox('restorestatus', 'Imaging Xen In Progress - Disk ' + json.thisdisk);
                $("#xenimagingbar .progressbarinner").html('Imaging Xen VM ' + json.progress + '% - Disk ' + json.thisdisk).css('width', json.progress + '%');
                sb_xen_imagedisk();
            } else if (json.status == 3) {
                $("#xenimagingbar .progressbarinner").html('Imaging Xen VM 100% - Disk ' + json.thisdisk).css('width', '100%');
                sb_update_statusbox('restorestatus', 'Imaging Xen Complete - Disk ' + json.thisdisk);
                sb_xen_migrate_progress = 3;
                sb_xen_shutdown(0);
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


