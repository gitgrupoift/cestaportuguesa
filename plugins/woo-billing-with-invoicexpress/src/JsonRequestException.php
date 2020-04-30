<?php
namespace Webdados\InvoiceXpressWooCommerce;

class JsonRequestException extends \UnexpectedValueException {

	// code of the request error
	protected $code;
	// the message of the request error
	protected $message;

	public function __construct( $parameters = array() ) {
		// auto-populate object..
		foreach ( $parameters as $key => $value ) {
			$this->$key = $value;
		}

		parent::__construct( $this->message, $this->code );
	}
}
