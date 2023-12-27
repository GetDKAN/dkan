<?php

namespace Drupal\harvest\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the harvest hash entity edit forms.
 */
class HarvestHashForm extends ContentEntityForm {

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
        $this->messenger()->addStatus($this->t('New harvest hash %label has been created.', $message_arguments));
        $this->logger('harvest')->notice('Created new harvest hash %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The harvest hash %label has been updated.', $message_arguments));
        $this->logger('harvest')->notice('Updated harvest hash %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.harvest_hash.canonical', ['harvest_hash' => $entity->id()]);

    return $result;
  }

}
