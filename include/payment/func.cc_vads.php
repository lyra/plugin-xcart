<?php
#####################################################################################################
#
#					Module pour la plateforme de paiement PayZen
#						Version : 1.0d (r�vision 28637)
#									########################
#					D�velopp� pour X-Cart
#						Version : 4.4.2
#						Compatibilit� plateforme : V2
#									########################
#					D�velopp� par Lyra Network
#						http://www.lyra-network.com/
#						29/08/2011
#						Contact : support@payzen.eu
#
#####################################################################################################

if ( !defined('XCART_START') ) { 
	header("Location: ../../");
	die("Access denied"); 
}

/**
 * @package VadsApi
 * @author Alain Dubrulle <supportvad@lyra-network.com>
 * @copyright Lyra-network.com
 * PHP classes to integrate an e-commerce solution with the payment platform supported by lyra-network.
 */

/**
 * Class managing parameters checking, form and signature building, response analysis and more
 * @package VadsApi
 * @version 2.1
 */
class VadsApi {
	// **************************************
	// PROPERTIES
	// **************************************
	/**
	 * The fields to send to the vads platform
	 * @var array[string]VadsField
	 * @access private
	 */
	var $requestParameters;
	/**
	 * Certificate to send in TEST mode
	 * @var string
	 * @access private
	 */
	var $keyTest;
	/**
	 * Certificate to send in PRODUCTION mode
	 * @var string
	 * @access private
	 */
	var $keyProd;
	/**
	 * Url of the payment page
	 * @var string
	 * @access private
	 */
	var $platformUrl;
	/**
	 * Set to true to send the redirect_* parameters
	 * @var boolean
	 * @access private
	 */
	var $redirectEnabled;
	/**
	 * SHA-1 authentication signature
	 * @var string
	 * @access private
	 */
	var $signature;
	/**
	 * Raw response sent by the platform
	 * @var VadsResponse
	 * @access private
	 */
	var $response;

