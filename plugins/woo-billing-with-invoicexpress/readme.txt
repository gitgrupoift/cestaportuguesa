=== Invoicing with InvoiceXpress for WooCommerce - Free ===
Contributors: webdados
Tags: ecommerce, invoicexpress, woocommerce, invoice, billing, e-commerce, warehouses, invoiceexpress, factura, fatura, invoices, facturas, faturas, invoicing, facturação, faturação, simplified, simplificada, proforma, orçamento, quote, credit note, nota de crédito, guia de transporte, shipping, delivery note, backend, erp, dropshipping
Author URI: https://www.webdados.pt
Plugin URI: https://invoicewoo.com
Requires at least: 4.9
Requires PHP: 5.6
Tested up to: 5.5
Stable tag: 2.6.1

WooCommerce legal invoicing made easy with InvoiceXpress integration.

== Description ==

“Invoicing with InvoiceXpress for WooCommerce - Free” allows you to easily create legal invoices for your WooCommerce orders using InvoiceXpress, directly on your store dashboard, and send them via email to the client.

InvoiceXpress is an online invoicing software approved by the Portuguese Tax and Customs Authority, which can be accessed from anywhere via a browser. Although it was developed with the Portuguese market in mind, it can also be used by store owners from countries worldwide. You can [sign up for a 30-day free trial here](https://www.app.invoicexpress.com/accounts/new?token=webdadoslda-1_bb491d).

= Features: =

* Invoices and Simplified invoices;
* Manual issuing of documents;
* Send documents via email;
* Custom emails by document type;
* VAT field on the checkout;
* Issue VAT exempt documents;
* Very limited technical support (bug fixing only);

= Pro version features: =

In addition to all you can do with the free plugin, the [paid version](https://invoicewoo.com) offers you a number of additional features.

* Technical support;
* Receipts, Invoices-receipt, VAT MOSS invoices, Credit notes, Quotes, Proformas, Delivery notes, and Return delivery notes;
* Automatic issuing of documents, on configurable order status;
* Automatic documents scheduling;
* Leave documents as Draft on InvoiceXpress;
* Documents cancelation;
* List documents on My Account - Orders;
* Send documents using the InvoiceXpress API for better performance;
* Issue documents in Portuguese, English or Spanish;
* Choose default documents invoicing sequence;
* Choose order specific documents invoicing sequence;
* Choose default unit of measurement: Product or Service;
* [WooCommerce EU VAT Assistant by Aelia](https://wordpress.org/plugins/woocommerce-eu-vat-assistant/) integration for EU VAT exemption on B2B;
* [EU VAT Number by WooCommerce](https://woocommerce.com/products/eu-vat-number/) (experimental) integration for EU VAT exemption on B2B;
* Default warehouse (load site) address and licence plate for delivery notes;
* Issuance of guides without values;
* Send Delivery notes directly to the warehouse or the dropshipping supplier;
* WooCommerce order item meta on the InvoiceXpress item description;
* Prevent issuing of incorrect documents if non-standard coupons are used;
* [WPML](https://wpml.org/)/[WooCommerce Multilingual](https://wordpress.org/plugins/woocommerce-multilingual/) integration for multilingual stores;
* [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) compatible;
* Developer license for up to 10 WooCommerce stores;
* [Extensions](https://invoicewoo.com/extensions/)

Check out all the Pro features on the [plugin website](https://invoicewoo.com/features-and-pricing/)

== Installation ==

* Use the included automatic install feature on your WordPress admin panel and search for “Invoicing InvoiceXpress WooCommerce”.
* Go to “WooCoomerce > InvoiceXpress Free” and fill in the subdomain and API key provided by InvoiceXpress on their dashboard (Account > Integrations > API).
* Setup the WooCommerce taxes according to your InvoiceXpress taxes settings - [check the documentation](https://invoicewoo.com/documentation/installation-guide/setting-up-woocommerce-taxes/).

== Frequently Asked Questions ==

= Can you provide free support for this plugin? =

Probably not, unless it’s a very specific issue, under our consideration.
We welcome bug reports and will do anything in our power to fix them for you, but it’s impossible to provide free support on such complex software like an invoicing plugin is.

This is also why our [Pro version](https://invoicewoo.com) has a yearly subscription instead of a one-time fee. The Pro plugin will not stop working if you decide not to renew your subscription, but you’ll stop receiving automatic updates and included technical support. That’s the only way we can ensure continued development and keep up to date with WordPress and WooCommerce changes over time.

= Is this plugin an invoicing software? =

No. This is just an integration between WooCommerce and the InvoiceXpress API. You need to subscribe to the InvoiceXpress service.

= Can I contribute with a translation? =

Sure. Go to [GlotPress](https://translate.wordpress.org/projects/wp-plugins/woo-billing-with-invoicexpress) and help us out.

== Changelog ==

= 2.6.1 =
* Partial Receipts on Invoice-receipts (Pro version)
* Better customer search on InvoiceXpress to avoid duplicates (Pro version)
* Tested with WooCommerce 4.1.0-rc.1 and Wordpress 5.5-alpha-47609

= 2.6.0.1 =
* Bugfix when products included as order items are deleted before a document is issued

= 2.6.0 =
* It’s now possible to issue VAT MOSS Invoices (Pro version - experimental)
* Fix `get_eu_vat_countries` after WooCommerce 4.0.0 (Pro version)
* Tested with WooCommerce 4.1.0-beta.1 and Wordpress 5.5-alpha-47547

= 2.5.3.1 =
* Tested with WooCommerce 4.0.0

= 2.5.3 =
* WooCommerce Subscriptions integration bugfix: VAT code must be copied to renewals (Pro version)
* Unique references for Shipping and Order Fees (can be reverted by returning false to `invoicexpress_woocommerce_shipping_and_fee_ref_unique`)
* New filters to allow developers do add other items to the document based on the last added one: `invoicexpress_woocommerce_items_after_document_item_add`, `invoicexpress_woocommerce_items_after_document_shipping_add`, `invoicexpress_woocommerce_items_after_document_fee_add`

= 2.5.2.1 =
* Fix invoicing when WooCommerce Admin is active (Pro version)

= 2.5.2 =
* WooCommerce Subscriptions compatibility (Pro version)
* Check if the order is valid to avoid issuing documents for special types of orders like Subscriptions, for example (Pro version)

= 2.5.1.1 =
* Change plugin website

= 2.5.1 =
* Bugfix for WordPress versions below 5.0

= 2.5 =
* Move VAT Field options to the "Taxes" tab
* Code refactoring: WooCommerce EU VAT Assistant and EU VAT Number integration (Pro version)
* Prevent automatic invoices for orders with tax exemption (Pro version)
* Check for table and create it if needed on the scheduled documents cron
* Bugfix: Credit notes were being created even if the order didn’t had a Receipt (Pro version)
* Tested with WooCommerce 3.9.0-rc.2

= 2.4.10.2 =
* Spanish translation corrections - thanks [Fernando Tellado](https://tellado.es/)
* Better translators descriptions

= 2.4.10 =
* Get delivery guide AT Code after getting the PDF instead of before (Pro version)
* Show our notices when WooCommerce Admin 0.23.2 (or above) is active
* Spanish translation - thanks [Caribay Camacho](https://caribaycamacho.com/)
* Tested with WooCommerce 3.9.0-beta.2 and Wordpress 5.3.3-alpha-46995

= 2.4.9.1 =
* Small tweaks on the version upgrade routine
* Tested with WooCommerce 3.9.0-beta.1 and Wordpress 5.3.1-alpha-46771

= 2.4.9 =
* Show updates for extensions on the API settings page (Pro version)
* Use Select2 on settings select fields
* Fix multiselect settings field and allow for `optgroup` on settings select fields

= 2.4.8 =
* Fix multiselect settings fields
* Pass manual or automatic mode to the preventDocumentIssuing method thus allowing preventing the issuing of documents based on that
* Add the reason the document issuing was prevented as an order note
* Tested with Wordpress 5.3.1-alpha-46771

= 2.4.7
* Get delivery guide AT Code (Pro version)
* Change ReDownload PDF behavior
* Tested with Wordpress 5.3.0

= 2.4.6 =
* Fix a bug where Receipts would not be sent by email on InvoiceXpress PDF file mode (Pro version)
* Move "Automatic document errors email" to the General settings (Pro version)
* Soft discontinue "Update order status" (Pro version)
* Better email sending debug
* Tested with WooCommerce 3.8.0 and Wordpress 5.2.5-alpha

= 2.4.5 =
* Automatic receipts can now be issued on a different status (Pro version)

= 2.4.4 =
* Automatic documents tweaks and better debug (Pro version)
* Allow developers to silently prevent document issuing
* Code clean-up and reuse

= 2.4.3 =
* New `invoicexpress_woocommerce_before_document_email` action
* When the automatic receipts are active, do them before sending the Invoice email, so that the "Paid" badge of the new templates is shown on the PDF file (Pro version)
* Small Updater fix (Pro version)
* Soft discontinue bulk Invoices
* Check for updates on the API settings screen (Pro version)
* Use version constant instead of getting it from the database each time it’s needed
* Fix setting the receipt id and permalink on the order meta (Pro version)
* Fix PHP notice when sending email using the new hybrid mode (Pro version)
* Fix PHP notice when issuing a receipt (Pro version)
* Tested with WooCommerce 3.8.0-beta.1 and Wordpress 5.2.4-alpha-46074

= 2.4.2 =
* Fix CSS loading on the settings page and improve field sizes
* Small fix when downloading the PDF file from InvoiceXpress
* Feedback when WordPress email sending fails (from WooCommerce 3.8.0 onwards)
* New hybrid Email/PDF mode (Pro version)
* Better explanation of the new Email/PDF methods (Pro version)
* Fix document type names on My Account (Pro version)
* Fix Automatic receipt issuing for Invoices and Simplified invoices (Pro version)

= 2.4.1 =
* Fix delivery guide email recipient (Pro version)
* Fix endpoint and global logo parameter when using the new InvoiceXpress email sending method (Pro version)
* Create an order note when the email is sent with the new InvoiceXpress email sending method (Pro version)
* Add a div id on the My Account documents list so that store owners can style it with CSS (Pro version)

= 2.4.0 =
* Better feedback when the document is issued (and finalized) on InvoiceXpress but the PDF could nout be downloaded (and sent to the client)
* Ability to repeat the download (and sending to the client) process when it fails the first time
* PDF fetching and sending code refactor
* Send documents using InvoiceXpress instead of WordPress (Pro version)
* List documents on My Account - Orders (Pro version)
* Load order.js, cancel.js and credit.js only on the order edit screen
* UX fix on the settings fields
* Load the default refund motive into the Reason for refund field (Pro version)
* Reason for refund on the Credit notes observations (Pro version)
* Warning when not using WP-Admin interface on the same language as the default WPML language, because translatable strings may get mixed up (Pro version)

= 2.3.1 =
* Fix bug where invoicing documents could be repeatedly issued after the order had a credit note
* Show our notices when WooCommerce Admin is active
* Fix bug issuing Quotes and Proformas (Pro version)

= 2.3.0 =
* Performance improvements on the settings pages
* New developer hooks
* Better version upgrade routines
* Better debugging
* Automatic documents scheduling (Pro version)
* Better licensing control (Pro version)

= 2.2.1 =
* Plugin version on scripts and styles
* New `invoicexpress_woocommerce_document_item`, `invoicexpress_woocommerce_document_shipping` and `invoicexpress_woocommerce_document_fee` developer filters to allow manipulation or exclusion of each document line
* Better feedback when the VAT number is missing and is required

= 2.2.0 =
* New option to set the default unit of measurement: Product or Service (Pro version)
* New `invoicexpress_woocommerce_document_item_unit`, `invoicexpress_woocommerce_document_shipping_unit` and `invoicexpress_woocommerce_document_fee_unit` filters to allow developers to change each document item unit of measurement
* WordPress 4.9 minimum requirement
* PHP 5.6 minimum requirement
* Translations update
* Tested with WooCommerce 3.7.0-beta.1

= 2.1.9 =
* Several changes on the licensing so that extensions can use it (Pro version)
* Third parties can now prevent the issuing of documents when using the `invoicexpress_woocommerce_{$type}_data` filters
* Fix on the plugin version update on the database
* Translations update
* Tested with Wordpress 5.2.3-alpha-45552

= 2.1.8 =
* Try to avoid issuing duplicate automatic documents when PayPal has both IPN and PDT notifications active (Pro version)
* Fix PHP notice when creating new orders on wp-admin and automatic invoices or transport guides are activated (Pro version)
* Better tax exemption descriptions
* Better debugging
* Tested with WooCommerce 3.6.4 and Wordpress 5.2.2-RC1

= 2.1.7 =
* Experimental partial VAT exemption
* Prevent automatic invoicing of orders without values, unless explicitly activated on the options (Pro version)
* Better error handling and feedback from the InvoiceXpress API
* Fix VAT exemption error for documents and items with no value
* Initial implementation of generic handling actions so that they can be logged by an external debug plugin
* Composer update and plugin initialization refactoring for better PHP 7.3 compatibility
* Tested with WooCommerce 3.6.3 and Wordpress 5.2.1

= 2.1.6 =
* Bugfix on Bulk Invoices
* Bugfix on VAT on Shipping and Fees
* Tested with WooCommerce 3.6.2

= 2.1.5 =
* Use `wp_remote_get` instead of `fopen`
* Small bugfix on a string
* EU VAT Number by WooCommerce support (Pro version) for EU VAT exemption on B2B
* Tested with WooCommerce 3.6.0 (RC 1)

= 2.1.4.6 =
* Fix product description on some multilingual configurations
* Shipping and fee name now have a prefix
* Tested with WooCommerce 3.6.0 (beta 1) and WordPress 5.1.1

= 2.1.4.5 =
* Shot VAT field on the order admin screen even when "YITH WooCommerce Checkout Manager" is in use
* Do not show tax exemption field on the order admin screen when the order total is zero
* Code cleanup

= 2.1.4.4 =
* Bugfix when editing the VAT number on My Account - Billing Address
* Tested with WooCommerce 3.5.5 and WordPress 5.1

= 2.1.4.3 =
* Code cleanup

= 2.1.4.2 =
* Use `get_order_number()` instead of `get_id()` for documents reference, for compatibility with plugins that manipulate the order number
* New function to set the item description for all the document types
* New `invoicexpress_woocommerce_document_item_title` filter

= 2.1.4.1 =
* InvoiceXpress links fix

= 2.1.4 =
* New method and `invoicexpress_woocommerce_prevent_document_issuing` filter that allows developers to prevent the document issuing based on the order details

= 2.1.3 =
* Small tweaks
* Tested with WooCommerce 3.5.4

= 2.1.2 =
* Bugfix on the default tax exemption motive for Portuguese businesses exempt from taxes

= 2.1.1 =
* New filter on the documents filename: `invoicexpress_woocommerce_document_filename`
* Small adjustment on default documents filename
* Bugfix on Invoices-receipt email (Pro version)
* Tested with WooCommerce 3.5.3 and WordPress 5.0.3

= 2.1.0 =
* Future-proofing by using InvoiceXpress API v2 in JSON
* Document language now based on an option instead of client country (on the Pro version, the Free version always issues documents in Portuguese)
* Caching document sequences for a small boost in performance
* Complete refactoring of ClientChecker class and the way the client is identified on InvoiceXpress
* Code review and elimination of duplicate code on all the controllers
* Fix VAT number validation on the checkout and when issuing documents (both with our field and WooCommerce EU VAT Assistant on the Pro version)
* Translations update
* Tested with WordPress 5.0.2

= 2.0.8 =
* Use WooCommerce CRUD functions only to get and set metadata from and to orders
* Fix the VAT number, document sequence and delivery note fields on the order edit screen
* Bulk invoice tax exemption bugfix
* Fix on the JavaScript enqueues
* Code review
* New filter to disable WooCommerce EU VAT Assistant field status feedback on the checkout (Pro version)
* Fix sending Delivery notes directly to the warehouse or the dropshipping supplier (Pro version)
* Translations update

= 2.0.7.2 =
* Bugfix when upgrading database version could cause a fatal error on some situations

= 2.0.7.1 =
* Version number fix

= 2.0.7 =
* WPML initial support (Pro version)
* Tweak on the document emails’ placeholder replacements
* Store InvoiceXpress document sequence number on order meta and show it on notifications and order notes
* Fix Invoice-receipts and Quotes meta when storing document id and pdf link (Pro version)
* Fix Receipts notifications (Pro version)
* Fix Delivery and Return delivery notes translations (Pro version)
* Translations update

= 2.0.6 =
* Update translations
* Send Delivery notes directly to the warehouse or the dropshipping supplier (Pro version)
* Bugfix on error emails for Automatic invoices (Pro version)

= 2.0.5 =
* Add plugin version to the settings page title
* Propagate the `invoicexpress_woocommerce_after_document_issue` and `invoicexpress_woocommerce_after_document_finish` developer filters to all documents
* Fix some translations

= 2.0.4 =
* Code review
* Associate same order Invoices to Guides and vice versa on InvoiceXpress
* Translations update
* Refactor possible status and not recommended status for automatic documents (Pro version)
* WooCommerce EU VAT Assistant link update (Pro version)
* Fix automatic documents when a custom order status exist (Pro version)
* Fix Transport guide loaded time for automatic documents (Pro version)
* Fix automatic receipts notifications for shop admins (Pro version)

= 2.0.3 =
* Update translations
* Fix automatic documents notifications for shop admins (Pro version)
* Several Transport guides fixes (Pro version)
* Fix translation loading (Pro version)
* Tested with WordPress 5.0.1

= 2.0.2 =
* Fix textdomain so that the Free version uses the WordPress.org GlotPress

= 2.0.1 =
* Fix fatal error on specific situations when updating plugin database version

= 2.0.0 =
* Under new management.
* Changed the plugin name.
* Extensive code overhaul and bug fixing (can contain breaking changes).
* WooCommerce 3.x and PHP 7.x compatibility reviewed.
* Removed WooCommerce backward compatibility (below 3.x).
* Applied WordPress Coding Standards.
* Refactored the settings screen.
* Added email placeholders.
* Refactored email invoice fields.
* Option not to change order status when issuing documents (by default).
* Removed check for WooCommerce deprecated functions.
* Requirements check for `allow_url_fopen` and `curl`.
* Developer hooks (actions and filters).
* And much more...

= 1.2.8 =
* NEW Added document observations field.
* NEW Now able to select customer or company as documents entity.

= 1.2.6 =
* NEW FEATURE added option to use product ID or SKU on document code section.
* Email attachment bug fixed.

= 1.2.5 =
* VAT is now sent via order emails.
* Bug-fixes.

= 1.2.4 =
* Variations have different product description os documents.
* Products with SKU show their SKU instead of ID.
* Bug-fixes and tweaks.

= 1.2.3 =
* Added option to download again created documents.
* Small bug fixes.
* Moved uploads folder to wordpress default.
* Changed error messages.

= 1.2.2 =
* Added compatibility with WooCommerce EU VAT Assistant plugin.

= 1.2.1 =
* Product variations now show up on invoice item description.
* Small bug fixes.

= 1.2 =
* Fixed: it is now possible to create documents with different tax rate items.
* Small bug fixes.

= 1.1 =
* Fixed: it is now possible to create documents without VAT number
* Fixed: VAT format is validated in front-end to avoid errors.
* Bugfix of some features.

= 1.0 =
* First release
