<?php

namespace Drupal\metastore\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the resource mapping entity edit forms.
 */
class ResourceMappingForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New resource mapping %label has been created.', $message_arguments));
        $this->logger('metastore')->notice('Created new resource mapping %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The resource mapping %label has been updated.', $message_arguments));
        $this->logger('metastore')->notice('Updated resource mapping %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.resource_mapping.collection');

    return $result;
  }

}
