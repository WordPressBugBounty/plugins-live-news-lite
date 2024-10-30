=== Live News - Responsive News Ticker ===
Contributors: DAEXT
Tags: news ticker, ticker, news, live, breaking news
Donate link: https://daext.com
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.09
License: GPLv3

Generate a news ticker to communicate the latest updates, including financial news, weather warnings, election results, sports scores, and more.

== Description ==
The Live News Lite plugin generates a fixed news ticker that you can use to communicate the latest news, financial news, weather warnings, election results, sports results, etc.

By default, the news ticker includes a **Featured News** section in red and a **Sliding News** in black, and it's similar to the ones used by networks like Fox News, CNN, Sky News, etc.

In terms of content, the news should be manually added by the website administrator or by users that belong to other configured roles.

### Pro Version

The [Pro Version](https://daext.com/live-news/) of this plugin, allows you to:

* Automatically generate news based on the posts
* Automatically generate the news based on a specified RSS feed (E.g., Your own RSS feed, the RSS feed of a tv channel, the RSS feed of a radio station.)
* Control the speed and the delay of the sliding news with advanced options of the news ticker

#### Features

This plugin is highly customizable and comes with 46 options per news ticker, 4 options per featured news, 9 options per sliding news, and 4 general options.

#### Customizable Style

All the elements displayed in the news ticker are customizable in terms of colors and typography. The images used to represent the open and close buttons and the clock background are replaceable with your custom images.

#### Links in the News Ticker

You can optionally apply links to the news and open them in the same tab or new tabs based on your selections.

#### Applicable Globally or Only on Specific URLs

The news ticker can be applied to all the pages of your website or only to specific URLs. In case of a setup with news tickers assigned to different URLs, you can create an unlimited number of news tickers.

#### Sliding News Images

You can place small images before and after the text of the sliding news. Use this feature to display the news provider's logo, represent financial trends, categorize different types of communications, and more.

#### Clock

The news ticker has an optional clock that displays the time in a custom format defined with [Moment.js](http://momentjs.com) token.

In the plugin options, you can also decide if you want to display the server time, the user time, if you want to apply a time offset, and the frequency of the time updates.

#### Mobile Device Detection

The plugin uses the Mobile Detect Js library to detect the device of the user. The resulting value is used to display or hide the news ticker or specific news ticker elements based on the device type. This behavior is defined by the administrator in the news ticker settings.

#### Cached Cycled

The news ticker updates the news with AJAX requests at the end of each news cycle. With the **Cached Cycled** option, you can define the number of cycles per AJAX request.

#### WordPress Transients API

You can optionally store the news ticker data in a WordPress transient and limit the number of queries used to retrieve the news.

#### Support for RTL Layouts

We have included the **Enable RTL Layout** to allow the use of the plugin in RTL websites.

#### Suitable for Digital Signage Systems

You can use the plugin locally in a browser-based digital signage system.

#### Advanced Options

Advanced options to further customize the news ticker are also available:

* The **Sliding News Margin** option to define the margin between the sliding news
* The **Sliding News Padding** option determines the padding on the left and the right of each sliding news. This option is also helpful to control the distance between the sliding news text and the optional images.
* Control the opacity of the news ticker with the **Featured News Background Color Opacity** and the **Sliding News Background Color Opacity** options
* And more.

### Documentation

For more information on how to implement this plugin, please see the [plugin documentation](https://daext.com/doc/live-news/) published on our website.

### Credits

This plugin makes use of the following resources:

* [Mobile Detect JS](https://github.com/hgoebl/mobile-detect.js) licensed under the [MIT License](http://www.opensource.org/licenses/mit-license.php)
* [Moment.js](http://momentjs.com) licensed under the [MIT License](http://www.opensource.org/licenses/mit-license.php)
* [Chosen](https://github.com/harvesthq/chosen) licensed under the [MIT License](http://www.opensource.org/licenses/mit-license.php)

== Installation ==
= Installation (Single Site) =

With this procedure you will be able to install the Live News Lite plugin on your WordPress website:

1. Visit the **Plugins -> Add New** menu
2. Click on the **Upload Plugin** button and select the zip file you just downloaded
3. Click on **Install Now**
4. Click on **Activate Plugin**

= Installation (Multisite) =

This plugin supports both a **Network Activation** (the plugin will be activated on all the sites of your WordPress Network) and a **Single Site Activation** in a **WordPress Network** environment (your plugin will be activated on a single site of the network).

With this procedure you will be able to perform a **Network Activation**:

1. Visit the **Plugins -> Add New** menu
2. Click on the **Upload Plugin** button and select the zip file you just downloaded
3. Click on **Install Now**
4. Click on **Network Activate**

With this procedure you will be able to perform a **Single Site Activation** in a **WordPress Network** environment:

1. Visit the specific site of the **WordPress Network** where you want to install the plugin
2. Visit the **Plugins** menu
3. Click on the **Activate** button (just below the name of the plugin)

== Changelog ==

= 1.09 =

*October 30, 2024*

* Major back-end UI update.
* Refactoring.
* Tools menu added.
* Maintenance menu added.
* Two new plugin options have been added.

= 1.08 =

*April 8, 2024*

* Fixed a bug (started with WordPress version 6.5) that prevented the creation of the plugin database tables and the initialization of the plugin options during the plugin activation.

= 1.07 =

*October 24, 2023*

* Nonce fields have been added to the "Tickers", "Featured News", and "Sliding News" menus.
* Fixed PHP warnings.
* General refactoring. The phpcs "WordPress-Core" ruleset has been partially applied to the plugin code.

= 1.06 =

*August 29, 2023*

* Minor back-end CSS fixes.
* Menu footer links added.

= 1.05 =

*August 03, 2022*

* The "Cached Cycles" ticker option is now properly used in the front-end scripts. This change solves a bug that prevented the news from being updated at the end of the cycles.
* The translation functions text domain now matches the plugin slug.
* The "Export to Pro" menu has been added.
* The links to the Pro version have been updated with the new Pro version page.
* Minor back-end improvements.

= 1.03 =

*March 19, 2022*

* Plugin renamed to "Live News".
* Improved validation and escaping in the back-end menus.

= 1.02 =

*May 6, 2021*

* Initial release.

== Screenshots ==
1. News Tickers menu
2. Featured News menu
3. Sliding News menu
4. Options menu
5. News ticker in the front-end