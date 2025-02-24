#!/bin/bash
#install-multitenancy.sh

#1) Install HAProxy

#2) Create order instances: 
#port 8081 is used only for the “Homepage manager” alone without a tenant, so it is unused on view.med.cornell.edu,
#port 8082 only for “tenant manager” on both view.med.cornell.edu and on view.online with just “SuperUser” and “SuperUser Deputy” accounts,
#port 8083 – demo institution/department tenant on both,
#port 8084 – test institution/department tenant on both,
#port 8086 – always reserved for real tenant #1, 
#	thus on public view.online it is the /c/wcm/pathology tenant, but on view.med.cornell.edu it is serving the “tenant 1+homepage manager”;
#port 8087 is always the second real tenant #2,
#port 8088-would be the third tenant, etc so you could script the creation of tenants

#3) For each order instances set APP_SUBDIR for setPublicPath(process.env.APP_SUBDIR+'/build') in webpack.config.json
#https://ideneal.medium.com/how-to-export-symfony-4-environment-variables-into-front-end-application-with-encore-ed45463bee5a

#4) Create /etc/httpd/conf/tenant-httpd.conf for each order instances above

#5) Create combined certificate and key order-ssl.com.pem: 
#cat "$bashpath"/order-lab/ssl/apache2.crt "$bashpath"/order-lab/ssl/apache2.key > /etc/haproxy/certs/order.com.pem

#6) Start each httpd configs: sudo httpd -f /etc/httpd/conf/httpd1.conf -k restart
#7) Start HAProxy: sudo systemctl restart haproxy

if [ -z "$bashdbuser" ]
  then 	
    bashdbuser=$1
fi
if [ -z "$bashdbpass" ]
  then 	
    bashdbpass=$2
fi
if [ -z "$bashprotocol" ]
  then 	
    bashprotocol=$3
fi
if [ -z "$bashdomainname" ]
  then 	
    bashdomainname=$4
fi
if [ -z "$bashsslcertificate" ]
  then
    bashsslcertificate=$5
fi
if [ -z "$bashemail" ]
  then
    bashemail=$6
fi
if [ -z "$multitenant" ]
  then
    multitenant=$7
fi

POSITIONAL=()
while [[ $# -gt 0 ]]
do
key="$1"
case $key in
	-m|--multitenant)
		multitenant="$2"
		shift # past argument
		shift # past value
    ;;
    -p|--path)
        bashpath="$2"
        shift # past argument
        shift # past value
    ;;
    -s|--scheme)
        bashprotocol="$2"
        shift # past argument
        shift # past value
    ;;
    -u|--username)
        bashdbuser="$2"
        shift # past argument
        shift # past value
    ;;
    -t|--password)
        bashdbpass="$2"
        shift # past argument
        shift # past value
    ;;
    -d|--domain)
        bashdomainname="$2"
        shift # past argument
        shift # past value
    ;;
    -e|--email)
        bashemail="$2"
        shift # past argument
        shift # past value
    ;;
    -l|--sertificate)
        bashsslcertificate="$2"
        shift # past argument
        shift # past value
    ;;
    *)    # unknown option
		POSITIONAL+=("$1") # save it in an array for later
		shift # past argument
    ;;
esac
done
set -- "${POSITIONAL[@]}" # restore positional parameters

#bashpath="/usr/local/bin"
#bashpath="/srv"
if [ -z "$bashpath" ]; then
    bashpath="/usr/local/bin"
    #bashpath="/srv"
fi

if [ ! -z "$bashemail" ] && [ "$bashemail" = "none" ]
  then
    bashemail=""
fi
if [ ! -z "$bashsslcertificate" ] && [ "$bashsslcertificate" = "none" ]
  then
    bashsslcertificate=""
fi
if [ ! -z "$bashdomainname" ] && [ "$bashdomainname" = "none" ]
  then
    bashdomainname=""
fi
if [ ! -z "$bashprotocol" ] && [ "$bashprotocol" = "none" ]
  then
    bashprotocol=""
fi

echo bashdbuser=$bashdbuser
echo bashdbpass=$bashdbpass
echo bashprotocol=$bashprotocol
echo bashdomainname=$bashdomainname
echo bashsslcertificate=$bashsslcertificate
echo bashemail=$bashemail
echo multitenant=$multitenant
echo bashpath=$bashpath

