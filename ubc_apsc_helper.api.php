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

use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Session\AccountInterface;

/**
 * Implements hook_form_alter().
 *
 * include an additional CSS library on node add/edit forms
 *
 * @param array<string, mixed> $form
 *   The array containing form elements.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state being viewed.
 * @param string $form_id
 *   The form ID.
 */
function ubc_apsc_helper_form_alter(array &$form, FormStateInterface $form_state, string $form_id): void {
	
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
 * Implements hook_form_FORM_ID_alter().
 * Set new user account creation form to have email notification to true by default
 *
 * @param array<string, mixed> $form
 *   The array containing form elements.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state being viewed.
 * @param string $form_id
 *   The form ID.
 */
function ubc_apsc_helper_form_user_register_form_alter(array &$form, FormStateInterface $form_state, string $form_id): void
{
	if (isset($form['account']['notify'])) {
		// Notify user by default on account creation.
		$form['account']['notify']['#default_value'] = TRUE;
	}
}

/**
 * Implements hook_preprocess_media()
 *
 * - Change the loading for embeded iframes from 'eager' to 'lazy'.
 * - If cookiebot enabled and active for the user, change the attributes for iframe to account for cookie consent choice, load JS to show message that consent needs to be granted for iframe to load
 *
 * @param array<string, mixed> $variables
 */
function ubc_apsc_helper_preprocess_media(array &$variables): void
{
	// set remote embedded videos to lazy loading
	if (isset($variables['content']['field_media_oembed_video']) && is_array($variables['content']['field_media_oembed_video'])) {
		$variables['content']['field_media_oembed_video'][0]['#attributes']['loading'] = 'lazy';
	}

	$config = \Drupal::config('ubc_apsc_helper.settings');
	$current_user = \Drupal::currentUser();

	if ($config->get('ubc_apsc_helper.cookiebot_load') && _cookiebot_load_user_checks($config, $current_user)) {
		// modify oembed video attributes for cookiebot marketing cookie consent
		if (isset($variables['content']['field_media_oembed_video']) && is_array($variables['content']['field_media_oembed_video'])) {
			$variables['content']['field_media_oembed_video'][0]['#attributes']['data-cookieblock-src'] = $variables['content']['field_media_oembed_video'][0]['#attributes']['src'];
			unset($variables['content']['field_media_oembed_video'][0]['#attributes']['src']);
			$variables['content']['field_media_oembed_video'][0]['#attributes']['data-cookieconsent'] = 'marketing';
			$variables['content']['field_media_oembed_video'][0]['#attached']['library'][] = 'ubc_apsc_helper/cookiebot-iframe-consent';
		}
	}
}

/**
 * Implements hook_preprocess_html().
 *
 * Adds an optional class to the <body> element.
 *
 * @param array<string, mixed> $variables
 *   The template variables.
 */
function ubc_apsc_helper_preprocess_html(array &$variables): void {
  // Get module config settings.
  $config = \Drupal::config('ubc_apsc_helper.settings');

  $body_class = $config->get('ubc_apsc_helper.external_stylesheet_body_class');

  // Additional class for UBC APSC modifier styles.
  if (!empty($body_class)) {
    $variables['attributes']['class'][] = $body_class;
  }
}

/**
 * Implements hook_library_info_build().
 *
 * Add dynamic library definitions.
 *
 * @return array<string, array<string, mixed>>
 *   The library definitions, keyed by library name.
 */
function ubc_apsc_helper_library_info_build(): array {
	
	$libraries = [];

	$config = \Drupal::config('ubc_apsc_helper.settings');

	if ($config->get('ubc_apsc_helper.external_stylesheet_load')) {

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
 * Implements hook_js_alter().
 * replace the tiny-slider library from the kraken theme with a version that has no dependencies and can run without being blocked
 *
 * @param array<string, array<string, mixed>> $javascript
 *   The JS assets, keyed by asset path or URL.
 * @param \Drupal\Core\Asset\AttachedAssetsInterface $assets
 *   The assets attached to the current response.
 */
function ubc_apsc_helper_js_alter(array &$javascript, AttachedAssetsInterface $assets): void
{

	// skip if we are on an administrative page
	if (\Drupal::service('router.admin_context')->isAdminRoute())
		return;

	$js_to_alter = 'themes/custom/kraken/js/tiny.slider.min.js';

	if (isset($javascript[$js_to_alter])) {

		// Bubble up the 'user.roles' cache context to cache pages separately by role
		$cache_metadata = new CacheableMetadata();
		$cache_metadata->addCacheContexts(['user.roles']);
		\Drupal::service('renderer')->addCacheableDependency($javascript[$js_to_alter], $cache_metadata);

		$config = \Drupal::config('ubc_apsc_helper.settings');

		$current_user = \Drupal::currentUser();

		// load module version of the tiny slider library for cookiebot compatibility
		if ($config->get('ubc_apsc_helper.cookiebot_load') && _cookiebot_load_user_checks($config, $current_user)) {

			$library_discovery = \Drupal::service('library.discovery');
			$library = $library_discovery->getLibraryByName('ubc_apsc_helper', 'tiny-slider-cookiebot');

			// 3. Swap out the data path for the file based on the authentication state
			$module_path = \Drupal::service('extension.list.module')->getPath('ubc_apsc_helper');

			if (!empty($library['js'])) {
				$module_js_asset = reset($library['js']);
				$javascript[$js_to_alter]['preprocess'] = $module_js_asset['preprocess'];
				$javascript[$js_to_alter]['data'] = $module_js_asset['data'];
				$javascript[$js_to_alter]['attributes'] = $module_js_asset['attributes'];
			}
		}
	}
}

/**
 * Implements hook__page_attachments(array &$page)
 * Add attachments (typically assets) to a page before it is rendered.
 *
 * If defined/activated,
 * - Load external CSS library
 * - Load cookiebot script + styles
 *
 *@param array<string, mixed> $page
 */
function ubc_apsc_helper_page_attachments(array &$page): void
{

	$is_admin = \Drupal::service('router.admin_context')->isAdminRoute();

	$config = \Drupal::config('ubc_apsc_helper.settings');

	/* Load CSS library*/
	if ($config->get('ubc_apsc_helper.external_stylesheet_load') && !$is_admin) {

		$page['#attached']['library'][] = 'ubc_apsc_helper/ubc-apsc-styles';
	}

	/* Load cookiebot script first in page */
	if ($config->get('ubc_apsc_helper.cookiebot_load') && !$is_admin) {

		$current_user = \Drupal::currentUser();

		// if the user is anonymous or has a role that requires cookiebot, load the script
		if (_cookiebot_load_user_checks($config, $current_user)) {
			$library_discovery = \Drupal::service('library.discovery');
			$cookiebot_library = $library_discovery->getLibraryByName('ubc_apsc_helper', 'cookiebot-script');

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

			$page['#attached']['html_head'][] = [$cookiebot_script, 'cookiebot-script'];
			$page['#attached']['library'][] = 'ubc_apsc_helper/cookiebot-banner-styles';
		}
	}
}

/**
 * Checks whether Cookiebot should load for the given user.
 *
 * @param \Drupal\Core\Config\ImmutableConfig $config
 *   The module configuration.
 * @param \Drupal\Core\Session\AccountInterface $current_user
 *   The current user.
 *
 * @return bool
 *   TRUE if Cookiebot should load for this user, FALSE otherwise.
 */
function _cookiebot_load_user_checks(ImmutableConfig $config, AccountInterface $current_user): bool {
  if ($current_user->isAnonymous()) {
    return TRUE;
  }

  $cookiebot_user_roles = $config->get('ubc_apsc_helper.cookiebot_user_roles');

  if (!empty($cookiebot_user_roles) && is_array($cookiebot_user_roles)) {
    return !empty(array_intersect($cookiebot_user_roles, $current_user->getRoles()));
  }

  return FALSE;
}

/**
 * Implements hook_update_projects_alter().
 *
 * Hides projects from the list to avoid "No available releases found"
 * warnings on the available updates report.
 *
 * @param array<string, array<string, mixed>> $projects
 *   The projects array, keyed by project machine name.
 *
 * @see \Drupal\update\UpdateManager::getProjects()
 * @see \Drupal\Core\Utility\ProjectInfo::processInfoList()
 */
function ubc_apsc_helper_update_projects_alter(array &$projects): void
{
	// Hide a site-specific module from the list.
	unset($projects['ubc_apsc_helper']);
}
