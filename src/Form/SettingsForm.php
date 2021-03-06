<?php

namespace Drupal\group_subdomain\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configures settings for this module.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'group_subdomain_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['group_subdomain.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('group_subdomain.settings');

    $form['base_host'] = array(
      '#type' => 'textfield',
      '#title' => 'Base Host',
      '#description' => 'ie: cms2.unl.edu (no slashes, no protocol)',
      '#default_value' => $config->get('base_host'),
      '#required' => TRUE,
    );
    
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('group_subdomain.settings');

    $config->set('base_host', $form_state->getValue('base_host'));

    $config->save();
    parent::submitForm($form, $form_state);
  }

}
