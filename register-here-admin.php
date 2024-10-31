<?php

 //
 // ------------------------------------------------------------------
 //   Deltagere
 // ------------------------------------------------------------------
 //

// Tilføj selvstændigt menupunkt  -  LISTER
add_action('admin_menu', 'register_here_lists_menu');

function register_here_lists_menu() {

    // Add the top-level admin menu
    $page_title = 'Register Here! - ' . __('Lister', 'register-here');
    $menu_title = 'Register Here! - ' . __('Lister', 'register-here');
    $capability = 'manage_options';
    $menu_slug = 'register-here-lists';
    $function = 'register_here_lists_menu';
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function);  #  Tager også et ICON og et PLACERING (default placering nederst) parameter

 
	// Add submenu page with same slug as parent to ensure no duplicates  ############
	$sub_menu_title = __('Tilmeldte', 'register-here');
   if (get_option( 'register_here_text_tilmeldt' ) != "") {
		$sub_menu_title = get_option( 'register_here_text_tilmeldte' );
	}
   $submenu_function = 'register_here_lists';
   add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $submenu_function);    #############   Same SLUG as PARENT menu-function  ######

	// Add the submenu page for Incheck
#   $submenu_page_title = 'Register Here! - ' . __('Indcheck', 'register-here');
#   $submenu_title = __('Indcheck', 'register-here');
#   $submenu_slug = 'register-here-lists-indcheck';
#   $submenu_function = 'register_here_lists_indcheck';
#   add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);

}  //  function register_here_lists_menu


function register_here_lists() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
	echo "<h2>Register Here! - "; _e("Liste over"); echo " " . get_option('register_here_text_tilmeldte'); echo "</h2>";	

	global $wpdb;
	global $register_here_db_prefix;

	$table_name = $wpdb->prefix . $register_here_db_prefix . "participant";

	#########   TODO
	$todo = reghere_rensreq('todo');
	$id = reghere_rensreq('id');

	if ($todo == "delete") {  	$wpdb->update( $table_name, array( 'status' => '0'), array( 'id' => $id) ); }
	if ($todo == "undelete") {	$wpdb->update( $table_name, array( 'status' => '1'), array( 'id' => $id) ); }


	$sql = "SELECT * FROM $table_name ORDER BY par_name";
	$results = $wpdb->get_results($sql, ARRAY_A) or die(mysql_error());

	echo "
<style>
	.t75t {  
		font-size: 75%;
		vertical-align: top;
	}		
	.t {
		vertical-align: top;
	}		
