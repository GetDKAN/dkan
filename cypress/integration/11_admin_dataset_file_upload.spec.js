context('Admin dataset file upload', () => {
  let baseurl = Cypress.config().baseUrl
  beforeEach(() => {
    cy.drupalLogin('testeditor', 'testeditor')
  })

  context('Create dataset with NetStorage upload', () => {
    it('can fill up the form with distribution and submit', () => {
      cy.visit(baseurl + "/node/add/data")
      cy.wait(2000)
      cy.get('#edit-field-json-metadata-0-value-title').type('DKANTEST remote file test', { force:true } )
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
        .type('https://download.medicaid.gov/data/wallpaper-banner.y7hy-xrtw.194ac9ce-07a3-5e94-a3ee-cfafaa164c2b.csv', { force:true })
      cy.get('#edit-submit')
        .click({ force:true })
      cy.get('.messages--status')
        .should('contain','has been created')

      //run Cron Job to get table populated on dataset page
      cy.visit('/admin/config/system/cron')
      cy.get('#edit-run')
        .click({force: true})
      cy.get('.messages--status', {timeout: 120000})
        .should('be.visible')

      //view dataset and verify that the preview table exists
      cy.visit('/admin/dkan/datasets')
      cy.get('#edit-title')
        .type('DKANTEST remote file test',{ force:true })
      cy.get('#edit-submit-dkan-dataset-content')
        .click({ force:true })
      cy.get('tbody > tr > .views-field-title > a')
        .should('be.visible')
        .click({ force:true })
      cy.get('.dc-resource > a', {timeout: 30000})
        .should('be.visible')
      cy.get('.dc-datatable > .dc-table', {timeout: 60000})
        .should('be.visible')
    })
  })

  context('Create dataset with file upload', () => {
    it('can fill up the form with distribution and submit', () => {
      const selectorDist = '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-downloadurl-upload'
      const fileName = 'example.csv'
      const fileType = 'csv'

      cy.visit(baseurl + '/node/add/data')
      cy.wait(2000)
      cy.get('#edit-field-json-metadata-0-value-title').type('DKANTEST distribution title file upload', { force:true } )
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
      cy.get(selectorDist).uploadFile(fileName, fileType)
      //wait for the file to be fully loaded
      cy.get('.file--mime-text-csv', {timeout: 120000})
        .should('be.visible')
      cy.get('#edit-submit')
        .click({ force:true })
      cy.get('.messages--status')
        .should('contain','has been created')
      // run cron job to get table populated on dataset page
      cy.visit('/admin/config/system/cron')
      cy.get('#edit-run')
        .click({force: true})
      cy.get('.messages--status', {timeout: 120000})
        .should('be.visible')

      cy.visit('/admin/dkan/datasets')
      cy.get('#edit-title')
        .type('DKANTEST distribution title file upload',{ force:true })
      cy.get('#edit-submit-dkan-dataset-content')
        .click({ force:true })
      cy.get('tbody > tr > .views-field-title > a')
        .should('be.visible')
        .click({ force:true })
      cy.get('.dc-resource > a', {timeout: 30000})
        .should('be.visible')
      cy.get('.dc-datatable > .dc-table', {timeout: 60000})
        .should('be.visible')
    })
  })

})
