context('Admin content and dataset views', () => {
    let baseurl = Cypress.config().baseUrl;
    beforeEach(() => {
        cy.drupalLogin('testeditor', 'testeditor')
    })

    it('Admin can create a dataset with the json form UI.', () => {
        cy.visit(baseurl + "/node/add/data")
        cy.wait(2000)
        cy.get('#edit-field-json-metadata-0-value-title').type('DKANTEST2 dataset title', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-description').type('DKANTEST2 dataset description.', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-accesslevel').select('public', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-modified').type('2020-02-02', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-publisher-publisher-name').type('DKANTEST2 Publisher', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-fn').type('DKANTEST2 Contact Name', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail').type('mailto:dkantest@test.com', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-keyword-keyword-0').type('open data', { force: true })
        cy.get('#edit-submit').click({ force:true })
        cy.get('.messages--status').should('contain','has been created')
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

    it('The dataset data node titles should link to the REACT dataset page', () => {
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

    it('Admin user can delete a dataset', () => {
        cy.visit(baseurl + "/admin/content/datasets")
        cy.wait(2000)
        cy.get('#edit-node-bulk-form-0').check({ force:true })
        cy.get('#edit-submit--2').click({ force:true })
        cy.get('input[value="Delete"]').click({ force:true })
        cy.get('.messages').should('contain','Deleted 1 content item.')
    })

    it('Admin user can delete a data node', () => {
        cy.visit(baseurl + "/admin/content/node")
        cy.wait(2000)
        cy.get('#edit-node-bulk-form-0').check({ force:true })
        cy.get('#edit-submit--2').click({ force:true })
        cy.get('input[value="Delete"]').click({ force:true })
        cy.get('.messages').should('contain','Deleted 1 content item.')
    })

})
