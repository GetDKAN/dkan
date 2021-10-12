context('Metastore', () => {

  const api_uri = Cypress.config('apiUri')
  const user_credentials = Cypress.env('TEST_USER_CREDENTIALS')
  const uuid_regex = new RegExp(Cypress.env('UUID_REGEX'))
  const metastore_schemas = [
    'dataset',
    'publisher',
    'distribution',
    'theme',
    'keyword',
    'data-dictionary',
  ]

  function getMetastoreCreateEndpoint (schema_id) {
    return `/${api_uri}/metastore/schemas/${schema_id}/items`
  }

  function getMetastoreGetEndpoint (schema_id, identifier) {
    return `/${api_uri}/metastore/schemas/${schema_id}/items/${identifier}`
  }

  function getMetastorePutEndpoint (schema_id, identifier) {
    return `/${api_uri}/metastore/schemas/${schema_id}/items/${identifier}`
  }

  function getMetastorePatchEndpoint (schema_id, identifier) {
    return `/${api_uri}/metastore/schemas/${schema_id}/items/${identifier}`
  }

  function getMetastoreDeleteEndpoint (schema_id, identifier) {
    return `/${api_uri}/metastore/schemas/${schema_id}/items/${identifier}`
  }

  // Generate a random uuid.
  // Credit: https://stackoverflow.com/questions/105034/create-guid-uuid-in-javascript
  function generateMetastoreIdentifier () {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8)
      return v.toString(16)
    })
  }

  function generateRandomString () {
    return generateMetastoreIdentifier()
  }

  function generateRandomDateString () {
    const start = new Date('1970-01-01T00:00:00.000Z')
    const end = new Date()
    const date = new Date(+start + Math.random() * (end - start))

    const year = date.getFullYear().toString()
    const month = date.getMonth().toString().padStart(2, '0')
    const day = date.getDate().toString().padStart(2, '0')

    return year + '-' + month + '-' + day
  }

  // Create a metastore item via API.
  function createMetastore (schema_id, item = null) {
    item = item || generateMetastore(schema_id)
    // Lookup the proper metastore creation procedure for the given schema ID.
    return cy.request({
      method: 'POST',
      url: `${api_uri}/metastore/schemas/${schema_id}/items`,
      auth: user_credentials,
      body: item
    })
  }

  function generateMetastore (schema_id, identifier = null) {
    // Generate a unique metastore identifier if one was not supplied.
    identifier = identifier || generateMetastoreIdentifier()
    // Lookup the proper metastore generation procedure for the given schema ID.
    const metastore_generator_dictionary = {
      "dataset": generateDataset,
      "publisher": generatePublisher,
      "distribution": generateDistribution,
      "theme": generateTheme,
      "keyword": generateKeyword,
      "data-dictionary": generateDataDictionary,
    }
    return metastore_generator_dictionary[schema_id](identifier)
  }

  // Generate a metastore dataset item object.
  function generateDataset(uuid) {
    return {
      title: "Title for " + uuid,
      description: "Description for " + uuid,
      identifier: uuid,
      accessLevel: "public",
      bureauCode: ["1234:56"],
      modified: generateRandomDateString(),
      "@type": "dcat:Dataset",
      distribution: [
        {
          "@type": "dcat:Distribution",
          downloadURL: "https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv",
          mediaType: "text/csv",
          format: "csv",
          description: `<p>${generateRandomString()}</p>`,
          title: generateRandomString()
        }
      ],
      keyword: [
        generateRandomString(),
        generateRandomString(),
        generateRandomString()
      ],
      contactPoint: {
        "@type": "vcard:Contact",
        fn: generateRandomString() + " " + generateRandomString(),
        hasEmail: "mailto:first.last@example.com"
      }
    }
  }

  // Generate a metastore publisher item object.
  function generatePublisher(uuid) {
    return {
      "identifier": uuid,
      "data": {
        "@type": "org:Organization",
        "name": generateRandomString(),
        "subOrganizationOf": generateRandomString()
      }
    }
  }

  function generateDistribution(uuid) {
    return {
      "identifier": uuid,
      "data": {
        "title": "Title for " + uuid,
        "description": `<p>${generateRandomString()}</p>`,
        "format": "csv",
        "mediaType": "text/csv",
        "downloadURL": "https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv",
      }
    }
  }

  function generateTheme(uuid) {
    return {
      "identifier": uuid,
      "data": generateRandomString()
    }
  }

  function generateKeyword(uuid) {
    return {
      "identifier": uuid,
      "data": generateRandomString()
    }
  }

  // Generate a metastore data-dictionary item object.
  function generateDataDictionary(uuid) {
    return {
      "identifier": uuid,
      "title": "Title for " + uuid,
      "data": {
        "fields": [
          {
            "name": generateRandomString(),
            "title": generateRandomString(),
            "type": "string",
            "format": "default"
          }
        ]
      }
    }
  }

  context('Catalog', () => {
    it('Should contain newly created datasets', () => {
      createMetastore('dataset').then((response) => {
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
      const item = generateMetastore(schema_id)
      createMetastore(schema_id, item).then((response) => {
        cy.request(getMetastoreGetEndpoint(schema_id, response.body.identifier) + '?view=foobar').then((response) => {
          expect(response.body.keyword).eql(item.keyword)
        })
      })
    })

    it('data (default)', () => {
      const item = generateMetastore(schema_id)
      createMetastore(schema_id, item).then((response) => {
        cy.request(getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
          expect(response.body.keyword).eql(item.keyword)
        })
      })
    })

    it('data+identifier', () => {
      const item = generateMetastore(schema_id)
      createMetastore(schema_id, item).then((response) => {
        cy.request(getMetastoreGetEndpoint(schema_id, item.identifier) + '?show-reference-ids').then((response) => {
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

  metastore_schemas.forEach((schema_id) => {
    context(`Metastore Item Creation (${schema_id})`, () => {
      it(`Create a ${schema_id}`, () => {
        createMetastore(schema_id).then((response) => {
          expect(response.status).eql(201)
          expect(response.body.identifier).to.match(uuid_regex)
        })
      })

      it('Create request fails with an empty payload', () => {
        cy.request({
          method: 'POST',
          url: getMetastoreCreateEndpoint(schema_id),
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
          url: getMetastoreCreateEndpoint(schema_id),
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
          url: getMetastoreGetEndpoint(schema_id, generateRandomString()),
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
          url: getMetastorePutEndpoint(schema_id, generateRandomString()),
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
          url: getMetastorePutEndpoint(schema_id, generateRandomString()),
          auth: user_credentials,
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(400)
        })
      })

      it('PUT fails to modify the identifier', () => {
        createMetastore(schema_id).then((response) => {
          expect(response.status).eql(201)
          cy.request({
            method: 'PUT',
            url: getMetastorePutEndpoint(schema_id, response.body.identifier),
            auth: user_credentials,
            body: generateMetastore(schema_id),
            failOnStatusCode: false
          }).then((response) => {
            expect(response.status).eql(409)
          })
        })
      })

      it('PUT updates an existing dataset', () => {
        createMetastore(schema_id).then((response) => {
          expect(response.status).eql(201)
          const new_item = generateMetastore(schema_id, response.body.identifier)
          cy.request({
            method: 'PUT',
            url: getMetastorePutEndpoint(schema_id, response.body.identifier),
            auth: user_credentials,
            body: new_item
          }).then((response) => {
            expect(response.status).eql(200)
            expect(response.body.endpoint).eql(getMetastoreGetEndpoint(schema_id, new_item.identifier))
            expect(response.body.identifier).eql(response.body.identifier)
            // Verify item.
            cy.request(getMetastoreGetEndpoint(schema_id, response.body.identifier)).then((response) => {
              expect(response.status).eql(200)
              expect(response.body).eql(new_item)
            })
          })
        })
      })

      it('PUT creates a dataset, if non-existent', () => {
        const item = generateMetastore(schema_id)
        const endpoint = getMetastorePutEndpoint(schema_id, item.identifier)
        cy.request({
          method: 'PUT',
          url: endpoint,
          auth: user_credentials,
          body: item
        }).then((response) => {
          expect(response.status).eql(201)
          expect(response.body.endpoint).eql(endpoint)
          // Verify item.
          cy.request(getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
            expect(response.status).eql(200)
            expect(response.body).eql(item)
          })
        })
      })

      it('PUT fails to modify if data is the same', () => {
        const item = generateMetastore(schema_id)
        createMetastore(schema_id, item).then((response) => {
          cy.request({
            method: 'PUT',
            url: getMetastorePutEndpoint(schema_id, item.identifier),
            auth: user_credentials,
            body: item,
            failOnStatusCode: false
          }).then((response) => {
            expect(response.status).eql(403)
            // Verify data has not been modified.
            cy.request(getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
              expect(response.status).eql(200)
              expect(response.body).eql(item)
            })
          })
        })
      })
    })

    context(`Metastore Item Replacement (${schema_id})`, () => {
      it('PATCH fails for non-existent dataset', () => {
        const identifier = generateRandomString(schema_id)
        cy.request({
          method: 'PATCH',
          url: getMetastorePatchEndpoint(schema_id, identifier),
          auth: user_credentials,
          body: generateMetastore(schema_id, identifier),
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).eql(412)
        })
      })

      it('PATCH fails to modify the identifier', () => {
        createMetastore(schema_id).then((response) => {
          expect(response.status).eql(201)
          cy.request({
            method: 'PATCH',
            url: getMetastorePatchEndpoint(schema_id, response.body.identifier),
            auth: user_credentials,
            body: {
              identifier: generateRandomString(schema_id)
            },
            failOnStatusCode: false
          }).then((response) => {
            expect(response.status).eql(409)
          })
        })
      })

      it('PATCH - empty payload', () => {
        const item = generateMetastore(schema_id)
        createMetastore(schema_id, item).then((response) => {
          expect(response.status).eql(201)
          cy.request({
            method: 'PATCH',
            url: getMetastorePatchEndpoint(schema_id, item.identifier),
            auth: user_credentials,
            body: {},
            failOnStatusCode: false
          }).then((response) => {
            expect(response.status).eql(200)
            // Verify data.
            cy.request(getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
              expect(response.status).eql(200)
              expect(response.body).eql(item)
            })
          })
        })
      })

      it('PATCH - basic case', () => {
        createMetastore(schema_id).then((response) => {
          expect(response.status).eql(201)
          const item = generateMetastore(schema_id, response.body.identifier)
          cy.request({
            method: 'PATCH',
            url: getMetastorePatchEndpoint(schema_id, response.body.identifier),
            auth: user_credentials,
            body: item
          }).then((response) => {
            expect(response.status).eql(200)
            // Verify item.
            cy.request(getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
              expect(response.status).eql(200)
              expect(response.body).eql(item)
            })
          })
        })
      })
    })

    context(`Metastore Item Deletion (${schema_id})`, () => {
      it('Delete existing datasets', () => {
        createMetastore(schema_id).then((response) => {
          const identifier = response.body.identifier
          cy.request({
            method: 'DELETE',
            url: getMetastoreDeleteEndpoint(schema_id, identifier),
            auth: user_credentials
          }).then((response) => {
            expect(response.status).eql(200)
            cy.request({
              url: getMetastoreGetEndpoint(schema_id, identifier),
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
      const item = generateMetastore(schema_id)
      createMetastore(schema_id, item).then((response) => {
        expect(response.status).eql(201)
        const new_keywords = [
          generateRandomString(),
          generateRandomString()
        ]
        cy.request({
          method: 'PATCH',
          url: getMetastorePatchEndpoint(schema_id, item.identifier),
          auth: user_credentials,
          body: {
            keyword: new_keywords
          }
        }).then((response) => {
          expect(response.status).eql(200)
          // Verify expected data: added, removed, edited and left unchanged.
          cy.request(getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
            expect(response.status).eql(200)
            new_keywords.forEach((keyword) => expect(response.body.keyword).to.contain(keyword))
            item.keyword.forEach((keyword) => expect(response.body.keyword).to.not.contain(keyword))
          })
        })
      })
    })

    it('PATCH modifies object properties (add, remove, edit)', () => {
      const item = generateMetastore(schema_id)
      createMetastore(schema_id, item).then((response) => {
        expect(response.status).eql(201)
        const new_keywords = [
          generateRandomString(),
          generateRandomString()
        ]
        const endpoint = getMetastorePatchEndpoint(schema_id, item.identifier)
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
          cy.request(getMetastoreGetEndpoint(schema_id, item.identifier)).then((response) => {
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
