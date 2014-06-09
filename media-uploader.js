jQuery(document).ready(function($){
	var
		custom_uploader,
		$esc = function(selector, context) {
			selector = selector.replace( /(:|\.|\[|\])/g, "\\$1" );
			if(context) context = context.replace( /(:|\.|\[|\])/g, "\\$1" );
			return $(selector, context);
		};

	$esc('#icmp[avatar-select]').click(function(e) {
		e.preventDefault();
		if(custom_uploader) {
			custom_uploader.open();
			return;
		}
		custom_uploader = wp.media({
			title: icmp_l10n.title,
			library: {type: 'image'},
			button: {text: icmp_l10n.button},
			multiple: false,
			state: 'library'
		});
		custom_uploader.on('select', function() {
			var images = custom_uploader.state().get('selection');
			images.each(function(file){
				console.log(file.toJSON());
				$esc('#icmp[avatar-url]').val(file.toJSON().url);
			});
		});
		custom_uploader.open();
	});
});