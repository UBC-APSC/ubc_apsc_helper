document.querySelectorAll('iframe[data-cookieblock-src]').forEach((iframe) => {
	// Create a new div element.
	const div = document.createElement('div');
	div.classList.add('cookieconsent-optout-marketing');

	// Create and set the HTML content.
	div.innerHTML = `<div aria-label="Status message" class="message message--status js-dismiss" role="status" aria-live="polite">
      <button class="js-dismiss__trigger message__close" data-dismiss="message">×</button>
      <svg role="img" class="message__icon">
        <use xlink:href="#icon-alert"></use>
      </svg>
      <div class="message__content">Please <a href="javascript:Cookiebot.renew()">accept marketing-cookies</a> to watch this video.
            </div>
  </div>`;

	// Insert the div element before the iframe element.
	iframe.parentNode.insertBefore(div, iframe);
});
