const api_uri = Cypress.config('apiUri')
const user_credentials = Cypress.env('TEST_USER_CREDENTIALS')

export const metastore_schemas = [
  'dataset',
  'publisher',
  'distribution',
  'theme',
  'keyword',
  'data-dictionary',
]

export function getMetastoreCreateEndpoint (schema_id) {
  return `/${api_uri}/metastore/schemas/${schema_id}/items`
}

export function getMetastoreGetEndpoint (schema_id, identifier) {
  return `/${api_uri}/metastore/schemas/${schema_id}/items/${identifier}`
}

export function getMetastorePutEndpoint (schema_id, identifier) {
  return `/${api_uri}/metastore/schemas/${schema_id}/items/${identifier}`
}

export function getMetastorePatchEndpoint (schema_id, identifier) {
  return `/${api_uri}/metastore/schemas/${schema_id}/items/${identifier}`
}

export function getMetastoreDeleteEndpoint (schema_id, identifier) {
  return `/${api_uri}/metastore/schemas/${schema_id}/items/${identifier}`
}

// Generate a random uuid.
// Credit: https://stackoverflow.com/questions/105034/create-guid-uuid-in-javascript
export function generateMetastoreIdentifier () {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8)
    return v.toString(16)
  })
}

export function generateRandomString () {
  return generateMetastoreIdentifier()
}

export function generateRandomDateString () {
  const start = new Date('1970-01-01T00:00:00.000Z')
  const end = new Date()
  const date = new Date(+start + Math.random() * (end - start))

  const year = date.getFullYear().toString()
  const month = date.getMonth().toString().padStart(2, '0')
  const day = date.getDate().toString().padStart(2, '0')

  return year + '-' + month + '-' + day
}

// Create a metastore item via API.
export function createMetastore (schema_id, item = null) {
  item = item || generateMetastore(schema_id)
  // Lookup the proper metastore creation procedure for the given schema ID.
  return cy.request({
    method: 'POST',
    url: getMetastoreCreateEndpoint(schema_id),
    auth: user_credentials,
    body: item
  })
}

export function generateMetastore (schema_id, identifier = null) {
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
export function generateDataset(uuid) {
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
export function generatePublisher(uuid) {
  return {
    "identifier": uuid,
    "data": {
      "@type": "org:Organization",
      "name": generateRandomString(),
      "subOrganizationOf": generateRandomString()
    }
  }
}

export function generateDistribution(uuid) {
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

export function generateTheme(uuid) {
  return {
    "identifier": uuid,
    "data": generateRandomString()
  }
}

export function generateKeyword(uuid) {
  return {
    "identifier": uuid,
    "data": generateRandomString()
  }
}

// Generate a metastore data-dictionary item object.
export function generateDataDictionary(uuid) {
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
