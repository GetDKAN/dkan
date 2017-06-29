SETTINGS_PATH=docroot/sites/default/settings.php
DIR=$(dirname $SETTINGS_PATH)
chmod +w $SETTINGS_PATH 
chmod +w $DIR
echo ".. Reconnect mysql."
MYSQL_IP=`echo $1 | sed 's/.*@\(.*\)\/.*/\1/g'`
sed -i "s/\(^\s*\)'host' => \(.*\),/\1'host' => $MYSQL_IP,/g" $SETTINGS_PATH

chmod -w $SETTINGS_PATH 
chmod -w $DIR

