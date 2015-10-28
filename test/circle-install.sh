if [ -f ~/backups/dkan-install.sql ]; then
    # Use the cached install backup.
    echo "===> Loading install from cached database found at ~/backups/dkan-install.sql."
    cp circle.settings.php sites/default/settings.php
    drush sql-drop -y && drush sqlc < ~/backups/dkan-install.sql
else
    # Do full installation
    echo "===> No cached database found at ~/backups/dkan-install.sql. Installing from scratch."
    echo "drush si dkan --sites-subdir=default --db-url=$DATABASE_URL --account-name=admin --account-pass=admin  --site-name="DKAN" install_configure_form.update_status_module='array(FALSE,FALSE)' --yes"
    drush si dkan --sites-subdir=default --db-url=$DATABASE_URL --account-name=admin --account-pass=admin  --site-name="DKAN" install_configure_form.update_status_module='array(FALSE,FALSE)' --yes
    drush cc drush; drush cc all --yes
    #store cached backup
    mkdir -p ~/backups
    drush sql-dump > ~/backups/dkan-install.sql
fi
