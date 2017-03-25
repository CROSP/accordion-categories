<?php
/**
 * User: crosp
 * Date: 3/20/17
 * Time: 12:33 PM
 */

function setup_accordion_plugin_filter()
{
    add_filter(Accordion_Categories_Widget::FILTER_CATEGORY_ICON, 'provide_category_icon', 10, 2);
}

function provide_category_icon($category_icon, $category_id)
{
    return get_option(sprintf(CATEGORY_ICON_OPTION_PATTERN, $category_id));
}
