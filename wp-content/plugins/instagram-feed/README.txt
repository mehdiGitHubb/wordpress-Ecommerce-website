=== Smash Balloon Social Photo Feed ===
Contributors: smashballoon, craig-at-smash-balloon, am, smub
Tags: Instagram, Instagram feed, Instagram photos, Instagram widget, Instagram gallery
Requires at least: 4.1
Tested up to: 6.1
Stable tag: 6.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Formerly "Instagram Feed". Display clean, customizable, and responsive Instagram feeds from multiple accounts. Supports Instagram oEmbeds.

== Description ==

Display Instagram posts from your Instagram accounts, either in the same single feed or in multiple different ones.

= Features =
* **New:** Now automatically powers your **Instagram oEmbeds**
* Super **simple to set up**
* Display photos from **multiple Instagram accounts** in the same feed or in separate feeds
* Completely **responsive** and mobile ready - layout looks great on any screen size and in any container width
* **Completely customizable** - Customize the width, height, number of photos, number of columns, image size, background color, image spacing and more!
* Display **multiple Instagram feeds** on the same page or on different pages throughout your site
* **GDPR Compliance** - automatically integrates with many of the popular GDPR cookie consent plugins and includes a 1-click easy GDPR setting.
* Use the built-in **shortcode options** to completely customize each of your Instagram feeds
* Display thumbnail, medium or **full-size photos** from your Instagram feed
* **Infinitely load more** of your Instagram photos with the 'Load More' button
* Includes a **Follow on Instagram button** at the bottom of your feed
* Display a **beautiful header** at the top of your feed
* Display your Instagram photos chronologically or in random order
* Add your own Custom CSS and JavaScript for even deeper customizations
* Handy block for easily adding your feed to posts and pages using the block editor

= Benefits =
* **Increase Social Engagement** - Increase engagement between you and your Instagram followers. Increase your number of followers by displaying your Instagram content directly on your site.
* **Save Time** - Don't have time to update your photos on your site? Save time and increase efficiency by only posting your photos to Instagram and automatically displaying them on your website
* **Display Your Content Your Way** - Customize your Instagram feeds to look exactly the way you want, so that they blend seemlessly into your site or pop out at your visitors!
* **Keep Your Site Looking Fresh** - Automatically push your new Instagram content straight to your site to keep it looking fresh and keeping your audience engaged.
* **Super simple to set up** - Once installed, you can be displaying your Instagram photos within 30 seconds! No confusing steps or Instagram Developer account needed.
* **Powers all Instagram oEmbeds on your site** - With WordPress removing support for Instagram oEmbeds, the plugin will now power all Instagram embeds on your site, old and new, to allow them to continue working.

= Pro Version =
In order to maintain the free version of the plugin on an ongoing basis, and to provide quick and effective support for free, we offer a Pro version of the plugin. The Pro version allows you to:
* Display Hashtag feeds
* View photos and videos in a popup lightbox directly on your site
* View post comments for user feeds
* Display the number of like and comments for each post
* Create carousels from your posts
* Use "Masonry" or "Highlight" layouts for your feeds
* Display captions for photos and videos
* Filter posts based on hashtag/word
* Advanced moderation system for hiding/showing certain posts
* Block posts by specific users
* Create "shoppable" Instagram feeds, and more.

[Find out more about the Pro version](https://smashballoon.com/instagram-feed/?utm_campaign=instagram-free-readme&utm_source=proversion&utm_medium=profindout "Instagram Feed Pro") or [try out the Pro demo](https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free-readme&utm_source=proversion&utm_medium=readmedemo "Instagram Feed Pro Demo").

= Featured Reviews =
"**Simple and concise** - Excellent plugin. Simple and non-bloated. I had a couple small issues with the plugin when I first started using it, but a quick comment on the support forums got a new version pushed out the next day with the fix. Awesome support!" - [Josh Jones](https://wordpress.org/support/topic/simple-and-concise-3 'Simple and concise Instagram plugin')

"**Great plugin, greater support!** - I've definitely noticed an increase in followers on Instagram since I added this plugin to my sidebar. Thanks for the help in making some adjustments...looks and works great!" - [BNOTP](https://wordpress.org/support/topic/thanks-for-a-great-plugin-6 'Great plugin, greater Support!')

= Feedback or Support =
We're dedicated to providing the most customizable, robust and well supported Instagram feed plugin in the world, so if you have an issue or have any feedback on how to improve the plugin then please open a ticket in the [Support forum](http://wordpress.org/support/plugin/instagram-feed 'Instagram Feed Support Forum').

For a pop-up photo **lightbox**, to display posts by **hashtag**, show photo **captions**, **video** support + more, check out the [Pro version](http://smashballoon.com/instagram-feed/?utm_campaign=instagram-free-readme&utm_source=feedbacj&utm_medium=support 'Instagram Feed Pro').

== Installation ==

1. Install the Instagram Feed plugin either via the WordPress plugin directory, or by uploading the files to your web server (in the `/wp-content/plugins/` directory).
2. Activate the Instagram Feed plugin through the 'Plugins' menu in WordPress.
3. Navigate to the 'Instagram Feed' settings page to connect your Instagram account.
4. Use the shortcode `[instagram-feed]` in your page, post or widget to display your Instagram photos.
5. You can display multiple Instagram feeds by using shortcode options, for example: `[instagram-feed num=6 cols=3]`

For simple step-by-step directions on how to set up the Instagram Feed plugin please refer to our [setup guide](http://smashballoon.com/instagram-feed/free/?utm_campaign=instagram-free-readme&utm_source=installation&utm_medium=setup 'Instagram Feed setup guide').

= Display your Feed =

**Single Instagram Feed**

Copy and paste the following shortcode directly into the page, post or widget where you'd like the Instagram feed to show up: `[instagram-feed]`

**Multiple Instagram Feeds**

If you'd like to display multiple Instagram feeds then you can set different settings directly in the shortcode like so: `[instagram-feed num=9 cols=3]`

If you'd like to display feed from more than one account, connect multiple accounts on the "Configure" tab and then add the user name in the shortcode: `[instagram-feed user="ANOTHER_USER_NAME"]`

You can display as many different Instagram feeds as you like, on either the same page or on different pages, by just using the shortcode options below. For example:
`[instagram-feed]`
`[instagram-feed user="ANOTHER_USER_NAME"]`
`[instagram-feed user="ANOTHER_USER_NAME, YET_ANOTHER_USER_NAME" num=4 cols=4 showfollow=false]`

See the table below for a full list of available shortcode options:

= Shortcode Options =
* **General Options**
* **user** - An Instagram User Name (must have account connected) - Example: `[instagram-feed user=AN_INSTAGRAM_USER_NAME]`
* **width** - The width of your Instagram feed. Any number - Example: `[instagram-feed width=50]`
* **widthunit** - The unit of the width of your Instagram feed. 'px' or '%' - Example: `[instagram-feed widthunit=%]`
* **height** - The height of your Instagram feed. Any number - Example: `[instagram-feed height=250]`
* **heightunit** - The unit of the height of your Instagram feed. 'px' or '%' - Example: `[instagram-feed heightunit=px]`
* **background** - The background color of the Instagram feed. Any hex color code - Example: `[instagram-feed background=#ffff00]`
* **class** - Add a CSS class to the Instagram feed container - Example: `[instagram-feed class=feedOne]`
*
* **Photo Options**
* **sortby** - Sort the Instagram posts by Newest to Oldest (none) or Random (random) - Example: `[instagram-feed sortby=random]`
* **num** - The number of Instagram posts to display initially. Maximum is 33 - Example: `[instagram-feed num=10]`

* **cols** - The number of columns in your Instagram feed. 1 - 10 - Example: `[instagram-feed cols=5]`
* **imageres** - The resolution/size of the Instagram photos. 'auto', full', 'medium' or 'thumb' - Example: `[instagram-feed imageres=full]`
* **imagepadding** - The spacing around your Instagram photos - Example: `[instagram-feed imagepadding=10]`
* **imagepaddingunit** - The unit of the padding in your Instagram feed. 'px' or '%' - Example: `[instagram-feed imagepaddingunit=px]`
* **disablemobile** - Disable the mobile layout for your Instagram feed. 'true' or 'false' - Example: `[instagram-feed disablemobile=true]`
*
* **Header Options**
* **showheader** - Whether to show the Instagram feed Header. 'true' or 'false' - Example: `[instagram-feed showheader=false]`
* **showbio** - Whether to show the account's bio in the Instagram feed Header. 'true' or 'false' - Example: `[instagram-feed showbio=false]`
* **custombio** - Custom Bio text for the Instagram feed Header - Example: `[instagram-feed custombio="My custom bio."]`
* **customavatar** - URL of a custom Avatar for the header. Example: `[instagram-feed customavatar="https://my-site.com/avatar.jpg"]`

* **headercolor** - The color of the Instagram feed Header text. Any hex color code - Example: `[instagram-feed headercolor=#333]`
*
* **'Load More' Button Options**
* **showbutton** - Whether to show the 'Load More' button. 'true' or 'false' - Example: `[instagram-feed showbutton='false']`
* **buttoncolor** - The background color of the button. Any hex color code - Example: `[instagram-feed buttoncolor=#000]`
* **buttontextcolor** - The text color of the button. Any hex color code - Example: `[instagram-feed buttontextcolor=#fff]`
* **buttontext** - The text used for the button - Example: `[instagram-feed buttontext="Load More Photos"]`
*
* **'Follow on Instagram' Button Options**
* **showfollow** - Whether to show the 'Follow on Instagram' button. 'true' or 'false' - Example: `[instagram-feed showfollow=true]`
* **followcolor** - The background color of the 'Follow on Instagram' button. Any hex color code - Example: `[instagram-feed followcolor=#ff0000]`
* **followtextcolor** - The text color of the 'Follow on Instagram' button. Any hex color code - Example: `[instagram-feed followtextcolor=#fff]`
* **followtext** - The text used for the 'Follow on Instagram' button - Example: `[instagram-feed followtext="Follow me"]`

For more shortcode options, check out the [Pro version](http://smashballoon.com/instagram-feed/?utm_campaign=instagram-free-readme&utm_source=shortcode&utm_medium=shortcodepro 'Instagram Feed Pro').

= Setting up the Free Instagram Feed WordPress Plugin =

1) Once you've installed the Instagram Feed plugin click on the Instagram Feed item in your WordPress menu

2) Click on the large blue Instagram button to log into your Instagram account and connect your Instagram account. If you're having trouble retrieving your Instagram information from Instagram then try using the Instagram button on [this page](https://smashballoon.com/instagram-feed/token/?utm_campaign=instagram-free-readme&utm_source=settingup&utm_medium=connectionproblem) instead.

You can also display photos from other Instagram accounts by connecting additional Instagram accounts and adding the user name in the shortcode.

3) Navigate to the Instagram Feed customize page to customize your Instagram feed.

4) Once you've customized your Instagram feed, click on the Display Your Feed tab to grab the [instagram-feed] shortcode.

5) Copy the Instagram Feed shortcode and paste it into any page, post or widget where you want the Instagram feed to appear.

6) You can paste the Instagram Feed shortcode directly into your page editor.

