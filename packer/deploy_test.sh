#!/bin/bash
# A sample Bash script, by Ryan

#bash deploy-order-digital-ocean.sh 
#-t API-TOKEN-FROM-STEP-1 
#-p parameters.yml 
#$3 dbuser - optional (default symfony)
#$4 dbpass - optional (default symfony)
#$5 protocol - optional (default http)
#$6 domain_name.tld - optional
#$7 ssl_certificate.crt - optional
#$8 intermediate_certificate.ca-crt 


#apitoken=$1
#parameters=$2
#dbuser=$3
#dbpass=$4

#protocol=$5
#domainname=$6
#sslcertificate=$7
#sslprivatekey=$8


#$ bash deploy_test.sh --token apitoken --parameters parameters.yml --dbuser symfony --dbpass symfony --protocol http --domainname domainname --sslcertificate localhost.crt --sslprivatekey localhost.key

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

if [ -z "$dbuser" ]
  then 	
    dbuser='symfony'
fi

if [ -z "$dbpass" ]
  then 	
    dbpass='symfony'
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
echo "parameters=$parameters" 
echo "dbuser=$dbuser"
echo "dbpass=$dbpass"

echo "protocol=$protocol"
echo "domainname=$domainname"
echo "sslcertificate=$sslcertificate"
echo "sslprivatekey=$sslprivatekey"

exit

while getopts "t:p:u:s:r:d:c:k:" opt; do
  case ${opt} in
    t ) # process option t
		#echo "option t" 
		apitoken=$OPTARG
      ;;
    p ) # process option p
		#echo "option p"
		parameters=$OPTARG
      ;;
	u ) dbuser=$OPTARG;;
	s ) dbpass=$OPTARG;;
	r ) protocol=$OPTARG;;
	d ) domainname=$OPTARG;;
	c )	sslcertificate=$OPTARG;;
	k )	sslprivatekey=$OPTARG;;	
    \? ) echo "Usage: cmd [-t api token] [-p parameter file name]"
      ;;
  esac
done

echo "api_token=$apitoken" 
echo "parameters=$parameters" 
echo "dbuser=$dbuser"
echo "dbpass=$dbpass"

echo "protocol=$protocol"
echo "domainname=$domainname"
echo "sslcertificate=$sslcertificate"
echo "sslprivatekey=$sslprivatekey"


