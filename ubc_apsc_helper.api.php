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
 * - Change the loading for embeded iframes from 'eager' to 'lazy'.
 * - If cookiebot enabled, change the attributes for iframe to account for cookie consent choice, load JS to show message that consent needs to be granted for iframe to load
 *
 * @param array &$form
 *   The array containing form elements
 */
function ubc_apsc_helper_preprocess_media(array &$variables) {
	// set remote embedded videos to lazy loading
	if(isset($variables['content']['field_media_oembed_video']) && is_array($variables['content']['field_media_oembed_video'])) {
		$variables['content']['field_media_oembed_video'][0]['#attributes']['loading'] = 'lazy';
	}
	
	$config = \Drupal::config('ubc_apsc_helper.settings');

	if($config->get('ubc_apsc_helper.cookiebot_load')) {
		
		if(isset($variables['content']['field_media_oembed_video']) && is_array($variables['content']['field_media_oembed_video'])) {
			$variables['content']['field_media_oembed_video'][0]['#attributes']['data-cookieblock-src'] = $variables['content']['field_media_oembed_video'][0]['#attributes']['src'];
			unset($variables['content']['field_media_oembed_video'][0]['#attributes']['src']);
			$variables['content']['field_media_oembed_video'][0]['#attributes']['data-cookieconsent'] = 'marketing';
			$variables['content']['field_media_oembed_video'][0]['#attached']['library'][] = 'ubc_apsc_helper/cookiebot-iframe-consent';
		}
	}
}

/**
 * Implements hook_preprocess_html()
 *
 * - Option to include additional classes to the <body> element and load an external CSS library
 *
 * @param array $variables: An associative array containing:
 * - page: A render element representing the page.
 */
function ubc_apsc_helper_preprocess_html(&$variables) {
	
  // get module config settings
  $config = \Drupal::config('ubc_apsc_helper.settings');
  
  if($config->get('ubc_apsc_helper.external_stylesheet_load')) {
	$body_class = $config->get('ubc_apsc_helper.external_stylesheet_body_class');
	
	//additional class for UBC APSC modifier styles
	if(!empty($body_class))
		$variables['attributes']['class'][] = $body_class;
  }
}

/**
 * Implements hook_library_info_build().
 *
 * Add dynamic library definitions.
 */

function ubc_apsc_helper_library_info_build() {
	
	$libraries = [];
	
	$config = \Drupal::config('ubc_apsc_helper.settings');
	
	if($config->get('ubc_apsc_helper.external_stylesheet_load')) {
		
		$file_url = $config->get('ubc_apsc_helper.external_stylesheet_url');
		
		$libraries['ubc-apsc-styles'] = [
		  'version' => '1.0',
		  'css' => [
		    'theme' => [
			  "$file_url" => [
			    'type' => 'external',
				'minified' => true,
				'weight' => 50,
				'group' => CSS_THEME,
				'data' => "$file_url",
				'version' => '1',
				'media' => 'all',
				'preprocess' => true,
				'license' => [
				  'name' => 'GNU-GPL-2.0-or-later',
				  'url' => 'https://www.drupal.org/licensing/faq',
				  'gpl-compatible' => true,
				],
			  ],
			],
		  ],
		];
	}
	
	return $libraries;
}

/**
 * Implements hook__page_attachments(array &$page)
 * Add attachments (typically assets) to a page before it is rendered.
 *
 * If defined/activated,
 * - Load external CSS library
 * - Load cookiebot script
 *
 * @param array $variables: An associative array containing:
 * - page: A render element representing the page.
 *
 */
function ubc_apsc_helper_page_attachments(array &$page) {
	
	$is_admin = \Drupal::service('router.admin_context')->isAdminRoute();
	
	$config = \Drupal::config('ubc_apsc_helper.settings');
	
	if($config->get('ubc_apsc_helper.external_stylesheet_load') && !$is_admin) {

		$page['#attached']['library'][] = 'ubc_apsc_helper/ubc-apsc-styles';
		
	}
	
	if($config->get('ubc_apsc_helper.cookiebot_load') && !$is_admin) {
		
		$library_discovery = \Drupal::service('library.discovery');
		$cookiebot_library = $library_discovery->getLibraryByName('ubc_apsc_helper', 'cookiebot');

		$cookiebot_script = [
			'#type' => 'html_tag',
			'#tag' => 'script',
			'#attributes' => [
			  'src' => $cookiebot_library['js'][0]['data'],
			  'id' => $cookiebot_library['js'][0]['attributes']['id'],
			  'data-cbid' => $config->get('ubc_apsc_helper.cookiebot_datacbid'),
			  'data-blockingmode' => $cookiebot_library['js'][0]['attributes']['data-blockingmode'],
			],
			'#weight' => -200,
		  ];

		$page['#attached']['html_head'][] = [$cookiebot_script, 'cookiebot'];
	  
	}
}

/**
 * Implements hook_update_projects_alter(&$projects).
 * Alter the list of projects before fetching data and comparing versions.
 *
 * Hide projects from the list to avoid "No available releases found" warnings on the available updates report
 *
 * @see \Drupal\update\UpdateManager::getProjects()
 * @see \Drupal\Core\Utility\ProjectInfo::processInfoList()
 */
function ubc_apsc_helper_update_projects_alter(&$projects) {
  // Hide a site-specific module from the list.
  unset($projects['ubc_apsc_helper']);
}