	// **************************************
	// CONSTRUCTOR
	// **************************************
	/**
	 * Constructor.
	 * Initialize request fields definitions.
	 */
	function VadsApi() {
		//TODO success n'est pas utilis�
		$success = true;
		/*
		 * D�finition des param�tres de la requ�te
		 */
		// Common or long regexes
		$ans = "[^<>]"; // Any character (except the dreadful "<" and ">")
		$an63 = '#^[A-Za-z0-9]{0,63}$#';
		$an255 = '#^[A-Za-z0-9]{0,255}$#';
		$ans255 = '#^' . $ans . '{0,255}$#';
		$ans127 = '#^' . $ans . '{0,127}$#';
		$supzero = '[1-9]\d*';
		$regex_payment_cfg = '#^(SINGLE|MULTI:first=\d+;count=' . $supzero
				. ';period=' . $supzero . ')$#';
		$regex_trans_date = '#^\d{4}' . '(1[0-2]|0[1-9])'
				. '(3[01]|[1-2]\d|0[1-9])' . '(2[0-3]|[0-1]\d)' . '([0-5]\d){2}$#';//AAAAMMJJhhmmss
		$regex_mail = '#^[^@]+@[^@]+\.\w{2,4}$#'; //TODO plus restrictif
		$regex_params = '#^([^&=]+=[^&=]*)?(&[^&=]+=[^&=]*)*$#'; //name1=value1&name2=value2...

		// D�claration des param�tres, de leur valeurs par d�faut, de leur format...
		//		$this->_addRequestField('raw_signature', 'DEBUG Signature', '#^.+$#',
		//		                false);
		$this->_addRequestField('signature', 'Signature', "#^[0-9a-f]{40}$#", true);
		$this->_addRequestField('vads_action_mode', 'Action mode',
						"#^INTERACTIVE|SILENT$#", true, 11);
		$this->_addRequestField('vads_amount', 'Amount', '#^' . $supzero . '$#',
						true);
		$this->_addRequestField('vads_available_languages', 'Available languages',
						"#^(|[A-Za-z]{2}(;[A-Za-z]{2})*)$#", false, 2);
		$this->_addRequestField('vads_capture_delay', 'Capture delay', "#^\d*$#");
		$this->_addRequestField('vads_contracts', 'Contracts', $ans255);
		$this->_addRequestField('vads_contrib', 'Contribution', $ans255);
		$this->_addRequestField('vads_ctx_mode', 'Mode', "#^TEST|PRODUCTION$#",
						true);
		$this->_addRequestField('vads_currency', 'Currency', "#^\d{3}$#", true, 3);
		$this->_addRequestField('vads_cust_antecedents', 'Customer history',
						"#^NONE|NO_INCIDENT|INCIDENT$#");
		$this->_addRequestField('vads_cust_address', 'Customer address', $ans255);
		$this->_addRequestField('vads_cust_country', 'Customer country',
						"#^[A-Za-z]{2}$#", false, 2);
		$this->_addRequestField('vads_cust_email', 'Customer email', $regex_mail,
						false, 127);
		$this->_addRequestField('vads_cust_id', 'Customer id',
						$an63, false, 63);
		$this->_addRequestField('vads_cust_name', 'Customer name',
						$ans127, false, 127);
		$this->_addRequestField('vads_cust_cell_phone', 'Customer cell phone',
						$an63, false, 63);
		$this->_addRequestField('vads_cust_phone', 'Customer phone', $an63, false,
						63);
		$this->_addRequestField('vads_cust_title', 'Customer title', '#^'.$ans.'{0,63}$#', false,
						63);
		$this->_addRequestField('vads_cust_city', 'Customer city',
						'#^' . $ans . '{0,63}$#', false, 63);
		$this->_addRequestField('vads_cust_zip', 'Customer zip code', $an63, false,
						63);
		$this->_addRequestField('vads_language', 'Language', "#^[A-Za-z]{2}$#",
						false, 2);
		$this->_addRequestField('vads_order_id', 'Order id',
						"#^[A-za-z0-9]{0,12}$#", false, 12);
		$this->_addRequestField('vads_order_info', 'Order info', $ans255);
		$this->_addRequestField('vads_order_info2', 'Order info 2', $ans255);
		$this->_addRequestField('vads_order_info3', 'Order info 3', $ans255);
		$this->_addRequestField('vads_page_action', 'Page action', "#^PAYMENT$#",
						true, 7);
		$this->_addRequestField('vads_payment_cards', 'Payment cards',
						"#^[A-Za-z0-9;]{0,127}$#", false, 127);
		$this->_addRequestField('vads_payment_config', 'Payment config',
						$regex_payment_cfg, true);
		$this->_addRequestField('vads_payment_src', 'Payment source', "#^$#", false,
						0);
		$this->_addRequestField('vads_redirect_error_message',
						'Redirection error message', $ans255, false);
		$this->_addRequestField('vads_redirect_error_timeout',
						'Redirection error timeout', $ans255, false);
		$this->_addRequestField('vads_redirect_success_message',
						'Redirection success message', $ans255, false);
		$this->_addRequestField('vads_redirect_success_timeout',
						'Redirection success timeout', $ans255, false);
		$this->_addRequestField('vads_return_mode', 'Return mode',
						"#^NONE|GET|POST?$#", false, 4);
		$this->_addRequestField('vads_return_get_params', 'GET return parameters',
						$regex_params, false);
		$this->_addRequestField('vads_return_post_params',
						'POST return parameters', $regex_params, false);
		$this->_addRequestField('vads_ship_to_name', 'Shipping name',
						'#^' . $ans . '{0,127}$#', false, 127);
		$this->_addRequestField('vads_ship_to_phone_num', 'Shipping phone',
						$ans255, false, 63);
		$this->_addRequestField('vads_ship_to_street', 'Shipping street', $ans127,
						false, 127);
		$this->_addRequestField('vads_ship_to_street2', 'Shipping street (2)',
						$ans127, false, 127);
		$this->_addRequestField('vads_ship_to_state', 'Shipping state', $an63,
						false, 63);
		$this->_addRequestField('vads_ship_to_country', 'Shipping country',
						"#^[A-Za-z]{2}$#", false, 2);
		$this->_addRequestField('vads_ship_to_city', 'Shipping city',
						'#^' . $ans . '{0,63}$#', false, 63);
		$this->_addRequestField('vads_ship_to_zip', 'Shipping zip code', $an63,
						false, 63);
		$this->_addRequestField('vads_shop_name', 'Shop name', $ans127);
		$this->_addRequestField('vads_shop_url', 'Shop url', $ans127);
		$this->_addRequestField('vads_site_id', 'Site id', "#^\d{8}$#", true, 8);
		$this->_addRequestField('vads_theme_config', 'Theme', $ans255);
		$this->_addRequestField('vads_trans_date', 'Transaction date',
						$regex_trans_date, true, 14);
		$this->_addRequestField('vads_trans_id', 'Transaction id',
						"#^[0-8]\d{5}$#", true, 6);
		$this->_addRequestField('vads_url_success', 'Success url', $ans127, false,
						127);
		$this->_addRequestField('vads_url_referral', 'Referral url', $ans127,
						false, 127);
		$this->_addRequestField('vads_url_refused', 'Refused url', $ans127, false,
						127);
		$this->_addRequestField('vads_url_cancel', 'Cancel url', $ans127, false,
						127);
		$this->_addRequestField('vads_url_error', 'Error url', $ans127, false, 127);
		$this->_addRequestField('vads_url_return', 'Return url', $ans127, false,
						127);
		$this->_addRequestField('vads_user_info', 'User info', $ans255);
		$this->_addRequestField('vads_validation_mode', 'Validation mode',
						"#^[01]?$#", false, 1);
		$this->_addRequestField('vads_version', 'Gateway version', "#^V2$#", true,
						2);

		// Set some default parameters
		$success &= $this->set('vads_version', 'V2');
		$success &= $this->set('vads_page_action', 'PAYMENT');
		$success &= $this->set('vads_action_mode', 'INTERACTIVE');
		$success &= $this->set('vads_payment_config', 'SINGLE');
		$timestamp = time();
		$success &= $this->set('vads_trans_id', $this->_generateTransId($timestamp));
		$success &= $this->set('vads_trans_date', gmdate('YmdHis', $timestamp));
	}

	/**
	 * Generate a trans_id.
	 * To be independent from shared/persistent counters, we use the number of 1/10seconds since midnight,
	 * which has the appropriate format (000000-899999) and has great chances to be unique.
	 * @return string the generated trans_id
	 * @access private
	 */
	function _generateTransId($timestamp) {
		list($usec, $sec) = explode(" ", microtime()); // microseconds, php4 compatible
		$temp = ($timestamp + $usec - strtotime('today 00:00')) * 10;
		$temp = sprintf('%06d', $temp);

		return $temp;
	}