</style>
";
	echo "<table width='100%'>";

	$th = "";
   $th .= "<tr>";
   $th .= "<th>" . __("Navn", 'register-here') . "</th>";
   $th .= "<th>" . __("Adresse", 'register-here') . "</th>";
   $th .= "<th>" . __("Kontakt", 'register-here') . "</th>";
   for ($x=1; $x<=10; $x++) {
      $xx = substr("00" . $x, -2);
		if ( get_option('register_here_extra_fields_' .$xx . '_active', 0) == 1 )  {  
			$th .= "<th>" . get_option('register_here_extra_fields_' . $xx, '') . "</th>"; 
		}
   }
   $th .= "<th>";
   if (get_option( 'register_here_text_tilmeldt' ) != "") {
      $th .= get_option( 'register_here_text_tilmeldt' );
   } else {
      _e("Tilmeldt", 'register-here');
   }
   $th .= " " . __("dato") . "</th>";
	$th .= "<th>" . __("Funktioner", 'register-here') . "</th>";
	$th .= "</tr>";

	echo $th;


	$page = reghere_rensreq('page');

	$showdeleted = get_option( 'register_here_deleted_participants' );  #  VALUES:  noshow, showitalic, showbottom
	$bottomrows = "";

	$vist = 0;
	foreach( $results as $result ) {
		$pre = "";  $pre2 = "";
		if ($showdeleted == "showitalic" and $result['status'] == 0) {  $pre = "<i>";  $pre2 = "</i>";  }

		$t = "";
		$t .= "<tr>";
      $t .= "<td class=t>{$pre}" . $result['par_name'] . "{$pre2}</td>";
      $t .= "<td class=t75t>{$pre}";
      if ($result['adr1']) $t .= $result['adr1'] . "<br>";
      if ($result['adr2']) $t .= $result['adr2'] . "<br>";
      if ($result['zip'] || $result['town']) $t .= $result['zip'] . " " . $result['town'] . "<br>";
      if ($result['country']) $t .= $result['country'] . "<br>";
		$t .= "{$pre2}</td>";
		$t .= "<td class=t75t>{$pre}";
		if ($result['cell']) $t .= $result['cell'] . "<br>";
      if ($result['email']) $t .= $result['email'] . "<br>";
      $t .= "{$pre2}</td>";
      for ($x=1; $x<=10; $x++) {
         $xx = substr("00" . $x, -2);
			if ( get_option('register_here_extra_fields_' . $xx . '_active', 0) == 1 )  {  
				$t .= "<td class=t>{$pre}" . $result['extra_' . $xx] . "{$pre2}</td>"; 
			}
      }
		$t .= "<td class=t75t>{$pre}" . $result['dt'] . "{$pre2}</td>";
#		$t .= "<td class=t75t>{$pre}Status " . $result['status'] . "{$pre2}</td>";
		$t .= "<td class=t75t>{$pre}";
		if ($result['status'] == 1) {
			$t .= "<a href='?page={$page}&todo=delete&id=" . $result['id'] . "'>" . __("Slet", 'register-here') . "</a>";
		} else {
			$t .= "<a href='?page={$page}&todo=undelete&id=" . $result['id'] . "'>" . __("Genskab", 'register-here') . "</a>";
		}
		$t .= "{$pre2}</td>";
      $t .= "</tr>";

		if ($result['status'] == 0) {
			if ($showdeleted == "noshow") {
				##  Vis ikke slettede deltagere - gør intet her
			} else {
				if ($showdeleted == "showbottom") {
					$bottomrows .= $t;
				} else {
					echo $t;
					$vist ++;
				}
			}
		} else { //  if ($result['status'] == 0)
			echo $t;
			$vist ++;
		}  //   ELSE   if ($result['status'] == 0)

		##  Overskrift på hver 20. linie
		if ($vist % 20 == 0 and $vist > 0) {
			echo $th;
		}

	}  //  foreach( $results as $result ) {

	if ($bottomrows != "") {
		echo "<tr><td colspan=99><br><hr><br><h3>" . __("Slettede poster") . "</h3></td></tr>";
		echo $th;
		echo $bottomrows;
	}

	echo $th;
	echo "</table>";

}  //  function register_here_lists


function register_here_lists_indcheck() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
	echo "<h2>Register Here! - "; _e("Indcheck-liste"); echo "</h2>";	
	

}  //  function register_here_lists_indcheck


 //
 // ------------------------------------------------------------------
 //   Indstillinger
 // ------------------------------------------------------------------
 //



// Tilføj selvstændigt menupunkt
add_action('admin_menu', 'register_here_menu_pages');

function register_here_menu_pages() {
    // Add the top-level admin menu
    $page_title = 'Register Here! - ' . __('Indstillinger', 'register-here');
    $menu_title = 'Register Here! - ' . __('Indstillinger', 'register-here');
    $capability = 'manage_options';
    $menu_slug = 'register-here-settings';
    $function = 'register_here_settings';
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function);  #  Tager også et ICON og et PLACERING (default placering nederst) parameter

 
	 // Add submenu page with same slug as parent to ensure no duplicates  ############
    $sub_menu_title = __('Indstillinger', 'register-here');
    $submenu_function = 'register_here_settings';
    add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $submenu_function);    #############   Same SLUG as PARENT menu-function  ######


	// Add the submenu page for Participant Extra Fields
    $submenu_page_title = 'Register Here! - ' . __('Tilmelding ekstra felter', 'register-here');
    $submenu_title = __('Tilmelding felter', 'register-here');
    $submenu_slug = 'register-here-extra-fields';
    $submenu_function = 'register_here_extra_fields';
    add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);

	// Add the submenu page for Titles and words
    $submenu_page_title = 'Register Here! - ' . __('Formular og liste titler (og ord)', 'register-here');
    $submenu_title = __('Titler og ord', 'register-here');
    $submenu_slug = 'register-here-titles-and-words';
    $submenu_function = 'register_here_titles_and_words';
    add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);

