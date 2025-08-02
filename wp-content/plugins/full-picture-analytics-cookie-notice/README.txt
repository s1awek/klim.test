=== Analytics & Privacy Toolkit - WP Full Picture ===
Contributors: chrisplaneta, freemius
Donate link: https://wpfullpicture.com/
Tags: Consent mode, Analytics, GDPR, GTM, Google Ads
Requires at least: 5.4.0
Tested up to: 6.8.1
Stable tag: 9.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use Google Analytics, Meta Pixel, GTM and other tracking tools according to privacy laws. Track user actions and WooCommerce events.

== Description ==

WP Full Picture is an all-in-1 privacy and analytics plugin for WordPress and WooCommerce.

With WP Full Picture you can:
- easily install many popular tracking tools, like Google Analytics, Ads, Meta Pixel, GTM and more,
- use them according to privacy laws (with a built-in consent banner and other privacy tools),
- and track user actions, WooCommerce events and more.

## ⚙️ HOW DOES IT WORK - IN 3 STEPS

[youtube https://www.youtube.com/watch?v=_HGafFvF7dQ]

1. Install all the analytics and marketing tools you need with ready-to-use modules,
2. (optionally) Configure tracking WooCommerce events and user actions,
3. Enable the built-in consent banner and (optionally) other privacy features.

All your tracking tools will follow the consent banner's rules and only load when the user agrees to cookies (unless you set it differently) - with no cookie scanning, limits or integration issues.

## 🧩 WHAT IS INCLUDED

WP Full Picture comes with:

1. **16 ready-to use modules** for installing Google Analytics, Google Ads, Google Tag Manager, Meta Pixel, Microsoft Clarity, Microsoft Advertising, Matomo, PostHog, Simple Analytics, LinkedIn Insight Tag, Plausible Analytics, X Ads / Twitter Ads, TikTok Pixel, Pinterest Conversion Tag, Hotjar, Crazy Egg and Inspectlet,
2. **Google Tag Manager integration and Custom Scripts module** for installing other tools,
3. **Pre-configured WooCommerce tracking** - from tracking product impressions to purchases,
4. **Event tracking** - form submissions, click tracking, affiliate link tracking, and more,
5. **Set of privacy tools** - consent banner, iframes manager, GDPR setup helper and more,
6. **View traffic reports in admin area** - you can view reports from Looker Studio, Databox, and other similar platforms,
7. **Free package of 1000 records of consents** that you can save in our cloud database - check our [very affordable plans](https://wpfullpicture.com/pricing#hook_cdb_plans). 

**Attention**. Saving and storing consents is a paid service. As a free user, you can save 1000 proofs for free and purchase one of [very affordable plans](https://wpfullpicture.com/pricing#hook_cdb_plans) when you need more.

## 🛒 WOOCOMMERCE TRACKING

WP Full Picture lets you easily track WooCommerce events in 14 tracking tools (like Google Analytics, Meta Pixel or Hotjar) and Google Tag Manager.

See how you can set up tracking the full customer journey (from product views to purchases) in Google Analytics in 5 minutes.

[youtube https://www.youtube.com/watch?v=WN5y4tUu4hc]

## 🍪 CONSENT BANNER and other privacy tools

WP Full Picture comes with a consent banner and several other privacy tools, which help you comply with privacy regulations.

See how they compare with CookieBot, CookieYes and Complianz.

[youtube https://www.youtube.com/watch?v=IrSMKJDB840]

## ALTERNATIVE TO...

WP Full Picture is an alternative to:
- PixelYourSite, Pixel Manager for WooCommerce and other similar plugins,
- GTM4WP plugin (WP FP includes its own Google Tag Manager integration),
- CookieBot, CookieYes, Complianz and similar solutions

## 😊 WHO IS IT FOR

WP Full Picture is for everyone with minimal knowledge of analytics tools, however, you need to know HTML and CSS to use advanced functions.

We do not recommend WP Full Picture, if you show ads on your website. Our consent banner does not have the IAB certificate for websites with ads.

## 👌 WHY CHOOSE WP FULL PICTURE?

Choose WP Full Picture if you want to:

- manage all your analytics tools and privacy solutions with one plugin,
- follow GDPR, PiPEDA and other privacy laws,
- quickly set up advanced tracking of user actions and WooCommerce events,
- avoid conflicts between analytics tools and consent banners,
- display analytics dashboards from Looker Studio, Databox, or other similar platforms inside WP admin.

## ✍️ PREMIUM SERVICES - RECORDS OF CONSENT

Many consent management solutions keep basic record of consents in the WordPress's database.

However, these consents often cannot prove that the visitor had enough information to make a choice. What is more, consents in the site's database can be easily manipulated.

Unlike them, WP Full Picture's consents are saved in the cloud server in France (where you cannot edit them) and also contain:
- configuration of consent banner at the time of consent
- configuration of analytics tools at the time of consent
- copy of privacy policy page
- and more

[See example](https://wpfullpicture.com/sdc_download/20763/?key=uttr6mmij3ssnrpyeo3myg4ys7i0y1).

## 💎 WP FULL PICTURE PRO

WP Full Picture Pro is a powerful tool for businesses. It offers:

- **CAPI / Server-side tracking** - more accurate tracking in Meta Pixel and Google Analytics (for WooCommerce orders).
- **Status-based order tracking** - tracking purchases when they get specific order status.
- **Advanced Triggers** - this lets you track multi-step actions, like visiting 5 product pages or showing interest in a product.
- **Visitor scoring** - scores visitors based on their actions to see which traffic sources bring the best leads.
- **Metadata tracking** - tracking custom user, post and taxonomy data.
- and more

[See Free vs Pro comparison](https://wpfullpicture.com/free-vs-pro/)

== Frequently Asked Questions ==

= Is there anything I should know before I start using the plugin? =

Yes. If you use a caching plugin or a tool like Autoptimize, make sure to turn off the settings that combine and minifiy JavaScript files. These can cause issues with the plugin.

= Is WP Full Picture incompatible with any plugins or themes? =

Yes. So far we have noticed that the plugin has problems with:

- PixelYourSite plugin - to avoid issues, please go to General Settings page > Performance section > and enable option "Save main JS functions in a file"
- OceanWP theme - Consent Banner settings do not show in the Theme Customizer
- Kubio theme - the theme uses CSS styles that makes WP FP's Consent Banner show in the center of the page instead of the screen. To fix it, please add to your website this custom CSS `body#kubio{transform: none !important;}`
- Vertice theme - the same problem and solution as above

= Is WP Full Picture compatible with page builders? =

Yes. We tested it with Elementor, Bricks, Breakdance and saw no issues.

= Does WP Full Picture support Google's consent mode v2? =

Yes. Consent mode v2 is fully supported. It works with Google Analytics, Google Ads and GTM. You can enable it in the settings of the cookie notice / consent banner.

= Does WP Full Picture display statistics in the dashboard? =

Yes. WP Full Picture lets you display in your WP admin panel reports and dashboards created in Google Looker Studio, Databox and other similar services. 

These platforms allow you to create advanced reports with aggregated data from various analytics and marketing tools, Google spreadsheets and even WooCommerce data.

= I installed an analytics tool using a different plugin. Do I have to remvoe it and install again using WP Full Picture? =

No. Use Tracking Tools Manager module to take control of tracking scripts loaded by other plugins.

= Do I have to use the WP Full Picture's cookie notice? =

No you don't. All the modules can be used separately.

= Can I use a different consent management plugin or platform (CookieBot, Iubenda, CookieYes, etc.) with WP Full Picture? =

No. WP Full Picture's modules for installing analytics tools are optimized to work with WP Full Picture's consent banner.

= Can I use WP Full Picture on a website that displays ads? =

yes, but with limitations. To show personalised ads from Google Adsense and other similar advertising platforms, you need to use a consent banner with IAB FTC certificate. WP Full Picture does not have it at the moment.

= How does WP Full Picture's cookie notice block cookies?

WP Full Picture doesn't need to block cookies. Instead, it blocks scripts that install and use these cookies.

The end result is the same, but the website works a little bit faster because it loads the tracking scripts only after the visitor agrees to cookies.

= Is WP Full Picture's cookie notice limited in any way? =

No. It can be displayed unlimited number of times, on unlimited pages by unlimited number of visitors.

= I live in the EU, but I want to start tracking visitors right after they visit the page. Can I do this? =

Technically, you can. Legally you can't.

= Will my site comply with ALL privacy regulations just by using WP Full Picture? =

No. Privacy regulations cover many areas of business. WP Full Picture helps you handle only a part of it, so you still need to be aware and act accordingly to be fully compliant with the rest of them.

= Can I translate texts in the cookie notice? =

Yes. WP Full Picture has been tested and works with multilingual plugins WPML and Polylang. It is possible that it also works with other plugins but we haven't tested them.

= Does WP Full Picture generate product feeds for Google Shopping or Facebook? =

No. WP Full Picture is focused on tracking and privacy. To generate a product catalogue you can use one of many plugins from WordPress repository or cloud platforms.

== Screenshots ==

1. Install tracking and marketing tools
2. Track WooCommerce events
3. Track user actions
4. Comply with privacy laws
5. Check GDPR complaince status
6. View traffic and marketing reports in the admin panel

== Changelog ==

= 9.0.0 (02-07-2025) =

* [New] User interface overhaul, including major code refactoring, new modules, changed settings and improved texts
* [New] Setup mode
* [New] Records of consent can be now stored in email account (PRO only)
* [New] Backup restoration functions are built from scratch
* [New] You can now add meta tags in the head section of HTML
* [New] MS UET Consent Mode
* [New] Added Custom events tracking to the second module of Google Analytics
* [Update] Google Advanced Consent Mode is now enabled by default
* [Update] Google Tag now loads for all visits (tracking managed by advanced consent mode)
* [Update] [Custom Scripts] HTML comments are now automatically removed from the pasted code
* [Update] Backups are now sorted by date
* [Update] fp_info shortcode now includes information on iframes and automatically managed, 3rd-party tracking tools
* [Update] fp_info shortcode now checks for duplicates
* [Update] [Rest/ AJAX calls] Changed function for getting visitor's IP address
* [Fix] [Blocking scripts] Rewritten method of blocking scripts with specific content
* [Fix] Added a fallback widget list name for WooCommerce
* [Removed] Removed Pixel Caffeine from supported plugins in the Tracking Tools Manager
* [Removed] Privacy mode in Hotjar has been removed and replaced with data supression option
* [Removed] [GA, GAds, MS Ads] Removed setting to track without waiting for consent as they did not work with consent modes
* [Removed] Enabling debug mode no longer displays WP options below the "Save settings" button
* [Other] Visitors are now always asked for consent after new tracking tools are enabled or priv. policy text changes
* [Other] [Woo] Move "blocking sourcebuster.js" to WooCommerce settings
* [Other] Added a "feedback" button under all popup texts

= 8.5.3.4 (21-05-2025) [Pro-only update] =

* [Fix] [Woo] Added a check for the "billing_address_2" field in checkout
* [Info] Version numbers from 8.5.3.1 to 8.5.3.3 were made available only for Free users in WP repository. They are identical to 8.5.3. They were published because of an error in WP org's plugin repository.

= 8.5.3.4 (21-05-2025) [Pro-only update] =

* [Fix] [Woo] Added a check for the "billing_address_2" field in checkout
* [Info] Version numbers from 8.5.3.1 to 8.5.3.3 were made available only for Free users in WP repository. They are identical to 8.5.3. They were published because of an error in WP org's plugin repository.

= 8.5.3 (19-05-2025) =

* [New] [Privacy] Setting default consents can be turned off by setting "fp.vars.use_other_cmp" to true
* [Update] [Google Ads] Added "currency" to "purchase" events (for dynamic remarketing)
* [Update] [MS Ads] Added revenue values to some ecommerce events
* [Update] Freemius SDK
* [Update] [Woo] Set a default "woo custom widget" for "list_name"
* [Fix] [GTM] The event "fp_privacyPreferencesChanged" is now pushed to the DL after the consents are updated

= 8.5.2 (31-03-2025) =

* [Fix] [Meta Pixel] By mistake, the field for adding test event code was available only for Pro users
* [Fix] [Woo] There is no longer a PHP notice when there is no "billing address 2"
* [Update] Updated texts in the GDPR setup helper

= 8.5.1 (24-03-2025) =

* [Update] [Meta Pixel] If the _fbp cookie is missing, it is now generated by WP FP to improve match quality
* [Update] [GDPR] Updated texts for the GDPR setup helper and Records of Consents
* [Fix] [Meta Pixel] The settings field for the test event code did not show up until CAPI key was entered
* [Fix] For some reason Select2 fields stopped showing placeholders

= 8.5.0 (18-03-2025) =

* [New] [Woo] Added an option to send "product view" events when visitors change product variants
* [New] [Woo] Added an option to send an extra "product view" event for "default variants" on product pages
* [New] [Records of consent] Visitors can now see the consent data collected  in CDB (must be enabled by admin)
* [Update] [Woo] Added an extra check in JS to make sure no order is tracked twice
* [Fix] [Consent Banner] When "Settings" panel was disabled, hiding the panel with toggling icon did not work correctly
* [Fix] [CDB] Fixed a bug which sometimes prevented the latest WP FP configuration from being sent to CDB
* [Fix] Content of files with settings backups sometimes opened directly in a new tab
* [Fix] [Consent banner] Visitor were asked for consent every time priv. policy or tools changed - no matter whether the settings "ask visitors for consent" was enabled or not
* [Other] [Woo] [GA4 / Meta Pixel] Added a check to disable Status-Based Order Tracking (a.k.a Advanced Order Tracking) when the plugin switches from Pro to Free
* [Other] [GA4 / Meta Pixel] Added an extra debug information about server side tracking
* [Other] Cookie for saving consents is now always set to expire after 182 days unless it's for development
* [Other] New format of saving consents IDs in cdb_id cookie
* [Other] [Woo] [Pro] Added extra checks for getting customer data
* [Other] [Woo] [Pro] Renamed "Advanced Order Tracking" to "Status-Based Order Tracking"
* [Removed] IPs are no longer sent to CDB

= 8.4.0 (26-02-2025) =

* [New] Google Ads can now be installed using GTAG ID
* [New] [Woo] Added an option to provide a custom selector for product teasers
* [Update] Major rewrite of the internal file structure of the admin section of the plugin
* [Update] [Pro] Function for assigning non-HTTP referrers to proper sources no longer changes document.referrer if it contains a UTM
* [Update] WooCommerce default brand taxonomy is now tracked by default. All other ones are now optional.
* [Fix] Custom scripts were not saved in files right after the option was enabled in the general settings
* [Fix] [Woo] [Pro] In some situations user data was not being sent on the purchase confirmation page
* [Fix] [Google Consent Mode] Changed the default state of "functionality" to "denied" (set to "granted" after visitors agree to  personalisation cookies)
* [Other] Added a default style "display:none" to consent banner and toggler to hide them when custom content customizers are enabled (e.g. CartFlows setup manager or Kandence Email Customizer)
* [Other] Added an early "return" to the updater function
* [Other] Added a check to make sure that no premium modules are loaded when the user cancels Pro and gets a refund
* [Other] Change links to YT videos to links to the documentation

= 8.3.2 (27-01-2025) =

* [Update] Freemius SDK

= 8.3.1 (22-01-2025) =

* [Fix] Quick fix after last update. JS files did not get loaded if the site admin never save3d "general settings".

= 8.3.0 (21-01-2025) =

* [New] [Consent banner] Added an option to hide the banner on selected pages
* [New] [Performance] You can now save WP FP's main JS and Custom Scripts in files
* [New] Free users can now send data to the website's server via AJAX
* [Update] Added "nowprocket" parameter to inline JS so that WP Rocket does not break the plugin
* [Update] Custom Script now only output important data to fp.cscr object
* [Update] Added a browser console notification when a custom script is loaded and triggered
* [Update] Updated Freemius SDK
* [Fix] [GTM] Re-added mistakenly removed noscript fallback
* [Fix] [Free] WP FP settings didn't get sent to CDB after they were changed
* [Fix] Consent banner did not hide on the privacy page
* [Fix] [i18n] Updated loading of translation files
* [Removed] Default jQuery file dependency (it is now only added when Woo is enabled)
* [Removed] [Custom Scripts] Removed a condition which prevented scripts from loading in the customizer if the "force load" was active
* [Removed] [Custom Scripts] ID field in a script section (it is not necessary)
* [Other] [GDPR Compliance Helper] Add information that Google reCaptcha is not GDPR compliant and must be replaced
* [Other] Added licence is_pro checks to all JS files
* [Other] [Woo] Moved loading of inline script with the checkout data lower the head element (100 value) to make sure that it loads after the helpers FILE
* [Other] [Consent banner] Added "noopener" attribute to "Powered by" link to remove the warning in ahrefs

= 8.2.1 (18-12-2024) =

* [Fix] [Pro] When changing status of an order which contained a coupon code, Advanced Order Tracking for GA 4 gave error
* [Fix] GDPR Setup Helper no longer shows empty categories when modules are enabled without saved configuration settings

= 8.2.0 (16-12-2024) =

* [New] Free users can now use the ConsentsDB service
* [New] [GA] [Pro] Enhanced conversions is now also available for GA
* [New] [Woo] [Pro] Advanced order tracking for Meta Pixel and GA
* [New] [Free] Checking for bots is now available in the free version
* [Update] Server requests are now by default stopped for known bots
* [Update] Greatly improved system of backing up WP FP's settings
* [Update] [GA4] Added tracking shipping costs and taxes in separate order parameters
* [Update] Optimized server-side functions for Meta CAPI.
* [Update] Deferring scripts is now done via the WP's own method introduced in WP 6.3
* [Fix] [Consent banner] Quotes were escaped which prevented shortcodes from working
* [Fix] [Woo] fpdata.woo.cart was empty if there were no mini cart in the HTML of the checkout start page
* [Fix] Moved FP.getInner() to head-js.php to prevent errors on first page load when autoptimize joins JS files
* [Fix] Meta _fbc generation from fbclid URL parameter
* [Fix] [Reports] Fixed a bug that prevented users from creating multiple sections for adding iframes
* [Fix] Fixed the "translations loading too early" notice in WP 6.7
* [Fix] In some cituations Helpers JS file was loaded in the DOM head instead of the footer
* [Fix] [Facebook Pixel] External ID is now sent sha256 encoded
* [Fix] [Hotjar] Woo events were not sent if the user chose not to track event parameters
* [Removed] [GA4] Enabling debugView in the settings - useless if the site is live. Now enabling can be done only via ?ga4debug=on URL parameter
* [Removed] [Woo] Removed an icon indicating whether the order "thank you" page was viewed
* [Removed] Noscript fallbacks are no longer used since they are not GDPR compliant
* [Other] Included latest JS and CSS files for select2 dropdowns
* [Other] Function FP.doActions() can now pass arguments between actions
* [Other] Consent banner - default with increased to 700px

= 8.1.2 (26-11-2024) =

* [Other] Checked compatibility with latest WP version
* [Other] Added Black Friday deal info

= 8.1.1 (15-10-2024) =

* [Fix] A few minor fixes to the logic and texts of the GDPR setup helper
* [Fix] The page no longer refreshes when only Google's tools are loaded and visitors consent to only some cookies
* [Fix] [Woo] Orders from not logged clients are no longer tracked when they return to the "thank you" page
* [Removed] Removed checkbox with the "Administrator" role for excluded user roles - unnecessary, since admins are always excluded
* [Other] The name of the exported settings file now includes the site's domain
* [Other] Polish translation of the GDPR setup helper

= 8.1.0 (08-10-2024) =

* [New] Import/export of plugin settings
* [Removed] Adblock usage tracking - removed due to low accuracy

= 8.0.1 (01-10-2024) =

* [Fix] [Pro] Fixed PHP error when a user tried to enter secret key to consentsDB without registering it first
* [Fix] [Facebook] If user.id is missing, no external_id is set
* [Fix] [Woo] Semicolons are now removed from product titles and categories to prevent JSON parsing errors
* [Other] If OceanWP theme is active, then we disable the customizer controls for the Consent Banner and show in-admin notifications.

= 8.0.0 (24-09-2024) =

* [New] [Privacy] [Pro] [Beta] Saving consents in the cloud database
* [New] [Privacy] GDPR setup helper
* [New] [Pro] Visitor scoring
* [New] WP Full Picture is now fully translated to Polish
* [New] [Plausible] Added an option to track properties as goals
* [New] [Plausible] [Pro] Custom events tracking
* [New] [Advanced Triggers] [Pro] Added new triggers
* [New] [Consent Banner] You can now add shortcodes and links to all paragraph texts in the banner
* [New] [Consent Banner] Added a new button "I agree only to statistics"
* [New] [Consent Banner] You can now display a button in the corner of the screen for toggling the display of the banner
* [New] [Server side tracking] [Pro] Added AJAX method of sending data to the server so you can choose between this and Rest API.
* [New] [Matomo] WP FP now automatically sends custom "add to cart", "remove from cart" and "purchase" events along the internal Matomo ecommerce events
* [New] Added a big list of common robots user agents
* [Update] [Plausible] Statistics page can now be added to menu without enabling the "Reports" module
* [Update] [Plausible] Events can now be tracked as either separate events without properties or one event with properties
* [Update] [UI] Improved UI for giving various permissions to non-admin users
* [Update] [Advanced Triggers] [Pro] Improved function for initiating advanced triggers in the JS of tracking modules
* [Update] [Advanced Triggers] [Pro] Trigger title is now clearly marked as "required"
* [Update] [Advanced Triggers] [Pro] Unified the moment of initiating action listeners between scripts of tracking modules
* [Update] [UI] User no longer needs to enter the privacy page URL in separate fields. It is now set to the same URL as in WP's Settings > Privacy
* [Update] Tracking views of specific page elements now also works for dynamically added elements
* [Update] Improved form tracking
* [Update] Information about the last observed element is now available in fpdata as a reference to the DOM object
* [Update] Optimisations in head-js.php
* [Update] [WooCommerce] Analytics tools are now loaded on the "Thank you" page even if it is not in focus
* [Update] [Privacy] Optional cookies saved by the plugin now wait for consents for statistics
* [Fix] [Advanced Triggers] [Pro] When no conditions are given the trigger always passes checks (previously it didn't)
* [Fix] Traffic from Line app is now attributed to line-android-app.jp and not line-android-app.js
* [Fix] [GA4 #2, MS Clarity] Applied new UI for tracking metadata
* [Fix] [Privacy] [Hotjar] Order ID is no longer tracked in privacy mode and if visitors didn't agree to tracking statistics
* [Fix] [Privacy] [Matomo] Cross-browser tracking requires consent to send user IDs
* [Fix] [Inspectlet] Consents to personalisation cookies is no lober required when A/B tests are disabled
* [Fix] [Privacy] [Matomo] When privacy mode is enabled, cross-borwser tracking is disabled until visitors agree to tracking in a consent banner
* [Fix] [Privacy] [Matomo] When privacy mode is enabled, order IDs are randomized
* [Fix] [Advanced triggers] [Pro] When "compare with" value is 0 then the field did not save
* [Fix] [GTM] When "protect datalayer" option was turned on, the option NOT to clear ecommerce data did not work
* [Fix] Fixed cross browser tracking in GA and Matomo
* [Fix] Single checkbox field threw errors when it was not used in the repeater field
* [Fix] [Crazy Egg] Outbound clicks now have correct tag names
* [Fix] Adblock checker sometimes didn't fire because the test file loaded before the DOM was ready
* [Fix] [Consent Banner] Creating links in descriptions didn't work correctly when the custom text was removed
* [Fix] Added a workaround to stop Google for WooCommerce plugin from overwriting consents
* [Fix] [Woo] No longer tracks clicks in "add to cart button on product teasers of products that cannot be purchased
* [Fix] [Woo] Settings that required Woo module and plugin, sometimes didn't get disabled when they should
* [Removed] [Advanced Triggers] [Pro] "Instant" action trigger (Replaced with "dom_loaded" trigger)
* [Other] Mark Advanced Triggers scripts as Premium only
* [Other] Min. required WP version is now 5.4
* [Other] [UI] Added a link to the "What's new" page with info on latest updates
* [Other] [UI] Added info on what consents are needed to run a script
* [Other] [CSS] Changed the class of a toggle switch from "fupi_slider" to "fupi_switch_slider"
* [Deprecated] fpdata.new_tab will be removed in 8.2 since there is no bulletproof solution to check it
* [Deprecated] adblock checks will be removed in 8.2 since its accuracy is very poor after adblock updates