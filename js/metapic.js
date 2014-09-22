(function ($) {
	tinymce.PluginManager.add('metapic', function (editor, url) {
		var dom, currentImage;
		var editorSettings = {
			access_token: window.parent.$_metapic_access_token,
			api_url: "http://api.metapic.dev"
		};

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

				var deleteElement = dom.create(elementType, {
					'data-mce-bogus': '1',
					'contenteditable': false,
					'class': 'dashicons dashicons-trash metapic'
				});

				$(dom.select(".dashicons-edit")).after(element);
				$(dom.select(".dashicons-tag")).after(deleteElement);
				$(element).on("click", function(e) {
					var metapicEditor = currentImage.MetapicEditor(editorSettings);
					metapicEditor.fireModal();
				});

				$(deleteElement).on("click", function(e) {
					var imageId = parseInt(currentImage.attr("data-metapic-id"), 10);
					if (imageId > 0) {
						var metapicEditor = currentImage.MetapicEditor(editorSettings);
						metapicEditor.deleteImage(imageId);
					}
				});
			}
		});

		editor.on( 'click', function( event ) {
			if ( event.target.nodeName === 'IMG' ) {
				currentImage = $(event.target);
			}
		});

		/*
		editor.addButton('metapic', {
			text:    'Metapic',
			icon:    false,
			onclick: function () {
				editor.insertContent('WPExplorer.com is awesome!');
			}
		});*/
	});
})(jQuery);