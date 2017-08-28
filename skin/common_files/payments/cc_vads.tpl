{*
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
*}

{assign var="return_url" value="`$http_location`/payment/cc_vads.php?mode=return"}

<script type="text/javascript" src="{$SkinDir}/js/vads.js"></script>

<h1>{$module_data.module_name}</h1>
<img src="{$ImagesDir}/PayZen.jpg" alt="PayZen logo" align="right" style="padding-left: 10px;" />
<p>{$lng.txt_cc_configure_top_text}</p>

<table cellspacing="10">
<tr>
<td style="text-align: right; width: 180px;">{$lng.txt_vads_developped_by}</td>
<td><a href="http://www.lyra-network.com/" target="_blank">Lyra network</a></td>
</tr>
<tr>
<td style="text-align: right; width: 180px;">{$lng.txt_vads_contact_email}</td>
<td><a href="mailto:support@payzen.eu">support@payzen.eu</a></td>
</tr>
<tr>
<td style="text-align: right; width: 180px;">{$lng.txt_vads_contrib_version}</td>
<td>1.0d</td>
</tr>
<tr>
<td style="text-align: right; width: 180px;">{$lng.txt_vads_version}</td>
<td>V2</td>
</tr><tr>
<td style="text-align: right; width: 180px;">{$lng.txt_vads_cms_version}</td>
<td>X-Cart 4.4.2</td>
</tr>
</table>

<hr/>

{capture name=dialog}
<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post" name="vads_params">

<table cellspacing="10">
<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_site_id}:</td>
<td><input type="text" name="param02" size="20" value="{$module_data.param02}" />
	<br/><font class="SmallText">{$lng.txt_vads_site_id}</font>
</td>
</tr>

<tr>
<td><input type="hidden" name="param03" id="param03" value="" /></td>
</tr>

{assign var=key_params value="#||#"|explode:$module_data.param03}
<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_test_key}:</td>
<td><input type="text" id="test_key" size="20" value="{$key_params[0]}" />
	<br/><font class="SmallText">{$lng.txt_vads_test_key}</font>
</td>
</tr>

<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_prod_key}:</td>
<td><input type="text" id="prod_key" size="20" value="{$key_params[1]}" />
	<br/><font class="SmallText">{$lng.txt_vads_prod_key}</font>
</td>
</tr>

<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_ctx_mode}:</td>
<td>
  <select name="testmode" style="width: 110px;">
    <option value="Y" {if $module_data.testmode eq "Y"} selected="selected"{/if}>{$lng.txt_vads_mode_test}</option>
    <option value="N" {if $module_data.testmode eq "N"} selected="selected"{/if}>{$lng.txt_vads_mode_prod}</option>
  </select>
  <br/><font class="SmallText">{$lng.txt_vads_ctx_mode}</font>
</td>
</tr>

<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_cgi_url}:</td>
<td><input type="text" name="param01" size="60" value="{$module_data.param01}" />
	<br/><font class="SmallText">{$lng.txt_vads_cgi_url}</font>
</td>
</tr>

<tr>
<td><input type="hidden" name="param04" id="param04" value="" /></td>
</tr>
{assign var=region_params value="#||#"|explode:$module_data.param04}
<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_language}:</td>
<td>
	<select id="language" style="width: 170px;">
		<option value="fr" {if $region_params[0] eq "fr"} selected="selected"{/if}>{$lng.txt_vads_lang_fr}</option>
		<option value="de" {if $region_params[0] eq "de"} selected="selected"{/if}>{$lng.txt_vads_lang_de}</option>
		<option value="en" {if $region_params[0] eq "en"} selected="selected"{/if}>{$lng.txt_vads_lang_en}</option>
		<option value="zh" {if $region_params[0] eq "zh"} selected="selected"{/if}>{$lng.txt_vads_lang_zh}</option>
		<option value="es" {if $region_params[0] eq "es"} selected="selected"{/if}>{$lng.txt_vads_lang_es}</option>
		<option value="it" {if $region_params[0] eq "it"} selected="selected"{/if}>{$lng.txt_vads_lang_it}</option>
		<option value="ja" {if $region_params[0] eq "ja"} selected="selected"{/if}>{$lng.txt_vads_lang_ja}</option>
		<option value="pt" {if $region_params[0] eq "pt"} selected="selected"{/if}>{$lng.txt_vads_lang_pt}</option>
	</select>
	<br/><font class="SmallText">{$lng.txt_vads_language}</font>
</td>
<tr>

