=== All-in-1 Analytics and Consent Banner - WP Full Picture ===
Contributors: chrisplaneta
Donate link: https://wpfullpicture.com/
Tags: Consent mode, Analytics, GDPR, GTM, Google Ads
Requires at least: 5.4.0
Tested up to: 6.8.2
Stable tag: 9.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Set up analytis tools and a consent banner in 1 tool. Integrations with Google Analytics, GTM, Meta pixel, WooCommerce and more.

== Description ==

WP Full Picture is an all-in-1 plugin for analytics and consent management.

It can replace several plugins and save you from dealing with compatibility issues and confusing setup. It replaces:

- PixelYourSite, Pixel Manager for WooCommerce, MonsterInsights and other similar plugins,
- GTM4WP plugin (WP FP includes its own Google Tag Manager integration),
- CookieBot, CookieYes, Complianz and similar solutions.

It is the only tool you need to install tracking tools, display a consent banner (GDPR-compliant, with consent mode), manage consents, track user actions and show statistics in WP admin panel. Sweet!

See for yourself.

[youtube https://www.youtube.com/watch?v=5Z_5k089oHw]

## 🧩 INTEGRATIONS WITH TRACKING TOOLS

WP FP has ready-to-use integrations with:

- Google Analytics [feature list](https://wpfullpicture.com/module/google-analytics-integration-for-wordpress/)
- Google Ads [feature list](https://wpfullpicture.com/module/google-ads/)
- Google Tag Manager [feature list](https://wpfullpicture.com/module/google-tag-manager/)
- Meta Pixel [feature list](https://wpfullpicture.com/module/meta-pixel/)
- Pinterest Ads tag [feature list](https://wpfullpicture.com/module/pinterest-ads/)
- Hotjar [feature list](https://wpfullpicture.com/module/hotjar/)
- Twitter / X [feature list](https://wpfullpicture.com/module/x-ads-twitter-ads/)
- Microsoft Advertising [feature list](https://wpfullpicture.com/module/microsoft-advertising/)
- Microsoft Clarity [feature list](https://wpfullpicture.com/module/microsoft-clarity/)
- LinkedIn Insights Tag [feature list](https://wpfullpicture.com/module/linkedin-insight-tag/)
- TikTok Pixel [feature list](https://wpfullpicture.com/module/tiktok-pixel/)
- Plausible Analytics [feature list](https://wpfullpicture.com/module/plausible-analytics/)
- PostHog (installation only)
- Simple Analytics (installation only)
- Matomo [feature list](https://wpfullpicture.com/module/matomo/)
- Inspectlet [feature list](https://wpfullpicture.com/module/inspectlet/)
- Crazy Egg [feature list](https://wpfullpicture.com/module/crazy-egg/)

You can also install other tools with the built-in GTM integration and Custom Scripts module [feature list](https://wpfullpicture.com/module/custom-scripts/).

All of these integrations work seemlessly with our built-in consent banner and consent management solutions.

**[PRO]** Currently, there are no extra integrations available in the Pro version.

## 🍪 CONSENT BANNER

WP Full Picture comes with a GDPR-compliant consent banner. It supports **Google Advanced Consent Mode** and **Microsoft UET Consent Mode** out-of-the-box.

It manages all tracking tools installed with WP Full Picture - with no extra setup, cookie scanning or usage limits.

Other tools can be controlled with our iframes blocker and tracking tools manager.

See how it compares to CookieBot, CookieYes and Complianz.

[youtube https://www.youtube.com/watch?v=IrSMKJDB840]

**[PRO]** The Pro version lets you show a consent banner only in countries where it is required.

## ✍️ CONSENT MANAGEMENT

WP Full Picture Free comes with an integration with ConsentsDB, for storing users' tracking consents. The service is paid extra, starting at less then a dollar a month.

**[PRO]** WP Full Picture Pro comes with a free option to store consents in your email inbox.

## 👁️ ADVANCED TRACKING

WP Full Picture Free lets you track more user actions, events and data than any of its competitors.

You can track:

- Clicks in affiliate links
- Clicks in contact links
- Clicks on buttons and other page elements
- Form submissions
- Views of popups, ads, pricing tables and other important page elements
- WooCommerce events (see below)
- Locations of broken links (to pages that don't exist)
- Page types, categories and tags
- Custom taxonomies
- User roles and statuses
- and more

**[PRO]** WP Full Picture Pro comes with advanced tracking features:

- Advanced Matching / Enhanced conversions tracking
- Behavioral tracking (multi-conditional event triggers)
- Visitor scoring (for measuring the quality of traffic sources)
- Server-side tracking
- Google Tag Gateway
- Tracking sources of traffic from Android social media apps
- Metadata tracking
- JavaScript error tracking (only supported by Google Analytics)
- and more

## 🛒 WOOCOMMERCE TRACKING

WP Full Picture lets you easily track WooCommerce events in 14 tracking tools, from Google Analytics to Google Tag Manager.

You can track product views, clicks in "add to cart" buttons, checkouts, purchases and many more.

We send every event with product and event data (except user information - PRO only), which is extremely important for advertising and conversion tracking.

**[PRO]** The PRO version comes with status-based order tracking (an alternative method of tracking purchases) and advanced matching/enhanced conversions.

## 📈 STATISTICS IN WP ADMIN

Do you want to see traffic statistics and marketing reports in your WP admin dashboard? No problem.

You can easily show in your WP admin panel reports and dashboards from Google Looker Studio, Databox and other similar services.

**[PRO]** PRO version lets you choose non-admin users in your store that will also have access to reports.

## 💎 WP FULL PICTURE PRO vs FREE

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

Yes. We tested it with Gutenberg, Elementor, Bricks, Breakdance and saw no issues.

= Does WP Full Picture support Google's consent mode v2? =

Yes. Consent mode v2 is fully supported. It works with Google Analytics, Google Ads and GTM. It works out-of-the-box. Simply, enable the consent banner module and it will work.

= Does WP Full Picture display statistics in the dashboard? =

Yes. WP Full Picture lets you display in your WP admin panel reports and dashboards created in Google Looker Studio, Databox and other similar services. 

These platforms allow you to create advanced reports with aggregated data from various analytics and marketing tools, Google spreadsheets and even WooCommerce data.

= I installed an analytics tool using a different plugin. Do I have to remvoe it and install again using WP Full Picture? =

No. You can use the settings in the Consent Banner module, to take control of tracking scripts loaded by other plugins, and load them according to privacy regulations.

= I am using a tracking tool installed with a different plugin. Can I only use WP Full Picture's consent banner? =

Yes. You can use it to control tracking scripts installed by other plugins and load them according to privacy laws.

= Can I use a different consent management plugin or platform (CookieBot, Iubenda, CookieYes, etc.) with WP Full Picture? =

No. WP Full Picture's modules for installing analytics tools are only optimized to work with WP Full Picture's consent banner.

= Can I use WP Full Picture on a website that displays ads? =

Yes, but with limitations. To show personalised ads from Google Adsense and other advertising platforms, you need to use a consent banner with IAB FTC certificate. WP Full Picture does not have it at the moment.

= How does WP Full Picture's cookie notice block cookies?

Depending on the tracking tool, WP Full Picture either instructs that tool not to load cookies or blocks scripts that create these cookies.

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

= 9.1.2 (02-09-2025) =

* [Removed] Information about promotional consents in ConsentsDB
* [Removed] Small code cleaning

= 9.1.1 (13-08-2025) =

* [Fix] GDPR setup info did not show some headings if the Privacy Policy page was not published
* [Fix] Rest API issues with Zapier
* [Other] Added social links to the menu

= 9.1.0 (06-08-2025) =

* [New] [Woo] [Pro] Added a meta box with tracking information to order pages
* [New] [Woo] [Pro] Re-written status-based order tracking
* [New] [Consent banner] You can now disable asking for consents after privacy policy changes or new modules activation. Handy while setting up tracking and testing.
* [Update] More information is now output to the browser console when the Setup Mode is enabled
* [Update] Setup mode is now automatically turned off after 6 hours
* [Fix] Added a check to make sure that modules which require some settings are no longer loaded if the user did not save any
* [Fix] fupi_tools options are now saved if they are empty
* [Fix] Iframes managed with HTML or shortcode did not load if no other iframe management options were enabled
* [Fix] Actions triggered when the page loses focus are now correctly triggered
* [Fix] Translating iframe block in WPML and Polylang did not work correctly after 9.0 update. Now it's fixed
* [Fix] Google Analytics debug view could not be enabled via the link parameter
* [Fix] Meta tag was not saved in the General Settings when the settings were saved for the first time
* [Other] Small UI tweaks

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