/* Ahcene Babori Academic – minimal front-end behaviour (no dependencies). */
(function () {
	'use strict';

	var $ = function (s, c) { return (c || document).querySelector(s); };

	/* Mobile navigation */
	var navBtn = $('#abt-menu-toggle'), nav = $('#abt-nav');
	if (navBtn && nav) {
		navBtn.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			navBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && nav.classList.contains('is-open')) {
				nav.classList.remove('is-open');
				navBtn.setAttribute('aria-expanded', 'false');
				navBtn.focus();
			}
		});
	}

	/* Search bar */
	var sBtn = $('#abt-search-toggle'), sBar = $('#abt-searchbar');
	if (sBtn && sBar) {
		sBtn.addEventListener('click', function () {
			var open = sBar.hasAttribute('hidden');
			if (open) { sBar.removeAttribute('hidden'); var i = $('input[type=search]', sBar); if (i) { i.focus(); } }
			else { sBar.setAttribute('hidden', ''); }
			sBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		sBar.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') { sBar.setAttribute('hidden', ''); sBtn.setAttribute('aria-expanded', 'false'); sBtn.focus(); }
		});
	}

	/* Copy citation */
	document.addEventListener('click', function (e) {
		var b = e.target.closest ? e.target.closest('[data-copy]') : null;
		if (!b) { return; }
		var src = $(b.getAttribute('data-copy'));
		if (!src || !navigator.clipboard) { return; }
		navigator.clipboard.writeText(src.textContent.trim()).then(function () {
			var span = b.querySelector('span'), old = span ? span.textContent : '';
			if (span && window.abtI18n) {
				span.textContent = window.abtI18n.copied;
				setTimeout(function () { span.textContent = old; }, 2000);
			}
		});
	});
})();
