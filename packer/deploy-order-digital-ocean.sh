#!/bin/bash
# ORDER installation script

#There are 3 files containing installation code: 
#1) This file deploy-order-digital-ocean.sh
#2) centos-install.sh
#3) order-packer-centos.json

#bash deploy-order-digital-ocean.sh 
#--token API-TOKEN-FROM-STEP-1 
#--os operational system: centos (default) or ubuntu
#-p parameters.yml 
#-dbuser - optional (default symfony)
#-dbpass - optional (default symfony)
#-protocol - optional (default http)
#--domainname domain_name.tld - optional
#--sslcertificate ssl_certificate.crt - optional
#--sslprivatekey intermediate_certificate.ca-crt
#--email - optional if sslcertificate=installcertbot
#--multitenant - false/haproxy (default false)

#os - centos or ubuntu
#apitoken=$1
#parameters=$2
#dbuser=$3
#dbpass=$4

#protocol=$5
#domainname=$6
#sslcertificate=$7
#sslprivatekey=$8



#Available images: https://do-community.github.io/available-images/


#$ bash deploy_test.sh --token apitoken --os centos --parameters parameters.yml --os alma9 --dbuser symfony --dbpass symfony --protocol http --domainname domainname --sslcertificate localhost.crt --sslprivatekey localhost.key
#$ bash deploy_test.sh --token apitoken --os centos --parameters parameters.yml --os alma9 --dbuser symfony --dbpass symfony --protocol https --domainname domainname --sslcertificate installcertbot --email email@example.com

COLOR='\033[1;36m'
NC='\033[0m' # No Color

POSITIONAL=()
while [[ $# -gt 0 ]]
do
key="$1"

case $key in
    -t|--token)
		apitoken="$2"
		shift # past argument
		shift # past value
    ;;
	-o|--os)
		os="$2"
		shift # past argument
		shift # past value
    ;;
    -s|--parameters)
		parameters="$2"
		shift # past argument
		shift # past value
    ;;
    -u|--dbuser)
		dbuser="$2"
		shift # past argument
		shift # past value
    ;;
	-p|--dbpass)
		dbpass="$2"
		shift # past argument
		shift # past value
    ;;
	-l|--protocol)
		protocol="$2"
		shift # past argument
		shift # past value
    ;;
	-d|--domainname)
		domainname="$2"
		shift # past argument
		shift # past value
    ;;
  -e|--email)
		email="$2"
		shift # past argument
		shift # past value
    ;;
	-c|--sslcertificate)
		sslcertificate="$2"
		shift # past argument
		shift # past value
    ;;
	-k|--sslprivatekey)
		sslprivatekey="$2"
		shift # past argument
		shift # past value
    ;;
  -f|--sshfingerprint)
		sshfingerprint="$2"
		shift # past argument
		shift # past value
    ;;
	-m|--multitenant)
		multitenant="$2"
		shift # past argument
		shift # past value
    ;;
    --default)
		DEFAULT=YES
		shift # past argument
    ;;
    *)    # unknown option
		POSITIONAL+=("$1") # save it in an array for later
		shift # past argument
    ;;
esac
done
set -- "${POSITIONAL[@]}" # restore positional parameters

if [ -z "$os" ]
  then 	
    os='alma9'
fi

if [ -z "$dbuser" ]
  then 	
    dbuser='symfony'
fi

if [ -z "$dbpass" ]
  then 	
    dbpass='symfony'
fi

if [ -z "$parameters" ]
  then 	
    parameters='parameters.yml'
fi

if [ -z "$protocol" ]
  then 	
    protocol='http'
fi

if [ -z "$domainname" ]
  then 	
    domainname='domainname'
fi

if [ -z "$sslcertificate" ]
  then 	
    sslcertificate='localhost.crt'
fi

if [ -z "$sslprivatekey" ]
  then 	
    sslprivatekey='localhost.key'
fi


echo "api_token=$apitoken" 
echo "os=$os"
echo "parameters=$parameters" 
echo "dbuser=$dbuser"
echo "dbpass=$dbpass"

