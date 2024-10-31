<?php
/**
 * Plugin Name: Register here!
 * Plugin URI: http://wordpress.org/plugins/register-here/
 * Description: Registration for events. 
 * Version: 0.3.0
 * Author: Pierre Husted Sigvardsen
 * Author URI: http://www.pi-hus.dk
 * License: GPLv2 or later
 * 
 * Text Domain: register-here
 * Domain Path: /lang
 */


# Originally developed for registration at danish LARP-events.


require_once('register-here-globals.php');	//  Globale VARIABLER og FUNKTIONER

include_once('register-here-database.php');	//  Database-definitioner
register_activation_hook( __FILE__, 'register_here_install' );    ## Udfør ved aktivering af Plug-In - Må tilsyneladende ikke ligge i include-filen.
register_activation_hook( __FILE__, 'register_here_install_data' ); 



function register_here( $content ) {
	
	if (strpos($content, '[register_here')) {
		$content_new = '';

		$tag_start = strpos($content, '[register_here');
		$tag_stop = strpos($content, ']', $tag_start);
		$full_tag = substr($content, $tag_start, $tag_stop - $tag_start +1);
		#$content_new .= "DEBUG - full_tag  $full_tag <br>";
		#$content_new .= "DEBUG - tag_start  $tag_start <br>";
		#$content_new .= "DEBUG - tag_stop  $tag_stop <br>";


		$register_here_title = get_option('register_here_title');
# 		echo "DEBUG - register_here_title $register_here_title <br>";
		$register_here_min_age = get_option('register_here_min_age');
# 		echo "DEBUG - register_here_min_age $register_here_min_age <br>";
		$register_here_start = get_option('register_here_start');
# 		echo "DEBUG - register_here_start $register_here_start <br>";
		$register_here_stop = get_option('register_here_stop');
# 		echo "DEBUG - register_here_stop $register_here_stop <br>";
		$register_here_register_start = get_option('register_here_register_start');
# 		echo "DEBUG - register_here_register_start $register_here_register_start <br>";
		$register_here_register_stop = get_option('register_here_register_stop');
# 		echo "DEBUG - register_here_register_stop $register_here_register_stop <br>";

# register_here_title
# register_here_min_age
# register_here_start
# register_here_stop
# register_here_register_start
# register_here_register_stop

		if ($full_tag == "[register_here]") {
			$content_new .= register_here_show_registration();
		} else {

			$tag = $full_tag;
			$tag = str_replace('[register_here', '', $tag);
			$tag = trim(str_replace(']', '', $tag));
			
			#echo "DEBUG - tag $tag <br>";
			if ($tag == "list") {
				$content_new .= register_here_show_list();
			}

			if ($tag == "show") {
				$content_new .= register_here_show_details( $shopid, $pageid );
			}


		}
		
		$content = str_replace($full_tag, $content_new, $content);

		return $content;
	}
  // otherwise returns the database content
  return $content;
}

