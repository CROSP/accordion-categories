<?php
// Block direct requests
if ( !defined('ABSPATH')) {
    die('-1');
}
// Save extra taxonomy fields callback function.
add_action( 'edited_category', 'save_category_icon', 10, 2 );
add_action( 'create_category', 'save_category_icon', 10, 2 );
 
function save_category_icon( $term_id ) {
    if ( isset( $_POST[POST_KEY_CATEGORY_KEY] ) ) {
        $option_name = sprintf(CATEGORY_ICON_OPTION_PATTERN,$term_id);
        $new_data = $_POST[POST_KEY_CATEGORY_KEY];
        update_option( $option_name, $new_data );
    }
}