echo "protocol=$protocol"
echo "domainname=$domainname"
echo "sslcertificate=$sslcertificate"
echo "sslprivatekey=$sslprivatekey"
echo "email=$email"
echo "sshfingerprint=$sshfingerprint"
echo "multitenant=$multitenant"

echo "*** Verifying files presence ***"
if [ -z "$apitoken" ]
  then 	
    echo "Error: no token is provided"
    exit 0
fi

if [ -z "$parameters" ]
  then 	
    echo "Error: no parameter file is provided"
    exit 0
fi

if [ -z "$email" ] && [ "$sslcertificate" = "installcertbot" ]
  then
    #email='myemail@myemail.com'
    echo "Error: email is not provided for installcertbot option"
    echo "To enable CertBot installation for SSL/https functionality, please include your email address via --email email@example.com"
    exit 0
fi

#echo "Testing parameters: exit"
#exit 0

TESTING=true

if [ "$os" = "alma8" ]
  then 	
	ORDERPACKERJSON="order-packer-alma8.json"
	TESTING=false
fi
if [ "$os" = "alma9" ] || [ "$os" = "alma" ]
  then  
	ORDERPACKERJSON="order-packer-alma9.json"
	TESTING=false
fi
if [ "$os" = "centos7" ] || [ "$os" = "centos" ]
  then 	
	ORDERPACKERJSON="order-packer-centos7.json"
	TESTING=false
fi
if [ "$os" = "ubuntu22" ] || [ "$os" = "ubuntu" ]
  then 	
	ORDERPACKERJSON="order-packer-ubuntu22.json"
	TESTING=false
fi

#Optional OS
if [ "$os" = "alma8basiconly" ]
  then 	
	ORDERPACKERJSON="order-packer-alma8-basic-only.json"
fi
if [ "$os" = "alma9basiconly" ]
  then 	
	ORDERPACKERJSON="order-packer-alma9-basic-only.json"
fi
if [ "$os" = "alma9hcl" ]
  then  
	ORDERPACKERJSON="order-packer-alma9.json.pkr.hcl"
fi
if [ "$os" = "centosonly" ]
  then 	
	ORDERPACKERJSON="order-packer-centos-only.json"
fi
if [ "$os" = "centos7basiconly" ]
  then 	
	ORDERPACKERJSON="order-packer-centos7-basic-only.json"
fi
if [ "$os" = "centos-without-composer" ]
  then 	
	ORDERPACKERJSON="order-packer-centos-without-composer.json"
fi
if [ "$os" = "ubuntu22basiconly" ]
  then 	
	ORDERPACKERJSON="order-packer-ubuntu22-basic-only.json"
fi



#if [ -z "$ORDERPACKERJSON" ]
#then
#    echo "order-packer.json not found. Use default order-packer-ubuntu.json"
#	ORDERPACKERJSON="order-packer-ubuntu.json"
#fi

echo "ORDERPACKERJSON=$ORDERPACKERJSON"

if [ -f "$ORDERPACKERJSON" ]; then
    echo "$ORDERPACKERJSON exist"
else 
    echo "$ORDERPACKERJSON does not exist"
	exit 0;
fi

###### Create ssh keys ######
echo "*** Create ssh keys ***"
#https://stackoverflow.com/questions/3659602/automating-enter-keypresses-for-bash-script-generating-ssh-keys
#-N "" tells it to use an empty passphrase (the same as two of the enters in an interactive script)
#-f my.key tells it to store the key into my.key (change as you see fit).
#o send enters to an interactive script: echo -e "\n\n\n" | ssh-keygen -t rsa
#It will generate two files in the current folder: sshkey and sshkey.pub
#
#Note: ssh keys added to json file (for example order-packer-centos7.json): file (sshkey.pub), shell (Copy public key to authorized_keys ...)
#move sshkey logic from json to a separate shell script, same for all, except ubuntu22: if sshkey.pub exists => update sshd_config, if not => skip
ssh-keygen -t rsa -b 4096 -N "" -f ./sshkey
###### Create ssh keys ######

#Create snapshot_name_bash_value unique name: use in packer: "snapshot_name": "snapshot_name_bash_value",
snapshot_name_bash_value=packer-$os-`date '+%Y-%m-%d-%H-%M-%S'`
echo snapshot_name_bash_value=$snapshot_name_bash_value
#exit 0

