
if [ "$HOSTNAME" = "cli" ]; then
  HOST=cli
else
  HOST=localhost
fi

cp ./dkan/.ahoy/.scripts/.ht.router.php ./docroot/
cd ./docroot

echo $HOST
php -S $HOST:8888 .ht.router.php