import { generateDataset, createDataset, removeDatasets, getResourceIdentifier } from '../support/functions';

context('SQL Endpoint', () => {

  let datasetIdentifier;
  let userCredentials = Cypress.env('TEST_USER_CREDENTIALS');
  let apiUri = Cypress.config().apiUri;

  function importToDatastore(resourceIdentifier, userCredentials) {
    cy.request({
      method: 'POST',
      url: apiUri + '/datastore/imports',
      auth: userCredentials,
      body: {
        "resource_id": resourceIdentifier
      }
    }).then((response) => {
      expect(response.status).eql(200);
      expect(response.body.ResourceLocalizer.status).eql("done");
      expect(response.body.Import.status).eql("done");
    });
  }

  beforeEach(() => {
    // Cleaning up in case others didn't.
    removeDatasets(apiUri, userCredentials);

    const dataset = generateDataset();
    datasetIdentifier = dataset.identifier;
    createDataset(dataset, apiUri, userCredentials);

    getResourceIdentifier(datasetIdentifier, apiUri);

    cy.get('@resourceIdentifier').then((identifier) => {
      importToDatastore(identifier, userCredentials);
    })
  });

  // Clean up after ourselves.
  /*after(() => {
    removeDatasets()
  })*/

  context('SELECT', () => {

    it('All', () => {
      cy.get('@resourceIdentifier').then((identifier) => {

        let query = `[SELECT * FROM ${identifier}];`
        cy.request({
          method: 'POST',
          url: apiUri + '/datastore/sql',
          body: {
            "query": query,
            "show_db_columns": true
          }
        }).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.length).eql(2)
          cy.fixture('electionDistricts').then((json) => {
            json.properties.forEach((x) => {
              expect(response.body[0].hasOwnProperty(x)).equal(true)
            })
          })
        })
      })
    });

    it('Specific fields', () => {
      cy.get('@resourceIdentifier').then((identifier) => {
        let query = `[SELECT lon,lat FROM ${identifier}];`
        cy.request({
          method: 'POST',
          url: apiUri + '/datastore/sql',
          body: {
            "query": query,
            "show_db_columns": true
          }
        }).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.length).eql(2)
          let properties = [
            "lat",
            "lon"
          ]

          properties.forEach((x) => {
            expect(response.body[0].hasOwnProperty(x)).equal(true)
          })
          expect(response.body[0].hasOwnProperty("prov_id")).equal(false)
        })
      })
    });

  });


  context('WHERE', () => {
    it('Single condition', () => {
      cy.get('@resourceIdentifier').then((identifier) => {
        let query = `[SELECT * FROM ${identifier}][WHERE dist_name = "Pusht Rod"];`
        cy.request({
          method: 'POST',
          url: apiUri + '/datastore/sql',
          body: {
            "query": query,
            "show_db_columns": true
          }
        }).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.length).eql(1)
        })
      })
    });

    it('Multiple conditions', () => {

      cy.get('@resourceIdentifier').then((identifier) => {
        let query = `[SELECT * FROM ${identifier}][WHERE prov_name = "Farah" AND dist_name = "Pusht Rod"];`
        cy.request({
          method: 'POST',
          url: apiUri + '/datastore/sql',
          body: {
            "query": query,
            "show_db_columns": true
          }
        }).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.length).eql(1)
        })
      });

    })

  })

  context('ORDER BY', () => {

    it('Ascending explicit', () => {
      cy.get('@resourceIdentifier').then((identifier) => {
        let query = `[SELECT * FROM ${identifier}][ORDER BY dist_name ASC];`
        cy.request({
          method: 'POST',
          url: apiUri + '/datastore/sql',
          body: {
            "query": query,
            "show_db_columns": true
          }
        }).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.length).eql(2)
          expect(response.body[0].dist_name).eql("Pusht Rod")
        })
      })
    });

    it('Descending explicit', () => {
      cy.get('@resourceIdentifier').then((identifier) => {
        let query = `[SELECT * FROM ${identifier}][ORDER BY dist_name DESC];`
        cy.request({
          method: 'POST',
          url: apiUri + '/datastore/sql',
          body: {
            "query": query,
            "show_db_columns": true
          }
        }).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.length).eql(2)
          expect(response.body[0].dist_name).eql("Qala-e-Kah")
        })
      })
    })

  })

  context('LIMIT and OFFSET', () => {
    it('Limit only', () => {
      cy.get('@resourceIdentifier').then((identifier) => {
        let query = `[SELECT * FROM ${identifier}][ORDER BY dist_name ASC][LIMIT 1];`
        cy.request({
          method: 'POST',
          url: apiUri + '/datastore/sql',
          body: {
            "query": query,
            "show_db_columns": true
          }
        }).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.length).eql(1)
          expect(response.body[0].dist_name).eql("Pusht Rod")
        })
      })
    })

    it('Limit and offset', () => {
      cy.get('@resourceIdentifier').then((identifier) => {
        let query = `[SELECT * FROM ${identifier}][ORDER BY dist_name ASC][LIMIT 1 OFFSET 1];`
        cy.request({
          method: 'POST',
          url: apiUri + '/datastore/sql',
          body: {
            "query": query,
            "show_db_columns": true
          }
        }).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.length).eql(1)
          expect(response.body[0].dist_name).eql("Qala-e-Kah")
        })
      })
    })

  })

});
