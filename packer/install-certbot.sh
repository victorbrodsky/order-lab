#!/bin/bash

#https://certbot.eff.org/instructions?ws=apache&os=centosrhel8
#bash /usr/local/bin/order-lab/packer/install-certbot.sh tincry.com installcertbot oli2002@med.cornell.edu apitoken
#run script from local PC: ssh root@159.203.85.84 'bash -s' < ./install-certbot.sh need to enter root password

COLOR='\033[1;36m'
NC='\033[0m' # No Color

#if true
  #disable this script
#  then
#    echo Script install-cerbot.sh: Disabled
#    exit 0
#fi

#sudo snap install doctl

if [ -z "$domainname" ]
  then 	
    domainname=$1
fi

if [ -z "$sslcertificate" ]
  then
    sslcertificate=$2
fi

if [ -z "$email" ]
  then
    email=$3
fi

if [ -z "$multitenant" ]
  then
    multitenant=$4
fi

#if [ -z "$userpass" ]
#  then
#    userpass=$4
#fi

#bashpath="/usr/local/bin"
bashpath="/srv"

echo domainname=$domainname
echo sslcertificate=$sslcertificate
echo email=$email
echo bashpath=$bashpath
echo multitenant=$multitenant
#echo userpass=$userpass

echo Script install-cerbot.sh: domainname=$domainname

#Testing
#echo -e ${COLOR} End of install-certbot.sh script ${NC}
#exit 0

if [ "$sslcertificate" != "installcertbot" ]
  then
    echo "Abort certbot installation: sslcertificate option is not 'installcertbot'"
    exit 0
fi

if [ -z "$email" ] && [ "$sslcertificate" = "installcertbot" ] ]
  then
    #email='myemail@myemail.com'
    echo "Error: email is not provided for installcertbot option"
    echo "To enable CertBot installation for SSL/https functionality, please include your email address via --email email@example.com"
    exit 0
fi

if [ ! -z "$domainname" ]
	then 
		echo -e ${COLOR} Script install-cerbot.sh: Install cerbot for domain: "$domainname" ${NC}
	else
		echo -e ${COLOR} Script install-cerbot.sh: Domain name is not provided: Do not install certbot on all OS ${NC}
		exit
fi	

