#!/bin/bash

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
#cat /usr/local/bin/order-lab/ssl/apache2.crt /usr/local/bin/order-lab/ssl/apache2.key > /etc/haproxy/certs/order.com.pem

#6) Start each httpd configs: sudo httpd -f /etc/httpd/conf/httpd1.conf -k restart
#7) Start HAProxy: sudo systemctl restart haproxy

COLOR='\033[1;36m'
NC='\033[0m' # No Color


f_install_haproxy () {
	echo -e ${COLOR} Install haproxy ${NC}
	sudo yum install -y haproxy

	#https://ideneal.medium.com/how-to-export-symfony-4-environment-variables-into-front-end-application-with-encore-ed45463bee5a
	#dotenv installed via: sudo yarn install --frozen-lockfile 
	#echo -e ${COLOR} Install dotenv ${NC}
	#sudo yarn add dotenv
}

f_create_single_order_instance () {
	echo -e ${COLOR} Create instance: order-lab-"$1" port "$2" ${NC}
	cd /usr/local/bin/
	git clone https://github.com/victorbrodsky/order-lab.git /usr/local/bin/order-lab order-lab-"$1"
	
	echo -e ${COLOR} List ORDER folder after clone ${NC}
	ls /usr/local/bin/order-lab
	
	#chown -R apache:apache /var/www
	echo -e ${COLOR} sudo chmod a+x /usr/local/bin/order-lab-"$1" ${NC}
	sudo chmod a+x /usr/local/bin/order-lab-"$1"
	
	echo -e ${COLOR} sudo chown -R apache:apache /usr/local/bin/order-lab-"$1" ${NC}
	sudo chown -R apache:apache /usr/local/bin/order-lab-"$1"
	
	echo -e ${COLOR} Set environment APP_SUBDIR: replace "APP_SUBDIR=" to "APP_SUBDIR=$3" ${NC}
	sed -i -e "s/APP_SUBDIR=/APP_SUBDIR=$3/g" /usr/local/bin/order-lab-"$1"/orderflex/.env
}
f_create_order_instances() {
	f_create_single_order_instance "order-lab-homepagemanager" "8081" ""
	f_create_single_order_instance "order-lab-tenantmanager" "8082" "tenant-manager"
}

#Create /etc/httpd/conf/tenant-httpd.conf for each order instances above
f_create_tenant_htppd() {
	f_create_single_tenant_htppd "homepagemanager" 8081
	f_create_single_tenant_htppd "tenantmanager" 8082
	
}
f_create_single_tenant_htppd() {
	echo -e ${COLOR} Create "$1"-httpd.conf ${NC}
	cp /etc/httpd/conf/httpd.conf /etc/httpd/conf/"$1"-httpd.conf 
	
	echo -e ${COLOR} Replace 'Listen 80' by Listen "$2" ${NC}
	sed -i -e 's/^Listen/#&/'
	echo "Listen $2" >> /etc/httpd/conf/"$1"-httpd.conf
	
	echo -e ${COLOR} Append 'PidFile /var/run/httpd$2.pid' ${NC}
	sed -i -e 's/^PidFile/#&/' 
	echo "PidFile /var/run/httpd$2.pid" >> /etc/httpd/conf/"$1"-httpd.conf 
	
	echo -e ${COLOR} Append VirtualHost config ${NC}
	cat /usr/local/bin/order-lab/packer/000-default.conf >> /etc/httpd/conf/"$1"-httpd.conf
	
	echo -e ${COLOR} Replace port '80' by "$2" ${NC}
	sed -i -e "s/:80/:$2/g" /etc/httpd/conf/"$1"-httpd.conf
	
	echo -e ${COLOR} Replace 'order-lab' by order-lab-"$1" ${NC}
	sed -i -e "s/order-lab/$1/g" /etc/httpd/conf/"$1"-httpd.conf
}


f_create_order_instances
f_create_tenant_htppd




