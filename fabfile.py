from fabric.api import * 
import os

env.htdocs = '/Applications/MAMP/htdocs/'
env.root = '/Applications/MAMP/htdocs/dkan'
env.site = env.root + '/sites'
env.profiles = env.root + '/profiles'
env.db = 'dkan'
def deploy():
	local('git pull')
	
	with lcd(env.htdocs):
		local('sudo rm -rf dkan')
	
	local('drush make dkan_distro.make ' + env.root)
	
	with lcd(env.site):
		 local('drush site-install dkan --db-url=mysql://root:root@localhost/'+env.db+' -y')