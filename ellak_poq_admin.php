<?php
function ellak_poq_admin_actions()
{
    add_management_page( 'Public opinion questionnaire', 'Public opinion questionnaire', 'manage_options', 'public-opinion-questionnaire', 'ellak_poq_admin_options' );
}
 
add_action('admin_menu', 'ellak_poq_admin_actions');

function ellak_poq_admin_options()
{
    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
    
    require_once('views/ellak_poq_admin_options_view.php');
}