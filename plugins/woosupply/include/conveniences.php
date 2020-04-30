<?php
namespace LWS\WOOSUPPLY;

// don't call the file directly
if( !defined( 'ABSPATH' ) ) exit();

/**	Provided for convenience.
 *	Access to some features without including anything in modules. */
class Conveniences
{
	function getSupplierOrderStatusList()
	{
		require_once LWS_WOOSUPPLY_INCLUDES . '/supplierorder.php';
		return SupplierOrder::statusList();
	}

	/** @return -1 if the first status is lower than the second, 0 if they are equal, and 1 if the second is lower.
	 *	Status are compared relatively to their position in order status list @see getSupplierOrderStatusList().
	 *	@param $strict (bool, default false) when a status does not exist, if strict is true this function return false. Else not found status is considered as the lowest. */
	function cmpOrderStatus($status1, $status2, $strict=false)
	{
		$list = array_keys($this->getSupplierOrderStatusList());
		$p1 = array_search($status1, $list);
		$p2 = array_search($status2, $list);
		if( !$strict )
		{
			if( $p1 === false ) $p1 = -1;
			if( $p2 === false ) $p2 = -1;
		}
		else if( $p1 === false || $p2 === false )
			return false;

		if( $p1 < $p2 ) return -1;
		else if( $p1 > $p2 ) return 1;
		else return 0;
	}

	function formatOrderNumber($orderId)
	{
		if( !isset($this->order_prefix) )
			$this->order_prefix = \get_option('lws_woosupply_supplie_order_id_prefix', '');
		if( !isset($this->nbdigits) )
			$this->nbdigits = \get_option('lws_woosupply_supplie_order_id_digits');

		$num = empty($this->nbdigits) ? $orderId : str_pad($orderId, $this->nbdigits,'0', STR_PAD_LEFT);
		return $this->order_prefix . $num;
	}

	function getSupplierOrder($id, $getOrCreate=false)
	{
		require_once LWS_WOOSUPPLY_INCLUDES . '/supplierorder.php';
		return (empty($id) && $getOrCreate) ? SupplierOrder::create() : SupplierOrder::get($id);
	}

	function getSupplierProduct($id, $getOrCreate=false)
	{
		require_once LWS_WOOSUPPLY_INCLUDES . '/supplierproduct.php';
		return (empty($id) && $getOrCreate) ? SupplierProduct::create() : SupplierProduct::get($id);
	}

	function getSupplier($id, $getOrCreate=false)
	{
		require_once LWS_WOOSUPPLY_INCLUDES . '/supplier.php';
		return (empty($id) && $getOrCreate) ? Supplier::create() : Supplier::get($id);
	}

	function getCountryState()
	{
		require_once LWS_WOOSUPPLY_INCLUDES . '/countrystate.php';
		return CountryState::instance();
	}

	/** return a format string to use with  %1$s is city, %2$s is zip */
	function getCityZipFormat()
	{
		return \apply_filters('lws_woosupply_city_zip_format', \get_option('lws_woosupply_pdf_address_city_zip_order', 'zc')=='zc' ? '%2$s %1$s' : '%1$s, %2$s');
	}

	/** @return a new Dompdf instance @see https://github.com/dompdf/dompdf
	 * @param $content (string|false) if a content is given, it is rendered in the pdf that will be ready to be streamed.
	 * @param $paperSize (string) 'letter', 'legal', 'A4', etc.
	 * @param $orientation (string) 'portrait' or 'landscape'.
	 * @param $attributes (array) @see Dompdf::Options::set */
	function getPDF($content=false, $paperSize='A4', $orientation='portrait', $attributes=null)
	{
		if( !isset($this->autoloadDompdf) || !$this->autoloadDompdf )
		{
			require_once LWS_WOOSUPPLY_ASSETS . '/dompdf/src/Autoloader.php';
			\Dompdf\Autoloader::register();
			$this->autoloadDompdf = true;
		}

		$options = new \Dompdf\Options();
		if( empty($attributes) || !is_array($attributes) )
			$attributes = array();
		$attributes = \wp_parse_args($attributes, array(
			'isHtml5ParserEnabled' => true,
			'defaultFont' => 'DejaVu Sans',
			'isRemoteEnabled' => true,
			'isPhpEnabled' => true // allows writing html <script type="text/php">$PAGE_NUM</script> / <script type="text/php">$PAGE_COUNT</script>
		));

		$options->set($attributes);
		$dompdf = new \Dompdf\Dompdf($options);
		$contxt = stream_context_create([
			'ssl' => [
				'verify_peer' => FALSE,
				'verify_peer_name' => FALSE,
				'allow_self_signed'=> TRUE
			]
		]);
		$dompdf->setHttpContext($contxt);

		if($content !== false)
			$dompdf->loadHtml($content);

		$dompdf->setPaper($paperSize, $orientation);
		if($content !== false)
			$dompdf->render();

		return $dompdf;
	}

