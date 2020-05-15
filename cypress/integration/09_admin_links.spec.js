context('Administration pages', () => {
  let baseurl = Cypress.config().baseUrl;
  beforeEach(() => {
      cy.drupalLogin('testeditor', 'testeditor')
  })

  it('I should see a link for the dataset properties configuration', () => {
    cy.visit(baseurl + "/user")
    cy.get('.toolbar-icon-system-admin-config').contains('Configuration').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('DKAN').next('.toolbar-menu').then($el=>{
          cy.wrap($el).invoke('show')
          cy.wrap($el).contains('Dataset properties').click()
          cy.get('.fieldset-legend').should('have.text', 'List of dataset properties with referencing and API endpoint')
        })
    })
  })

  it('I should see a link for the SQL endpoint configuration', () => {
    cy.visit(baseurl + "/user")
    cy.get('.toolbar-icon-system-admin-config').contains('Configuration').next('.toolbar-menu').then($el=>{
        cy.wrap($el).invoke('show')
        cy.wrap($el).contains('DKAN').next('.toolbar-menu').then($el=>{
          cy.wrap($el).invoke('show')
          cy.wrap($el).contains('SQL endpoint')
        })
    })
    cy.visit(baseurl + "/admin/config/dkan/sql_endpoint")
    cy.get('label').should('have.text', 'Rows limit')
  })
})
