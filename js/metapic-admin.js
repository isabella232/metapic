(function($) {
	$(document).ready(function() {
		var editContainer = $(".mtpc-deeplinking"),
			editLink = $(".edit-deeplink-status"),
			checkValue,
			checkBox = $("#deeplink-status-check"),
			hidden = $("#deeplink-status-auto"),
			originalValue = (checkBox.is(":checked")) ? 1 : 0;

		editLink.on("click", function(e) {
			e.preventDefault();
			$(this).hide();
			$($(this).attr("href")).slideDown("fast");
		});

		$(".save-deeplink-status, .cancel-deeplink-status").on("click", function(e) {
			e.preventDefault();
			var clickedElement = $(this);
			checkValue = (checkBox.is(":checked")) ? 1 : 0;

			$($(this).attr("href")).slideUp("fast", function() {
			});

			editLink.show();
			if (clickedElement.hasClass("save-deeplink-status")) {
				editContainer.find(".deeplink-status-text").hide();
				editContainer.find(".status-" + checkValue.toString()).show();
				hidden.val(checkValue);
				originalValue = checkValue;
			}
			else {
				if (clickedElement.hasClass("cancel-deeplink-status")) {
					hidden.val(originalValue);
					checkBox.prop("checked", originalValue);
				}
			}
		});

		$("#metapic-help-button").on("click", function(e) {
			e.preventDefault();
			var tokenUrl = $(this).data("tokenUrl");
			$.getJSON(tokenUrl, function (data) {
				$.event.trigger({
					type: "metapic",
					baseUrl: data['metapicApi'],
					startPage: "dashboard",
					hideSidebar: true,
					randomKey: data['access_token']['access_token']
				});
			});
		});
	});
})(jQuery);