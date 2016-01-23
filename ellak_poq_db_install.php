<?php
/* Install plugin tables */
function ellak_poq_db_install () {
	global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$ellak_poq_db_version = '0.0.5';
    $installed_ver = get_option( "ellak_poq_db_version" );

    if ( $installed_ver != $ellak_poq_db_version ) {
        $charset_collate = $wpdb->get_charset_collate();

        // Questions table
        $questions_table_name = $wpdb->prefix . 'poq_questions';
        $sql = "CREATE TABLE $questions_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            question varchar(1024) DEFAULT '' NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";
        dbDelta($sql);

        // Answers table
        $answers_table_name = $wpdb->prefix . 'poq_answers';
        $sql = "CREATE TABLE $answers_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            question_id mediumint(9) NOT NULL,
            answer varchar(1024) DEFAULT '' NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // User_answers table
        $user_answers_table_name = $wpdb->prefix . 'poq_user_answers';
        $sql = "CREATE TABLE $user_answers_table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            user_id mediumint(9) NOT NULL,
            answer_id mediumint(9) NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";
        dbDelta($sql);

        update_option( "ellak_poq_db_version", $ellak_poq_db_version );
    }
}

/* Register the hook functions */
add_action( 'plugins_loaded', 'ellak_poq_db_install' );