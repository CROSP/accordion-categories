<?php
// Block direct requests
if ( !defined('ABSPATH')) {
	die('-1');
}
// Add the field to the Add New Category page
add_action( 'category_add_form_fields', 'pt_taxonomy_add_new_meta_field', 10, 2 );
 
function pt_taxonomy_add_new_meta_field() {
    // this will add the custom meta field to the add new term page
    ?>
    <div class="form-field">
        <label for="<?php echo CATEGORY_ICON_FIELD_ID; ?>"><?php _e( 'Category Icon', 'accordion_categories' ); ?></label>
        <input type="text" name="<?php echo CATEGORY_ICON_FIELD_ID; ?>" id="<?php echo CATEGORY_ICON_FIELD_ID; ?>" value="">
        <p class="description"><?php printf(__( 'Choose your category icon from. For example: <b>fa fa-wordpress</b>','pt' )); ?></p>
    </div>
<?php
}