TEXTTOENDREPLACE='host all all 0.0.0.0/0 md5'
sed -i -e "s/TEXTTOEND/$TEXTTOENDREPLACE/g" /var/lib/pgsql/12/data/pg_hba.conf	