#Testing: Exit the script with a success status (0)
#exit 0

COLOR='\033[1;36m'
NC='\033[0m' # No Color

#TODO: convert ../../ to a string without '/' in httpd config file name 
#TODO: test if parameters can be provided to the functions as $1 $2 $3
tenantsArray=(
	"homepagemanager 8081 " 
	"tenantmanager 8082 tenant-manager"
	"tenantappdemo 8083 c/demo-institution/demo-department"
	"tenantapptest 8084 c/test-institution/test-department"
	"tenantapp1 8085 c/wcm/pathology"
	"tenantapp2 8086 c/wcm/psychiatry"
)
declare -a tenantsArrayTest=(
	"homepagemanager 8081 "
	"tenantmanager 8082 tenant-manager"
	"tenantappdemo 8083 c/demo-institution/demo-department"
)

f_start_all_httpd_test() {
	#echo -e ${COLOR} String:"$str" ${NC}
	echo -e ${COLOR} First element ${NC}
	echo $1
	echo -e ${COLOR} Second element ${NC}
	echo $2
	echo -e ${COLOR} Third element ${NC}
	echo $3
	
	#f_start_single_httpd "homepagemanager" 8081
	#for str in ${tenantsArrayTest[@]}; do
		#echo -e ${COLOR} Start single httpd "$str" ${NC}
		#f_start_single_httpd $str;shift
		#f_start_single_httpd $str
		#f_start_single_httpd $str
	#done
}
f_replace() {
	echo -e ${COLOR} replace "$3" in /etc/httpd/conf/"$1"-httpd.conf  ${NC}
	sed -i -e "s,aliasurl,$3,g" /etc/httpd/conf/"$1"-httpd.conf
}
f_test () {
    #sed -i -e 's/^Listen/#&/' /etc/httpd/conf/"$1"-httpd.conf 
	echo -e ${COLOR} f_test ${NC}
	#sed -i -e 's/^bind *:80/#&/' /etc/haproxy/haproxy.cfg
	#sed -i -e 's/^\s*bind *:80/#&/' /etc/haproxy/haproxy.cfg
	#sed -i -e 's/^global/#&/' /etc/haproxy/haproxy.cfg
	#sed -i -e 's/^\s*bind \*:80/#&/' /etc/haproxy/haproxy.cfg
	
	#f_start_all_httpd_test tenantappdemo 8083 c/demo-institution/demo-department
	#f_start_all_httpd
	
	#for str in ${tenantsArrayTest[@]}; do
	#for str in "${tenantsArrayTest[@]}"; do
	#	echo -e ${COLOR} Testing "$str" ${NC}
		#f_start_single_httpd $str;shift
		#f_start_single_httpd $str
		#don't use ""!
	#	f_start_all_httpd_test $str
	#done

	f_replace tenantapp1 8085 c/wcm/pathology
}





#1) Install HAProxy
f_install_haproxy () {
	echo -e ${COLOR} Install haproxy ${NC}
	sudo yum install -y haproxy
	
	echo -e ${COLOR} Copy haproxy from packer ${NC}
	sudo mv /etc/haproxy/haproxy.cfg /etc/haproxy/haproxy.cfg_orig
	sudo cp "$bashpath"/order-lab/packer/haproxy.cfg /etc/haproxy/

	if [ ! -z "$bashprotocol" ] && [ "$bashprotocol" = "https" ]
		then 
			echo -e ${COLOR} Enable https 'bind *:443 ssl' and disable 'bind *:80' in haproxy.cfg ${NC}
			sed -i -e 's/^\s*bind \*:80/#&/' /etc/haproxy/haproxy.cfg
		else
			echo -e ${COLOR} Use default 'http bind *:80' and disable 'bind *:443' in haproxy.cfg ${NC}
			sed -i -e 's/^\s*bind \*:443/#&/' /etc/haproxy/haproxy.cfg
			
			echo -e ${COLOR} disable 'http-request redirect scheme https unless { ssl_fc }' in haproxy.cfg ${NC}
			sed -i -e 's/^\s*http-request/#&/' /etc/haproxy/haproxy.cfg
	fi	
	
	echo -e ${COLOR} Adding new line to haproxy to prevent 'Missing LF on last line' ${NC}
	echo "" >> /etc/haproxy/haproxy.cfg

	#https://ideneal.medium.com/how-to-export-symfony-4-environment-variables-into-front-end-application-with-encore-ed45463bee5a
	#dotenv installed via: sudo yarn install --frozen-lockfile 
	#echo -e ${COLOR} Install dotenv ${NC}
	#sudo yarn add dotenv
	
	echo -e ${COLOR} Install netstat ${NC}
	sudo yum install -y net-tools
	
	#Install firewall
	#Enable ports
}