#	// Add the submenu page for Payment
#    $submenu_page_title = 'Register Here! - Betaling';
#    $submenu_title = 'Betaling';
#    $submenu_slug = 'register-here-payment';
#    $submenu_function = 'register_here_payment';
#    add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);

	// Add the submenu page for Feedback
    $submenu_page_title = 'Register Here! - ' . __('Tilbagemeldinger', 'register-here');
    $submenu_title = __('Tilbagemeldinger', 'register-here');
    $submenu_slug = 'register-here-feedback';
    $submenu_function = 'register_here_feedback';
    add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);

    // Add the submenu page for Help
    $submenu_page_title = 'Register Here! - ' . __('Hj&aelig;lp', 'register-here');
    $submenu_title = __('Hj&aelig;lp', 'register-here');
    $submenu_slug = 'register-here-help';
    $submenu_function = 'register_here_help';
    add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
}

// INDSTILLINGER undermenu
function register_here_settings() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

	$msg = "";
	if(isset($_POST['submit'])) {

		if ( isset($_REQUEST['register_here_title']) )			{ $register_here_title = $_REQUEST['register_here_title']; }  else  { $register_here_title = ""; }
		if ( isset($_REQUEST['register_here_page_id_form']) )	{ $register_here_page_id_form = $_REQUEST['register_here_page_id_form']; }  else  { $register_here_page_id_form = ""; }
		if ( isset($_REQUEST['register_here_page_id_list']) )	{ $register_here_page_id_list = $_REQUEST['register_here_page_id_list']; }  else  { $register_here_page_id_list = ""; }
		if ( isset($_REQUEST['register_here_email']) )	{ $register_here_email = $_REQUEST['register_here_email']; }  else  { $register_here_email= ""; }
		if ( isset($_REQUEST['register_here_deleted_participants']) )	{ $register_here_deleted_participants = $_REQUEST['register_here_deleted_participants']; }  else  { $register_here_deleted_participants = ""; }

		if ( isset($_REQUEST['register_here_min_age']) )		{ $register_here_min_age = $_REQUEST['register_here_min_age']; }  else  { $register_here_min_age = ""; }
		if ( isset($_REQUEST['register_here_start']) )			{ $register_here_start = $_REQUEST['register_here_start']; }  else  { $register_here_start = ""; }
		if ( isset($_REQUEST['register_here_stop']) )			{ $register_here_stop = $_REQUEST['register_here_stop']; }  else  { $register_here_stop = ""; }
		if ( isset($_REQUEST['register_here_register_start']) )	{ $register_here_register_start = $_REQUEST['register_here_register_start']; }  else  { $register_here_register_start = ""; }
		if ( isset($_REQUEST['register_here_register_stop']) )	{ $register_here_register_stop = $_REQUEST['register_here_register_stop']; }  else  { $register_here_register_stop = ""; }

		// update values
		update_option('register_here_title', $register_here_title);
		update_option('register_here_page_id_form', $register_here_page_id_form);
		update_option('register_here_page_id_list', $register_here_page_id_list);
		update_option('register_here_email', $register_here_email);
		update_option('register_here_deleted_participants', $register_here_deleted_participants);
		update_option('register_here_min_age', $register_here_min_age);
		update_option('register_here_start', $register_here_start);
		update_option('register_here_stop', $register_here_stop);
		update_option('register_here_register_start', $register_here_register_start);
		update_option('register_here_register_stop', $register_here_register_stop);

		$msg = __("Indstillinger opdateret", 'register-here');
	}

    // Render the HTML for the Settings page or include a file that does
	echo "<h2>Register Here! - ";  _e("Indstillinger", 'register-here'); echo "</h2>";

	echo "<form method='post'>";

	echo "<p>";
	if ($msg != "") { echo "<div class='updated fade'>" . $msg . "</div>"; }
	echo "</p>";

	echo "<table class='form-table'>";

	echo "<tr><td width=200 valign=top>";  _e("Titel", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( 'register_here_title' ) );
	echo "<input type='text' name='register_here_title' value='$setting' size=84 maxlength=255 />";
	echo "<br><p class='description'>"; _e("Indtast arrangementets titel, hvis der er anderledes end sidens titel. Ellers anvendes sidens titel.", 'register-here'); echo "</p></td></tr>";

	echo "<tr><td width=200 valign=top>";  _e("Side med tilmelding", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( 'register_here_page_id_form' ) );
	wp_dropdown_pages( array( 'name' => 'register_here_page_id_form', 'selected' => $setting ) );
	echo "<br><p class='description'>"; _e("Bruges i forbindelse med redirects, links, henvisninger og lign.", 'register-here'); echo "</p></td></tr>";

	echo "<tr><td width=200 valign=top>";  _e("Side med liste", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( 'register_here_page_id_list' ) );
	wp_dropdown_pages( array( 'name' => 'register_here_page_id_list', 'selected' => $setting ) );
	echo "<br><p class='description'>"; _e("Bruges i forbindelse med redirects, links, henvisninger og lign.", 'register-here'); echo "</p></td></tr>";

	echo "<tr><td width=200 valign=top>";  _e("Email", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( 'register_here_email' ) );
	echo "<input type='text' name='register_here_email' value='$setting' size=84 maxlength=255 />";
	echo "<br><p class='description'>"; _e("Email der skal sendes til n&aring;r der er tilmeldinger eller fejl.", 'register-here'); echo "</p></td></tr>";

	echo "<tr><td width=200 valign=top>";  _e("Slettede deltagere", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( 'register_here_deleted_participants' ) );
	echo "<input type=radio name='register_here_deleted_participants' id='register_here_deleted_participants_noshow' value='noshow' ";
	if ($setting == "noshow") { echo "CHECKED"; }
	echo ">"; _e("Vis ikke"); echo "&nbsp; ";
	echo "<input type=radio name='register_here_deleted_participants' id='register_here_deleted_participants_showitalic' value='showitalic' ";
	if ($setting == "showitalic") { echo "CHECKED"; }
	echo ">"; _e("Vis i listen i"); echo " <i>"; _e("kursiv skrift"); echo "</i>&nbsp;&nbsp; ";
	echo "<input type=radio name='register_here_deleted_participants' id='register_here_deleted_participants_noshow' value='showbottom' ";
	if ($setting == "showbottom") { echo "CHECKED"; }
	echo ">"; _e("Vis nederst i listen"); echo "&nbsp;&nbsp; ";
	echo "<br><p class='description'>"; _e("Hvordan skal slettede deltagere/tilmeldte vises i listen i admin", 'register-here'); echo "</p></td></tr>";


	#	To be added later....
