import * as dkan from '../support/helpers/dkan'

context('Admin content and dataset views', () => {
    let baseurl = Cypress.config().baseUrl;

    beforeEach(() => {
        const user_credentials = Cypress.env('TEST_USER_CREDENTIALS')
        cy.drupalLogin(user_credentials.user, user_credentials.pass)
        cy.visit(baseurl + "/admin/dkan/datasets")
    })

    it('The admin content screen has an exposed data type filter that contains the values I expect.', () => {
        cy.get('#edit-data-type').select('dataset',{ force: true }).should('have.value', 'dataset')
        cy.get('#edit-data-type').select('distribution',{ force: true }).should('have.value', 'distribution')
        cy.get('#edit-data-type').select('keyword',{ force: true }).should('have.value', 'keyword')
        cy.get('#edit-data-type').select('publisher',{ force: true }).should('have.value', 'publisher')
        cy.get('#edit-data-type').select('theme',{ force: true }).should('have.value', 'theme')
    })

    it('Confirm the admin view has expected items.', () => {
        // The content table has a column for Data Type.
        cy.get('#view-field-data-type-table-column > a').should('contain','Data Type');
        // There is an "Add new dataset" button that takes user to the dataset json form.
        cy.get('.view-header > .form-actions > .button').should('contain', 'Add new dataset').click({ force:true })
        cy.contains('h1', 'Create Data');
    })

    it('User can create a dataset with the UI.', () => {
        // Create a dataset.
        cy.visit(baseurl + "/node/add/data")
        cy.wait(2000)
        cy.get('#edit-field-json-metadata-0-value-title').type('DKANTEST dataset title', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-description').type('DKANTEST dataset description.', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-accesslevel').select('public', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-modified-date').type('2020-02-02', { force:true } )
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
        cy.get('#edit-submit').click({ force:true })
        cy.get('.button').contains('Yes').click({ force:true })
        cy.get('.messages--status').should('contain','has been created')
    })

    it('Test moderation bulk operations', () => {
        dkan.createDatasetWithModerationState('Testing bulk operations', 'published')
        cy.visit(baseurl + "/admin/dkan/datasets")
        cy.wait(2000)
        cy.get('tbody > :nth-child(1) > .views-field-status').should('contain', 'Published')
        cy.get('tbody > :nth-child(1) > .views-field-moderation-state', {timeout: 2000}).should('contain', 'Published')
        // Change the state of the first dataset from published to published hidden.
        cy.get('#edit-node-bulk-form-0').click({force:true})
        cy.get('#edit-action').select('Hide current revision',{ force: true }).should('have.value', 'hide_current')
        cy.get('#edit-submit--2').click({ force:true })
        cy.get('.button').contains('Yes').click({ force:true })
        cy.get('tbody > :nth-child(1) > .views-field-status').should('contain', 'Published')
        cy.get('tbody > :nth-child(1) > .views-field-moderation-state', {timeout: 2000}).should('contain', 'Published (hidden)')
         // Change the state of the first dataset from hidden to archived.
        cy.get('#edit-node-bulk-form-0').click({force:true})
        cy.get('#edit-action').select('Archive current revision',{ force: true }).should('have.value', 'archive_current')
        cy.get('#edit-submit--2').click({ force:true })
        cy.get('.button').contains('Yes').click({ force:true });
        cy.get('tbody > :nth-child(1) > .views-field-status', {timeout: 2000}).should('contain', 'Unpublished')
        cy.get('tbody > :nth-child(1) > .views-field-moderation-state', {timeout: 2000}).should('contain', 'Archived')
         // Change the state of the first dataset from archived to published.
        cy.get('#edit-node-bulk-form-0').click({force:true})
        cy.get('#edit-action').select('Publish latest revision',{ force: true }).should('have.value', 'publish_latest')
        cy.get('#edit-submit--2').click({ force:true })
        cy.get('.button').contains('Yes').click({ force:true })
        cy.get('tbody > :nth-child(1) > .views-field-status', {timeout: 2000}).should('contain', 'Published')
        cy.get('tbody > :nth-child(1) > .views-field-moderation-state', {timeout: 2000}).should('contain', 'Published')
         // Delete the dataset.
        cy.get('#edit-node-bulk-form-0').click({force:true})
        cy.get('#edit-action').select('Delete content',{ force: true }).should('have.value', 'node_delete_action')
        cy.get('#edit-submit--2').click({ force:true })
        cy.get('.button').contains('Yes').click({ force:true })
        cy.get('#edit-submit').click({ force:true })
        cy.get('.messages--status').should('contain','Deleted 1 content item.')
    })

  })
