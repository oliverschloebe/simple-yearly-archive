=== Simple Yearly Archive ===
Contributors: Alphawolf
Donate link: http://www.schloebe.de/donate/
Tags: gettext, archive, yearly, polyglot, shortcode, exclude, category, WPML, language, localization, multilingual
Requires at least: 3.0
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple Yearly Archive is a rather neat and simple Wordpress plugin that allows you to display your archives in a year-based list.

== Description ==

Simple Yearly Archive is a rather neat and simple Wordpress plugin that allows you to **display your archives in a year-based list**. It works mostly like the usual WP archive, but displays all published posts seperated by their year of publication. That said, it’s also possible to restrict the output to certain categories, and much more.

**Allows use of shortcode since version 1.1.0**

**Included languages:**

* English
* German (de_DE) (Thanks to me ;-))
* Italian (it_IT) (Thanks for contributing italian language goes to [Gianni Diurno](http://gidibao.net))
* Russian (ru_RU) (Thanks for contributing russian language goes to [Dimitry German](http://grugl.me))
* Belorussian (by_BY) (Thanks for contributing belorussian language goes to [Marcis Gasuns](http://www.fatcow.com))
* Uzbek (uz_UZ) (Thanks for contributing uzbek language goes to [Alexandra Bolshova](http://www.comfi.com))
* French (fr_FR) (Thanks for contributing french language goes to [Jean-Michel Meyer](http://www.li-an.fr/blog))
* Chinese (zh_CN) (Thanks for contributing chinese language goes to [Mariana Ma](http://marianama.net))
* Japanese (ja) (Thanks for contributing japanese language goes to [Chestnut](http://staff.blog.bng.net))

[Click here for a demo](http://www.schloebe.de/archiv/ "Click here for a demo")

[Developer on Google+](https://plus.google.com/118074611982254715031 "Developer on Google+") | [Developer on Twitter](http://twitter.com/wpseek "Developer on Twitter")

[Become A Patron, Support The Developer.](http://www.patreon.com/oliver_schloebe "Become A Patron, Support The Developer.")

**Looking for more WordPress plugins? Visit [www.schloebe.de/portfolio/](http://www.schloebe.de/portfolio/)**

== Frequently Asked Questions ==

Configuration? Parameters? [Head over here](http://www.schloebe.de/wordpress/simple-yearly-archive-plugin/ "Head over here")

== Installation ==

1. Download the plugin and unzip it.
1. Upload the folder simple-yearly-archive/ to your /wp-content/plugins/ folder.
1. Activate the plugin from your Wordpress admin panel.
1. Installation finished.

== Changelog ==

= 1.6.2.5 =
* Added a CSS class post id to the post links so people can do more custom things with CSS or javascript

= 1.6.2.2 =
* Hide comments count for posts with comments closed

= 1.6.2.1 =
* Fixed a bug that did not reverse post order if "Reverse order" was selected

= 1.6.2 =
* Improved WPML support

= 1.6.1 =
* Initial WPML support (thanks to Emilie from bornbilingue.com for the help!)

= 1.6.0 =
* Support for post types

= 1.5.0 =
* Significant changes that result in a lot less memory consumption on blogs with 1000+ posts
* Code cleanup

= 1.4.3.3 =
* Fixed another PHP notice. Didn't have enough coffee.

= 1.4.3.2 =
* Fixed a PHP notice when using exclude/include parameter (thanks Lea!)

= 1.4.3.1 =
* Fixed an issue that caused to load unsecure resources on SSL enabled sites

= 1.4.3 =
* Fixed a bug that caused listing "auto draft" posts

= 1.4.2 =
* Fixed a bug with the anchored years overview at the top

= 1.4.1 =
* Added a date wrapper span so you can hide the date via CSS

= 1.4 =
* New option "Collapsible years?" added

= 1.3.3 =
* Readme.txt updated to be more compliant with the readme.txt standard
* Moved screenshots off the package to the assets/ folder

= 1.3.2 =
* Maintenance update #2 ( Dominik :) )

= 1.3.1 =
* Maintenance update

= 1.3.0 =
* Option to reverse the order of the year/posts list output

= 1.2.9 =
* Maintenance update

= 1.2.8 =
* A few fixes that resulted from the previous versions

= 1.2.7 =
* Character encoding for new date format string fixed
* Fixed a bug that occured when "Anchored overview at the top" was checked while "Linked years" was unchecked (Thanks Kroom!)
* Added an admin notice when someone didn't already switch to the new date format string

= 1.2.6 =
* IMPORTANT: Date format changed to reflect localized date strings. Please update your date string in the plugin's settings!

= 1.2.5 =
* Optional anchored links to each year at the top

= 1.2.4 =
* Archive links now working again

= 1.2.3 =
* Minor performance improvements
* Min version set to 2.3

= 1.2.2 =
* Private posts are now prefixed with "Private" in order to follow WordPress standards (Thanks Andrei Borota!)

= 1.2.1 =
* Fixed a warning message

= 1.2 =
* Date format can be set in the shortcode like `[SimpleYearlyArchive ... dateformat="d/m"]`

= 1.1.50 =
* Changed post authot output from user_login to display_name

= 1.1.40 =
* Added japanese localization (Thanks to [Chestnut](http://staff.blog.bng.net))!)

= 1.1.31 =
* Fixed an issue on server configurations having PHP short tags disabled

= 1.1.30 =
* Fixed an issue that threw an 'Missing argument 3' warning in PHP
* Added `apply_filters('sya_archive_output', $output)` filter hook so you can alter the HTML output before it's being returned
* Added french localization (Thanks to [Jean-Michel Meyer](http://www.li-an.fr/blog)!)

= 1.1.20 =
* Added the `include` parameter allowing to include categories instead of only excluding them
* code cleanup

= 1.1.10 =
* Minor Code Changes

= 1.1.9 =
* Fixed issue on displaying post count for each year when there are excluded categories

= 1.1.8 =
* Some options page changes
* Improved compatibility with WP 2.7
* Code improvements

= 1.1.7 =
* Some options page changes
* Improved compatibility with WP 2.7
* Code improvements

= 1.1.5 =
* Exclude code changed that works like the WordPress method now (which makes this archive plugin unique ;-) )
* Private and password-protected posts now show up depending on user capibilities

= 1.1.2 =
* Markup is now html strict compatible

= 1.1.1 =
* Option added to display post author after each post
* Added italian localization (Thanks to Gianni Diurno!)

= 1.1.0 =
* Improved compatibility with WordPress 2.6
* Added shortcode compatibility
* Minor html changes

= 1.0.1 =
* Improved compatibility with WordPress 2.2.x
* Fixed issue that occasionally occured with the inline function

= 1.0 =
* Option added to display categories after each post

= 0.98 =
* Fixed error, that prevented backend localization

= 0.97 =
* Simple Yearly Archive options page has WP 2.5 style (if used in WP 2.5+) (see screenshots)
* Performance improvements

= 0.96 =
* Year headings do not show if there are no posts in that year (Thanks to Stephanie C. Leary!)

= 0.95 =
* Option "Show optional Excerpt" added
* Option "Max. chars of Excerpt" added
* Option "Indentation of Excerpt" added

= 0.91 =
* WP 2.3 compatibility on exclude cateogries
* minor language fixes
* minor fixes and code optimisation

= 0.9 =
* Added a bunch of new options

= 0.82 =
* gettext-ready, plugins like language-switcher or polyglot are supported now

= 0.81 =
* Now compatible with the Admin Drop Down Menu Plugin, which caused to not to be able to access the options page

= 0.8 =
* New options page in Wordpress administration
* plugin can now be called from within a page/post

= 0.7 =
* Now it’s possible to show posts from the given date of year only
* Little fix in get_year_link

= 0.6 =
* 2 parameters added: Display the current year’s posts or the past year’s posts only
* Posts remain sorted in case of changing the post’s timestamp

= 0.5 =
* The plugin has been released

== Screenshots ==

1. The options page