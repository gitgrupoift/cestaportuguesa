jQuery(document).ready(function ($) {
	var old_status = $("#order_status").val();
	var old_action = $("select[name=wc_order_action]").val();

	$("#order_status").on("change", function () {
		if ($(this).val() == "wc-cancelled") {
			checkIfCancelable();
		}
	});

	$("select[name=wc_order_action]").on("change", function () {
		if ($(this).val() == "hd_wc_ie_plus_cancel_document") {
			checkIfCancelable();
		}
	});

	function checkIfCancelable() {
		$(".save_order").attr("disabled", true);
		$(".wc-reload").attr("disabled", true);
		activateFormBlock();
		$.ajax({
			url: ajaxurl,
			data: {
				action: "hd_invoicexpress_cancelable",
				order_id: woocommerce_admin_meta_boxes.post_id
			},
			success: function (response) {
				deactivateFormBlock();
				if (response) {
					//console.log("ORDER IS CANCELLABLE");
				} else {
					$("#order_status").val(old_status);
					$("select[name=wc_order_action]").val(old_action);
					window.alert(
						hd_wc_ie_cancel_order.alert_message
					);
					//console.log("ORDER IS NOT CANCELLABLE");
				}
				$(".save_order").attr("disabled", false);
				$(".wc-reload").attr("disabled", false);
			},
			error: function (errorThrown) {
				deactivateFormBlock();
				console.log(errorThrown);
				$(".save_order").attr("disabled", false);
				$(".wc-reload").attr("disabled", false);
			}
		});
	}

	// Actually block it
	function activateFormBlock() {
		$('body.post-php.post-type-shop_order form#post').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
	}

	function deactivateFormBlock() {
		$('body.post-php.post-type-shop_order form#post').unblock();
	}

});
