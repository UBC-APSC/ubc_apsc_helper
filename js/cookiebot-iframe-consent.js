(function($) {
		
	const iframe = $('iframe[data-cookieblock-src]');

	// Create a new div element
	const div = $('<div>').addClass('cookieconsent-optout-marketing');

	// Insert the div element before the iframe element
	iframe.before(div);

	// Create the HTML content
	const htmlContent = `<div aria-label="Status message" class="message message--status js-dismiss" role="status" aria-live="polite">
      <button class="js-dismiss__trigger message__close" data-dismiss="message">×</button>
      <svg role="img" class="message__icon">
        <use xlink:href="#icon-alert"></use>
      </svg>
      <div class="message__content">Please <a href="javascript:Cookiebot.renew()">accept marketing-cookies</a> to watch this video.
            </div>
  </div>`;

	// Set the HTML content of the div
	div.html(htmlContent);
})(jQuery);
