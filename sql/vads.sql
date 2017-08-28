/* Delete VADS payment processor from database if already installed. */
DELETE FROM `xcart_ccprocessors` WHERE `processor` = 'cc_vads.php';

/* Delete VADS payment method from database if already installed and activated. */
DELETE FROM `xcart_payment_methods` WHERE `processor_file` = 'cc_vads.php';

/* Delete all VADS payment processor language constants from database. */
DELETE FROM `xcart_languages` WHERE `name` LIKE 'lbl_vads_%' OR `name` LIKE 'txt_vads_%';

/* Insert a new payment processor VADS with default values. */
INSERT INTO `xcart_ccprocessors` VALUES ('PayZen', 'C', 'cc_vads.php', 'cc_vads.tpl', 'https://secure.payzen.eu/vads-payment/', '12345678', '1234567890123456#||#1234567890123456', 'fr#||#978', '#||#', '#||##||#GET#||#false', '5#||#Votre paiement a bien été pris en compte, vous allez être redirigé dans quelques instants.', '5#||#Une erreur s''est produite, vous allez être redirigé dans quelques instants.', '', 'Y', 'N', 'Y', '', '', '', 0, 'Y', '', 0, 'N', '0%', '0%');

/* Insert vads language constants to database. */
INSERT INTO `xcart_languages` (`code`, `name`, `value`) VALUES 
	/* English constant parameter labels */  
	('en', 'lbl_vads_cgi_url', 'Plateform URL'),
	('en', 'lbl_vads_site_id', 'Site Identifiant'),
	('en', 'lbl_vads_ctx_mode', 'Context mode'),
	('en', 'lbl_vads_test_key', 'Test mode certificate'),
	('en', 'lbl_vads_prod_key', 'Production mode certificate'),
	('en', 'lbl_vads_capture_delay', 'Capture delay'),
	('en', 'lbl_vads_currency', 'Currency'),
	('en', 'lbl_vads_language', 'Language'),
	('en', 'lbl_vads_amount_min', 'Minimum amount'),
	('en', 'lbl_vads_amount_max', 'Maximum amount'),
	('en', 'lbl_vads_valid_mode', 'Validation mode'),
		
	('en', 'lbl_vads_redir_enable', 'Automatic forward'),
	('en', 'lbl_vads_redir_suc_msg', 'Success forward message'),
	('en', 'lbl_vads_redir_suc_to', 'Success forward timeout'),
	('en', 'lbl_vads_redir_err_msg', 'Failure forward message'),
	('en', 'lbl_vads_redir_err_to', 'Failure forward timeout'),
		
	('en', 'lbl_vads_return_mode', 'Return mode'),
	('en', 'lbl_vads_return_url', 'Return URL'),
	
	/* English constant parameter descriptions */ 
	('en', 'txt_vads_cgi_url', 'Url the client will be redirected to'),
	('en', 'txt_vads_site_id', 'Site ID provided by the payment gateway'),
	('en', 'txt_vads_ctx_mode', 'Test or production mode'),
	('en', 'txt_vads_test_key', 'Certificate provided by the gateway for test'),
	('en', 'txt_vads_prod_key', 'Certificate provided by the gateway'),
	('en', 'txt_vads_capture_delay', 'Delay before banking (in days)'),
	('en', 'txt_vads_currency', 'Select the currency to be used'),
	('en', 'txt_vads_language', 'Default language on the payment page'),
	('en', 'txt_vads_amount_min', 'Minimum amount for which this payment method is available'),
	('en', 'txt_vads_amount_max', 'Maximum amount for which this payment method is available'),
	('en', 'txt_vads_valid_mode', 'If manual is selected, you will have to confirm payments manually in your bank backoffice'),
		
	('en', 'txt_vads_redir_enable', 'Redirect the client to the shop at the end of the payment process'),
	('en', 'txt_vads_redir_suc_msg', 'Message displayed before redirection after a successful payment'),
	('en', 'txt_vads_redir_suc_to', 'Time before the client is redirected after a successful payment'),
	('en', 'txt_vads_redir_err_msg', 'Message displayed before redirection after a failed payment'),
	('en', 'txt_vads_redir_err_to', 'Time before the client is redirected after a failed payment'),
		
	('en', 'txt_vads_return_mode', 'How the client will transmit the payment result'),
	('en', 'txt_vads_return_url', 'URL on which the client is redirected after payment process'),
		
	/* English default values */ 
	('en', 'txt_vads_lang_fr', 'French'),
	('en', 'txt_vads_lang_de', 'German'),
	('en', 'txt_vads_lang_en', 'English'),
	('en', 'txt_vads_lang_zh', 'Chinese'),
	('en', 'txt_vads_lang_es', 'Spanish'),
	('en', 'txt_vads_lang_it', 'Italian'),
	('en', 'txt_vads_lang_ja', 'Japanese'),
	('en', 'txt_vads_lang_pt', 'Portuguese'),
	
	('en', 'txt_vads_developped_by', 'Developped by: '),
	('en', 'txt_vads_contact_email', 'Contact us: '),
	('en', 'txt_vads_contrib_version', 'Module version: '),
	('en', 'txt_vads_version', 'Platform version: '),
	('en', 'txt_vads_cms_version', 'Tested with: '),
	('en', 'txt_vads_silent_url', 'URL server to sever to be set on the back-office PayZen : '),
	('en', 'txt_vads_mode_test', 'TEST'),
	('en', 'txt_vads_mode_prod', 'PRODUCTION'),
	('en', 'txt_vads_valid_default', 'Default'),
	('en', 'txt_vads_valid_auto', 'Auto'),
	('en', 'txt_vads_valid_manual', 'Manual'),
	('en', 'txt_vads_redir_disabled', 'Disabled'),
	('en', 'txt_vads_redir_enabled', 'Enabled'),
	('en', 'txt_vads_order_not_fnd', 'The order doesn''t exist in our database.'),
	('en', 'txt_vads_check_u_failed', 'The automatic confirmation failed. Have you correctly mentionned the server URL in the PayZen back-office ?'),
		
	/* French constant parameter labels */
	('fr', 'lbl_vads_cgi_url', 'URL de la plateforme'),
	('fr', 'lbl_vads_site_id', 'Identifiant du site'),
	('fr', 'lbl_vads_ctx_mode', 'Mode'),
	('fr', 'lbl_vads_test_key', 'Certificat en mode test'),
	('fr', 'lbl_vads_prod_key', 'Certificat en mode production'),
	('fr', 'lbl_vads_capture_delay', 'Délai avant remise en banque'),
	('fr', 'lbl_vads_currency', 'Monnaie'),
	('fr', 'lbl_vads_language', 'Langue'),
	('fr', 'lbl_vads_amount_min', 'Montant minimum'),
	('fr', 'lbl_vads_amount_max', 'Montant maximum'),
	('fr', 'lbl_vads_valid_mode', 'Mode de validation'),
		
	('fr', 'lbl_vads_redir_enable', 'Redirection automatique'),
	('fr', 'lbl_vads_redir_suc_msg', 'Message avant redirection (succès)'),
	('fr', 'lbl_vads_redir_suc_to', 'Temps avant redirection (succès)'),
	('fr', 'lbl_vads_redir_err_msg', 'Message avant redirection (échec)'),
	('fr', 'lbl_vads_redir_err_to', 'Temps avant redirection (échec)'),
		
	('fr', 'lbl_vads_return_mode', 'Mode de retour'),
	('fr', 'lbl_vads_return_url', 'URL de retour'),
	
	/* French constant parameter descriptions */ 
	('fr', 'txt_vads_cgi_url', 'Le client sera redirigé à cette adresse pour payer'),
	('fr', 'txt_vads_site_id', 'L''identifiant de votre site, disponible dans l''outil de gestion de caisse'),
	('fr', 'txt_vads_ctx_mode', 'Mode test ou production'),
	('fr', 'txt_vads_test_key', 'Certificat fourni par la plateforme de paiement pour test'),
	('fr', 'txt_vads_prod_key', 'Certificat fourni par la plateforme de paiement'),
	('fr', 'txt_vads_capture_delay', 'Délai avant remise en banque (en jours)'),
	('fr', 'txt_vads_currency', 'Sélectionner la monnaie à utiliser'),
	('fr', 'txt_vads_language', 'Langue par défaut de la page de paiement'),
	('fr', 'txt_vads_amount_min', 'Montant minimum pour lequel cette methode de paiement est disponible'),
	('fr', 'txt_vads_amount_max', 'Montant maximum pour lequel cette methode de paiement est disponible'),
	('fr', 'txt_vads_valid_mode', 'En mode manuel, vous devrez confirmer les paiements dans l''outil de gestion de caisse'),
		
	('fr', 'txt_vads_redir_enable', 'Rediriger le client vers la boutique à la fin du processus de paiement'),
	('fr', 'txt_vads_redir_suc_msg', 'Message affiché au client avant qu''il soit redirigé vers la boutique après un paiement réussi'),
	('fr', 'txt_vads_redir_suc_to', 'Temps en secondes avant que le client soit redirigé vers la boutique après un paiement réussi'),
	('fr', 'txt_vads_redir_err_msg', 'Message affiché au client avant qu''il soit redirigé vers la boutique après l''échec du paiement'),
	('fr', 'txt_vads_redir_err_to', 'Temps en secondes avant que le client soit redirigé vers la boutique après l''échec du paiement'),
		
	('fr', 'txt_vads_return_mode', 'Façon dont le client transmettra le résultat du paiement lors de son retour sur la boutique'),
	('fr', 'txt_vads_return_url', 'Url vers laquelle le client sera redirigé à la fin du processus de paiement'),
		
	/* French default values */
	('fr', 'txt_vads_lang_fr', 'Français'),
	('fr', 'txt_vads_lang_de', 'Allemand'),
	('fr', 'txt_vads_lang_en', 'Anglais'),
	('fr', 'txt_vads_lang_zh', 'Chinois'),
	('fr', 'txt_vads_lang_es', 'Espagnol'),
	('fr', 'txt_vads_lang_it', 'Italien'),
	('fr', 'txt_vads_lang_ja', 'Japonais'),
	('fr', 'txt_vads_lang_pt', 'Portugais'),
	
	('fr', 'txt_vads_developped_by', 'Développé par: '),
	('fr', 'txt_vads_contact_email', 'Courriel de contact: '),
	('fr', 'txt_vads_contrib_version', 'Version du module: '),
	('fr', 'txt_vads_version', 'Version de la plateforme: '),
	('fr', 'txt_vads_cms_version', 'Testé avec: '),
	('fr', 'txt_vads_silent_url', 'URL serveur à  serveur à  renseigner sur le back-office PayZen de votre boutique: '),
	('fr', 'txt_vads_mode_test', 'TEST'),
	('fr', 'txt_vads_mode_prod', 'PRODUCTION'),
	('fr', 'txt_vads_valid_default', 'Par défaut'),
	('fr', 'txt_vads_valid_auto', 'Automatique'),
	('fr', 'txt_vads_valid_manual', 'Manuelle'),
	('fr', 'txt_vads_redir_disabled', 'Désactivée'),
	('fr', 'txt_vads_redir_enabled', 'Activée'),
	('fr', 'txt_vads_order_not_fnd', 'La commande n''existe pas en base de données.'),
	('fr', 'txt_vads_check_u_failed', 'La confirmation automatique n''a pas fonctionné. Avez-vous renseigné correctement l''url serveur dans le back-office PayZen ?');