#2) Create order instances
f_create_single_order_instance () {

	echo -e ${COLOR} Check if instance exists: "$1" port "$2" url "$3" ${NC}
	if [ -d "$bashpath/order-lab-$1" ]; then
        echo -e ${COLOR} Target directory ["$bashpath/order-lab-$1"] already exist ${NC}
        return 0
    else
        echo -e ${COLOR} Target directory ["$bashpath/order-lab-$1"] does not exist ${NC}
    fi

	echo -e ${COLOR} Create instance: "$1" port "$2" url "$3" ${NC}
	#cd "$bashpath"/
	changedir "$bashpath"/
	echo -e ${COLOR} Current folder: ${NC}
	pwd
	
	git clone https://github.com/victorbrodsky/order-lab.git "$bashpath"/order-lab-"$1"
	
	echo -e ${COLOR} Check if instance has been created: "$1" port "$2" url "$3" ${NC}
	if [ ! -d "$bashpath/order-lab-$1" ]; then
		echo -e ${COLOR} Error! Instance "$bashpath/order-lab-$1" has not been created ${NC}
		return 0
	else	
		echo -e ${COLOR} Instance "$bashpath/order-lab-$1" has been created! ${NC}
	fi
	
	echo -e ${COLOR} List ORDER folder after clone ${NC}
	ls "$bashpath"/order-lab-"$1"
	
	echo -e ${COLOR} Add ownership in repository ${NC}
	git config --global --add safe.directory "$bashpath"/order-lab-"$1"
	
	#chown -R apache:apache /var/www
	echo -e ${COLOR} sudo chmod a+x "$bashpath"/order-lab-"$1" ${NC}
	sudo chmod a+x "$bashpath"/order-lab-"$1"
	
	echo -e ${COLOR} sudo chown -R apache:apache "$bashpath"/order-lab-"$1" ${NC}
	sudo chown -R apache:apache "$bashpath"/order-lab-"$1"
	
	echo -e ${COLOR} Copy env ${NC}
	cp "$bashpath"/order-lab/packer/.env "$bashpath"/order-lab-"$1"/orderflex/
	
	echo -e ${COLOR} Copy env.test ${NC}
	cp "$bashpath"/order-lab/packer/.env.test "$bashpath"/order-lab-"$1"/orderflex/
	
	#3) For each order instances set APP_SUBDIR
	echo -e ${COLOR} Set environment APP_SUBDIR: replace "APP_SUBDIR=" to "APP_SUBDIR=$3" ${NC}
	#sed -i -e "s/APP_SUBDIR=/APP_SUBDIR=$3/g" "$bashpath"/order-lab-"$1"/orderflex/.env
	sed -i -e "s,APP_SUBDIR=,APP_SUBDIR=$3,g" "$bashpath"/order-lab-"$1"/orderflex/.env
	
	#copy parameters.yml
	echo -e ${COLOR} Copy parameters.yml for order-lab-"$1" ${NC}
	sudo cp "$bashpath"/order-lab/orderflex/config/parameters.yml "$bashpath"/order-lab-"$1"/orderflex/config/parameters.yml	

	echo -e ${COLOR} Set DB name for order-lab-"$1" ${NC}
	sed -i -e "s/database_name: scanorder/database_name: $1/g" "$bashpath"/order-lab-"$1"/orderflex/config/parameters.yml
	
	echo -e ${COLOR} Set tenant_role as "$1" for order-lab-"$1" ${NC}
	sed -i -e "s/tenant_role: null/tenant_role: $1/g" "$bashpath"/order-lab-"$1"/orderflex/config/parameters.yml
	
	#run composer
	echo -e ${COLOR} Run composer for order-lab-"$1" ${NC}
	#sudo cd "$bashpath"/order-lab-"$1"/orderflex
	changedir "$bashpath/order-lab-$1"/orderflex
	echo -e ${COLOR} Current folder before install tenant for order-lab-"$1": ${NC}
	pwd
	
	echo -e ${COLOR} composer validate ${NC}
	COMPOSER_ALLOW_SUPERUSER=1 /usr/local/bin/composer validate --verbose
	
	echo -e ${COLOR} composer diagnose ${NC}
	COMPOSER_ALLOW_SUPERUSER=1 /usr/local/bin/composer diagnose --verbose

	echo -e ${COLOR} composer install to ["$bashpath"/order-lab-"$1"/orderflex] ${NC}
	COMPOSER_ALLOW_SUPERUSER=1 /usr/local/bin/composer install --working-dir="$bashpath"/order-lab-"$1"/orderflex --verbose
	#COMPOSER_ALLOW_SUPERUSER=1 composer install --verbose
	
	COMPOSER_ALLOW_SUPERUSER=1 /usr/local/bin/composer dump-autoload --working-dir="$bashpath"/order-lab-"$1"/orderflex
	#COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload
	
	echo -e ${COLOR} List ORDER folder after composer ${NC}
	ls "$bashpath"/order-lab-"$1"/orderflex
	
	#echo -e ${COLOR} Change folder to order-lab-"$1" ${NC}
	#sudo cd "$bashpath"/order-lab-"$1"/orderflex
	echo -e ${COLOR} Install yarn frozen-lockfile for order-lab-"$1" ${NC}
	sudo yarn install --frozen-lockfile --cwd "$bashpath"/order-lab-"$1"/orderflex
	
	echo -e ${COLOR} Install additional.sh. env for python for order-lab-"$1" ${NC}
	#TODO: can not change directory inside script
	bash "$bashpath"/order-lab-"$1"/packer/additional.sh "$bashpath"

	echo -e ${COLOR} Install db.config for python postgres-manage-python for order-lab-"$1" ${NC}
    cp "$bashpath"/order-lab/utils/db-manage/postgres-manage-python/sample.config "$bashpath"/order-lab/utils/db-manage/postgres-manage-python/db.config
    sed -i -e "s/dbname/$1/g" "$bashpath"/order-lab/utils/db-manage/postgres-manage-python/db.config
    sed -i -e "s/dbusername/symfony/g" "$bashpath"/order-lab/utils/db-manage/postgres-manage-python/db.config
    sed -i -e "s/dbuserpassword/symfony/g" "$bashpath"/order-lab/utils/db-manage/postgres-manage-python/db.config

	changedir "$bashpath"/order-lab-"$1"/orderflex
	echo -e ${COLOR} Current folder before deploy tenant for order-lab-"$1": ${NC}
	pwd
	
	#run deploy	
	#echo -e ${COLOR} Run deploy for python for order-lab-"$1" ${NC}
	#sudo chmod +x "$bashpath"/order-lab-"$1"/orderflex/deploy_prod.sh
	#bash "$bashpath"/order-lab-"$1"/orderflex/deploy_prod.sh -withdb
	
	echo -e ${COLOR} chown apache for order-lab-"$1" ${NC}
	sudo chown -R apache:apache "$bashpath"/order-lab-"$1"
	sudo chown -R apache:apache "$bashpath"/order-lab-"$1"/.git/
	
	echo -e ${COLOR} Create and update DB for order-lab-"$1" ${NC}
	pwd
	sudo php "$bashpath"/order-lab-"$1"/orderflex/bin/console doctrine:database:create
	sudo php "$bashpath"/order-lab-"$1"/orderflex/bin/console doctrine:schema:update --complete --force
	sudo php "$bashpath"/order-lab-"$1"/orderflex/bin/console doctrine:migration:status
	sudo php "$bashpath"/order-lab-"$1"/orderflex/bin/console doctrine:migration:sync-metadata-storage
	#sudo php "$bashpath"/order-lab-"$1"/orderflex/bin/console doctrine:migration:version --add --all --no-interaction
	
	echo -e ${COLOR} Final run deploy for order-lab-"$1" ${NC}
	bash "$bashpath"/order-lab-"$1"/orderflex/deploy_prod.sh -withdb
}
f_create_order_instances() {
	#for str in ${tenantsArray[@]}; do
	#    echo -e ${COLOR} Create order instance: "$str" ${NC}
	#	f_create_single_order_instance $str
		#f_create_single_order_instance "homepagemanager" "8081" ""
		#f_create_single_order_instance "tenantmanager" "8082" "tenant-manager"
	#done
	f_create_single_order_instance homepagemanager 8081
	f_create_single_order_instance tenantmanager 8082 tenant-manager
	f_create_single_order_instance tenantappdemo 8083 c/demo-institution/demo-department
	f_create_single_order_instance tenantapptest 8084 c/test-institution/test-department
	f_create_single_order_instance tenantapp1 8085 c/wcm/pathology
	f_create_single_order_instance tenantapp2 8086 c/wcm/psychiatry
}

