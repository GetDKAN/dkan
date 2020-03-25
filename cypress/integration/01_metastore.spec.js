context('Metastore', () => {

    // Generate a random uuid
    // Credit: https://stackoverflow.com/questions/105034/create-guid-uuid-in-javascript
    function uuid4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    // Generate a data item.
    function json(){
        let uuid = uuid4();
        return {
            title: "Title for " + uuid,
            description: "Description for " + uuid,
            identifier: uuid,
            accessLevel: "public",
            bureauCode: ["1234:56"],
            modified: "2020-02-28",
            "@type": "dcat:Dataset",
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

    let apiUri = Cypress.config().apiUri;
    let endpoint = apiUri + '/metastore/schemas/dataset/items';
    let user_credentials = Cypress.env("TEST_USER_CREDENTIALS");
    let json1              = json();
    let json2              = json();
    let jsonShouldNotExist = json();
    let jsonPost           = json();
    let jsonPut            = json();
    let jsonPatch          = json();

    let uuidRegex = new RegExp(Cypress.env('UUID_REGEX'));

    // Create two datasets with random uuid.
    before(function() {
        cy.request({
            method: 'POST',
            url: endpoint,
            auth: user_credentials,
            body: json1
        })
        cy.request({
            method: 'POST',
            url: endpoint,
            auth: user_credentials,
            body: json2
        })
    })

    // Clean up after ourselves.
    after(function() {
      cy.request({
        method: 'DELETE',
        url: endpoint + '/' + json1.identifier,
        auth: user_credentials
      })
      cy.request({
        method: 'DELETE',
        url: endpoint + '/' + json2.identifier,
        auth: user_credentials
      })
    })

    context('GET requests', () => {
        it('GET a non-existent dataset', () => {
            cy.request({
                url: endpoint + '/' + jsonShouldNotExist.identifier,
                failOnStatusCode: false
            }).then((response) => {
                expect(response.status).eql(404)
            })
        })
    })

    context('Dereference methods', () => {
      it('bad query parameter', () => {
        cy.request(endpoint + '/' + json1.identifier + '?view=foobar').then((response) => {
          expect(response.body.keyword).eql(json1.keyword)
        })
      })

      it('data (default)', () => {
        cy.request(endpoint + '/' + json1.identifier).then((response) => {
          expect(response.body.keyword).eql(json1.keyword)
        })
      })

      it('data+identifier', () => {
        cy.request(endpoint + '/' + json1.identifier + '?show-reference-ids').then((response) => {
          expect(response.body.keyword).not.eql(json1.keyword)
          expect(response.body.keyword.length).eql(json1.keyword.length)
          expect(response.body.keyword[0].identifier).to.match(uuidRegex)
          expect(response.body.keyword[0].data).eql(json1.keyword[0])
          expect(response.body.keyword[1].identifier).to.match(uuidRegex)
          expect(response.body.keyword[1].data).eql(json1.keyword[1])
          expect(response.body.keyword[2].identifier).to.match(uuidRegex)
          expect(response.body.keyword[2].data).eql(json1.keyword[2])
        })
      })
    })

    context('POST requests', () => {
      it('POST fails with no payload, or empty payload', () => {
        cy.request({
          method: 'POST',
          url: endpoint,
          auth: user_credentials,
          body: {
          },
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(400)
        })

        cy.request({
          method: 'POST',
          url: endpoint,
          auth: user_credentials,
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(400)
        })
      })
    })

    context('PUT requests', () => {
      it('PUT fails with no payload, or empty payload', () => {
        cy.request({
          method: 'PUT',
          url: endpoint + '/' + jsonShouldNotExist.identifier,
          auth: user_credentials,
          body: {
          },
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(400)
        })

        cy.request({
          method: 'PUT',
          url: endpoint + '/' + jsonShouldNotExist.identifier,
          auth: user_credentials,
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(400)
        })
      })

      it('PUT fails to modify the identifier', () => {
        cy.request({
          method: 'PUT',
          url: endpoint + '/' + json1.identifier,
          auth: user_credentials,
          body: jsonShouldNotExist,
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(409)
        })
      })

      it('PUT updates an existing dataset', () => {
        cy.request({
          method: 'PUT',
          url: endpoint + '/' + json1.identifier,
          auth: user_credentials,
          body: {
            title: json1.title + ", updated by PUT",
            description: "Description updated by PUT",
            identifier: json1.identifier,
            accessLevel: "public",
            modified: "2020-02-20",
            keyword: ["test"]
          }
        }).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.endpoint).eql("/" + endpoint + "/" + json1.identifier)
          expect(response.body.identifier).eql(json1.identifier)
        })
        // Verify expected title.
        cy.request(endpoint + '/' + json1.identifier).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.title).eql(json1.title + ", updated by PUT")
          expect(response.body.description).eql("Description updated by PUT")
        })
      })

      it('PUT creates a dataset, if non-existent', () => {
        cy.request({
          method: 'PUT',
          url: endpoint + '/' + jsonPut.identifier,
          auth: user_credentials,
          body: jsonPut
        }).then((response) => {
          expect(response.status).eql(201)
          expect(response.body.endpoint).eql("/" + endpoint + "/" + jsonPut.identifier)
        })
        // Verify data is as expected.
        cy.request(endpoint + '/' + jsonPut.identifier).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.title).eql(jsonPut.title)
          expect(response.body.description).eql(jsonPut.description)
          expect(response.body.identifier).eql(jsonPut.identifier)
        })
      })

      it('PUT fails to modify if data is the same', () => {
        cy.request({
          method: 'PUT',
          url: endpoint + '/' + jsonPut.identifier,
          auth: user_credentials,
          body: jsonPut,
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(403)
        })
        // Verify data has not been modified.
        cy.request(endpoint + '/' + jsonPut.identifier).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.title).eql(jsonPut.title)
          expect(response.body.description).eql(jsonPut.description)
          expect(response.body.identifier).eql(jsonPut.identifier)
        })
        })
    })

    context('PATCH requests', () => {
      it('PATCH fails for non-existent dataset', () => {
        cy.request({
          method: 'PATCH',
          url: endpoint + '/' + jsonShouldNotExist.identifier,
          auth: user_credentials,
          body: jsonShouldNotExist,
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(412)
        })
      })

      it('PATCH fails to modify the identifier', () => {
        cy.request({
          method: 'PATCH',
          url: endpoint + '/' + json2.identifier,
          auth: user_credentials,
          body: {
            title: "Title Updated By PATCH",
            identifier: jsonPatch.identifier
          },
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(409)
        })
      })

      it('PATCH - empty payload', () => {
        cy.request({
          method: 'PATCH',
          url: endpoint + '/' + json2.identifier,
          auth: user_credentials,
          body: { },
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(200)
        })
        // Verify data is as expected.
        cy.request(endpoint + '/' + json2.identifier).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.title).eql(json2.title)
          expect(response.body.description).eql(json2.description)
          expect(response.body.identifier).eql(json2.identifier)
        })
      })

      it('PATCH - basic case', () => {
        cy.request({
          method: 'PATCH',
          url: endpoint + '/' + json2.identifier,
          auth: user_credentials,
          body: {
            description: "Description updated by PATCH."
          }
        }).then((response) => {
          expect(response.status).eql(200)
        })
        // Verify expected title.
        cy.request(endpoint + '/' + json2.identifier).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.description).eql("Description updated by PATCH.")
        })
      })

      it('PATCH modifies array elements (add, remove, edit)', () => {
        cy.request({
          method: 'PATCH',
          url: endpoint + '/' + json2.identifier,
          auth: user_credentials,
          body: {
            keyword: ["firsttag", "third", "fourthtag"]
          }
        }).then((response) => {
          expect(response.status).eql(200)
        })
        // Verify expected data: added, removed, edited and left unchanged.
        cy.request(endpoint + '/' + json2.identifier).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.keyword).to.contain("firsttag")
          expect(response.body.keyword).to.not.contain("secondtag")
          expect(response.body.keyword).to.contain("third")
          expect(response.body.keyword).to.contain("fourthtag")
        })
      })

      it('PATCH modifies object properties (add, remove, edit)', () => {
        cy.request({
          method: 'PATCH',
          url: endpoint + '/' + json2.identifier,
          auth: user_credentials,
          body: {
            contactPoint: {
              fn: "Contact's name updated by PATCH",
              "@type": null,
              newKey: "new value"
            }
          }
        }).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.endpoint).eql("/" + endpoint + "/" + json2.identifier)
          expect(response.body.identifier).eql(json2.identifier)
        })
        // Verify expected data: added, removed, edited and left unchanged.
        cy.request(endpoint + '/' + json2.identifier).then((response) => {
          expect(response.status).eql(200)
          expect(response.body.contactPoint["fn"]).eql("Contact's name updated by PATCH")
          expect(response.body.contactPoint["@type"]).to.be.undefined
          expect(response.body.contactPoint.hasEmail).eql("mailto:first.last@example.com")
          expect(response.body.contactPoint.newKey).eql("new value")
        })
      })
    })

    context('DELETE requests', () => {
      it('DELETE existing datasets', () => {
        cy.request({
          method: 'DELETE',
          url: endpoint + '/' + jsonPost.identifier,
          auth: user_credentials
        }).then((response) => {
          expect(response.status).eql(200)
        })
        cy.request({
          url: endpoint + '/' + jsonPost.identifier,
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(404)
        })

        cy.request({
          method: 'DELETE',
          url: endpoint + '/' + jsonPut.identifier,
          auth: user_credentials
        }).then((response) => {
          expect(response.status).eql(200)
        })
        cy.request({
          url: endpoint + '/' + jsonPut.identifier,
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(404)
        })
      })
    })
})
