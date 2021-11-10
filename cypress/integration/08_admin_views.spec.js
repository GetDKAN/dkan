context('Admin content and dataset views', () => {
    let baseurl = Cypress.config().baseUrl;
    beforeEach(() => {
        cy.drupalLogin('testeditor', 'testeditor')
    })

    it('The admin content screen has an exposed data type filter that contains the values I expect.', () => {
        cy.visit(baseurl + "/admin/dkan/datasets")
        cy.get('h1').should('have.text', 'DKAN Metastore (Datasets)')
        cy.get('#edit-data-type').select('dataset',{ force: true }).should('have.value', 'dataset')
        cy.get('#edit-data-type').select('distribution',{ force: true }).should('have.value', 'distribution')
        cy.get('#edit-data-type').select('keyword',{ force: true }).should('have.value', 'keyword')
        cy.get('#edit-data-type').select('publisher',{ force: true }).should('have.value', 'publisher')
        cy.get('#edit-data-type').select('theme',{ force: true }).should('have.value', 'theme')
    })

    it('The admin content screen has bulk operations options.', () => {
        cy.visit(baseurl + "/admin/dkan/datasets")
        cy.get('#edit-action').select('Archive current revision',{ force: true }).should('have.value', 'archive_current')
        cy.get('#edit-action').select('Delete content',{ force: true }).should('have.value', 'node_delete_action')
        cy.get('#edit-action').select('Publish latest revision',{ force: true }).should('have.value', 'publish_latest')
    })

    it('The content table has a column for Data Type', () => {
        cy.visit(baseurl + "/admin/dkan/datasets")
        cy.get('#view-field-data-type-table-column > a').should('contain','Data Type');
    })

    it('There is a link in the admin menu to the datasets admin screen.', () => {
        cy.visit(baseurl + "/admin/dkan/datasets")
        cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=> {
            cy.wrap($el).invoke('show')
            cy.wrap($el).contains('Datasets')
        })
    })

    it('There is an "Add new dataset" button that takes user to the dataset json form.', () => {
        cy.visit(baseurl + "/admin/dkan/datasets")
        cy.get('h1').should('have.text', 'DKAN Metastore (Datasets)')
        cy.get('.view-header > .form-actions > .button').should('contain', 'Add new dataset').click({ force:true })
        cy.get('h1').should('have.text', 'Create Data')
    })

    it('User can archive, publish, and edit, and delete a dataset. The edit link on the admin view should go to the json form.', () => {
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
          .click({ force:true });
        cy.get('input[aria-controls="select2-edit-field-json-metadata-0-value-publisher-publisher-name-results"]').type('DKANTEST Publisher{enter}')
        // End filling up publisher.
        cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-fn').type('DKANTEST Contact Name', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail').type('dkantest@test.com', { force:true } )
        // Fill select2 field for keyword.
        cy.get('#edit-field-json-metadata-0-value-keyword-keyword-0 + .select2')
        .find('.select2-selection')
        .click({ force: true });
        cy.get('input[aria-controls="select2-edit-field-json-metadata-0-value-keyword-keyword-0-results"]').type('open data{enter}')
        // End filling up keyword.
        cy.get('#edit-submit').click({ force:true })
        cy.get('.messages--status').should('contain','has been created')
        cy.visit(baseurl + "/admin/dkan/datasets")
        cy.wait(2000)
        cy.get('tbody > :nth-child(1) > .views-field-status').should('contain', 'Published')
        cy.get('#edit-node-bulk-form-0').click({force:true})
        cy.get('#edit-action').select('Archive current revision',{ force: true }).should('have.value', 'archive_current')
        cy.get('#edit-submit--2').click({ force:true })
        cy.get('tbody > :nth-child(1) > .views-field-status').should('contain', 'Unpublished')
        cy.get('#edit-node-bulk-form-0').click({force:true})
        cy.get('#edit-action').select('Publish latest revision',{ force: true }).should('have.value', 'publish_latest')
        cy.get('#edit-submit--2').click({ force:true })
        cy.get('tbody > :nth-child(1) > .views-field-status').should('contain', 'Published')
        cy.get('tbody > :nth-child(1) > .views-field-nothing > a').invoke('attr', 'href').should('contain', '/edit')
        cy.get('tbody > :nth-child(1) > .views-field-nothing > a').click({ force: true })
        cy.get('h1').should('contain.text', 'Edit Data')
        cy.get('#edit-delete').click({ force:true })
        cy.get('#edit-submit').click({ force:true })
        cy.get('.messages--status').should('contain','has been deleted')
    })

  })