#4) Create /etc/httpd/conf/tenant-httpd.conf for each order instances above
f_create_tenant_htppd() {
    echo -e ${COLOR} f_create_tenant_htppd ${NC}
	#f_create_single_tenant_htppd "homepagemanager" 8081 
	#f_create_single_tenant_htppd "tenantmanager" 8082 tenant-manager
	#for str in ${tenantsArray[@]}; do
	#    echo -e ${COLOR} Create httpd: "$str" ${NC}
	#	f_create_single_tenant_htppd $str
	#done
	f_create_single_tenant_htppd homepagemanager 8081
	f_create_single_tenant_htppd tenantmanager 8082 tenant-manager
	f_create_single_tenant_htppd tenantappdemo 8083 c/demo-institution/demo-department
	f_create_single_tenant_htppd tenantapptest 8084 c/test-institution/test-department
	f_create_single_tenant_htppd tenantapp1 8085 c/wcm/pathology
	f_create_single_tenant_htppd tenantapp2 8086 c/wcm/psychiatry
	
	sudo systemctl daemon-reload
}
f_create_single_tenant_htppd() {

	echo -e ${COLOR} Check if httpd exists: "$1" port "$2" url "$3" ${NC}
	if [ -d "/etc/httpd/conf/$1-httpd.conf" ]; then
		echo -e ${COLOR} Httpd /etc/httpd/conf/"$1"-httpd.conf does exist ${NC}
		return 0
	fi

	echo -e ${COLOR} Create "$1"-httpd.conf ${NC}
	cp /etc/httpd/conf/httpd.conf /etc/httpd/conf/"$1"-httpd.conf 
	
	echo -e ${COLOR} Replace 'Listen 80' by Listen "$2" ${NC}
	sed -i -e 's/^Listen/#&/' /etc/httpd/conf/"$1"-httpd.conf 
	echo "Listen $2" >> /etc/httpd/conf/"$1"-httpd.conf
	
	echo -e ${COLOR} Append 'PidFile /var/run/httpd$2.pid' ${NC}
	sed -i -e 's/^PidFile/#&/' /etc/httpd/conf/"$1"-httpd.conf 
	echo "PidFile /var/run/httpd$2.pid" >> /etc/httpd/conf/"$1"-httpd.conf 
	
	echo -e ${COLOR} Append VirtualHost config ${NC}
	cat "$bashpath"/order-lab/packer/000-default.conf >> /etc/httpd/conf/"$1"-httpd.conf
	
	echo -e ${COLOR} Replace port '80' by "$2" ${NC}
	sed -i -e "s/:80/:$2/g" /etc/httpd/conf/"$1"-httpd.conf
	
	echo -e ${COLOR} Replace DocumentRoot 'order-lab' by order-lab-"$1" ${NC}
	sed -i -e "s/order-lab/order-lab-$1/g" /etc/httpd/conf/"$1"-httpd.conf
	
	#Alias /c/demo-institution/demo-department "$bashpath"/order-lab-2/orderflex/public/
	if [ -n "$3" ]
		then
			echo -e ${COLOR} Replace Alias url 'aliasurl' by "$3" ${NC}
			#Alias /order "$bashpath"/order-lab/orderflex/public/
			#sed -i -e "s/aliasurl/$3/g" /etc/httpd/conf/"$1"-httpd.conf
			sed -i -e "s,aliasurl,$3,g" /etc/httpd/conf/"$1"-httpd.conf
		else
			echo -e ${COLOR} Alias url not provided "$3" ${NC}
	fi	
	
	#Since you're running multiple instances manually, you'll need to create custom systemd service files to manage them.
	#Create httpd service
	echo -e ${COLOR} Create httpd"$1".service for port "$2", url "$3" ${NC}
	cp "$bashpath"/order-lab/packer/custom_httpd.service /etc/systemd/system/httpd"$1".service
	sed -i -e "s/httpd_custom.conf/$1-httpd.conf/g" /etc/systemd/system/httpd"$1".service
	echo -e ${COLOR} Enable httpd"$1".service for port "$2", url "$3" ${NC}
	sudo systemctl enable httpd"$1"
	#sudo systemctl start httpd"$1"
}

