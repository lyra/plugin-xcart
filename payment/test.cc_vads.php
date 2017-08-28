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

if(isset($_GET) && isset($_GET['mode']) && $_GET['mode'] == 'checkout') {
	$amount_params = explode('#||#', $module_params['param05']);
	if (is_array($amount_params) && count($amount_params) == 2) {
		$amount_min = $amount_params[0];
		$amount_max = $amount_params[1];
		
		global $cart;
		
		// Chack amount min / max
	    if((!empty($amount_min) && $cart['display_subtotal'] < $amount_min)
			|| (!empty($amount_max) && $cart['display_subtotal'] > $amount_max)) {
	    	$good = false;
	    }
	}
}

?>
