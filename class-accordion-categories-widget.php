<?php
// Block direct requests
if (!defined('ABSPATH')) {
    die('-1');
}

class Accordion_Categories_Widget extends WP_Widget
{
    /**
     *    Constants
     **/
    // Settings
    const INVALID_CATEGORY_ID = -1;
    const SHOW_POST_COUNT = 'show_post_count';
    const OPTION_DEFAULT_CATEGORY_ICON = 'default_category_icon';
    const SHOW_CATEGORY_ICON = 'show_category_icon';
    const AUTO_GENERATE_CSS = 'auto_generate_css';
    const AUTO_OVERFLOW_PADDING = 'auto_overflow_padding';
    const OPTION_NESTING_TYPE = 'nesting_type';
    const TITLE = 'title';
    const SHOW_EMPTY_CATEGORIES = 'show_empty_categories';
    private static $NESTING_TYPES = ['text-indent', 'padding'];
    // CSS Classes
    const CLASS_WIDGET_MAIN = 'accordion-menu-widget';
    const CLASS_CATEGORY_LIST = 'accordion-list';
    const CLASS_IS_ROOT = 'is-root';
    const CLASS_HAS_CHILDREN = 'has-children';
    const CLASS_IS_ACTIVE = 'is-active';
    const CLASS_ITEM_CONTROLS = 'accordion-list-item__controls';
    const CLASS_ITEM_ICON = 'accordion-list-item__icon';
    const CLASS_ITEM = 'accordion-list-item';
    const CLASS_ITEM_LINK = 'accordion-list-item__link';
    const CLASS_ITEM_POST_COUNT = 'accordion-list-item__post-count';
    const CLASS_ITEM_TOGGLE_ICON = 'accordion-list-item__toggle-icon';
    // Common class for each category will set as id
    const CATEGORY_ID = 'category-id-%d';
    const PATTERN_LEVEL_CLASS = 'accordion-list--level-%d';
    // Patterns to replace
    const PLACEHOLDER_HAS_CHILDREN = "%has_children";

    const FILTER_CATEGORY_ICON = "accordion_category_icon";
    /**
     *    Variables
     **/
    // Indices for sort order
    const INDEX_NESTING_TEXT_INDENT = 0;
    const INDEX_NESTING_PADDING = 1;
    // Settings
    private $term_type = 'category';
    private $title;
    private $nesting_type;
    private $show_post_count = true;
    private $show_empty_categories = true;
    private $show_category_icon = true;
    private $auto_generate_css = true;
    private $auto_overflow_padding = true;
    private $current_category_id = self::INVALID_CATEGORY_ID;
    private $default_category_icon;

    /**
     * Register widget with WordPress.
     */

    function __construct()
    {
        parent::__construct(
            'Accordion_Categories_Widget', // Base ID
            __('Accordion Categories Menu', 'text_domain'), // Name
            array('description' => __('This widget allows to display categories hierarchy in an accordion style', 'text_domain'),) // Args
        );

    }

    /**
     * Init widget (styles, scripts)
     * Enqueue plugin style-file
     */
    public function init_widget($instance)
    {
        $this->parse_instance($instance);
        wp_register_style('accordion-category-style', plugins_url('css/accordion-category-style.css', __FILE__));
        wp_enqueue_style('accordion-category-style');
        // Register the script like this for a plugin:
        wp_enqueue_script('jquery');
        wp_register_script('accordion-category-script', plugins_url('js/accordion-categories.js', __FILE__));
        wp_enqueue_script('accordion-category-script');

        $script_params = [
            self::AUTO_GENERATE_CSS => $this->auto_generate_css,
            self::AUTO_OVERFLOW_PADDING => $this->auto_overflow_padding,
            self::SHOW_CATEGORY_ICON => $this->show_category_icon,
            self::SHOW_EMPTY_CATEGORIES => $this->show_empty_categories,
            self::SHOW_POST_COUNT => $this->show_post_count,
            self::OPTION_NESTING_TYPE => $this->nesting_type,
            self::TITLE => $this->title
        ];
        wp_localize_script('accordion-category-script', 'accordionMenuParams', $script_params);
    }

