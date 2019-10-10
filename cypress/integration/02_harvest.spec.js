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

  context('POST harvest/plans', () => {
    it.skip('Register a new harvest', () => {

    });

    it('Requires authenticated user', () => {
      cy.request({
        url: apiUri + '/harvest/plans',
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).eql(401)
      })
    })
  });

  context('GET harvest/plans/PLAN_ID', () => {
    it.skip('Get a single harvest plan'), () => {

    };

    it('Requires authenticated user', () => {
      cy.request({
        url: apiUri + '/harvest/runs',
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).eql(401)
      })
    })
  });

  context('GET harvest/runs?plan=PLAN_ID', () => {
    it.skip('Gives list of previous runs for a harvest id', () => {

    });

    it('Requires authenticated user', () => {
      cy.request({
        url: apiUri + '/harvest/runs?plan=PLAN_ID',
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).eql(401)
      })
    })
  });

  context('POST harvest/runs', () => {
    it.skip('Run a harvest', () => {

    });

    it('Requires authenticated user', () => {
      cy.request({
        url: apiUri + '/harvest/runs',
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).eql(401)
      })
    })
  });

  context('GET harvest/runs/{identifier}', () => {
    it.skip('Gives information about a single previous harvest run', () => {

    });

    it('Requires authenticated user', () => {
      cy.request({
        url: apiUri + '/harvest/runs',
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).eql(401)
      })
    })
  });

});
