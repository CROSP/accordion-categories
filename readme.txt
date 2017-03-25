=== Accordion Categories ===
Contributors: crosp
Author: Alexander Molochko
Donate link: https://crosp.net/
Tags: category, categories, responsive, accordion, drop-down, menu, widget, categories tree, categories hierarchy, category widget, sidebar
Requires at least: 3.2
Tested up to: 4.7.3
Stable tag: 1.2
License: GPLv2 or later

Accordion Categories is a Wordpress widget that allows you to display categories in the hierachical order

== Description ==

Accordion Categories allows you to show categories menu in a sidebar. Every instance of the widget can be configured according to your needs.
This widget helps visitors of your site to navigate through categories easily. Auto overflow style generation is supported.

= Main Features =

The major features of Accordion Categories include:

* Display categories hierarchically.
* Show/hide empty categories (without any post).
* Show/hide a category post count (number of post associated with a category) including all children categories
* Show categories icons (using filter)
* Auto overflow padding, used for creating styles dynamically in order to prevent text overflow

= Contribution =

You can find the plugin source code on github [Accordion Categories](https://github.com/CROSP/accordion-categories)

== Installation ==

1. Upload the entire `accordion-categories` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use `Accordion Categories Menu` widget in any registered sidebar `Appearance->Widgets`

= Note =

If you are interested of using category icons then refer to the FAQ section.

== Frequently Asked Questions ==

= Why doesn't the feature XX work ? =

If you have found a bug, or the plugin doesn't work correctly, please feel free to contact me.
I will do my best to fix an issue.

= How to use category icons ? =

If you need to have an icon associated with a category I'd suggest you to add a custom category field.
I have provided an example of adding a custom category field. You can find it in the `example/category-icon` folder.
Or you can use your own implementation. The plugin provides a specific filter for that purpose.
The name of the filter to hook - `accordion_category_icon`.


== Screenshots ==

1. Widget appearance.
2. Widget configuration.
3. Show empty categories.
4. Autoverflow padding.
5. With category icons.

== Changelog ==

= 0.1.2=
* Release Date - 25 March 2017 *
* Fix the margin style bug caused by the default css file

= 0.1.0 =
* Release Date - 19 March 2017 *
* Initial plugin release