#	echo "<tr><td>";  _e("Aldersgr&aelig;nse", 'register-here'); echo "</td><td>";
#    $setting = esc_attr( get_option( "register_here_min_age" ) );
#    echo "<input type='text' name='register_here_min_age' value='$setting' size=4 maxlength=2 />";  _e("&aring;r", 'register-here'); echo " <p class='description'>"; _e("Ved arrangementets begyndelse", 'register-here'); echo "</p></td></tr>";
#
#	########   Alle dato_felterne bør laves med en standard DATO_FIELD_et_eller_andet   ########
#	echo "<tr><td>";  _e("Arrangementet foreg&aring;r", 'register-here'); echo "</td><td>";
#    $setting = esc_attr( get_option( 'register_here_start' ) );
#    echo "<p class='description'>";
#	echo "<input type='text' name='register_here_start' value='$setting' size=17 maxlength=20 />  til  ";
#    $setting = esc_attr( get_option( 'register_here_stop' ) );
#    echo "<input type='text' name='register_here_stop' value='$setting' size=17 maxlength=20 /> ";
#	echo "[";  _e("&Aring;&Aring;&Aring;&Aring;MMDD HH:MM", 'register-here'); echo "]</p></td></tr> ";
#
#	echo "<tr><td>";  _e("Tilmelding skal v&aelig;re &aring;ben", 'register-here'); echo "</td><td>";
#    $setting = esc_attr( get_option( 'register_here_register_start' ) );
#    echo "<p class='description'>";
#	echo "<input type='text' name='register_here_register_start' value='$setting' size=17 maxlength=20 />  til  ";
#    $setting = esc_attr( get_option( 'register_here_register_stop' ) );
#    echo "<input type='text' name='register_here_register_stop' value='$setting' size=17 maxlength=20 /> ";
#	echo "[";  _e("&Aring;&Aring;&Aring;&Aring;MMDD HH:MM", 'register-here'); echo "]</p></td></tr> ";
	echo "</table>";

	echo submit_button();
	echo "</form>";
}  #  function register_here_settings


