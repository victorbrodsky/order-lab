file=$1
#file="script_user_test.sql"
echo "file=$file" 
#replace dbo -> public
sed -i -e "s/dbo/public/g" "$file"
#replace [ -> null
sed -i -e "s/\[//g" "$file"
#replace ] -> null
sed -i -e "s/\]/''/g" "$file"
#replace N' -> '
sed -i -e "s/N'/'/g" "$file"
#INSERT public.
sed -i -e "s/INSERT public./INSERT INTO public./g" "$file"
