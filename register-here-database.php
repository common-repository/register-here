<?php

###############################
##  Danner database-tabeller hvis de ikke findes i forvejen.
##    Og opdaterer dem hvis der er ændringer


global $register_here_db_version;
$register_here_db_version = "0.3";

global $register_here_db_prefix;
$register_here_db_prefix = "reghere_";


reghere_log_me("DEBUG - register_here_db_version - $register_here_db_version");



function register_here_install () {
	global $wpdb;
	global $register_here_db_version;
	global $register_here_db_prefix;

	$installed_ver = get_option( "register_here_db_version" );
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


	###  GROUP / HOLD  ###

#	if ( $installed_ver != $register_here_db_version ) {
		# reghere_log_me("DEBUG - installed_ver - $installed_ver");
		$table_name = $wpdb->prefix . $register_here_db_prefix . "group"; 

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			dt datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			grp_name tinytext NOT NULL,
			descr text NOT NULL,
			url VARCHAR(512) DEFAULT '' NOT NULL,
			UNIQUE KEY id (id)
		);";

		dbDelta( $sql );

#	}  #  if ( $installed_ver != $register_here_db_version )
	


	###  PARTICIPANT / DELTAGER  ###

		$table_name = $wpdb->prefix . $register_here_db_prefix . "participant"; 

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			dt datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			sessionid varchar(256) NOT NULL, 
			status tinyint DEFAULT 1,
			par_name varchar(512) NOT NULL,
			adr1 varchar(512) NOT NULL,
			adr2 varchar(512) NULL,
			zip varchar(512) NOT NULL,
			town varchar(512) NOT NULL,
			country varchar(512) NOT NULL,
			cell varchar(512) NULL,
			email varchar(512) NULL,
			username varchar(100) NULL,
			password varchar(100) NULL,
			notes text NOT NULL,
			extra_01 text NULL, 
			extra_02 text NULL, 
			extra_03 text NULL, 
			extra_04 text NULL, 
			extra_05 text NULL, 
			extra_06 text NULL, 
			extra_07 text NULL, 
			extra_08 text NULL, 
			extra_09 text NULL, 
			extra_10 text NULL, 
			UNIQUE KEY id (id)
		);";

		dbDelta( $sql );



##  Gemmes i stedet for som Wordpress OPTIONs
#
#		###  PARTICIPANT_FIELDS / DELTAGER EKSTRA FELTER  ###
#
#		$table_name = $wpdb->prefix . $register_here_db_prefix . "participant_fields"; 
#
#		$sql = "CREATE TABLE $table_name (
#			id mediumint(9) NOT NULL AUTO_INCREMENT,
#			dt datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
#			extra_01_active tinyint NOT NULL Default 0, 
#			extra_01_title varchar(200) NULL, 
#			extra_02_active tinyint NOT NULL Default 0, 
#			extra_02_title varchar(200) NULL, 
#			extra_03_active tinyint NOT NULL Default 0, 
#			extra_03_title varchar(200) NULL, 
#			extra_04_active tinyint NOT NULL Default 0, 
#			extra_04_title varchar(200) NULL, 
#			extra_05_active tinyint NOT NULL Default 0, 
#			extra_05_title varchar(200) NULL, 
#			extra_06_active tinyint NOT NULL Default 0, 
#			extra_06_title varchar(200) NULL, 
#			extra_07_active tinyint NOT NULL Default 0, 
#			extra_07_title varchar(200) NULL, 
#			extra_08_active tinyint NOT NULL Default 0, 
#			extra_08_title varchar(200) NULL, 
#			extra_09_active tinyint NOT NULL Default 0, 
#			extra_09_title varchar(200) NULL, 
#			extra_10_active tinyint NOT NULL Default 0, 
#			extra_10_title varchar(200) NULL, 
#			UNIQUE KEY id (id)
#		);";
#
#		dbDelta( $sql );
#



	add_option( "register_here_db_version", $register_here_db_version );

}  #  function register_here_install


function register_here_install_data () {
	global $wpdb;
	global $register_here_db_prefix;
	$table_name = $wpdb->prefix . $register_here_db_prefix . "group"; 

	$sql = "Select * From $table_name Where name = 'Intet hold'";
	$result = mysql_query ( $sql );
	if ($row = mysql_fetch_assoc ($result)) {
		# Do nothing - data is already here
	} else  {	

		## Indsæt default data - et hold som altid kan bruges
		$name = "Intet hold";
		$text = "Holdet her er til dem der endnu ikke har bestemt sig, eller bare gerne vil v&aelig;re udenfor holdene.";
		$rows_affected = $wpdb->insert( $table_name, array( 'dt' => current_time('mysql'), 'name' => $name, 'descr' => $text ) );
	}

}  #  function register_here_install_data


# DEN HER SKAL BRUGES NÅR DER SKAL LAVES VERSION-UPDATE-SCRIPT
# function register_here_update_db_check() {
#     global $register_here_db_version;
#     if (get_site_option( 'register_here_db_version' ) != $register_here_db_version) {
#         register_here_install();
#     }
# }
#add_action( 'plugins_loaded', 'register_here_update_db_check' );

?>
