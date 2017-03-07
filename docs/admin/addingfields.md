# Adding Fields to DKAN

The following will allow you to add new fields to DKAN content types like Dataset.

The result will create a new field "MY NEW FIELD" as on the Dataset form:

![dataset form](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-10-02%20at%2011.21.18%20AM.png)

which will appear on the Dataset view when content is entered:

![dataset view](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-10-02%20at%2011.29.43%20AM.png)

## Adding the New Field

Let's add a field to the **Dataset** content type as an example.

+ Go to **/admin/structure/types/manage/dataset/fields** in your browser:

![field manage page](http://docs.getdkan.com/sites/default/files/add%20field%20screen.png)

+ Scroll down till you see the **Add new field** input row

+ Let's add a **MY NEW FIELD** field as an example:

![Add new field](http://docs.getdkan.com/sites/default/files/add%20new%20field2.png)

+ Press **Save** and proceed with the field setup:

![](http://docs.getdkan.com/sites/default/files/my%20new%20field%20settings.png)

![](http://docs.getdkan.com/sites/default/files/Screen%20Shot%202014-10-02%20at%2011.44.44%20AM.png)

## Adjust Where Field Appears on Form

+ Adjust weight of field to control where it appears on the form:

![adjust weight](http://docs.getdkan.com/sites/default/files/my%20new%20field%20weight.png)

## Adjust Where Field Appears on Dataset Page

+ Click "Manage Display" to adjust where it appears in the output

+ To add to the "Dataset Info" table on the Dataset view drag to "Dataset Information" group
+ Remember to hide the "Label" for display in the table:

![Adjust label](http://docs.getdkan.com/sites/default/files/my%20new%20field%20table.png)

We provide hook implementations in order to add extra options or remove existing ones to/from the field_license options

## Adding/Removing License Options to/From License Field

In order to add options to the existing ones you need to implement `hook_license_subscribe` in the following fashion:

```php
    // Let's asume we want to do this as part of the fictitious license_options_extra module
    function license_options_extra_license_subscribe() {
      return array(
        'tcl' => array(
          'label' => 'Talis Community License (TCL)',
          'uri' => 'http://opendefinition.org/licenses/tcl/',
        ),
      );
    }
```

The code above add the **Talis Community License (TCL)** license referencing it to the **tcl** key. It also provides a link to the license (optional). You can provide as many options as you want through the array being returned.

### Removing License Options

In order to remove options from the existing ones you need to implement `hook_license_unsubscribe` in the following fashion:

```php
    // Let's asume we want to do this as part of the fictitious license_options_extra module
    function license_options_extra_license_unsubscribe() {
      return array(
        'notspecified',
      );
    }
```

The code above removes the **notspecified** option. You can provide as many options as you want through the array being returned.

### Additional notes about the behavior of both hooks

+ The options provided through the license drupal field configuration are **COMPLETELY** ignored. 
+ **hook_license_subscribe** implementations are of course called before **hook_license_unsubscribe** implementations.
+ Options subscribed through **hook_license_subscribe** are processed as they come through the order of modules provided by the drupal registry.
+ If multiple options are provided using the same key then it grabs the first one that comes in and ignores the rest
+ If you want to **replace** and item that already exists, unsubscribe the existing key and provided an alternative one for your option

### References to the code

+ Hooks are invoked [here](https://github.com/NuCivic/dkan_dataset/blob/7.x-1.x/modules/dkan_dataset_content_types/dkan_dataset_content_types.license_field.inc#L22)
+ Field formatter implementation for the license field is in [here](https://github.com/NuCivic/dkan_dataset/blob/7.x-1.x/modules/dkan_dataset_content_types/dkan_dataset_content_types.module#L28)

