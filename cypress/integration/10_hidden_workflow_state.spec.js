import * as dkan from '../support/helpers/dkan'

const baseurl = Cypress.config().baseUrl;

// create dataset
function createDataset(dataset_title, moderation_state) {
  // Create a dataset.
  cy.visit(baseurl + '/node/add/data')
  cy.get('#edit-field-json-metadata-0-value-title')
    .type(dataset_title, { force:true } )
  cy.get('#edit-field-json-metadata-0-value-description')
    .type('DKANTEST dataset description.', { force:true } )
  cy.get('#edit-field-json-metadata-0-value-accesslevel')
    .select('public', { force:true } )
  cy.get('#edit-field-json-metadata-0-value-modified-date')
    .type('2020-02-02', { force:true } )
  // Fill select2 field for publisher.
  cy.get('#edit-field-json-metadata-0-value-publisher-publisher-name + .select2')
    .find('.select2-selection')
    .click({ force:true })
  cy.get('input[aria-controls="select2-edit-field-json-metadata-0-value-publisher-publisher-name-results"]')
    .type('DKANTEST Publisher{enter}')
  // End filling up publisher.
  cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-fn')
    .type('DKANTEST Contact Name', { force:true } )
  cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail')
    .type('dkantest@test.com', { force:true } )
  // Fill select2 field for keyword.
  cy.get('#edit-field-json-metadata-0-value-keyword-keyword-0 + .select2')
    .find('.select2-selection')
    .click({ force: true })
  cy.get('input[aria-controls="select2-edit-field-json-metadata-0-value-keyword-keyword-0-results"]')
    .type('open data{enter}')
  cy.get('#edit-moderation-state-0-state')
    .select(moderation_state, { force:true } )
  // End filling up keyword.
  cy.get('#edit-submit')
    .click({ force:true })
  cy.get('.messages--status')
    .should('contain','has been created')
}

context('Hidden datasets', () => {
  beforeEach(() => {
    cy.drupalLogin('testeditor', 'testeditor')
  })

  it('Newly created hidden datasets are visible when visited directly, but hidden from the catalog.', () => {
    // create hidden dataset
    const dataset_title = dkan.generateRandomString()
    createDataset(dataset_title, 'hidden')
    cy.visit(baseurl + '/admin/dkan/datasets')
    cy.get('tbody > :nth-child(1) > .views-field-title > a').click()
    cy.should('contain', dataset_title)
    // ensure dataset is hidden from catalog
    dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
      expect(response.status).to.eq(200)
      expect(response.body.results).to.be.empty
    })
  })

  it('Existing datasets which are transitioned to hidden are visible when visited directly, but hidden from the catalog.', () => {
    // create published dataset
    const dataset_title = dkan.generateRandomString()
    createDataset(dataset_title, 'published')
    // ensure dataset is present in catalog
    dkan.searchMetastore({fulltext: dataset_title, facets: ''}).then((response) => {
      expect(response.status).to.eq(200)
      expect(response.body.total).to.eq('1')
    })

    // hide the published dataset
    cy.visit(baseurl + '/admin/dkan/datasets')
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
    cy.visit(baseurl + '/admin/dkan/datasets')
    cy.get('tbody > :nth-child(1) > .views-field-title > a').click()
    cy.should('contain', dataset_title)
  })

})
