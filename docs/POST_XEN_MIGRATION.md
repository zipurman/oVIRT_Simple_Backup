# Post Migration Steps

There will be a high probability that when migrating from Citrix Xen your VM will be unbootable.

The most common issues are
  - Grub2 bootloader issues
  - Grub loads and you can select a kernel. The boot will fail because it can't find your LVM with your root partition
  - Gremlins

# Diagnosing Issues

  - If you do not see Grub2 boot loader then skip to the Fixing Grub
  - If it complains about missing /dev/mapper/$MY_LV_ROOT then skip to the Fixing Missing root LVM

# Fixing Grub
Part of the migration is the auto fixup of grub (if selected during the configration of the migration).
If Grub is not loading then follow the following guide
http://www.system-rescue-cd.org/disk-partitioning/Repairing-a-damaged-Grub/

# Fixing Missing root LVM
If Grub is working and you still can't boot. ie. there are errors complaining about missing /dev/mapper/$MY_ROOT_LVM this means that you do not have the correct driver in your initrd of your newly migrated VM.

This is the case of older Centos/Ubuntu VMs installed on Xen.

A quick way to check what sort of drivers your initrd has is when you get dropped into the initramfs/dracut shell and run
```sh
cat /proc/modules
```
If you do not see any modules that say virtio or equivalent then it means the initrd does not have the kernel module to load your new VM's disk interface.
To get your VM booting, simply change the interface from "virtio-sci" or "virtio" TO "IDE"

Reboot your VM and it should start.

# Re-enabling Virtio Interface for Disk
If you want to reenable virtio for your disk, you will have to regenerate a new initrd with virtio modules. Once that is done you can switch your disk interface back to "virtio-scsi"

Check your current initrd to see if you have virtio modules/drivers
```sh
lsinitramfs /path/to/$INITRAMFS | grep -i virtio
```
It should return nothing.

What you want to do is to
1. add virtio modules to initrd
2. regenerate a new initrd. This is distro dependant.
3. update grub to install new initrd
4. switch back to virtio-scsi
5. profit


*On some very rare occasion your migrated VM may contain a kernel that does not have any virtio modules. So you can regenerate the initrd all you want but there are no modules to add. To resolve this just install a new kernel via a rescuecd. The basic process can be adapted from here
https://askubuntu.com/questions/28099/how-to-restore-a-system-after-accidentally-removing-all-kernels*

First we backup the current initrd if it goes horribly wrong
1. Find your kernel version with. You will be using this kernel version to identify your initrd
    ```sh
    uname -r
    ```
2. make a copy of your initrd that is in /boot. The name of your initrd is distro dependant.
    ```sh
    cd /boot
    cp NAME-OF-INITRD-$KERNELVER NAME-OF-INITRD-$KERNELVER.bak
    ```
Debian, Ubuntu. Explicitly add the modules to your initrd
*Tested on Ubuntu 12.04. Will probably be the same for debian*
1. Edit /etc/initramfs/modules add modules so your file looks like this
    ```sh
    # List of modules that you want to include in your initramfs.
    # They will be loaded at boot time in the order below.
    #
    # Syntax:  module_name [args ...]
    #
    # You must run update-initramfs(8) to effect this change.
    #
    # Examples:
    #
    # raid1
    # sd_mod
    virtio
    virtio_pci
    virtio_net
    virtio_blk
    virtio_scsi
    ```
2. Regenerate the initrd for all the kernels
    ```sh
    update-initramfs -u -k all
    ```
3. Test to see if your initrd contains the new virtio modules
    ```sh
    lsinitramfs /boot/initrd.img-`uname -r` | grep -i virtio
    ```
    It should return something like this
    ```sh
    lib/modules/3.13.0-117-generic/kernel/drivers/scsi/virtio_scsi.ko
    lib/modules/3.13.0-117-generic/kernel/drivers/net/caif/caif_virtio.ko
    ```
4. Update grub to use your new initrd
    ```sh
    update-grub
    grub-install #if required
    ```
5. Reboot and switch your disk interface from "IDE" to "virtio-scsi"

For Centos/Fedora there is no initramfs-tools. We will be using mkinitrd
*Tested on Centos 7*
1. run the mkinitrd tool with the required modules
    ```sh
    mkinitrd --with virtio_pci --with virtio_blk --with virtio_scsi -f -v /boot/initramfs-`uname -r` `uname -r`
    ```
2. Check the new initrd to see if the virtio modules were added
    ```sh
    lsinitrd /boot/initramfs-`uname -r`.img | grep -i virtio
    ```
    You should see something like this
    ```sh
    Arguments: -f -v --add-drivers ' virtio virtio_pci virtio_blk virtio_scsi'
    -rw-r--r--   1 root     root        27885 Nov  3  2015 usr/lib/modules/3.10.0-229.20.1.el7.x86_64/kernel/drivers/block/virtio_blk.ko
    -rw-r--r--   1 root     root        50501 Nov  3  2015 usr/lib/modules/3.10.0-229.20.1.el7.x86_64/kernel/drivers/net/virtio_net.ko
    -rw-r--r--   1 root     root        29125 Nov  3  2015 usr/lib/modules/3.10.0-229.20.1.el7.x86_64/kernel/drivers/scsi/virtio_scsi.ko
    drwxr-xr-x   2 root     root            0 Oct 31 15:14 usr/lib/modules/3.10.0-229.20.1.el7.x86_64/kernel/drivers/virtio
    -rw-r--r--   1 root     root        15797 Nov  3  2015 usr/lib/modules/3.10.0-229.20.1.el7.x86_64/kernel/drivers/virtio/virtio.ko
    -rw-r--r--   1 root     root        21253 Nov  3  2015 usr/lib/modules/3.10.0-229.20.1.el7.x86_64/kernel/drivers/virtio/virtio_pci.ko
    -rw-r--r--   1 root     root        25541 Nov  3  2015 usr/lib/modules/3.10.0-229.20.1.el7.x86_64/kernel/drivers/virtio/virtio_ring.ko
    ```
3. Update grub to use your new initrd
    ```sh
    update-grub
    grub-install #if required
    ```
4. Reboot and switch your disk interface from "IDE" to "virtio-scsi"


-- Thanks to tane for creating this document ;)