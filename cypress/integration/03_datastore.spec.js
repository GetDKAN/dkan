context('Datastore API', () => {
  let expected_columns;
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
          title: "District Names"
        }
      ],
      keyword: [
        "firsttag",
        "secondtag",
        "thirdtag"
      ],
      contactPoint: {
        "@type": "vcard:Contact",
        fn: "Firstname Lastname",
        hasEmail: "mailto:first.last@example.com"
      }
    }
  }

  before(() => {
    createDataset();
    cy.fixture('electionDistricts').then((json) => {
      cy.request(apiUri + '/metastore/schemas/dataset/items/' + dataset_identifier + '?show-reference-ids').then((response) => {
        expect(response.status).eql(200);
        resource_identifier = response.body.distribution[0].identifier;
        expect(resource_identifier).to.match(new RegExp(Cypress.env('UUID_REGEX')));
      });
      expected_columns = json.properties
    })
  });

  // Clean up after ourselves.
  after(() => {
    removeDataset()
  })

  it('GET empty', () => {
    cy.request({
      url: apiUri + '/datastore/imports/' + resource_identifier,
      failOnStatusCode: false
    }).then((response) => {
      expect(response.body.message).eql("A datastore for resource " + resource_identifier + " does not exist.")
    })
  });

  it('Import, List, Get Info, and Delete', () => {
    // Import.
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

    // List.
    cy.request({
      url: apiUri + '/datastore/imports',
      auth: user_credentials
    }).then((response) => {
      let firstKey = Object.keys(response.body)[0];
      expect(response.status).eql(200);
      expect(response.body[firstKey].hasOwnProperty('fileFetcher')).equals(true);
      expect(response.body[firstKey].hasOwnProperty('fileFetcherStatus')).equals(true);
      expect(response.body[firstKey].hasOwnProperty('fileName')).equals(true);
    })

    // Get Info.
    cy.request(apiUri + '/datastore/imports/' + resource_identifier).then((response) => {
      expect(response.status).eql(200);
      expect(response.body.columns).eql(expected_columns);
      expect(response.body.numOfRows).eql(399);
      expect(response.body.numOfColumns).eql(9);
    });

    // Delete.
    cy.request({
      method: 'DELETE',
      url: apiUri + '/datastore/imports/' + resource_identifier,
      auth: user_credentials
    }).then((response) => {
      expect(response.status).eql(200);
    });
  });

  it('GET openapi api spec', () => {
    cy.request(apiUri + '/datastore').then((response) => {
      expect(response.status).eql(200);
      expect(response.body.hasOwnProperty('openapi')).equals(true);
    })
  });

});
