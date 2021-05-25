context('Administration pages', () => {
  let baseurl = Cypress.config().baseUrl;
  beforeEach(() => {
      cy.drupalLogin('testeditor', 'testeditor')
  })

  it('I should see a link for the dataset properties configuration', () => {
    cy.visit(baseurl + "/admin")
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('Configure referencing')
    })
    cy.visit(baseurl + "/admin/dkan/properties")
    cy.get('.option').should('contain.text', 'Distribution (distribution)')
  })

  it('I should see a link for the SQL endpoint configuration', () => {
    cy.visit(baseurl + "/admin")
    cy.get('.toolbar-icon-system-admin-dkan').contains('DKAN').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('SQL endpoint')
    })
    cy.visit(baseurl + "/admin/dkan/sql_endpoint")
    cy.get('label').should('have.text', 'Rows limit')
  })
})