// EKSTRA FELTER undermenu
function register_here_extra_fields() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

	$msg = "";
	if(isset($_POST['submit'])) {

		for ($x=1; $x<=10; $x++) {
			$xx = substr("00" . $x, -2);
			update_option('register_here_extra_fields_' . $xx . '_active', reghere_rensreq('register_here_extra_fields_' . $xx . '_active'));
			update_option('register_here_extra_fields_' . $xx . '', reghere_rensreq('register_here_extra_fields_' . $xx . ''));
		} # for ($x=1; $x<=10; $x++) 

		$msg = __("Ekstra felter opdateret", 'register-here');
	}

    // Render the HTML for the Settings page or include a file that does
	echo "<h2>Register Here! - ";  _e("Tilmelding ekstra felter", 'register-here'); echo "</h2>";
	_e("De ekstra felter tilf&oslash;jes til formularen, og desuden til listerne.", 'register-here');
	echo "<br>";

	echo "<form method='post'>";

	echo "<p>";
	if ($msg != "") { echo "<div class='updated fade'>";  _e($msg); echo "</div>"; }
	echo "</p>";

	echo "<table class='form-table'>";

	for ($x=1; $x<=10; $x++) {
		$xx = substr("00" . $x, -2);
		echo "<tr><td width=200 valign=top>";  _e("Felt nr", 'register-here'); echo " " . $xx . " "; _e("titel", 'register-here'); echo "</td><td>";
		$setting = esc_attr( get_option( "register_here_extra_fields_$xx" ) );
		if ($setting) { $setting_checked = " CHECKED "; }
		echo "<input type='text' name='register_here_extra_fields_$xx' value='$setting' size=50 maxlength=200 /><br>";

		$setting_checked = "";
		$setting = esc_attr( get_option( "register_here_extra_fields_" . $xx . "_active" ) );
		if ($setting == 1) {  $setting_checked = " CHECKED "; }
		echo "<input type='checkbox' name='register_here_extra_fields_" . $xx . "_active' $setting_checked VALUE='1' /> "; _e("Aktivt", 'register-here'); echo "<br>";
		echo "</td></tr>";
	} # for ($x=1; $x<=10; $x++) 

	echo "</table>";

	echo submit_button();
	echo "</form>";
}  #  function register_here_extra_fields


// TITLER OG ORD undermenu
function register_here_titles_and_words() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

	$msg = "";
	if(isset($_POST['submit'])) {

		#		if ( isset($_REQUEST['register_here_listtitle']) )			{ $register_here_listtitle = $_REQUEST['register_here_listtitle']; }  
		#		else  { $register_here_listtitle = ""; }
		if ( isset($_REQUEST['register_here_text_tilmeldte_deltagere_til']) )	{ $register_here_text_tilmeldte_deltagere_til = $_REQUEST['register_here_text_tilmeldte_deltagere_til']; 
		}  else  { $register_here_text_tilmeldte_deltagere_til = ""; }
		if ( isset($_REQUEST['register_here_text_du_kan_selv_tilmelde_dig']) )	{ $register_here_text_du_kan_selv_tilmelde_dig = $_REQUEST['register_here_text_du_kan_selv_tilmelde_dig']; 
		}  else  { $register_here_text_du_kan_selv_tilmelde_dig = ""; }
		if ( isset($_REQUEST['register_here_text_tilmeld']) )	{ $register_here_text_tilmeld = $_REQUEST['register_here_text_tilmeld']; 
		}  else  { $register_here_text_tilmeld = ""; }
		if ( isset($_REQUEST['register_here_text_tilmeldt']) )	{ $register_here_text_tilmeldt = $_REQUEST['register_here_text_tilmeldt']; 
		}  else  { $register_here_text_tilmeldt = ""; }
		if ( isset($_REQUEST['register_here_text_tilmeldte']) )	{ $register_here_text_tilmeldte = $_REQUEST['register_here_text_tilmeldte']; 
		}  else  { $register_here_text_tilmeldte = ""; }
		if ( isset($_REQUEST['register_here_text_du_kan_se_deltagerlisten']) )	{ $register_here_text_du_kan_se_deltagerlisten = $_REQUEST['register_here_text_du_kan_se_deltagerlisten']; 
		}  else  { $register_here_text_du_kan_se_deltagerlisten = ""; }
		if ( isset($_REQUEST['register_here_text_du_er_nu_tilmeldt_til']) )	{ $register_here_text_du_er_nu_tilmeldt_til = $_REQUEST['register_here_text_du_er_nu_tilmeldt_til']; 
		}  else  { $register_here_text_du_er_nu_tilmeldt_til= ""; }

		// update values
#		update_option('register_here_listtitle', $register_here_listtitle);
		update_option('register_here_text_tilmeldte_deltagere_til', $register_here_text_tilmeldte_deltagere_til);
		update_option('register_here_text_du_kan_selv_tilmelde_dig', $register_here_text_du_kan_selv_tilmelde_dig);
		update_option('register_here_text_tilmeld', $register_here_text_tilmeld);
		update_option('register_here_text_tilmeldt', $register_here_text_tilmeldt);
		update_option('register_here_text_tilmeldte', $register_here_text_tilmeldte);
		update_option('register_here_text_du_kan_se_deltagerlisten', $register_here_text_du_kan_se_deltagerlisten);
		update_option('register_here_text_du_er_nu_tilmeldt_til', $register_here_text_du_er_nu_tilmeldt_til);

		$msg = __("Indstillinger opdateret", 'register-here');
	}

    // Render the HTML for the Settings page or include a file that does
	echo "<h2>Register Here! - ";  _e("Titler og ord", 'register-here'); echo "</h2>";

	echo "<form method='post'>";

	echo "<p>";
	if ($msg != "") { echo "<div class='updated fade'>" . $msg . "</div>"; }
	echo "</p>";

	echo "<table class='form-table'>";
	echo "<tr><td colspan=2><p class='description'>"; _e("Du kan tilpasse formularen og listen, s&aring; de kan fremst&aring; som lige pr&aelig;cis den liste du &oslash;nsker.", 'register-here');
	echo "<br>"; _e("Hvis felterne herunder holdes tomme, s&aring; bruges default-teksterne.", 'register-here'); echo "</p></td></tr>";

