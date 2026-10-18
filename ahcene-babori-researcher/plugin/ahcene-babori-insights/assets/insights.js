/* Share buttons + first-party page-view beacon (no cookies, no third parties). */
(function () {
	'use strict';
	var C = window.abiCfg || {};
	function beacon(data) {
		if (!C.endpoint) { return; }
		var body = JSON.stringify(data);
		try {
			if (navigator.sendBeacon && navigator.sendBeacon(C.endpoint, new Blob([body], { type: 'application/json' }))) { return; }
		} catch (e) {}
		try { fetch(C.endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: body, keepalive: true, credentials: 'omit' }); } catch (e2) {}
	}
	function view() { beacon({ type: 'view', post: C.post || 0, ref: document.referrer || '' }); }
	if (C.track && navigator.doNotTrack !== '1') {
		if (document.visibilityState === 'prerender' || document.visibilityState === 'hidden') {
			document.addEventListener('visibilitychange', function once() { if (document.visibilityState === 'visible') { document.removeEventListener('visibilitychange', once); view(); } });
		} else { view(); }
	}
	document.querySelectorAll('.abi-share').forEach(function (box) {
		var msg = box.querySelector('.abi-share__msg'), url = box.getAttribute('data-url'), title = box.getAttribute('data-title');
		var native = box.querySelector('[data-net="native"]');
		if (native && navigator.share) { native.hidden = false; }
		box.addEventListener('click', function (e) {
			var el = e.target.closest('[data-net]');
			if (!el || !box.contains(el)) { return; }
			var net = el.getAttribute('data-net');
			if (C.track) { beacon({ type: 'share', post: C.post || 0, net: net }); }
			if (net === 'copy') {
				var done = function () { if (msg) { msg.textContent = C.copied || 'Copied'; setTimeout(function () { msg.textContent = ''; }, 2500); } };
				if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(url).then(done); }
				else { var i = document.createElement('input'); i.value = url; document.body.appendChild(i); i.select(); try { document.execCommand('copy'); done(); } catch (x) {} document.body.removeChild(i); }
			} else if (net === 'native' && navigator.share) {
				navigator.share({ title: title, url: url }).catch(function () {});
			}
		});
	});
})();