#5) Create combined certificate and key order-ssl.com.pem
f_create_combined_certificate() {
	echo -e ${COLOR} Create combined certificate and key order-ssl.com.pem ${NC}
	cat "$bashpath"/order-lab/ssl/apache2.crt "$bashpath"/order-lab/ssl/apache2.key > /etc/haproxy/certs/order.com.pem
}

f_stop_httpd() {
	echo -e ${COLOR} Mask httpd service i.e. completely disable it so that no other service can activate httpd ${NC}
	sudo systemctl mask httpd
	sudo systemctl disable httpd
	
	echo -e ${COLOR} Stop default /etc/httpd/conf/httpd.conf ${NC}
	sudo httpd -f /etc/httpd/conf/httpd.conf -k stop
	sudo systemctl stop httpd.service
	
	echo -e ${COLOR} Disable ssl.conf ${NC}
	sudo mv /etc/httpd/conf.d/ssl.conf /etc/httpd/conf.d/ssl.conf_orig
}

#6) Start each httpd configs: sudo httpd -f /etc/httpd/conf/httpd1.conf -k restart
f_start_single_httpd() {
	#sleep 3
	#echo -e ${COLOR} Stop /etc/httpd/conf/"$1"-httpd.conf for port "$2", url "$3" ${NC}
	#sudo httpd -f /etc/httpd/conf/"$1"-httpd.conf -k stop
	#sleep 3
	#echo -e ${COLOR} Start /etc/httpd/conf/"$1"-httpd.conf for port "$2", url "$3" ${NC}
	#sudo httpd -f /etc/httpd/conf/"$1"-httpd.conf -k start
	
	sleep 3
	echo -e ${COLOR} Stop httpd"$1" service for port "$2", url "$3" ${NC}
	sudo systemctl stop httpd"$1"
	
	sleep 3
	echo -e ${COLOR} Start httpd"$1" service for port "$2", url "$3" ${NC}
	sudo systemctl start httpd"$1"
	
	sleep 3
	echo -e ${COLOR} Status httpd"$1" for port "$2", url "$3" ${NC}
	sudo netstat -na | grep :"$2"
	sudo systemctl status httpd"$1"
	
	#Start /etc/httpd/conf/tenantmanager-httpd.conf 
	#(98)Address already in use: AH00072: make_sock: could not bind to address [::]:8082
	#(98)Address already in use: AH00072: make_sock: could not bind to address 0.0.0.0:8082
	#no listening sockets available, shutting down
	#AH00015: Unable to open logs
}
f_start_all_httpd() {
	#echo -e ${COLOR} Stop default /etc/httpd/conf/httpd.conf ${NC}
	#sudo httpd -f /etc/httpd/conf/httpd.conf -k stop
	#sleep 5  # Waits 5 seconds.
	#f_start_single_httpd "homepagemanager" 8081
	#sleep 5  # Waits 5 seconds.
	#f_start_single_httpd "tenantmanager" 8082
	
	#for str in ${tenantsArray[@]}; do
	#	#echo -e ${COLOR} Start single httpd "$str" ${NC}
	#	f_start_single_httpd "$str"
	#done
	
	f_start_single_httpd homepagemanager 8081
	f_start_single_httpd tenantmanager 8082 tenant-manager
	f_start_single_httpd tenantappdemo 8083 c/demo-institution/demo-department
	f_start_single_httpd tenantapptest 8084 c/test-institution/test-department
	f_start_single_httpd tenantapp1 8085 c/wcm/pathology
	f_start_single_httpd tenantapp2 8086 c/wcm/psychiatry
}