echo "*** Pre processing json file: replace and provide parameters ***"
sed -i -e "s/api_token_bash_value/$apitoken/g" "$ORDERPACKERJSON"
sed -i -e "s/parameters_bash_file/$parameters/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_dbuser/$dbuser/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_dbpass/$dbpass/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_protocol/$protocol/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_dbuser/$dbuser/g" parameters.yml
sed -i -e "s/bash_dbpass/$dbpass/g" parameters.yml

sed -i -e "s/bash_email/$email/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_domainname/$domainname/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_sslcertificate/$sslcertificate/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_sslprivatekey/$sslprivatekey/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_sshfingerprint/$sshfingerprint/g" "$ORDERPACKERJSON"
sed -i -e "s/bash_multitenant/$multitenant/g" "$ORDERPACKERJSON"

sed -i -e "s/snapshot_name_bash_value/$snapshot_name_bash_value/g" "$ORDERPACKERJSON"


############ Run packer json file ############
echo "*** Building VM image from packer=[$ORDERPACKERJSON] ... ***"
#PACKEROUT=$(packer build "$ORDERPACKERJSON" | tail -1)
#echo "*** PACKEROUT=$PACKEROUT ***"
packer build "$ORDERPACKERJSON" | tee buildpacker.log
############ EOF Run packer json file ############

echo "*** Post processing json file (Not important and can be removed) ***"
sed -i -e "s/$apitoken/api_token_bash_value/g" "$ORDERPACKERJSON"
sed -i -e "s/$parameters/parameters_bash_file/g" "$ORDERPACKERJSON"
sed -i -e "s/$dbuser/bash_dbuser/g" "$ORDERPACKERJSON"
sed -i -e "s/$dbpass/bash_dbpass/g" "$ORDERPACKERJSON"
sed -i -e "s/$protocol/bash_protocol/g" "$ORDERPACKERJSON"
sed -i -e "s/$dbuser/bash_dbuser/g" parameters.yml
sed -i -e "s/$dbpass/bash_dbpass/g" parameters.yml
sed -i -e "s/$email/bash_email/g" "$ORDERPACKERJSON"
sed -i -e "s/$domainname/bash_domainname/g" "$ORDERPACKERJSON"
sed -i -e "s/$sslcertificate/bash_sslcertificate/g" "$ORDERPACKERJSON"
sed -i -e "s/$sslprivatekey/bash_sslprivatekey/g" "$ORDERPACKERJSON"
sed -i -e "s/$sshfingerprint/bash_sshfingerprint/g" "$ORDERPACKERJSON"
sed -i -e "s/$snapshot_name_bash_value/snapshot_name_bash_value/g" "$ORDERPACKERJSON"
sed -i -e "s/$multitenant/bash_multitenant/g" "$ORDERPACKERJSON"

#--> digitalocean: A snapshot was created: 'packer-1642782038' (ID: 100353988) in regions 'nyc3'
#Use Packer v1.7.0 or later
echo "*** Building VM image from packer=[$ORDERPACKERJSON] ... ***"
LASTLINE=$(tail -1 buildpacker.log)
echo "*** Packer LASTLINE=$LASTLINE ***"
IMAGENAME=$(tail -1 buildpacker.log |grep -oP "(?<=created: ').*(?=' )")
IMAGEID=$(tail -1 buildpacker.log |grep -oP "(?<=ID: ).*(?=\))")
echo "image ID=$IMAGEID; name=$IMAGENAME"

echo "*** Sleep for 120 sec ***"
sleep 120

#https://docs.digitalocean.com/reference/doctl/reference/compute/certificate/create/
#doctl compute certificate create --type lets_encrypt --name mycert --dns-names tincry.com
#$ doctl compute certificate create --type lets_encrypt --name mycert --dns-names tincry.com
#ID                                      Name      DNS Names     SHA-1 Fingerprint    Expiration Date         Created At              Type            State
#99aba0bf-5366-4892-8b62-eac67d3e884a    mycert    tincry.com                         0001-01-01T00:00:00Z    2023-10-26T15:25:45Z    lets_encrypt    pending

