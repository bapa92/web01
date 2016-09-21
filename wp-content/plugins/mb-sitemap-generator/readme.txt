=== Sitemap Generator ===
Contributors: mbsec
Tags: sitemap, site map, sitemap.xml, xml sitemap, image sitemap, xml image sitemap, image, images, video sitemap, xml video sitemap, video, videos, image seo, video seo, seo, google, youtube, yahoo, bing, baidu, yandex
Requires at least: 4.2
Tested up to: 4.5
Stable tag: 1.4.0
License: GPL v3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

An easy to use XML sitemap generator with support for image and video sitemaps for WordPress.

== Description ==
The [Sitemap Generator](https://www.marcobeierer.com/wordpress-plugins/sitemap-generator) uses an external service to crawl your website and create a XML sitemap of your website. The generator works thus for every plugin out of the box. The computation costs for your website is also very low because the crawler acts like a normal visitor, who visits all pages of your site once.

If you host your website on a dedicated server, you may not need this plugin, because you should have enough resources to generate your sitemap on your server. But if you host your website in a shared environment (as the most WordPress users do), it would be wise to outsource the generation of your sitemap to an external service like this plugin uses to generate the sitemap. This way it is guaranteed that the speed of your website is not affected for your visitors during the generation of the sitemap.

= Features =
* Simple setup.
* Works out of the box with all WordPress plugins.
* Low computation costs for your webserver.

= Technical Features =
* Respects your robots.txt file (also the crawl-delay directive).
	* You could use the user-agent MB-SiteCrawler to control the crawler.
* Support for robots (noindex) meta elements.
* Adds nearly all indexable filetypes (for example .pdf, .xls, .doc) to the sitemap.
	* See the document [Sitemap Generator Data](https://www.marcobeierer.com/tools/sitemap-generator-data) on my website for more information.

= Additional Technical Features of the Professional Version =
* Generation of image sitemaps.
* Generation of video sitemaps.
	* Currently HTML5 video elements and embedded YouTube videos are supported.

= Upcoming Technical Features =
* Support for Vimeo videos in video sitemaps.
* Support for HTML5 picture elements in image sitemaps.
* Automatic daily creation of sitemaps.

= Technical Requirements =
* cURL 7.18.1 or higher.
	* PHP 5.3 should be compiled against a compatible cURL version in the most cases. PHP 5.4 or higher should by default provide a compatible cURL version.
* OpenSSL 0.9.8f or higher.

= Data Aggregation and Indexable File Types =
I have published a detailed document about how the Sitemap Generator aggregates the data for the generation of sitemaps and which file types are getting indexed on my website.

[Sitemap Generator Data](https://www.marcobeierer.com/tools/sitemap-generator-data)

= Is the service free of charge? =
The Sitemap Generator service allows you to create a sitemap with up to 500 URLs for free. If your website has more URLs or you like to integrate an image or video sitemap, you could buy the professional version to create a sitemap with up to 50000 URLs at the following website. The wordpress plugin itself is free of charge, but nearly useless without the external service. Please note that also not indexable URLs (for example .zip files) count to the quota.

[Sitemap Generator Professional](https://www.marcobeierer.com/wordpress-plugins/sitemap-generator-professional)

= Limitations =
By default the Sitemap Generator indexes the first 500 URLs of your website. If your website has more URLs, please see the section 'Is the service free of charge?'.

= Warnings =
If you already have an existing sitemap.xml in your WordPress root directory, this file would be overwritten. It is thus recommended to backup your existing sitemap.xml file before using the Sitemap Generator. I also have not tested the generator on Windows webspace. You should also access the sitemap.xml after the generation finished and check if everything is fine.

= Pre-Installation Verification Test =
If you like to test if the Sitemap Generator works fine with your website before you will install the plugin, you could use the [Online Sitemap Generator](https://www.marcobeierer.com/tools/sitemap-generator#generator) on my website, which uses to same technology as the plugin to generate the sitemaps.  

= Use of an External Server =
The Sitemap Generator uses an external server, operated by the developer of the plugin, to crawl your website and detect broken links. This means, that there is some communication between your website and the server. The only data that is communicated to the external server by your website is the URL of your website and the fact that you are using WordPress. The server than crawlers your website (as a normal visitor does) and answers with the generated sitemap.

== Installation ==
1. Upload the 'mb-sitemap-generator' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Access the generator with the "Sitemap Generator" button in the sidebar and use the "Generate your sitemap" button to start the generation process. The sitemap will be saved as sitemap.xml in your WordPress root directory. **Be aware that an existing sitemap.xml file would be overwritten without asking.**
4. Use the "Show the sitemap" button to download your sitemap and check if the generated sitemap is complete.

== Frequently Asked Questions ==

= Why does the Sitemap Generator not index any URLs of my site? =

**Noindex set for all pages**

The Sitemap Generator is aware of robots (noindex) meta elements and does not list pages that are marked with the noindex attribute. I saw some websites in the wild, which have added the noindex attribute on each page. Please make sure that this is not the case for your website. Neither the Sitemap Generator nor a search engine will index your site if the noindex attribute is set globally.

**Site crawler blocked**

Another reason for a sitemap with no URLs could be that the crawler of the Sitemap Generator is blocked by your hosting provider. I have observed this issue especially with free and really cheap hosting providers. Some block crawlers (and regular visitors) already after five fast sequential requests. The issue could be fixed by whitelisting the IP of the crawler. However, I think this option is not available for the affected hosting services. Alternatively it is possible to use the crawl-delay directive in your robots.txt to set the delay between two requests.

= Is it possible to filter the URLs which are listed in the sitemap? =

The Sitemap Generator recognizes the noindex attribute if set on a page and respects your robots.txt file. It is thus possible to filter the results with these two mechanisms. A filter function in the plugin is not available, because it makes no sense in my opinion. If a page is not listed in a XML sitemap file, that means not that a search engine will not find it. Sooner or later the search engine finds and indexes the page. So the use of the noindex attribute and robots.txt are a clean solutions which is also respected by all serious search engines.

= Which user-agent should I use in the robots.txt file? =
The Sitemap Generator uses a custom user-agent group named MB-SiteCrawler. This allows you a fine grained control of which pages are parsed and added to the sitemap. If you do not define a group for the custom user-agent in your robots.txt file, the default set in the * group apply.

= How are images which are not embedded in a page handled? =

Images which are only linked to directly and not embedded in a HTML page are listed in the image sitemap and not as normal URLs. There is sadly no specification about how to handle such images, but because images need some context to be evaluated correctly in this day and age, I think the image sitemap is the best place to put them.

= How are embedded images from external domains are handled? =

If you embed images from external domains on your website, they are listed in the image sitemap. So it's no problem if you deliver your images for example through a CDN services which is available under another domain. Please not that this is only true for embedded images and not if you directly link to images on other domains.

= Does the Sitemap Generator work in my local development environment? =

No, the Sitemap Generator needs to crawl your website and the generator has no access to you local network.

= The Sitemap Generator is very slow. What can I do? =
In the most cases this is due to the fact that you have set a large value for the crawl-delay directive in your robots.txt file. Some hosters also add the crawl-delay directive automatically to your robots.txt file. The crawl-delay defines the time in seconds between to requests of the crawler.

== Screenshots ==

1. The user interface of the Sitemap Generator.

== Changelog ==

= 1.4.0 =
*Release Date - 12th July, 2016*

* Added an option to ignore embedded content (for example images).
* Added an option to define the maximum number of concurrent connections.
* Bugfixes
	* Implemented Cache-Control for AJAX requests.
	* Fixed the PHP short tag issue.
	* Specific error message if write to file failed.

= 1.3.1 =
*Release Date - 1st February, 2016*

* Improved cURL error messages.
* Bugfix: Replaced get_site_url() with get_home_url(), which referes to the option "Site Address (URL)".

= 1.3.0 =
*Release Date - 11th November, 2015*

* Implemented crawl and sitemap stats.
* Implemented error message if backend is down.
* Implemented better error messages to detect problems on startup.
* Technical changes
	* Renamed global JS vars (namespacing).
	* Removed german language strings.
	* Moved some vars to a separate file to use the same JS file with multiple CMS.

= 1.2.5 =
*Release Date - 27th September, 2015*

* Implemented better error message if token is invalid or has expired.
* Implemented better error message if limit is reached.

= 1.2.4 =
*Release Date - 27th September, 2015*

* Another bug fix release for an issue with PHP 5.3.

= 1.2.3 =
*Release Date - 27th September, 2015*

* Bug fix release, one file was missing in the previous release.

= 1.2.2 =
*Release Date - 27th September, 2015*

* Better error reporting if website is not reachable.
* Fixed bug that external files (for example pdf files) were added to the sitemap.
* Added a check for the correct cURL version.
* Added a check if the plugin is used in a local development environment.

= 1.2.1 =
*Release Date - 31th August, 2015*

* Added support for title and caption in image sitemaps.
* Added support for tags, view count and region restriction of YouTube videos in video sitemap.
* Removed CDATA sections and escape the content instead in image and video sitemaps.
* Added a link to the Sitemap Generator Data overview page in the readme.

= 1.2.0 =
*Release Date - 28th August, 2015*

* Undone change introduced in 1.1.0: Pages blocked by the robots.txt file are not parsed from now on as in versions older than 1.1.0. I rethought this point and think crawlers should respect the robots.txt, no matter which purpose the crawler has.
* Support for custom user-agent group (MB-SiteCrawler) in robots.txt.
* The video sitemap supports YouTube videos from now on. The data is fetched through the YouTube Data API.
* Some text changes in the plugins backend.

= 1.1.0 =
*Release Date - 21th August, 2015*

*Please note that the plugin was not changed, just the backend service.*

* Images which are linked to, but not embedded in a page, are now shown in the image sitemap to give them some context. In earlier version these images were listed as normal URLs.
* Pages, blocked by the robots.txt file, were not parsed in earlier version. This is fixed now. They still don't get listed in the sitemap, but the links on these pages are detected.
* Fixed an issue with the evaluation of the HTML base tag. A base tag href value with a trailing slash was not evaluated correctly before.
* Some smaller bug fixes and performance improvements.

= 1.0.4 =
*Release Date - 16th August, 2015*

* Bug fix release for issue in 1.0.3.

= 1.0.3 =
*Release Date - 16th August, 2015*

* Changed menu position to a more unique one.
* Added a message to indicate if the URL limit was reached.

= 1.0.2 =
*Release Date - 10th August, 2015*

* Added information about the use of an external server to the readme file.
* Use the native WordPress functions to load the JavaScript files.

= 1.0.1 =
*Release Date - 8th August, 2015*

* Changed license from AGPL to GPL.
* Added a FAQ section.
* Added a note to the plugin admin interface.

= 1.0.0 =
*Release Date - 7th August, 2015*

* Added support for video sitemaps.

= 1.0.0-beta.5 =
*Release Date - 7th August, 2015*

* Added support for nearly all indexable filetypes.
* Added support for robots (noindex) meta elements.

= 1.0.0-beta.4 =
*Release Date - 1st August, 2015*

* Added support for image sitemaps.

= 1.0.0-beta.3 =
*Release Date - 30th May, 2015*

* Implemented support for authorization tokens.

= 1.0.0-beta.2 =
*Release Date - 16th May, 2015*

* Improved the user interface.

= 1.0.0-beta.1 =
*Release Date - 9th May, 2015*

* Initial release.