<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_currency}:</td>
<td>
	<select id="currency" style="width: 170px;">
		<option value="32"{if $region_params[1] eq "32"} selected="selected"{/if}>Argentine Peso</option>
		<option value="36"{if $region_params[1] eq "36"} selected="selected"{/if}>Australian Dollar</option>
		<option value="116"{if $region_params[1] eq "116"} selected="selected"{/if}>Riel</option>
		<option value="124"{if $region_params[1] eq "124"} selected="selected"{/if}>Canadian Dollar</option>
		<option value="156"{if $region_params[1] eq "156"} selected="selected"{/if}>Chinese Renminbi Yuan</option>
		<option value="191"{if $region_params[1] eq "191"} selected="selected"{/if}>Croatian Kuna</option>
		<option value="203"{if $region_params[1] eq "203"} selected="selected"{/if}>Czech Koruna</option>
		<option value="208"{if $region_params[1] eq "208"} selected="selected"{/if}>Danish Krone</option>
		<option value="233"{if $region_params[1] eq "233"} selected="selected"{/if}>Kroon</option>
		<option value="344"{if $region_params[1] eq "344"} selected="selected"{/if}>Hong Kong Dollar</option>
		<option value="348"{if $region_params[1] eq "348"} selected="selected"{/if}>Hungary Forint</option>
		<option value="352"{if $region_params[1] eq "352"} selected="selected"{/if}>Iceland Krona</option>
		<option value="360"{if $region_params[1] eq "360"} selected="selected"{/if}>Rupiah</option>
		<option value="392"{if $region_params[1] eq "392"} selected="selected"{/if}>Japanese Yen</option>
		<option value="410"{if $region_params[1] eq "410"} selected="selected"{/if}>South Korean Won</option>
		<option value="428"{if $region_params[1] eq "428"} selected="selected"{/if}>Latvian Lats</option>
		<option value="440"{if $region_params[1] eq "440"} selected="selected"{/if}>Lithuanian Litus</option>
		<option value="458"{if $region_params[1] eq "458"} selected="selected"{/if}>Malasian Ringgit</option>
		<option value="484"{if $region_params[1] eq "484"} selected="selected"{/if}>Mexican Peso</option>
		<option value="554"{if $region_params[1] eq "554"} selected="selected"{/if}>New Zealand Dollar</option>
		<option value="578"{if $region_params[1] eq "578"} selected="selected"{/if}>Norwegian Krone</option>
		<option value="608"{if $region_params[1] eq "608"} selected="selected"{/if}>Philippine Peso</option>
		<option value="643"{if $region_params[1] eq "643"} selected="selected"{/if}>Russian Ruble</option>
		<option value="702"{if $region_params[1] eq "702"} selected="selected"{/if}>Singapore Dollar</option>
		<option value="710"{if $region_params[1] eq "710"} selected="selected"{/if}>South Africa Rand</option>
		<option value="752"{if $region_params[1] eq "752"} selected="selected"{/if}>Swedish Krona</option>
		<option value="756"{if $region_params[1] eq "756"} selected="selected"{/if}>Swiss Franc</option>
		<option value="764"{if $region_params[1] eq "764"} selected="selected"{/if}>Thailand Baht</option>
		<option value="826"{if $region_params[1] eq "826"} selected="selected"{/if}>Pound Sterling</option>
		<option value="840"{if $region_params[1] eq "840"} selected="selected"{/if}>US Dollar</option>
		<option value="901"{if $region_params[1] eq "901"} selected="selected"{/if}>New Taiwan Dollar</option>
		<option value="946"{if $region_params[1] eq "946"} selected="selected"{/if}>Leu</option>
		<option value="949"{if $region_params[1] eq "949"} selected="selected"{/if}>Turkish Lira</option>
		<option value="952"{if $region_params[1] eq "952"} selected="selected"{/if}>CFA Franc BCEAO</option>
		<option value="953"{if $region_params[1] eq "953"} selected="selected"{/if}>CFP Franc</option>
		<option value="975"{if $region_params[1] eq "975"} selected="selected"{/if}>Bulgarian Lev</option>
		<option value="978"{if $region_params[1] eq "978"} selected="selected"{/if}>EURO</option>
		<option value="985"{if $region_params[1] eq "985"} selected="selected"{/if}>Zloty</option>
		<option value="986"{if $region_params[1] eq "986"} selected="selected"{/if}>Brazilian Real</option>
	</select>
	<br/><font class="SmallText">{$lng.txt_vads_currency}</font>
</td>
</tr>