#	echo "<tr><td width=200 valign=top>";  _e("Listens titel", 'register-here'); echo "</td><td>";
#	$setting = esc_attr( get_option( 'register_here_listtitle' ) );
#	echo "<input type='text' name='register_here_listtitle' value='$setting' size=84 maxlength=255 />";
#	echo "<br><p class='description'>"; _e("Om listen er en tilmelding, medlemsliste eller noget helt tredie. Default: Tilmelding", 'register-here'); echo "</p></td></tr>";

	echo "<tr><td width=200 valign=top>";  _e("'Tilmeldte deltagere til'", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( 'register_here_text_tilmeldte_deltagere_til' ) );
	echo "<input type='text' name='register_here_text_tilmeldte_deltagere_til' value='$setting' size=84 maxlength=255 /></td></tr>";

	echo "<tr><td width=200 valign=top>";  _e("'Du kan selv tilmelde dig'", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( 'register_here_text_du_kan_selv_tilmelde_dig' ) );
	echo "<input type='text' name='register_here_text_du_kan_selv_tilmelde_dig' value='$setting' size=84 maxlength=255 /></td></tr>";

	echo "<tr><td width=200 valign=top>";  _e("'Tilmeld'", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( 'register_here_text_tilmeld' ) );
	echo "<input type='text' name='register_here_text_tilmeld' value='$setting' size=84 maxlength=255 /></td></tr>";

	echo "<tr><td width=200 valign=top>";  _e("'Tilmeldt'", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( 'register_here_text_tilmeldt' ) );
	echo "<input type='text' name='register_here_text_tilmeldt' value='$setting' size=84 maxlength=255 /></td></tr>";

	echo "<tr><td width=200 valign=top>";  _e("'Tilmeldte'", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( 'register_here_text_tilmeldte' ) );
	echo "<input type='text' name='register_here_text_tilmeldte' value='$setting' size=84 maxlength=255 /></td></tr>";

	echo "<tr><td width=200 valign=top>";  _e("'Du kan se deltagerlisten'", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( 'register_here_text_du_kan_se_deltagerlisten' ) );
	echo "<input type='text' name='register_here_text_du_kan_se_deltagerlisten' value='$setting' size=84 maxlength=255 /></td></tr>";

	echo "<tr><td width=200 valign=top>";  _e("'Du er nu tilmeldt til'", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( 'register_here_text_du_er_nu_tilmeldt_til' ) );
	echo "<input type='text' name='register_here_text_du_er_nu_tilmeldt_til' value='$setting' size=84 maxlength=255 /></td></tr>";




        echo "</table>";

        echo submit_button();
        echo "</form>";

}  #   function register_here_payment


// BETALING undermenu
function register_here_payment() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    // Render the HTML for the Help page or include a file that does
	echo "<h2>Register Here! - ";  _e("Betalingsmuligheder", 'register-here'); echo "</h2>";
}  #   function register_here_payment

