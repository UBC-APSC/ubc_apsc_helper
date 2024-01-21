<?php

namespace Drupal\ubc_apsc_helper\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;

class UbcApscHelperForm extends ConfigFormBase {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs an AutoParagraphForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, StateInterface $state) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),$container
      ->get('state')
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ubc_apsc_helper_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ubc_apsc_helper.settings',
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
	  
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
	
    // Default settings.
    $config = $this->config('ubc_apsc_helper.settings');
	
	// Syndicated content origin site label.
    $form['external_stylesheet_load'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Load external stylesheet'),
      '#default_value' => $config->get('ubc_apsc_helper.external_stylesheet_load'),
      '#description' => $this->t('Check this box to load an external stylesheet.'),
    ];
	
	// Syndicated content origin site label.
    $form['external_stylesheet_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('External stylesheet URL'),
      '#default_value' => $config->get('ubc_apsc_helper.external_stylesheet_url'),
      '#description' => $this->t('The URL for the external stylesheet (preferably without protocol http: or https:)'),
      '#required' => TRUE,
    ];
	
	// Syndicated content origin domain.
    $form['external_stylesheet_body_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Additional &lt;body&gt; class'),
      '#default_value' => $config->get('ubc_apsc_helper.external_stylesheet_body_class'),
      '#description' => $this->t('Optional, include class(es) on the &lt;body&gt; tag to increase specificity. Seperate multiple classes by a space.'),
    ];
	
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }
  
    /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
	  
    $config = $this->config('ubc_apsc_helper.settings');
	
    $config->set('ubc_apsc_helper.external_stylesheet_load', $form_state->getValue('external_stylesheet_load'));
    $config->set('ubc_apsc_helper.external_stylesheet_url', $form_state->getValue('external_stylesheet_url'));
    $config->set('ubc_apsc_helper.external_stylesheet_body_class', $form_state->getValue('external_stylesheet_body_class'));
	
    $config->save();
	
    return parent::submitForm($form, $form_state);
  }
}
