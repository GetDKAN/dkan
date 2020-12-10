<?php

namespace Drupal\metastore_entity\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\language\Entity\ContentLanguageSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for node type forms.
 *
 * @internal
 */
class MetadataSchemaForm extends BundleEntityFormBase {

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
      $fields = $this->entityFieldManager->getBaseFieldDefinitions('node');
      // Create a node with a fake bundle using the type's UUID so that we can
      // get the default values for workflow settings.
      // @todo Make it possible to get default values without an entity.
      //   https://www.drupal.org/node/2318187
      $metadata = $this->entityTypeManager->getStorage('metadata')->create(['schema' => $schema->uuid()]);
    }
    else {
      $form['#title'] = $this->t('Edit %label schema', ['%label' => $schema->label()]);
      $fields = $this->entityFieldManager->getFieldDefinitions('metadata', $schema->id());
      $metadata = $this->entityTypeManager->getStorage('metadata')->create(['schema' => $schema->id()]);
    }

    $form['name'] = [
      '#title' => t('Name'),
      '#type' => 'textfield',
      '#default_value' => $schema->label(),
      '#description' => t('The human-readable name of this metadata schema. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    ];

    $form['schema'] = [
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

    // $form['additional_settings'] = [
    //   '#type' => 'vertical_tabs',
    //   '#attached' => [
    //     'library' => ['node/drupal.content_types'],
    //   ],
    // ];

    $form['submission'] = [
      '#type' => 'details',
      '#title' => t('Submission form settings'),
      '#group' => 'additional_settings',
      '#open' => TRUE,
    ];
    $form['submission']['title_label'] = [
      '#title' => t('Title field label'),
      '#type' => 'textfield',
      '#default_value' => $fields['title']->getLabel(),
      '#required' => TRUE,
    ];
    // $form['submission']['preview_mode'] = [
    //   '#type' => 'radios',
    //   '#title' => t('Preview before submitting'),
    //   '#default_value' => $type->getPreviewMode(),
    //   '#options' => [
    //     DRUPAL_DISABLED => t('Disabled'),
    //     DRUPAL_OPTIONAL => t('Optional'),
    //     DRUPAL_REQUIRED => t('Required'),
    //   ],
    // ];
    $form['submission']['help'] = [
      '#type' => 'textarea',
      '#title' => t('Explanation or submission guidelines'),
      '#default_value' => $schema->getHelp(),
      '#description' => t('This text will be displayed at the top of the page when creating or editing content of this type.'),
    ];
    $form['workflow'] = [
      '#type' => 'details',
      '#title' => t('Publishing options'),
      '#group' => 'additional_settings',
    ];
    $workflow_options = [
      'status' => $metadata->status->value,
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

      $language_configuration = ContentLanguageSettings::loadByEntityTypeBundle('metadata', $schema->id());
      $form['language']['language_configuration'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'node',
          'bundle' => $schema->id(),
        ],
        '#default_value' => $language_configuration,
      ];
    }
    $form['display'] = [
      '#type' => 'details',
      '#title' => t('Display settings'),
      '#group' => 'additional_settings',
    ];
    $form['display']['display_submitted'] = [
      '#type' => 'checkbox',
      '#title' => t('Display author and date information'),
      '#default_value' => $schema->displaySubmitted(),
      '#description' => t('Author username and publish date will be displayed.'),
    ];

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

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $schema = $this->entity;
    $schema->setNewRevision($form_state->getValue(['options', 'revision']));
    $schema->set('type', trim($schema->id()));
    $schema->set('name', trim($schema->label()));

    $status = $schema->save();

    $t_args = ['%name' => $schema->label()];

    if ($status == SAVED_UPDATED) {
      $this->messenger()->addStatus($this->t('The schema %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('The schema %name has been added.', $t_args));
      $context = array_merge($t_args, ['link' => $schema->toLink($this->t('View'), 'collection')->toString()]);
      $this->logger('node')->notice('Added metadata schema %name.', $context);
    }

    $fields = $this->entityFieldManager->getFieldDefinitions('metadata', $schema->id());
    // Update title field definition.
    $title_field = $fields['title'];
    $title_label = $form_state->getValue('title_label');
    if ($title_field->getLabel() != $title_label) {
      $title_field->getConfig($schema->id())->setLabel($title_label)->save();
    }
    // Update workflow options.
    // @todo Make it possible to get default values without an entity.
    //   https://www.drupal.org/node/2318187
    $metadata = $this->entityTypeManager->getStorage('metadata')->create(['schema' => $schema->id()]);
    $value = (bool) $form_state->getValue(['options', 'status']);
    if ($metadata->status->value != $value) {
      $fields['status']->getConfig($schema->id())->setDefaultValue($value)->save();
    }
    $this->entityFieldManager->clearCachedFieldDefinitions();
    $form_state->setRedirectUrl($type->toUrl('collection'));
  }

}
