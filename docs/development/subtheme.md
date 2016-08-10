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
