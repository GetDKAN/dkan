<?php

namespace Drupal\dkan_js_frontend\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * DKAN JS Frontend settings form.
 *
 * @package Drupal\dkan_js_frontend\Form
 * @codeCoverageIgnore
 */
class DkanJsFrontendSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dkan_js_frontend_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['dkan_js_frontend.config'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['intro'] = [
      '#type' => 'markup',
      '#markup' => $this->t('The DKAN JS front end module assumes a JavaScript app has been loaded into a <i>frontend</i> directory in the docroot of your site code base. This can be changed by updating the css and js directory locations below. All JS/CSS files from the specified directories will be attached to any route/path template that has been defined under Routes.'),
    ];

    $form['css_folder'] = [
      '#type' => 'textfield',
      '#min' => 1,
      '#title' => $this->t('CSS Directory'),
      '#default_value' => $this->config('dkan_js_frontend.config')->get('css_folder'),
      '#description' => $this->t('Path to the css directory.'),
    ];

    $form['js_folder'] = [
      '#type' => 'textfield',
      '#min' => 1,
      '#title' => $this->t('JS Directory'),
      '#default_value' => $this->config('dkan_js_frontend.config')->get('js_folder'),
      '#description' => $this->t('Path to the js directory.'),
    ];

    $form['minified'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Is minified'),
      '#config_target' => 'dkan_js_frontend.config:minified',
      '#description' => $this->t('Global: applied to both the CSS and JS library unless overridden.'),
    ];

    $form['preprocess'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Preprocess'),
      '#config_target' => 'dkan_js_frontend.config:preprocess',
      '#description' => $this->t('Global: applied to both the CSS and JS library unless overridden.'),
    ];

    $form['datastore_query_api'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Datastore Query API'),
      '#config_target' => 'dkan_js_frontend.config:datastore_query_api',
      '#description' => $this->t('Use the dataset UUID and distribution index 0 for datastore queries rather than the distribution UUID.'),
    ];

    $routes = $this->config('dkan_js_frontend.config')->get('routes') ?? [];
    $form['routes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Routes'),
      '#description' => $this->t('Add any path you want this module to use with a string structured like: <pre>unique_path_name,/the_path</pre>
      One pairing per line. The first part is used by Drupal to store path and the second is the actual path where the JS frontend will display.<p>If the Drupal <a href="https://www.drupal.org/project/simple_sitemap">Simple XML sitemap module</a> is installed, the routes listed here will automatically be added to the default sitemap.</p>'),
      '#default_value' => implode(PHP_EOL, $routes),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $routes_list = $form_state->getValue('routes');
    $routes_list = trim($routes_list);
    $form_state->setValue('routes', []);
    if (strlen($routes_list) !== 0) {
      $routes = [];
      $paths = explode(PHP_EOL, $routes_list);
      foreach ($paths as $path) {
        $path = trim($path);
        if (strlen($path) !== 0) {
          $routes[] = strtolower($path);
        }
      }

      $form_state->setValue('routes', $routes);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('dkan_js_frontend.config')
      ->set('css_folder', $form_state->getValue('css_folder'))
      ->set('js_folder', $form_state->getValue('js_folder'))
      ->set('datastore_query_api', $form_state->getValue('datastore_query_api'))
      ->set('minified', $form_state->getValue('minified'))
      ->set('preprocess', $form_state->getValue('preprocess'))
      ->set('routes', $form_state->getValue('routes'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
