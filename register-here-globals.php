<?php

##############################
##  Denne fil indeholder både GLOBALE VARIABLER og GLOBAL FUNKTIONER



##############################
###  GLOBALE VARIABLER

if (!defined('MYPLUGIN_THEME_DIR'))
    define('MYPLUGIN_THEME_DIR', ABSPATH . 'wp-content/themes/' . get_template());

if (!defined('MYPLUGIN_PLUGIN_NAME'))
    define('MYPLUGIN_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));

if (!defined('MYPLUGIN_PLUGIN_DIR'))
    define('MYPLUGIN_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . MYPLUGIN_PLUGIN_NAME);

if (!defined('MYPLUGIN_PLUGIN_URL'))
    define('MYPLUGIN_PLUGIN_URL', WP_PLUGIN_URL . '/' . MYPLUGIN_PLUGIN_NAME);


###############################



##############################
###  GLOBALE FUNKTIONER

function reghere_rens ($tekst) {  
	##  Rens tekst inden den bruges  -  KRAEVER (vist ikke mere - da mysql_real.... ikke bruges) at SQL-forbindelsen er sat op inden den bruges
	# $tekst = mysql_real_escape_string($tekst);
	$tekst = esc_sql($tekst);
	$tekst = str_replace("--", "", str_replace(";", ",", $tekst));
	return $tekst;
}  #  function reghere_rens

function reghere_renstal ($tekst) {  ##  Rens tal inden de bruges; godtag KUN tal  
	$tekst = reghere_rens($tekst);
	$tekst = preg_replace('/\D/', '', $tekst);
	return $tekst;
}  #  function reghere_renstal

function reghere_rensreq ($req) {  ##  Rens URL-parametre inden de bruges; godtag KUN tal  
	$tekst = "";
	if (isset($_REQUEST[$req])) { $tekst = reghere_rens($_REQUEST[$req]); }
	return $tekst;
}  #  function reghere_rensreq

function reghere_quote ($tekst) {  ##  Rens og quote URL-parametre inden de bruges i SQL
	return "'" . reghere_rens($tekst) . "'";
}  #  function reghere_quote

function reghere_formatpris ($pris) {
	return "DKK " . number_format($pris, 0, ",", ".") . "";
}  #  function reghere_formatpris


function reghere_title() {  ##  Arrangementets titel - enten det fra indstillingerne, eller sidens titel
	$title = get_option('register_here_title');
	if (!$title) { $title = get_bloginfo( 'name', 'display'); }
	return $title;
}  # function reghere_title


function reghere_version() {
	if ( ! function_exists( 'get_plugins' ) ) {	require_once( ABSPATH . 'wp-admin/includes/plugin.php' ); }
	$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
	$plugin_file = "register-here.php";  #  Hardcoder den her, da function kan kaldes fra forskellige filer
	return $plugin_folder[$plugin_file]['Version'];
}  #  function reghere_version
	

function reghere_log_me($message) {
	##  Skriver log-besked i /wp-content/debug.log
	if (WP_DEBUG === true) {
		if (is_array($message) || is_object($message)) {
			error_log(print_r($message, true));
		} else {
			error_log($message);
		}
	}
}  #  function reghere_log_me



?>
