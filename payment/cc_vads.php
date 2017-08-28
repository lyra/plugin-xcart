<?php 
#####################################################################################################
#
#					Module pour la plateforme de paiement PayZen
#						Version : 1.0d (révision 28637)
#									########################
#					Développé pour X-Cart
#						Version : 4.4.2
#						Compatibilité plateforme : V2
#									########################
#					Développé par Lyra Network
#						http://www.lyra-network.com/
#						29/08/2011
#						Contact : support@payzen.eu
#
#####################################################################################################

if (!isset($REQUEST_METHOD))
	$REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];

// VADS response manage
if (($REQUEST_METHOD == 'POST' || $REQUEST_METHOD == 'GET') && isset($_GET['mode']) && $_GET['mode'] == 'return') {
	require './auth.php';
	if (!func_is_active_payment('cc_vads.php'))
		exit;

	x_load(
    	'order',
    	'payment'
	);
	
	// Get VADS processor params
	$module_params = func_get_pm_params('cc_vads.php');
	$key_params = explode('#||#', $module_params['param03']);
	$ctx_mode = $module_params['testmode'] == 'Y' ? 'TEST' : 'PRODUCTION';
		
	// Get data from $_POST or $_GET
	$data = isset($_POST['vads_order_id']) ? $_POST : (isset($_GET['vads_order_id']) ? $_GET : null); 
	
	// If vads_return_mode == 'NONE'
	if($data == null) {
		require $xcart_dir.'/payment/payment_ccend.php';
		exit ;
	}

	require_once $xcart_dir.'/include/payment/func.cc_vads.php';
	$vads = new VadsApi();
	$resp = $vads->getResponse($data, $ctx_mode, $key_params[0], $key_params[1]);

	$from_server = isset($data['vads_hash']);
	
	// Prepare response infos
	$bill_output['sessid'] = func_query_first_cell("SELECT sessionid FROM $sql_tbl[cc_pp3_data] WHERE ref='".$resp->get('order_id')."'");
  	$bill_output['code'] = $resp->code == '00' ? 1 : 2;
    $bill_output['billmes'] = $resp->message;

	if(!empty($resp->extraMessage)) {
		$bill_output['billmes'] .= '. '.$resp->extraMessage;
	}
	if(!empty($resp->authMessage)) {
		$bill_output['billmes'] .= '. '.$resp->authMessage;
	}	
	if(!empty($resp->warrantyMessage)) {
	   	$bill_output['billmes'] .= '. '.$resp->warrantyMessage;
	}
	
	$skey = $resp->get('order_id');
	
	if(!$resp->isAuthentified()){
		if($from_server){
			exit($resp->getOutputForGateway('auth_fail'));
		}
		else {
			require $xcart_dir.'/payment/payment_ccview.php';
		}
	}

	// Retrieve order info from database
	$order_info = func_select_order($resp->get('order_id')); 
	
	// Order not found
	if(!$order_info){
		if($from_server){
			die($resp->getOutputForGateway('order_not_found'));
		}
		else {
			$bill_output['billmes'] .= '. '. func_get_langvar_by_name('txt_vads_order_not_fnd');
			require $xcart_dir.'/payment/payment_ccview.php';
		}
	}

	// Order not processed yet
	if($order_info['status'] == 'I') {
		if($resp->isAcceptedPayment()) {
			if($from_server) {
				echo($resp->getOutputForGateway('payment_ok'));
			}
			else {
				if($ctx_mode == 'TEST') {
					// Mode TEST warning : Check URL not correctly called
					$bill_output['billmes'] .= '. '. func_get_langvar_by_name('txt_vads_check_u_failed'); 
				}
			}
			require $xcart_dir.'/payment/payment_ccend.php';
		}
		else {
			if($from_server){
				echo($resp->getOutputForGateway('payment_ko'));
			}
			else {
				if ($ctx_mode == 'TEST'){
					// Mode TEST warning : Check URL not correctly called
					$bill_output['billmes'] .= '. '. func_get_langvar_by_name('txt_vads_check_u_failed'); 
				}
			}
			require $xcart_dir.'/payment/payment_ccend.php';
		}
	}
	else {
		// Order already processed
		if($resp->isAcceptedPayment()) {
			if($from_server){
				die ($resp->getOutputForGateway('payment_ok_already_done'));
			}
			else {	
				require($xcart_dir.'/payment/payment_ccview.php');
			}
		}
		else {
			if($from_server){
				die ($resp->getOutputForGateway('payment_ko_on_order_ok'));
			}
			else {
	    		require($xcart_dir.'/payment/payment_ccview.php');
			}
		}	
	}
}

