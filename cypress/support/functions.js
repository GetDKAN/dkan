
// Generate a data item.
export function generateDataset() {
  const uuid =  uuid4();
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
        downloadURL: "https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv",
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

// Create a dataset.
export function createDataset(dataset, apiUri, userCredentials) {
  const endpoint = apiUri + '/metastore/schemas/dataset/items';
  return cy.request({
    method: 'POST',
    url: endpoint,
    auth: userCredentials,
    body: dataset
  })
}

export function getResourceIdentifier(datasetIdentifier, apiUri) {
  cy.request(apiUri + '/metastore/schemas/dataset/items/' + datasetIdentifier + '?show-reference-ids')
    .as('request');

  cy.get('@request')
    .its('status')
    .should(($status) => expect($status).eql(200));

  cy.get('@request')
    .then((response) => {
        return response.body.distribution[0].identifier;
      }
    )
    .as('resourceIdentifier');
}

export function removeDatasets(apiUri, userCredentials) {
  let endpoint = apiUri + '/metastore/schemas/dataset/items';
  cy.request({
    method: 'GET',
    url: endpoint,
  }).then((response) => {
    let datasets = response.body;
    datasets.forEach((dataset) => {
      cy.request({
        method: 'DELETE',
        url: endpoint + "/" + dataset.identifier,
        auth: userCredentials,
      })
    });
  })
}

// Generate a random uuid.
// Credit: https://stackoverflow.com/questions/105034/create-guid-uuid-in-javascript
function uuid4() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}
