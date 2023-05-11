context('Administration pages', () => {
  let baseurl = Cypress.config().baseUrl;
  beforeEach(() => {
    const user_credentials = Cypress.env('TEST_USER_CREDENTIALS')
    cy.drupalLogin(user_credentials.user, user_credentials.pass)
    cy.visit(baseurl + "/admin/dkan")
  })

  it('I should see a link for the dataset properties configuration', () => {
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('Metastore referencer')
    })
    cy.visit(baseurl + "/admin/dkan/properties")
    cy.get('.option').should('contain.text', 'Distribution (distribution)')
  })

  it('I should see a link for the datastore configuration', () => {
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('Datastore settings')
    })
    cy.visit(baseurl + "/admin/dkan/datastore")
    cy.get('label[for="edit-rows-limit"]').should('have.text', 'Rows limit')
  })

  it('I should see a link for the datastore status', () => {
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('Datastore Import Status')
    })
    cy.visit(baseurl + "/admin/dkan/datastore/status")
    cy.contains('h1', 'Datastore Import Status');
  })

  it('I should see a link for the harvest status', () => {
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('Harvests')
    })
    cy.visit(baseurl + "/admin/dkan/harvest")
    cy.contains('h1', 'Harvests');
  })

  it('There is a link in the admin menu to the datasets admin screen.', () => {
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=> {
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('Datasets')
    })
  })

})
