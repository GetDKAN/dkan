import { generateDataset, createDataset, removeDatasets, getResourceIdentifier } from '../support/functions';

context('Datastore API', () => {
  let datasetIdentifier;
  let userCredentials = Cypress.env('TEST_USER_CREDENTIALS');
  let apiUri = Cypress.config().apiUri;

  beforeEach(() => {
    cy.fixture('electionDistricts').its('properties').as('columns');

    // Cleaning up in case others didn't.
    removeDatasets(apiUri, userCredentials);

    const dataset = generateDataset();
    datasetIdentifier = dataset.identifier;
    createDataset(dataset, apiUri, userCredentials);

    getResourceIdentifier(datasetIdentifier, apiUri);
  });

  after(() => {
    removeDatasets(apiUri, userCredentials);
    cy.request({
        method: 'GET',
        failOnStatusCode: false,
        url: apiUri +'/metastore/schemas/dataset/items/' + datasetIdentifier + '?show-reference-ids'
      })
      .its('status')
      .should(($status) => expect($status).eql(404));
  });

  it('Import', () => {
    cy.get('@resourceIdentifier')
      .then((identifier) => {
        cy.request({
            method: 'POST',
            url: apiUri + '/datastore/imports',
            auth: userCredentials,
            body: {
              "resource_id": identifier
            }
        })
          .then((response) => {
            expect(response.status).eql(200);
            expect(response.body.ResourceLocalizer.status).eql("done");
            expect(response.body.Import.status).eql("done");
          });
      });
  });

  it('List', () => {
    cy.request({
      url: apiUri + '/datastore/imports',
      auth: userCredentials
    }).then((response) => {
      let firstKey = Object.keys(response.body)[0];
      expect(response.status).eql(200);
      expect(response.body[firstKey].hasOwnProperty('fileFetcher')).equals(true);
      expect(response.body[firstKey].hasOwnProperty('fileFetcherStatus')).equals(true);
      expect(response.body[firstKey].hasOwnProperty('fileName')).equals(true);
    })
  });

  it('Get Info', () => {
    cy.get('@resourceIdentifier')
      .then((identifier) => {
        cy.request(apiUri + '/datastore/imports/' + identifier)
          .then((response) => {
            expect(response.status).eql(200);
            expect(response.body.numOfRows).eql(2);
            expect(response.body.numOfColumns).eql(6);
            cy.get('@columns').then((columns) => expect(Object.keys(response.body.columns)).eql(columns));
          });
      });
  });

});
