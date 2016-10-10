# Developing Off of DKAN

### Drupal Distributions  

As a [Drupal
Distribution](https://drupal.org/documentation/build/distributions) DKAN is a
flexible framework which developers can build off of and add to.  

In order to build off of DKAN it is best to become familiar with the way in
which [Drupal
Distributions](https://drupal.org/documentation/build/distributions) function. 
 
DKAN consists of of a distribution profile which manages the initial
installation, 3rd party libraries and drupal modules, and DKAN specific
modules.  
  
Below is a simplified version of where the DKAN code sits within the fully
packaged version:

    
<blockcode language="bash">
profiles/  
    dkan/  
		libraries/ (3rd party libraries)  
		modules/  
			dkan/ (dkan modules)  
			contrib/ (3rd party modules)  
		themes/ (dkan modules)  
sites/  
	all/  
		libraries/ (your libraries)  
		modules/ (your modules)  
		themes/ (your themes)
</blockcode>

After installing DKAN additional functionality should be added to the "sites"
directory.  
  
DKAN can be periodically updated, for example when new versions are released,
by updated the "profiles/dkan" folder.  

### Advanced Workflow  

For building sites off of DKAN, we recommend the following workflow: [Maintaining your installed Drupal distro](https://www.acquia.com/blog/maintaining-your-installed-drupal-distro)

This workflow is recommended because it best allows grabbing new functionality
from DKAN, contributing code to DKAN, as well as maintaining other 3rd party
modules.  

### Pantheon

[Pantheon](https://www.getpantheon.com/) offers DKAN hosting which includes a
workflow that merges in periodic updates to DKAN as well as Drupal core
security updates. This workflow allows developers and site owners a way to get
periodic updates of DKAN that is simpler but allows for less control than the
advanced workflow recommended above.  