7) You can use the default WordPress 'Text' widget to display your Instagram Feed in a sidebar or other widget area.

== Frequently Asked Questions ==

= Can I display multiple Instagram feeds on my site or on the same page? =

Yep. You can display multiple Instagram feeds by using our built-in shortcode options, for example: `[instagram-feed user="smashballoon" cols=3]`. Be sure to connect the related Instagram account on the "Configure" tab.

= Can I display photos from more than one Instagram account in one single feed? =

Yep. You can add multiple user names from the connected accounts on the plugin's Settings page, or directly in the shortcode, separated by commas, like so: `[instagram-feed user="smashballoon, instagramfeed"]`.

= Does the plugin work with Instagram oEmbeds? =

In version 2.5, support was added to allow the plugin to power your Instagram oEmbeds as official support for these is no longer available in WordPress core. Just connect your account on the oEmbeds settings page inside the plugin and we'll do the rest. No developer app or account required.

= How do I find my Instagram Access Token and Instagram User ID =

We've made it super easy. Simply click on the big blue button on the Instagram Feed Settings page and log into your Instagram account. The plugin will then ask if you'd like to connect the account and start using it in a feed.

= My Instagram feed isn't displaying. Why not!? =

There are a few common reasons for this:

* **Your Access Token may not be valid.** Try clicking on the blue Instagram login button on the plugin's Settings page again and copy and paste the Instagram token it gives you into the plugin's Access Token field.
* **The plugin's JavaScript file isn't being included in your page.** This is most likely because your WordPress theme is missing the WordPress [wp_footer](http://codex.wordpress.org/Function_Reference/wp_footer) function which is required for plugins to be able to add their JavaScript files to your page. You can fix this by opening your theme's **footer.php** file and adding the following directly before the closing </body> tag: `<?php wp_footer(); ?>`
* **Your website may contain a JavaScript error which is preventing JavaScript from running.** The plugin uses JavaScript to load the Instagram photos into your page and so needs JavaScript to be running in order to work. You would need to remove any existing JavaScript errors on your website for the plugin to be able to load in your feed.

If you're still having an issue displaying your feed then please open a ticket in the [Support forum](http://wordpress.org/support/plugin/instagram-feed 'Instagram Feed Support Forum') with a link to the page where you're trying to display the Instagram feed and, if possible, a link to your Instagram account.

= Are there any security issues with using an Access Token on my site? =

Nope. The Access Token used in the plugin is a "read only" token, which means that it could never be used maliciously to manipulate your Instagram account.

= Can I view the full-size photos or play Instagram videos directly on my website?  =

This is a feature of the [Pro version](http://smashballoon.com/instagram-feed/?utm_campaign=instagram-free-readme&utm_source=faqs&utm_medium=fullsize 'Instagram Feed Pro') of the plugin, which allows you to view the photos in a pop-up lightbox, support videos, display captions, display photos by hashtag + more!

= How do I embed my Instagram Feed directly into a WordPress page template? =

You can embed your Instagram feed directly into a template file by using the WordPress [do_shortcode](http://codex.wordpress.org/Function_Reference/do_shortcode) function: `<?php echo do_shortcode('[instagram-feed]'); ?>`.

= My Feed Stopped Working – All I see is a Loading Symbol =

If your Instagram photos aren't loading and all your see is a loading symbol then there are a few common reasons:

1) There's an issue with the Instagram Access Token that you are using

You can obtain a new Instagram Access Token on the Instagram Feed Settings page by clicking the blue Instagram login button and then copy and pasting it into the plugin's 'Access Token' field.

Occasionally the blue Instagram login button does not produce a working access token. You can try [this link](https://smashballoon.com/instagram-feed/token/?utm_campaign=instagram-free-readme&utm_source=faqs&utm_medium=faqconnectionissue) as well.

2) The plugin's JavaScript file isn't being included in your page

This is most likely because your WordPress theme is missing the WordPress wp_footer function which is required for plugins to be able to add their JavaScript files to your page. You can fix this by opening your theme's footer.php file and adding the following directly before the closing </body> tag:

<?php wp_footer(); ?>

3) There's a JavaScript error on your site which is preventing the plugin's JavaScript file from running

You find find out whether this is the case by right clicking on your page, selecting 'Inspect Element', and then clicking on the 'Console' tab, or by selecting the 'JavaScript Console' option from your browser's Developer Tools.

If a JavaScript error is occurring on your site then you'll see it listed in red along with the JavaScript file which is causing it.

4) The feed you are trying to display has no Instagram posts

If you are trying to display an Instagram feed that has no posts made to it, a loading symbol may be all that shows for the Instagram feed or nothing at all. Once you add an Instagram post the Instagram feed should display normally

5) The shortcode you are using is incorrect

You may have an error in the Instagram Feed shortcode you are using or are missing a necessary argument.

= What are the available shortcode options that I can use to customize my Instagram feed? =

