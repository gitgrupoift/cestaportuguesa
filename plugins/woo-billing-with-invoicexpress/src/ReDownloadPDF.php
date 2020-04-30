<?php
namespace Webdados\InvoiceXpressWooCommerce;

use Webdados\InvoiceXpressWooCommerce\JsonRequest as JsonRequest;

/* WooCommerce CRUD ready */
/* JSON API ready */

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

class ReDownloadPDF extends BaseController {

	public function __construct( Plugin $plugin ) {
		parent::__construct( $plugin );
		$this->startAPI();
	}

	private function startAPI() {
		if ( isset( $_GET['order_id'] ) && ! empty( $_GET['order_id'] ) && isset( $_GET['document_id'] ) && ! empty( $_GET['document_id'] ) && isset( $_GET['document_type'] ) && ! empty( $_GET['document_type'] ) ) {

			$order_id      = sanitize_text_field( $_GET['order_id'] );
			$document_id   = sanitize_text_field( $_GET['document_id'] );
			$document_type = sanitize_text_field( $_GET['document_type'] );

			$order_object = wc_get_order( $order_id );

			// Change order state if needed
			$this->changeOrderState( $document_id, 'finalized', $document_type );

			/* Get a PDF */
			$return = $this->getDocumentPDF( $document_id );
			if ( ! $return['success'] ) {
				$codeStr    = __( 'Code', 'woo-billing-with-invoicexpress' );
				$messageStr = __( 'Message', 'woo-billing-with-invoicexpress' );
				/* Add notice */
				$error_notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress error', 'woo-billing-with-invoicexpress' ),
					$codeStr . ': ' . $return['error_code'] . " - " . $messageStr . ': ' . $return['error_message']
				);
				echo $error_notice;
				return;
			} else {
				/* Add notice */
				$notice = sprintf(
					'<strong>%s:</strong> %s',
					__( 'InvoiceXpress', 'woo-billing-with-invoicexpress' ),
					trim(
						sprintf(
							/* translators: %1$s: document name */
							__( 'Successfully downloaded %1$s PDF file', 'woo-billing-with-invoicexpress' ),
							$this->plugin->type_names[$document_type]
						)
					)
				);
				Notices::add_notice( $notice );
			}


			$document_url = $return['object']->output->pdfUrl;
			$this->storeAndNoteDocument( $order_object, $document_url, $document_type, $document_id );
			wp_redirect( get_edit_post_link( intval( $_GET['order_id'] ), '' ) );
			die;
			//_e( 'PDF Download successfull! Refresh the parent page.', 'woo-billing-with-invoicexpress' );
			?>
			<!--<script type="text/javascript">
				window.opener.location.reload();
				window.close();
			</script>-->
			<?php
		} else {
			_e( 'Malformed url.', 'woo-billing-with-invoicexpress' );
		}
	}

}
