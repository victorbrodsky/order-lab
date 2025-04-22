#!/bin/bash

#Install sshkey for Alma, Centos

###
#"echo @### Copy public key to authorized_keys: cat ~/.ssh/sshkey.pub >> ~/.ssh/authorized_keys ###",
#"cat ~/.ssh/sshkey.pub >> ~/.ssh/authorized_keys",
#"chmod 700 /root/.ssh",
#"chmod 600 /root/.ssh/authorized_keys",
#"echo 'PubkeyAuthentication yes' >> /etc/ssh/sshd_config",
#"echo 'PasswordAuthentication yes' >> /etc/ssh/sshd_config",
#"echo @### Restart ssh ###",
#"sudo systemctl restart sshd"
###


COLOR='\033[1;36m'
NC='\033[0m' # No Color

if [ -z "$sshfingerprint" ]
  then 	
    sshfingerprint=$1
fi

echo "sshfingerprint=$sshfingerprint"

if [ ! -z "$sshfingerprint" ]
	then
	  echo -e ${COLOR} SSH fingerprint exists: $sshfingerprint ${NC}
		echo -e ${COLOR} Copy public key to authorized_keys: cat ~/.ssh/sshkey.pub >> ~/.ssh/authorized_keys ${NC}
		cat ~/.ssh/sshkey.pub >> ~/.ssh/authorized_keys
		chmod 700 /root/.sshs
		chmod 600 /root/.ssh/authorized_keys
		echo 'PubkeyAuthentication yes' >> /etc/ssh/sshd_config
		echo 'PasswordAuthentication yes' >> /etc/ssh/sshd_config
		echo -e ${COLOR} Restart ssh ${NC}
		sudo systemctl restart sshd	
	else
		echo "File not found!"
		echo -e ${COLOR} File sshkey.pub not found: skip public key authorization settings ${NC}
fi	