function register_here_show_list() {
	global $wpdb;
	global $register_here_db_prefix;

	$content_new = "";
	$todo = "";  if (isset($_GET{'todo'})) { $todo = $_GET{'todo'}; }

	echo "<h4>";
	if (get_option( 'register_here_text_tilmeldte_deltagere_til' ) != "") {
		echo get_option( 'register_here_text_tilmeldte_deltagere_til' );
	} else {
		_e("Tilmeldte deltagere til", 'register-here');
	}
	echo " " . reghere_title() . ".</h4>";
	if (get_option( 'register_here_text_du_kan_selv_tilmelde_dig' ) != "") {
		echo get_option( 'register_here_text_du_kan_selv_tilmelde_dig' );
	} else {
		_e("Du kan selv tilmelde dig", 'register-here');
	}
	echo " <a href='?page_id=" . esc_attr( get_option( 'register_here_page_id_form' ) ) . "'>" . __("her", 'register-here') . "</a><br><br>";


	$table_name = $wpdb->prefix . $register_here_db_prefix . "participant"; 
	$sql = "SELECT * FROM $table_name WHERE status = 1 ORDER BY par_name";
	#echo "DEBUG - SQL <br>$sql <br>";
	$results = $wpdb->get_results($sql, ARRAY_A) or die(mysql_error());

	echo "<table width='100%'>";
    foreach( $results as $result ) {
		echo "<tr>";
		echo "<td>" . $result['par_name'] . "<br>";
		echo "<p style='font-size:75%'>";		
		if ($result['adr1']) echo $result['adr1'] . "<br>";
		if ($result['adr2']) echo $result['adr2'] . "<br>";
		if ($result['zip'] || $result['town']) echo $result['zip'] . " " . $result['town'] . "<br>";
		if ($result['country']) echo $result['country'] . "<br>";
		if ($result['cell']) echo $result['cell'] . "<br>";
		if ($result['email']) echo $result['email'] . "<br>";
		echo "</p></td>";
		echo "<td>";
		for ($x=1; $x<=10; $x++) {
			$xx = substr("00" . $x, -2);
			if ( get_option('register_here_extra_fields_' .$xx . '_active', 0) == 1 )  {	echo get_option('register_here_extra_fields_' . $xx, '') . ": " . $result['extra_' . $xx] . "<br>"; }
		} # for ($x=1; $x<=10; $x++) 

		echo "<p style='font-size:75%'><br>";
		if (get_option( 'register_here_text_tilmeldt' ) != "") {
			echo get_option( 'register_here_text_tilmeldt' );
		} else {
			 _e("Tilmeldt", 'register-here');
		}
		echo ": " . $result['dt'] . "</p>";

		echo "</td>";
		echo "</tr>";
    }
	echo "</table>";

	if (get_option( 'register_here_text_du_kan_selv_tilmelde_dig' ) != "") {
		echo get_option( 'register_here_text_du_kan_selv_tilmelde_dig' );
	} else {
		 _e("Du kan selv tilmelde dig", 'register-here');
 	}
 	echo " <a href='?page_id=" . esc_attr( get_option( 'register_here_page_id_form' ) ) . "'>" . __("her", 'register-here') . "</a><br><br>";

}  #  function register_here_show_list


function register_here_show_registration( ) {
	$content_new = "";
	$todo = "";  if (isset($_GET{'todo'})) { $todo = $_GET{'todo'}; }

	if ($todo == "") {
		$content_new .= register_here_show_registration_form();
	}
	#echo "DEBUG todo $todo <br>";

	if ($todo == "add") {
		$content_new .= register_here_do_registration();
	}

	return $content_new;
}  #  function register_here_show_registration


