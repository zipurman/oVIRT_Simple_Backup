# oVirtSimpleBackup - XENVM - Installer - Debian

## Instructions for using this installer

 - [x] These instructions will setup your Debian VM in your Xen Server Environment so it is ready for oVirtSimpleBackup to connect to. This VM in the Xen Server Environment will be used to help oVirtSimpleBackup to migrate your VMs from Xen to oVirt. If this is what you want, then follow the instructions below:
 
 
 1. Create a new VM in XEN called VMMIGRATE
    * INSTALL DEBIAN 9 using the following settings:
        * NAME: VMMIGRATE
        * RAM: >=4GB
        * DISK: 8GB using automated partitioning
        * MAKE SURE YOU INSTALL USING "US ENGLISH" FOR LANGUAGE AS SOME SCRIPTING MAY NOT WORK IF USING OTHER LANGUAGES
        * MINIMAL INSTALL WITH SSH SERVER
        
    * ONCE XEN VMMIGRATE VM INSTALLED
        * Login as root and run the following commands:
        
            ``sed -i '2,5 s/^/#/' /etc/apt/sources.list``
            
            ``apt-get update``
     
            ``apt-get install pv lzop gzip fsarchiver chroot wget``
    
            ``sed -i "s/PermitRootLogin without-password/#PermitRootLogin without-password/g" /etc/ssh/sshd_config``
            
            ``echo "PermitRootLogin yes" >> /etc/ssh/sshd_config``
            
            ``echo "UseDNS no" >> /etc/ssh/sshd_config``
            
            ``service ssh restart``
            
            ``cd /root && mkdir .ssh && chmod 700 .ssh``
            
            ``mkdir /mnt/migrate``
            
            ``vi /etc/fstab`` and add the following line changing the ip and path
            
                192.168.1.50:/nfspath/to/migrate/share /mnt/migrate nfs4 rw,async,hard,intr,noexec 0 0
                
            ``mount /mnt/migrate && chmod 777 /mnt/migrate/``
            
 2. ON ONE OF YOUR XEN HOSTS
        
        One of your Xen Server Hosts will need to be part of the migration as well. oVirtSimpleBackup will call from oVirt over to the selected Xen Server Host to initiate commands for moving disks and start/stopping VMs. For this reason, you must do the following on a selected Xen Server Host to allow oVirtSimpleBackup to remotely run the commands:
        
        ``mkdir /root/.ssh``
        
        ``chmod 700 /root/.ssh``
        
        ``echo "UseDNS no" >> /etc/ssh/sshd_config``
        
        ``service sshd restart``
        
    After the above is completed, you can [Proceed to the next step](https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/server/installer/README.md)