	function getCurrencySymbol($currency='')
	{
		$symbol = '';
		if( function_exists('\get_woocommerce_currency_symbol') )
			$symbol = \get_woocommerce_currency_symbol($currency);
		return \apply_filters('lws_woosupply_currency_symbol_get', $symbol, $currency);
	}

	function getCurrentCurrency()
	{
		$currency = '';
		if( function_exists('\get_woocommerce_currency') )
			$currency = \get_woocommerce_currency($currency);
		return \apply_filters('lws_woosupply_currency_get', $currency);
	}

	/** @param float @return string */
	function getDisplayQuantity($qty=0.0)
	{
		if( !isset($this->quantityDecimals) )
			$this->quantityDecimals = \get_option('lws_woosupply_quantity_decimals', 0);
		return \apply_filters('lws_woosupply_quantity_format', \number_format_i18n($qty, $this->quantityDecimals), $qty);
	}

	/** @param string @return float */
	function getRawQuantity($displayQty='0.0')
	{
		return \apply_filters('lws_woosupply_quantity_raw', $this->unlocaliseDecimal($displayQty), $displayQty);
	}

	/** @param float @return string */
	function getDisplayPrice($price=0.0)
	{
		if( function_exists('\wc_price') )
			$disp = $this->getWCFormatedPrice($price);
		else
			$disp = \number_format_i18n($price, 2);
		return \apply_filters('lws_woosupply_price_format', $disp, $price);
	}

	/** @param float @return string */
	private function getWCFormatedPrice($price=0.0)
	{
		if( function_exists('\wc_price') )
		{
			$args = $this->getWCArgs();
			$unformatted_price = $price;
			$negative          = $price < 0;
			$price             = apply_filters( 'raw_woocommerce_price', floatval( $negative ? $price * -1 : $price ) );
			$price             = apply_filters( 'formatted_woocommerce_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] );
			if( $negative )
				$price = '-'.$price;
		}
		return $price;
	}

	/** @return WCArgs WooCommerce default args */
	public function getWCArgs()
	{
		if( !isset($this->WCArgs) )
		{
			$this->WCArgs = array(
				'ex_tax_label'       => false,
				'currency'           => '',
				'decimal_separator'  => function_exists('wc_get_price_decimal_separator') ? \wc_get_price_decimal_separator() : '.',
				'thousand_separator' => function_exists('wc_get_price_thousand_separator') ? \wc_get_price_thousand_separator() : '',
				'decimals'           => function_exists('wc_get_price_decimals') ? \wc_get_price_decimals() : '2',
				'price_format'       => function_exists('get_woocommerce_price_format') ? \get_woocommerce_price_format() : '%2$s%1$s',
			);
			$this->WCArgs = apply_filters('wc_price_args', $this->WCArgs);
		}
		return $this->WCArgs;
	}

	/** @param float @return string */
	function getDisplayPriceWithCurrency($price=0.0, $currency='')
	{
		if( function_exists('\wc_price') )
		{
			$args = $this->getWCArgs();
			$negative          = $price < 0;
			$price = $this->getWCFormatedPrice($price);

			if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
				$price = wc_trim_zeros( $price );
			}

			$disp = ($negative ? '-' : '');
			$disp .= html_entity_decode(sprintf($args['price_format'], get_woocommerce_currency_symbol($args['currency']), $price));
		}
		else
			$disp = \number_format_i18n($price, 2) . $this->getCurrencySymbol();
		return \apply_filters('lws_woosupply_price_format', $disp, $price);
	}

	/** @param string @return float */
	function getRawPrice($displayPrice='0.0')
	{
		return \apply_filters('lws_woosupply_price_raw', $this->unlocaliseDecimal($displayPrice), $displayPrice);
	}

	/** guess a float convertion whatever localisation format.
	 *	Decimal part is sorted out by first dot or comma found from right of the string. */
	function unlocaliseDecimal($number)
	{
		$dot = strrpos($number, '.');
		$comma = strrpos($number, ',');

		if( $dot === false && $comma === false )
		{
			return intval(preg_replace('/[^\d\+\-]/', '', $number));
		}
		else
		{
			if( $dot === false )
				$sep = $comma;
			else if( $comma === false )
				$sep = $dot;
			else
				$sep = max($dot, $comma);

			$int = preg_replace('/[^\d\+\-]/', '', substr($number, 0, $sep));
			$dec = preg_replace('/[^\d]/', '', substr($number, $sep+1));
			return floatval($int.'.'.$dec);
		}
	}

