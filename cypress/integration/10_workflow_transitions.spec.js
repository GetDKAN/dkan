import * as dkan from '../support/helpers/dkan'

context('DKAN Workflow', () => {
  beforeEach(() => {
    const user_credentials = Cypress.env('TEST_USER_CREDENTIALS')
    cy.drupalLogin(user_credentials.user, user_credentials.pass)
  })

  it('Draft datasets are hidden from the catalog until published.', () => {
    // Create draft dataset
    const dataset_title = dkan.generateRandomString()
    dkan.createDatasetWithModerationState(dataset_title, 'draft')
    cy.get('@datasetId').then(datasetId => {

      // Ensure dataset is hidden from catalog
      dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.results).to.be.empty
      })

      // Ensure dataset details are not available when directly visited
      cy.request({
        url: '/api/1/metastore/schemas/dataset/items/' + datasetId,
        failOnStatusCode: false
      }).should((response) => {
        expect(response.status).to.eq(404)
        expect(response.body.message).to.contain(datasetId + ' not found')
      })

      // Publish the draft dataset
      cy.get('@nodeId').then((nodeId) => {
        cy.visit('/node/' + nodeId + '/edit')
      })
      cy.get('h1.page-title').should('contain', dataset_title)
      cy.get('#edit-moderation-state-0-state').select('published')
      cy.get('#edit-submit').click()
      cy.get('.button').contains('Yes').click({ force:true })
      cy.get('.messages--status').should('contain', 'has been updated')

      // Ensure dataset is visible via public API with correct title
      cy.request('/api/1/metastore/schemas/dataset/items/' + datasetId).should((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.title).to.eq(dataset_title)
      })

      // Ensure dataset is present in catalog
      dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.total).to.eq('1')
      })
    })
  })

  it('Existing datasets which are archived cannot be visited, and are hidden from the catalog.', () => {
    // Create published dataset
    const dataset_title = dkan.generateRandomString()
    dkan.createDatasetWithModerationState(dataset_title, 'published')
    cy.get('@datasetId').then(datasetId => {

      // Ensure dataset is visible via public API with correct title
      cy.request('/api/1/metastore/schemas/dataset/items/' + datasetId).should((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.title).to.eq(dataset_title)
      })

      // Ensure dataset is present in catalog
      dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.total).to.eq('1')
      })

      // Archive the published dataset
      cy.get('@nodeId').then((nodeId) => {
        cy.visit('/node/' + nodeId + '/edit')
      })
      cy.get('h1.page-title').should('contain', dataset_title)
      cy.get('#edit-moderation-state-0-state').select('archived')
      cy.get('#edit-submit').click()
      cy.get('.messages--status').should('contain', 'has been updated')

      // Ensure dataset is hidden from catalog
      dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.results).to.be.empty
      })

      // Ensure dataset details are not available when directly visited
      cy.request({
        url: '/api/1/metastore/schemas/dataset/items/' + datasetId,
        failOnStatusCode: false
      }).should((response) => {
        expect(response.status).to.eq(404)
        expect(response.body.message).to.contain(datasetId + ' not found')
      })
    })
  })

  it('Newly created hidden datasets are visible when visited directly, but hidden from the catalog.', () => {
    // create hidden dataset
    const dataset_title = dkan.generateRandomString()
    dkan.createDatasetWithModerationState(dataset_title, 'hidden')
    cy.get('@datasetId').then(datasetId => {

      // Ensure dataset is visible via public API with correct title
      cy.request('/api/1/metastore/schemas/dataset/items/' + datasetId).should((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.title).to.eq(dataset_title)
      })

      // Ensure dataset is hidden from catalog
      dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.results).to.be.empty
      })
    })
  })

  it('Existing datasets which are transitioned to hidden are visible when visited directly, but hidden from the catalog.', () => {
    // create published dataset
    const dataset_title = dkan.generateRandomString()
    // Create a new published dataset in UI and get the resulting UUID
    dkan.createDatasetWithModerationState(dataset_title, 'published')
    cy.get('@datasetId').then(datasetId => {

      // Ensure dataset is visible via public API with correct title
      cy.request('/api/1/metastore/schemas/dataset/items/' + datasetId).should((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.title).to.eq(dataset_title)
      })

      // Ensure dataset is visible in search
      dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.total).to.eq('1')
      })

      // Set the dataset workflow state to hidden
      cy.get('@nodeId').then((nodeId) => {
        cy.visit('/node/' + nodeId + '/edit')
      })
      cy.get('h1.page-title').should('contain', dataset_title)
      cy.get('#edit-moderation-state-0-state').select('hidden')
      cy.get('#edit-submit').click()
      cy.get('.messages--status').should('contain', 'has been updated')

      // Ensure dataset is now hidden from search
      dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.results).to.be.empty
      })

      // Ensure hidden dataset details are still available via public API
      cy.request('/api/1/metastore/schemas/dataset/items/' + datasetId).should((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.title).to.eq(dataset_title)
      })
    })
  })
})