f_add_tenant_haproxy() {
	
	echo -e ${COLOR} Check if HAProxy has tenant: "$1" port "$2" url "$3" ${NC}
	HAProxyConfig="/etc/haproxy/haproxy.cfg"
	if grep -q "$1" "$HAProxyConfig"; then
		echo -e ${COLOR} Do not add tenant to HAProxy "$HAProxyConfig", it is already exist: "$1" port "$2" url "$3" ${NC}
		return 0
	fi


	#https://stackoverflow.com/questions/15559359/insert-line-after-match-using-sed
	#sed append before line: i\
	#add new tenant i.e. '3' to frontend after ###START-FRONTEND-CUSTOM-TENANTS 
	# acl tenantapp3_url path_beg -i /c/wcm/psychiatry
    # use_backend tenantapp3_backend if tenantapp3_url
	FRONT_1="\ \ \ \ acl tenantapp$1_url path_beg -i /$3 \n"
	FRONT_2="\ \ \ \ use_backend tenantapp$1_backend if tenantapp$1_url"
	#FRONT_3="\ \ \ \ ###END-FRONTEND-CUSTOM-TENANTS"
	FRONTENDSTR="$FRONT_1$FRONT_2"
	echo FRONTENDSTR=$FRONTENDSTR
	#sed -i -e "s,###END-FRONTEND-CUSTOM-TENANTS,$FRONTENDSTR,g" /etc/haproxy/haproxy.cfg
	sed -i "/###END-FRONTEND-CUSTOM-TENANTS/i $FRONTENDSTR" /etc/haproxy/haproxy.cfg
	
	#add tenant to backend after ###START-BACKEND-CUSTOM-TENANTS
	#backend tenantapp3_backend
    #server tenantapp3_server *:8087 check
	BACK_1="\ \ \ \ backend tenantapp$1_backend \n"
	BACK_2="\ \ \ \ server tenantapp$1_server *:$2 check"
	#BACK_3="\ \ \ \ ###END-BACKEND-CUSTOM-TENANTS"
	BACKENDSTR="$BACK_1$BACK_2"
	echo BACKENDSTR=$BACKENDSTR
	#sed -i -e "s/###END-BACKEND-CUSTOM-TENANTS/$BACKENDSTR/g" /etc/haproxy/haproxy.cfg
	sed -i "/###END-BACKEND-CUSTOM-TENANTS/i $BACKENDSTR" /etc/haproxy/haproxy.cfg
	#TEST:
	#sed -i '/###END-BACKEND-CUSTOM-TENANTS/i \ \ BACKENDSTR' /etc/haproxy/haproxy.cfg
}

