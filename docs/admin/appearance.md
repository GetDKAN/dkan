# Customizing DKAN's Appearance

DKAN takes advantage of Drupal’s well-developed and flexible theming system,
allowing administrators complete graphical control over the entire DKAN site.
Features such as responsive page templates, accessible design elements, and
built-in media management provide the latest in design and presentation
technologies.

## Default DKAN Theme

In Drupal and DKAN, the collection of page templates, fonts, colors, images,
and other “look and feel” elements are known as a “theme.” The DKAN
distribution of Drupal includes a default “DKAN Theme” called NuBoot Radix, that visually
highlights all of the DKAN-specific data elements in the software.

DKAN administrators have the choice of customizing the existing DKAN Theme or
implementing an entirely new theme, or creating a [subtheme of nuboot_radix](dkan-documentation/dkan-developers-guide/creating-sub-theme-dkan).

By default, the DKAN Theme is located in: `[SITEROOT]/profiles/dkan/themes/contrib/nuboot_radix`

The default DKAN Theme is a sub-theme of [Radix](https://www.drupal.org/project/radix),  and Radix uses components and plugins from [Bootstrap](https://www.drupal.org/project/bootstrap).

## Theme and Appearance Settings

### Site name and slogan
From the settings screen, you can toggle on the **site name** and **slogan**, simply check the box next to the elements you want to use.
![Site name and slogan settings][Site name and slogan settings]

### Logo
Uncheck the 'Use the default' checkbox, and upload a new logo file in the logo image settings section.
![Logo settings][Logo settings]

### Shortcut icon
If you would like to use a different favicon image, uncheck the 'Use the default' checkbox, and upload your own.
![Favicon settings][Favicon settings]

### Copyright info
To change the **copyright** information that displays at the bottom of every page, edit the text in the copyright field and save.
![Copyright settings][Copyright settings]

### Hero background image/color
The **Hero Unit** is the background image that displays on the front page. To use a different photo than the one supplied, click the 'Choose file' button to upload a new image. This image will expand to cover the full width of the browser so be sure to upload a horizontal/landscape image and not a vertical/portrait image.
![Hero unit settings][Hero unit settings]

### Color scheme
To use the **colorizer** option, you must use the default theme as the admin theme. Navigate to `[SITEROOT]/admin/appearance` and scroll to the bottom. Confirm that the Admin theme is set to 'Default Theme'.

Now navigate to `[SITEROOT]/admin/appearance/colorizer` by clicking on the 'Colorizer' tab. Here you will see the color scheme options. There are a few default options you can select from the drop down, or you can enter hex values to create a custom color scheme, be sure to click 'Save Configuration' when finished. Your new colors are saved to a css file in your files directory. If you do not see your changes you may need to clear the colorizer cache by clicking the 'Clear Colorizer Cache' button. These colors will trump all other styles in the theme.
![Colorizer settings][Colorizer settings]

### Fonts
On the Appearance page, you will see a sub-menu item for **@font-your-face**. Navigate to `[SITEROOT]/admin/appearance/fontyourface`.

By default, the Google fonts api is enabled. If you click on the **Browse all fonts** tab, you will be able to select which google fonts to add to your site. Once you have made your selections, click on the **Enabled fonts** tab to view the font settings screen. Click on the **By CSS selector**, here you can select the css tag and what font should be applied to it using the table. To add fonts to specific css selectors, add them to the open text field at the bottom of the list, select a font from the dropdown next to it and click the **Save applied fonts** button
For more information on how to use @fontyourface visit the [project page](https://www.drupal.org/project/fontyourface).
![Select google fonts][Select google fonts]
![Apply fonts][Apply fonts]

## More Information About Drupal Theming

If your organization has no experience with Drupal theming, we strongly
recommend starting any theme customization by reading the [Drupal.org Theming
Guide](https://drupal.org/documentation/theme).
