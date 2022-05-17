context('Admin dataset json form', () => {
    let baseurl = Cypress.config().baseUrl;
    beforeEach(() => {
      const user_credentials = Cypress.env('TEST_USER_CREDENTIALS')
      cy.drupalLogin(user_credentials.user, user_credentials.pass)
    })

    it('The dataset form has the correct required fields.', () => {
        cy.visit(baseurl + "/node/add/data")
        cy.get('#edit-field-json-metadata-0-value-title').should('have.attr', 'required', 'required')
        cy.get('#edit-field-json-metadata-0-value-description').should('have.attr', 'required', 'required')
        cy.get('#edit-field-json-metadata-0-value-accesslevel').should('have.attr', 'required', 'required')
        cy.get('#edit-field-json-metadata-0-value-modified-date').should('have.attr', 'required', 'required')
        cy.get('#edit-field-json-metadata-0-value-publisher-publisher-name').should('have.attr', 'required', 'required')
        cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-fn').should('have.attr', 'required', 'required')
        cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail').should('have.attr', 'required', 'required')
    })

    it('License and format fields are select or other elements in dataset form', () => {
      cy.visit(baseurl + '/node/add/data')
      cy.get('#edit-field-json-metadata-0-value-license-select').select('select_or_other', { force: true })
      cy.get('#edit-field-json-metadata-0-value-license-other.form-url').should('be.visible')
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format-select').select('select_or_other', { force: true })
      cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format-other.form-text').should('be.visible')
    })

    it('User can create and edit a dataset with the json form UI. User can delete a dataset.', () => {
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
        cy.get('#edit-actions').within(() => {
          cy.get('#edit-preview').should('not.exist')
        })
        cy.get('#edit-submit').click({ force:true })
        cy.get('.button').contains('Yes').click({ force:true });
        cy.get('.messages--status').should('contain','has been created')
        // Confirm the default dkan admin view is filtered to show only datasets.
        cy.visit(baseurl + "/admin/dkan/datasets")
        cy.get('tbody tr').each(($el) => {
          cy.wrap($el).within(() => {
            cy.get('td.views-field-field-data-type').should('contain', 'dataset')
          })
        })
        // Edit the dataset.
        cy.get('#edit-title').type('DKANTEST dataset title', { force:true } )
        cy.get('#edit-submit-dkan-dataset-content').click({ force:true })
        cy.get('tbody > tr:first-of-type > .views-field-nothing > a').click({ force:true })
        cy.wait(2000)
        cy.get('#edit-field-json-metadata-0-value-title').should('have.value','DKANTEST dataset title')
        cy.get('#edit-field-json-metadata-0-value-title').type('NEW dkantest dataset title',{ force:true })
        cy.get('#edit-field-json-metadata-0-value-accrualperiodicity').select('Annual', { force:true })
        cy.get('#edit-field-json-metadata-0-value-keyword-keyword-0 + .select2')
        .find('.select2-selection')
        .click({ force: true });
        cy.get('input[aria-controls="select2-edit-field-json-metadata-0-value-keyword-keyword-0-results"]').type('testing{enter}')
        cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-title').type('DKANTEST distribution title text', { force:true })
        cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-description').type('DKANTEST distribution description text', { force:true })
        cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format-select').select('csv', { force:true })
        cy.get('#edit-actions').within(() => {
          cy.get('#edit-preview').should('not.exist')
        })
        cy.get('#edit-submit').click({ force:true })
        cy.get('.button').contains('Yes').click({ force:true });
        cy.get('.messages--status').should('contain','has been updated')
        // Delete dataset.
        cy.visit(baseurl + "/admin/dkan/datasets")
        cy.wait(2000)
        cy.get('#edit-action').select('Delete content',{ force: true }).should('have.value', 'node_delete_action')
        cy.get('#edit-node-bulk-form-0').check({ force:true })
        cy.get('#edit-submit--2').click({ force:true })
        cy.get('.button').contains('Yes').click({ force:true });
        cy.get('input[value="Delete"]').click({ force:true })
        cy.get('.messages').should('contain','Deleted 1 content item.')
    })

})