############### Install doctl and create droplet from image ###############
echo "*** Getting image ID ***"
echo "*** Doctl must be installed! https://www.digitalocean.com/docs/apis-clis/doctl/how-to/install/ ***"
echo "" | doctl auth init --access-token $apitoken #echo "" simulate enter pressed

#2) doctl compute domain records create $domainname --record-type A --record-name @ --record-ttl 60 --record-data $DROPLETIP -v
#doctl compute domain records create view.online --record-type A --record-name @ --record-ttl 60 --record-data 142.93.65.236 -v
#DROPLETIP=$(ip -o route get to 8.8.8.8 | sed -n 's/.*src \([0-9.]\+\).*/\1/p')
#echo -e ${COLOR} Script install-cerbot.sh: DROPLETIP="$DROPLETIP" ${NC}

echo "*** Creating droplet IMAGENAME=$IMAGENAME, IMAGEID=$IMAGEID, sshfingerprint=$sshfingerprint... ***"
#DROPLET=$(doctl compute droplet create $IMAGENAME --size 2gb --image $IMAGEID --region nyc3 --wait | tail -1)
#??? Error: POST https://api.digitalocean.com/v2/droplets: 422 The image for this droplet does not use root passwords, please use an SSH key.
#Therefore, specify ssh key's fingerprint:
# doctl compute droplet create packer-1698339421 --size 2gb --image 143115012 --region nyc3 --wait --ssh-keys 4d:54:62:****
if [ -z "$sshfingerprint" ]
  then
    echo "*** Compute droplet without --ssh-keys ... ***"
    DROPLET=$(doctl compute droplet create $IMAGENAME --size 2gb --image $IMAGEID --region nyc3 --wait | tail -1)
  else
    echo "*** Compute droplet with --ssh-keys $sshfingerprint ... ***"
    DROPLET=$(doctl compute droplet create $IMAGENAME --size 2gb --image $IMAGEID --region nyc3 --wait --ssh-keys $sshfingerprint | tail -1)
fi

dropletinfos=( $DROPLET )
echo "After create droplet: dropletinfos=$dropletinfos"

DROPLETIP="${dropletinfos[2]}"
echo "Create droplet IP=$DROPLETIP"
############### EOF Install doctl and create droplet from image ###############

if [ -z $DROPLETIP ]
  then
    echo "Droplet IP is empty. Exit installation. DROPLETIP=$DROPLETIP"
    exit 0
fi

#TESTING=true
#TESTING=false
echo "TESTING=$TESTING"
if [ "$TESTING" = true ]
  then
    echo -e ${COLOR} Testing="$TESTING" => Exit ${NC}
    exit 0
fi
#not testing

#doctl compute domain records create "$domainname" --record-type A --record-name @ --record-ttl 60 --record-data "$DROPLETIP" -v
########## Create domain ###########
echo "Before creating domainname=$domainname"
if [ -n "$domainname" ] && [ "$domainname" != "domainname" ]
  then
    #0) check and create domain and DNS
    echo "Create domain domainname=$domainname"
    DOMAINCHECK=$(doctl compute domain get $domainname)
    echo "Check if domain $domainname exists: $DOMAINCHECK"
    if [ -z "$DOMAINCHECK" ]
      then
        echo "Create domain domainname=$domainname"
        DOMAINRES=$(doctl compute domain create $domainname --ip-address $DROPLETIP)
        echo "Created domain DOMAINRES=$DOMAINRES"
    fi

    #check and delete existing domain DNS's A records with record 'www' or '@'
    #1) doctl compute domain records list $domainname
    LIST=$(doctl compute domain records list $domainname | grep -e '@' -e 'www' | grep -w A | awk '{print $1}')
    #listinfo=( $LIST )
    #RECORDID="${listinfo[0]}"

    #2) doctl compute domain records delete $domainname record_id. --force - Delete record without confirmation prompt
    for recordid in $LIST; do
      echo "Delete old DNS record ID=$recordid"
      DELETERES=$(doctl compute domain records delete $domainname $recordid --force -v)
      #echo "DELETERES=$DELETERES"
    done

    #doctl compute domain create domain_name --ip-address droplet_ip_address
    #'--record-name www' will create domain name with www prefix, i.e. www.view.online
    #'--record-name @' will create domain name without prefix, i.e. view.online
    #doctl compute domain records create $domainname --record-type A --record-name www --record data $DROPLETIP --record-ttl 30 -v
    #https://docs.digitalocean.com/reference/doctl/reference/compute/domain/records/update/
    #'doctl compute domain records create' or 'doctl compute domain records update': --record-ttl 	The record’s Time To Live value, in seconds, default: 1800
    DOMAIN=$(doctl compute domain records create $domainname --record-type A --record-name @ --record-ttl 60 --record-data $DROPLETIP -v)
    echo "DOMAIN=$DOMAIN"
    DROPLETIP="$domainname"
  else
	  echo "Do not create domain domainname=$domainname"
