<?php
namespace Webdados\InvoiceXpressWooCommerce;

class CountryTranslation {

	private $countries_en;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->countries_en = [
			'AX' => '&#197;land Islands',
			'AF' => 'Afghanistan',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'PW' => 'Belau',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BQ' => 'Bonaire, Saint Eustatius and Saba',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'VG' => 'British Virgin Islands',
			'BN' => 'Brunei',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CG' => 'Congo (Brazzaville)',
			'CD' => 'Congo (Kinshasa)',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CW' => 'Cura&Ccedil;ao',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island and McDonald Islands',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'CI' => 'Ivory Coast',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Laos',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao S.A.R., China',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'KP' => 'North Korea',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PS' => 'Palestinian Territory',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'QA' => 'Qatar',
			'IE' => 'Republic of Ireland',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russia',
			'RW' => 'Rwanda',
			'ST' => 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe',
			'BL' => 'Saint Barth&eacute;lemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'SX' => 'Saint Martin (Dutch part)',
			'MF' => 'Saint Martin (French part)',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'SM' => 'San Marino',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia/Sandwich Islands',
			'KR' => 'South Korea',
			'SS' => 'South Sudan',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syria',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom (UK)',
			'US' => 'United States (US)',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VA' => 'Vatican',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'WF' => 'Wallis and Futuna',
			'EH' => 'Western Sahara',
			'WS' => 'Western Samoa',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		];
	}

	/**
	 * Get countries list.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_countries() {
		return $this->countries_en;
	}

	/**
	 * Translate WooCommerce countries by IX countries.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public static function translate( $country ) {
		$translation = $country;
		switch ( $country ) {
			case 'Åland Islands':
				return 'Finland';

			case '&#197;land Islands':
				return 'Finland';

			case 'Antarctica':
				return 'United States';

			case 'Belau':
				return 'Palau';

			case 'Bonaire, Saint Eustatius and Saba':
				return 'Netherlands';

			case 'Bouvet Island':
				return 'Norway';

			case 'French Southern Territories':
				return 'France';

			case 'Macao S.A.R., China':
				return 'Macau';

			case 'Macedonia':
				return 'Macedonia (Former Yug. Rep.)';

			case 'Marshall Islands':
				return 'United States';

			case 'North Korea':
				return 'Korea, North';

			case 'Palestinian Territory':
				return 'Palestine';

			case 'Pitcairn':
				return 'Pitcairn Island';

			case 'Saint Barthélemy':
				return 'France';

			case 'Saint Helena':
				return 'St. Helena';

			case 'Saint Kitts and Nevis':
				return 'St. Kitts and Nevis';

			case 'Saint Lucia':
				return 'St. Lucia';

			case 'Saint Martin (French part)':
				return 'France';

			case 'Saint Martin (Dutch part)':
				return 'Netherlands';

			case 'Saint Pierre and Miquelon':
				return 'France';

			case 'Saint Vincent and the Grenadines':
				return 'UK';

			case 'South Georgia/Sandwich Islands':
				return 'UK';

			case 'South Korea':
				return 'Korea, South';

			case 'South Sudan':
				return 'Sudan';

			case 'Svalbard and Jan Mayen':
				return 'Svalbard and Jan Mayen Islands';

			case 'Republic of Ireland':
				return 'Ireland';

			case 'United States (US)':
				return 'United States';

			case 'United Kingdom (UK)':
				return 'UK';

			case 'Tokelau':
				return 'UK';

			case 'Tonga':
				return 'UK';

			case 'Wallis and Futuna':
				return 'Wallis and Futuna Islands';
		}

		return $country;
	}

	/**
	 * Get language by country
	 *
	 * @since  1.0.0
	 * @param  string $country Country string.
	 * @return string
	 */
	public static function get_language( $country ) {

		switch ( $country ) {
			case 'Portugal':
			case 'Brazil':
			case 'Angola':
			case 'Mozambique':
			case 'Timor-Leste':
			case 'Cape Verde':
			case 'São Tomé and Príncipe':
			case 'Guinea-Bissau':
				return 'pt';

			case 'Spain':
			case 'Mexico':
			case 'Colombia':
			case 'Argentina':
			case 'Peru':
			case 'Venezuela':
			case 'Chile':
			case 'Ecuador':
			case 'Guatemala':
			case 'Cuba':
			case 'Bolivia':
			case 'Dominican Republic':
			case 'Honduras':
			case 'Paraguay':
			case 'El Salvador':
			case 'Nicaragua':
			case 'Costa Rica':
			case 'Panama':
			case 'Uruguay':
			case 'Equatorial Guinea':
				return 'es';
		}

		return 'en';
	}
}
