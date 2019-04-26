context('SQL Endpoint', () => {

    let endpoint = "http://dkan/api/v1/sql/"
    let dataset_identifier = "c9e2d352-e24c-4051-9158-f48127aa5692"

    context('SELECT', () => {
        it('All', () => {
            let query = endpoint + `[SELECT * FROM ${dataset_identifier}];`
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
            let query = endpoint + `[SELECT lon,lat FROM ${dataset_identifier}];`
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
            let query = endpoint + `[SELECT * FROM ${dataset_identifier}][WHERE prov_name LIKE "Farah"];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(11)
            })
        })

        it('Multiple conditions', () => {
            let query = endpoint + `[SELECT * FROM ${dataset_identifier}][WHERE prov_name LIKE "Farah" AND dist_name LIKE "Farah"];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(1)
            })
        })

    })

    context('ORDER BY', () => {
        it('Ascending', () => {
            let query = endpoint + `[SELECT * FROM ${dataset_identifier}][WHERE prov_name LIKE "%25"][ORDER BY prov_name];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(399)
                expect(response.body[0].prov_name).eql("Badakhshan")
            })
        })

        it('Ascending explicit', () => {
            let query = endpoint + `[SELECT * FROM ${dataset_identifier}][WHERE prov_name LIKE "%25"][ORDER BY prov_name ASC];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(399)
                expect(response.body[0].prov_name).eql("Badakhshan")
            })
        })

        it('Descending explicit', () => {
            let query = endpoint + `[SELECT * FROM ${dataset_identifier}][WHERE prov_name LIKE "%25"][ORDER BY prov_name DESC];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(399)
                expect(response.body[0].prov_name).eql("Zabul")
            })
        })

    })

    context('LIMIT and OFFSET', () => {
        it('Limit only', () => {
            let query = endpoint + `[SELECT * FROM ${dataset_identifier}][WHERE prov_name LIKE "%25"][ORDER BY prov_name][LIMIT 5];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(5)
                expect(response.body[0].prov_name).eql("Badakhshan")
            })
        })

        it('Limit and offset', () => {
            let query = endpoint + `[SELECT * FROM ${dataset_identifier}][WHERE prov_name LIKE "%25"][ORDER BY prov_name ASC][LIMIT 5 OFFSET 100];`
            cy.request(query).then((response) => {
                expect(response.status).eql(200)
                expect(response.body.length).eql(5)
                expect(response.body[0].prov_name).eql("Faryab")
            })
        })

    })

})