	/**
	 * Shortcut function used in constructor to build requestParameters
	 * @param string $name
	 * @param string $label
	 * @param string $regex
	 * @param boolean $required
	 * @access private
	 */
	function _addRequestField($name, $label, $regex, $required = false,
			$length = 255) {
		$this->requestParameters[$name] = new VadsField($name, $label, $regex,
				$required, $length);
	}

	// **************************************
	// INTERNATIONAL FUNCTIONS
	// **************************************

	/**
	 * Returns the iso codes of language accepted by the payment page
	 * @static
	 * @return array[int]string
	 */
	function getSupportedLanguages() {
		return array('fr', 'de', 'en', 'es', 'zh', 'it', 'ja', 'pt');
	}

	/**
	 * Return the list of currencies recognized by the vads platform
	 * @static
	 * @return array[int]VadsCurrency 
	 */
	function getSupportedCurrencies() {
		return array(
				new VadsCurrency('ARS', 32),
				new VadsCurrency('AUD', 36),
				new VadsCurrency('KHR', 116, 0),
				new VadsCurrency('CAD', 124),
				new VadsCurrency('CNY', 156, 1),
				new VadsCurrency('HRK', 191),
				new VadsCurrency('CZK', 203),
				new VadsCurrency('DKK', 208),
				new VadsCurrency('EKK', 233),
				new VadsCurrency('HKD', 344),
				new VadsCurrency('HUF', 348),
				new VadsCurrency('ISK', 352, 0),
				new VadsCurrency('IDR', 360, 0),
				new VadsCurrency('JPY', 392, 0),
				new VadsCurrency('KRW', 410, 0),
				new VadsCurrency('LVL', 428),
				new VadsCurrency('LTL', 440),
				new VadsCurrency('MYR', 458),
				new VadsCurrency('MXN', 484),
				new VadsCurrency('NZD', 554),
				new VadsCurrency('NOK', 578),
				new VadsCurrency('PHP', 608),
				new VadsCurrency('RUB', 643),
				new VadsCurrency('SGD', 702),
				new VadsCurrency('ZAR', 710),
				new VadsCurrency('SEK', 752),
				new VadsCurrency('CHF', 756),
				new VadsCurrency('THB', 764),
				new VadsCurrency('GBP', 826),
				new VadsCurrency('USD', 840),
				new VadsCurrency('TWD', 901, 1),
				new VadsCurrency('RON', 946),
				new VadsCurrency('TRY', 949),
				new VadsCurrency('XOF', 952, 0),
				new VadsCurrency('XPF', 953, 0),
				new VadsCurrency('BGN', 975),
				new VadsCurrency('EUR', 978),
				new VadsCurrency('PLN', 985),
				new VadsCurrency('BRL', 986));
	}

	/**
	 * Return a currency from its iso 3-letters code
	 * @static
	 * @param string $alpha3
	 * @return VadsCurrency
	 */
	function findCurrencyByAlphaCode($alpha3) {
		$list = VadsApi::getSupportedCurrencies();
		foreach ($list as $currency) {
			/** @var VadsCurrency $currency */
			if ($currency->alpha3 == $alpha3) {
				return $currency;
			}
		}
		return null;
	}

	/**
	 * Returns a currency form its iso numeric code
	 * @static
	 * @param int $num
	 * @return VadsCurrency
	 */
	function findCurrencyByNumCode($numeric) {
		$list = VadsApi::getSupportedCurrencies();
		foreach ($list as $currency) {
			/** @var VadsCurrency $currency */
			if ($currency->num == $numeric) {
				return $currency;
			}
		}
		return null;
	}

	/**
	 * Returns a currency numeric code from its 3-letters code
	 * @static
	 * @param string $alpha3
	 * @return int
	 */
	function getCurrencyNumCode($alpha3) {
		$currency = VadsApi::findCurrencyByAlphaCode($alpha3);
		return is_a($currency, 'VadsCurrency') ? $currency->num : null;
	}

	// **************************************
	// GETTERS/SETTERS
	// **************************************
	/**
	 * Shortcut for setting multiple values with one array
	 * @param array[string]mixed $parameters
	 * @return boolean true on success
	 */
	function setFromArray($parameters) {
		$ok = true;
		foreach ($parameters as $name => $value) {
			$ok &= $this->set($name, $value);
		}
		return $ok;
	}

	/**
	 * General getter.
	 * Retrieve an api variable from its name. Automatically add 'vads_' to the name if necessary.
	 * Example : <code><?php $siteId = $api->get('site_id'); ?></code>
	 * @param string $name
	 * @return mixed null if $name was not recognised
	 */
	function get($name) {
		if (!$name || !is_string($name)) {
			return null;
		}

		// V1/shortcut notation compatibility
		$name = (substr($name, 0, 5) != 'vads_') ? 'vads_' . $name : $name;

		if ($name == 'vads_key_test') {
			return $this->keyTest;
		} elseif ($name == 'vads_key_prod') {
			return $this->keyProd;
		} elseif ($name == 'vads_platform_url') {
			return $this->platformUrl;
		} elseif ($name == 'vads_redirect_enabled') {
			return $this->redirectEnabled;
		} elseif (array_key_exists($name, $this->requestParameters)) {
			return $this->requestParameters[$name]
					->getValue();
		} else {
			return null;
		}
	}

