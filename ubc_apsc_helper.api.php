<?php

/**
 * @file
 *
 * The contents of this file are never loaded, or executed, it is purely for
 * documentation purposes.
 *
 * @link https://www.drupal.org/docs/develop/coding-standards/api-documentation-and-comment-standards#hooks
 * Read the standards for documenting hooks. @endlink
 *
 */

/**
 * Implements hook_form_alter().
 *
 * include an additional CSS library on node add/edit forms
 *
 * @param array &$form
 *   The array containing form elements
 *   session.
 * @param \Drupal\node\FormStateInterface $form_state
 *   The form state being viewed.
 *
 */
function ubc_apsc_helper_form_alter(&$form, FormStateInterface $form_state, $form_id) {
	
  /* @var Drupal\Core\Entity\FieldableEntityInterface $entity */
  $formObject = $form_state->getFormObject();
  
  // include additional CSS library on node forms
  if ($formObject instanceof \Drupal\Core\Entity\EntityFormInterface) {
    $entity = $formObject->getEntity();
    if ($entity->getEntityTypeId() === 'node') {
      $form['#attached']['library'][] = 'ubc_apsc_helper/apsc-admin-styles';
    }
  }
}

/**
 * Implements hook_preprocess_media()
 *
 * Change the loading for embeded iframes from 'eager' to 'lazy'
 *
 * @param array &$form
 *   The array containing form elements
 */
function ubc_apsc_helper_preprocess_media(array &$variables) {
	// set remote embedded videos to lazy loading
	if(isset($variables['content']['field_media_oembed_video']) && is_array($variables['content']['field_media_oembed_video'])) {
		$variables['content']['field_media_oembed_video'][0]['#attributes']['loading'] = 'lazy';
	}
}
