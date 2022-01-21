import * as dkan from '../support/helpers/dkan'

context('Archived datasets', () => {
  beforeEach(() => cy.drupalLogin('testeditor', 'testeditor'))

  it('Existing datasets which are archived cannot be visited, and are hidden from the catalog.', () => {
    // create published dataset
    const dataset_title = dkan.generateRandomString()
    dkan.createDatasetWithModerationState(dataset_title, 'published')
    // ensure dataset is present in catalog
    dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
      expect(response.status).to.eq(200)
      expect(response.body.total).to.eq('1')
    })

    // archive the published dataset
    cy.visit('/admin/dkan/datasets')
    cy.get('tbody > :nth-child(1) > .views-field-nothing > a').click()
    cy.get('#edit-moderation-state-0-state').select('archived')
    cy.get('#edit-submit').click()
    cy.get('.messages--status').should('contain', 'has been updated')
    // ensure dataset is hidden from catalog
    dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
      expect(response.status).to.eq(200)
      expect(response.body.results).to.be.empty
    })
    // ensure dataset details are not available when directly visited
    cy.visit('/admin/dkan/datasets')
    // prevent 404 error from causing cypress to fail the test
    Cypress.on('uncaught:exception', (err, runnable) => {
      console.log('test')
      console.log(err.description)
      return false
    })
    cy.get('tbody > :nth-child(1) > .views-field-title > a').click()
    cy.get('body').should('not.contain', dataset_title)
  })

})

context('Hidden datasets', () => {
  beforeEach(() => cy.drupalLogin('testeditor', 'testeditor'))

  it('Newly created hidden datasets are visible when visited directly, but hidden from the catalog.', () => {
    // create hidden dataset
    const dataset_title = dkan.generateRandomString()
    dkan.createDatasetWithModerationState(dataset_title, 'hidden')
    cy.visit('/admin/dkan/datasets')
    cy.get('tbody > :nth-child(1) > .views-field-title > a').click()
    cy.get('body').should('contain', dataset_title)
    // ensure dataset is hidden from catalog
    dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
      expect(response.status).to.eq(200)
      expect(response.body.results).to.be.empty
    })
  })

  it('Existing datasets which are transitioned to hidden are visible when visited directly, but hidden from the catalog.', () => {
    // create published dataset
    const dataset_title = dkan.generateRandomString()
    dkan.createDatasetWithModerationState(dataset_title, 'published')
    // ensure dataset is present in catalog
    dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
      expect(response.status).to.eq(200)
      expect(response.body.total).to.eq('1')
    })

    // hide the published dataset
    cy.visit('/admin/dkan/datasets')
    cy.get('tbody > :nth-child(1) > .views-field-nothing > a').click()
    cy.get('#edit-moderation-state-0-state').select('hidden')
    cy.get('#edit-submit').click()
    cy.get('.messages--status').should('contain', 'has been updated')
    // ensure dataset is hidden from catalog
    dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
      expect(response.status).to.eq(200)
      expect(response.body.results).to.be.empty
    })

    // ensure dataset details are available when directly visited
    cy.visit('/admin/dkan/datasets')
    cy.get('tbody > :nth-child(1) > .views-field-title > a').click()
    cy.should('contain', dataset_title)
  })

})