	/**
	 * General setter.
	 * Set an api variable with its name and the provided value. Automatically add 'vads_' to the name if necessary.
	 * Example : <code><?php $api->set('site_id', '12345678'); ?></code>
	 * @param string $name
	 * @param mixed $value
	 * @return boolean true on success
	 */
	function set($name, $value) {
		if (!$name || !is_string($name)) {
			return false;
		}

		// V1/shortcut notation compatibility
		$name = (substr($name, 0, 5) != 'vads_') ? 'vads_' . $name : $name;

		// Search appropriate setter
		if ($name == 'vads_key_test') {
			return $this->setCertificate($value, 'TEST');
		} elseif ($name == 'vads_key_prod') {
			return $this->setCertificate($value, 'PRODUCTION');
		} elseif ($name == 'vads_platform_url') {
			return $this->setPlatformUrl($value);
		} elseif ($name == 'vads_redirect_enabled') {
			return $this->setRedirectEnabled($value);
		} elseif (array_key_exists($name, $this->requestParameters)) {
			return $this->requestParameters[$name]
					->setValue($value);
		} else {
			return false;
		}
	}

	/**
	 * Set target url of the payment form
	 * @param string $url
	 * @return boolean
	 */
	function setPlatformUrl($url) {
		if (!preg_match('#https?://([^/]+/)+#', $url)) {
			return false;
		}
		$this->platformUrl = $url;
		return true;
	}

	/**
	 * Enable/disable redirect_* parameters
	 * @param mixed $enabled false, '0', a null or negative integer or 'false' to disable
	 * @return boolean
	 */
	function setRedirectEnabled($enabled) {
		$this->redirectEnabled = !(!$enabled || $enabled == '0'
				|| strtolower($enabled) == 'false');
		return true;
	}

	/**
	 * Set TEST or PRODUCTION certificate
	 * @param string $key
	 * @param string $mode
	 * @return boolean true if the certificate could be set
	 */
	function setCertificate($key, $mode) {
		// Check format
		if (!preg_match('#\d{16}#', $key)) {
			return false;
		}

		if ($mode == 'TEST') {
			$this->keyTest = $key;
		} elseif ($mode == 'PRODUCTION') {
			$this->keyProd = $key;
		} else {
			return false;
		}
		return true;
	}

	/**
	 * Return certificate according to current mode, false if mode was not set
	 * @return string|boolean
	 * @access private
	 */
	function _getCertificate() {
		switch ($this->requestParameters['vads_ctx_mode']
				->getValue()) {
			case 'TEST':
				return $this->keyTest;
				break;

			case 'PRODUCTION':
				return $this->keyProd;
				break;

			default:
				return false;
				break;
		}
	}

	/**
	 * Generate signature from a list of VadsField
	 * @param array[string]VadsField $fields
	 * @return string
	 * @access private
	 */
	function _generateSignatureFromFields($fields = null, $hashed = true) {
		$params = array();
		$fields = ($fields !== null) ? $fields : $this->requestParameters;
		foreach ($fields as $field) {
			if ($field->isRequired() || $field->isFilled()) {
				$params[$field->getName()] = $field->getValue();
			}
		}
		return $this->sign($params, $this->_getCertificate(), $hashed);
	}

	/**
	 * Public static method to compute and signature
	 * @param array[string]string $parameters payment gateway request/response parameters
	 * @param string $key shop certificate
	 * @param boolean $hashed set to false to get the raw, unhashed signature
	 * @param boolean $utf8_encode set to true if the parameters are not encoded in utf8
	 * @access public
	 * @static
	 */
	function sign($parameters, $key, $hashed = true, $utf8_encode = false) {
		$signContent = "";
		ksort($parameters);
		foreach ($parameters as $name => $value) {
			if (substr($name, 0, 5) == 'vads_') {
				$value = $utf8_encode ? utf8_encode($value) : $value;
				$signContent .= $value . '+';
			}
		}
		$signContent .= $key;
		$sign = $hashed ? sha1($signContent) : $signContent;
		return $sign;
	}

	// **************************************
	// REQUEST PREPARATION FUNCTIONS
	// **************************************
	/**
	 * Unset the value of optionnal fields if they are unvalid
	 */
	function clearInvalidOptionnalFields() {
		$fields = $this->getRequestFields();
		foreach ($fields as $field) {
			if (!$field->isValid() && !$field->isRequired()) {
				$field->setValue(null);
			}
		}
	}
	
	/**
	 * Check all payment fields
	 * @param array $errors will be filled with the name of invalid fields
	 * @return boolean
	 */
	function isRequestReady(&$errors = null) {
		$errors = is_array($errors) ? $errors : array();
		$fields = $this->getRequestFields();
		foreach ($fields as $field) {
			if (!$field->isValid()) {
				$errors[] = $field->getName();
			}
		}
		return sizeof($errors) == 0;
	}

