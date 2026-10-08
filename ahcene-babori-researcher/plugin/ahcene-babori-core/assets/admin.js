/* Admin helpers: media picker and relation filters. No dependencies besides jQuery + wp.media. */
(function ($) {
	'use strict';

	$(document).on('click', '.abc-media__pick', function (e) {
		e.preventDefault();
		var $wrap = $(this).closest('.abc-media');
		var mime = $wrap.data('mime');
		var opts = { title: 'Choose a file', button: { text: 'Use this file' }, multiple: false };
		if (mime) { opts.library = { type: String(mime).split(',') }; }
		var frame = wp.media(opts);
		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			$wrap.find('input[type=hidden]').val(att.id).trigger('change');
			$wrap.find('.abc-media__name').text(att.filename || att.title);
			$wrap.find('.abc-media__clear').prop('hidden', false);
			var $prev = $wrap.find('.abc-media__preview');
			if ($prev.length && att.url) { $prev.attr('src', att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url).prop('hidden', false); }
		});
		frame.open();
	});

	$(document).on('click', '.abc-media__clear', function (e) {
		e.preventDefault();
		var $wrap = $(this).closest('.abc-media');
		$wrap.find('input[type=hidden]').val('').trigger('change');
		$wrap.find('.abc-media__name').text('No file selected');
		$wrap.find('.abc-media__preview').prop('hidden', true);
		$(this).prop('hidden', true);
	});

	$(document).on('input', '.abc-posts__filter', function () {
		var q = this.value.toLowerCase();
		$(this).closest('.abc-posts').find('.abc-posts__list label').each(function () {
			this.style.display = this.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : '';
		});
	});
})(jQuery);