    public function traverse_term_tree($parent = 0, $current_level = 0, &$prevous_result = '')
    {
        $result = '';
        $args = array(
            'hide_empty' => ($this->show_empty_categories ? '0' : '1'),
            'orderby' => 'name',
            'order' => 'ASC',
            'taxonomy' => $this->term_type,
            'pad_counts' => 1
        );
        $categories = get_categories($args);
        $next = wp_list_filter($categories, array('parent' => $parent));
        if ($next) {
            // Increase the current depth level
            $level_class = sprintf(self::PATTERN_LEVEL_CLASS, $current_level);
            // Create new list
            // 1. Add general list class
            // 2. Add level class
            // 3. If this is the root list add is-root class to make it visible
            $result .= '<ul class="' . self::CLASS_CATEGORY_LIST . ' ' . $level_class . ' ' . ($current_level == 0 ? self::CLASS_IS_ROOT : '') . '">';
            $current_level++;
            foreach ($next as $cat) {
                // Form default output of list item
                // Formatting li (list item)
                // Retrieve the category icon
                $existing_icon = $this->default_category_icon;
                $category_icon = apply_filters(self::FILTER_CATEGORY_ICON, $existing_icon, $cat->term_id);
                // 1. Assign unique id
                $result .=
                    '<li id="' . sprintf(self::CATEGORY_ID, $cat->term_id);
                $result .= '" class="' . self::CLASS_ITEM . ' ';
                // 2. Assign pattern to replace later
                $result .= self::PLACEHOLDER_HAS_CHILDREN
                    . ' ' . ($cat->term_id == $this->current_category_id ? self::CLASS_IS_ACTIVE : '') . '">';
                // 3. Show post count if necessary
                // Controls wrapper
                $result .= '<div class="' . self::CLASS_ITEM_CONTROLS . '">';
                // Collapse expand icon
                $result .= '<span class="' . self::CLASS_ITEM_TOGGLE_ICON . '"></span>';
                // Category icon
                if ($this->show_category_icon) {
                    $result .= '<span class="' . self::CLASS_ITEM_ICON . ' ' .
                        (!empty($category_icon) ? $category_icon : "") . '"></span>';
                }
                // 4. Show link
                $result .= '<a class="' . self::CLASS_ITEM_LINK . '" href="' . get_term_link($cat->slug, $cat->taxonomy);
                $result .= '" title="' . sprintf(__("View all posts in %s"), $cat->name) . '" ' . '>';
                $result .= $cat->name;
                $result .= '</a>';
                if ($this->show_post_count) {
                    $result .= '<div class="' . self::CLASS_ITEM_POST_COUNT . '">' . number_format_i18n($cat->count) . '</div>';
                }
                $result .= '</div>';
                // Set recusive result to empty string as default
                $recursive_result = '';
                // Check if this is not top level category
                if ($cat->term_id !== 0) {
                    // Call function recursively
                    $recursive_result = self::traverse_term_tree($cat->term_id, $current_level, $result);
                }
                // Append recursive result to existing one
                $result .= $recursive_result;
                // !IMPORTANT if while recursive call pattern was not replaced (not last child)
                // We need to replace it by ourselves first case is when this is top level category
                // (recursive result is empty) and second is when recursive result is not empty in this case
                // we found category that has children
                $result = str_replace(self::PLACEHOLDER_HAS_CHILDREN, !empty($recursive_result) ? self::CLASS_HAS_CHILDREN : '', $result);
            }

            // Close tags
            $result .= '</li>';
            $result .= '</ul>';
        } else {
            // Nothing for now
        }
        return $result;
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        if (is_active_widget(false, false, $this->id_base, true)) {
            $this->init_widget($instance);
        }
        if (is_category()) {
            $category = get_category(get_query_var('cat'));
            $this->current_category_id = $category->cat_ID;
        }
        echo $args['before_widget'] . "\n";
        if ($this->title) {
            echo $args['before_title'] . $this->title . $args['after_title'];
        }
        // Put all output inside container wrapper
        echo '<div class="' . self::CLASS_WIDGET_MAIN . '">' . "\n";
        // Output main part categories menu
        echo self::traverse_term_tree();
        // Close container tag
        echo '</div>';
        echo $args['after_widget'] . "\n";
    }