function register_here_get_version() {
	if ( ! function_exists( 'get_plugins' ) ) {	require_once( ABSPATH . 'wp-admin/includes/plugin.php' ); }
	$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
	$plugin_file = basename( ( __FILE__ ) );
	return $plugin_folder[$plugin_file]['Version'];
}  #   register_here_get_version

function register_here_get_version2() {
$plugin_data = get_plugin_data( __FILE__ );
$plugin_version = $plugin_data['Version'];
return $plugin_version;
}

// TILBAGEMELDING/FEEDBACK undermenu
function register_here_feedback() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

	$msg = "";
	if(isset($_POST['submit'])) {

		if ( isset($_REQUEST['register_here_feedback_install']) )	{ $register_here_feedback_install = $_REQUEST['register_here_feedback_install']; }  else  { $register_here_feedback_install = ""; }
		if ( isset($_REQUEST['register_here_feedback_participants']) )	{ $register_here_feedback_participants = $_REQUEST['register_here_feedback_participants']; }  else  { $register_here_feedback_participants = ""; }

		update_option('register_here_feedback_install', $register_here_feedback_install);
		update_option('register_here_feedback_participants', $register_here_feedback_participants);

		$msg = __("Indstillinger opdateret", 'register-here') . "<br>";


		## Der er sat noget i "Indsamles om installationen"
		if ( isset($_REQUEST['register_here_feedback_install']) )	{
			if ( isset($_REQUEST['register_here_feedback_install_old']) )	{ $register_here_feedback_install_old = $_REQUEST['register_here_feedback_install_old']; }  else  { $register_here_feedback_install_old = "0"; }
			if ($register_here_feedback_install == "1" && $register_here_feedback_install_old == "0") {
				# D.v.s. hvis den er valgt nu, og enten ikke var valgt før, eller valgt fra før  -  Så registrerer vi data

				$vers__ = urlencode(reghere_version());
				$wp_vers__ = urlencode(get_bloginfo('version'));
				$server_id__ = get_option('register_here_feedback_server_id');

				if ( isset($_REQUEST['register_here_feedback_comment']) )	{ $register_here_feedback_comment = $_REQUEST['register_here_feedback_comment']; }  else  { $register_here_feedback_comment = ""; }
				$comment__ = urlencode($register_here_feedback_comment);


				$url = "http://www.pierrehusted.dk/register-here/?todo=register_install&server_id=" . $server_id__ . "&vers=" . $vers__ . "&wp_vers=" . $wp_vers__ . "&comment=" . $comment__;
				#$msg .= "DEBUG - url: $url<br>";

				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				$output = curl_exec($curl);

				$server_id_ = substr($output, strpos($output, 'Install ID') + strlen('Install ID') + 1);
				$server_id = str_replace("'", "", $server_id_);
				update_option('register_here_feedback_server_id', $server_id);

				$msg .= htmlentities($output) . "<br>";
				#$msg .= "DEBUG - server_id_ $server_id_ <br>";

			}  #  if ($register_here_feedback_install == "1" && $register_here_feedback_install_old == "0")
		}  #  if ( isset($_REQUEST['register_here_feedback_install']) )

	}  #  if(isset($_POST['submit']))


	echo "<h2>Register Here! - ";  _e("Tilbagemeldinger", 'register-here'); echo "</h2>";
	_e("For at kunne danne statistik over brugen af", 'register-here'); echo " <i>Register Here!</i> "; _e("vil vi meget gerne indsamle lidt informationer om plug-in'ets installation og brug.", 'register-here');
	echo "<br>";
	#echo "<br>";

	echo "<form method='post'>";

	echo "<p>";
	if ($msg != "") { echo "<div class='updated fade'>" . $msg . "</div>"; }
	echo "</p>";


	echo "<table class='form-table'>";

	echo "<tr><td width=200 valign=top>";  _e("Tillad at der indsamles info om installationen", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( "register_here_feedback_install" ) );
	$setting_checked_1 = "";
	$setting_checked_0 = "";
	$setting = esc_attr( get_option( "register_here_feedback_install" ) );
	if ($setting == 1) {  $setting_checked_1 = " CHECKED "; } else { $setting_checked_0 = " CHECKED "; }
	echo "<input type='radio' name='register_here_feedback_install' $setting_checked_1 VALUE='1' /> "; _e("Tillad indsamling", 'register-here'); echo "<br>";
	echo "<input type='radio' name='register_here_feedback_install' $setting_checked_0 VALUE='0' /> "; _e("Ingen indsamling", 'register-here'); echo "<br>";
	echo "<input type='hidden' name='register_here_feedback_install_old' VALUE='$setting' /> ";
	echo "<p class='description'>"; _e("Der indsamles f&oslash;lgende informationer: Tidspunkt for tilladelse, Serverens IP-nummer, Server ID, Register Here! version, Wordpress version.", 'register-here'); echo "</p></td></tr>";

	echo "<tr><td width=200 valign=top>";  _e("Tillad at der indsamles info om tilmeldingerne", 'register-here'); echo "</td><td>";
	$setting = esc_attr( get_option( "register_here_feedback_participants" ) );
	$setting_checked_1 = "";
	$setting_checked_0 = "";
	$setting = esc_attr( get_option( "register_here_feedback_participants" ) );
	if ($setting == 1) {  $setting_checked_1 = " CHECKED "; } else { $setting_checked_0 = " CHECKED "; }
	echo "<input type='radio' name='register_here_feedback_participants' $setting_checked_1 VALUE='1' /> "; _e("Tillad indsamling", 'register-here'); echo "<br>";
	echo "<input type='radio' name='register_here_feedback_participants' $setting_checked_0 VALUE='0' /> "; _e("Ingen indsamling", 'register-here'); echo "<br>";
	echo "<p class='description'>"; _e("Der indsamles f&oslash;lgende informationer: Tidspunkt for tilmelding, Serverens IP-nummer, Den tilmeldtes IP-nummer, Register Here! version, Wordpress version.", 'register-here'); echo "<br>";
	_e("Kr&aelig;ver at der er tilladt indsamling af info om installationen, da det k&aelig;der sig til Server ID (som kommer derfra).", 'register-here');
	echo "</p></td></tr>";

	echo "<tr><td>"; _e("Kommentarer", 'register-here'); echo "</td><td><input type='text' name='register_here_feedback_comment' value='' size=50 maxlength=200 /><br>";
	echo "<p class='description'>"; _e("Kommentarerne sendes til udvikleren n&aring;r &aelig;ndringerne i disse indstillinger gemmes.", 'register-here'); echo "</p></td></tr>";
	
	echo "</table>";

	echo submit_button();
	echo "</form>";

	$server_id = get_option('register_here_feedback_server_id');
	if ($server_id != "") { echo "<br><p class='description'>Server ID $server_id </p>"; }

}  #   function register_here_feedback


