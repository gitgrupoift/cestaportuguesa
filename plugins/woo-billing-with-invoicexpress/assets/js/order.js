jQuery(document).ready(function ($) {

	// Automatically load VAT
	$("#customer_user").on("change", function (e) {
		if (window.confirm(woocommerce_admin_meta_boxes.load_billing)) {
			var user_id = $("#customer_user").val();

			if (!user_id) {
				window.alert(woocommerce_admin_meta_boxes.no_customer_selected);
				return false;
			}

			var data = {
				user_id: user_id,
				type_to_load: "billing",
				action: "woocommerce_get_customer_details",
				security: woocommerce_admin_meta_boxes.get_customer_details_nonce
			};

			$(this)
				.closest(".edit_address")
				.block({
					message: null,
					overlayCSS: {
						background:
							"#fff url(" +
							woocommerce_admin_meta_boxes.plugin_url +
							"/assets/images/ajax-loader.gif) no-repeat center",
						opacity: 0.6
					}
				});

			$.ajax({
				url: woocommerce_admin_meta_boxes.ajax_url,
				data: data,
				type: "POST",
				success: function (response) {
					var info = response;

					if (info) {
						$("input#_billing_VAT_code").val(info.billing_VAT_code);
					}

					$(".edit_address").unblock();
				}
			});
		}
		return false;
	});

	$("a.load_customer_billing").off("click");
	$("a.load_customer_billing").on("click", function () {
		if (window.confirm(woocommerce_admin_meta_boxes.load_billing)) {
			var user_id = $("#customer_user").val();

			if (!user_id) {
				window.alert(woocommerce_admin_meta_boxes.no_customer_selected);
				return false;
			}

			var data = {
				user_id: user_id,
				type_to_load: "billing",
				action: "woocommerce_get_customer_details",
				security: woocommerce_admin_meta_boxes.get_customer_details_nonce
			};

			$(this)
				.closest(".edit_address")
				.block({
					message: null,
					overlayCSS: {
						background:
							"#fff url(" +
							woocommerce_admin_meta_boxes.plugin_url +
							"/assets/images/ajax-loader.gif) no-repeat center",
						opacity: 0.6
					}
				});

			$.ajax({
				url: woocommerce_admin_meta_boxes.ajax_url,
				data: data,
				type: "POST",
				success: function (response) {
					var info = response;

					if (info) {
						$("input#_billing_VAT_code").val(info.billing_VAT_code);
					}

					$(".edit_address").unblock();
				}
			});
		}
		return false;
	});

	$("select[name=wc_order_action]").change(function () {
		var passed = 1;

		if (
			$(this)
				.val()
				.indexOf("hd_wc_ie_plus") >= 0
		) {
			if ($("#_billing_tax_exemption_reason").length) {
				if (
					$("#_billing_tax_exemption_reason option:selected").text() ==
					"No Exemption"
				) {
					// AJAX request to get and fill custom fields
					var post_id = getParameterByName("post");

					$.ajax({
						url: ajaxurl,
						data: {
							action: "hd_wc_ie_prevent_invoice",
							orderID: post_id
						},
						dataType: "json",
						success: function (response) {
							if (response) {
								alert(
									"You have to define a tax exemption reason, or attribute taxes to the order products."
								);
								$("select[name=wc_order_action]").prop("selectedIndex", 0);
							}
						},
						error: function (errorThrown) {
							console.log(errorThrown);
						}
					});
				}
			}
		}

		// validations for transport guides
		if ($(this).val() == "hd_wc_ie_plus_generate_transport_guide") {
			// this is to support Woocommerce Inventory Manager plugin
			var val = -1;
			$("td.warehouse select").each(function () {
				if (val > 0 && val != $(this).val()) {
					passed = 0;
					alert(
						"Invalid warehouse selection. All items should come from the same warehouse to be used in transport guides."
					);
					return;
				} else {
					val = $(this).val();
				}
			});
			// Check VAT and License Plate
			var vat = $("#_billing_VAT_code").val();
			var country = $("#_billing_country").val();
			var licensePlate = $("#license_plate").val();

			// check if failed any conditions
			if (passed == 0) {
				$(this).val("");
				$(".save_order").hide();
			} else {
				$(".save_order").show();
			}
		}
	});

	function getParameterByName(name, url) {
		if (!url) url = window.location.href;
		name = name.replace(/[\[\]]/g, "\\$&");
		var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)", "i"),
			results = regex.exec(url);
		if (!results) return null;
		if (!results[2]) return "";
		return decodeURIComponent(results[2].replace(/\+/g, " "));
	}

	// Block when one of our options is choosen (may not work for all the scenarios)
	$('body.post-php.post-type-shop_order select[name=wc_order_action]').change(function () {
		if (
			$(this)
				.val()
				.indexOf('hd_wc_ie_plus') >= 0
		) {
			// Add event
			$('.post-type-shop_order form#post').on('submit', activateFormBlock);
		} else {
			// Remove event
			$('.post-type-shop_order form#post').off('submit', activateFormBlock);
		}
	});

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

	//Default refund motive
	if ( $( '#refund_reason' ).length ) {
		$( '#refund_reason' ).val( hd_wc_ie_order.default_refund_reason );
	}

});