fi

echo -e ${COLOR} Sleep 180 seconds after creating domain "$domainname" with IP "$DROPLETIP" ${NC}
sleep 180
########## EOF Create domain ###########

#this certificate only for Some product features, like load balancer SSL termination and custom Spaces CDN endpoints, require SSL certificates
#echo -e ${COLOR} Use doctl to compute certificate ${NC}
#echo "*** Sleep for 60 sec after certbot ***"
#echo -e ${COLOR} Sleep for 60 sec before open init web page ${NC}
#CERTRES=$(doctl compute certificate create --type lets_encrypt --name mycert --dns-names $domainname)
#sleep 60
#Might need to reboot droplet: doctl compute droplet-action reboot droplet_id 	Reboot a Droplet
#TODO: use ssh key or custom admin username/password to run install-certbot.sh
#Need create private/public keys. Add public key to DigitalOcean, ssh-keygen -t rsa -b 4096 -C "cinava@yahoo.com"
# and use:
#doctl compute ssh 381798128 --ssh-key-path 'pathto\.ssh\id_rsa'
#Enter passphrase
#exit

###### Run install-certbot.sh on the droplet using ssh keys ######
#keys: sshkey-private key, sshkey.pub-public key
#--ssh-key-path 	Path to SSH private key
if [ "$sslcertificate" = "installcertbot" ] && [ -n "$domainname" ] && [ -n "$email" ]
  then
    echo -e ${COLOR} Run bash script install-certbot.sh via ssh. IMAGENAME="$IMAGENAME", domainname="$domainname", sslcertificate="$sslcertificate", email="$email" ${NC}
    echo | doctl compute ssh "$IMAGENAME" --ssh-key-path ./sshkey --ssh-command 'bash /srv/order-lab/packer/install-certbot.sh $domainname $sslcertificate $email'
  else
    echo -e ${COLOR} Skip certbot installation ${NC}
fi

echo -e ${COLOR} Note: here, dropletname is the same as IMAGENAME "$IMAGENAME" ${NC}
echo -e ${COLOR} You can install certbot later manually by running install-certbot.sh:  ${NC}
echo -e ${COLOR} doctl compute ssh "$IMAGENAME" --ssh-key-path mysshkey --ssh-command \'bash /srv/order-lab/packer/install-certbot.sh tincry.com installcertbot myemail@email.com\'  ${NC}

echo -e ${COLOR} You can login to the droplet by running:  ${NC}
echo -e ${COLOR} doctl compute ssh "$IMAGENAME" --ssh-key-path mysshkey  ${NC}

#append the timestamp to the file name of both the public and private SSH keys generated by the script (-YYYY-MM-DD-HH-MM-SS)
echo -e ${COLOR} Append the timestamp to the file name of both the public and private SSH keys ${NC}
curr_dt=`date +"%Y-%m-%d-%H-%M-%S"`
echo -e ${COLOR} curr_dt="$curr_dt" ${NC}
mv ./sshkey ./sshkey-"$curr_dt"
mv ./sshkey.pub ./sshkey-"$curr_dt".pub

#Result:
#Run bash script install-certbot.sh vi ssh
#The authenticity of host '138.197.124.240 (138.197.124.240)' can't be established.
#ED25519 key fingerprint is SHA256:hIVlx+Y76XIEbTBj9CZ/lWOCHynsCgqR/HpW5sm2bcQ.
#This key is not known by any other names
#Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
#Warning: Permanently added '138.197.124.240' (ED25519) to the list of known hosts.
#Connection closed by 138.197.124.240 port 22
#Error: exit status 255
#root@138.197.36.209: Permission denied (publickey,gssapi-keyex,gssapi-with-mic)

