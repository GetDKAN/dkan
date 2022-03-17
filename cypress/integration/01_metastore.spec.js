import * as dkan from '../support/helpers/dkan'
const api_uri = Cypress.config('apiUri')
const user_credentials = Cypress.env('TEST_USER_CREDENTIALS')
const uuid_regex = new RegExp(Cypress.env('UUID_REGEX'))

context('Metastore', () => {
  context('Catalog', () => {
    it('Should contain newly created datasets', () => {
      dkan.createMetastore('dataset').then((response) => {
        expect(response.status).eql(201)
        const identifier = response.body.identifier
        cy.request('data.json').then((response) => {
          expect(response.status).eql(200)
          cy.wrap(response.body)
            .its('dataset')
            .should('not.be.empty')
            .then((list) => Cypress._.map(list, 'identifier'))
            .should('include', identifier)
        })
      })
    })

    it('Corresponds to catalog shell', () => {
      cy.request('data.json').then((response) => {
        expect(response.status).eql(200)
        expect(response.body["@context"]).eql("https://project-open-data.cio.gov/v1.1/schema/catalog.jsonld")
        expect(response.body["@id"]).eql("http://dkan/data.json")
        expect(response.body["@type"]).eql("dcat:Catalog")
        expect(response.body.conformsTo).eql("https://project-open-data.cio.gov/v1.1/schema")
        expect(response.body.describedBy).eql("https://project-open-data.cio.gov/v1.1/schema/catalog.json")
      })
    })
  })

  context('Dereference methods', () => {
    const schema_id = 'dataset'

    it('bad query parameter', () => {
      const item = dkan.generateMetastore(schema_id)
      dkan.createMetastore(schema_id, item).then((response) => {
        cy.request(dkan.getMetastoreGetEndpoint(schema_id, response.body.identifier) + '?view=foobar').then((response) => {
          expect(response.body.keyword).eql(item.keyword)
        })
      })
    })

    it('data (default)', () => {
      const item = dkan.generateMetastore(schema_id)
      dkan.createMetastore(schema_id, item).then((response) => {
        cy.request(dkan.getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
          expect(response.body.keyword).eql(item.keyword)
        })
      })
    })

    it('data+identifier', () => {
      const item = dkan.generateMetastore(schema_id)
      dkan.createMetastore(schema_id, item).then((response) => {
        cy.request(dkan.getMetastoreGetEndpoint(schema_id, item.identifier) + '?show-reference-ids').then((response) => {
          expect(response.body.keyword).not.eql(item.keyword)
          expect(response.body.keyword.length).eql(item.keyword.length)
          expect(response.body.keyword[0].identifier).to.match(uuid_regex)
          expect(response.body.keyword[0].data).eql(item.keyword[0])
          expect(response.body.keyword[1].identifier).to.match(uuid_regex)
          expect(response.body.keyword[1].data).eql(item.keyword[1])
          expect(response.body.keyword[2].identifier).to.match(uuid_regex)
          expect(response.body.keyword[2].data).eql(item.keyword[2])
        })
      })
    })
  })

  dkan.metastore_schemas.forEach((schema_id) => {
    context(`Metastore Item Creation (${schema_id})`, () => {
      it(`Create a ${schema_id}`, () => {
        dkan.createMetastore(schema_id).then((response) => {
          expect(response.status).eql(201)
          expect(response.body.identifier).to.match(uuid_regex)
        })
      })

      it('Create request fails with an empty payload', () => {
        cy.request({
          method: 'POST',
          url: dkan.getMetastoreCreateEndpoint(schema_id),
          auth: user_credentials,
          body: {},
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(400)
        })
      })

      it('Create request fails with no payload', () => {
        cy.request({
          method: 'POST',
          url: dkan.getMetastoreCreateEndpoint(schema_id),
          auth: user_credentials,
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(400)
        })
      })
    })

    context(`Metastore Item Retrieval (${schema_id})`, () => {
      it(`GET a non-existent ${schema_id}`, () => {
        cy.request({
          url: dkan.getMetastoreGetEndpoint(schema_id, dkan.generateRandomString()),
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(404)
        })
      })
    })

    context(`Metastore Item Replacement (${schema_id})`, () => {
      it('PUT fails with an empty payload', () => {
        cy.request({
          method: 'PUT',
          url: dkan.getMetastorePutEndpoint(schema_id, dkan.generateRandomString()),
          auth: user_credentials,
          body: {},
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(400)
        })
      })

      it('PUT fails with no payload', () => {
        cy.request({
          method: 'PUT',
          url: dkan.getMetastorePutEndpoint(schema_id, dkan.generateRandomString()),
          auth: user_credentials,
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(400)
        })
      })

      it('PUT fails to modify the identifier', () => {
        dkan.createMetastore(schema_id).then((response) => {
          expect(response.status).eql(201)
          cy.request({
            method: 'PUT',
            url: dkan.getMetastorePutEndpoint(schema_id, response.body.identifier),
            auth: user_credentials,
            body: dkan.generateMetastore(schema_id),
            failOnStatusCode: false
          }).then((response) => {
            expect(response.status).eql(409)
          })
        })
      })

      it('PUT updates an existing dataset', () => {
        dkan.createMetastore(schema_id).then((response) => {
          expect(response.status).eql(201)
          const new_item = dkan.generateMetastore(schema_id, response.body.identifier)
          cy.request({
            method: 'PUT',
            url: dkan.getMetastorePutEndpoint(schema_id, response.body.identifier),
            auth: user_credentials,
            body: new_item
          }).then((response) => {
            expect(response.status).eql(200)
            expect(response.body.endpoint).eql(dkan.getMetastoreGetEndpoint(schema_id, new_item.identifier))
            expect(response.body.identifier).eql(response.body.identifier)
            // Verify item.
            cy.request(dkan.getMetastoreGetEndpoint(schema_id, response.body.identifier)).then((response) => {
              expect(response.status).eql(200)
              expect(response.body).eql(new_item)
            })
          })
        })
      })

      it('PUT creates a dataset, if non-existent', () => {
        const item = dkan.generateMetastore(schema_id)
        const endpoint = dkan.getMetastorePutEndpoint(schema_id, item.identifier)
        cy.request({
          method: 'PUT',
          url: endpoint,
          auth: user_credentials,
          body: item
        }).then((response) => {
          expect(response.status).eql(201)
          expect(response.body.endpoint).eql(endpoint)
          // Verify item.
          cy.request(dkan.getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
            expect(response.status).eql(200)
            expect(response.body).eql(item)
          })
        })
      })

      it('PUT fails to modify if data is the same', () => {
        const item = dkan.generateMetastore(schema_id)
        dkan.createMetastore(schema_id, item).then((response) => {
          cy.request({
            method: 'PUT',
            url: dkan.getMetastorePutEndpoint(schema_id, item.identifier),
            auth: user_credentials,
            body: item,
            failOnStatusCode: false
          }).then((response) => {
            expect(response.status).eql(403)
            // Verify data has not been modified.
            cy.request(dkan.getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
              expect(response.status).eql(200)
              expect(response.body).eql(item)
            })
          })
        })
      })
    })

    context(`Metastore Item Replacement (${schema_id})`, () => {
      it('PATCH fails for non-existent dataset', () => {
        const identifier = dkan.generateRandomString(schema_id)
        cy.request({
          method: 'PATCH',
          url: dkan.getMetastorePatchEndpoint(schema_id, identifier),
          auth: user_credentials,
          body: dkan.generateMetastore(schema_id, identifier),
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(412)
        })
      })

      it('PATCH fails to modify the identifier', () => {
        dkan.createMetastore(schema_id).then((response) => {
          expect(response.status).eql(201)
          cy.request({
            method: 'PATCH',
            url: dkan.getMetastorePatchEndpoint(schema_id, response.body.identifier),
            auth: user_credentials,
            body: {
              identifier: dkan.generateRandomString(schema_id)
            },
            failOnStatusCode: false
          }).then((response) => {
            expect(response.status).eql(409)
          })
        })
      })

      it('PATCH - empty payload', () => {
        const item = dkan.generateMetastore(schema_id)
        dkan.createMetastore(schema_id, item).then((response) => {
          expect(response.status).eql(201)
          cy.request({
            method: 'PATCH',
            url: dkan.getMetastorePatchEndpoint(schema_id, item.identifier),
            auth: user_credentials,
            body: {},
            failOnStatusCode: false
          }).then((response) => {
            expect(response.status).eql(200)
            // Verify data.
            cy.request(dkan.getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
              expect(response.status).eql(200)
              expect(response.body).eql(item)
            })
          })
        })
      })

      it('PATCH - basic case', () => {
        dkan.createMetastore(schema_id).then((response) => {
          expect(response.status).eql(201)
          const item = dkan.generateMetastore(schema_id, response.body.identifier)
          cy.request({
            method: 'PATCH',
            url: dkan.getMetastorePatchEndpoint(schema_id, response.body.identifier),
            auth: user_credentials,
            body: item
          }).then((response) => {
            expect(response.status).eql(200)
            // Verify item.
            cy.request(dkan.getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
              expect(response.status).eql(200)
              expect(response.body).eql(item)
            })
          })
        })
      })
    })

    context(`Metastore Item Deletion (${schema_id})`, () => {
      it('Delete existing datasets', () => {
        dkan.createMetastore(schema_id).then((response) => {
          const identifier = response.body.identifier
          cy.request({
            method: 'DELETE',
            url: dkan.getMetastoreDeleteEndpoint(schema_id, identifier),
            auth: user_credentials
          }).then((response) => {
            expect(response.status).eql(200)
            cy.request({
              url: dkan.getMetastoreGetEndpoint(schema_id, identifier),
              failOnStatusCode: false
            }).then((response) => {
              expect(response.status).eql(404)
            })
          })
        })
      })
    })
  })

  context('Metastore Item Replacement (dataset; edge cases)', () => {
    const schema_id = 'dataset'
    it('PATCH modifies array elements (add, remove, edit)', () => {
      const item = dkan.generateMetastore(schema_id)
      dkan.createMetastore(schema_id, item).then((response) => {
        expect(response.status).eql(201)
        const new_keywords = [
          dkan.generateRandomString(),
          dkan.generateRandomString()
        ]
        cy.request({
          method: 'PATCH',
          url: dkan.getMetastorePatchEndpoint(schema_id, item.identifier),
          auth: user_credentials,
          body: {
            keyword: new_keywords
          }
        }).then((response) => {
          expect(response.status).eql(200)
          // Verify expected data: added, removed, edited and left unchanged.
          cy.request(dkan.getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
            expect(response.status).eql(200)
            new_keywords.forEach((keyword) => expect(response.body.keyword).to.contain(keyword))
            item.keyword.forEach((keyword) => expect(response.body.keyword).to.not.contain(keyword))
          })
        })
      })
    })

    it('PATCH modifies object properties (add, remove, edit)', () => {
      const item = dkan.generateMetastore(schema_id)
      dkan.createMetastore(schema_id, item).then((response) => {
        expect(response.status).eql(201)
        const new_keywords = [
          dkan.generateRandomString(),
          dkan.generateRandomString()
        ]
        const endpoint = dkan.getMetastorePatchEndpoint(schema_id, item.identifier)
        cy.request({
          method: 'PATCH',
          url: endpoint,
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
          expect(response.body.endpoint).eql(endpoint)
          expect(response.body.identifier).eql(item.identifier)
          // Verify expected data: added, removed, edited and left unchanged.
          cy.request(dkan.getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
            expect(response.status).eql(200)
            expect(response.body.contactPoint["fn"]).eql("Contact's name updated by PATCH")
            expect(response.body.contactPoint["@type"]).to.be.undefined
            expect(response.body.contactPoint.hasEmail).eql(item.contactPoint.hasEmail)
            expect(response.body.contactPoint.newKey).eql("new value")
          })
        })
      })
    })
  })
})
