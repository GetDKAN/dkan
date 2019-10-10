context('SQL Endpoint', () => {

  let dataset_identifier;
  let resource_identifier;
  let user_credentials = Cypress.env('TEST_USER_CREDENTIALS');
  let apiUri = Cypress.config().apiUri;

  // Create a dataset.
  function createDataset() {
    let endpoint = apiUri + '/metastore/schemas/dataset/items';
    let json1 = json();
    cy.request({
      method: 'POST',
      url: endpoint,
      auth: user_credentials,
      body: json1
    })
  }

  function removeDataset() {
    let endpoint = apiUri + '/metastore/schemas/dataset/items';
    cy.request({
      method: 'DELETE',
      url: endpoint + '/' + dataset_identifier,
      auth: user_credentials
    })
  }

  function importToDatastore() {
    cy.log(resource_identifier);
    cy.request({
      method: 'POST',
      url: apiUri + '/datastore/imports',
      auth: user_credentials,
      body: {
        "resource_id": resource_identifier
      }
    }).then((response) => {
      expect(response.status).eql(200);
      expect(response.body.Resource.status).eql("done");
      expect(response.body.Import.status).eql("done");
    });
  }

  function dropFromDatastore() {
    cy.request({
      method: 'DELETE',
      url: apiUri + '/datastore/imports/' + resource_identifier,
      auth: user_credentials
    }).then((response) => {
      expect(response.status).eql(200);
    });
  }

  // Generate a random uuid.
  // Credit: https://stackoverflow.com/questions/105034/create-guid-uuid-in-javascript
  function uuid4() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    });
  }

  // Generate a data item.
  function json(){
    dataset_identifier = uuid4();
    let uuid =  dataset_identifier;
    return {
      title: "Title for " + uuid,
      description: "Description for " + uuid,
      identifier: uuid,
      accessLevel: "public",
      bureauCode: ["1234:56"],
      "@type": "dcat:Dataset",
      distribution: [
        {
          "@type": "dcat:Distribution",
          downloadURL: "http://demo.getdkan.com/sites/default/files/district_centerpoints_0.csv",
          mediaType: "text/csv",
          format: "csv",
          description: "<p>You can see this data plotted on a map, by clicking on 'Map' below. Individual data records can be seen by clicking on each point.</p>",
          title: "District Names SQL"
        }
      ]
    }
  }

  before(() => {
    createDataset();
    cy.fixture('electionDistricts').then((json) => {
      cy.request(apiUri + '/metastore/schemas/dataset/items/' + dataset_identifier + '?show-reference-ids').then((response) => {
        expect(response.status).eql(200);
        resource_identifier = response.body.distribution[0].identifier;
        expect(resource_identifier).to.match(new RegExp(Cypress.env('UUID_REGEX')));
        importToDatastore();
      });
    })

  });

  // Clean up after ourselves.
  after(() => {
    dropFromDatastore();
    removeDataset()
  })

  context('SELECT', () => {
    it('All', () => {
      let query = `[SELECT * FROM ${resource_identifier}];`
      cy.request({
        method: 'GET',
        url: apiUri + '/datastore/sql',
        body: {
          "query": query
        }
      }).then((response) => {
        expect(response.status).eql(200)
        expect(response.body.length).eql(399)
        cy.fixture('electionDistricts').then((json) => {
          json.properties.forEach((x) => {
            expect(response.body[0].hasOwnProperty(x)).equal(true)
          })
        })
      })
    })

    it('Specific fields', () => {
      let query = `[SELECT lon,lat FROM ${resource_identifier}];`
      cy.request({
        method: 'GET',
        url: apiUri + '/datastore/sql',
        body: {
          "query": query
        }
      }).then((response) => {
        expect(response.status).eql(200)
        expect(response.body.length).eql(399)
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
  })

  context('WHERE', () => {
    it('Single condition', () => {
      let query = `[SELECT * FROM ${resource_identifier}][WHERE prov_name = 'Farah'];`
      cy.request({
        method: 'GET',
        url: apiUri + '/datastore/sql',
        body: {
          "query": query
        }
      }).then((response) => {
        expect(response.status).eql(200)
        expect(response.body.length).eql(11)
      })
    })

    it('Multiple conditions', () => {
      let query = `[SELECT * FROM ${resource_identifier}][WHERE prov_name = 'Farah' AND dist_name = 'Farah'];`
      cy.request({
        method: 'GET',
        url: apiUri + '/datastore/sql',
        body: {
          "query": query
        }
      }).then((response) => {
        expect(response.status).eql(200)
        expect(response.body.length).eql(1)
      })
    })

  })

  context('ORDER BY', () => {

    it('Ascending explicit', () => {
      let query = `[SELECT * FROM ${resource_identifier}][ORDER BY prov_name ASC];`
      cy.request({
        method: 'GET',
        url: apiUri + '/datastore/sql',
        body: {
          "query": query
        }
      }).then((response) => {
        expect(response.status).eql(200)
        expect(response.body.length).eql(399)
        expect(response.body[0].prov_name).eql("Badakhshan")
      })
    })

    it('Descending explicit', () => {
      let query = `[SELECT * FROM ${resource_identifier}][ORDER BY prov_name DESC];`
      cy.request({
        method: 'GET',
        url: apiUri + '/datastore/sql',
        body: {
          "query": query
        }
      }).then((response) => {
        expect(response.status).eql(200)
        expect(response.body.length).eql(399)
        expect(response.body[0].prov_name).eql("Zabul")
      })
    })

  })

  context('LIMIT and OFFSET', () => {
    it('Limit only', () => {
      let query = `[SELECT * FROM ${resource_identifier}][ORDER BY prov_name ASC][LIMIT 5];`
      cy.request({
        method: 'GET',
        url: apiUri + '/datastore/sql',
        body: {
          "query": query
        }
      }).then((response) => {
        expect(response.status).eql(200)
        expect(response.body.length).eql(5)
        expect(response.body[0].prov_name).eql("Badakhshan")
      })
    })

    it('Limit and offset', () => {
      let query = `[SELECT * FROM ${resource_identifier}][ORDER BY prov_name ASC][LIMIT 5 OFFSET 100];`
      cy.request({
        method: 'GET',
        url: apiUri + '/datastore/sql',
        body: {
          "query": query
        }
      }).then((response) => {
        expect(response.status).eql(200)
        expect(response.body.length).eql(5)
        expect(response.body[0].prov_name).eql("Faryab")
      })
    })

  })

})
