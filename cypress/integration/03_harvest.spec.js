context('Harvest', () => {

  let user_credentials = Cypress.env("TEST_USER_CREDENTIALS");
  let apiUri = Cypress.config().apiUri;

  // Set up.
  before(() => {
    cy.request({
      method: 'POST',
      url: apiUri + '/harvest/plans',
      auth: user_credentials,
      body: {
        "identifier": "test",
        "extract": {
          "type": "\\Harvest\\ETL\\Extract\\DataJson",
          "uri": "https://dkan-default-content-files.s3.amazonaws.com/data.json"
        },
        "load": {
          "type": "\\Drupal\\harvest\\Load\\Dataset"
        }
      }
    }).then((response) => {
      expect(response.status).eql(200)
    })
  });

  // Clean up.
  after(() => {
    cy.request({
      method: 'DELETE',
      url: apiUri + '/harvest/plans/test',
      auth: user_credentials,
    }).then((response) => {
      expect(response.status).eql(200)
    })
  });

  context('GET harvest/plans', () => {
    it('List harvest identifiers', () => {
      cy.request({
        url: apiUri + '/harvest/plans',
        auth: user_credentials
      }).then((response) => {
        expect(response.status).eql(200);
        expect(response.body.length).eql(1);
        expect(response.body[0]).eql('test');
        cy.request({
          method: "POST",
          url: apiUri + '/harvest/runs',
          auth: user_credentials,
          body: {
            "plan_id": "test"
          }
        }).then((response) => {
          expect(response.status).eql(200);
          cy.log(response)
        })
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
    it('Requires authenticated user', () => {
      cy.request({
        method: "POST",
        url: apiUri + '/harvest/plans',
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).eql(401)
      })
    })
  });

  context('GET harvest/plans/PLAN_ID', () => {
    it('Get a single harvest plan', () => {
      cy.request({
        url: apiUri + '/harvest/plans/test',
        auth: user_credentials
      }).then((response) => {
        expect(response.status).eql(200)
        expect(response.body.identifier).eql('test')
      })
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

  context('GET harvest/runs?plan=PLAN_ID', () => {
    it('Gives list of previous runs for a harvest id', () => {
      cy.request({
        url: apiUri + '/harvest/runs?plan=test',
        auth: user_credentials
      }).then((response) => {
        expect(response.status).eql(200)
        expect(response.body.length).eql(1)
      })
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
    it('Requires authenticated user', () => {
      cy.request({
        method: "POST",
        url: apiUri + '/harvest/runs',
        failOnStatusCode: false
      }).then((response) => {
        expect(response.status).eql(401)
      })
    })
  });

  context('GET harvest/runs/{identifier}', () => {
    it('Gives information about a single previous harvest run', () => {
      cy.request({
        url: apiUri + '/harvest/runs?plan=test',
        auth: user_credentials
      }).then((response) => {
        expect(response.status).eql(200)
        let run_id = response.body[0]
        cy.request({
          url: apiUri + '/harvest/runs/' + run_id + '?plan=test',
          auth: user_credentials
        }).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.status.extract).eql("SUCCESS")
        })
      })
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
