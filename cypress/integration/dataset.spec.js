context('Dataset', () => {

    beforeEach(() => {
      cy.visit("http://dkan/dataset/5dc1cfcf-8028-476c-a020-f58ec6dd621c")
    })
  
    it('I see the title and description', () => {
      cy.wait(6000)
      cy.get('h1').should('have.text', 'Gold Prices in London 1950-2008 (Monthly)')
      cy.get('.col-md-9').contains('Monthly gold prices (USD) in London from Bundesbank.')
    })
  
    it('I see the release and update date, identifier, and contact information.', () => {
      let today = new Date().toISOString().slice(0, 10);
      today = today.toString();

      var keys = [
        "Publisher",
        "Identifier",
        "Issued",
        "Last Update",
        "Contact",
        "Contact E-mail",
        "Public Access Level",
        "Homepage URL"
      ]
  
      var values = [
        'demo.getdkan.com',
        '5dc1cfcf-8028-476c-a020-f58ec6dd621c',
        '2013-02-10',
        today,
        'Gray, Stefanie',
        'mailto:datademo@example.com',
        'public',
        'http://demo.getdkan.com/dataset/gold-prices-london-1950-2008-monthly'
      ]
  
      keys.forEach((value, index) => {
        var final = index + 1;
        cy.get('.table-three > .table > tbody > :nth-child(' + final + ') > :nth-child(1)').contains(value);
        cy.get('.table-three > .table > tbody > :nth-child(' + final + ') > :nth-child(2)').contains(values[index]);
      })
    })
  
    it('I see the file is available to download', () => {
      cy.get('.resource > svg').should('have.attr', 'class', 'dkan-icon')
      cy.get('.resource > a').should('have.attr', 'href', 'http://demo.getdkan.com/sites/default/files/data_0.csv')
    })
  
    it('I see the tags.', () => {
      cy.get('.tag-wrapper > :nth-child(1) > a').contains("economy");
      cy.get('.tag-wrapper > :nth-child(2) > a').contains("price");
      cy.get('.tag-wrapper > :nth-child(3) > a').contains("time-series");
    })
  
    it('I see datastore details.', () => {
      cy.get('.table-one > h3').contains('What\'s in this Dataset?')
      cy.get('.table-one > .table > thead > tr > :nth-child(1)').should('contain', 'Rows')
      cy.get('.table-one > .table > thead > tr > :nth-child(2)').should('contain', 'Columns')
      cy.get('.table-one > .table > tbody > tr > :nth-child(1)').should('contain', '0')
      cy.get('.table-one > .table > tbody > tr > :nth-child(2)').should('contain', '2')
  
  
      cy.get('.table-two > h3').contains('Columns in this Dataset')
      cy.get('.table-two > .table > thead > tr > :nth-child(1)').should('contain','Column Name')
      cy.get('.table-two > .table > thead > tr > :nth-child(2)').should('contain','Type')
      cy.get('.table-two > .table > tbody > :nth-child(1) > :nth-child(1)').should('contain','date')
      cy.get('.table-two > .table > tbody > :nth-child(1) > :nth-child(2)').should('contain','String')
      cy.get('.table-two > .table > tbody > :nth-child(2) > :nth-child(1)').should('contain','price')
      cy.get('.table-two > .table > tbody > :nth-child(2) > :nth-child(2)').should('contain','String')
  
    })

    it('I can filter the data by year', () => {
      cy.get('.ReactTable .rt-tr > :nth-child(1) > input').type('1952')
      cy.get('.-pagination .-pageInfo .-totalPages').should('contain','2')
    })

    it('I can sort the data by price', () => {
      cy.get('.ReactTable :nth-child(2) > .rt-resizable-header-content').click()
      cy.get('.ReactTable .rt-tbody > :nth-child(1) > .rt-tr > :nth-child(2)').should('contain','34.49')
    })
  })