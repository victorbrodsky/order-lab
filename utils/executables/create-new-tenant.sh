#!/bin/sh

while getopts t:u:p: opts; do
   case ${opts} in
      t) TENANTID=${OPTARG} ;;
      u) URL=${OPTARG} ;;
	  p) PORT=${OPTARG} ;;
   esac
done


#tenant ID
echo TENANTID=$TENANTID
echo URL=$URL
echo PORT=$PORT


source ../../packer/install-multitenancy.sh

#f_create_single_order_instance tenantapp3 8086 c/wcm/informatics
f_create_single_order_instance "$TENANTID" "$PORT" "$URL"

#f_create_single_tenant_htppd tenantapp3 8086 c/wcm/informatics
#f_start_single_httpd tenantapp3 8086 c/wcm/informatics


