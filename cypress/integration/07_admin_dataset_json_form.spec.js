context('Admin dataset json form', () => {
    let baseurl = Cypress.config().baseUrl;
    beforeEach(() => {
        cy.drupalLogin('testeditor', 'testeditor')
    })

    it('The dataset form has the correct required fields.', () => {
        cy.visit(baseurl + "/admin/dkan/dataset")
        cy.get('#root__title').should('have.text', 'Project Open Data Dataset')
        cy.get('#root .control-label[for="root_title"] > .required').should('be.visible')
        cy.get('#root .control-label[for="root_description"] > .required').should('be.visible')
        cy.get('#root .control-label[for="root_accessLevel"] > .required').should('be.visible')
        cy.get('#root .control-label[for="root_modified"] > .required').should('be.visible')
        cy.get('#root .control-label[for="root_publisher_name"] > .required').should('be.visible')
        cy.get('#root .control-label[for="root_contactPoint_fn"] > .required').should('be.visible')
        cy.get('#root .control-label[for="root_contactPoint_hasEmail"] > .required').should('be.visible')
        cy.get('#root_keyword__title > .required').should('be.visible')
    })

    it('User can create a dataset with the json form UI.', () => {
        cy.visit(baseurl + "/admin/dkan/dataset")
        cy.wait(2000)
        cy.get('#root_title').type('DKANTEST dataset title', { force:true } )
        cy.get('#root_description').type('DKANTEST dataset description.', { force:true } )
        cy.get('#root_accessLevel').select('public', { force:true } )
        cy.get('#root_modified').type('2020-02-02', { force:true } )
        cy.get('#root_publisher_name').type('DKANTEST Publisher', { force:true } )
        cy.get('#root_contactPoint_fn').type('DKANTEST Contact Name', { force:true } )
        cy.get('#root_contactPoint_hasEmail').type('mailto:dkantest@test.com', { force:true } )
        cy.get('#root_keyword_0').type('open data', { force:true } )
        cy.get('.btn-success').click({ force:true })
        cy.get('.toast-content-container > .toast-content').should('contain','has been created')
    })

    it('Admin user can edit a dataset with the json form UI.', () => {
        cy.visit(baseurl + "/admin/content/datasets")
        cy.get('#edit-title').type('DKANTEST dataset title', { force:true } )
        cy.get('#edit-submit-dkan-dataset-content').click({ force:true })
        cy.get('tbody > tr:first-of-type > .views-field-nothing > a').click({ force:true })
        cy.wait(2000)
        cy.get('#root_title').should('have.value','DKANTEST dataset title')
        cy.get('#root_title').type('NEW dkantest dataset title',{ force:true })
        cy.get('#root_accrualPeriodicity').select('Annual', { force:true })
        cy.get('#root_keyword > :nth-child(4) > .col-xs-3 > .btn').click({ force:true })
        cy.get('#root_keyword_1').type('testing', { force:true })
        cy.get(':nth-child(2) > .col-xs-3 > .btn-group > .array-item-move-up').click({ force:true })
        cy.get('#root_distribution > :nth-child(4) > .col-xs-3 > .btn').click({ force:true })
        cy.get('#root_distribution_0_title').type('DKANTEST distribution title text', { force:true })
        cy.get('#root_distribution_0_description').type('DKANTEST distribution description text', { force:true })
        cy.get('#root_distribution_0_format').type('csv', { force:true })
        cy.get('.btn-success').click({ force:true })
        cy.get('.toast-content').should('contain','has been updated')
    })

    it('Admin user can delete a dataset (cleanup)', () => {
        cy.visit(baseurl + "/admin/content/datasets")
        cy.wait(2000)
        cy.get('#edit-node-bulk-form-0').check({ force:true })
        cy.get('#edit-submit--2').click({ force:true })
        cy.get('input[value="Delete"]').click({ force:true })
        cy.get('.messages').should('contain','Deleted 1 content item.')
    })

})
