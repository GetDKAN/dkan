##########################################################################################
      _                _                                  _                     _
   __| | _____   _____| | ___  _ __  _ __ ___   ___ _ __ | |_    __ _  ___  ___| | _____
  / _` |/ _ \ \ / / _ \ |/ _ \| '_ \| '_ ` _ \ / _ \ '_ \| __|  / _` |/ _ \/ _ \ |/ / __|
 | (_| |  __/\ V /  __/ | (_) | |_) | | | | | |  __/ | | | |_  | (_| |  __/  __/   <\__ \
  \__,_|\___| \_/ \___|_|\___/| .__/|_| |_| |_|\___|_| |_|\__|  \__, |\___|\___|_|\_\___/
                              |_|                               |___/
##########################################################################################

##########################################################################################
##### Delta Module
##########################################################################################
Project Page:   http://drupal.org/project/delta
Issue Queue:    http://drupal.org/project/issues/delta
Usage Stats:    http://drupal.org/project/usage/delta
Maintainer(s):  
                Jake Strawn 
                  http://himerus.com
                  http://developmentgeeks.com
                  http://facebook.com/developmentgeeks
                  http://drupal.org/user/159141
                  http://twitter.com/himerus
                Sebastian Siemssen
                  http://twitter.com/thefubhy
                  http://drupal.org/user/761344
##########################################################################################

Delta Module Information
========================
The Delta module enables contextual theme settings for Omega (drupal.org/project/omega) 
subthemes. The combination of Delta, Omega and Context will give you the ability to create 
duplicates of theme settings and via Context assign them as a reaction to any context you 
can create.

Usage
=====

 1.) Download and install the Delta & Context modules
 2.) Download and enable an Omega Subtheme
     a.) http://drupal.org/project/omega (Omega Base theme & Starterkit)
     b.) http://drupal.org/project/gamma (Gamma subtheme)
 3.) Visit /admin/appearance and configure the defaults for your Omega subtheme
 4.) Visit /admin/appearance/delta and select the checkbox for the appropriate subtheme 
     on the default settings tab
 5.) Visit /admin/appearance/delta/templates/add and create your first template.
     a.) Give it a pretty name
     b.) Select your theme from the dropdown & save
 6.) Automatically directed to /admin/appearance/delta/templates/configure/your-template,
     you will be able to customize the settings for this special copy of the theme settings.
 7.) Visit /admin/structure/context, and add or edit a context with your conditions
 8.) Select Delta from the reactions box, and select the appropriate Delta template you 
     just created.
 9.) Save and Enjoy...

Related Information
===================
  * http://himerus.com/blog/himerus/omega-intro-2-delta-module-contextual-theme-settings