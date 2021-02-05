<?php

namespace Drupal\metastore_entity\Form;

use Drupal\Console\Utils\Validator;
use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\language\Entity\ContentLanguageSettings;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator as JsonSchemaValidator;
use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for node type forms.
 *
 * @ingroup metastore_entity
 */
class MetastoreSchemaForm extends BundleEntityFormBase {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs the NodeTypeForm object.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $schema = $this->entity;
    if ($this->operation == 'add') {
      $form['#title'] = $this->t('Add schema');
      $fields = $this->entityFieldManager->getBaseFieldDefinitions('metastore_item');
      $metastore_item = $this->entityTypeManager->getStorage('metastore_item')->create(['schema' => $schema->uuid()]);
    }
    else {
      $form['#title'] = $this->t('Edit %label schema', ['%label' => $schema->label()]);
      $metastore_item = $this->entityTypeManager->getStorage('metastore_item')->create(['schema' => $schema->id()]);
    }

    $form['name'] = [
      '#title' => t('Name'),
      '#type' => 'textfield',
      '#default_value' => $schema->label(),
      '#description' => t('The human-readable name of this metadata schema. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $schema->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => $schema->isLocked(),
      '#machine_name' => [
        'exists' => ['Drupal\node\Entity\NodeType', 'load'],
        'source' => ['name'],
      ],
      '#description' => t('A unique machine-readable name for this content type. It must only contain lowercase letters, numbers, and underscores. This name will be used for constructing the URL of the %schema-add page.', [
        '%schema-add' => t('Add schema'),
      ]),
    ];

    $form['description'] = [
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $schema->getDescription(),
      '#description' => t('This text will be displayed on the <em>Add new metadata</em> page.'),
    ];

    $form['schema'] = [
      '#title' => t('JSON Schema'),
      '#type' => 'text_format',
      '#format' => 'json',
      '#allowed_formats' => ['json'],
      '#default_value' => $schema->getSchema(),
      '#description' => t('Validation schema'),
      '#element_validate' => [[$this, 'validateSchema']],
    ];

    $form['workflow'] = [
      '#type' => 'details',
      '#title' => t('Publishing options'),
      '#collapsed' => FALSE,
      '#open' => TRUE,
    ];
    $workflow_options = [
      'status' => $metastore_item->status->value,
      'revision' => $schema->shouldCreateNewRevision(),
    ];
    // Prepare workflow options to be used for 'checkboxes' form element.
    $keys = array_keys(array_filter($workflow_options));
    $workflow_options = array_combine($keys, $keys);
    $form['workflow']['options'] = [
      '#type' => 'checkboxes',
      '#title' => t('Default options'),
      '#default_value' => $workflow_options,
      '#options' => [
        'status' => t('Published'),
        'revision' => t('Create new revision'),
      ],
      '#description' => t('Users with sufficient access rights will be able to override these options.'),
    ];
    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = [
        '#type' => 'details',
        '#title' => t('Language settings'),
        '#group' => 'additional_settings',
      ];

      $language_configuration = ContentLanguageSettings::loadByEntityTypeBundle('metastore_item', $schema->id());
      $form['language']['language_configuration'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'node',
          'bundle' => $schema->id(),
        ],
        '#default_value' => $language_configuration,
      ];
    }

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save schema');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $id = trim($form_state->getValue('schema'));
    // '0' is invalid, since elsewhere we check it using empty().
    if ($id == '0') {
      $form_state->setErrorByName('type', $this->t("Invalid machine-readable name. Enter a name other than %invalid.", ['%invalid' => $id]));
    }
  }

  public function validateSchema($element, FormStateInterface $form_state, $form) {
    try {
      $test = new RootedJsonData("{}", $form_state->getValue('schema')['value']);
    }
    catch (\Exception $e) {
      $form_state->setError($element, t('Schema failed validation with message: ":msg"', [':msg' => $e->getMessage()]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $schema = $this->entity;
    $schema->set('id', trim($schema->id()));
    $schema->set('name', trim($schema->label()));

    $status = $schema->save();

    $t_args = ['%name' => $schema->label()];

    if ($status == SAVED_UPDATED) {
      $this->messenger()->addStatus($this->t('The schema %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('The schema %name has been added.', $t_args));
      $context = array_merge($t_args, ['link' => $schema->toLink($this->t('View'), 'collection')->toString()]);
      $this->logger('metastore_entity')->notice('Added metastore schema %name.', $context);
    }

    // Update workflow options.
    $metastore_item = $this->entityTypeManager->getStorage('metastore_item')->create(['schema' => $schema->id()]);
    $value = (bool) $form_state->getValue(['options', 'status']);
    if ($metastore_item->status->value != $value) {
      $fields['status']->getConfig($schema->id())->setDefaultValue($value)->save();
    }
    $this->entityFieldManager->clearCachedFieldDefinitions();
    $form_state->setRedirectUrl($schema->toUrl('collection'));
  }

}
