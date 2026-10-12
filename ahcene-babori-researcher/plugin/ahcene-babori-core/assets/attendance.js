/* Teacher-only attendance scanner: camera -> QR/barcode -> REST record. No external services. */
(function () {
	'use strict';
	var C = window.abcAtt;
	var dlg = document.getElementById('abc-att');
	if (!C || !dlg) { return; }
	var t = C.i18n;
	var $ = function (id) { return document.getElementById(id); };
	var video = $('abc-att-video'), toggle = $('abc-att-toggle'), statusEl = $('abc-att-status');
	var dateEl = $('abc-att-date'), form = $('abc-att-form'), codeEl = $('abc-att-code');
	var countEl = $('abc-att-count'), recentEl = $('abc-att-recent');
	var module = 0, stream = null, timer = 0, busy = false, last = '', lastAt = 0, detector = null;
	var canvas = document.createElement('canvas'), ctx = canvas.getContext('2d', { willReadFrequently: true });

	function say(msg, kind) { statusEl.textContent = msg; statusEl.setAttribute('data-kind', kind || ''); }
	function beep() {
		try {
			var A = window.AudioContext || window.webkitAudioContext, a = new A(), o = a.createOscillator(), g = a.createGain();
			o.frequency.value = 880; g.gain.value = 0.08; o.connect(g); g.connect(a.destination); o.start();
			setTimeout(function () { o.stop(); a.close(); }, 120);
		} catch (e) {}
		if (navigator.vibrate) { navigator.vibrate(60); }
	}
	function api(method, body, query) {
		var url = C.endpoint + (query || '');
		return fetch(url, {
			method: method, credentials: 'same-origin',
			headers: { 'X-WP-Nonce': C.nonce, 'Content-Type': 'application/json' },
			body: body ? JSON.stringify(body) : undefined
		}).then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); });
	}
	function render(d) {
		countEl.textContent = d.count;
		recentEl.textContent = '';
		(d.recent || []).forEach(function (r) {
			var li = document.createElement('li');
			li.textContent = r.time + ' — ' + (r.name ? r.name + ' (' + r.code + ')' : r.code);
			recentEl.appendChild(li);
		});
	}
	function refresh() {
		api('GET', null, '?module=' + module + '&date=' + encodeURIComponent(dateEl.value)).then(render).catch(function () {});
	}
	function record(code) {
		code = (code || '').trim();
		if (!code || busy) { return; }
		busy = true;
		api('POST', { module: module, code: code, date: dateEl.value }).then(function (d) {
			var who = d.name ? d.name + ' (' + d.code + ')' : d.code;
			say((d.status === 'recorded' ? t.att_ok : t.att_dup) + ': ' + who, d.status);
			if (d.status === 'recorded') { beep(); }
			render(d);
		}).catch(function () { say(t.att_err, 'error'); }).then(function () { busy = false; });
	}

	function tick() {
		if (!stream) { return; }
		var done = function (v) {
			var now = Date.now();
			if (v && (v !== last || now - lastAt > 4000)) { last = v; lastAt = now; record(v); }
			timer = setTimeout(tick, 180);
		};
		if (video.readyState < 2) { timer = setTimeout(tick, 200); return; }
		if (detector) {
			detector.detect(video).then(function (r) { done(r && r[0] ? r[0].rawValue : ''); }).catch(function () { done(''); });
		} else if (window.jsQR) {
			var w = video.videoWidth, h = video.videoHeight, s = Math.min(1, 640 / w);
			canvas.width = Math.round(w * s); canvas.height = Math.round(h * s);
			ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
			var img = ctx.getImageData(0, 0, canvas.width, canvas.height);
			var q = window.jsQR(img.data, img.width, img.height, { inversionAttempts: 'dontInvert' });
			done(q ? q.data : '');
		} else { done(''); }
	}
	function start() {
		if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { say(t.att_nocam, 'error'); return; }
		navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false }).then(function (s) {
			stream = s; video.srcObject = s; video.play();
			toggle.textContent = t.att_stop; dlg.classList.add('is-scanning'); say('', '');
			if ('BarcodeDetector' in window) {
				try { detector = new window.BarcodeDetector({ formats: ['qr_code', 'code_128', 'code_39', 'ean_13', 'ean_8', 'itf', 'codabar', 'upc_a', 'pdf417', 'data_matrix'] }); } catch (e) { detector = null; }
			}
			tick();
		}).catch(function () { say(t.att_nocam, 'error'); });
	}
	function stop() {
		clearTimeout(timer);
		if (stream) { stream.getTracks().forEach(function (k) { k.stop(); }); }
		stream = null; video.srcObject = null; toggle.textContent = t.att_start; dlg.classList.remove('is-scanning');
	}

	toggle.addEventListener('click', function () { if (stream) { stop(); } else { start(); } });
	form.addEventListener('submit', function (e) { e.preventDefault(); record(codeEl.value); codeEl.value = ''; codeEl.focus(); });
	dateEl.addEventListener('change', refresh);
	dlg.querySelectorAll('[data-att-close]').forEach(function (b) { b.addEventListener('click', function () { dlg.close(); }); });
	dlg.addEventListener('close', stop);
	document.querySelectorAll('.abc-att-open').forEach(function (b) {
		b.addEventListener('click', function () {
			module = parseInt(b.getAttribute('data-module'), 10) || 0;
			$('abc-att-module').textContent = b.getAttribute('data-title') || '';
			if (!dateEl.value) { dateEl.value = C.today; }
			say('', ''); last = '';
			if (typeof dlg.showModal === 'function') { dlg.showModal(); } else { dlg.setAttribute('open', ''); }
			refresh(); start();
		});
	});
})();
