(function ($) {

	tinymce.PluginManager.add('metapic', function (editor, url) {
		var dom, currentImage;


		editor.on("init", function () {
			dom = editor.dom;


        });

		// Register a command so that it can be invoked by using tinyMCE.activeEditor.execCommand( 'WP_Link' );
		editor.addCommand('Metapic', function () {
            editor.insertContent('<strong>Metapic is awesome!</strong>');
		});
		editor.addShortcut('ctrl+m', '', 'Metapic');

		/*$(document).on('metapic.save-success', function (e, data) {
			if (currentImage) {
				currentImage.attr("data-metapic-id", data.image.id);
				currentImage.attr("data-metapic-tags", JSON.stringify(data.tags));
			}
		});
        */
        $(document).on("metapicReturn", function(data){
            console.log("metapicReturn");
            console.log(data);
            editor.insertContent(data.text);
        });


		editor.on('NodeChange', function (e) {
			var editorToolbar = dom.select("#mceu_6");
			var metapicTagIcon = dom.select(".metapic");
			console.log(editor);
			if (editorToolbar.length > 0 && metapicTagIcon.length === 0) {
				var elementType = $(editorToolbar).find(">:first-child").prop('tagName');
				var element = dom.create(elementType, {
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
				$(element).on("click", function (e) {
					var metapicEditor = currentImage.MetapicEditor(editorSettings);
					metapicEditor.fireModal();
				});

				$(deleteElement).on("click", function (e) {
					var imageId = parseInt(currentImage.attr("data-metapic-id"), 10);
					if (imageId > 0) {
						var metapicEditor = currentImage.MetapicEditor(editorSettings);
						metapicEditor.deleteImage(imageId);
					}
				});
			}
		});

		editor.on('click', function (event) {
			if (event.target.nodeName === 'IMG') {
				currentImage = $(event.target);
			}
		});
		var metapicButton;
		editor.addButton('metapic', {
			text: 'Metapic text',
			icon: false,
			onclick: function () {
                $.getJSON( "/?metapic_randomNummber", function( data ) {
                    $.event.trigger({
                        type: "metapic",
                        text:editor.selection.getContent(),
                        baseUrl:data['metapicApi'],
                        startPage: "find/default",
                        hideSidebar:true,
                        randomKey:data['access_token']['access_token']
                    })
                });
			},
			onPostRender: function () {
				editor.on('nodechange', function (event) {
					setState(metapicButton, event);
				});
			}
		});


        editor.addButton('metapicimg', {
            text: 'Metapic image',
            icon: false,
            onclick: function () {
                $.getJSON( "/?metapic_randomNummber", function( data ) {
                    var src=$(editor.selection.getNode()).attr("src");
                    $.event.trigger({
                        type: "metapic",
                        baseUrl:data['metapicApi'],
                        startPage: "tag-editor",
                        hideSidebar:true,
                        imgSrc:src,
                        //text:editor.selection.getNode(),
                        randomKey:data['access_token']['access_token']
                    });
                });
            },
            onPostRender: function () {
                metapicButton = this;
                editor.on('nodechange', function (event) {
                    setState(metapicButton, event);
                });
            }
        });
        editor.addButton('metapicCollage', {
            text: 'Collage',
            icon: false,
            onclick: function () {

               // editor.insertContent('metapicCollage');

                $.getJSON( "/?metapic_randomNummber", function( data ) {
                    var src=$(editor.selection.getNode()).attr("src");
                    $.event.trigger({
                        type: "metapic",
                        baseUrl:data['metapicApi'],
                        startPage: "collage",
                        imgSrc:src,
                        hideSidebar:true,
                        randomKey:data['access_token']['access_token']

                    })
                })
            },
            onPostRender: function () {
                metapicButton = this;

                editor.on('nodechange', function (event) {
                    setState(metapicButton, event);
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