context('Harvest', () => {

  let user_credentials = Cypress.env("TEST_USER_CREDENTIALS");
  let apiUri = Cypress.config().apiUri;

  // Set up.
  before(() => {

  });

  // Clean up.
  after(() => {

  });

  context('GET harvest/plans', () => {
    it('List harvest identifiers', () => {
      cy.request({
        url: apiUri + '/harvest/plans',
        auth: user_credentials
      }).then((response) => {
        expect(response.status).eql(200);
        expect(response.body.length).eql(0);
      })
    })

    it('Requires authenticated user', () => {
      cy.request({
        url: apiUri + '/harvest/plans',
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).eql(401)
      })
    });
  });

});
