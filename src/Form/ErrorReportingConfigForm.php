<?php

namespace Drupal\error_reporting\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for error reporting settings.
 */
class ErrorReportingConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['error_reporting.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'error_reporting_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('error_reporting.settings');

    $form['heading'] = [
      '#markup' => '<h2>' . $this->t('Error Reporting Settings') . '</h2>',
    ];

    $form['enable_error_reporting'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable custom error reporting'),
      '#default_value' => $config->get('enable_error_reporting'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('error_reporting.settings');
    $config->set('enable_error_reporting', $form_state->getValue('enable_error_reporting'))->save();

    parent::submitForm($form, $form_state);
  }

}
