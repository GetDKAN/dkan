# DKAN Theming

## NuBoot Radix

NuBoot Radix is the default theme for DKAN and is a subtheme of [Radix](https://www.drupal.org/project/radix) which uses [bootstrap](https://github.com/twbs/bootstrap) styles and is compatible with panelized sites. This theme has some basic customization features built in, for many client sites, these configurations will be all that is necessary to meet the client expectations. The configurable items are:

*   Logo (svg logos will display better on retina displays)
*   Front page Hero image OR you can set a solid color background for the hero region.
*   Favicon.
*   Copyright text.
*   Color options via the Colorizer screen
*   Fonts: use font-your-face to use alternate fonts

For more details on these configurations visit [http://docs.getdkan.com/dkan-documentation/dkan-users/custom-appearance](http://docs.getdkan.com/dkan-documentation/dkan-users/custom-appearance)

## Creating a new subtheme

To create a Nuboot Radix subtheme, run these commands

```drush en radix```

```drush vset theme_default radix```

```drush radix "MyThemeName" --kit=https://github.com/NuCivic/radix-kit-nuboot/archive/master.zip```

```drush vset theme_default MyThemeName```

```drush dis radix```

OR if using Ahoy:

```ahoy dkan theme new-from-kit [new-theme-name]```

```ahoy dkan theme setup```

```ahoy dkan theme watch```

Your new subtheme will be placed in to the /sites/all/themes/ directory, it will contain only the directory structure, add your overrides where appropriate.

## Theming Tools
Install Node and npm. You will use [gulp](https://www.npmjs.com/package/gulp) for compiling the sass files. To get your local environment set up, follow these steps:

1. Install Node and npm. You can read a guide on how to install node here: [https://docs.npmjs.com/getting-started/installing-node](https://docs.npmjs.com/getting-started/installing-node)
2. Install bower: `npm install -g bower`.
3. Go to the root of your theme and run the following commands: `npm run setup`.
4. Update browserSyncProxy in config.json
5. Edit the files under the scss and js directory, these will be compiled into the assets directory. Run the following command to compile Sass and watch for changes: `gulp`.

## Icon Fonts
DKAN uses two icon fonts.

### dkan-flaticon
Used for file types (csv, pdf, xls, etc) [designed by Freepik](http://www.flaticon.com/packs/file-formats-icons)

The font files and the css are inside the Nuboot Radix theme `dkan/themes/nuboot_radix`. If you would like to use your own file type icons you can override the dkan-flaticon css by creating a custom theme. OR, if you would like to use the dkan-flaticon icons but NOT use Nuboot Radix as your base theme, you will need to copy the dkan-flaticon fonts and the dkan-flaticon.css into the theme you are using. 

```
dkan/themes/nuboot_radix/assets/fonts
dkan/themes/nuboot_radix/assets/css/dkan-flaticon.css
```

### dkan-topics
Used for the Content Type icons and Topics icons [by Streamline Icons](http://www.streamlineicons.com/index.html)

If you would like to use your own icon font for Topics, you can do that through the user interface. 

1. As an administrator user you can navigate to Configuration > Content authoring > Font Icon Select Options `admin/config/content/font_icon_select_options`. 
2. Use the 'Upload New Library' button to upload your icon font files.
3. Navigate to Structure > Taxonomy > Topics > Manage fields > Icon `admin/structure/taxonomy/dkan_topics/fields/field_topic_icon`
4. Scroll down to the 'Icon field settings' fieldset and select your font from the drop down options.
