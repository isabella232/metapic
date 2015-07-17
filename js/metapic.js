(function ($) {
	var pluginName = 'metapic';
	tinymce.PluginManager.add(pluginName, function (editor, url) {
		var dom, currentImage, currentMode;


		editor.on("init", function () {
			dom = editor.dom;
		});

		// Register a command so that it can be invoked by using tinyMCE.activeEditor.execCommand( 'WP_Link' );
		editor.addCommand('Metapic', function () {
			editor.insertContent('<strong>Metapic is awesome!</strong>');
		});
		editor.addShortcut('ctrl+m', '', 'Metapic');

		$(document).on("metapicReturn", function (data) {
			var returnData = data.text,
				selection = $(editor.selection.getNode());

			console.log(returnData);
			switch (currentMode) {
				case "image":
					returnData = $(returnData);
					selection = $(editor.selection.getNode());
					selection.attr("data-metapic-id", returnData.attr("data-metapic-id"));
					selection.attr("data-metapic-tags", returnData.attr("data-metapic-tags"));
					break;
				default:
					if (selection.is("img")) {
						if (selection.parent().is("a")) {
							selection.parent().replaceWith(returnData);
						}
						else {
							selection.replaceWith(returnData);
						}
					}
					else {
						editor.insertContent(data.text);
					}
					break;
			}
		});

		var metapicButton;
		var linkButton = pluginName + 'link';
		editor.addButton(linkButton, {
			title: editor.getLang('metapic.linkTitle'),
			text: editor.getLang('metapic.linkText'),
			icon: false,
			onclick: function () {
				currentMode = "text";
				var selection = $(editor.selection.getContent());
				var contentConfig = (selection.is("img")) ? {} : {format : 'text'};
				$.getJSON(editor.settings.mtpc_iframe_url, function (data) {
					$.event.trigger({
						type: "metapic",
						text: editor.selection.getContent(contentConfig),
						baseUrl: data['metapicApi'],
						startPage: "find/default",
						hideSidebar: true,
						randomKey: data['access_token']['access_token']
					})
				});
			},
			onPostRender: function () {
				editor.on('nodechange', setupTextButton);
				editor.on('click', setupTextButton);
			}
		});

		function setupTextButton(event) {
			if ($.trim(editor.selection.getContent()) == "") {
				editor.controlManager.setDisabled(linkButton, true);
				editor.controlManager.setActive(linkButton, false);
			}
			else {
				editor.controlManager.setDisabled(linkButton, false);
				if (editor.selection.getNode().nodeName == "A") {
					editor.controlManager.setActive(linkButton, true);
				}
			}
		}

		var imageButton = pluginName + 'img';
		editor.addButton(imageButton, {
			title: editor.getLang('metapic.imageTitle'),
			icon: 'image',
			onclick: function () {
				currentMode = "image";
				$.getJSON(editor.settings.mtpc_iframe_url, function (data) {
					var src = $(editor.selection.getNode()).attr("src");
					$.event.trigger({
						type: "metapic",
						baseUrl: data['metapicApi'],
						startPage: "tag-editor",
						hideSidebar: true,
						imgSrc: src,
						//text:editor.selection.getNode(),
						randomKey: data['access_token']['access_token']
					});
				});
			},
			onPostRender: function () {
				metapicButton = this;
				editor.on('click', setupImageButton);
				editor.on('nodechange', setupImageButton);
			}
		});

		function setupImageButton(event) {
			if (editor.selection.getNode().nodeName != "IMG") {
				editor.controlManager.setDisabled(imageButton, true);
			}
			else {
				editor.controlManager.setDisabled(imageButton, false);
			}

			if ($(editor.selection.getNode()).attr("data-metapic-id")) {
				editor.controlManager.setActive(imageButton, true);

			}
			else {
				editor.controlManager.setActive(imageButton, false);
			}
		}

		var collageButton = pluginName + 'collage';
		editor.addButton(collageButton, {
			title: editor.getLang('metapic.collageTitle'),
//			text: editor.getLang('metapic.collageText'),
			image: editor.settings.mtpc_plugin_url + "/images/mtpc-collage.svg",
			onclick: function () {
				currentMode = "collage";
				$.getJSON(editor.settings.mtpc_iframe_url, function (data) {
					var src = $(editor.selection.getNode()).attr("src");
					$.event.trigger({
						type: "metapic",
						baseUrl: data['metapicApi'],
						startPage: "collage",
						imgSrc: src,
						hideSidebar: true,
						randomKey: data['access_token']['access_token']

					})
				})
			},
			onPostRender: function () {
				metapicButton = this;
				editor.on('click', setupCollageButton);
				editor.on('nodechange', setupCollageButton);
			}
		});

		function setupCollageButton(event) {
			if ($.trim(editor.selection.getContent()) != "") {
				editor.controlManager.setDisabled(collageButton, true);
			}
			else {
				editor.controlManager.setDisabled(collageButton, false);
			}
		}

		function removeButtonClasses(button) {
			button.removeClass("metapic-new").removeClass("metapic-image").removeClass("metapic-text");
			return button;
		}
	});


})(jQuery);