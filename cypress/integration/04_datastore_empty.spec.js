context('Datastore API - Empty', () => {

  let apiUri = Cypress.config().apiUri;
  let resource_identifier = "blah";

  it('GET empty', () => {
    cy.request({
      url: apiUri + '/datastore/imports/' + resource_identifier,
      failOnStatusCode: false
    }).then((response) => {
      expect(response.body.message).eql("A datastore for resource " + resource_identifier + " does not exist.")
    })
  });

  it('GET openapi api spec', () => {
    cy.request(apiUri + '/datastore').then((response) => {
      expect(response.status).eql(200);
      expect(response.body.hasOwnProperty('openapi')).equals(true);
    })
  });

});