// Prepare form to send to VADS payment platform for checkout
else {
	if (!defined('XCART_START')) { 
		header("Location: ../"); 
		die("Access denied"); 
	}
	
	// Load the almighty API object
	$vads = new VadsApi();
	
	// Load configuration parameters
	$key_params = explode('#||#', $module_params['param03']);
	$region_params = explode('#||#', $module_params['param04']);
	$other_params = explode('#||#', $module_params['param06']);
	$redir_success = explode('#||#', $module_params['param07']);
	$redir_error = explode('#||#', $module_params['param08']);
	
	$lang = $store_language;
	if(!in_array($lang, VadsApi::getSupportedLanguages())){
		$lang = $region_params[0] ? $region_params[0] : 'fr';
	}
	
	$order_id = join("-", $secure_oid);
	if(!$duplicate)
    	db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessionid,trstat) VALUES ('".$order_id."','".$XCARTSESSID."','GO|".implode('|',$secure_oid)."')");
	
	$config_params = array(
		'vads_platform_url' => $module_params['param01'],
		'vads_site_id' => $module_params['param02'],
		'vads_key_test' => $key_params[0],
		'vads_key_prod' => $key_params[1],
		'vads_ctx_mode' =>  $module_params['testmode'] == 'Y' ? 'TEST' : 'PRODUCTION',
	
		'vads_language' => $lang,
		'vads_currency' => $region_params[1],
		'vads_capture_delay' => $other_params[0],
		'vads_validation_mode' => $other_params[1],
	
		'vads_return_mode' => $other_params[2],
		'vads_redirect_enabled' => $other_params[3],
		'vads_redirect_success_timeout' => $redir_success[0],
		'vads_redirect_success_message' => utf8_encode($redir_success[1]),
		'vads_redirect_error_timeout' => $redir_error[0],
		'vads_redirect_error_message' => utf8_encode($redir_error[1]),
		
		'vads_url_return' => $module_params['param09'] ? $module_params['param09'] : $http_location."/payment/cc_vads.php?mode=return"
	);
	$vads->setFromArray($config_params);
	
	$order_params = array(
		'vads_amount' => 100 * $cart['total_cost'],
		'vads_contrib' => 'X-Cart4.4.2_1.0d',
		'vads_order_id' => $order_id,
	
		'vads_cust_address' => utf8_encode($userinfo['b_address'] . ' ' . $userinfo['b_address_2']),
		'vads_cust_country' => $userinfo['b_country'],
		'vads_cust_email' => $userinfo['email'],
		'vads_cust_id' => $userinfo['id'],
		'vads_cust_name' => utf8_encode($userinfo['firstname'] . ' ' . $userinfo['lastname']),
		'vads_cust_phone' => $userinfo['b_phone'],
		'vads_cust_title' => $userinfo['title'],
		'vads_cust_city' => utf8_encode($userinfo['b_city']),
		'vads_cust_zip' => utf8_encode($userinfo['b_zipcode']),
	
	    'vads_ship_to_name' => utf8_encode($userinfo['s_firstname'] . ' ' . $userinfo['s_lastname']),
	    'vads_ship_to_phone_num' => utf8_encode($userinfo['s_phone']),
		'vads_ship_to_street' => utf8_encode($userinfo['s_address']),
		'vads_ship_to_street2' => utf8_encode($userinfo['s_address_2']),
	    'vads_ship_to_city' => utf8_encode($userinfo['s_city']),
	    'vads_ship_to_state' => utf8_encode($userinfo['s_statename']),
	    'vads_ship_to_country' => utf8_encode($userinfo['s_country']),
	    'vads_ship_to_zip' => utf8_encode($userinfo['s_zipcode'])
	);
	$vads->setFromArray($order_params);
	
	$post = array();
	foreach ($vads->getRequestFields() as $field) {
		$post[$field->getName()] = $field->getValue();
	}
	
	func_create_payment_form($vads->platformUrl, $post, $module_params['module_name']);
}
exit;

?>

