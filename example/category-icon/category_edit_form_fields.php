<?php
// Block direct requests
if ( !defined('ABSPATH')) {
    die('-1');
}
// Add the field to the Edit Category page
add_action( 'category_edit_form_fields', 'pt_taxonomy_edit_meta_field', 10, 2 );
 
function pt_taxonomy_edit_meta_field($term) {
 
    // put the term ID into a variable
    $term_id = $term->term_id;
    $current_option = sprintf(CATEGORY_ICON_OPTION_PATTERN,$term_id);
    // retrieve the existing value(s) for this meta field. This returns an array
    $category_icon = get_option($current_option);
     ?>
    <tr class="form-field">
    <th scope="row" valign="top"><label for="<?php echo CATEGORY_ICON_FIELD_ID; ?>"><?php _e( 'Category Icon', 'accordion_categories' ); ?></label></th>
        <td>
            <input type="text" name="<?php echo CATEGORY_ICON_FIELD_ID; ?>" id="<?php echo CATEGORY_ICON_FIELD_ID; ?>" value="<?php echo esc_attr( $category_icon ) ? esc_attr( $category_icon ) : ''; ?>">
            <p class="description"><?php printf(__( 'Choose your category icon from . For example: <b>fa fa-wordpress</b>','accordion_categories' )); ?></p>
        </td>
    </tr>
<?php
}