	/**
	 * Return the list of fields to send to the payment gateway
	 * @return array[string]VadsField a list of VadsField or false if a parameter was invalid
	 * @see VadsField
	 */
	function getRequestFields() {
		$fields = $this->requestParameters;

		// Filter redirect_parameters if redirect is disabled
		if (!$this->redirectEnabled) {
			$redirectFields = array(
					'vads_redirect_success_timeout',
					'vads_redirect_success_message',
					'vads_redirect_error_timeout',
					'vads_redirect_error_message');
			foreach ($redirectFields as $fieldName) {
				unset($fields[$fieldName]);
			}
		}

		foreach ($fields as $fieldName => $field) {
			if (!$field->isFilled() && !$field->isRequired()) {
				unset($fields[$fieldName]);
			}
		}

		// Compute signature
		//		$fields['raw_signature']->setValue($this->_generateSignatureFromFields($fields, false));
		$fields['signature']->setValue($this->_generateSignatureFromFields($fields));

		// Return the list of fields
		return $fields;
	}

	/**
	 * Return the url of the payment page with urlencoded parameters (GET-like url)
	 * @return boolean|string
	 */
	function getRequestUrl() {
		$fields = $this->getRequestFields();

		$url = $this->platformUrl . '?';
		foreach ($fields as $field) {
			if ($field->isFilled()) {
				$url .= $field->getName() . '=' . rawurlencode($field->getValue())
						. '&';
			}
		}
		$url = substr($url, 0, -1); // remove last &
		return $url;
	}

	/**
	 * Return the html form to send to the payment gateway
	 * @param string $enteteAdd
	 * @param string $inputType
	 * @param string $buttonValue
	 * @param string $buttonAdd
	 * @param string $buttonType
	 * @return string
	 */
	function getRequestHtmlForm($enteteAdd = '', $inputType = 'hidden',
			$buttonValue = 'Aller sur la plateforme de paiement', $buttonAdd = '',
			$buttonType = 'submit') {

		$html = "";
		$html .= '<form action="' . $this->platformUrl . '" method="POST" '
				. $enteteAdd . '>';
		$html .= "\n";
		$html .= $this->getRequestFieldsHtml('type="' . $inputType . '"');
		$html .= '<input type="' . $buttonType . '" value="' . $buttonValue . '" '
				. $buttonAdd . '/>';
		$html .= "\n";
		$html .= '</form>';
		return $html;
	}

	/**
	 * Return the html code of the form fields to send to the payment page
	 * @param string $inputAttributes
	 * @return string
	 */
	function getRequestFieldsHtml($inputAttributes = 'type="hidden"') {
		$fields = $this->getRequestFields();
		
		$html = '';
		$format = '<input name="%s" value="%s" ' . $inputAttributes . "/>\n";
		foreach ($fields as $field) {
			if ($field->isFilled()) {
				$html .= sprintf($format, $field->getName(), $field->getValue());
			}
		}
		return $html;
	}

	// **************************************
	// RESPONSE ANALYSIS FUNCTIONS
	// **************************************
	/**
	 * Prepare to analyse check url or return url call
	 * @param array[string]string $parameters $_REQUEST by default
	 * @param string $ctx_mode
	 * @param string $key_test
	 * @param string $key_prod
	 */
	function loadResponse($parameters = null, $ctx_mode = null, $key_test = null,
			$key_prod = null) {
		$parameters = is_null($parameters) ? $_REQUEST : $parameters;
		$parameters = VadsApi::uncharm($parameters);

		// Load site credentials if provided
		if (!is_null($ctx_mode)) {
			$this->set('vads_ctx_mode', $ctx_mode);
		}
		if (!is_null($key_test)) {
			$this->set('vads_key_test', $key_test);
		}
		if (!is_null($key_prod)) {
			$this->set('vads_key_prod', $key_prod);
		}

		$this->response = new VadsResponse();
		$this->response->load($parameters, $this->_getCertificate());
	}

	/**
	 * Return a VadsResponse object representing the result of the payment.
	 * You can provide arguments to load data as in loadResponse.
	 * @see VadsApi::loadResponse
	 * @return VadsResponse
	 */
	function getResponse($parameters = null, $ctx_mode = null, $key_test = null,
			$key_prod = null) {
		//TODO ? redondance avec loadResponse
		if (is_null($this->response)) {
			$this->loadResponse($parameters, $ctx_mode, $key_test, $key_prod);
		}
		return $this->response;
	}
	
	/**
	 * PHP is not yet a sufficiently advanced technology to be indistinguishable from magic...
	 * so don't use magic_quotes, they mess up with the gateway response analysis.
	 * 
	 * @param array $potentiallyMagicallyQuotedData
	 */
	function uncharm($potentiallyMagicallyQuotedData) {
		if (get_magic_quotes_gpc()) {
			$sane = array();
			foreach ($potentiallyMagicallyQuotedData as $k => $v) {
				$saneKey = stripslashes($k);
				$saneValue = is_array($v) ? VadsApi::uncharm($v) : stripslashes($v);
				$sane[$saneKey] = $saneValue;
			}
		} else {
			$sane = $potentiallyMagicallyQuotedData;
		}
		return $sane;
	}
}

/**
 * Class representing the result of a transaction (sent by the check url or by the client return)
 * @package VadsApi
 */
