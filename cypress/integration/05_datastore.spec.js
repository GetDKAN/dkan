context('Datastore API', () => {
  let expected_columns;
  let dataset_identifier;
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

  function removeDatasets() {
    let endpoint = apiUri + '/metastore/schemas/dataset/items';
    cy.request({
      method: 'GET',
      url: endpoint,
    }).then((response) => {
      let datasets = response.body;
      cy.log(datasets);
      datasets.forEach((dataset) => {
        cy.request({
          method: 'DELETE',
          url: endpoint + "/" + dataset.identifier,
          auth: user_credentials,
        })
      });
    })
  }

  async function getResourceIdentifier() {
    return cy.request(apiUri + '/metastore/schemas/dataset/items/' + dataset_identifier + '?show-reference-ids').then((response) => {
        expect(response.status).eql(200);
        return response.body.distribution[0].identifier;
      });
  }

  function getExpectedColumns() {
    cy.fixture('electionDistricts').then((json) => {
      expected_columns = json.properties
    })
  }

  function dropDatastores() {
    // Delete.
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
      modified: "2020-02-28",
      "@type": "dcat:Dataset",
      distribution: [
        {
          "@type": "dcat:Distribution",
          downloadURL: "https://dkan-default-content-files.s3.amazonaws.com/district_centerpoints_small.csv",
          mediaType: "text/csv",
          format: "csv",
          description: "<p>Nah.</p>",
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
    removeDatasets();
    createDataset();
  });

  it('Import', () => {
    getResourceIdentifier().then((resource_identifier) => {
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
    });

  });

  it('List', () => {
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
  });

  it('Get Info', () => {
    getResourceIdentifier().then((resource_identifier) => {
      cy.request(apiUri + '/datastore/imports/' + resource_identifier).then((response) => {
        expect(response.status).eql(200);
        expect(response.body.columns).eql(expected_columns);
        expect(response.body.numOfRows).eql(2);
        expect(response.body.numOfColumns).eql(6);
      });
    });
  });

});
