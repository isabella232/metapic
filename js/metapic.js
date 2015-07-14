(function ($) {

	tinymce.PluginManager.add('metapic', function (editor, url) {
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
			var returnElement, selection;
			switch (currentMode) {
				case "image":
					returnElement = $(data.text);
					selection = $(editor.selection.getNode());
					selection.attr("data-metapic-id", returnElement.attr("data-metapic-id"));
					selection.attr("data-metapic-tags", returnElement.attr("data-metapic-tags"));
					break;
				default:
					editor.insertContent(data.text);
					break;
			}
		});

		var metapicButton;
		editor.addButton('metapic', {
			text: 'Metapic text',
			icon: false,
			onclick: function () {
				currentMode = "text";
				$.getJSON("/?metapic_randomNummber", function (data) {
					$.event.trigger({
						type: "metapic",
						text: editor.selection.getContent(),
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
				editor.controlManager.setDisabled('metapic', true);
				editor.controlManager.setActive('metapic', false);
			}
			else {
				editor.controlManager.setDisabled('metapic', false);
				if (editor.selection.getNode().nodeName == "A") {
					editor.controlManager.setActive('metapic', true);
				}
			}
		}


		editor.addButton('metapicimg', {
			text: 'Metapic image',
			icon: false,
			onclick: function () {
				currentMode = "image";
				$.getJSON("/?metapic_randomNummber", function (data) {
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
				editor.controlManager.setDisabled('metapicimg', true);
			}
			else {
				editor.controlManager.setDisabled('metapicimg', false);
			}

			if ($(editor.selection.getNode()).attr("data-metapic-id")) {
				editor.controlManager.setActive('metapicimg', true);

			}
			else {
				editor.controlManager.setActive('metapicimg', false);
			}
		}


		editor.addButton('metapicCollage', {
			text: 'Collage',
			icon: false,
			onclick: function () {
				currentMode = "collage";
				$.getJSON("/?metapic_randomNummber", function (data) {
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

				editor.on('nodechange', function (event) {
				});
			}
		});


		function setState(button, event) {
			var editorContent = editor.selection.getContent();
			var c = editor.selection.getNode().nodeName;
			//console.log(c);

			button.removeClass("metapic-new").removeClass("metapic-image").removeClass("metapic-text");
			if (editor.selection.isCollapsed() && !button.hasClass("metapic-new")) {
				removeButtonClasses(button).addClass("metapic-new");
				//console.log("BRAND NEW");
			}
			else if (c == "IMG") {
				removeButtonClasses(button).addClass("metapic-image");
				//console.log("IT'S AN IMAGE");
			}
			else if (editorContent.length > 0) {
				removeButtonClasses(button).addClass("metapic-text");
				//console.log("IT'S TEXT");
			}
			else {
			}
			//console.log(editor.selection.isCollapsed(), editor.selection.getContent());
		}

		function removeButtonClasses(button) {
			button.removeClass("metapic-new").removeClass("metapic-image").removeClass("metapic-text");
			return button;
		}
	});


})(jQuery);