class VadsResponse {
	/**
	 * Raw response parameters array
	 * @var array
	 * @access private
	 */
	var $raw_response = array();
	/**
	 * Certificate used to check the signature
	 * @see VadsApi::sign
	 * @var boolean
	 * @access private
	 */
	var $certificate;
	/**
	 * Value of vads_result
	 * @var string
	 * @access private
	 */
	var $code;
	/**
	 * Translation of $code (vads_result)
	 * @var string
	 * @access private
	 */
	var $message;
	/**
	 * Value of vads_extra_result
	 * @var string
	 * @access private
	 */
	var $extraCode;
	/**
	 * Translation of $extraCode (vads_extra_result)
	 * @var string
	 * @access private
	 */
	var $extraMessage;
	/**
	 * Value of vads_auth_result
	 * @var string
	 * @access private
	 */
	var $authCode;
	/**
	 * Translation of $authCode (vads_auth_result)
	 * @var string
	 * @access private
	 */
	var $authMessage;
	/**
	 * Value of vads_warranty_result
	 * @var string
	 * @access private
	 */
	var $warrantyCode;
	/**
	 * Translation of $warrantyCode (vads_warranty_result)
	 * @var string
	 * @access private
	 */
	var $warrantyMessage;

	/**
	 * Associative array containing human-readable translations of response codes. Initialized to french translations.
	 * @var array
	 * @access private
	 */
	var $translation = array(
			'no_code' => '',
			'no_translation' => '',
			'results' => array(
					'00' => 'Paiement r�alis� avec succ�s',
					'02' => 'Le commer�ant doit contacter la banque du porteur',
					'05' => 'Paiement refus�',
					'17' => 'Annulation client',
					'30' => 'Erreur de format de la requ�te',
					'96' => 'Erreur technique lors du paiement'),
			'extra_results_default' => array(
					'empty' => 'Pas de contr�le effectu�',
					'00' => 'Tous les contr�les se sont d�roul�s avec succ�s',
					'02' => 'La carte a d�pass� l?encours autoris�',
					'03' => 'La carte appartient � la liste grise du commer�ant',
					'04' => 'Le pays d?�mission de la carte appartient � la liste grise du commer�ant',
					'05' => 'L?adresse IP appartient � la liste grise du commer�ant',
					'99' => 'Probl�me technique rencontr� par le serveur lors du traitement d?un des contr�les locaux'),
			'extra_results_30' => array(
					'00' => 'signature',
					'01' => 'version',
					'02' => 'merchant_site_id',
					'03' => 'transaction_id',
					'04' => 'date',
					'05' => 'validation_mode',
					'06' => 'capture_delay',
					'07' => 'config',
					'08' => 'payment_cards',
					'09' => 'amount',
					'10' => 'currency',
					'11' => 'ctx_mode',
					'12' => 'language',
					'13' => 'order_id',
					'14' => 'order_info',
					'15' => 'cust_email',
					'16' => 'cust_id',
					'17' => 'cust_title',
					'18' => 'cust_name',
					'19' => 'cust_address',
					'20' => 'cust_zip',
					'21' => 'cust_city',
					'22' => 'cust_country',
					'23' => 'cust_phone',
					'24' => 'url_success',
					'25' => 'url_refused',
					'26' => 'url_referral',
					'27' => 'url_cancel',
					'28' => 'url_return',
					'29' => 'url_error',
					'30' => 'identifier',
					'31' => 'contrib',
					'32' => 'theme_config',
					'34' => 'redirect_success_timeout',
					'35' => 'redirect_success_message',
					'36' => 'redirect_error_timeout',
					'37' => 'redirect_error_message',
					'38' => 'return_post_params',
					'39' => 'return_get_params',
					'40' => 'card_number',
					'41' => 'expiry_month',
					'42' => 'expiry_year',
					'43' => 'card_cvv',
					'44' => 'card_info',
					'45' => 'card_options',
					'46' => 'page_action',
					'47' => 'action_mode',
					'48' => 'return_mode',
					'50' => 'secure_mpi',
					'51' => 'secure_enrolled',
					'52' => 'secure_cavv',
					'53' => 'secure_eci',
					'54' => 'secure_xid',
					'55' => 'secure_cavv_alg',
					'56' => 'secure_status',
					'60' => 'payment_src',
					'61' => 'user_info',
					'62' => 'contracts',
					'70' => 'empty_params',
					'99' => 'other'),
			'auth_results' => array(
					'00' => 'transaction approuv�e ou trait�e avec succ�s',
					'02' => 'contacter l?�metteur de carte',
					'03' => 'accepteur invalide',
					'04' => 'conserver la carte',
					'05' => 'ne pas honorer',
					'07' => 'conserver la carte, conditions sp�ciales',
					'08' => 'approuver apr�s identification',
					'12' => 'transaction invalide',
					'13' => 'montant invalide',
					'14' => 'num�ro de porteur invalide',
					'30' => 'erreur de format',
					'31' => 'identifiant de l?organisme acqu�reur inconnu',
					'33' => 'date de validit� de la carte d�pass�e',
					'34' => 'suspicion de fraude',
					'41' => 'carte perdue',
					'43' => 'carte vol�e',
					'51' => 'provision insuffisante ou cr�dit d�pass�',
					'54' => 'date de validit� de la carte d�pass�e',
					'56' => 'carte absente du fichier',
					'57' => 'transaction non permise � ce porteur',
					'58' => 'transaction interdite au terminal',
					'59' => 'suspicion de fraude',
					'60' => 'l?accepteur de carte doit contacter l?acqu�reur',
					'61' => 'montant de retrait hors limite',
					'63' => 'r�gles de s�curit� non respect�es',
					'68' => 'r�ponse non parvenue ou re�ue trop tard',
					'90' => 'arr�t momentan� du syst�me',
					'91' => '�metteur de cartes inaccessible',
					'96' => 'mauvais fonctionnement du syst�me',
					'94' => 'transaction dupliqu�e',
					'97' => '�ch�ance de la temporisation de surveillance globale',
					'98' => 'serveur indisponible routage r�seau demand� � nouveau',
					'99' => 'incident domaine initiateur'),
			'warranty_results' => array(
					'YES' => 'Le paiement est garanti',
					'NO' => 'Le paiement n\'est pas garanti',
					'UNKNOWN' => 'Suite � une erreur technique, le paiment ne peut pas �tre garanti'));