#Result on Ubuntu:
#Run bash script install-certbot.sh via ssh. IMAGENAME=packer-1699040312, domainname=tincry.com, sslcertificate=installcertbot, email=oli2002@med.cornell.edu
#The authenticity of host '161.35.182.201 (161.35.182.201)' can't be established.
#ED25519 key fingerprint is SHA256:4nx0hixk58KdfJVVex7dpQLEXLwV6FKM41gkeG7knN0.
#This key is not known by any other names
#Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
#Warning: Permanently added '161.35.182.201' (ED25519) to the list of known hosts.
#Connection closed by 161.35.182.201 port 22
#Error: exit status 255

#exit
###### EOF Run install-certbot.sh on the droplet ######

DROPLETIPWEB="http://$DROPLETIP/directory/admin/first-time-login-generation-init/"
# '! -z' === '-n': -n has value; -z - is empty
# url /directory/admin/first-time-login-generation-init/https might not work if certificate is not installed correctly,
# because will set scheme (connection-channel) to https and run deploy script.
# Therefore url /directory/admin/first-time-login-generation-init/ is safer to run.
if [ -n "$protocol" ] && [ "$protocol" = "https" ]
  then
    if [ "$sslcertificate" = "installcertbot" ] && [ -n "$domainname" ]
      then
	      DROPLETIPWEB="http://$domainname/directory/admin/first-time-login-generation-init/https"
	      #DROPLETIPWEB="http://$domainname/order/directory/admin/first-time-login-generation-init/"
	    else
	      DROPLETIPWEB="http://$DROPLETIP/directory/admin/first-time-login-generation-init/https"
	      #DROPLETIPWEB="http://$DROPLETIP/order/directory/admin/first-time-login-generation-init/"
	  fi
  else
    DROPLETIPWEB="http://$DROPLETIP/directory/admin/first-time-login-generation-init/"
fi

echo "Trying to open a web browser in OS $OSTYPE... You can try to open a web browser manually and go to $DROPLETIPWEB"

#xdg-open "$DROPLETIPWEB"
if [[ "$OSTYPE" == "linux-gnu" ]]; then
    echo -e ${COLOR} Run browser in linux-gnu ${NC}
		xdg-open "$DROPLETIPWEB"
elif [[ "$OSTYPE" == "darwin" ]]; then
    # Mac OSX
		echo "open a web browser manually and go to $DROPLETIPWEB"
elif [[ "$OSTYPE" == "cygwin" ]]; then
    # POSIX compatibility layer and Linux environment emulation for Windows
    echo -e ${COLOR} Run browser in cygwin ${NC}
		#xdg-open Chrome --incognito "$DROPLETIPWEB"
		xdg-open "$DROPLETIPWEB"
elif [[ "$OSTYPE" == "msys" ]]; then
    # Lightweight shell and GNU utilities compiled for Windows (part of MinGW)
		echo -e ${COLOR} Run browser in msys ${NC}
		#start Chrome --incognito "$DROPLETIPWEB";
		start "$DROPLETIPWEB";
elif [[ "$OSTYPE" == "win32" ]]; then
    # Windows
		echo -e ${COLOR} Run browser in win32 ${NC}
		#start Chrome --incognito "$DROPLETIPWEB";
		start "$DROPLETIPWEB";
elif [[ "$OSTYPE" == "freebsd"* ]]; then
    echo -e ${COLOR} Run browser in freebsd ${NC}
		#xdg-open Chrome --incognito "$DROPLETIPWEB"
		xdg-open "$DROPLETIPWEB"
else
    # Unknown.
		echo "open a web browser manually and go to $DROPLETIPWEB"
fi #deploy-order-digital-ocean.sh: line 447: syntax error: unexpected end of file


#Notes:
#user-data in packer
#	"user_data": "#cloud-config          runcmd:         - bash install-certbot.sh bash_domainname bash_sslcertificate bash_email api_token_bash_value snapshot_name_bash_value"


