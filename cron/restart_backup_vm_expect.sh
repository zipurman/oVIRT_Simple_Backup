#!/usr/bin/expect -f
#requires packages: expect

set user "admin@internal"
set password "yourpasswordhere"
set vmtostart "SimpleBackup"
set url "https://ovirtmgmt.yourdomain.com/ovirt-engine/api"

spawn /usr/bin/ovirt-shell -l $url -u $user -I -c -D -E "action vm $vmtostart start"

expect "?assword:"
send "$password\r"
expect eof