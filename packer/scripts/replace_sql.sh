FILE=$1

sed -i -e 's/dbo/public/g' "$FILE"
sed -i -e 's/[//g' "$FILE"
sed -i -e 's/]//g' "$FILE"
sed -i -e 's/N\'/\'/g' "$FILE"
