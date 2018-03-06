<?php

	sb_pagetitle( 'XenServer(Citrix) Migrate' );
	$howto = varcheck( "howto", 0, "FILTER_VALIDATE_INT", 0 );

	if ( $howto == 1 ) {

		?>
        <h3>Steps to setting up Xen Migration and backup Scripts</h3>
        <ol>
            <li>
                On one of your Xen Hosts to allow remote ssh for xe commands
                <ol>
                    <li>As Root:
                        <ol>
                            <li>
                                mkdir /root/.ssh
                            </li>
                            <li>
                                chmod 700 /root/.ssh
                            </li>
                        </ol>
                    </li>
                </ol>
            </li>
            <li>On Xen Server
                <ol>
                    <li>Create a Debian Linux VM named VMMIGRATE
                        <ol>
                            <li>Install the following packages:
                                <ol>
                                    <li>pv</li>
                                    <li>dialog (only if using bash script)</li>
                                    <li>fsarchiver</li>
                                    <li>chroot</li>
                                    <li>wget</li>
                                </ol>
                                </ul></li>
                            <li>
                                As root:
                                <ol>
                                    <li>
                                        vi /etc/ssh/sshd_config
                                        <ul>
                                            <li>#rem out this line:<br/>#PermitRootLogin without-password</li>
                                            <li>#add this line:<br/>PermitRootLogin yes</li>
                                        </ul>
                                    </li>
                                    <li>
                                        /etc/init.d/ssh restart
                                    </li>
                                    <li>
                                        cd /root
                                    </li>
                                    <li>
                                        wget
                                        https://raw.githubusercontent.com/zipurman/oVIRT_Simple_Backup/master/xen_migrate/xen_migrate.sh
                                    </li>
                                    <li>
                                        chmod +e xen_migrate.sh
                                    </li>
                                    <li>
                                        mkdir .ssh
                                    </li>
                                    <li>
                                        chmod 700 .ssh
                                    </li>
                                    <li>
                                        mkdir /mnt/migrate
                                    </li>
                                    <li>
                                        vi /etc/fstab
                                        <ul>
                                            #add the following line with your IP and PATH info<br/>
                                            192.168.1.123:/path/to/folder/on/nfs /mnt/migrate nfs
                                            rw,async,hard,intr,noexec 0 0
                                        </ul>
                                    </li>
                                    <li>
                                        mount /mnt/migrate
                                    </li>
                                    <li>
                                        chmod 777 /mnt/migrate/
                                    </li>
                                </ol>
                            </li>
                        </ol>
                    </li>
                </ol>
            </li>
            <li>On oVirt Server
                <ol>
                    <li>
                        Create a Debian Linux VM named BackupEngine
                    </li>
                    <li>
                        Install the following packages:
                        <ol>
                            <li>pv</li>
                            <li>curl</li>
                            <li>xmlstarlet</li>
                            <li>lsscsi</li>
                            <li>dialog</li>
                            <li>exim4 (requires config of /etc/exim4/update-exim4.conf.conf & /etc/init.d/exim4
                                restart)
                            </li>
                            <li>uuid-runtime</li>
                            <li>fsarchiver</li>
                            <li>php5</li>
                            <li>php5-curl</li>
                        </ol>
                    </li>
                    <li>
                        As root:
                        <ol>
                            <li>
                                vi /etc/ssh/sshd_config
                                <ul>
                                    <li>#rem out this line:<br/>#PermitRootLogin without-password</li>
                                    <li>#add this line:<br/>PermitRootLogin yes</li>
                                </ul>
                            </li>
                            <li>
                                /etc/init.d/ssh restart
                            </li>
                            <li>mkdir /mnt/backups</li>
                            <li>mkdir /mnt/migrate</li>
                            <li>mkdir /mnt/linux</li>
                            <li>vi /etc/fstab
                                <ul>
                                    #add the following line with your IP and PATH info<br/>
                                    192.168.1.123:/path/to/folder/on/nfs /mnt/backups nfs rw,async,hard,intr,noexec 0
                                    0<br/>
                                    192.168.1.123:/path/to/folder/on/nfs /mnt/migrate nfs rw,async,hard,intr,noexec 0
                                    0<br/>
                                </ul>
                            </li>
                            <li>
                                mount /mnt/backups
                            </li>
                            <li>
                                mount /mnt/migrate
                            </li>
                            <li>
                                mkdir /root/.ssh
                            </li>
                            <li>
                                chmod 700 /root/.ssh
                            </li>
                            <li>usermod -a -G disk www-data</li>
                            <li>chown root:disk /bin/dd</li>
                            <li>chown www-data:disk /dev/vdb</li>
                            <li>a2enmod ssl</li>
                            <li>service apache2 restart</li>
                            <li>mkdir /etc/apache2/ssl</li>
                            <li>openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key
                                -out /etc/apache2/ssl/apache.crt <b>Do Not Add Pass Phrase</b></li>
                            <li>chmod 600 /etc/apache2/ssl/*</li>
                            <li>vi /etc/apache2/sites-available/default-ssl.conf<br/>
                                <ol>
                                    <li>ServerName backupengine.<b>yourdomain</b>.com:443</li>
                                    <li>DocumentRoot /var/www/html/site</li>
                                    <li>SSLCertificateFile /etc/apache2/ssl/apache.crt</li>
                                    <li>
                                        <SSLCertificateKeyFile
                                        /etc/apache2/ssl/apache.key/li>
                                </ol>
                            </li>
                            <li>a2ensite default-ssl.conf</li>
                            <li>service apache2 reload</li>
                            <li>chsh -s /bin/bash www-data</li>
                            <li>chmod 777 /mnt</li>
                            <li>chmod 777 /mnt/migrate/</li>
                            <li>chmod 777 /mnt/backup</li>
                            <li>chmod 777 /mnt/linux</li>
                            <li>mkdir /var/www/.ssh</li>
                            <li>chown www-data:www-data /var/www/.ssh</li>
                            <li>chmod 700 /var/www/.ssh</li>
                            <li>su www-data</li>
                            <li>ssh-keygen -t rsa</li>
                            <li>ssh-copy-id root@<b>ip.of.VMMIGRATE.VM</b></li>
                            <li>ssh-copy-id root@<b>ip.of.XEN.HOST</b></li>
                            <li>cd /var/www/html/</li>
                            <li><b>Download the files and folders from
                                    https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/webUI/server into this folder</b>
                            </li>
                            <li>touch /var/www/html/config.php</li>
                            <li>chown www-data:root /var/www -R</li>
                            <li>vi /var/www/html/allowed_ips.php (And change allowed IP addresses)</li>
                        </ol>
                    </li>
                </ol>
            </li>
            <li>
                on oVirtEngine VM
                <ol>
                    <li>As Root:
                        <ol>
                            <li>engine-config -s CORSSupport=true</li>
                            <li>engine-config -s CORSAllowedOrigins=*</li>
                        </ol>
                    </li>
                    <li>cd /usr/share/ovirt-engine/ui-plugins</li>
                    <li><b>Download the files and folders from
                            https://github.com/zipurman/oVIRT_Simple_Backup/tree/master/webUI/plugin into this directory.</b></li>
                    <li>vi simpleBackup.json
                        <ul>
                            <li>Change IP Address in simpleBackup.json to match your oVirt BackupEngine VM</li>
                        </ul>
                    </li>
                    <li>service ovirt-engine restart</li>
                </ol>
            </li>
            <li>
                You should now be able to login to your oVirt Web UI and see the SimpleBackup menu item on the left.
            </li>
        </ol>

		<?php

	} else {

		sb_pagedescription( 'This tool requires all pieces to be configured correctly. <a href="?area=5&howto=1">Click Here</a> for the list of what is required for a successful migration.<br/><br/><b><i>NOTE: This utility will ONLY image a single disk VM from Xen to oVirt. It DOES NOT transfer NICs, MAC Addresses, MEMORY, or ANY settings from Xen Server. Once the VM image is migrated, you can then change the require settings in oVirt before launching your newly migrated VM.</i></b>' );

		if ( empty( $action ) ) {

			sb_pagedescription( 'The following VMs are listed from Xen on ' . $settings['xen_ip'] . '' );

			sb_table_start();

			$rowdata = array(
				array(
					"text"  => "VM",
					"width" => "50%",
					x,
				),
				array(
					"text"  => "Status",
					"width" => "10%",
				),
				array(
					"text"  => "UUID",
					"width" => "40%",
				),
			);
			sb_table_heading( $rowdata );

			//is-control-domain=false (exclude servers)
			exec( 'ssh root@' . $settings['xen_ip'] . ' xe vm-list is-control-domain=false', $output );

			$rowitem = 1;
			foreach ( $output as $item ) {

				if ( ! empty( $item ) ) {
					if ( $rowitem == 1 ) {
						$uuid = str_replace( 'uuid ( RO)', '', $item );
						$uuid = preg_replace( '/[^0-9a-zA-Z\-]/i', '', $uuid );
					} else if ( $rowitem == 2 ) {
						$vmname = preg_replace( '/^.*:/i', '', $item );
					} else if ( $rowitem == 3 ) {
						$status = str_replace( 'power-state ( RO): ', '', $item );
						$status = trim( $status );
					}

					if ( $rowitem == 3 ) {

						$vmextratag = '';
						if ( $uuid == $settings['xen_migrate_uuid'] ) {
							$vmextratag .= ' <b>* Migration VM *</b>';
						}

						if ( $status == 'running' ) {
							$status = '<span class="statusup">Running</span>';
						} else {
							$status = '<span class="statusdown">' . $status . '</span>';
						}

						$rowdata = array(
							array(
								"text" => '<a href="?area=5&action=xenvmname&vmname=' . $vmname . '&xenuuid=' . $uuid . '">' . $vmname . '</a>' . $vmextratag,
							),
							array(
								"text" => $status,
							),
							array(
								"text" => $uuid,
							),
						);
						sb_table_row( $rowdata );
						$rowitem = 0;
					}
					if ( $rowitem < 3 ) {
						$rowitem ++;
					}
				}

			}

			sb_table_end();

		} else if ( $action == 'xenvmname' ) {

			$xenuuid = varcheck( "xenuuid", '' );
			$vmname  = varcheck( "vmname", '' );

			echo '<a href="?area=5">&lt;-- BACK</a><br/><br/>';

			exec( 'ssh root@' . $settings['xen_ip'] . ' xe vm-disk-list vm=' . $xenuuid, $output );

			foreach ( $output as $item ) {
				if ( strpos( $item, 'virtual-size' ) !== false ) {
					$size = preg_replace( '/^.*:/i', '', $item );
				}
				//

			}
			echo $output[10];

			//		showme( $output );

			exec( 'ssh root@' . $settings['xen_ip'] . ' xe vm-list is-control-domain=false uuid=' . $xenuuid, $output2 );
			$status = str_replace( 'power-state ( RO): ', '', $output2['2'] );
			$status = trim( $status );
			if ( $status == 'running' ) {
				$status = '<span class="statusup">Running</span>';
			} else {
				$status = '<span class="statusdown">' . $status . '</span>';
			}

			sb_table_start();

			$rowdata = array(
				array( "text" => "", "width" => "15%", ),
				array( "text" => "", "width" => "35%", ),
				array( "text" => "", "width" => "15%", ),
				array( "text" => "", "width" => "35%", ),
			);
			sb_table_heading( $rowdata );

			$rowdata = array(
				array( "text" => 'VM Name:', ),
				array( "text" => $vmname, ),
				array( "text" => 'UUID:', ),
				array( "text" => $xenuuid, ),
			);
			sb_table_row( $rowdata );

			$sizegb  = $size / 1024 / 1024 / 1024;
			$rowdata = array(
				array( "text" => 'Disk Size:', ),
				array( "text" => $sizegb . 'GB', ),
				array( "text" => 'Status:', ),
				array( "text" => $status, ),
			);
			sb_table_row( $rowdata );

			$vbd_uuid = $output['1'];
			$vdi_uuid = $output['7'];
			$vbd_uuid = preg_replace( '/^.*:/i', '', $vbd_uuid );
			$vdi_uuid = preg_replace( '/^.*:/i', '', $vdi_uuid );
			$vbd_uuid = str_replace( ' ', '', $vbd_uuid );
			$vdi_uuid = str_replace( ' ', '', $vdi_uuid );
			$rowdata  = array(
				array( "text" => 'VBD UUID:', ),
				array( "text" => $vbd_uuid, ),
				array( "text" => 'VDI UUID:', ),
				array( "text" => $vdi_uuid, ),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array( "text" => 'Re-Start Xen VM After Imaging:', ),
				array(
					"text" => sb_input( array(
						'type'  => 'select',
						'name'  => 'option_restartxenyn',
						'list'  => array(
							array( 'id' => '0', 'name' => 'No', ),
							array( 'id' => '1', 'name' => 'Yes', ),
						),
						'value' => 0,
					) ),
				),
				array( "text" => 'New VM Name:', ),
				array(
					"text" => sb_input( array(
						'type'      => 'text',
						'name'      => 'restorenewname',
						'size'      => '36',
						'maxlength' => '36',
						'value'     => '',
					) ),
				),
			);
			sb_table_row( $rowdata );

			$rowdata = array(
				array( "text" => 'Fix Grub:', ),
				array(
					"text" => sb_input( array(
						'type'  => 'select',
						'name'  => 'option_fixgrub',
						'list'  => array(
							array( 'id' => '0', 'name' => 'No', ),
							array( 'id' => '1', 'name' => 'Yes', ),
						),
						'value' => 0,
					) ),
				),
				array( "text" => 'Fix Swap:', ),
				array(
					"text" => sb_input( array(
						'type'  => 'select',
						'name'  => 'option_fixswap',
						'list'  => array(
							array( 'id' => '0', 'name' => 'No', ),
							array( 'id' => '1', 'name' => 'Yes', ),
						),
						'value' => 0,
					) ),
				),
			);
			sb_table_row( $rowdata );

			echo sb_input( array( 'type' => 'hidden', 'name' => 'disksize', 'value' => $sizegb, ) );
			echo sb_input( array( 'type' => 'hidden', 'name' => 'vmname', 'value' => 'xen.img', ) );
			echo sb_input( array( 'type' => 'hidden', 'name' => 'xenuuid', 'value' => $xenuuid, ) );
			echo sb_input( array( 'type' => 'hidden', 'name' => 'vbd_uuid', 'value' => $vbd_uuid, ) );
			echo sb_input( array( 'type' => 'hidden', 'name' => 'vdi_uuid', 'value' => $vdi_uuid, ) );
			echo sb_input( array( 'type' => 'hidden', 'name' => 'buname', 'value' => '-migrate-', ) );
			echo sb_input( array(
				'type'  => 'hidden',
				'name'  => 'vmuuid',
				'value' => $settings['uuid_backup_engine'],
			) );//not used - just for validation areas
			echo sb_input( array( 'type' => 'hidden', 'name' => 'sb_area', 'value' => 4, ) );

			sb_table_end();

			sb_pagedescription( '<b><i>Migration process. The script will do the following:</i></b>
				<ol>
				<li>Shutdown Xen VM</li>
				<li>Remove Disk From Xen VM</li>
				<li>Shutdown Xen SimpleBackup VM</li>
				<li>Attach Xen Disk to Xen SimpleBackup VM</li>
				<li>Start Xen SimpleBackup VM</li>
				<li>Image disk to ' . $settings['mount_migrate'] . '</li>
				<li>Shutdown Xen SimpleBackup VM</li>
				<li>Remove Disk From Xen SimpleBackup VM</li>
				<li>Re-Attach Xen Disk to original Xen VM</li>
				<li>Optionally Start Xen  VM</li>
				<li>Restore Disk Image to New oVirt VM</li>
				</ol>' );

			sb_gobutton( 'Migrate This Xen VM Now', '', 'sb_migrateXenStart();' );

			sb_progress_bar( 'xenbar' );
			sb_progress_bar( 'xenimagingbar' );
			sb_progress_bar( 'creatediskstatus' );
			sb_progress_bar( 'imagingbar' );

			sb_status_box( 'restorestatus' );

		}
	}