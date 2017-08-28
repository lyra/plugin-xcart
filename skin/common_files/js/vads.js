// Concatenate params before post form and stock them in database
function submit_form() {
	document.getElementById('param03').value = document.getElementById('test_key').value 
		+ '#||#' + document.getElementById('prod_key').value;
	
	document.getElementById('param04').value = document.getElementById('language').value 
		+ '#||#' + document.getElementById('currency').value;
	
	document.getElementById('param05').value = document.getElementById('amount_min').value 
		+ '#||#' + document.getElementById('amount_max').value;
	
	document.getElementById('param06').value = document.getElementById('capture_delay').value
		+ '#||#' + document.getElementById('valid_mode').value 
		+ '#||#' + document.getElementById('return_mode').value 
		+ '#||#' + document.getElementById('redir_enable').value;
	
	document.getElementById('param07').value = document.getElementById('redir_suc_to').value 
		+ '#||#' + document.getElementById('redir_suc_msg').value;
	
	document.getElementById('param08').value = document.getElementById('redir_err_to').value 
		+ '#||#' + document.getElementById('redir_err_msg').value;
	
	document.forms["vads_params"].submit();
}
