# Modifying the Front Page

NuBoot Radix is the default theme for dkan. It has a custom panel layout template that can be found at ```dkan/webroot/profiles/dkan/themes/contrib/nuboot_radix/panels/layouts```

![Disable default front page][Disable default front page]

### If you do not want to use the hero unit background image in your new layout:

Simply change the layout using the user interface:

1. Navigate to the  ```Structure > Pages```  admin screen
2. Disable the default front page
3. Create a new page:

    *   Add a title
    *   Set the path
    *   Check "Make this your site home page" box
    *   Select layout
    *   Add content

![Create new page][Create new page]
![Select new layout][Select new layout]

### If you DO want to use the hero unit background image:
You will need to copy the new layout files into your custom theme, and copy the hero unit code to the layout template.

1.  [Create a custom theme based on nuboot_radix](http://docs.getdkan.com/dkan-documentation/dkan-developers-guide/creating-sub-theme-dkan) 

2. Copy the layout files from the source (such as radix_layouts) to your custom theme, or create your own files from scratch. Navigate to ```dkan/webroot/profiles/dkan/modules/contrib/radix_layouts/plugins/layouts``` to see the layout files for radix. Select the one you would like to use and copy it, rename it, and add it to your custom theme: ```dkan/webroot/sites/all/themes/[custom_theme]/panels/layouts/[renamed_layout]```
![copy layout files][copy layout files]

3. Copy the hero unit markup from the full_width layout option in nuboot_radix to your new custom layout option, change the panel region names/variables as needed. You might also need to adjust the css to work with your new layout as well.

4. Uncomment or add  ```plugins[panels][layouts] = panels/layouts``` in your theme .info file
![Uncomment panels line][Uncomment panels line]

5. Then follow the steps of the first option:

    *  Disable the default front page
    *  Create a new front page
    *  Set the layout to your new custom layout
    *  Add content

<!-- Images -->
[Disable default front page]: http://docs.getdkan.com/sites/default/files/Screenshot_9_8_15__12_52_PM.png
[Create new page]: http://docs.getdkan.com/sites/default/files/Screenshot_9_8_15__1_40_PM.png
[Select layout]: http://docs.getdkan.com/sites/default/files/Screenshot_9_8_15__1_42_PM.png
[Select new layout]: http://docs.getdkan.com/sites/default/files/Screenshot_9_8_15__1_42_PM-2.png
[copy layout files]: http://docs.getdkan.com/sites/default/files/layouts.png
[Uncomment panels line]: http://docs.getdkan.com/sites/default/files/Screenshot_9_8_15__2_04_PM.png