	/** @param $code if set, return only the label if any or the code itself if not found.
	 * @return array of unit. */
	function getQuantityUnits($code = false)
	{
		static $units = false;
		if( false === $units )
		{
			$units = array(
				''  => '',
				'μL'  => __("Microliter", LWS_WOOSUPPLY_DOMAIN),
				'MBF' => __("1000 Board Feet", LWS_WOOSUPPLY_DOMAIN),
				'MCF' => __("1000 Cubic Feet", LWS_WOOSUPPLY_DOMAIN),
				'ACR' => __("Acre", LWS_WOOSUPPLY_DOMAIN),
				'A'   => __("Ampere", LWS_WOOSUPPLY_DOMAIN),
				'ARP' => __("Arpent", LWS_WOOSUPPLY_DOMAIN),
				'BAG' => __("Bag", LWS_WOOSUPPLY_DOMAIN),
				'BL'  => __("Bale", LWS_WOOSUPPLY_DOMAIN),
				'BBL' => __("Barrel", LWS_WOOSUPPLY_DOMAIN),
				'BFT' => __("Board Foot", LWS_WOOSUPPLY_DOMAIN),
				'BK'  => __("Book", LWS_WOOSUPPLY_DOMAIN),
				'BOT' => __("Bottle", LWS_WOOSUPPLY_DOMAIN),
				'BOX' => __("Box", LWS_WOOSUPPLY_DOMAIN),
				'BCK' => __("Bucket", LWS_WOOSUPPLY_DOMAIN),
				'BE'  => __("Bundle", LWS_WOOSUPPLY_DOMAIN),
				'BU'  => __("Bushel", LWS_WOOSUPPLY_DOMAIN),
				'CAN' => __("Can", LWS_WOOSUPPLY_DOMAIN),
				'CG'  => __("Card", LWS_WOOSUPPLY_DOMAIN),
				'CAR' => __("Carton", LWS_WOOSUPPLY_DOMAIN),
				'CE'  => __("Case", LWS_WOOSUPPLY_DOMAIN),
				'CM'  => __("Centimeter", LWS_WOOSUPPLY_DOMAIN),
				'CDS' => __("Cord", LWS_WOOSUPPLY_DOMAIN),
				'CRT' => __("Crate", LWS_WOOSUPPLY_DOMAIN),
				'CM3' => __("Cubic centimeter", LWS_WOOSUPPLY_DOMAIN),
				'DM3' => __("Cubic decimeter", LWS_WOOSUPPLY_DOMAIN),
				'FT3' => __("Cubic foot", LWS_WOOSUPPLY_DOMAIN),
				'IN3' => __("Cubic inch", LWS_WOOSUPPLY_DOMAIN),
				'M3'  => __("Cubic meter", LWS_WOOSUPPLY_DOMAIN),
				'YD3' => __("Cubic yard", LWS_WOOSUPPLY_DOMAIN),
				'DAY' => __("Day", LWS_WOOSUPPLY_DOMAIN),
				'DE'  => __("Deal", LWS_WOOSUPPLY_DOMAIN),
				'DM'  => __("Decimeter", LWS_WOOSUPPLY_DOMAIN),
				'LE'  => __("Deliverable line item", LWS_WOOSUPPLY_DOMAIN),
				'DS'  => __("Display", LWS_WOOSUPPLY_DOMAIN),
				'DZN' => __("Dozen", LWS_WOOSUPPLY_DOMAIN),
				'EA'  => __("Each", LWS_WOOSUPPLY_DOMAIN),
				'FOZ' => __("Fluid Ounce US", LWS_WOOSUPPLY_DOMAIN),
				'FT'  => __("Foot", LWS_WOOSUPPLY_DOMAIN),
				'G'   => __("Gram", LWS_WOOSUPPLY_DOMAIN),
				'GRO' => __("Gross", LWS_WOOSUPPLY_DOMAIN),
				'000' => __("Group proportion", LWS_WOOSUPPLY_DOMAIN),
				'HA'  => __("Hectare", LWS_WOOSUPPLY_DOMAIN),
				'H'   => __("Hour", LWS_WOOSUPPLY_DOMAIN),
				'CEN' => __("Hundred", LWS_WOOSUPPLY_DOMAIN),
				'IN'  => __("Inch", LWS_WOOSUPPLY_DOMAIN),
				'JOB' => __("Job", LWS_WOOSUPPLY_DOMAIN),
				'KG'  => __("Kilogram", LWS_WOOSUPPLY_DOMAIN),
				'KM'  => __("Kilometer", LWS_WOOSUPPLY_DOMAIN),
				'KIT' => __("Kit", LWS_WOOSUPPLY_DOMAIN),
				'LF'  => __("Linear Foot", LWS_WOOSUPPLY_DOMAIN),
				'L'   => __("Liter", LWS_WOOSUPPLY_DOMAIN),
				'LOT' => __("Lot", LWS_WOOSUPPLY_DOMAIN),
				'LUG' => __("Lug", LWS_WOOSUPPLY_DOMAIN),
				'M'   => __("Meter", LWS_WOOSUPPLY_DOMAIN),
				'uM'  => __("Micrometer", LWS_WOOSUPPLY_DOMAIN),
				'MI'  => __("Mile", LWS_WOOSUPPLY_DOMAIN),
				'MG'  => __("Milligram", LWS_WOOSUPPLY_DOMAIN),
				'ML'  => __("Milliliter", LWS_WOOSUPPLY_DOMAIN),
				'MM'  => __("Millimeter", LWS_WOOSUPPLY_DOMAIN),
				'MIN' => __("Minute", LWS_WOOSUPPLY_DOMAIN),
				'MON' => __("Month", LWS_WOOSUPPLY_DOMAIN),
				'NAM' => __("Nanometer", LWS_WOOSUPPLY_DOMAIN),
				'OZ'  => __("Ounce", LWS_WOOSUPPLY_DOMAIN),
				'PAC' => __("Pack", LWS_WOOSUPPLY_DOMAIN),
				'PAD' => __("Pad", LWS_WOOSUPPLY_DOMAIN),
				'PR'  => __("Pair", LWS_WOOSUPPLY_DOMAIN),
				'PAL' => __("Pallet", LWS_WOOSUPPLY_DOMAIN),
				'1'   => __("Piece", LWS_WOOSUPPLY_DOMAIN),
				'PT'  => _x("Pint", "US liquid", LWS_WOOSUPPLY_DOMAIN),
				'LB'  => __("Pound", LWS_WOOSUPPLY_DOMAIN),
				'QT'  => _x("Quart", "US liquid", LWS_WOOSUPPLY_DOMAIN),
				'RM'  => __("Ream", LWS_WOOSUPPLY_DOMAIN),
				'ROL' => __("Roll", LWS_WOOSUPPLY_DOMAIN),
				'S'   => __("Second", LWS_WOOSUPPLY_DOMAIN),
				'SET' => __("Set", LWS_WOOSUPPLY_DOMAIN),
				'ST'  => __("Sheet", LWS_WOOSUPPLY_DOMAIN),
				'SQ'  => __("Square", LWS_WOOSUPPLY_DOMAIN),
				'CM2' => __("Square centimeter", LWS_WOOSUPPLY_DOMAIN),
				'FT2' => __("Square foot", LWS_WOOSUPPLY_DOMAIN),
				'IN2' => __("Square inch", LWS_WOOSUPPLY_DOMAIN),
				'KM2' => __("Square kilometer", LWS_WOOSUPPLY_DOMAIN),
				'M2'  => __("Square meter", LWS_WOOSUPPLY_DOMAIN),
				'MI2' => __("Square mile", LWS_WOOSUPPLY_DOMAIN),
				'MM2' => __("Square millimeter", LWS_WOOSUPPLY_DOMAIN),
				'YD2' => __("Square yard", LWS_WOOSUPPLY_DOMAIN),
				'TH'  => __("Thousand", LWS_WOOSUPPLY_DOMAIN),
				'TON' => _x("Ton", "short (2000 lb)", LWS_WOOSUPPLY_DOMAIN),
				'T'   => _x("Tonne", "metric ton, 1000 kg", LWS_WOOSUPPLY_DOMAIN),
				'TU'  => __("Tube", LWS_WOOSUPPLY_DOMAIN),
				'GAL' => __("US gallon", LWS_WOOSUPPLY_DOMAIN),
				'VIA' => __("Vial", LWS_WOOSUPPLY_DOMAIN),
				'W'   => __("Watt", LWS_WOOSUPPLY_DOMAIN),
				'WK'  => __("Weeks", LWS_WOOSUPPLY_DOMAIN),
				'YD'  => __("Yard", LWS_WOOSUPPLY_DOMAIN),
				'YR'  => __("Years", LWS_WOOSUPPLY_DOMAIN),
			);
		}

		if( $code !== false )
			return isset($units[$code]) ? $units[$code] : $code;
		else
			return $units;
	}