function register_here_do_registration() {
	global $wpdb;
	global $register_here_db_prefix;
	
	$content_new = "";


	$table_name = $wpdb->prefix . $register_here_db_prefix . "participant"; 

	$fields = array( 'dt' => current_time('mysql', 1), 'sessionid' => session_id() );

	if ( isset($_REQUEST['par_name']) )		$fields['par_name'] = reghere_rensreq('par_name');
	if ( isset($_REQUEST['adr1']) )			$fields['adr1'] = reghere_rensreq('adr1');
	if ( isset($_REQUEST['adr2']) )			$fields['adr2'] = reghere_rensreq('adr2');
	if ( isset($_REQUEST['zip']) )			$fields['zip'] = reghere_rensreq('zip');
	if ( isset($_REQUEST['town']) )			$fields['town'] = reghere_rensreq('town');
	if ( isset($_REQUEST['country']) )		$fields['country'] = reghere_rensreq('country');
	if ( isset($_REQUEST['cell']) )			$fields['cell'] = reghere_rensreq('cell');
	if ( isset($_REQUEST['email']) )		$fields['email'] = reghere_rensreq('email');
	if ( isset($_REQUEST['username']) )		$fields['username'] = reghere_rensreq('username');
	if ( isset($_REQUEST['password']) )		$fields['password'] = reghere_rensreq('password');
	if ( isset($_REQUEST['notes']) )		$fields['notes'] = reghere_rensreq('notes');	
	
	for ($x=1; $x<=10; $x++) {
		$xx = substr("00" . $x, -2);
		if ( isset($_REQUEST['extra_' . $xx]) )		$fields['extra_' . $xx] = reghere_rensreq('extra_' . $xx);
	} # for ($x=1; $x<=10; $x++) 

	$wpdb->insert( $table_name, $fields );

	$content_new .= __("Mange tak", 'register-here') . " " . $fields['par_name'] . ". <br>";
	if (get_option( 'register_here_text_du_er_nu_tilmeldt_til' ) != "") {
		$content_new .= get_option( 'register_here_text_du_er_nu_tilmeldt_til' );
	} else {
		$content_new .= __("Du er nu tilmeldt til", 'register-here');
	}
	$content_new .= " " . reghere_title() . ".";


	# Find ID paa indsat post i tabel
	$sql = "SELECT id FROM $table_name WHERE sessionid = '" . session_id() . "' ORDER BY id DESC LIMIT 1";
	$table_id = $wpdb->get_var($sql); 
	# echo "DEBUG - table_id $table_id <br>";


	#### Tilbagemelding / Feedback
	if ( get_option( "register_here_feedback_install" ) == 1 && get_option( "register_here_feedback_participants" ) == 1 ) {

		$vers__ = urlencode(reghere_version());
		$wp_vers__ = urlencode(get_bloginfo('version'));
		$server_id__ = get_option('register_here_feedback_server_id');
		if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) )			{ $par_ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }  else  { $par_ip = ""; }
		$par_ip__ = urlencode($par_ip);
		if ( isset($_SERVER['REMOTE_ADDR']) )			{ $par_ip2 = $_SERVER['REMOTE_ADDR']; }  else  { $par_ip2 = ""; }
		$par_ip2__ = urlencode($par_ip2);

		$url = "http://www.pierrehusted.dk/register-here/?todo=register_participant&server_id=" . $server_id__ . "&vers=" . $vers__ . "&wp_vers=" . $wp_vers__ . "&par_ip=" . $par_ip__. "&par_ip2=" . $par_ip2__;
		#$content_new .= "DEBUG - url: $url<br>";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);

		#$content_new .= "DEBUG - output - " . htmlentities($output) . "<br>";
	
	}  #  if ( get_option( "register_here_feedback_install" ) == 1 && get_option( "register_here_feedback_participants" ) == 1 )


	if (get_option( 'register_here_email' ) != "" )  {
		$to = get_option( 'register_here_email' );
		$to = filter_var($to, FILTER_SANITIZE_EMAIL);
		// Validate e-mail address
		if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
			if (get_option( 'register_here_text_tilmeldt' ) != "") {
				$tilmeldt = get_option( 'register_here_text_tilmeldt' );
			} else {
				$tilmeldt = __("tilmeldt", 'register-here');
			}

 			$subject = "Register here! - " . $tilmeldt;
			$message = "<html><head><title>Register here! email</title></head><body>";
 			$message .= "<b>" . get_option( 'register_here_title' ) . " " . __("har en ny") . " " . strtolower($tilmeldt) . "</b><br><br><br>";
     		if ( isset($_REQUEST['par_name']) )             $message .= "<b>Navn</b>: " . reghere_rensreq('par_name') . "<br><br>";
     		if ( isset($_REQUEST['adr1']) )                 $message .= "<b>Adresse linie 1</b>: " . reghere_rensreq('adr1') . "<br><br>";
     		if ( isset($_REQUEST['adr2']) )                 $message .= "<b>Adresse linie 2</b>: " . reghere_rensreq('adr2') . "<br><br>";
     		if ( isset($_REQUEST['zip']) )                  $message .= "<b>Postnr</b>: " . reghere_rensreq('zip') . "<br><br>";
     		if ( isset($_REQUEST['town']) )                 $message .= "<b>Bynavn</b>: " . reghere_rensreq('town') . "<br><br>";
     		if ( isset($_REQUEST['country']) )              $message .= "<b>Land</b>: " . reghere_rensreq('country') . "<br><br>";
     		if ( isset($_REQUEST['cell']) )                 $message .= "<b>Mobil</b>: " . reghere_rensreq('cell') . "<br><br>";
     		if ( isset($_REQUEST['email']) )                $message .= "<b>Email</b>: " . reghere_rensreq('email') . "<br><br>";
     		# if ( isset($_REQUEST['username']) )             $message .= "<b>Brugernavn</b>: " . reghere_rensreq('username') . "<br><br>";
     		# if ( isset($_REQUEST['password']) )             $message .= "<b>Kodeord</b>: " . reghere_rensreq('password') . "<br><br>";
     		if ( isset($_REQUEST['notes']) )                $message .= "<b>Note</b>: " . reghere_rensreq('notes') . "<br><br>";
     		for ($x=1; $x<=10; $x++) {
        		$xx = substr("00" . $x, -2);
        		if ( isset($_REQUEST['extra_' . $xx]) )	$message .=  "<b>" . get_option('register_here_extra_fields_' . $xx) . "</b>: " . reghere_rensreq('extra_' . $xx) . "<br><br>";
     		} # for ($x=1; $x<=10; $x++)
			$message .= "<br><br>Med venlig Hilsen<br><i><a href='https://wordpress.org/plugins/register-here/'>Register here!</a><br>";
 			$message .= "</body></html>";

 			$headers = "MIME-Version: 1.0" . "\r\n";
 			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
 			// More headers
 			# $headers .= 'From: <webmaster@example.com>' . "\r\n";
 			# $headers .= 'Cc: myboss@example.com' . "\r\n";

 			mail($to, $subject, $message, $headers);
 		}
 	}  //  if (get_option( 'register_here_email' ) != "" )  



 	$content_new .= "<br><br>";
	if (get_option( 'register_here_text_du_kan_se_deltagerlisten' ) != "") {
 		$content_new .= get_option( 'register_here_text_du_kan_se_deltagerlisten' );
 	} else {
 		$content_new .= __("Du kan se deltagerlisten", 'register-here');
 	}
	$content_new .= " <a href='?page_id=" . esc_attr( get_option( 'register_here_page_id_list' ) ) . "'>" . __("her", 'register-here') . "</a><br><br>";


	return $content_new;
}  #  function register_here_do_registration


