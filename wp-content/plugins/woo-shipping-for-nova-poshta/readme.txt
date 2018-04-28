=== Woo Shipping for Nova Poshta ===
Contributors: snezbritskiy
Tags: eCommerce, nova poshta, новая почта, shipping, e-commerce, store, sales, sell, shop, cart, checkout, storefront
Requires at least: 4.1
Tested up to: 4.9.1
Stable tag: 2.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin for administrating Nova Poshta shipping method within WooСommerce Plugin

== Description ==

This plugin allows you to set up shipping your goods with popular Ukrainian logistic company Nova Poshta.
Your customer can set shipping destination - select destination city and Nova Poshta warehouse, and get shipping price to this destination.

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of WooCommerce, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “WooСommerce Nova Poshta Shipping” and click Search Plugins. Once you’ve found it you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading this plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

= Minimum Requirements =

* WordPress 3.8 or greater
* WooCommerce version 2.0.0 or greater
* PHP version 5.4.1 or greater
* MySQL version 5.0 or greater

== Screenshots ==

1. Nova Poshta shipping settings screen
2. The list of Nova Poshta warehouses for selected city
3. Tracking Nova Poshta shipping within admin panel

== Known issues ==

1. Shipping calculator flushes shipping price when you select Nova Poshta. Is reproducing if you have more then two shipping methods available only. After you update shipping price for Nova Poshta, Nova Poshta is not selected, and when you try to select Nova Poshta, the price is flushing
2. Shipping zones are not supporting.

== Plugin contributing ==

Source code for this plugin is stored on [Github](https://github.com/sergeynezbritskiy/woo-shipping-with-nova-poshta). It is an open source project. If you want to help to make plugin better, you can:
1. Create an issue with your suggestion and proposition of how to improve plugin functionality
2. Report a bug
3. If you are a developer you can fork this project and create pull-request with your improvements

== Changelog ==

= 1.0.0 =
* Task - Set up automatic updates of Nova Poshta locations
* Task - User can select Nova Poshta area, city and warehouse from the dropdown lists
* Task - Upgrade shipping calculator to work with Nova Poshta Locations
= 1.1.2 =
* Task - rename plugin according to Wordpress.org conventions
= 1.1.3 =
* Task - refactoring code, fix NovaPoshta::isNP() method
* Bugfix - fix calculating shipping costs for products with empty weight
= 1.1.4 =
* Bugfix - fix translations
* Task - check compatibility with WooCommerce 2.6.4
= 1.1.5 =
* Bugfix - fix problem with updating addresses within My Account
* Task - refactoring of class Checkout, proper ordering methods and properties
= 1.2.0 =
* Task - add backward compatibility up to PHP 5.4.1
= 1.2.1 =
* Bugfix - fix problem with wrong database charset and collation
* Bugfix - fix issue with deactivating hook for applications with wpdb prefix not wp_
* Task - improve usability, add links to settings page, review page
* Task - improve translations
* Task - improve logging
= 2.0.0 =
* Task - forward compatibility with Woocommerce 3.x
= 2.0.1 =
* Bugfix - hotfix, revert DatabaseSync class to previous version because of fatal error
= 2.0.2 =
* Bugfix - handle exception related to expired or wrong API key during synchronisation database
* Task - refactoring in order to increase readability
* Task - avoid removing "Choose an option" from warehouses dropdown on Region change
= 2.0.3 =
* Bugfix - restore missing composer dependencies