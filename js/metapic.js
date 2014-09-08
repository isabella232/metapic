(function ($) {
	tinymce.PluginManager.add('metapic', function (editor, url) {
		var dom, currentImage;

		editor.on("init", function() {
			dom = editor.dom;
		});

		$(document).on('metapic.save-success', function(e, data) {
			if (currentImage) {
				currentImage.attr("data-metapic-id", data.image.id);
				currentImage.attr("data-metapic-tags", JSON.stringify(data.tags));
			}
		});

		editor.on('NodeChange', function (e) {
			var editorToolbar = dom.select("#wp-image-toolbar");
			var metapicTagIcon = dom.select(".metapic");
			if (editorToolbar.length > 0 && metapicTagIcon.length === 0) {
				var elementType = $(editorToolbar).find(">:first-child").prop('tagName');
				var element = dom.create( elementType, {
					'data-mce-bogus': '1',
					'contenteditable': false,
					'class': 'dashicons dashicons-tag metapic'
				});
				$(dom.select(".dashicons-edit")).after(element);
				$(element).on("click", function(e) {
					var metapicEditor = currentImage.MetapicEditor({
						access_token: "$2y$10$dd9QS4h6m2/.sz55NmOdx.WU0NPlehVdo61khohYKrN3NpHpz56AW"
					});
					metapicEditor.fireModal();
				});
			}
		});

		editor.on( 'click', function( event ) {
			if ( event.target.nodeName === 'IMG' ) {
				currentImage = $(event.target);
			}
		});

		editor.addButton('metapic', {
			text:    'Metapic',
			icon:    false,
			onclick: function () {
				editor.insertContent('WPExplorer.com is awesome!');
			}
		});
	});
})(jQuery);