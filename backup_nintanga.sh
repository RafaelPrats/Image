HOST=10.200.99.217
USER=inflorit_us
PASSW='1f0r0Em*.2581'

mysqldump -h $HOST -u $USER -p$PASSW inflorit > db/inflorit.sql