    private function parse_instance($instance)
    {
        $this->title = sanitize_text_field(isset($instance[self::TITLE]) ? $instance[self::TITLE] : '');
        $this->show_post_count = isset($instance[self::SHOW_POST_COUNT]) ? (bool)$instance[self::SHOW_POST_COUNT] : true;
        $this->show_empty_categories = isset($instance[self::SHOW_EMPTY_CATEGORIES]) ? (bool)$instance[self::SHOW_EMPTY_CATEGORIES] : true;
        $this->show_category_icon = isset($instance[self::SHOW_CATEGORY_ICON]) ? (bool)$instance[self::SHOW_CATEGORY_ICON] : true;
        $this->nesting_type = isset($instance[self::OPTION_NESTING_TYPE]) ? $instance[self::OPTION_NESTING_TYPE] : self::$NESTING_TYPES[self::INDEX_NESTING_TEXT_INDENT];
        $this->auto_generate_css = isset($instance[self::AUTO_GENERATE_CSS]) ? (bool)$instance[self::AUTO_GENERATE_CSS] : true;
        $this->auto_overflow_padding = isset($instance[self::AUTO_OVERFLOW_PADDING]) ? (bool)$instance[self::AUTO_OVERFLOW_PADDING] : true;
        $this->default_category_icon = sanitize_text_field(isset($instance[self::OPTION_DEFAULT_CATEGORY_ICON]) ? $instance[self::OPTION_DEFAULT_CATEGORY_ICON] : "");
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {
        $this->parse_instance($instance);
        ?>
        <p><label for="<?php echo $this->get_field_id(self::TITLE); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id(self::TITLE); ?>"
                   name="<?php echo $this->get_field_name(self::TITLE); ?>" type="text"
                   value="<?php echo esc_attr($this->title); ?>"/></p>
        <!-- Font size unit -->
        <p>
            <label for="<?php echo $this->get_field_id(self::OPTION_DEFAULT_CATEGORY_ICON); ?>"><?php _e('Default category icon:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id(self::OPTION_DEFAULT_CATEGORY_ICON); ?>"
                   name="<?php echo $this->get_field_name(self::OPTION_DEFAULT_CATEGORY_ICON); ?>" type="text"
                   value="<?php echo esc_attr($this->default_category_icon); ?>"/>
        </p>
        <!-- Nesting type -->
        <p>
            <label for="<?php echo $this->get_field_id(self::OPTION_NESTING_TYPE); ?>"><?php _e('Indent method :'); ?></label>
            <select id="<?php echo $this->get_field_id(self::OPTION_NESTING_TYPE); ?>"
                    name="<?php echo $this->get_field_name(self::OPTION_NESTING_TYPE); ?>">
                <?php foreach (self::$NESTING_TYPES as $type) : ?>
                    <option value="<?php echo esc_attr($type); ?>"
                        <?php selected($this->nesting_type, $type); ?>>
                        <?php echo esc_html($type); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id(self::SHOW_EMPTY_CATEGORIES); ?>"
               name="<?php echo $this->get_field_name(self::SHOW_EMPTY_CATEGORIES); ?>"<?php checked($this->show_empty_categories); ?> />
        <label for="<?php echo $this->get_field_id(self::SHOW_EMPTY_CATEGORIES); ?>"><?php _e('Display empty categories'); ?></label>
        <br/>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id(self::SHOW_POST_COUNT); ?>"
               name="<?php echo $this->get_field_name(self::SHOW_POST_COUNT); ?>"<?php checked($this->show_post_count); ?> />
        <label for="<?php echo $this->get_field_id(self::SHOW_POST_COUNT); ?>"><?php _e('Show post counts'); ?></label>
        <br/>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id(self::SHOW_CATEGORY_ICON); ?>"
               name="<?php echo $this->get_field_name(self::SHOW_CATEGORY_ICON); ?>"<?php checked($this->show_category_icon); ?> />
        <label for="<?php echo $this->get_field_id(self::SHOW_CATEGORY_ICON); ?>"><?php _e('Show category icon'); ?></label>
        <br/>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id(self::AUTO_GENERATE_CSS); ?>"
               name="<?php echo $this->get_field_name(self::AUTO_GENERATE_CSS); ?>"<?php checked($this->auto_generate_css); ?> />
        <label for="<?php echo $this->get_field_id(self::AUTO_GENERATE_CSS); ?>"><?php _e('Auto generate css'); ?></label>
        <br/>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id(self::AUTO_OVERFLOW_PADDING); ?>"
               name="<?php echo $this->get_field_name(self::AUTO_OVERFLOW_PADDING); ?>"<?php checked($this->auto_overflow_padding); ?> />
        <label for="<?php echo $this->get_field_id(self::AUTO_OVERFLOW_PADDING); ?>"><?php _e('Auto overflow padding'); ?></label>
        <br/>

        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance[self::TITLE] = sanitize_text_field($new_instance[self::TITLE]);
        $instance[self::SHOW_POST_COUNT] = !empty($new_instance[self::SHOW_POST_COUNT]) ? 1 : 0;
        $instance[self::OPTION_NESTING_TYPE] = sanitize_text_field($new_instance[self::OPTION_NESTING_TYPE]);
        $instance[self::SHOW_EMPTY_CATEGORIES] = !empty($new_instance[self::SHOW_EMPTY_CATEGORIES]) ? 1 : 0;
        $instance[self::SHOW_CATEGORY_ICON] = !empty($new_instance[self::SHOW_CATEGORY_ICON]) ? 1 : 0;
        $instance[self::AUTO_GENERATE_CSS] = !empty($new_instance[self::AUTO_GENERATE_CSS]) ? 1 : 0;
        $instance[self::AUTO_OVERFLOW_PADDING] = !empty($new_instance[self::AUTO_OVERFLOW_PADDING]) ? 1 : 0;
        $instance[self::OPTION_DEFAULT_CATEGORY_ICON] = $new_instance[self::OPTION_DEFAULT_CATEGORY_ICON];
        return $instance;
    }
}

?>