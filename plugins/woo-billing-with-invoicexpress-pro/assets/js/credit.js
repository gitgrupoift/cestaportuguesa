jQuery(document).ready(function($) {
	$("button.do-manual-refund").click(function() {
		reloadPageAjax();
	});

	$("button.do-api-refund").click(function() {
		reloadPageAjax();
	});

	function reloadPageAjax() {
		$.ajax({
			url: ajaxurl,
			data: {
				action: "hd_invoicexpress_reload",
				order_id: woocommerce_admin_meta_boxes.post_id
			},
			success: function(response) {
				window.location.href = window.location.href;
			},
			error: function(errorThrown) {
				console.log(errorThrown);
			}
		});
	}
});