The below options are available on the Instagram Feed Settings page but can also be used directly in the `[instagram-feed]` shortcode to customize individual Instagram feeds on a feed-by-feed basis.

* **General Options**
* **user** - An Instagram User Name (must have account connected) - Example: `[instagram-feed user=AN_INSTAGRAM_USER_NAME]`
* **width** - The width of your Instagram feed. Any number - Example: `[instagram-feed width=50]`
* **widthunit** - The unit of the width of your Instagram feed. 'px' or '%' - Example: `[instagram-feed widthunit=%]`
* **height** - The height of your Instagram feed. Any number - Example: `[instagram-feed height=250]`
* **heightunit** - The unit of the height of your Instagram feed. 'px' or '%' - Example: `[instagram-feed heightunit=px]`
* **background** - The background color of the Instagram feed. Any hex color code - Example: `[instagram-feed background=#ffff00]`
* **class** - Add a CSS class to the Instagram feed container - Example: `[instagram-feed class=feedOne]`
*
* **Photo Options**
* **sortby** - Sort the Instagram posts by Newest to Oldest (none) or Random (random) - Example: `[instagram-feed sortby=random]`
* **num** - The number of Instagram posts to display initially. Maximum is 33 - Example: `[instagram-feed num=10]`

* **cols** - The number of columns in your Instagram feed. 1 - 10 - Example: `[instagram-feed cols=5]`
* **imageres** - The resolution/size of the Instagram photos. 'auto', full', 'medium' or 'thumb' - Example: `[instagram-feed imageres=full]`
* **imagepadding** - The spacing around your Instagram photos - Example: `[instagram-feed imagepadding=10]`
* **imagepaddingunit** - The unit of the padding in your Instagram feed. 'px' or '%' - Example: `[instagram-feed imagepaddingunit=px]`
* **disablemobile** - Disable the mobile layout for your Instagram feed. 'true' or 'false' - Example: `[instagram-feed disablemobile=true]`
*
* **Header Options**
* **showheader** - Whether to show the Instagram feed Header. 'true' or 'false' - Example: `[instagram-feed showheader=false]`
* **showbio** - Whether to show the account's bio in the Instagram feed Header. 'true' or 'false' - Example: `[instagram-feed showbio=false]`
* **custombio** - Custom Bio text for the Instagram feed Header - Example: `[instagram-feed custombio="My custom bio."]`
* **customavatar** - URL of a custom Avatar for the header. Example: `[instagram-feed customavatar="https://my-site.com/avatar.jpg"]`

* **headercolor** - The color of the Instagram feed Header text. Any hex color code - Example: `[instagram-feed headercolor=#333]`
*
* **'Load More' Button Options**
* **showbutton** - Whether to show the 'Load More' button. 'true' or 'false' - Example: `[instagram-feed showbutton='false']`
* **buttoncolor** - The background color of the button. Any hex color code - Example: `[instagram-feed buttoncolor=#000]`
* **buttontextcolor** - The text color of the button. Any hex color code - Example: `[instagram-feed buttontextcolor=#fff]`
* **buttontext** - The text used for the button - Example: `[instagram-feed buttontext="Load More Photos"]`
*
* **'Follow on Instagram' Button Options**
* **showfollow** - Whether to show the 'Follow on Instagram' button. 'true' or 'false' - Example: `[instagram-feed showfollow=true]`
* **followcolor** - The background color of the 'Follow on Instagram' button. Any hex color code - Example: `[instagram-feed followcolor=#ff0000]`
* **followtextcolor** - The text color of the 'Follow on Instagram' button. Any hex color code - Example: `[instagram-feed followtextcolor=#fff]`
* **followtext** - The text used for the 'Follow on Instagram' button - Example: `[instagram-feed followtext="Follow me"]`

