<?php

namespace Drupal\json_form_widget\Tests\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the json form widget.
 *
 * This test begins to replace Cypress test:
 * - 07_admin_dataset_json_form.spec.js
 *
 * @group dkan
 * @group json_form_widget
 * @group functional
 */
class AdminDatasetJsonFormTest extends BrowserTestBase {

  protected static $modules = [
    'dkan',
    'json_form_widget',
    'node',
  ];

  protected $defaultTheme = 'stark';

  public function testAdminDatasetJsonForm() {
    /** @var \Drupal\metastore\MetastoreService $metastore_service */
    $metastore_service = $this->container->get('dkan.metastore.service');
    /** @var \Drupal\metastore\ValidMetadataFactory $metadata_factory */
    $metadata_factory = $this->container->get('dkan.metastore.valid_metadata');

    $this->drupalLogin(
    // @todo Figure out least possible admin permissions.
      $this->drupalCreateUser([], NULL, TRUE)
    );
    $assert = $this->assertSession();

    // 07_admin_dataset_json_form.spec.js : The dataset form has the correct
    // required fields.
    $this->drupalGet('node/add/data');
    $assert->statusCodeEquals(200);

    $page = $this->getSession()->getPage();

    // These fields should be marked as required.
    foreach ([
      '#edit-field-json-metadata-0-value-title',
      '#edit-field-json-metadata-0-value-description',
      '#edit-field-json-metadata-0-value-accesslevel',
      '#edit-field-json-metadata-0-value-modified-date',
      '#edit-field-json-metadata-0-value-publisher-publisher-name',
      '#edit-field-json-metadata-0-value-contactpoint-contactpoint-fn',
      '#edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail',
    ] as $locator) {
      $this->assertEquals(
        'required',
        $page->find('css', $locator)->getAttribute('required')
      );
    }

    // 07_admin_dataset_json_form.spec.js : License and format fields are
    // select_or_other elements in dataset form.
    // These select elements have an '- Other -' option.
    foreach ([
      "#edit-field-json-metadata-0-value-license-select option[value='select_or_other']",
      "#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format-select option[value='select_or_other']",
    ] as $locator) {
      $item = $page->find('css', $locator);
      $this->assertEquals('select_or_other', $item->getValue());
    }
    // Assert the existence of the 'other' text element for select_or_other
    // fields.
    foreach ([
      '#edit-field-json-metadata-0-value-license-other.form-url',
      '#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format-other.form-text',
    ] as $locator) {
      $this->assertNotNull($page->find('css', $locator));
    }

    // 07_admin_dataset_json_form.spec.js : User can create and edit a dataset
    // with the json form UI. User can delete a dataset.
    // We need a publisher.
    $publisher_name = uniqid();
    $metastore_service->post('publisher',
      $metastore_service->getValidMetadataFactory()->get(
        json_encode((object) [
          'identifier' => '9deadc2f-50e0-512a-af7c-4323697d530d',
          'data' => ['name' => $publisher_name],
        ]), 'publisher', ['method' => 'POST'])
    );
    // We need a keyword.
    $keyword_data = uniqid();
    $metastore_service->post('keyword',
      $metastore_service->getValidMetadataFactory()->get(json_encode((object) [
        'identifier' => '05b2e74a-eb23-585b-9c1c-4d023e21e8a5',
        'data' => $keyword_data,
      ]), 'keyword', ['method' => 'POST'])
    );

    // Use the form.
    $this->drupalGet('node/add/data');
    $assert->statusCodeEquals(200);
    $this->submitForm([
      'edit-field-json-metadata-0-value-title' => 'DKANTEST dataset title',
      'edit-field-json-metadata-0-value-description' => 'DKANTEST dataset description.',
      'edit-field-json-metadata-0-value-accesslevel' => 'public',
      'edit-field-json-metadata-0-value-modified-date' => '2020-02-02',
      'edit-field-json-metadata-0-value-publisher-publisher-name' => $publisher_name,
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-fn' => 'DKANTEST Contact Name',
      'edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail' => 'dkantest@test.com',
      'edit-field-json-metadata-0-value-keyword-keyword-0' => $keyword_data,
    ], 'Save');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Data DKANTEST dataset title has been created.');

    // Confirm the default dkan admin view is filtered to show only datasets.
//    $this->drupalGet('admin/dkan/datasets');

//    cy.visit(baseurl + "/admin/dkan/datasets")
//        cy.get('tbody tr').each(($el) => {
//      cy.wrap($el).within(() => {
//        cy.get('td.views-field-field-data-type').should('contain', 'dataset')
//          })
//        })



    // Edit the dataset.
//    $this->drupalGet('admin/dkan/datasets');
//    cy.get('#edit-title').type('DKANTEST dataset title', { force:true } )
//        cy.get('#edit-submit-dkan-dataset-content').click({ force:true })
//        cy.get('tbody > tr:first-of-type > .views-field-nothing > a').click({ force:true })
//        cy.wait(2000)
//        cy.get('#edit-field-json-metadata-0-value-title').should('have.value','DKANTEST dataset title')
//        cy.get('#edit-field-json-metadata-0-value-title').type('NEW dkantest dataset title',{ force:true })
//        cy.get('#edit-field-json-metadata-0-value-accrualperiodicity').select('Annual', { force:true })
//        cy.get('#edit-field-json-metadata-0-value-keyword-keyword-0 + .select2')
//        .find('.select2-selection')
//        .click({ force: true });
//        cy.get('input[aria-controls="select2-edit-field-json-metadata-0-value-keyword-keyword-0-results"]').type('testing{enter}')
//        cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-title').type('DKANTEST distribution title text', { force:true })
//        cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-description').type('DKANTEST distribution description text', { force:true })
//        cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format-select').select('csv', { force:true })
//        cy.get('#edit-submit').click({ force:true })
//        cy.get('.button').contains('Yes').click({ force:true });
//        cy.get('.messages--status').should('contain','has been updated')

//    cy.visit(baseurl + "/admin/dkan/datasets")
//        cy.wait(2000)
//        cy.get('#edit-node-bulk-form-0').check({ force:true })
//        cy.get('#edit-action').select('Delete content',{ force: true }).should('have.value', 'node_delete_action')
//        cy.get('#edit-submit').click({ force:true })
//        cy.get('input[value="Delete"]').click({ force:true })
//        cy.get('.messages__content').should('contain','Deleted 1 content item.')
  }

}
