if [ -z ${MYSQL+x} ]; then
  echo "MYSQL url param was not set, setting to empty string by default."
  MYSQL=""
fi

drush si dkan --root=docroot --verbose --account-pass='admin' $MYSQL --site-name='DKAN' install_configure_form.update_status_module='array(FALSE,FALSE)' --yes && \
  drush --root=docroot sql-dump > backups/last_install.sql.tmp && \
  mv backups/last_install.sql.tmp backups/last_install.sql && \
  echo "Installed DKAN and created a new backup at backups/last_install.sql"
