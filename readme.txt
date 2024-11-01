=== Tax Exemption for WooCommerce ===
Contributors: ElliotVS, RelyWP, freemius
Tags: tax exemption,vat exemption,tax,vat,woocommerce
Donate link: https://www.relywp.com
Requires at least: 4.7
Tested up to: 6.6.2
Stable Tag: 1.5.1
License: GPLv3 or later.
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Tax Exemption plugin for WooCommerce. Allow customers to declare tax / VAT exemption eligibility, and provide tax exemption details.

== Description ==

Allow customers to declare tax exemption status on WooCommerce checkout, and provide tax / VAT exemption details.

This is useful if you have some customers that are eligible to be exempt from tax on your products, for example for the purpose of disability VAT exemption or charity VAT relief.

## Getting Started ##

Simply install the plugin, and visit the "Tax Exemption" settings page under "WooCommerce" in the WordPress admin.

Configure the plugin settings as required:

- Enable or disable the tax exemption feature.
- Select the tax class to use for the Tax / VAT exemption.
- Select the location on checkout to display the tax exemption fields.
- Enable or disable removal of tax on shipping for exempt customers.
- Enable or disable the tax exemption fields on the "My Account" page.
- Customise the text/labels used throughout the plugin.

Your customers will then be able to select the "I want to claim tax exemption" checkbox on the checkout page, and enter their tax exemption details.

Tax will be removed from their order if this is checked and they have entered their details successfully.

If an order is marked as tax / VAT exempt, this will be shown on the admin order details page, where you can see the tax exempt details.

## PRO Version ##

The PRO version offers several additional features including:

- Custom tax exemption form fields.
- Tax exemption certificate upload field.
- Tax exemption expiration date field.
- Tax exemption fields on registration form.
- Exemption status management for admins.
- Only allow exemption for approved users.
- Hide the tax exemption fields on checkout.
- Only remove tax from specific products.
- Only allow exemption for logged in users.
- Only allow exemption for selected user roles.
- Only allow exemption for selected countries.
- Customize the tax class for each user.
- Integration with "AvaTax".

<a href="https://relywp.com/plugins/tax-exemption-woocommerce/">UPGRADE TO PRO</a>

== Screenshots ==

1. Example of plugin settings.
2. Example of tax exempt users management section.
3. Example of tax exemption form on checkout page.
4. Example of tax exemption settings on "my account" page.
5. Example of PRO features & settings.

== Changelog ==

= Version 1.5.1 - 26th September 2024 =
- Fix: Fixed an issue with the tax exemption fields not showing on the checkout page in some cases.
- Other: Updated to Freemius SDK 2.8.1
- Other: Tested with WordPress 6.6.2

= Version 1.5.0 - 5th September 2024 =
- Improvement: (PRO) Added the following option under the "Exemption Certificates" settings: Require certificate uploads for each individual order, rather than it being saved to the customer/user.
- Fix: Fixed an issue with the name and reason fields being required even when they are disabled.
- Other: Tested with WooCommerce 9.2.3
- Other: Updated to Freemius SDK 2.7.4

= Version 1.4.3 - 1st August 2024 =
- Fix: Fixed an issue with the plugin causing an error when editing pages.

= Version 1.4.2 - 1st August 2024 =
- Tweak: Now automatically adds a "Zero rate" tax rate when visiting the settings page, if one does not already exist. Now shows a warning if this is not set as the tax class for tax exemption.
- Fix: (PRO) Fixed an issue with the current certificate file link not working correctly on the checkout page if one has already been uploaded previously.
- Fix: (PRO) Fixed an issue with the certificate upload field being required on the registration form even if the exemption option is not checked.
- Fix: (PRO) If tax exemption is shown on registration form, now hides the fields, unless the checkbox is checked.
- Other: Tested with WooCommerce 9.1.4
- Other: Tested with WordPress 6.6.1

= Version 1.4.1 - 12th July 2024 =
- Fix: Fixed an error with the name and reason fields being disabled, but if the required option was still enabled, it would show the error message stating the field is required.
- Fix: (PRO) Fixed an issue with the maximum file size for the certificate upload field not working correctly.

= Version 1.4.0 - 9th July 2024 =
- Improvement: Added compatibility with WooCommerce Block Checkout. It will now remove tax from the block checkout. However, the checkbox and fields can not be shown on the block checkout page, but it will now show the "Exemption Message" and link to the my account page.

= Version 1.3.4 - 8th July 2024 =
- Tweak: You can now edit whether the customer has exemption enabled or disabled on the admin "Exempt Customers" page.
- Tweak: Added a languages folder with a .pot file for translations. Also added "Spanish" translation files.
- Tweak: (PRO) Modified the max file size for the certificate upload field to use the WordPress "wp_max_upload_size" value.
- Fix: (PRO) Fixed a PHP error with the AvaTax integration enabled.
- Other: Tested with WordPress 6.5.5
- Other: Tested with WooCommerce 9.0.2
- Other: Updated to Freemius SDK 2.7.3

= Version 1.3.3 - 12th June 2024 =
- Fix: Fixed an issue with the checkout divs / layout formatting for the tax exemption section on the checkout page (in some cases), since the last update.