OSNAME=""
if cat /etc/*release | grep ^NAME | grep CentOS; then
    OSNAME="CentOS"
 elif cat /etc/*release | grep ^NAME | grep Red; then
    OSNAME="Red"
 elif cat /etc/*release | grep ^NAME | grep Ubuntu; then
    OSNAME="Ubuntu"
 elif cat /etc/*release | grep ^NAME | grep Alma; then
    OSNAME="Alma"
 else
    echo "OS NOT DETECTED, couldn't install packages"
    exit 1;
 fi
 echo "==============================================="
 echo "Installing packages on $OSNAME"
 echo "==============================================="

if [ "$OSNAME" = "Ubuntu" ];
	then
		echo "==============================================="
		echo "Use Ubuntu OS $OSNAME"
		echo "==============================================="
		echo -e ${COLOR} Script install-cerbot.sh: Disable the original ssl configuration default-ssl.conf on Ubuntu ${NC}
    sudo mv /etc/apache2/sites-enabled/default-ssl.conf /etc/apache2/sites-enabled/default-ssl.orig
    sudo mv /etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available/default-ssl.orig
	else
		echo "==============================================="
		echo "Use all others OS $OSNAME"
		echo "==============================================="
		echo -e ${COLOR} Script install-cerbot.sh: Disable the original ssl configuration default-ssl.conf  ${NC}
    sudo mv /etc/httpd/conf.d/default-ssl.conf /etc/httpd/conf.d/default-ssl.orig
fi

######## Copy the ssl config file for lets encrypt ########
#echo -e ${COLOR} Script install-cerbot.sh: Copy the ssl config file for lets encrypt ${NC}
#sudo cp /usr/local/bin/order-lab/packer/000-default-le-ssl.conf /etc/httpd/conf.d/000-default-le-ssl.conf
#sed -i -e "s/bash_domainname/$domainname/g" /etc/httpd/conf.d/000-default-le-ssl.conf
######## EOF Copy the ssl config file for lets encrypt ########

echo -e ${COLOR} Script install-cerbot.sh: Restart apache server before installing Certbot ${NC}
#Ubuntu: sudo systemctl restart apache2.service
if [ "$OSNAME" = "Ubuntu" ]; 
	then
		echo "==============================================="
		echo "Restart Apache on Ubuntu $OSNAME"
		echo "==============================================="
		sudo systemctl restart apache2.service
		sudo systemctl status apache2.service
	else	
		echo "==============================================="
		echo "Restart Apache on all others OS $OSNAME"
		echo "==============================================="
		sudo systemctl restart httpd.service
		sudo systemctl status httpd.service
fi

echo -e ${COLOR} Script install-cerbot.sh: Install Snapd ${NC}
if [[ -n "$multitenant" && "$multitenant" == "haproxy" ]]; then
    echo "multitenant is haproxy => use homepagemanager folder order-lab-homepagemanager/orderflex/"
    cd "$bashpath"/order-lab-homepagemanager/orderflex/
else
  echo "multitenant is not haproxy => use classical, single tenant home folder order-lab/orderflex/"
  cd "$bashpath"/order-lab/orderflex/
fi


echo -e ${COLOR} Script install-cerbot.sh: Install Snapd according to OS ${NC}
#Centos yum, Alma dnf, Ubuntu apt
#https://stackoverflow.com/questions/394230/how-to-detect-the-os-from-a-bash-script
YUM_PACKAGE_NAME="snapd"
if [ "$OSNAME" = "CentOS" ] 
then
	echo "==============================================="
    echo "Installing packages $YUM_PACKAGE_NAME on CentOS"
	echo "==============================================="
    sudo yum install -y "$YUM_PACKAGE_NAME"
    #sudo yum install -y python3-certbot-dns-digitalocean
elif [ "$OSNAME" = "Red" ] 
then
	echo "==============================================="
    echo "Installing packages $YUM_PACKAGE_NAME on RedHat"
	echo "==============================================="
    sudo yum install -y "$YUM_PACKAGE_NAME"
    #sudo yum install -y python3-certbot-dns-digitalocean
elif [ "$OSNAME" = "Ubuntu" ] 
then
    echo "==============================================="
    echo "Installing packages $YUM_PACKAGE_NAME on Ubuntu"
    echo "==============================================="
    #sudo apt-get update
    sudo apt-get install -y "$YUM_PACKAGE_NAME"
    #sudo apt install -y python3-certbot-dns-digitalocean
elif [ "$OSNAME" = "Alma" ] 
then
    echo "==============================================="
    echo "Installing packages $YUM_PACKAGE_NAME on Alma"
    echo "==============================================="
    sudo dnf install -y "$YUM_PACKAGE_NAME"
    #sudo dnf install -y python3-certbot-dns-digitalocean
else
    echo "OS NOT DETECTED, couldn't install package $YUM_PACKAGE_NAME"
    exit 1;
fi

echo -e ${COLOR} Script install-cerbot.sh: Enable and create symlink for Snapd ${NC}
sudo systemctl enable --now snapd.socket
sudo ln -s /var/lib/snapd/snap /snap

echo ""
sleep 3

echo -e ${COLOR} Script install-cerbot.sh: Install Certbot ${NC}
sudo snap install --classic certbot

echo ""
sleep 3

#sudo snap install --classic certbot => error: too early for operation, device not yet seeded or device model not acknowledged
echo -e "${COLOR} Script install-cerbot.sh: Install Certbot second attempt ${NC}"
sudo snap install --classic certbot

echo -e "${COLOR} Script install-cerbot.sh: create symbolik link ${NC}"
sudo ln -s /snap/bin/certbot /usr/bin/certbot

####### Creating a Sudo-Enabled User ######
#echo -e ${COLOR} Creating a Sudo-Enabled User ${NC}
#adduser adminuser
#
#echo -e ${COLOR} Sudo-Enabled User: create password ${NC}
#passwd adminuser
#
#echo -e ${COLOR} Sudo-Enabled User: add the user to the wheel group. All members of the wheel group have full sudo access ${NC}
#usermod -aG wheel adminuser
#
##Testing user
##su - adminuser
##sudo ls -la /root
####### EOF Creating a Sudo-Enabled User ######

###### Run certbot and create certificate. Can be done only when DNS is pointed to this droplet IP. ######
if true
  then
    echo -e "${COLOR} Sleep 120 seconds before installing certbot on Apache ${NC}"
    sleep 120
    #Certbot doesn’t officially support HAProxy, you’ll need to use the certonly command with the --standalone option. Here’s a general approach
    if [[ -n "$multitenant" && "$multitenant" == "haproxy" ]]; then
        echo -e "${COLOR} multitenant is haproxy => use haproxy certificate ${NC}"
        echo -e "${COLOR} 1 Stop HAProxy Temporarily ${NC}"
        sudo systemctl stop haproxy
        echo -e "${COLOR} 2 Run Certbot to Obtain a Certificate ${NC}"
        sudo certbot certonly --standalone --agree-tos --non-interactive --email "$email" --domains "view.online"
        echo -e "${COLOR} 3 Combine the certificate and private key in cert_key.pem ${NC}"
        cat /etc/letsencrypt/live/view.online/cert.pem /etc/letsencrypt/live/view.online/privkey.pem > /etc/letsencrypt/live/view.online/cert_key.pem

        echo -e "${COLOR} 4 Update your HAProxy configuration ${NC}"

        echo -e "${COLOR} 4a Enable *:443 ${NC}"
        #sed -i -e 's/#bind \*:443 ssl crt \/etc\/letsencrypt\/live\/view\.online\/cert_key\.pem/bind *:443 ssl crt \/etc\/letsencrypt\/live\/view\.online\/cert_key\.pem/g' /etc/haproxy/haproxy.cfg
        CONFIG_FILE="/etc/haproxy/haproxy.cfg"
        SEARCH_PATTERN="#bind *:443 ssl crt /etc/letsencrypt/live/view.online/cert_key.pem"
        REPLACE_PATTERN="bind *:443 ssl crt /etc/letsencrypt/live/view.online/cert_key.pem"
        # Uncomment the line
        sed -i -e "s/$SEARCH_PATTERN/$REPLACE_PATTERN/g" "$CONFIG_FILE"
        #sed -i -e "s/$SEARCH_PATTERN/bind *:443 ssl crt /etc/letsencrypt/live/view.online/cert_key.pem|g" "$CONFIG_FILE"
        #sed -i -e "s|^$SEARCH_PATTERN|bind *:443 ssl crt /etc/letsencrypt/live/view.online/cert_key.pem|g" "$CONFIG_FILE"

        echo -e "${COLOR} 4b Enable redirect scheme https ${NC}"
        sed -i -e 's/#http-request redirect scheme https unless { ssl_fc }/http-request redirect scheme https unless { ssl_fc }/g' /etc/haproxy/haproxy.cfg

        echo -e "${COLOR} 5 Restart HAProxy ${NC}"
        sudo systemctl start haproxy

        echo -e "${COLOR} 6 Set Up Auto-Renewal Certbot certificates expire every 90 days, so set up a cron job to renew them ${NC}"
        #sudo crontab -e
        #0 3 * * * certbot renew --quiet && systemctl reload haproxy
        (crontab -l 2>/dev/null; echo "0 3 * * * certbot renew --quiet && systemctl reload haproxy") | crontab -

        echo -e ${COLOR} Done with certbot and HaProxy ${NC}
    else
        echo -e ${COLOR} Script install-cerbot.sh: Get a certificate and have Certbot edit your apache configuration automatically ${NC}
        echo -e ${COLOR} Script install-cerbot.sh: sudo certbot -n -v --apache --agree-tos --email "$email" --domains "$domainname" ${NC}
        sudo certbot -n -v --apache --agree-tos --email "$email" --domains "$domainname"
        #certbot -n -v --apache --agree-tos --email "oli2002@med.cornell.edu" --domains "view.online"
        ##sudo certbot -n -v --apache --agree-tos --dns-digitalocean --email "$email" --domains "$domainname"
    fi

    echo -e ${COLOR} Script install-cerbot.sh: Test automatic renewal ${NC}
    sudo certbot renew --dry-run

    if [ "$OSNAME" = "Ubuntu" ]
      then
        echo "==============================================="
        echo "Use Ubuntu OS $OSNAME"
        echo "==============================================="
        if [[ -n "$multitenant" && "$multitenant" == "haproxy" ]]; then
            echo "multitenant is haproxy => use haproxy certificate. Do nothing here"
        else
          echo "multitenant is not haproxy => use httpd certificate"
          echo -e ${COLOR} Restore original 000-default.conf to enable to login with http ${NC}
          cp /etc/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled/000-default.conf_orig
          cp "$bashpath"/order-lab/packer/000-default.conf /etc/apache2/sites-enabled/000-default.conf
        fi

      else
        echo "==============================================="
        echo "Use on all others OS $OSNAME"
        echo "==============================================="
        if [[ -n "$multitenant" && "$multitenant" == "haproxy" ]]; then
            echo "multitenant is haproxy => use haproxy certificate. Do nothing here"
        else
          echo "multitenant is not haproxy => use httpd certificate"
          echo -e ${COLOR} Restore original 000-default.conf to enable to login with http ${NC}
          cp /etc/httpd/conf.d/000-default.conf /etc/httpd/conf.d/000-default.conf_orig
          cp "$bashpath"/order-lab/packer/000-default.conf /etc/httpd/conf.d/000-default.conf
        fi

    fi

    echo -e ${COLOR} Script install-cerbot.sh: Restart apache server after installing Certbot ${NC}
    if [ "$OSNAME" = "Ubuntu" ]
      then
        echo "==============================================="
        echo "Restart Apache on Ubuntu $OSNAME"
        echo "==============================================="
        if [[ -n "$multitenant" && "$multitenant" == "haproxy" ]]; then
            echo "multitenant is haproxy => already restarted haproxy. Do nothing here"
        else
          echo "multitenant is not haproxy => restart httpd"
          sudo systemctl restart apache2.service
          sudo systemctl status apache2.service
        fi
      else
        echo "==============================================="
        echo "Restart Apache on all others OS $OSNAME"
        echo "==============================================="
        if [[ -n "$multitenant" && "$multitenant" == "haproxy" ]]; then
            echo "multitenant is haproxy => already restarted haproxy. Do nothing here"
        else
          echo "multitenant is not haproxy => restart httpd"
          sudo systemctl restart httpd.service
          sudo systemctl status httpd.service
        fi
    fi
fi
###### EOF Run certbot and create certificate ######

echo -e ${COLOR} End of install-certbot.sh script ${NC}
exit 0

#Success:
#Certificate is saved at: /etc/letsencrypt/live/tincry.com/fullchain.pem
#Key is saved at:         /etc/letsencrypt/live/tincry.com/privkey.pem
#Log:                     /var/log/letsencrypt/letsencrypt.log
#[root@packer-1698242454 letsencrypt]# sudo certbot -n --apache --agree-tos --email oli2002@med.cornell.edu --domains tincry.com
#Saving debug log to /var/log/letsencrypt/letsencrypt.log
#Requesting a certificate for tincry.com
#
#Successfully received certificate.
#Certificate is saved at: /etc/letsencrypt/live/tincry.com/fullchain.pem
#Key is saved at:         /etc/letsencrypt/live/tincry.com/privkey.pem
#This certificate expires on 2024-01-23.
#These files will be updated when the certificate renews.
#Certbot has set up a scheduled task to automatically renew this certificate in the background.
#
#Deploying certificate
#Successfully deployed certificate for tincry.com to /etc/httpd/conf.d/000-default-le-ssl.conf
#Added an HTTP->HTTPS rewrite in addition to other RewriteRules; you may wish to check for overall consistency.
#Congratulations! You have successfully enabled HTTPS on https://tincry.com


########### Notes #############
#https://www.digitalocean.com/blog/automating-application-deployments-with-user-data
#https://github.com/daveworth/sample_app_rails_4/blob/master/deploy_to_do.sh
#{
#        "type": "shell",
#		"environment_vars": [
#			"domainname=bash_domainname",
#			"sslcertificate=bash_sslcertificate",
#			"email=bash_email",
#			"apitoken=api_token_bash_value",
#			"snapshot_name=snapshot_name_bash_value"
#		],
#        "script": "install-certbot.sh"
#	  }

#Renew certbot certificate with standalone option:
#1) stop web server: sudo systemctl stop haproxy.service
#2) run: sudo certbot certonly --standalone
#3) create /etc/letsencrypt/live/view.online/cert_key.pem containing both your server’s PEM-formatted TLS certificate and its private key
#3a) Verify Certificate is saved at: /etc/letsencrypt/live/view.online/fullchain.pem
     #Key is saved at:         /etc/letsencrypt/live/view.online/privkey.pem
#3b) cd /etc/letsencrypt/live/view.online/
#3c) cat cert.pem privkey.pem > cert_key.pem
#4) start haproxy: sudo systemctl start haproxy.service