	/** @param $file as reported by $_FILES. Your form must have the attribute enctype="multipart/form-data" to get $_FILES.
	 * @param $path relative destination directory (will be created if not exists). Base directory is {$wp_content}/uploads/woosupply_uploads.
	 * @param $denyAccess if true, a .htaccess is set @see echoFileAndDie to send it.
	 * @return (string|false) the final location (complete filename with path) of the file on serveur hard drive or false on failure. */
	function uploadFile($file, $path, $filename='', $denyAccess=true)
	{
		if( !\apply_filters('lws_woosupply_file_upload_granted', true, $file, $path, $filename, $denyAccess) )
			return false;

		if( empty($path) && $denyAccess )
			error_log("Attempt to deny access to root woosupply uploads directory. This could affect unexpected features.");

		$upload_dir = wp_upload_dir();
		$dest = \trailingslashit($upload_dir['basedir']) . 'woosupply_uploads/';
		if( !empty($path) )
			$dest .= \trim($path, DIRECTORY_SEPARATOR);

		if( $denyAccess )
		{
			if( !\wp_mkdir_p($dest) )
			{
				error_log("Cannot create uploads directory : " . $dest);
				return false;
			}
			if( !file_put_contents($dest . '.htaccess', "deny from all") )
			{
				error_log("Cannot restrict access to cache dir : " . $dest);
				return false;
			}
		}

		$dest .= \trailingslashit($upload_dir['subdir']);
		if( !\wp_mkdir_p($dest) )
		{
			error_log("Cannot create uploads subdirectory : " . $dest);
			return false;
		}

		if( empty($filename) )
			$filename = $file['name'];
		$index = 0;
		$basename = '-'.$filename;
		while( file_exists($dest . $filename) )
			$filename = ++$index . $basename;

		if( move_uploaded_file($file['tmp_name'], $dest . $filename) )
		{
			return $dest . $filename;
		}
		else
		{
			error_log("Cannot move uploaded file to uploads directory : " . $dest . $filename);
			return false;
		}
	}

