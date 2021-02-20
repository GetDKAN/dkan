context('Admin content and dataset views', () => {
    let baseurl = Cypress.config().baseUrl;
    beforeEach(() => {
        cy.drupalLogin('testeditor', 'testeditor')
    })

    // DKAN Content View.
    it('The admin content screen has an exposed data type filter that contains the values I expect.', () => {
        cy.visit(baseurl + "/admin/content/node")
        cy.get('h1').should('have.text', 'Content')
        cy.get('#edit-data-type').select('dataset',{ force: true }).should('have.value', 'dataset')
        cy.get('#edit-data-type').select('distribution',{ force: true }).should('have.value', 'distribution')
        cy.get('#edit-data-type').select('keyword',{ force: true }).should('have.value', 'keyword')
        cy.get('#edit-data-type').select('publisher',{ force: true }).should('have.value', 'publisher')
        cy.get('#edit-data-type').select('theme',{ force: true }).should('have.value', 'theme')
    })

    it('The content table has a column for Data Type', () => {
        cy.visit(baseurl + "/admin/content/node")
        cy.get('#view-field-data-type-table-column > a').should('contain','Data Type');
    })

    it('The dataset data node titles should link to the drupal node page', () => {
        cy.visit(baseurl + "/admin/content/node")
        cy.get('#edit-data-type').select('dataset',{ force:true })
        cy.get('#edit-submit-dkan-content').click({ force:true })
        cy.get('tbody > :nth-child(1) > .views-field-title > a').invoke('attr', 'href').should('contain', '/node/');
    })

    it('There is a link in the admin menu to the datasets admin screen.', () => {
        cy.visit(baseurl + "/admin/content/node")
        cy.get('.toolbar-icon-system-admin-content').trigger('mouseover')
        cy.get('ul.toolbar-menu ul.toolbar-menu > .menu-item > a')
            .invoke('attr', 'href')
            .then(href => {
                cy.visit(href);
        });
        cy.get('h1').should('have.text', 'Datasets')
    })

    // DKAN Dataset view
    it('There is an "Add new dataset" button that takes user to the dataset json form.', () => {
        cy.visit(baseurl + "/admin/content/datasets")
        cy.get('h1').should('have.text', 'Datasets')
        cy.get('.view-header > .form-actions > .button').should('contain', 'Add new dataset').click({ force:true })
    })

    it('The dataset edit link should go to the json form.', () => {
        cy.visit(baseurl + "/admin/content/datasets")
        cy.get('tbody > :nth-child(1) > .views-field-nothing > a').invoke('attr', 'href').should('contain', '/edit');
    })

})
