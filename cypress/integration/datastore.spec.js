context('Datastore Api', () => {

    let datastore_endpoint = "http://dkan/api/v1/datastore";
    let dataset_endpoint = "http://dkan/api/v1/dataset";
    let dataset_identifier = "c9e2d352-e24c-4051-9158-f48127aa5692";

    context('Headers and statistics', () => {
        it('headers', () => {
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

            cy.request(dataset_endpoint + '/' + dataset_identifier + '?values=both').then((response) => {
              let resource_identifier = response.body.distribution[0].identifier
              cy.request(datastore_endpoint + '/' + resource_identifier).then((response) => {
                cy.log(response);
                expect(response.status).eql(200);
                expect(response.body.columns).eql(expected_columns)
                expect(response.body.numOfRows).eql(399);
                expect(response.body.numOfColumns).eql(9)
              })
            })
        });
    })

});