= Version 1.3.2 - 11th June 2024 =
- Tweak: (PRO) Improved the "Tax Exemption" meta box on the admin order edit page when AvaTax integration is enabled.
- Tweak: Made a few small CSS/styling tweaks.
- Fix: Fixed an issue with the "Hide Fields On Checkout" option.
- Fix: Fixed issue in some cases with the file upload field on the checkout page not working.
- Other: Tested with WordPress 6.5.4
- Other: Tested with WooCommerce 8.9.3
- Other: Updated to Freemius SDK 2.7.2

= Version 1.3.1 - 9th January 2024 =
- Fix: Fixed an issue in the free version in some cases where the name and reason fields were not showing.

= Version 1.3.0 - 9th January 2024 =
- New: Added options to select whether the "Name" and "Reason" fields are shown and required on the tax exemption form.
- Other: Tested with WordPress 6.4.2
- Other: Tested with WooCommerce 8.5.0

= Version 1.2.1 - 1st December 2023 =
- Fix: (PRO) Fixed an issue with the new "AvaTax" integration.
- Tweak: Made a few other small tweaks.

= Version 1.2.0 - 1st December 2023 =
- New: (PRO) Added support for AvaTax. View the "AvaTax" tab on the plugin settings page for more information.
- New: (PRO) Added a new "Selected countries only" option. If enabled, you can limit tax exemption fields to only be available to certain countries (billing address on checkout).
- New: (PRO) Added a new "Tax Class Per Customer" option to PRO settings. If enabled, you can set a custom "Tax Class for Tax Exemption" for specific customers. This will override the global tax class set in the general settings.
- New: (PRO) When "Specific products only" is enabled, added an option to make it so an eligible product must be in cart to show tax exemption checkbox/form on checkout.
- New: Added "Tax Exemption Details" to the admin "New Order" email.
- Tweak: Made a few small changes to the admin page.
- Tweak: Added a loading spinner and message on the "Exempt Customers" tab, when loading the list of customers.
- Other: Tested with WordPress 6.4.1
- Other: Tested with WooCommerce 8.3.0

= Version 1.1.0 - 31st October 2023 =
- New: Added an "Exempt Customers" tab to the admin page, where you can manage, edit and add tax exempt customers.
- New: Added the "Settings on My Account Page" option to the free version.
- New: (PRO) Added an option to enable a "Expiration Date" field on the tax exemption form. If their exemption expires, it will be disabled on their account and if "approved users only" is enabled, they will require re-approval.
- New: (PRO) Added option to enable tax exemption fields on the WooCommerce user registration form.
- New: (PRO) When "Approved users only" is enabled, added option to show a tax exemption message on checkout with link to account page for non-approved users.
- New: (PRO) Added an option to "Hide Fields On Checkout". If enabled, they will only see the the "I want to claim tax exemption" checkbox on checkout. The tax exemption fields will be hidden and they will first need to edit their details on the "My Account" page.
- New: Added an option to show an "Exempt" orders column on the WooCommerce orders page.
- New: Added an "Tax Exemption Description" option to the "Custom Text" settings tab, which allows you to show text above the tax exemption fields.
- Improvement: (PRO) Files uploaded at checkout are now added to a temporary folder, and moved to the main folder when the order is completed.
- Improvement: (PRO) Automatically delete temporary PDF files that are not assigned to a user or order. Checked daily.
- Improvement: (PRO) Automatically delete the previous/old PDF file when a new one is uploaded to a user.
- Improvement: Added a few more possible locations for the "location on checkout" option for the tax exemption checkbox and fields on the checkout page.
- Tweak: Some small changes to the admin page.
- Tweak: Added the default WooCommerce classes to the tax exemption checkbox on the checkout page.
- Tweak: (PRO) When "Selected user roles only" is enabled, it will also hide the "Tax Exemption" tab on the "My Account" page, for users that are not allowed to use tax exemption.
- Fix: Fixed issue with some websites where tax exemption was not removing tax for logged in users that have not previously set their tax exemption details.
- Fix: (PRO) Fixed issue with the file upload field sometimes not working correctly on checkout page.
- Fix: (PRO) Fixed issue with deleting cerfiticate from my account page, if the file does not exist.
- Fix: (PRO) Fixed issue with certificate not being linked to order properly for guest checkout orders.
- Fix: Fixed the "Tax Exemption Title" custom text option not working correctly.
- Fix: Fixed some issues with HPOS compatibility.
- Fix: A few other small fixes.
- Other: Updated to Freemius SDK 2.6.0
- Other: Tested with WordPress 6.4.0

= Version 1.0.4 - 15th October 2023 =
- Tweak: Some small changes to the admin page.
- Other: Tested with WordPress 6.3.2

= Version 1.0.3 - 12th October 2023 =
- Tweak: Fixed issue with the "Enable Tax Exemption" option when disabled.
- Tweak: A few small changes to the admin page.

= Version 1.0.2 - 12th October 2023 =
- Tweak: Added a check to make sure WooCommerce is installed before running the plugin code.

= Version 1.0.1 - 10th October 2023 =
- New: Free version released.
- Tweak: A few small tweaks and fixes.

= Version 1.0.0 - 5th June 2023 =
- New: Initial PRO version release.