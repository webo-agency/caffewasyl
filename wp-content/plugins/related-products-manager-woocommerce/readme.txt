=== Related Products Manager for WooCommerce ===
Contributors: prowcplugins
Tags: woocommerce, related products, related, products, manager, woo commerce
Requires at least: 4.4
Tested up to: 5.2
Stable tag: 1.4.4
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Manage related products in WooCommerce, beautifully.

== Description ==

**Related Products Manager for WooCommerce** plugin lets you customize related products in WooCommerce.

= Main Features =

With this plugin you can:

* Change related products **total number** and **columns number**.
* Set **order by** (random, date, title, ID, modified, menu order, price) and **order** (ascending, descending) options.
* Relate products by **tag**, **category** or **product attribute**.
* **Hide** related products completely.

= Pro Version =

With [Pro version](https://wpfactory.com/item/related-products-manager-woocommerce/) you can also:

* Relate products **manually for each product** (i.e. select related products and/or product categories or tags from the list) or hide related product for selected products only (i.e. on per product basis).
* Relate products by product attribute **without setting attribute value** (attribute value will be automatically extracted from the current product).
* Change related products **position**.
* Change related products **title**.

= Feedback =

* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* For additional information please visit the [plugin page](https://wpfactory.com/item/related-products-manager-woocommerce/).

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > Related Products Manager".

== Changelog ==

= 1.4.4 - 17/10/2019 =
* Dev - Admin settings descriptions updated.
* Plugin author updated.

= 1.4.3 - 11/09/2019 =
* Dev - Relate - "Extra products limit" option added.
* Dev - Relate - Categories and tags - "Do not override (OR)" option added.
* Dev - Exclude - Now adding WPML translations to the taxonomy term lists.
* Dev - Advanced - "Clear all products transients" options added ("Block size" and "Time limit (seconds)").
* WC tested up to: 3.7.

= 1.4.2 - 15/05/2019 =
* Dev - "Exclude from related" options added.
* Tested up to: 5.2.

= 1.4.1 - 03/05/2019 =
* Dev - Title - `[alg_wc_rpm_product_category]`, `[alg_wc_rpm_product_tag]` and `[alg_wc_rpm_product_taxonomy]` shortcodes added.
* Dev - "WC tested up to" updated.

= 1.4.0 - 05/04/2019 =
* Dev - Relate - "Override categories and tags" option added.
* Dev - Relate - "WPML: Use default product ID" option added.
* Dev - Relate by product attribute - `post__not_in` added to the query args.
* Dev - "Position" section added.
* Dev - "Title" section (and `[alg_wc_rpm_translate]` shortcode) added.

= 1.3.0 - 21/01/2019 =
* Dev - Hide related products - Extra filter added.
* Dev - Relate manually - "Related categories" and "Related tags" options added.
* Dev - Relate manually - "Hide" meta box option added (i.e. hiding per product).
* Dev - Code refactoring.
* Dev - Admin settings descriptions updated.

= 1.2.1 - 25/08/2018 =
* Fix - `version_updated()` function fixed.

= 1.2.0 - 22/08/2018 =
* Dev - Relate by product attribute - On empty "Attribute value" relating by current product's attribute value.
* Dev - Relate manually - "Select box type" option added.
* Dev - Code refactoring.
* Dev - Admin settings restyled.
* Dev - "WC tested up to" added to plugin header.
* Dev - Plugin link updated.

= 1.1.0 - 21/05/2017 =
* Dev - WooCommerce v3.x.x compatibility.
* Dev - Plugin main file header updated.
* Dev - Plugin link updated from `http://coder.fm` to `https://wpcodefactory.com`.

= 1.0.1 - 07/03/2017 =
* Fix - Reset settings - Autoload in `add_option` call fixed.
* Dev - Language (POT) file added.

= 1.0.0 - 23/02/2017 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
