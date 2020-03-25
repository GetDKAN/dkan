context('Admin content and dataset views', () => {
    let baseurl = Cypress.config().baseUrl;
    beforeEach(() => {
        cy.drupalLogin('testeditor', 'testeditor')
    })

    // Create a datasest.
    it('Admin can create a dataset with the json form UI.', () => {
        cy.visit(baseurl + "/admin/dkan/dataset")
        cy.wait(2000)
        cy.get('#root_title').type('DKANTEST2 dataset title', { force:true } )
        cy.get('#root_description').type('DKANTEST2 dataset description.', { force:true } )
        cy.get('#root_accessLevel').select('public', { force:true } )
        cy.get('#root_modified').type('2020-02-02', { force:true } )
        cy.get('#root_publisher_name').type('DKANTEST2 Publisher', { force:true } )
        cy.get('#root_contactPoint_fn').type('DKANTEST2 Contact Name', { force:true } )
        cy.get('#root_contactPoint_hasEmail').type('mailto:dkantest@test.com', { force:true } )
        cy.get('#root_keyword_0').type('open data', { force:true } )
        cy.get('.btn-success').click({ force:true })
        cy.get('.toast-content-container > .toast-content').should('contain','has been created')
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
        cy.get('.vbo-table > thead > tr > #view-field-data-type-table-column > a').should('contain','Data Type');
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
    it('There is an "Add new dataset" button that takes user to the dataset json form. And a "Back to Datasets" button that returns user to the datasets view.', () => {
        cy.visit(baseurl + "/admin/content/datasets")
        cy.get('h1').should('have.text', 'Datasets')
        cy.get('.view-header > .button').should('contain', 'Add new dataset').click({ force:true })
        cy.get('#app > button.btn-default').should('contain', 'Back to Datasets').click({ force:true })
        cy.get('h1').should('have.text', 'Datasets')
    })

    it('The dataset data node titles should link to the REACT page. The edit link should go to the json form.', () => {
        cy.visit(baseurl + "/admin/content/datasets")
        //cy.get('tbody > :nth-child(1) > .views-field-title > a').invoke('attr', 'href').should('contain', '/dataset/')
        cy.get('tbody > :nth-child(1) > .views-field-nothing > a').invoke('attr', 'href').should('contain', 'admin/dkan/dataset?id=');
    })

    it('Admin user can delete a dataset', () => {
        cy.visit(baseurl + "/admin/content/datasets")
        cy.wait(2000)
        cy.get('#edit-node-bulk-form-0').check({ force:true })
        cy.get('#edit-submit--2').click({ force:true })
        cy.get('input[value="Delete"]').click({ force:true })
        cy.get('.messages').should('contain','Deleted 1 content item.')
    })

})