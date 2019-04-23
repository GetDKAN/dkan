context('Search', () => {

  beforeEach(() => {
    cy.visit("http://dkan/search")
  })

  /*
  Header Text Input Filter
  */
  it('When I enter text into the search input field in the header, I should see the number of datasets that match.', () => {
    cy.wait(5000)
    // Enter 'Hospital' into the text field in the header and confirm we get results.
    cy.get('.results-list input#search').type('health')
    cy.get('.input-group-btn #submit').click()
    // Wait for search page to load.
    cy.contains('Go!')
    cy.get('.results-message').contains('datasets found for health')
    // Pluck the number from the results summary message.
    cy.get('.results-message').as('count')
    cy.get('@count').invoke('text')
        .then((count) => {
          count = parseInt(count.substr(0,5));
          cy.log('message', count)
          // The summary number should equal the datasets returned.
          cy.get('.search-list').children().its('length').should('eq', count)
        })
  })

  /*
  Search Page Text Input Filter
  */
  it('When I enter text into the search input field on the search page, I should see the number of datasets that match.', () => {
    cy.wait(6000)
    // Enter 'Consumer' into the text field and confirm we get results.
    cy.get('.results-list input#search').type('election')
    cy.get('.results-message').should('contain', 'datasets found for election')
    // Pluck the number from the results summary message.
    cy.get('.results-message').as('count')
    cy.get('@count').invoke('text')
        .then((count) => {
          count = parseInt(count.substr(0,5));
          cy.log('message', count)
          // The summary number should equal the datasets returned.
          cy.get('.search-list').children().its('length').should('eq', count)
        })
    // Results list.
    cy.get('ul.search-list').children().each(function($el, i) {
        let index = i + 1;
        // Each result has a heading.
        cy.get('li:nth-child(' + index + ') .search-list-item > a').find('h2')
        // Each result has a theme.
        cy.get('li:nth-child(' + index + ') .search-list-item .item-theme').then((element) => {
          assert.isNotNull(element.text())
        })
        // Each result has a description.
        cy.get(':nth-child(' + index + ') .search-list-item .item-description').then((element) => {
          assert.isNotNull(element.text())
        })
        // Each result has file formats.
        cy.get(':nth-child(' + index + ') .search-list-item .format-types').then((element) => {
          assert.isNotNull(element.text())
        })
    })
  })

  /*
  SORTING
  */
  it('Sort results alphabetically', () => {
    cy.get('.search-list li:nth-child(1) a > h2')
     .should('have.text', 'U.S. Tobacco Usage Statistics')
    cy.get('select').select('Alphabetical')
    cy.get('.search-list li:nth-child(1) a > h2')
     .should('have.text', 'Afghanistan Election Districts')
    cy.get('.search-list li:nth-child(2) a > h2')
     .should('have.text', 'Crime Data for the Ten Most Populous Cities in the U.S.')
    cy.get('.search-list li:nth-child(3) a > h2')
     .should('contain', 'Florida Bike Lanes')
  })

  /*
  TOPIC FILTER
  */
  it('The category facet block should contain 4 topics', () => {
    cy.get(':nth-child(1) > .list-group').children().should('have.length', 4)
    cy.get(':nth-child(1) > h3').should('have.text','Category')
  })

  it.skip('wip - Get expected values from the data.json', () => {
    let jsonTopics = [];
    cy.fixture('data.json').then((data) => {
      Cypress._.each(data, (d) => {
        Cypress._.each(d.theme, (theme) => {
          if (!jsonTopics.includes(theme.title)) { 
            jsonTopics.push(theme.title) 
          }
        })
      })
      console.log(jsonTopics)
    })
  })

  it('The topic terms should match the expected POD themes', () => {
    cy.wait(2000)
    cy.get(':nth-child(1) > .list-group').then(($li) => {
      cy.fixture('topics.json').then((topics) => {
        Cypress._.each(topics, (category) => {
          expect($li).to.contain(category)
        })
      })
    })
  })

  it('Check results are returned when filtering for topic 1', () => {
    cy.get(':nth-child(1) > .list-group > :nth-child(1) > a').click()
    cy.get('.results-message').should('not.contain', '0')
    cy.get('.results-message').should('contain', 'datasets')
  })

  it('Check results are returned when filtering for topic 2', () => {
    cy.get(':nth-child(1) > .list-group > :nth-child(2) > a').click()
    cy.get('.results-message').should('not.contain', '0')
    cy.get('.results-message').should('contain', 'datasets')
  })

  it('Check results are returned when filtering for topic 3', () => {
    cy.get(':nth-child(1) > .list-group > :nth-child(3) > a').click()
    cy.get('.results-message').should('not.contain', '0')
    cy.get('.results-message').should('contain', 'datasets')
  })

  it('Check results are returned when filtering for topic 4', () => {
    cy.get(':nth-child(1) > .list-group > :nth-child(4) > a').click()
    cy.get('.results-message').should('not.contain', '0')
    cy.get('.results-message').should('contain', 'datasets')
  })

  /*
  KEYWORD FILTER
  */
  it('Check that the tags facet block has options', () => {
    cy.get(':nth-child(2) > .list-group').children()
      .its('length')
      .should('be.gt', 0)
    cy.get(':nth-child(2) > h3').should('have.text','Tags')
  })

  it('When filtering by keyword I should get a smaller results list', () => {
    cy.get('.search-list').children()
      .its('length').as('results')
    cy.get(':nth-child(1) > .list-group > :nth-child(1) > a').click()
    cy.get('.search-list').children()
      .its('length').as('filtered')
    expect('@filtered').to.be.lessThan('@results')
  })

  /*
  FORMAT FILTER
  */
  it('Check that the Format facet block has options', () => {
    cy.get(':nth-child(3) > .list-group').children()
      .its('length')
      .should('be.gt', 0)
    cy.get(':nth-child(3) > h3').should('have.text','Format')
  })

  it('When filtering by format I should get a smaller results list', () => {
    cy.get('.search-list').children()
      .its('length').as('results')
    cy.get(':nth-child(2) > .list-group > :nth-child(1) > a').click()
    cy.get('.search-list').children()
      .its('length').as('filtered')
    expect('@filtered').to.be.lessThan('@results')
  })

})
