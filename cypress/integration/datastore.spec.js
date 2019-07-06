context('Datastore Api', () => {

    let endpoint = "http://dkan/api/v1/datastore";
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

            cy.request(endpoint + '/' + dataset_identifier + '?values=both').then((response) => {
                expect(response.status).eql(200);
                expect(response.body.columns).eql(expected_columns)
            })
        });
        it('statistics', () => {
            cy.request(endpoint + '/' + dataset_identifier + '?values=both').then((response) => {
                expect(response.status).eql(200);
                expect(response.body.datastore_statistics.rows).eql('399');
                expect(response.body.datastore_statistics.columns).eql(9)
            })
        })
    })

});
