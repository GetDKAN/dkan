# DKAN Metadata Form

## Development

Assumes installation of [DKAN Tools](https://github.com/getkdkan/dkan-tools).

To work locally in development mode
- Copy your localhost url to `const baseUrl = "";` in the App.js file.
- `npm start`

When ready to commit your changes
- Remove the value from `const baseUrl = "";`
- `rm -R ../build`
- `npm run build`
- `dktl drush dkan-metadata-form:sync`
- `dktl drush cr`
- Review the form at `/admin/dkan/dataset`



## Learn More

This project was bootstrapped with [Create React App](https://github.com/facebook/create-react-app).