function register_here_show_registration_form() {
	global $wpdb;
	global $register_here_db_prefix;
	
	$content_new = "";

	$register_here_title = get_option('register_here_title');
#	echo "DEBUG - register_here_title $register_here_title <br>";
#	$register_here_min_age = get_option('register_here_min_age');
# 	echo "DEBUG - register_here_min_age $register_here_min_age <br>";
#	$register_here_start = get_option('register_here_start');
# 	echo "DEBUG - register_here_start $register_here_start <br>";
#	$register_here_stop = get_option('register_here_stop');
# 	echo "DEBUG - register_here_stop $register_here_stop <br>";
#	$register_here_register_start = get_option('register_here_register_start');
# 	echo "DEBUG - register_here_register_start $register_here_register_start <br>";
#	$register_here_register_stop = get_option('register_here_register_stop');
# 	echo "DEBUG - register_here_register_stop $register_here_register_stop <br>";

	$par_name = "";
	$adr1 = "";
	$adr2 = "";
	$zip = "";
	$town = "";
	$country = "";
	$cell = "";
	$email = "";
#	$username = "";
#	$password = "";
	$notes = "";

	$content_new .= "" . $register_here_title . "<br>";
	$content_new .= "<form>";
	$content_new .= "<input type='hidden' name='todo' value='add'>";
	$content_new .= "<input type='hidden' name='page_id' value='" . reghere_renstal($_GET{'page_id'}) . "'>";

	$content_new .= "<table style='width:630px;'>";
	$content_new .= "<tr><td style='width:200px;'>" . __("Navn", 'register-here') . "</td><td><input type='text' name='par_name' value='$par_name' size='50' maxlength='500' /></td></tr>";
	$content_new .= "<tr><td>" . __("Adresse", 'register-here') . "</td><td><input type='text' name='adr1' value='$adr1' size='50' maxlength='500' /><br><input type='text' name='adr2' value='$adr2' size='50' maxlength='500' /></td></tr>";
	$content_new .= "<tr><td>" . __("Postnummer", 'register-here') . "</td><td><input type='text' name='zip' value='$zip' size='50' maxlength='500' /></td></tr>";
	$content_new .= "<tr><td>" . __("Bynavn", 'register-here') . "</td><td><input type='text' name='town' value='$town' size='50' maxlength='500' /></td></tr>";
	$content_new .= "<tr><td>" . __("Land", 'register-here') . "</td><td><input type='text' name='country' value='$country' size='50' maxlength='500' /></td></tr>";
	$content_new .= "<tr><td>" . __("Mobiltlf", 'register-here') . "</td><td><input type='text' name='cell' value='$cell' size='50' maxlength='500' /></td></tr>";
	$content_new .= "<tr><td>" . __("Email", 'register-here') . "</td><td><input type='text' name='email' value='$email' size='50' maxlength='500' /></td></tr>";
#	$content_new .= "<tr><td>" . __("Brugernavn", 'register-here') . "</td><td><input type='text' name='username' value='$username' size='50' maxlength='500' /></td></tr>";
#	$content_new .= "<tr><td>" . __("Kodeord", 'register-here') . "</td><td><input type='text' name='password' value='$password' size='50' maxlength='500' /></td></tr>";
	for ($x=1; $x<=10; $x++) {
		$xx = substr("00" . $x, -2);
		if (get_option('register_here_extra_fields_' . $xx . '_active')) {  
			$content_new .= "<tr><td>" . get_option('register_here_extra_fields_' . $xx . '') . "</td><td><input type='text' name='extra_" . $xx . "' value='' size='50' maxlength='500' /></td></tr>";  
		}
	} # for ($x=1; $x<=10; $x++) 

	$content_new .= "<tr><td>" . __("Kommentarer", 'register-here') . "</td><td><textarea name='notes' size='50'>$notes</textarea></td></tr>";

	$content_new .= "</table>";

	$content_new .= "<input type='submit' value='";
	if (get_option( 'register_here_text_tilmeld' ) != "") {
		$content_new .= get_option( 'register_here_text_tilmeld' );
	} else {
		 $content_new .= __("Tilmeld", 'register-here');
	}
	$content_new .= "'>";

	$content_new .= "</form>";

	return $content_new;
}  #  function register_here_show_registration_form



// Now we set that function up to execute when the the_content action is called
add_action( 'the_content', 'register_here' );


add_action( 'plugins_loaded', 'register_here_load_textdomain' );

function register_here_load_textdomain() {
  load_plugin_textdomain( 'register-here', null, basename(dirname(__FILE__)) ."/lang/" );
}



// -------------------------
//    INCLUDE-FILES    
// -------------------------


include_once('register-here-admin.php');

//  Tilføj hurtigt link til INDSTILLINGER fra Plug-in listen
add_filter('plugin_action_links', 'register_here_plugin_action_links', 10, 2);
function register_here_plugin_action_links($links, $file) {  #  SKAL ligge i grundfilen for at den kan se hvornår den skal køres
    static $this_plugin;
    if (!$this_plugin) { $this_plugin = plugin_basename(__FILE__); }

    if ($file == $this_plugin) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=register-here-settings">Indstillinger</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}  #  function register_here_plugin_action_links


?>
