context('Administration pages', () => {
  let baseurl = Cypress.config().baseUrl;
  beforeEach(() => {
      cy.drupalLogin('testeditor', 'testeditor')
  })

  it('I should see a link for the dataset properties configuration', () => {
    cy.visit(baseurl + "/admin")
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('Metastore referencer')
    })
    cy.visit(baseurl + "/admin/dkan/properties")
    cy.get('.option').should('contain.text', 'Distribution (distribution)')
  })

  it('I should see a link for the datastore configuration', () => {
    cy.visit(baseurl + "/admin")
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('Datastore settings')
    })
    cy.visit(baseurl + "/admin/dkan/datastore")
    cy.get('label[for="edit-rows-limit"]').should('have.text', 'Rows limit')
  })
})
