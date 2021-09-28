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

    it('The dataset edit link should go to the json form.', () => {
        cy.visit(baseurl + "/admin/dkan/datasets")
        cy.get('tbody > :nth-child(1) > .views-field-nothing > a').invoke('attr', 'href').should('contain', '/edit')
        cy.get('tbody > :nth-child(1) > .views-field-nothing > a').click({ force: true })
        cy.get('h1').should('contain.text', 'Edit Data')
    })

    it('The default results list only contains datasets.', () => {
        cy.visit(baseurl + "/admin/dkan/datasets")
        cy.get('tbody td.views-field-field-data-type').each(($el, index, $list) => {
            expect($el.text()).to.contains('dataset')
        })
    })

  })
