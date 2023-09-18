<?php

namespace Drupal\dkan_resourcemap_entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the resourcemap entity edit forms.
 */
class ResourcemapForm extends ContentEntityForm {

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
        $this->messenger()->addStatus($this->t('New resourcemap %label has been created.', $message_arguments));
        $this->logger('dkan_resourcemap_entity')->notice('Created new resourcemap %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The resourcemap %label has been updated.', $message_arguments));
        $this->logger('dkan_resourcemap_entity')->notice('Updated resourcemap %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.resourcemap.canonical', ['resourcemap' => $entity->id()]);

    return $result;
  }

}