#7) Start HAProxy: sudo systemctl restart haproxy
f_start_haproxy() {
	#TODO: haproxy is not started?!
	echo -e ${COLOR} Enable haproxy ${NC}
	sudo systemctl enable haproxy
	
	echo -e ${COLOR} Start haproxy ${NC}
	sudo systemctl restart haproxy
	
	echo -e ${COLOR} Status haproxy ${NC}
	sudo systemctl status haproxy
	echo -e ${COLOR} Status haproxy: journalctl -xeu haproxy.service ${NC}
	sudo journalctl -xeu haproxy.service
}

f_restart_phpfpm() {
	#Make sure php-fpm is started	
	echo -e ${COLOR} Make sure php-fpm is started ${NC}
	sudo systemctl restart php-fpm
}

function changedir() {
  cd $1
}


if [ -n "$multitenant" ] && [ "$multitenant" == "haproxy" ]
	then
		echo -e ${COLOR} Use multitenancy multitenant="$multitenant" ${NC}
		#f_test
		if true; then
			echo -e ${COLOR} multitenancy True ${NC}
			f_install_haproxy
			f_create_order_instances
			f_create_tenant_htppd
			f_create_combined_certificate
			f_start_haproxy
			f_stop_httpd
			f_start_all_httpd
			##f_restart_phpfpm
		else
			echo -e ${COLOR} False ${NC}
			f_test
			#f_create_tenant_htppd
			#f_start_all_httpd
		fi
	else
		echo -e ${COLOR} Do not use multitenancy multitenant="$multitenant" ${NC}
fi







#From order-packer-centos7
#"echo @### Create system DB - TODELETE ###",
#"bash "$bashpath"/order-lab/orderflex/deploy_prod.sh",
#"php bin/console doctrine:database:create --connection=systemdb",
#"php bin/console doctrine:schema:update --em=systemdb --complete --force",
#"bash "$bashpath"/order-lab/orderflex/deploy_prod.sh"

