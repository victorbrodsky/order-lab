#!/bin/bash

#bash deploy-test.sh https view.online installcertbot oli2002@med.cornell.edu 159.203.164.243

if [ -z "$protocol" ]
  then
    protocol=$1
fi
if [ -z "$domainname" ]
  then
    domainname=$2
fi
if [ -z "$sslcertificate" ]
  then
    sslcertificate=$3
fi
if [ -z "$email" ]
  then
    email=$4
fi
if [ -z "$ORIGDROPLETIP" ]
  then
    ORIGDROPLETIP=$5
fi

echo protocol=$protocol
echo domainname=$domainname
echo sslcertificate=$sslcertificate
echo email=$email
echo ORIGDROPLETIP=$ORIGDROPLETIP

COLOR='\033[1;36m'
NC='\033[0m' # No Color

#ip = "159.203.164.243"
DROPLETIP="$domainname" #"view.online"

f_install_certbot() {
  if [ -z "$email" ] && [ "$sslcertificate" = "installcertbot" ] ]
      then
        #email='myemail@myemail.com'
        echo "Error: email is not provided for installcertbot option"
        echo "To enable CertBot installation for SSL/https functionality, please include your email address via --email email@example.com"
        exit 0
  fi
	if [ ! -z "$domainname" ] && [ ! -z "$protocol" ] && [ "$protocol" = "https" ]
		then
			echo -e ${COLOR} Install certbot on the Apache server ${NC}
			#bash /usr/local/bin/order-lab/packer/install-certbot.sh "$domainname" "$sslcertificate" "$email"
			#https://www.digitalocean.com/community/questions/run-shell-script-on-droplet-using-api
			echo -e ${COLOR} ssh root@ip 'bash -s' < ./usr/local/bin/order-lab/packer/install-certbot.sh ${NC}
			ssh root@"$ORIGDROPLETIP" 'bash -s' < ./usr/local/bin/order-lab/packer/install-certbot.sh
		else
			echo -e ${COLOR} Domain name is not provided: Do not install certbot on all OS ${NC}
	fi

	echo ""
	sleep 1
}

echo "Install certbot"
#f_install_certbot
echo "*** Sleep for 30 sec after certbot ***"
sleep 30

DROPLETIPWEB="http://$DROPLETIP/order/directory/admin/first-time-login-generation-init/https"

echo "Trying to open a web browser in OS $OSTYPE... You can try to open a web browser manually and go to $DROPLETIPWEB"

#xdg-open "$DROPLETIPWEB"
if [[ "$OSTYPE" == "linux-gnu" ]]; then
        # ...
		xdg-open "$DROPLETIPWEB"
elif [[ "$OSTYPE" == "darwin" ]]; then
        # Mac OSX
		echo "open a web browser manually and go to $DROPLETIPWEB"
elif [[ "$OSTYPE" == "cygwin" ]]; then
        # POSIX compatibility layer and Linux environment emulation for Windows
		xdg-open "$DROPLETIPWEB"
elif [[ "$OSTYPE" == "msys" ]]; then
        # Lightweight shell and GNU utilities compiled for Windows (part of MinGW)
		start "$DROPLETIPWEB";
elif [[ "$OSTYPE" == "win32" ]]; then
        # Windows
		start Chrome --incognito "$DROPLETIPWEB";
elif [[ "$OSTYPE" == "freebsd"* ]]; then
        # ...
		xdg-open "$DROPLETIPWEB"
else
        # Unknown.
		echo "open a web browser manually and go to $DROPLETIPWEB"
fi

