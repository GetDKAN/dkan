Extending and Customizing DKAN
==============


Much additional functionality can be added simply by installing one of the 
tens of thousands of `contrib  modules <https://www.drupal.org/project/project_module>`_ 
from the Drupal community. However, as a `Drupal
Distribution <https://drupal.org/documentation/build/distributions>`_ DKAN is a
flexible framework which developers can also build off of and add to.    
 
DKAN consists of of a distribution profile which manages the initial
installation, 3rd party libraries and drupal modules, and DKAN specific
modules.  
  
Below is a simplified version of where the DKAN code sits within the fully
packaged version::

   profiles/  
       dkan/  
         libraries/ (3rd party libraries)  
         modules/  
            dkan/ (dkan modules)  
            contrib/ (3rd party module dependencies)  
         themes/ (dkan themes)  
   sites/  
      all/  
         libraries/ (your libraries)  
         modules/ (your modules)  
         themes/ (your themes)

After installing DKAN additional functionality should be added to the "sites"
directory.  
  
In the future, this section will feature more detailed information on developing 
custom extentions to DKAN.

Adding/Removing License Options to/From License Field
-----------------------------------------------------
In order to add options to the existing ones you need to implement `hook_license_subscribe` in the following fashion:

.. code-block:: php

    // Assume we want to do this as part of the fictitious license_options_extra module
    function license_options_extra_license_subscribe() {
      return array(
        'tcl' => array(
          'label' => 'Talis Community License (TCL)',
          'uri' => 'http://opendefinition.org/licenses/tcl/',
        ),
      );
    }

The code above add the **Talis Community License (TCL)** license referencing it to the **tcl** key. It also provides a link to the license (optional). You can provide as many options as you want through the array being returned.

Removing License Options
**************************************************

In order to remove options from the existing ones you need to implement `hook_license_unsubscribe` in the following fashion:

.. code-block:: php 

   // Assume we want to do this as part of the fictitious license_options_extra module
   function license_options_extra_license_unsubscribe() {
      return array(
        'notspecified',
      );
   }

The code above removes the **notspecified** option. You can provide as many options as you want through the array being returned.

Additional notes about the behavior of both hooks
**************************************************

+ The options provided through the license drupal field configuration are **COMPLETELY** ignored. 
+ **hook_license_subscribe** implementations are of course called before **hook_license_unsubscribe** implementations.
+ Options subscribed through **hook_license_subscribe** are processed as they come through the order of modules provided by the drupal registry.
+ If multiple options are provided using the same key then it grabs the first one that comes in and ignores the rest
+ If you want to **replace** and item that already exists, unsubscribe the existing key and provided an alternative one for your option

References to the code
**************************************************

* Hooks are invoked `here <https://github.com/NuCivic/dkan/blob/7.x-1.x/modules/dkan/dkan_dataset/modules/dkan_dataset_content_types/dkan_dataset_content_types.license_field.inc#L64>`_
* Field formatter implementation for the license field is in `here <https://github.com/NuCivic/dkan/blob/7.x-1.x/modules/dkan/dkan_dataset/modules/dkan_dataset_content_types/dkan_dataset_content_types.module#L46>`_
