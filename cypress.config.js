const { defineConfig } = require('cypress')

module.exports = defineConfig({
  apiUri: 'api/1',
  env: {
    TEST_USER_CREDENTIALS: {
      user: 'testadmin',
      pass: 'testadmin',
    },
    UUID_REGEX:
      '^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}$',
  },
  e2e: {
    setupNodeEvents(on, config) {
      return require('./cypress/plugins/index.js')(on, config)
    },
  },
})
