# Creating a DKAN Subtheme

## NuBoot Radix

NuBoot Radix is the default theme for DKAN and is a subtheme of Radix which uses bootstrap styles and is compatible with panelized sites. This theme has some basic customization features built in, for many client sites, these configurations will be all that is necessary to meet the client expectations. The configurable items are:

*   Logo (svg logos will display better on retina displays)
*   Hero image OR you can set a solid color background for the hero region.
*   Favicon.
*   Copyright text.
*   Color options via the Colorizer screen
*   Fonts: use font-your-face to use alternate fonts

For more details on these configurations visit [http://docs.getdkan.com/dkan-documentation/dkan-users/custom-appearance](http://docs.getdkan.com/dkan-documentation/dkan-users/custom-appearance)

## Creating a new subtheme

When you need more control over the theme or prefer to keep customizations in code without using overrides, creating a subtheme of NuBoot Radix is an easy win. The subtheme will pick up all styles from the chain in cascading order: Radix > NuBoot Radix > Your Subtheme
So you only need to add styles where you want to override the upstream css, and add custom templates if you need specific changes to the layout.
To start a new Nuboot Radix subtheme, run this command

    drush radix "MyThemeName" --kit=https://github.com/NuCivic/radix-kit-nuboot/archive/master.zip

The new subtheme will be placed in to the /sites/all/themes/ directory, it will appear to have a lot of files but it is mostly just file structure to get you started.

## Theming Tools
When creating a new subtheme or contributing to NuBoot Radix, you will need to install Node and npm. Radix 7.x-3.3 has switched from using compass to using gulp for compiling the sass files. To get your local environment set up, follow these steps:

1. Make sure you have Node and npm installed. You can read a guide on how to install node here: [https://docs.npmjs.com/getting-started/installing-node](https://docs.npmjs.com/getting-started/installing-node)
2. Install bower: `npm install -g bower`.
3. Go to the root of your theme and run the following commands: `npm run setup`.
4. Update browserSyncProxy in config.json
5. Edit the files under the scss and js directory, these will be compiled into the assets directory. Run the following command to compile Sass and watch for changes: `gulp`.

## Theming for Older Versions of DKAN (7.x-1.11 or lower)

**NOTICE: These instructions only apply when using radix 7.x-3.0-rc4 and nuboot_radix 7.x-1.11** 

To create a subtheme for dkan 7.x-1.12+ go here http://docs.getdkan.com/dkan-documentation/dkan-developers-guide/creating-sub-theme-dkan-7x-112 

DKAN's default theme is [NuBoot Radix](https://github.com/NuCivic/nuboot_radix). This is a sub-theme of [Radix](https://www.drupal.org/project/radix). It has Sass and Compass support, and makes it easy to build responsive themes with Panels. 

If you would like to change the look of your dkan site, we recommend that you create a subtheme of NuBoot Radix. 

You can easily create a sub-theme of NuBoot Radix by running the following drush command in your _dkan/docroot/sites/all/themes/ directory_: `drush dl radix drush en radix drush cc all drush radix "THEME NAME" --kit=https://github.com/NuCivic/radix-kit-nuboot/archive/master.zip` 

**Important:** The Radix theme must be enabled for the command to work. Change "THEME NAME" to the name you would like to use for your custom theme. The new subtheme will be placed in to the /sites/all/themes/ directory, it will contain a few files to get you started. 

If you are NOT comfortable using [Compass and SASS](http://compass-style.org/), remove the config.rb file and the sass directory. Add your overrides to the stylesheets/custom-screen.css file. 

If you ARE comfortable using Compass and SASS, note that the config.rb file shows one dependency: `require 'compass/import-once/activate'` 

If you do not have this gem installed you may do so by running: `sudo gem install compass` 

There are many gems that will make theming faster and easier with additional mixins, and variables. NuBoot Radix uses the 'bootstrap-sass' and 'compass_radix' gems. You should not use these gems in your subtheme if you use NuBoot Radix as the base theme. If you do, the bootstrap styles in your subtheme will override the NuBoot Radix styles. 

If you do not want to use NuBoot Radix as the base theme, remove 'base theme = nuboot_radix' from the .info file. You can download and use these gems in your theme, be sure to add them to your config.rb file: `sudo gem install bootstrap-sass sudo gem install compass_radix` 

Once you have your subtheme ready, navigate to the Appearance admin page, click 'Set Default' on your theme. Your theme will inherit the bootstrap/radix/nuboot_radix styles. Any styles you put in your theme will override the base theme. For more information on sub-theming view:

*   Radix: [https://www.drupal.org/node/1896382]( https://www.drupal.org/node/1896382)
*   Bootstrap: [https://www.drupal.org/node/1978010](https://www.drupal.org/node/1978010)
*   Drupal: [https://www.drupal.org/node/225125](https://www.drupal.org/node/225125)