<tr>
<td><input type="hidden" name="param06" id="param06" value="" /></td>
</tr>
{assign var=other_params value="#||#"|explode:$module_data.param06}
<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_capture_delay}:</td>
<td><input type="text" id="capture_delay" size="10" value="{$other_params[0]}" />
	<br/><font class="SmallText">{$lng.txt_vads_capture_delay}</font>
</td>
</tr>

<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_valid_mode}:</td>
<td>
	<select id="valid_mode" style="width: 110px;">
		<option value="" {if $other_params[1] eq ""} selected="selected"{/if}>{$lng.txt_vads_valid_default}</option>
		<option value="0" {if $other_params[1] eq "0"} selected="selected"{/if}>{$lng.txt_vads_valid_auto}</option>
		<option value="1" {if $other_params[1] eq "1"} selected="selected"{/if}>{$lng.txt_vads_valid_manual}</option>		
	</select>
	<br/><font class="SmallText">{$lng.txt_vads_valid_mode}</font>
</td>
</tr>

<tr>
<td><input type="hidden" name="param05" id="param05" value="" /></td>
</tr>

{assign var=amount_params value="#||#"|explode:$module_data.param05}

<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_amount_min}:</td>
<td><input type="text" id="amount_min" size="20" value="{$amount_params[0]}" />
	<br/><font class="SmallText">{$lng.txt_vads_amount_min}</font>
</td>
</tr>

<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_amount_max}:</td>
<td><input type="text" id="amount_max" size="20" value="{$amount_params[1]}" />
	<br/><font class="SmallText">{$lng.txt_vads_amount_max}</font>
</td>
</tr>

<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_redir_enable}:</td>
<td>
	<select id="redir_enable" style="width: 110px;">
		<option value="false" {if $other_params[3] eq "false"} selected="selected"{/if}>{$lng.txt_vads_redir_disabled}</option>
		<option value="true" {if $other_params[3] eq "true"} selected="selected"{/if}>{$lng.txt_vads_redir_enabled}</option>		
	</select>
	<br/><font class="SmallText">{$lng.txt_vads_redir_enable}</font>
</td>
</tr>

<tr>
<td><input type="hidden" name="param07" id="param07" value="" /></td>
</tr>
{assign var=redir_success value="#||#"|explode:$module_data.param07}
<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_redir_suc_msg}:</td>
<td><input type="text" id="redir_suc_msg" size="60" value="{$redir_success[1]|replace:'\\\'':'\''}"/>
	<br/><font class="SmallText">{$lng.txt_vads_redir_suc_msg}</font>
</td>
</tr>

<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_redir_suc_to}:</td>
<td><input type="text" id="redir_suc_to" size="10" value="{$redir_success[0]}"/>
	<br/><font class="SmallText">{$lng.txt_vads_redir_suc_to}</font>
</td>
</tr>

<tr>
<td><input type="hidden" name="param08" id="param08" value="" /></td>
</tr>
{assign var=redir_error value="#||#"|explode:$module_data.param08}
<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_redir_err_msg}:</td>
<td><input type="text" id="redir_err_msg" size="60" value="{$redir_error[1]|replace:'\\\'':'\''}"/>
	<br/><font class="SmallText">{$lng.txt_vads_redir_err_msg}</font>
</td>
</tr>

<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_redir_err_to}:</td>
<td><input type="text" id="redir_err_to" size="10" value="{$redir_error[0]}"/>
	<br/><font class="SmallText">{$lng.txt_vads_redir_err_to}</font>
</td>
</tr>

<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_return_mode}:</td>
<td>
	<select id="return_mode" style="width: 110px;">
		<option value="NONE" {if $other_params[2] eq "NONE"} selected="selected"{/if}>NONE</option>
		<option value="GET" {if $other_params[2] eq "GET"} selected="selected"{/if}>GET</option>
		<option value="POST" {if $other_params[2] eq "POST"} selected="selected"{/if}>POST</option>		
	</select>
	<br/><font class="SmallText">{$lng.txt_vads_return_mode}</font>
</td>
</tr>

<tr>
<td style="text-align: right; width: 180px;">{$lng.lbl_vads_return_url}:</td>
<td><input type="text" name="param09" size="60" value="{$module_data.param09|default:$return_url}"/>
	<br/><font class="SmallText">{$lng.txt_vads_return_url}</font>
</td>
</tr>

<tr>
<td style="text-align: right; width: 180px;">{$lng.txt_vads_silent_url}</td>
<td><b>{$return_url}<b/></td>
</tr>

</table>
<br />
<input type="button" value="{$lng.lbl_update|strip_tags:false|escape}" onclick="submit_form();"/>

</form>
{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}