// HJÆLP undermenu
function register_here_help() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

	echo "<h2>Register Here! - ";  _e("Hj&aelig;lp", 'register-here'); echo "</h2>";

	echo "<br>" . __("Du inds&aelig;tter formularen ved at inds&aelig;tte", 'register-here') . " <i>[register_here]</i> " . __("i en side.", 'register-here') . "<br>";
	echo __("Det er desuden en god id&eacute; at v&aelig;lge siden i", 'register-here');
	echo ' <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=register-here-settings">';
	echo __("indstillinger", 'register-here') . "</a>";
	echo __("- da denne indstilling bruges til henvisninger.", 'register-here') . "<br>";

	echo "<br>" . __("Du inds&aelig;tter listen ved at inds&aelig;tte", 'register-here') . " <i>[register_here list]</i> " . __(" i en side.", 'register-here') . "<br>";
	echo __("Som med formularen, er det ogs&aring; her en god id&eacute; at v&aelig;lge siden i", 'register-here');
	echo ' <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=register-here-settings">';
	echo __("indstillinger", 'register-here') . "</a>";
	echo __("- da denne indstilling ogs&aring; bruges til henvisninger.", 'register-here') . "<br>";

	echo "<br><br>";
	echo __("Det er med tiden planen at udvide", 'register-here') . " <i>Register Here!</i> " . __("med nye funktioner.", 'register-here') . "<br>";
	echo __("S&aring; kig j&aelig;vnligt ind efter opdateringer.", 'register-here') . "<br>";


#	echo "DEBUG -  MYPLUGIN_PLUGIN_DIR - " . MYPLUGIN_PLUGIN_DIR . "<br>";
#	echo "DEBUG -  WP_PLUGIN_DIR - " . WP_PLUGIN_DIR . "<br>";
#	echo "DEBUG - admin_url() " . admin_url() . "<br>";
#	echo "DEBUG -  bloginfo('wpurl') " .  get_bloginfo('wpurl') . "<br>";
#	echo "DEBUG -  bloginfo('url') " .  get_bloginfo('url') . "<br>";

}  #  function register_here_help
?>
