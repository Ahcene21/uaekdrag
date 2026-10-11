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

	/* Mega menu toggles (touch / keyboard); hover is handled by CSS */
	document.addEventListener('click', function (e) {
		var t = e.target.closest ? e.target.closest('.abt-mega-toggle') : null;
		var open = document.querySelectorAll('.abt-has-mega.is-open');
		for (var i = 0; i < open.length; i++) {
			if (!t || open[i] !== t.parentNode) { open[i].classList.remove('is-open'); open[i].querySelector('.abt-mega-toggle').setAttribute('aria-expanded', 'false'); }
		}
		if (t) {
			var li = t.parentNode, on = li.classList.toggle('is-open');
			t.setAttribute('aria-expanded', on ? 'true' : 'false');
		}
	});
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') { return; }
		var open = document.querySelectorAll('.abt-has-mega.is-open');
		for (var i = 0; i < open.length; i++) { open[i].classList.remove('is-open'); open[i].querySelector('.abt-mega-toggle').setAttribute('aria-expanded', 'false'); }
	});

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
