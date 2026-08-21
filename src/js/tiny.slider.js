/* Import all plugins from tiny-slider */
import { tns } from "tiny-slider";

/**
 * @file
 * Tiny Slider integration using Drupal Behaviors.
 */

document.addEventListener('DOMContentLoaded', () => {
	
      const sliders = document.querySelectorAll('[data-tiny-slider]');

      sliders.forEach((element) => {
        // Read config from data attributes
        const inlineConfig = parseInlineConfig(element);

        const defaults = {
          container: element,
          items: 1,
          slideBy: 1,
          autoplay: false,
          autoplayTimeout: 3000,
          controls: true,
          speed: 300,
          nav: true,
          loop: true,
          gutter: 0,
        };

        // Read config from defaults and then combine with data attributes
        const options = {
          ...defaults,
          ...inlineConfig,
        };

        const slider = tns(options);

        element._tinySlider = slider;
      });

	  /**
	   * Parses optional inline JSON config from data-tiny-slider attribute.
	   *
	   * Usage in Twig: <div data-tiny-slider='{"loop": false, "items": 2}'>
	   *
	   * @param {HTMLElement} element
	   * @return {Object}
	   */
	  function parseInlineConfig(element) {
		try {
		  const raw = element.dataset.tinySlider;
		  return raw ? JSON.parse(raw) : {};
		} catch (e) {
		  console.warn('[tinySlider] Invalid JSON config on element:', element, e);
		  return {};
		}
	  }
  }
);
