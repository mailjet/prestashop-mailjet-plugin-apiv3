# [API v3] Mailjet for PrestaShop

## Description 

Boost your ROI and increase your merchant revenue with the Mailjet v3 all-in-one PrestaShop email plugin! Create, send and analyze your transactional and email marketing campaigns straight from within your PrestaShop merchant account and boost your bottom line.

<b>PLEASE NOTE</b> - this version of Mailjet’s add-on is compatible with only Mailjet v3 users.  If you are a v1 user (any account created before April 2014), please request to get your account migrated via https://www.mailjet.com/support/ticket. 

## Plug-in Key Info

* Plug-in languages: EN FR
* PrestaShop Compatibility: PrestaShop v1.4.0.1 - v1.7.0.0
* Mailjet Compatibility: Mailjet v3
* Support: https://www.mailjet.com/support/ticket
* Requires Mailjet account

## Merchant Benefits

With Mailjet, optimise your  deliverability,  get your emails delivered to the inbox and avoid the spam folder. Install the official Mailjet PrestaShop add-on and get access to:
 
* ROI Stats:  Real-time sales and net revenue figures per marketing newsletter. Find out who opened, clicked, bounced or unsubscribed from your mailings, create graphs, export data and much more
 
* Setup triggered email events based on customer behavior (birthday promo, abandoned cart, survey request, …)
 
* Group and create specific customer segments  to send targeted  content to thus increasing engagement and  open rates
 
* Automatically remove unsubscribers from your contact lists and Newsletter list to keep your deliverability reputation intact
 
* Personalize your mailings with any contact list properties
 
* Create & manage all Mailjet campaigns and contacts directly within PrestaShop

## Features

* Create personalized messages for your client base using our segmentation feature
 
* Compare the sending rates of multiple campaigns to target the best performing newsletters with Mailjet’s campaign comparison tool
 
* Use our drag-and-drop (WYSIWYG)  template builder to create beautiful newsletters -- no coding necessary
 
* 24/7 customer support is available in English, French, German and Spanish

Is your shop using version 1.5? This module is already pre-installed and ready to use! To activate it, go to the “Modules” tab in your back office and click “Install”.

To connect your Mailjet account, sign up or sign into your account and copy/paste your API and Secret Keys over into the add-on. 

## Customer Benefits

Your customers will benefit by receiving personalized and pertinent emails delivered straight into their inbox increasing engagement and repeat buying. 

## Installation:
1. Download the zip.
2. Unzip the archive and rename the folder to "mailjet"
3. Then zip again the folder
4. Upload the zip in your Prestashop installation.
5. Connect your Mailjet Account. 

If you are not yet a Mailjet user, please click [Register](https://app.mailjet.com/signup?p=prestashop-3.0) to create a new account. 
To view the different pricing plans, please click the ‘Pricing’ button.

Once you have a Mailjet account, click ‘Connect’ to enter your Mailjet Main Account API Key & Secret Key information as shown below.  Click the Mailjet account link to view your API Key information [Account API keys](https://www.mailjet.com/account/api_keys)

Copy and paste your credentials and click ‘Save & Login’.

Happy emailing!



## Changelog
= 3.4.11 =
* Fix translations

= 3.4.10 =
* Fix installation plugin issue

= 3.4.9 =
* Fix initial sync for php version 5.3
* Resolve initial masterList creation issue
* Encode callback parameter
* Update callback response message which resolve campaign callback issue

= 3.4.8 =
* Improve segmentation
* Provide a meaningful error message when the user doesn't enter mandatory data
* Fix datepicker locale issue
* Update the link to Mailjet documentation

= 3.4.7 =
* Improve the plugin activation page
* Enable the "Reset" plugin button

= 3.4.6 =
* Update the all sync scenarios
* Improve the initial sync to "Master Prestashop contact list" during plugin activation to send the contacts in bulks
* Update logos
* Update introduction video

= 3.4.5 =
* Fixed MasterList sync issues
* Unsubscribed customers are added to the segment contact list as unsubscribed instead of subscribed
* Optimized 'Update contact list' when a segmentation contains a large number of contacts
* Fixed an issue related to the exclude action in Segmentation
* Fixed an issue with the "Associate in real time" feature

= 3.4.4 =
* A new way to synchronize customers through segmentation
* Optimized the SQL

= 3.4.3 =
* Replace deprecated `autoExecute` DB class method with `execute`

= 3.4.2 =
* Customer synchronization fix - update contact properties on customer profile modification
* Customer synchronization fix - delete Mailjet contact if related Prestashop customer profile is deleted
* Customer synchronization fix - When Prestashop customer email is changed - delete the existing Mailjet contact with that email and create a new one with the updated email address

= 3.4.1 =
* Segmentation fix

= 3.4.0 =
* Added possibility to unsubscribe customer from Prestashop newsletter through the List Cleanup
* Added support of multiple Mailjet email events at one request

= 3.3.3 =
* Fixed errors in "Segmentation" screen
* Fixed bug causing installation fail for MySQL versions after 5.7

= 3.3.2 =
* Modified the error message on configuration page 
* Remove config.xml file as Prestashop.com upload section requires from us in order to allow our module to be uploaded.

= 3.3.1 =
* Added support of PrestaShop up to version 1.7.0.0

= 3.3.0 =
* Improvement of the account settings
* Integration optimisations including minor security fixes, structure enhancements and standard updates

= 3.2.15 =
* Add a sender address for transactional emails

= 3.2.14 =
* Plugin translated in German

= 3.2.13 =
* Fix on "Cateroy name" filter in Prestashop segmentation
* ES translation fixes

= 3.2.12 =
* ES translations

= 3.2.11 =
* Fix related with trigger emails

= 3.2.10 =
* Segmentation fix related with Group association

= 3.2.9 =
* Fixed displaying of special symbols in trigger emails
* Added checks for existing parameters in hookNewOrder()

= 3.2.8 =
* jQuery is included explicitly now in the module
* Added correct hooks, related to customer edition and properly handling of customer subscription/unsubscription to Mailjet's master contact list

= 3.2.7 =
* Mailjet’s segmentation feature now allows multi-store owners to filter their customers by the store they belong to.

= 3.2.6 =
* Added links to User guide and Support at the plugin footer
* Fixed marketing triggers cron script
* On creation of a new customer, the email and properties are now synced properly to Mailjet.

= 3.2.5 =
* Added explanation video to the plugin setup page. 
* Updated Mailjet logo with one with higher quality. 
* Added Spanish and Deutsch (German) translations for plugin configuration/setup page 

= 3.2.4 =
* Added new iFrame param - sp=display - to display sending policy block

= 3.2.3 =
* Localization URL fix on module setup

= 3.2.2 =
* Iframe URL localization fix

= 3.2.1 =
* Simplification of home page text
* Improvements in Segmentation tool 
