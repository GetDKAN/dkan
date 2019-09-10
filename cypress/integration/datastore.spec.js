context('Datastore API', () => {
  let expected_columns = [
    "lon",
    "lat",
    "unit_type",
    "dist_name",
    "prov_name",
    "dari_dist",
    "dari_prov",
    "dist_id",
    "prov_id"
  ];
  let datastore_endpoint = "http://dkan/api/v1/datastore";
  let dataset_endpoint = "http://dkan/api/v1/dataset";
  let dataset_identifier = "c9e2d352-e24c-4051-9158-f48127aa5692";
  let resource_identifier = null
  let user_credentials = {
    user: 'testuser',
    pass: '2jqzOAnXS9mmcLasy'
  };

  before(() => {
    cy.request(dataset_endpoint + '/' + dataset_identifier + '?values=both').then((response) => {
      resource_identifier = response.body.distribution[0].identifier
    })
  })

  it('GET - Empty', () => {
    cy.request({
        url: datastore_endpoint + '/' + resource_identifier,
        failOnStatusCode: false
      }
    ).then((response) => {
      expect(response.body.message).eql("A datastore for resource " + resource_identifier + " does not exist.")
    })
  })

  it('Import, Get Info, and Delete', () => {
    cy.request({
        method: 'PUT',
        url: datastore_endpoint +'/import/' + resource_identifier,
        auth: user_credentials
    }).then((response) => {
      expect(response.status).eql(200);
      expect(response.body.FileFetcherResult.status).eql("done");
      expect(response.body.ImporterResult.status).eql("done");
    })

    cy.request(datastore_endpoint + '/' + resource_identifier).then((response) => {
      expect(response.status).eql(200);
      expect(response.body.columns).eql(expected_columns)
      expect(response.body.numOfRows).eql(399);
      expect(response.body.numOfColumns).eql(9)
    })

    cy.request({
        method: 'DELETE',
        url: datastore_endpoint +'/' + resource_identifier,
        auth: user_credentials
    }).then((response) => {
      expect(response.status).eql(200);
    })

    cy.request({
        url: datastore_endpoint + '/' + resource_identifier,
        failOnStatusCode: false
    }).then((response) => {
      expect(response.body.message).eql("A datastore for resource " + resource_identifier + " does not exist.")
    })

  })

  it('PUT - Deferred Import', () => {
    cy.request({
      method: 'PUT',
      url: datastore_endpoint +'/import/' + resource_identifier + '/deferred',
      auth: user_credentials
    }).then((response) => {
        expect(response.status).eql(200);
    })
  })

});
