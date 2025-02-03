context('Administration pages', () => {
  let baseurl = Cypress.config().baseUrl;
  beforeEach(() => {
    const user_credentials = Cypress.env('TEST_USER_CREDENTIALS')
    cy.drupalLogin(user_credentials.user, user_credentials.pass)
    cy.visit(baseurl + "/admin/dkan")
  })

  it('DKAN menu contains expected links.', () => {
    const links = [
      'Datasets',
      'Datastore Import Status',
      'Datastore Settings',
      'Data Dictionary',
      'Harvests',
      'Metastore Settings',
      'Resource Settings'
    ]

    cy.visit(`${baseurl}/admin`)
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').parent().then(($menu) => {
      links.forEach((link) => {
        cy.get($menu).contains(link)
      })
    })
  })

  it('Admin can access the Metastore Settings.', () => {
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('Metastore Settings')
    })
    cy.visit(baseurl + "/admin/dkan/properties")
    cy.get('.option').should('contain.text', 'Distribution (distribution)')
  })

  it('Admin can access the Datastore Settings.', () => {
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('Datastore Settings')
    })
    cy.visit(baseurl + "/admin/dkan/datastore")
    cy.get('label[for="edit-rows-limit"]').should('have.text', 'Rows limit')
    cy.get('label[for="edit-response-stream-max-age"]').should('have.text', 'Response Stream Max-Age')
  })

  it('Admin can access the Datastore import status dashboard.', () => {
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('Datastore Import Status')
    })
    cy.visit(baseurl + "/admin/dkan/datastore/status")
    cy.contains('h1', 'Datastore Import Status');
  })

  it('Admin can access the Harvest dashboard', () => {
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('Harvests')
    })
    cy.visit(baseurl + "/admin/dkan/harvest")
    cy.contains('h1', 'Harvests');
  })

  it('Admin can access the dataset content view and can click a button to open the dataset form.', () => {
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=> {
      cy.wrap($el).invoke('show')
      cy.wrap($el).contains('Datasets').click({ force:true })
    })
    cy.contains('h1', 'Datasets');

    cy.get('.button').contains('+ Add new dataset').click( { force:true })
    cy.contains('h1', 'Create dataset');
  })

  it('DKAN menu contains link to create a dataset.', () => {
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=> {
      cy.wrap($el).invoke('show')
      cy.wrap($el).contains('Datasets').parent().within(() => {
        cy.get('li.menu-item a').contains('Create').click({ force:true })
      })
      cy.contains('h1', 'Create dataset')
    })
  })

  it('DKAN menu contains link to create a data dictionary.', () => {
    cy.get('.toolbar-icon-system-admin-dkan').parent().within(() => {
      cy.get('li.menu-item a').contains('Data Dictionary').click({ force:true })
    })
    cy.contains('h1', 'DKAN Metastore (Data Dictionaries)');
    cy.get('.button').contains('+ Add new data dictionary').click( { force:true })
    cy.get('fieldset').contains('Data Dictionary Fields');
    cy.contains('h1', 'Create data-dictionary')
  })

})
