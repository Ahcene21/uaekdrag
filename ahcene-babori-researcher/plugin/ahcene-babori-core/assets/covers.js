/* Render page 1 of an uploaded PDF to an image (PDF.js, in the browser) and use it as the publication cover. */
(function () {
	'use strict';
	var C = window.abcCovers, pdfjs = window.pdfjsLib;
	if (!C || !pdfjs) { return; }
	pdfjs.GlobalWorkerOptions.workerSrc = C.worker;

	function render(url) {
		return pdfjs.getDocument({ url: url, httpHeaders: { 'X-WP-Nonce': C.nonce }, withCredentials: true }).promise.then(function (pdf) {
			return pdf.getPage(1);
		}).then(function (page) {
			var base = page.getViewport({ scale: 1 }), scale = 720 / base.width, vp = page.getViewport({ scale: scale });
			var cv = document.createElement('canvas');
			cv.width = Math.round(vp.width); cv.height = Math.round(vp.height);
			var ctx = cv.getContext('2d');
			ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, cv.width, cv.height);
			return page.render({ canvasContext: ctx, viewport: vp }).promise.then(function () {
				return new Promise(function (res, rej) { cv.toBlob(function (b) { b ? res(b) : rej(new Error('canvas')); }, 'image/jpeg', 0.86); });
			});
		});
	}
	function upload(blob, postId) {
		return fetch(C.media, {
			method: 'POST', credentials: 'same-origin',
			headers: { 'X-WP-Nonce': C.nonce, 'Content-Type': 'image/jpeg', 'Content-Disposition': 'attachment; filename="publication-cover-' + postId + '.jpg"' },
			body: blob
		}).then(function (r) { if (!r.ok) { throw new Error('upload ' + r.status); } return r.json(); });
	}
	function saveCover(postId, att) {
		return fetch(C.route, { method: 'POST', credentials: 'same-origin', headers: { 'X-WP-Nonce': C.nonce, 'Content-Type': 'application/json' }, body: JSON.stringify({ post: postId, attachment: att }) })
			.then(function (r) { if (!r.ok) { throw new Error('save ' + r.status); } });
	}
	function pdfUrlOf(id) {
		return fetch(C.media + '/' + id, { credentials: 'same-origin', headers: { 'X-WP-Nonce': C.nonce } }).then(function (r) { return r.json(); }).then(function (m) { return m.source_url; });
	}

	/* ---- Auto mode (owner on the public publications page) ---- */
	if (C.mode === 'auto') {
		var note = document.createElement('div');
		note.setAttribute('role', 'status');
		note.style.cssText = 'position:fixed;inset-block-end:16px;inset-inline-start:16px;z-index:99999;background:#0f6b4f;color:#fff;padding:10px 16px;border-radius:999px;font:600 14px sans-serif;box-shadow:0 6px 24px rgba(0,0,0,.3)';
		document.body.appendChild(note);
		var queue = C.items.slice(), total = queue.length, ok = 0;
		(function step() {
			var it = queue.shift();
			if (!it) { note.textContent = ok ? 'Covers ready – refreshing…' : 'Could not create covers'; if (ok) { setTimeout(function () { location.reload(); }, 600); } else { setTimeout(function () { note.remove(); }, 4000); } return; }
			note.textContent = 'Creating publication covers (' + (total - queue.length) + '/' + total + ')…';
			render(it.pdf).then(function (b) { return upload(b, it.id); }).then(function (m) { return saveCover(it.id, m.id); }).then(function () { ok++; }).catch(function () {}).then(step);
		})();
		return;
	}

	/* ---- Bulk page ---- */
	if (C.mode === 'bulk') {
		var tbody = document.querySelector('#abc-covers-table tbody'), status = document.getElementById('abc-covers-status');
		var rows = {};
		C.items.forEach(function (it) {
			var tr = document.createElement('tr');
			tr.innerHTML = '<td class="c"></td><td></td><td></td><td class="s"></td>';
			tr.children[1].textContent = it.title;
			tr.children[2].textContent = it.hasPdf ? 'PDF available' : '— (no PDF)';
			tr.children[3].textContent = it.cover ? 'has cover' : (it.hasPdf ? 'missing' : '—');
			if (it.cover) { var im = document.createElement('img'); im.src = it.cover; im.style.cssText = 'width:44px;height:auto'; tr.children[0].appendChild(im); }
			tbody.appendChild(tr); rows[it.id] = tr;
		});
		document.getElementById('abc-covers-all').addEventListener('click', function () {
			var btn = this, redo = document.getElementById('abc-covers-redo').checked;
			var todo = C.items.filter(function (it) { return it.hasPdf && (redo || !it.cover); });
			if (!todo.length) { status.textContent = 'Nothing to do.'; return; }
			btn.disabled = true; var done = 0, fail = 0;
			(function next() {
				var it = todo.shift();
				if (!it) { status.textContent = 'Finished: ' + done + ' cover(s) created' + (fail ? ', ' + fail + ' failed' : '') + '.'; btn.disabled = false; return; }
				status.textContent = 'Working on: ' + it.title + ' …';
				render(it.pdf).then(function (b) { return upload(b, it.id); }).then(function (m) {
					return saveCover(it.id, m.id).then(function () {
						rows[it.id].children[3].textContent = 'done';
						rows[it.id].children[0].innerHTML = ''; var im = document.createElement('img'); im.src = m.source_url; im.style.cssText = 'width:44px;height:auto'; rows[it.id].children[0].appendChild(im);
						done++;
					});
				}).catch(function (e) { fail++; rows[it.id].children[3].textContent = 'failed (' + e.message + ')'; }).then(next);
			})();
		});
		return;
	}

	/* ---- Edit screen: button under the PDF field ---- */
	var pdfInput = document.querySelector('input[name$="[pub_pdf_id]"]'), coverInput = document.querySelector('input[name$="[pub_cover_id]"]');
	if (!pdfInput || !coverInput) { return; }
	var host = pdfInput.closest('.abc-media');
	var btn = document.createElement('button'); btn.type = 'button'; btn.className = 'button'; btn.textContent = 'Generate cover from the PDF';
	var msg = document.createElement('span'); msg.style.marginInlineStart = '10px'; msg.setAttribute('role', 'status');
	var wrap = document.createElement('p'); wrap.appendChild(btn); wrap.appendChild(msg); host.parentNode.insertBefore(wrap, host.nextSibling);
	btn.addEventListener('click', function () {
		var pdfId = parseInt(pdfInput.value, 10), postId = parseInt(document.getElementById('post_ID').value, 10);
		var extInput = document.querySelector('input[name$="[pub_pdf_url]"]');
		var source;
		if (pdfId) { source = pdfUrlOf(pdfId); }
		else if (extInput && extInput.value.trim() && postId) { source = Promise.resolve(C.proxy + (C.proxy.indexOf('?') > -1 ? '&' : '?') + 'post=' + postId); }
		else { msg.textContent = 'Choose an uploaded PDF (or save the publication with an external PDF link) first.'; return; }
		btn.disabled = true; msg.textContent = 'Reading the PDF…';
		source.then(render).then(function (b) { msg.textContent = 'Saving…'; return upload(b, postId); }).then(function (m) {
			coverInput.value = m.id;
			var wrapC = coverInput.closest('.abc-media');
			wrapC.querySelector('.abc-media__name').textContent = (m.slug || 'cover') + '.jpg';
			var cl = wrapC.querySelector('.abc-media__clear'); if (cl) { cl.hidden = false; }
			var pv = wrapC.querySelector('.abc-media__preview');
			if (!pv) { pv = document.createElement('img'); pv.className = 'abc-media__preview'; pv.style.cssText = 'max-width:160px;height:auto;display:block;margin:6px 0'; wrapC.insertBefore(pv, wrapC.firstChild); }
			pv.src = m.source_url; pv.hidden = false;
			msg.textContent = 'Cover created – press Update to save the publication.';
		}).catch(function (e) { msg.textContent = 'Could not create the cover (' + e.message + '). Is the PDF uploaded to this site?'; }).then(function () { btn.disabled = false; });
	});
})();
