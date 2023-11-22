#!/bin/bash

COLOR='\033[1;36m'
NC='\033[0m' # No Color

apitoken=$1
echo apitoken=$apitoken

snapshot_name=$2
echo snapshot_name=$snapshot_name

#ssh-keygen -t rsa -b 4096 -N "" -f ./sshkey
#testpar="MYTESTPAR"
#echo -e "1234567890\n" doctl compute ssh packer-1698358964 --ssh-key-path 'C:\Users\ch3\.ssh\id_rsa_2' --ssh-command "bash /usr/local/bin/order-lab/packer/install-certbot.sh $testpar"

#echo "Exit test.sh"
#exit 0

IMAGENAME="packer-1700687549"
domainname="tincry.com"
sslcertificate="installcertbot"
email="cinava@yahoo.com"
#echo -e "\n" doctl compute ssh $IMAGENAME --ssh-key-path ./sshkey  --ssh-command "whoami" #"bash /usr/local/bin/order-lab/packer/install-certbot.sh $domainname $sslcertificate $email"
#echo | doctl compute ssh $IMAGENAME --ssh-key-path ./sshkey  --ssh-command "bash /usr/local/bin/order-lab/packer/install-certbot.sh $domainname $sslcertificate $email"
#exit

if [ "$sslcertificate" = "installcertbot" ] && [ -n "$domainname" ] && [ -n "$email" ]
  then
    echo -e ${COLOR} Run bash script install-certbot.sh via ssh. IMAGENAME="$IMAGENAME", domainname="$domainname", sslcertificate="$sslcertificate", email="$email" ${NC}
    #echo | doctl ... - press enter
	echo | doctl compute ssh "$IMAGENAME" --ssh-key-path ./sshkey --ssh-command 'ls -a'
    #echo | doctl compute ssh "$IMAGENAME" --ssh-key-path ./sshkey --ssh-command 'bash /usr/local/bin/order-lab/packer/install-certbot.sh $domainname $sslcertificate $email'
  else
    echo -e ${COLOR} Skip certbot installation ${NC}
fi

echo "Exit test.sh"
exit 0

echo "*** Doctl must be installed! https://www.digitalocean.com/docs/apis-clis/doctl/how-to/install/ ***"
echo "" | doctl auth init --access-token $apitoken #echo "" simulate enter pressed
LASTLINE=$(doctl compute droplet list --format="Public IPv4" | tail -1)
lastlinevars=( $LASTLINE )
DROPLETIP=${lastlinevars[0]}
echo "droplet IP=[$DROPLETIP]"

LASTLINE=$(doctl compute snapshot list | grep "$snapshot_name")
echo "LASTLINE=$LASTLINE"

LASTLINEINFO=( $LASTLINE )

echo -e ${COLOR} *** Getting the first IMAGEID and the second IMAGENAME elements from LASTLINE *** ${NC}
IMAGEID="${LASTLINEINFO[0]}"
IMAGENAME="${LASTLINEINFO[1]}"
#echo -e ${COLOR} IMAGEID="$IMAGEID", IMAGENAME="$IMAGENAME" ${NC}
echo IMAGEID="$IMAGEID", IMAGENAME="$IMAGENAME"