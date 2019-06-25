context('SQL Endpoint', () => {

    let endpoint = "http://dkan/api/v1/sql/"
    let dataset_identifier = "c9e2d352-e24c-4051-9158-f48127aa5692"
    let resource_identifier

    // Obtain the resource identifier from the above dataset before proceeding.
    before(function() {
        let uuidRegex = /^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}$/;
        cy.request("http://dkan/api/v1/dataset/" + dataset_identifier + "?values=identifier").then((response) => {
            expect(response.body.distribution[0]).not.eql(dataset_identifier)
            expect(response.body.distribution[0]).to.match(uuidRegex);
            resource_identifier = response.body.distribution[0];
        })
    })

    context('SELECT', () => {
        it('All', () => {
            let query = endpoint + `[SELECT * FROM ${resource_identifier}];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(399)
                let properties = [
                    "dari_dist",
                    "dari_prov",
                    "dist_id",
                    "dist_name",
                    "lat",
                    "lon",
                    "prov_id",
                    "prov_name",
                    "unit_type"
                ]

                properties.forEach((x) => {
                    expect(response.body[0].hasOwnProperty(x)).equal(true)
                })
            })
        })

        it('Specific fields', () => {
            let query = endpoint + `[SELECT lon,lat FROM ${resource_identifier}];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(399)
                let properties = [
                    "lat",
                    "lon"
                ]

                properties.forEach((x) => {
                    expect(response.body[0].hasOwnProperty(x)).equal(true)
                })
                expect(response.body[0].hasOwnProperty("prov_id")).equal(false)
            })
        })
    })

    context('WHERE', () => {
        it('Single condition', () => {
            let query = endpoint + `[SELECT * FROM ${resource_identifier}][WHERE prov_name = 'Farah'];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(11)
            })
        })

        it('Multiple conditions', () => {
            let query = endpoint + `[SELECT * FROM ${resource_identifier}][WHERE prov_name = 'Farah' AND dist_name = 'Farah'];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(1)
            })
        })

    })

    context('ORDER BY', () => {

        it('Ascending explicit', () => {
            let query = endpoint + `[SELECT * FROM ${resource_identifier}][ORDER BY prov_name ASC];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(399)
                expect(response.body[0].prov_name).eql("Badakhshan")
            })
        })

        it('Descending explicit', () => {
            let query = endpoint + `[SELECT * FROM ${resource_identifier}][ORDER BY prov_name DESC];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(399)
                expect(response.body[0].prov_name).eql("Zabul")
            })
        })

    })

    context('LIMIT and OFFSET', () => {
        it('Limit only', () => {
            let query = endpoint + `[SELECT * FROM ${resource_identifier}][ORDER BY prov_name ASC][LIMIT 5];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(5)
                expect(response.body[0].prov_name).eql("Badakhshan")
            })
        })

        it('Limit and offset', () => {
            let query = endpoint + `[SELECT * FROM ${resource_identifier}][ORDER BY prov_name ASC][LIMIT 5 OFFSET 100];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(5)
                expect(response.body[0].prov_name).eql("Faryab")
            })
        })

    })

})
