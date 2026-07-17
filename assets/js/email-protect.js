// Baut mailto-Links erst im Browser zusammen, damit die Adresse nicht als
// zusammenhängender String im HTML-Quelltext steht und von Scrapern gefunden wird.
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('[data-email-user][data-email-domain]').forEach(function (el) {
		var address = el.getAttribute('data-email-user') + '@' + el.getAttribute('data-email-domain');
		el.setAttribute('href', 'mailto:' + address);
		el.textContent = address;
	});
});
