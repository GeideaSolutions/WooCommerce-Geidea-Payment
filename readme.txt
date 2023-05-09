=== Geidea Online Payments for WooCommerce ===

Contributors: geideapg123
Version: 2.0.3
Tags: credit card, geidea, Apple Pay, payment, payment for WordPress, payment for woocommerce, payment request, woocommerce
Requires at least: 6.0.2
Tested up to: 6.2
Requires PHP: 7.4
Stable tag: 2.0.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add Geidea plugin to your online store with No Code and start accepting online payments seamlessly with Geidea Payment Gateway.

== Description ==

Get paid online easily and securely. Geidea’s Payment Gateway is the easiest way to accept credit card and Apple Pay payments on your e-commerce website.
Increase your sales and offer a variety of checkout options for your customers by accepting credit card and Apple Pay payments on your website.

= Installation from within WordPress =

1. Visit your WordPress dashboard
2. Visit **Plugins > Add New**.
3. Search for **Geidea Online Payments**.
4. Install and activate the Geidea Online Payments plugin.

= Manual installation =

1. Download the Geidea Online Payments plugin
2. Unzip and upload the entire `geidea-online-payments` folder to the `/wp-content/plugins/` directory.
3. Visit **Plugins**.
4. Activate the Geidea Online Payments plugin.

= After activation =

1. Visit the new **WooCommerce > Settings > Payments** tab.
2. Toggle on the switch next to Geidea Online Payments option
3. Click on the Geidea Online Payments link in the Payments tab
4. Configure the individual options as you would like to use for accepting Payments.

== Frequently Asked Questions ==

= What is the purpose of this plugin? =

The primary purpose of the Geidea Online Payments plugin is to allow accepting payments from your online store using the Geidea Payment Gateway.

= Can I use this plugin on my production site? =

Yes.

= Where can I submit my plugin feedback? =

Feedback is encouraged and much appreciated by Geidea to help you serve better. If you have suggestions or requests for new features, you can [submit them as an issue in the WooCommerce Geidea Payment GitHub repository](https://github.com/GeideaSolutions/WooCommerce-Geidea-Payment/issues/new).

= How can I troubleshoot if I run into any challenges while configuring the plugin? =

If you need help with troubleshooting or have a question about the plugin, you can visit our troubleshooting section in this file below. If the issues persist, please [visit our support link to get in touch with us](https://geidea.net/merchants/en/support/).

= How can I report a security vulnerability with the plugin? =

If you would like to report a security issue, please [send us a mail to] (support@geidea.net) with the title "Security Vulnerability".


= Why use Geidea Online Payments plugin? =

1. Helps you accept all major credit cards and Apple Pay with no coding or extra integration.
2. The plugin is set up with the highest security standards and PCI-DSS compliance with Geidea Payment Gateway.
3. Better fraud detection and prevention using customer authentication (3D secure).
4. Advanced security settings like IP filtering and country restriction.
5. Track your sales, payout and your business using Geidea’s merchant portal.
5. Get paid fast and transfer your earnings to your bank account quickly.

= How will it help my business? =

1. Hassle-free, simple setup with no coding required.
2. Start selling online and accept online payments from your customers directly on your website.
3. Quick, easy and convenient for your customers and increased conversions due to high performant plugin.
4. Helps you accept local and international card payments in a seamless manner
5. Compatible with web and in-app payment experience. Customizable checkout page.
6. Compliant with the highest payment industry certification (PCI-DES Compliant).
7. Our anti-fraud monitoring and detection system secures your transactions and reduces fraud risks with real-time validation.
8. We provide end-to-end encryption and security with a wide variety of advanced tools like IP filtering, country restriction and much more.
9. Helps you access your earnings faster. Your payments transaction appear in your merchant portal immediately for easy tracking of your sales. Geidea automatically deposits your available balance to your bank account as daily payouts.

= Sign me up! =
Start accepting online payments today and benefit from free updates as Geidea launches new features and products.
Register for a Geidea account to set up the Geidea payment gateway plugin



== Upgrade Notice ==
Before running any update make sure to back up your website!

== Changelog ==

= 1.0.0 - 2021-06-01 =

= 1.0.1 - 2021-06-17 =
* Add - Updated installation guide.

= 1.0.2 - 2021-06-21 =
* Fix - Added check for file extension in custom logo field.

= 1.0.3 - 2021-06-22 =
* Updated - Changed module's structure.
* Fix - Escaped output html.

= 1.0.4 - 2021-06-23 =
* Updated - Refactored logo uploading.

= 1.0.5 - 2021-07-02 =
* Add - Added order note and change status for failed payments.

= 1.0.6 - 2021-07-19 =
* Fix - Fixed casting order amount for tokenise payment.
* Fix - Fixed change order waiting status after placing the order.

= 1.0.7 - 2021-08-25 =
* Fix - Fixed bug with output buffer.

= 1.0.8 - 2021-08-31 =
* Enhancement - Enhancement of the logo uploading mechanism.

= 1.0.9 - 2021-11-10 =
* Fix - Removed short tags from files.

= 1.0.10 - 2022-03-08 =
* Fix - Fixed order status update.

= 1.0.11 - 2022-04-18 =
* Enhancement - Added additional information fields to order page.

= 1.0.12 - 2022-06-14 =
* Enhancement - Added localization for "ar".

= 1.0.13 - 2022-07-18 =
* Enhancement - Moved card tokens to woocommerce submenu.
* Enhancement - Add receipt setting.

= 1.1.0 - 2022-08-17 =
* Enhancement - Refund support.

= 1.2.0 - 2022-09-19 =
* Enhancement - Move payment to the checkout page.

= 1.2.1 - 2022-10-19 =
* Enhancement - Add validation on checkout page.

= 1.2.2 - 2022-10-27 =
* Enhancement - Add card icons on checkout page.

= 1.2.3 - 2022-11-10 =
* Fix - Force https urls for Gateway.

= 1.2.4 - 2022-12-20 =
* Enhancement - Divide merchant logo and checkout icon.

= 1.3.0 - 2023-02-22 =
* Enhancement - Add getting merchant settings from the gateway.
* Enhancement - Add sending customer mobile phone to gateway

= 1.3.1 - 2023-03-13 =
* Fix - Fix sending shipping and billing addresses to gateway.

= 1.3.2 - 2023-03-27 =
* Fix - Fixed sending platform attributes to gateway, readme.txt and README.MD.

= 2.0.0 - 2023-04-13 =
* Enhancement - Added additional options for customizing the payment widget
* Enhancement - Implemented the payment widget with enhanced security using session tokens and embedded checkout
* Fix - Fixed saving card as tokens to WooCommerce
* Fix - Fixed payments with tokens
* Fix - Fixed refund and partial refund operations through WooCommerce
* Fix - Restructured code into multiple files based on capabilities
* Fix - Fixed all sniffs highlighted by WordPress code sniffer

= 2.0.1 - 2023-04-30 =
* Refactor the codebase

= 2.0.3 - 2023-05-09 =
* Security fixes
* Admin settings Fixes