import * as dkan from '../support/helpers/dkan'

context('Admin dataset file upload', () => {
  context('Create dataset with remote file', () => {
    const fileUrl = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv'
    const title = dkan.generateRandomString()

    beforeEach(() => {
      const user_credentials = Cypress.env('TEST_USER_CREDENTIALS')
      cy.drupalLogin(user_credentials.user, user_credentials.pass)
    })

    it('create the dataset', () => {
      cy.visit('/node/add/data')
      cy.wait(2000)
      cy.get('#edit-field-json-metadata-0-value-title').type(title, { force:true } )
      cy.get('#edit-field-json-metadata-0-value-description').type('DKANTEST distribution description.', { force:true } )
      cy.get('#edit-field-json-metadata-0-value-accesslevel').select('public', { force:true } )
      cy.get('#edit-field-json-metadata-0-value-modified-date').type('2021-02-02', { force:true } )
      // Fill select2 field for publisher.
      cy.get('#edit-field-json-metadata-0-value-publisher-publisher-name + .select2')
        .find('.select2-selection')
        .click({ force:true })
      cy.get('input[aria-controls="select2-edit-field-json-metadata-0-value-publisher-publisher-name-results"]').type('DKANTEST Publisher{enter}')
      // End filling up publisher.
      cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-fn').type('DKANTEST Contact Name', { force:true } )
      cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail').type('dkantest@test.com', { force:true } )
      // Fill select2 field for keyword.
      cy.get('#edit-field-json-metadata-0-value-keyword-keyword-0 + .select2')
        .find('.select2-selection')
        .click({ force: true })
      cy.get('input[aria-controls="select2-edit-field-json-metadata-0-value-keyword-keyword-0-results"]').type('open data{enter}')
      // End filling up keyword.
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-title')
        .type('distribution title test', { force:true } )
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-description')
        .type('distribution description test', { force:true } )
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format-select')
        .select('csv', { force:true })
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-file-url-type-remote')
        .click({ force:true })
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-file-url-remote')
        .type(fileUrl, { force:true })
      cy.get('#edit-submit')
        .click({ force:true })
      cy.get('.button').contains('Yes')
        .click({ force:true })
      cy.get('.messages--status')
        .should('contain','has been created')
    })

    it('can fill up the form with distribution and submit', () => {
      // run cron to import new dataset
      cy.visit('/admin/config/system/cron')
      cy.get('#edit-run')
        .click({force: true})
      cy.contains('h1', 'Cron');
      cy.get('.messages--status', {timeout: 120000})
        .should('be.visible')

      // verify dataset was imported successfully
      dkan.verifyFileImportedSuccessfully(fileUrl.split('/').pop())
    })

    it('uploaded dataset files show remote link on edit', () => {
      cy.visit('/admin/dkan/datasets')
      cy.get('#edit-title').type(title)
      cy.get('#edit-submit-dkan-dataset-content').click()
      cy.get('.views-field-nothing > a').click()
      cy.contains('h1', 'Edit Data');
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl a')
        .invoke('attr', 'href')
        .should('eq', fileUrl)
    })
  })

  context('Create dataset with file upload', () => {
    const fileName = 'example.csv'
    const fileType = 'csv'
    const title = dkan.generateRandomString()
    // generate a separate upload file name to prevent name collisions across
    // tests
    const uploadedFileName = dkan.generateCSVFileName()

    beforeEach(() => {
      const user_credentials = Cypress.env('TEST_USER_CREDENTIALS')
      cy.drupalLogin(user_credentials.user, user_credentials.pass)
    })

    it('create the dataset', () => {
      const selectorDist = '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-upload'
      cy.visit('/node/add/data')
      cy.wait(2000)
      cy.get('#edit-field-json-metadata-0-value-title').type(title, { force:true } )
      cy.get('#edit-field-json-metadata-0-value-description').type('DKANTEST distribution description.', { force:true } )
      cy.get('#edit-field-json-metadata-0-value-accesslevel').select('public', { force:true } )
      cy.get('#edit-field-json-metadata-0-value-modified-date').type('2021-02-02', { force:true } )
      // Fill select2 field for publisher.
      cy.get('#edit-field-json-metadata-0-value-publisher-publisher-name + .select2')
        .find('.select2-selection')
        .click({ force:true })
      cy.get('input[aria-controls="select2-edit-field-json-metadata-0-value-publisher-publisher-name-results"]').type('DKANTEST Publisher{enter}')
      // End filling up publisher.
      cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-fn').type('DKANTEST Contact Name', { force:true } )
      cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail').type('dkantest@test.com', { force:true } )
      // Fill select2 field for keyword.
      cy.get('#edit-field-json-metadata-0-value-keyword-keyword-0 + .select2')
        .find('.select2-selection')
        .click({ force: true })
      cy.get('input[aria-controls="select2-edit-field-json-metadata-0-value-keyword-keyword-0-results"]').type('open data{enter}')
      // End filling up keyword.
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-title')
        .type('distribution title test', { force:true } )
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-description')
        .type('distribution description test', { force:true } )
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format-select')
        .select('csv', { force:true })
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-file-url-type-upload')
        .click({ force:true })

      cy.get(selectorDist).uploadFile(fileName, fileType, uploadedFileName)
      //wait for the file to be fully loaded
      cy.get('.file--mime-text-csv', {timeout: 120000})
        .should('be.visible')
      cy.get('#edit-submit')
        .click({ force:true })
      cy.get('.button').contains('Yes')
        .click({ force:true })
      cy.get('.messages--status')
        .should('contain','has been created')
    })


    it('can import dataset with uploaded file', () => {
      // run cron to import new dataset
      cy.visit('/admin/config/system/cron')
      cy.get('#edit-run')
        .click({force: true})
      cy.get('.messages--status', {timeout: 120000})
        .should('be.visible')

      // verify dataset was imported successfully
      dkan.verifyFileImportedSuccessfully(uploadedFileName)
    })

    it('uploaded dataset files show local link on edit', () => {
      // validate URL of uploaded CSV file
      cy.visit('/admin/dkan/datasets')
      cy.get('#edit-title').type(title)
      cy.get('#edit-submit-dkan-dataset-content').click()
      cy.get('tbody > :nth-child(1) > .views-field-nothing > a').click({force: true})
      cy.get('h1').should('contain', 'Edit Data')
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl a')
        .invoke('attr', 'href')
        .should('contain', `uploaded_resources/${uploadedFileName}`)
    })
  })

})