	/** Out partial content @see https://github.com/tuxxin/MP4Streaming/blob/master/streamer.php */
	function echoFileAndDie($filepath, $filename='', $mime='')
	{
		if( !\apply_filters('lws_woosupply_file_access_granted', true, $filepath, $filename, $mime) )
		{
			header('HTTP/1.1 403 File access denied');
			exit;
		}

		$fp = @fopen($filepath, 'rb');
		if( !$fp )
		{
			header('HTTP/1.1 503 File unavailable');
			exit;
		}
		if( empty($mime) ) // guess mime (file could be pdf, png, zip...)
		{
			if( function_exists('mime_content_type') )
				$mime = @mime_content_type($filepath);
			else
				$mime = \wp_check_filetype($filepath)['type'];
		}
		if( empty($filename) )
			$filename = basename($filepath);

		$size   = filesize($filepath); // File size
		$length = $size;           // Content length
		$start  = 0;               // Start byte
		$end    = $size - 1;       // End byte
		if( !empty($mime) )
			header("Content-type: {$mime}");
		header("Content-Disposition: attachment; filename={$filename}");
		//header("Accept-Ranges: 0-$length");
		header("Accept-Ranges: bytes");
		if (isset($_SERVER['HTTP_RANGE'])) {
			$c_start = $start;
			$c_end   = $end;
			list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
			if (strpos($range, ',') !== false) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header("Content-Range: bytes $start-$end/$size");
				exit;
			}
			if ($range == '-') {
				$c_start = $size - substr($range, 1);
			}else{
				$range  = explode('-', $range);
				$c_start = $range[0];
				$c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
			}
			$c_end = ($c_end > $end) ? $end : $c_end;
			if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header("Content-Range: bytes $start-$end/$size");
				exit;
			}
			$start  = $c_start;
			$end    = $c_end;
			$length = $end - $start + 1;
			fseek($fp, $start);
			header('HTTP/1.1 206 Partial Content');
		}
		header("Content-Range: bytes $start-$end/$size");
		header("Content-Length: ".$length);
		$buffer = 1024 * 8;
		while(!feof($fp) && ($p = ftell($fp)) <= $end) {
			if ($p + $buffer > $end) {
				$buffer = $end - $p + 1;
			}
			set_time_limit(0);
			echo fread($fp, $buffer);
			flush();
		}
		fclose($fp);
		exit();
	}

}

?>
