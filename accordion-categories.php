<?php
/*
Plugin Name: Accordion Categories
Version: 1.2
Plugin URI: https://github.com/CROSP/accordion-categories
Description: Accordion Categories is a Wordpress widget that allows you to display categories in the hierachical order
Author: Alexander Molochko
Author URI: http://crosp.net/about
*/

// Block direct requests
if (!defined('ABSPATH')) {
    die('-1');
}

require_once("class-accordion-categories-widget.php");
define('AC_PLUGIN_PATH', realpath(dirname(__FILE__)));
define('AC_PLUGIN_URL', plugins_url('/', __FILE__));
// Registering widget with hook method	
add_action('widgets_init', function () {
    register_widget('Accordion_Categories_Widget');
});	