For more shortcode options, check out the [Pro version](http://smashballoon.com/instagram-feed/?utm_campaign=instagram-free-readme&utm_source=whatare&utm_medium=proshortcode 'Instagram Feed Pro').

For more FAQs related to the Instagram Feed plugin please visit the [FAQ section](https://smashballoon.com/instagram-feed/support/faq/?utm_campaign=instagram-free-readme&utm_source=whatare&utm_medium=faqs 'Instagram Feed plugin FAQs') on our website.

== Screenshots ==

1. Easily display feeds from any of your Instagram accounts
2. Your Instagram Feed is completely customizable and responsive
3. Combine multiple accounts into a single feed
5. Super quick and easy to get started. Just click the button to connect an Instagram account.
5. Customize layouts, styles, colors, and more
6. Just copy and paste the shortcode into any page, post or widget on your site

== Other Notes ==

Add beautifully clean, customizable, and responsive Instagram feeds to your website. Super simple to set up and tons of customization options to seamlessly match the look and feel of your site.

= Why do I need this? =

**Increase Social Engagement**
Increase engagement between you and your Instagram followers. Increase your number of Instagram followers by displaying your Instagram content directly on your site.

**Save Time**
Don't have time to update your photos on your site? Save time and increase efficiency by only posting your photos to Instagram and automatically displaying them on your website.

**Display Your Content Your Way**
Customize your Instagram feeds to look exactly the way you want, so that they blend seemlessly into your site or pop out at your visitors!

**Keep Your Site Looking Fresh**
Automatically push your new Instagram content straight to your site to keep it looking fresh and keeping your audience engaged.

**No Coding Required**
Choose from tons of built-in Instagram Feed customization options to create a truly unique feed of your Instagram content.

**Super simple to set up**
Once installed, you can be displaying your Instagram photos within 30 seconds! No confusing steps or Instagram Developer account needed.

**Mind-blowing Customer Support**
We understand that sometimes you need help, have issues or just have questions. We love our users and strive to provide the best support experience in the business. We're experts in the Instagram API and can provide unparalleled service and expertise. If you need support then just let us know and we'll get back to you right away.

= What can it do? =

* Display Instagram photos from any Instagram account you own.
* Completely responsive and mobile ready –your Instagram feed layout looks great on any screen size and in any container width
* Display multiple Instagram feeds on the same page or on different pages throughout your site by using our powerful Instagram Feed shortcode options
* Display posts from multiple Instagram User IDs
* Use the built-in shortcode options to completely customize each of your Instagram feeds
* Infinitely load more of your Instagram photos with the 'Load More' button
* Plus more features added all the time!

= Completely Customizable =

* By default the Instagram feed will adopt the style of your website, but can be completely customized to look however you like!
* Set the number of Instagram photos you want to display
* Choose how many columns to display your Instagram photos in and the size of the Instagram photos
* Choose to show or hide certain parts of the Instagram feed, such as the header, 'Load More', and 'Follow' buttons
* Control the width, height and background color of your Instagram feed
* Set the spacing/padding between the Instagram photos
* Display Instagram photos in chronological or random order
* Use your own custom text and colors for the 'Load More' and 'Follow' buttons
* Enter your own custom CSS or JavaScript for even deeper customization
* Use the shortcode options to style multiple Instagram feeds in completely different ways
* Plus more customization options added all the time!

== Changelog ==
= 6.1.1 =
* Fix: When using the customizer to enable the setting for the header "show outside scrollable area" and adding a background color. The preview would not show the same result as the actual feed.
* Fix: Disabling the JavaScript image loading on the "Advanced" settings tab would cause the customizer preview to look distorted.
* Fix: When customizing a feed, the load more button would be come active when switching the device preview.
* Fix: Fixed a PHP warning that would occur when bulk deleting feeds.

= 6.1 =
* New: Added the ability to filter "Reels" posts in feeds. When customizing a feed and using the moderation settings you can now choose to show or hide Instagram "Reels" posts.
* New: Add a header image and bio text for personal sources. Go to the settings page and click on the gear icon to add this to an existing source.
* New: Added support for Instagram "Reels" oEmbeds. Use WordPress' embed block to create rich oEmbed links in blog posts and pages.
* Tweak: Vue.js code is now loaded from a local file shipped with the plugin rather than an external CDN for use with the customizer in the admin area.

= 6.0.8 =
* Tweak: Added a workaround to retrieve missing images if none were returned by Instagram for a post.
* Fix: Custom colors assigned to the Follow button would not apply when using a custom color palette.
* Fix: Added additional plugin hardening.
* Fix: A fatal error would occur with older versions of PHP and WordPress in some circumstances.

= 6.0.7 =
* Fix: Removed legacy "disable mobile" setting support as it was causing confusion for users updating from 2.x where changes to feed columns would not have an effect.
* Fix: Removed the reference in the feed CSS file to an image file that didn't exist.in the feed CSS file.
* Fix: All sources would be removed when the grace period to address app permission issues ended. Now only the single source will be removed.
* Fix: The number of posts would be inaccurate in the feed preview when using the customizer for mobile devices.

= 6.0.6 =
* Tweak: Added a warning notice to allow a grace period before Instagram data is permanently deleted from your site after deauthorizing the Smash Balloon Instagram app. Due to Instagram requirements, any Instagram data on your site must be deleted within a reasonable time after the app has been deauthorized. The new warning notice provides a 7 day grace period to allow you time to reauthorize the app if you don't want the data to be deleted.
* Tweak: Reconnecting an account now results in deleting the original connection in the database and adding a new one. This will prevent issues with some caching systems like Redis.
* Fix: Only the first 20 sources were available when creating feeds and changing sources for a feed.
* Fix: The link in some error messages were incorrect resulting in "access denied" error messages when clicking on them.

= 6.0.5 =
* Tweak: If WordPress cron is broken or behind schedule and causing background caching to not work, the plugin will update the feed when the page loads.
* Fix: Jetpack's "Master Bar" feature was causing the sidebar in the customizer to be partially hidden.
* Fix: Added back support for the "class" shortcode setting for all feeds.
* Fix: Removed all Font Awesome icons and no longer include the CSS file from the Font Awesome CDN.

= 6.0.4 =
* Fix: Added back the ability to use up to 10 columns in feeds.
* Fix: The reconnect link that would display when an account had an error would not redirect to connect.smashballoon.com.

= 6.0.3 =
* Tweak: Updated our logo throughout the plugin to match our new [website](https://smashballoon.com/).
* Tweak: Changed how the hover color for follow and load more buttons is applied to prevent theme conflicts.
* Fix: Fixed JavaScript file not being added to the page when using the plugin GDPR Cookie Consent by WebToffee.
* Fix: Dismissing dashboard notifications would cause the "Add new feed" button to stop working until the page was refreshed.

= 6.0.2 =
* Fix: Fixed Instagram Feed JavaScript file missing from the page when using the "AJAX theme loading fix" setting causing blank images to display.
* Fix: Added the ability to create the custom database tables if there was an error when first trying to create them.
* Fix: Fixed the error message not displaying if there was an error when trying to connect a personal or basic account.

= 6.0.1 =
* Fix: Custom HTML templates were not applying to new feeds.
* Fix: Some custom tables were not being created for specific versions of MySQL.
* Fix: The shortcode setting "showfollow=false" was not working for legacy feeds.
* Fix: The shortcode settings "showheader" and "showbio" were applying for non-legacy feeds causing confusion when trying to change these settings in the customizer.
* Fix: The customizer would not resize images causing blank images to show when GDPR features were enabled.
* Fix: Fixed PHP warning "Undefined array key tagged".

= 6.0 =
* Important: Minimum supported WordPress version has been raised from 3.5 to 4.1.
* New: Our biggest update ever! We've completely redesigned the plugin settings from head to toe to make it easier to create, manage, and customize your Instagram feeds.
* New: All your feeds are now displayed in one place on the "All Feeds" page. This shows a list of any existing (legacy) feeds and any new ones that you create. Note: If you updated from a version prior to v2.8 then you may need to view your feeds on your webpage so that the plugin can locate them and list them here.
* New: Easily edit individual feed settings for new feeds instead of cumbersome shortcode options.
* New: It's now much easier to create feeds. Just click "Add New", select your feed type, connect your account, and you're done!
* New: Brand new feed customizer. We've completely redesigned feed customization from the ground up, reorganizing the settings to make them easier to find.
* New: Live Feed Preview. You can now see changes you make to your feeds in real time, right in the settings page. Easily preview them on desktop, tablet, and mobile sizes.
* New: Color Scheme option. It's now easier than ever to change colors across your feed without needing to adjust individual color settings. Just set a color scheme to effortlessly change colors across your entire feed.
* New: You can now change the number of columns in your feed across desktop, tablet, and mobile.
* New: Easily import and export feed settings to make it simple to move feeds across sites.

= 2.9.5 =
* Fix: Fixed an issue when reconnecting a personal account.
* Fix: Using showheader="true" in the shortcode would not work if the related setting was disabled on the settings page.
* Fix: Added additional plugin hardening.

= 2.9.4 =
* Tweak: All Instagram data is now encrypted in your WordPress database.
* Tweak: Access Tokens are no longer able to be viewed on the settings page.
* Tweak: Added a maximum caching time of 24 hours.
* Tweak: Added an expiration time to backup caches.
* Tweak: Deauthorizing our app inside your Instagram or Facebook account will now delete all data for that feed on your site.

= 2.9.3.1 =
* Fix: Fixed a problem with the image resizing table check that would cause blank images or non-optimized images to show in the feed.

= 2.9.3 =
* Fix: Fixed duplicate MySQL queries issue when checking for the resized images table.
* Fix: Fixed an issue with the integration with the GDPR Cookie Consent plugin by WebToffee.
* Fix: Removed max-height rule for the sbi_item elements to improve performance.
* Tweak: Improved the reliability of the Instagram account connection process.

= 2.9.2 =
* Tested with WordPress 5.8 update.
* Fix: PHP error "Uncaught Error: array_merge() does not accept unknown named parameters" when visiting the "About" page using PHP 8+.
* Fix: About page was not recognizing that YouTube Feeds Pro was installed and active when prompting the user to activate a YouTube Feed plugin.
* Fix: Fixed an issue with GDPR Cookie Consent by Web Toffee integration.

= 2.9.1 =
* Fix: Fixed several issues with GDPR Cookie Consent by Web Toffee integration.
* Tweak: Changed how connected accounts errors display to prevent temporary, non-actionable errors from triggering a notice.

= 2.9 =
* New: Added support for IGTV posts. When creating an IGTV post, keep the "Post a Preview" setting enabled and the IGTV post will appear in your feed. IGTV posts are only available for connected Instagram business profiles and aren't available if you're using a personal Instagram profile in the plugin.
* Fix: Fixed a PHP error when the HTTP request to refresh an access token resulted in an error.

= 2.8.2 =
* Fix: Changed how access tokens are retrieved to prevent conflict with the "Rank Math SEO" plugin when connecting an account.
* Fix: Updated jQuery methods for compatibility with WordPress 5.7.

= 2.8.1 =
* Fix: Fixed a PHP warning which would display in some situations: "array_diff(): Expected parameter 1 to be an array, string given".
* Fix: Fixed PHP warning "Undefined index: accesstoken" which would display when no primary account was selected.
* Fix: Fixed issue where account errors were not being removed after an account was deleted or reconnected.

= 2.8 =
* New: The locations of the Instagram feeds on your site will now be logged and listed on a single page for easier management. After this feature has been active for awhile, a "Feed Finder" link will appear next to the Feed Type setting on the plugin Settings page which allows you to see a list of all feeds on your site along with their locations.
* New: Local resized images will now include a 150x150 resolution version for each post.
* Tweak: Locally saved image quality set to 80% to increase feed performance without a noticeable visual difference.
* Tweak: Improved how posts are sorted by date when there are more than one user accounts in a feed.
* Fix: Old accounts from Instagram's deprecated, non-functioning API are ignored if still connected.

= 2.7 =
* Tweak: Several performance improvements have been made in this update such as improved caching and fewer database queries when displaying feeds.
* Tweak: The limit of resized, local images created and stored were raised for the overall number and the rate at which they could be created.
* Tweak: Improved how feed errors are handled and reported. API request delays will only apply to feeds encountering errors and will not affect other feeds.
* Tweak: Added a hook for disabling image resizing dynamically with PHP.
* Fix: PHP Warning "required parameter follows optional parameter" that would display when using PHP 8+.
* Fix: The GDPR feature would sometimes report errors when the feature was working fine.

= 2.6.2 =
* Tweak: If the image resizing feature isn't able to work successfully due to an issue, then the GDPR setting will be disabled unless manually enabled to prevent blank images in the feed.
* Fix: In some situations the GDPR setting was incorrectly reporting an error with image resizing.

= 2.6.1 =
* Fix: Fixed PHP error related to a missing file.

= 2.6 =
* New: Integrations with popular GDPR cookie consent solutions added: Cookie Notice by dFactory, GDPR Cookie Consent by WebToffee, Cookiebot by Cybot A/S, Complianz by Really Simple Plugins, and Borlabs Cookie by Borlabs. Visit the Instagram Feed settings page, Customize tab, GDPR section for more information.
* Fix: API error notices would not be removed from the WordPress dashboard after successfully reconnecting an account when the problem was resolved.
* Fix: Fixed PHP Error that would occur when connecting a personal account that would result in an HTTP error.
* Fix: oEmbeds were not always working in much older versions of WordPress.
* Fix: Play and carousel icons would appear very large for small images when the the mobile layout was disabled.

= 2.5.4 =
* Fix: Added more debugging info to the System Info for oEmbeds.
* Fix: Added a workaround for a rare issue where oEmbed access tokens wouldn't save.
* Fix: Carousel posts would not show images when using the "Disable JS Image Loading" setting and image resizing was disabled.

= 2.5.3 =
* Fix: Fixed an issue caused by an unannounced Instagram API change affecting thumbnails in certain video posts which don't have image data available in the API.
* Fix: Added oEmbed account info to the plugin "System Info" to make debugging easier.

= 2.5.2 =
* Fix: Fixed an issue with an Instagram API change causing some images not to display if the image resizing feature was disabled.

= 2.5.1 =
* Tweak: Minor update to footer.php template.
* Tweak: Added support for improved notices on the plugin settings page.
* Fix: Added aria-hidden="true" attribute to loader icon for better accessibility.

= 2.5 =
* New: Added support for Instagram oEmbeds. When you share a link to a Instagram post, WordPress automatically converts it into an embedded Instagram post for you (an "oEmbed"). However, on October 24, 2020, WordPress is discontinuing support for Instagram oEmbeds and so any existing or new embeds will no longer work. Don't worry though, we have your back! This update adds support for Instagram oEmbeds and so, after updating, the Instagram Feed plugin will automatically keep your oEmbeds working. It will also power any new oEmbeds you post going forward.
* New: Install our other free social media plugins right from the Instagram Feed settings menu. Use our Facebook, YouTube, and Twitter plugins to add even more social content to your website and help further engage your viewers and increase your followers.
* Tweak: Changed the names of the CSS and JavaScript files to prevent certain ad blockers from hiding the feed. Original files with original names still included in this update.
* Tweak: Background caching and favoring local images are now the default settings for new installs.
* Fix: Fixed PHP warning too few arguments when using Spanish translation files.

= 2.4.7 =
* Important: Due to recent Instagram changes, private accounts will need to be manually refreshed every 60 days. If you have a private Instagram account, consider making it public to avoid needing to manually reconnect your account.
* New: Added a notice for accounts that are private which lets you know how long until the account needs to be refreshed. You will also be alerted using our admin notice and email notification system if a private account will soon need to be refreshed.

= 2.4.6 =
* New: Added a PHP hook "sbi_clear_page_caches" which allows you to dynamically disable the Instagram Feed code that clears caches created by common page caching plugins.
* New: Added a PHP hook "sbi_resize_url" which allows you to change the default URL of locally stored images. This can be helpful for sites using CDNs.
* Tweak: Added a workaround for the wp_json_encode function used in older versions of WordPress.
* Fix: Compatibility updates for the upcoming WordPress version 5.5 release.

= 2.4.5 =
* Fix: Accounts can be connected without the use of JavaScript.
* Fix: Default URL for connecting an account changed to prevent "Invalid Scope" connection issue.

= 2.4.4 =
* Fix: Workaround added for PHP warning related to an undefined media_url index.
* Fix: Connecting a business account on a mobile device when more than 2 pages where returned was not possible.
* Fix: After connecting an account, the warning that there were no connected accounts would still be visible.
* Fix: URL for retrieving image files from Instagram using a redirect method was changed to prevent an extra, unnecessary redirect.

= 2.4.3 =
* Fix: The opt-in notice to help improve the plugin was not dismissing as expected for some sites due to the admin JavaScript file being cached by the browser.
* Fix: Disabled the "About Us" page plugin installation if using a version of WordPress earlier than 4.6.

= 2.4.2 =
* New: To help us improve the plugin we've added the ability to opt-in to usage tracking so that we can understand what features and settings are being used, and which features matter to you the most. This is disabled by default and will only be enabled if you explictly choose to opt in. If opted in, the plugin will send a report in the background once per week with your plugin settings and basic information about your website environment. No personal or sensitive data is collected (such as email addresses, Instagram account information, license keys, etc). To enable or disable usage tracking at a later date use the setting at: Instagram Feed > Customize > Advanced > Misc > Enable Usage Tracking. See [here](https://smashballoon.com/instagram-feed/usage-tracking/) for more information.
* Tweak: Added additional checks to make sure the HTTP protocol matches when using resized image URLs from the uploads folder.
* Tweak: More information is given when there is an account connection error when connecting an account on the "Configure" page.
* Tweak: Connecting a business account will permanently remove any accounts from the same user that are from the legacy Instagram API that is expiring in June.
* Fix: Added a workaround for sanitize_textarea_field for users using an older version of WordPress.
* Fix: Fixed HTML error causing the manually connect an account feature to not work.
* Fix: Access token and account ID are validated and formatted before trying to manually connect an account to prevent errors.

= 2.4.1 =
* Tweak: User feeds that do not have a user name or ID assigned to them will automatically use the first connected account for the feed.
* Tweak: rel="nofollow" added to all external Instagram Feed links found in the source of the page.
* Fix: API Error #2 was not clearing properly in error reports.

= 2.4 =
* New: Email alerts for critical issues. If there's an issue with an Instagram feed on your website which hasn't been resolved yet then you'll receive an email notification to let you know. This is sent once per week until the issue is resolved. These emails can be disabled by using the following setting: Instagram Feed > Customize > Advanced > Misc > Feed Issue Email Report.
* New: Admin notifications for critical issues. If there is an error with the feed, admins will see notices in the dashboard and on the front-end of the site along with instructions on how to resolve the issue. Front-end admin notifications can be disabled by using the following setting: Instagram Feed > Customize > Advanced > Misc > Disable Admin Error Notice.
* New: Added a WordPress 'Site Health' integration. If there is a critical error with your feeds, it will now be flagged in the site health page.
* New: Added "About Us" page for those who would like to learn more about Smash Balloon and our other products. Go to Instagram Feed -> About Us in the dashboard.
* New: Added support for an Instagram Feed widget. When on the widgets menu, look for the widget "Instagram Feed" to add your feed to a widget area.

= 2.3.1 =
* Fix: Added workaround for personal account connection error and header display issue due to an Instagram API bug. After updating, click "Save Changes" on the Instagram Feed settings page, "Configure" tab to clear your cache.

= 2.3 =
* New: Added an "Instagram Feed" Gutenberg block to use in the block editor, allowing you to easily add a feed to posts and pages.

= 2.2.2 =
* Tested with upcoming WordPress 5.4 update.
* Tweak: Language files updated to account for all new strings.

= 2.2.1 =
* Important: March 2 deadline for migrating to the new Instagram API pushed back to March 31.
* Fix: Some links to Instagram were missing a backslash at the end of the URL causing a 301 redirect.
* Fix: Error saving updated account information caused by emoji in account bio or in account names and MySQL tables that didn't have a UTF8mb4 character set.

= 2.2 =
* Important: On March 31, Instagram will stop supporting its old API which will disrupt feeds created from personal connected accounts. If you are using a personal account, you will need to reconnect the account on the Instagram Feed Settings page. Please [see here](https://smashballoon.com/instagram-api-changes-march-2-2020/) for more information.
* New: Support added for the new Instagram Basic Display API.
* New: Added PHP hooks 'sbi_before_feed' and 'sbi_after_feed' for displaying HTML before and after the main Instagram feed HTML.
* New: Added settings for adding a custom header avatar and custom header bio text. Go to the "Customize" tab "Header" area to set these or use customavatar="AVATAR URL" or custombio="BIO TEXT" in the shortcode.
* Tweak: Warnings and messages displaying on the front end of sites now display at the top of the feed.
* Tweak: Header template changed to accommodate missing data if connected as a personal account to the new API.
* Tweak: Changes to feed.php, header.php, and item.php templates.
* Tweak: Added CSS to prevent some themes from adding box shadows and bottom border when hovering over the header.
* Tweak: Added code to clear page caching from Litespeed cache when clearing page caches with the plugin.
* Tweak: Header and follow button will still be displayed when number of posts is set to 0.
* Fix: Emoji in the first few characters of a caption would cause the main post image to switch to an emoji when loading more.
* Fix: Pagination for "tagged" feeds not working for certain accounts.

= 2.1.5 =
* New: Added aria-label attributes to SVGs for improved accessibility.
* Tweak: Changed screen reader and alt text to be more SEO friendly (change made to item.php template).
* Tweak: Added PHP hooks to use custom alt and screen reader text.
* Fix: Image resolution setting option "Medium" dimensions changed from 306x306 to 320x320.
* Fix: Screen reader text would be visible if text was right aligned.
* Fix: Incorrect image resolution would be used when setting the image resolution to something other than auto.

= 2.1.4 =
* Tweak: If sb_instagram_js_options is not defined, a default object is set.
* Tweak: Added a text link in the settings page footer to our new free [YouTube plugin](https://wordpress.org/plugins/feeds-for-youtube/)
* Fix: Local images not being used when available in certain circumstances.

= 2.1.3 =
* New: Added filter "sbi_settings_pages_capability" to change what permission is needed to access settings pages.
* Tweak: Updated language files for version 2.0+.
* Tweak: Better error messages for no posts being found and API request delays.
* Tweak: If "Favor Local Images" setting is in use, a 640px resolution image will be created for images coming from a personal account.
* Tweak: Better error recovery when image file not found when viewing the feed.
* Tweak: Button and input field styling updated to look better with WordPress 5.3.
* Fix: Accounts that were connected prior to version 1.12 would not show the follow button if the header was not also displayed. Visit the "Configure" tab to have the account automatically updated.
* Fix: MySQL error when retrieving resized images. Thanks [the-louie](https://github.com/the-louie)!
* Fix: When using the new Twenty Twenty theme, Instagram icon in "follow" button displaying as block and causing the button text to appear on a new line.

= 2.1.2 =
* New: Added setting "API request size" on the "Customize" tab to allow requesting of more posts than are in the feed. Setting this to a high number will prevent no posts being found if you often post IG TV posts and use a personal account.
* Tweak: Removed width and height attributes from the image element in the feed to prevent notices about serving scaled images in optimization tools.

= 2.1.1 =
* New: Added ability to enqueue the CSS file through the shortcode. This loads the file in the footer of the site, and only on pages that include a feed. Enable on the "Customize" tab.
* Tweak: Resized images can be used in the page source code when "Disable js image loading" setting is enabled.
* Fix: HTML for header would still be visible in the source of the page when removing the header using showheader=false in the shortcode.

= 2.1 =
* New: Added the ability to overwrite default templates in your theme. View [this article](https://smashballoon.com/guide-to-creating-custom-templates/) for more information.
* New: Added several PHP hooks for modifying feeds settings and functionality.
* Fix: Using the "Load Initial Posts with AJAX" setting would cause images to not resize with the browser window.
* Fix: Added back language files for translations.
* Fix: Changing the image resolution setting would not change the image size.
* Fix: Follow button would not show if there was no connected account.
* Fix: Deleting any connected account will delete any connected accounts that have errors in the data that was saved for them.

= 2.0.2 =
* Fix: HTML for header would still be visible in the source of the page when removing the header using showheader=false in the shortcode
* Fix: CSS added to prevent layout issues when adding the feed to a "text" widget for certain themes

= 2.0.1 =
* Tweak: Force cache of major caching plugins to clear when updating plugin to avoid issues with previous CSS/JavaScript files being cached
* Tweak: Added version number to the end of JavaScript and CSS files to clear browser caches that are causing errors
* Fix: Added back filter to allow using shortcode in a custom HTML widget
* Fix: Added back settings to display bio information in header and change header size which were mistakenly removed in the last update
* Fix: Fixed a PHP notice which might display under certain circumstances

= 2.0 =
* **MAJOR UDPATE**
* New: We've rebuilt the plugin from the ground up with a focus on performance and reliability. Your feeds are now loaded from the server using PHP removing the reliance on AJAX.
* New: Local copies of images are now automatically stored on your server and used in your feed. You can disable this feature in the "Advanced" section of the "Customize" tab. Use the "Favor Local Images" setting on the "Customize" tab, "Advanced" sub-tab to have the plugin use local images whenever available, thus removing reliance on the Instagram CDN.
* New: You can now set the plugin to check for new Instagram posts in the background rather than when the page loads by using the new "Background caching" option which utilizes the WordPress "cron" feature. Enable this using the "Check for new posts" setting on the "Configure" tab.
* New: If you have a business account for Instagram, you can now connect to the new Instagram API. You can continue to use your connected personal account and do not need to connect a business account.

= 1.12.2 =
* Fix: Fixed error from Instagram when connecting a personal account.

= 1.12.1 =
* Tweak: If an image in a post fails to load then the plugin attempts to load it from another image source

= 1.12 =
* Fix: Includes fixes for some security vulnerabilities. Thanks to Julio Potier of [SecuPress](https://secupress.me/) for reporting the issues.
* Fix: Fixed an issue caused by a bug in the Instagram API which was preventing some Instagram accounts from being able to be connected. If you experienced an issue connecting an Instagram account then please try again after updating.
* Fix: Quotes represented by "%20" in Instagram data were causing a JSON parsing error.
* Tweak: Data for the feed is now cached outside of the admin-ajax.php calls.

= 1.11.3 =
* Fix: Escaped single quotes causing a JSON parse error under certain circumstances.
* Fix: Translatable code errors in the admin area causing some text to not be translatable.

= 1.11.2 =
* Fix: Unable to connect new accounts due to changes with Instagram's API. Remote requests to connect accounts are now made server-side.

= 1.11.1 =
* Fix: Feed would not load from a cache created with an older version of the plugin
* Fix: Fixed PHP warning trying to count string length of an array

= 1.11 =
* New: Added capability "manage_instagram_feed_options" to support customizations that will allow users/roles other than the administrator to access Instagram Feed settings pages.
* Fix: rel="noopener" added to all links that contain target="blank"
* Fix: HTTPS used in xlmns attribute for SVGs
* Fix: Fixed issues with strings in the admin area being translatable
* Fix: Fixed a potential security vulnerability. Thanks to [Martin Verreault](https://egyde.ca/) for reporting the issue.

= 1.10.2 =
* Confirmed compatibility with the upcoming WordPress 5.0 "Gutenberg" update
* Fix: Fixed an issue caused by some themes which affected the formatting of the 'Load More' and 'Follow' buttons
* Fix: Fixed an occasional formatting issue with error messages due to no line-height being set
* Fix: Minor admin UI fixes
* Tweak: Removed mention of some Pro features which will be deprecated due to upcoming Instagram API changes

= 1.10.1 =
* Tweak: Automatic image resolution detection setting now works better with wide images. Resizing the browser will now automatically raise the image resolution if needed.
* Fix: Fixed an issue where the Load More button would disappear if all posts for a feed were cached.

= 1.10 =
* New: We've made improvements to the way photos are loaded into the feed, adding a smooth transition to display photos subtly rather than suddenly.
* New: More header sizes; you can now choose from three sizes: small, medium, and large. Change this on the "Customize" tab.
* Fix: Using a percent for the image padding was causing the height of images to be too tall
* Fix: Removed a PHP notice when the Instagram API was blocked by the web host

= 1.9.1 =
* Fix: Captions missing as "alt" text for Instagram images.
* Fix: System information was not formatting connected Instagram accounts and user ids correctly
* Fix: "Unauthorized redirect URL" error occurring while trying to connect a new Instagram account due to recent changes from Instagram
* Fix: Using a percent for the image padding was causing the height of Instagram images to be to tall

= 1.9 =
* New: Retrieving Access Tokens and connecting multiple Instagram accounts is now easier using our improved interface for managing account information. While on the Configure tab, click on the big blue button to connect an account, or use the "Manually Connect an Account" option to connect one using an existing Access Token. Once an account is connected, you can use the associated buttons to either add it to your primary Instagram User feed or to a different Instagram feed on your site using the `user` shortcode option, eg: `user=smashballoon`.
* Tweak: Disabled auto load in the database for backup caches
* Fix: Fixed an occasional issue with the Instagram login flow which would result in an "Unauthorized redirect URL" error

= 1.8.3 =
* Fix: SVG icons caused some display problems in IE 11
* Fix: Removed support for using usernames in the Instagram User ID setting due to recent API changes. Will now default to the Instagram User ID attached to the Access Token.
* Fix: Backup feed not always being used when Access Tokens expire
* Fix: Instagram Access Tokens may have been incorrectly saved as invalid under certain circumstances

= 1.8.2 =
* Tweak: Setting "Cache Error API Recheck" enabled by default for new Instagram Feed installs
* Fix: Page caches created with the WP Rocket plugin will be cleared when the Instagram Feed settings are updated or the cache is forced to clear
* Fix: Fixed a rare issue where feeds were displaying "Looking for cache that doesn't exist" when page caching was not being used

= 1.8.1 =
* Fix: Fixed issue where Instagram feeds were displaying "Looking for cache that doesn't exist" when page caching was not being used
* Fix: Font method setting not working when "Are you using an ajax theme?" setting is enabled

= 1.8 =
* Important: Due to [recent changes](https://smashballoon.com/instagram-api-changes-april-4-2018/?utm_campaign=instagram-free-readme&utm_source=changelog&utm_medium=apichanges) in the Instagram API it is no longer possible to display photos from other Instagram accounts which are not your own. You can only display the user feed of the account which is associated with your Access Token.
* New: Added an Access Token shortcode option and support for multiple Instagram Access Tokens. If you own multiple Instagram accounts then you can now use multiple Access Tokens in order to display user feeds from each Instagram account, either in separate feeds, or in the same feed. Just use the `accesstoken` shortcode option. See [this FAQ](https://smashballoon.com/display-multiple-instagram-feeds/#multiple-user-feeds) for more information on displaying multiple User feeds.

= 1.7 =
* New: Added feed caching to limit the number of Instagram API requests. Use the setting on the "Configure" tab "Check for new posts every" to set how long feed data will be cached before refreshing.
* New: Added backup caching for all feeds. If the Instagram feed is unable to display then a backup feed will be shown to visitors if one is available. The backup cache can be disabled or cleared by using the following setting: `Customize > Misc > Enable Backup Caching`.
* New: Icons are now generated as SVGs for a sharper look and more semantic markup
* New: Instagram carousel posts include an icon to indicate that they are carousel posts
* Tweak: Using the "sort posts by random" feature will include the most recent 33 posts instead of just the posts shown in the Instagram feed
* Fix: links back to instagram.com will use the "www" prefix

= 1.6.2 =
* Fix: Fixed a rare issue where the Load More button wouldn't be displayed after the last update if the Instagram account didn't have many posts

= 1.6.1 =
* Fix: Fixed Font Awesome 5.0 causing Instagram icon to appear as a question mark with a circle
* Fix: Fixed inline padding style for sbi_images element causing validation error when set to "0" or blank space
* Fix: Added a workaround for an Instagram API bug which caused some feeds to show fewer posts than expected

= 1.6 =
* New: Loading icon appears when waiting for new posts after clicking the "Load More..." button
* New: Added translation files for Dutch (nl_NL)
* Fix: Fixed a potential security vulnerability. Thanks to [Magnus Stubman](http://dumpco.re/) for reporting the issue.

= 1.5.1 =
* New: The plugin is now compatible with the [WPML plugin](https://wpml.org/) allowing you to use multiple translations for your feeds on your multi-language sites
* New: Added translation files for Danish (da_DK), Finnish (fi_FL), Japanese (ja_JP), Norwegian (nn_NO), Portuguese (pt_PT), and Swedish (sv_SE) to translate the "Load More" and "Follow on Instagram" text

= 1.5 =
* New: Improved tool for retrieving Instagram Access Tokens
* New: Added an option to show/hide Instagram bio text in feed header
* New: Feeds that include IDs from "private" Instagram accounts will now ignore the private data and display a message to logged-in site admins which indicates that one of the Instagram accounts is private
* New: Feeds without any Instagram posts yet will display a message informing logged-in admins to make a post on Instagram in order to view the feed
* New: Added translation files for French (fr_FR), German (de_DE), English (en_EN), Spanish (es_ES), Italian (it_IT), and Russian (ru_RU) to translate "Load More..." and "Follow on Instagram"
* Tweak: Optimized several images used in the Instagram feed including loader.png
* Tweak: Font Awesome stylesheet handle has been renamed so it will only be loaded once if our Custom Facebook Feed plugin is also active
* Fix: Updated the Font Awesome icon font to the latest version: 4.7.0
* Fix: Padding removed from "Load More" button if no buttons are being used in the Instagram feed
* Fix: All links in the feed are now https
* Fix: Fixed JavaScript errors which were being caused if the Instagram Access Token had expired or the user ID was incorrect, private, or had no Instagram posts

= 1.4.9 =
* Compatible with WordPress 4.8

= 1.4.8 =
* Tweak: Updated plugin links for new WordPress.org repo
* Fix: Minor bug fixes

= 1.4.7 =
* Fix: Fixed a security vulnerabiliy
* Tested with upcoming WordPress 4.6 update

= 1.4.6.2 =
* Fix: Removed a comment from the plugin's JavaScript file which was causing an issue with some optimization plugins, such as Autoptimize.

= 1.4.6.1 =
* Fix: Fixed an issue with the Instagram image URLs which was resulting in inconsistent url references in some feeds

= 1.4.6 =
* **IMPORTANT: Due to the recent Instagram API changes, in order for the Instagram Feed plugin to continue working after June 1st you must obtain a new Access Token by using the Instagram button on the plugin's Settings page.** This is true even if you recently already obtained a new token. Apologies for any inconvenience.

= 1.4.5 =
* New: When you click on the name of a setting on the plugin's Settings pages it now displays the shortcode option for that setting, making it easier to find the option that you need
* New: Added a setting to disable the Font Awesome icon font if needed. This can be found under the Misc tab at the bottom of the Customize page.
* Tweak: Updated the Instagram icon to match their new branding
* Tweak: Added a help link next to the Instagram login button in case there's an issue using it
* Fix: Updated the Font Awesome icon font to the latest version: 4.6.3

= 1.4.4 =
* Fix: Fixed an issue caused by a specific type of emoji which would cause the feed to break when used in a post
* Tweak: Added links to our other **free** plugins to the bottom of the admin pages: [The Custom Facebook Feed](https://wordpress.org/plugins/custom-facebook-feed/) and [Custom Twitter Feeds](https://wordpress.org/plugins/custom-twitter-feeds/)

= 1.4.3 =
* Fix: Important notice added in the last update is now only visible to admins

= 1.4.2 =
* New: Compatible with Instagram's new API changes effective June 1st
* New: Added video icons to Instagram posts in the feed which contain videos
* New: Added a setting to allow you to use a fixed pixel width for the feed on desktop but switch to a 100% width responsive layout on mobile
* Tweak: Added a width and height attribute to the images to help improve Google PageSpeed score
* Tweak: A few minor UI tweaks on the settings pages
* Fix: Minified CSS and JS files

= 1.3.11 =
* Fix: Fixed a bug which was causing the height of the Instagram photos to be shorter than they should have been in some themes
* Fix: Fixed an issue where when an Instagram feed was initially hidden (in a tab, for example) then the Instagram photo resolution was defaulting to 'thumbnail'

= 1.3.10 =
* Fix: Fixed an issue which was setting the visibility of some Instagram photos to be hidden in certain browsers
* Fix: The new square photo cropping is no longer being applied to Instagram feeds displaying images at less than 150px wide as the images from Instagram at this size are already square cropped
* Fix: Fixed a JavaScript error in Internet Explorer 8 caused by the 'addEventListener' function not being supported

= 1.3.9 =
* Fix: Fixed an issue where Instagram photos wouldn't appear in the Instagram feed if it was initially being hidden inside of a tab or some other element
* Fix: Fixed an issue where the new Instagram image cropping fuction was failing to run on some sites and causing the Instagram images to appear as blank

= 1.3.8 =
* Fix: If you have uploaded an Instagram photo in portrait or landscape then the plugin will now display the square cropped version of the photo in your Instagram feed

= 1.3.7 =
* Fix: Fixed an issue with double quotes in photo captions (used in the Instagram photo alt tags) which caused a formatting issue

= 1.3.6 =
* Fix: Fixed an issue introduced in version 1.3.4 which was causing theme settings to not be applied in some themes

= 1.3.5 =
* Fix: Reverted the 'prop' function introduced in the last update back to 'attr' as prop isn't supported in older versions of jQuery
* Fix: Removed the image load function as it was causing Instagram images not to be displayed for some users

= 1.3.4 =
* Fix: Used the Instagram photo caption to add a more descriptive alt tag to the Instagram photos
* Fix: Instagram photos are now only displayed once they're fully loaded
* Fix: Added a stricter CSS implementation for some elements to prevent styles being overridden by themes
* Fix: Added CSS opacity property to Instagram images to prevent issues with lazy loading in some themes
* Fix: Removed a line of code which was disabling WordPress Debug/Error Reporting. If needed, this can be disabled again by using the setting at the bottom of the plugin's 'Customize' settings page.
* Fix: Made some JavaScript improvements to the core Instagram Feed plugin code

= 1.3.3 =
* Fix: Fixed an issue with the 'Load more' button not always showing when displaying Instagram photos from multiple Instagram User IDs
* Fix: Moved the initiating sbi_init function outside of the jQuery ready function so that it can be called externally if needed by Ajax powered themes/plugins

= 1.3.2 =
* New: Added an option to disable the Instagram Feed mobile layout
* New: Added an setting which allows you to use the Instagram Feed plugin with an Ajax powered theme
* New: Added a 'class' shortcode option which allows you to add a CSS to class to each individual Instagram feed: `[instagram-feed class=feedOne]`
* New: Added a Support tab which contains System Info to help with troubleshooting
* New: Added friendly error messages which display only to WordPress admins
* New: Added validation to the Instagram User ID field to prevent usernames being entered instead of IDs
* Tweak: Made the Instagram Access Token field slightly wider to prevent tokens being copy and pasted incorrectly
* Fix: Fixed a JavaScript bug which caused the feed not to load photos correctly in IE8

= 1.3.1 =
* Fix: Fixed an issue with the Instagram icon not appearing in the 'Follow on Instagram' button or in the Instagram Feed header
* Fix: Addressed a few CSS issues which were causing some minor formatting issues in the Instagram Feed on certain themes

= 1.3 =
* New: You can now display Instagram photos from multiple Instagram User IDs. Simply separate your Instagram IDs by commas.
* New: Added an optional header to the Instagram feed which contains your Instagram profile picture, Instagram username and Instagram bio. You can activate this on the Instagram Feed Customize page.
* New: The Instagram Feed plugin now includes an 'Auto-detect' option for the Instagram Image Resolution setting which will automatically set the correct Instagram image resolution based on the size of your Instagram feed.
* New: Added an optional 'Follow on Instagram' button which can be displayed at the bottom of your Instagram feed. You can activate this on the Instagram Feed Customize page.
* New: Added the ability to use your own custom text for the 'Load More' button
* New: Added a loader icon to indicate that the Instagram photos are loading
* New: Added a unique ID to each Instagram photo so that they can be targeted individually via CSS
* Tweak: Added a subtle fade effect to the Instagram photos when hovering over them
* Tweak: Improved the responsive layout behavior of the Instagram feed
* Tweak: Improved the documentation within the Instagram Feed plugin settings pages
* Tweak: Included a link to [step-by-step setup directions](http//:smashballoon.com/instagram-feed/free/?utm_campaign=instagram-free-readme&utm_source=changelog&utm_medium=changelog 'Instagram feed setup directions') for the plugin
* Fix: Fixed an issue with the feed not clearing other widgets correctly

= 1.2.3 =
* Fix: Replaced the 'on' function with the 'click' function to increase compatibility with themes using older versions of jQuery

= 1.2.2 =
* Tweak: Added an initialize function to the Instagram Feed plugin
* Fix: Fixed an occasional issue with the 'Sort Photos By' option being undefined

= 1.2.1 =
* Fix: Fixed a minor issue with the Custom JavaScript being run before the Instagram photos are loaded
* Fix: Removed stray PHP notices
* Fix: Changed the double quotes to single quotes on the 'data-options' attribute

= 1.2 =
* New: Added Custom CSS and Custom JavaScript sections which allow you to add your own custom CSS and JavaScript to the Instagram Feed plugin
* New: Added an option to display your Instagram photos in random order
* New: A new tabbed layout has been implemented on the Instagram Feed plugin's settings pages
* New: Added an option to preserve your Instagram Feed settings when uninstalling the plugin
* New: Added a [Pro version](http://smashballoon.com/instagram-feed/?utm_campaign=instagram-free-readme&utm_source=changelog&utm_medium=changelog 'Instagram Feed Pro') of the Instagram Feed plugin which allows you to display Instagram photos by hashtag, display Instagram captions, view Instagram photos in a pop-up lightbox, show the number of Instagram likes & comments and more
* Tweak: The 'Load More' button now automatically hides if there are no more Instagram photos to load
* Tweak: Added a small gap to the top of the 'Load More' button
* Tweak: Added a icon to the Instagram Feed menu item

= 1.1.6 =
* Fix: A maximum width is now only applied to the Instagram feed when the Instagram photos are displayed in one column

= 1.1.5 =
* Fix: Added a line of code which enables shortcodes to be used in widgets for themes which don't have it enabled

= 1.1.4 =
* Fix: Fixed an issue with the Instagram Access Token and Instagram User ID retrieval functionality in certain web browsers

= 1.1.3 =
* Fix: Fixed an issue with the maximum Instagram image width
* Fix: Corrected a typo in the Instagram Feed Shortcode Options table

= 1.1.1 =
* Pre-tested for the upcoming WordPress 4.0 update
* Fix: Fixed an uncommon issue related to the output of the Instagram content

= 1.1 =
* New: Added an option to set the number of Instagram photos to be initially displayed
* New: Added an option to show or hide the 'Load More' button
* New: Added 'Step 3' to the Instagram Feed Settings page explaining how to display your feed using the [instagram-feed] shortcode
* New: Added a full list of all available Instagram Feed shortcode options to help you if customizing multiple Instagram feeds

= 1.0.2 =
* Fix: Fixed an issue with the Instagram login URL on the plugin's Instagram Feed Settings page

= 1.0.1 =
* Fix: Fixed an issue with the Instagram Feed 'Load More' button opening an empty browser window in Firefox

= 1.0 =
* Launched the Instagram Feed plugin!