	//TODO not tested. not used.
	//	/**
	//	 * Replace current translation entries with provided ones
	//	 * @param array $translation
	//	 */
	//	function loadTranslation($translation) {
	//		return $this->translation = $this->_recurseLoadTranslation($this->translation, $translation);
	//	}
	//
	//	/**
	//	 * Recursively load a $loaded translation array into the $original translation array
	//	 * @param array $original
	//	 * @param array $loaded
	//	 */
	//	function _recurseLoadTranslation($original, $loaded) {
	//        foreach ($original as $key => $value)  {
	//            if (is_array($value)) {
	//                $original[$key] = $this->_recurseLoadTranslation($original[$key], $array[$key]);
	//            }
	//            else if(array_key_exists($value, $loaded)) {
	//                $original[$key] =  $loaded[$value];
	//            }
	//        }
	//        return $original;
	//	}

	/**
	 * Load response codes and translations from a parameter array.
	 * @param array[string]string $raw
	 * @param boolean $authentified
	 */
	function load($raw, $certificate) {
		$this->raw_response = is_array($raw) ? $raw : array();
		$this->certificate = $certificate;

		// Get codes
		$code = $this->_findInArray('vads_result', $raw, null);
		$extraCode = $this->_findInArray('vads_extra_result', $raw, null);
		$authCode = $this->_findInArray('vads_auth_result', $raw, null);
		$warrantyCode = $this->_findInArray('vads_warranty_code', $raw, null);

		// Common translations
		$noCode = $this->translation['no_code'];
		$noTrans = $this->translation['no_translation'];

		// Result and extra result
		if ($code == null) {
			$message = $noCode;
			$extraMessage = ($extraCode == null) ? $noCode : $noTrans;
		} else {
			$message = $this->_findInArray($code, $this->translation['results'],
							$noTrans);

			if ($extraCode == null) {
				$extraMessage = $noCode;
			} elseif ($code == 30) {
				$extraMessage = $this->_findInArray($extraCode,
								$this->translation['extra_results_30'], $noTrans);
			} else {
				$extraMessage = $this->_findInArray($extraCode,
								$this->translation['extra_results_default'], $noTrans);
			}
		}

		// auth_result
		if ($authCode == null) {
			$authMessage = $noCode;
		} else {
			$authMessage = $this->_findInArray($authCode,
							$this->translation['auth_results'], $noTrans);
		}

		// warranty_result
		if ($warrantyCode == null) {
			$warrantyMessage = $noCode;
		} else {
			$warrantyMessage = $this->_findInArray($warrantyCode,
							$this->translations['warranty_results'], $noTrans);
		}

		$this->code = $code;
		$this->message = $message;
		$this->authCode = $authCode;
		$this->authMessage = $authMessage;
		$this->extraCode = $extraCode;
		$this->extraMessage = $extraMessage;
		$this->warrantyCode = $warrantyCode;
		$this->warrantyMessage = $warrantyMessage;
	}

	/**
	 * Check response signature
	 * @return boolean
	 */
	function isAuthentified() {
		return VadsApi::sign($this->raw_response, $this->certificate)
				== $this->get('signature');
	}

	/**
	 * Check if the payment was successful
	 * @return boolean
	 */
	function isAcceptedPayment() {
		return $this->code == '00';
	}

	/**
	 * Check if the payment process was interrupted by the client
	 * @return boolean
	 */
	function isCancelledPayment() {
		return $this->code == '17';
	}

	/**
	 * Return the value of a response parameter.
	 * @param string $name
	 * @return string
	 */
	function get($name) {
		// Manage shortcut notations by adding 'vads_'
		if (!array_key_exists($name, $this->raw_response)) {
			$name = 'vads_' . $name;
		}
		return @$this->raw_response[$name];
	}

	/**
	 * Return the paid amount converted from cents (or currency equivalent) to a decimal value
	 * @return float
	 */
	function getFloatAmount() {
		$currency = VadsApi::findCurrencyByNumCode($this->get('currency'));
		return $currency->convertAmountToFloat($this->get('amount'));
	}

	/**
	 * Return a short description of the payment result, useful for logging
	 * @return string
	 */
	function getLogString() {
		$log = $this->code . ' : ' . $this->message;
		if ($this->code == '30') {
			$log .= ' (' . $this->extraCode . ' : ' . $this->extraMessage . ')';
		}
		return $log;
	}

	/**
	 * Return a formatted string to output as a response to the check url call
	 * @param string $case shortcut code for current situations. Most useful : payment_ok, payment_ko, auth_fail
	 * @param string $extraMessage some extra information to output to the payment gateway
	 * @return string
	 */
	function getOutputForGateway($case = '', $extraMessage = "") {
		$success = false;
		$message = '';

		// Messages pr�d�finis selon le cas
		$cases = array(
				'payment_ok' => array(true, 'Paiement valide trait�'),
				'payment_ko' => array(true, 'Paiement invalide trait�'),
				'payment_ok_already_done' => array(
						true,
						'Paiement valide trait�, d�j� enregistr�'),
				'order_not_found' => array(false, 'Impossible de retrouver la commande'),
				'payment_ko_on_order_ok' => array(
						false,
						'Code paiement invalide re�u pour une commande d�j� valid�e'),
				'auth_fail' => array(false, 'Echec authentification'),
				'ok' => array(true, ''),
				'ko' => array(false, ''));

		if (array_key_exists($case, $cases)) {
			$success = $cases[$case][0];
			$message = $cases[$case][1];
		}

		$message .= ' ' . $extraMessage;
		$message = str_replace("\n", '', $message);

		$response = '';
		$response .= '<span style="display:none">';
		$response .= $success ? "OK-" : "KO-";
		$response .= $this->get('hash');
		$response .= ($message === ' ') ? "\n" : "=$message\n";
		$response .= '</span>';
		return $response;
	}

	/**
	 * Private shortcut function
	 * @param string $value
	 * @param array[string]string $translations
	 * @param string $defaultTransation
	 * @access private
	 */
	function _findInArray($key, $array, $default) {
		if (is_array($array) && array_key_exists($key, $array)) {
			return $array[$key];
		}
		return $default;
	}
}

/**
 * Class representing a field of the form to send to the payment gateway
 * @package VadsApi
 */
class VadsField {
	/**
	 * Field's name. Matches the html input attribute
	 * @var string
	 * @access private
	 */
	var $name;
	/**
	 * Field's label in english, to be used by translation systems
	 * @var string
	 * @access private
	 */
	var $label;
	/**
	 * Field's maximum length. Matches the html text input attribute
	 * @var int
	 * @access private
	 */
	var $length;
	/**
	 * PCRE regular expression the field value must match
	 * @var string
	 * @access private
	 */
	var $regex;
	/**
	 * Whether the form requires the field to be set (even to an empty string)
	 * @var boolean
	 * @access private
	 */
	var $required;
	/**
	 * Field's value. Null or string
	 * @var string
	 * @access private
	 */
	var $value = null;

	/**
	 * Constructor
	 * @param string $name
	 * @param string $label
	 * @param string $regex
	 * @param boolean $required
	 * @param string $value
	 * @return VadsField
	 */
	function VadsField($name, $label, $regex, $required = false, $length = 255) {
		$this->name = $name;
		$this->label = $label;
		$this->regex = $regex;
		$this->required = $required;
		$this->length = $length;
	}

	/**
	 * Setter for value
	 * @param mixed $value
	 * @return boolean true if the value is valid
	 */
	function setValue($value) {
		$value = ($value === null) ? null : (string) $value;
		// We save value even if invalid (in case the validate function is too restrictive, it happened once) ...
		$this->value = $value;
		if (!$this->validate($value)) {
			// ... but we return a "false" warning
			return false;
		}
		return true;
	}

	/**
	 * Checks the current value
	 * @return boolean false if the current value is invalid or null and required
	 */
	function isValid() {
		return $this->validate($this->value);
	}

	/**
	 * Check if a value is valid for this field
	 * @param string $value
	 * @return boolean
	 */
	function validate($value) {
		if ($value === null && $this->isRequired()) {
			return false;
		}
		if ($value !== null && !preg_match($this->regex, $value)) {
			return false;
		}
		return true;
	}

	/**
	 * Setter for the required attribute
	 * @param boolean $required
	 */
	function setRequired($required) {
		$this->required = (boolean) $required;
	}

	/**
	 * Is the field required in the payment request ?
	 * @return boolean
	 */
	function isRequired() {
		return $this->required;
	}

	/**
	 * Return the current value of the field.
	 * @return string
	 */
	function getValue() {
		return $this->value;
	}

	/**
	 * Return the name (html attribute) of the field.
	 * @return string
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * Return the english human-readable name of the field.
	 * @return string
	 */
	function getLabel() {
		return $this->label;
	}

	/**
	 * Return the maximum length of the field's value.
	 * @return number
	 */
	function getLength() {
		return $this->length;
	}

	/**
	 * Has a value been set ?
	 * @return boolean
	 */
	function isFilled() {
		return !is_null($this->getValue());
	}
}

/**
 * Class representing a currency, used for converting alpha/numeric iso codes and float/integer amounts
 * @package VadsApi
 *
 */
class VadsCurrency {
	var $alpha3;
	var $num;
	var $decimals;

	function VadsCurrency($alpha3, $num, $decimals = 2) {
		$this->alpha3 = $alpha3;
		$this->num = $num;
		$this->decimals = $decimals;
	}

	function convertAmountToInteger($float) {
		$coef = pow(10, $this->decimals);

		return round(intval($float) * $coef);
	}

	function convertAmountToFloat($integer) {
		$coef = pow(10, $this->decimals);

		return floatval($integer) / $coef;
	}
}


?>