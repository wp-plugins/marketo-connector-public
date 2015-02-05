=== Marketo Connector for Wordpress - Public ===
Contributors: HooshMarketing
Donate Link: http://hooshmarketing.com/
Tags: Marketo, Marketo munchkin, Marketo forms, form submission to marketo, lead update, lead capture
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a cut down version of Marketo Connector for Wordpress.  

== Description ==

This public version of Marketo Connector for Wordpress is a cut down version of <a href="https://launchpoint.marketo.com/hoosh-marketing/1181-wordpress-integration-for-marketo/">MarketoConnector for Wordpress</a>. It supports Marketo Spark Edition and above.  

It has the following features:

*	Embedding of the Marketo Munchkin code on Wordpress pages for lead tracking.
*	mForm shortcode to alter forms so they will submit to Marketo either to update or register leads.

More features are lined up to be implemented on the public version.

Meanwhile, you may check out the Marketo Connector for Wordpress currently having the following features:

*	Embedding of the Marketo Munchkin code on Wordpress pages.
*	The following shortcodes are available:
	-	*mForm* - alter forms to submit them to Marketo to register or update leads.
	-	*getLead* - to fetch Lead information from Marketo and display to the web visitor.
	-	*mSeg* - conditional showing of content based on the Lead data as held in Marketo.
	-	*getCustomPost* - to show custom posts or types based on whether it matches the Lead data or not.
	-	*customPostData* - which information from the custom post to display i.e. title, content, featured image or custom information. This is in conjunction with the getCustomPost shortcode above. 
*	Its own Custom Post generator to help you use all shortcodes.
*	Automated generation of Shortcodes. 
*	Merging Lead information when a lead logs in to the Wordpress powered website.
*	Auto-blog updates i.e. emailing subscribers when a new post is made. 

= Docs and Support =

A more detailed article and FAQ is found <a href="http://hooshmarketing.com.au/internal/wordpress-free-plugin-documentation/">here</a>. 

== Installation ==

1. Upload the entire **marketoconnector-public** folder to the */wp-content/plugins/* directory.
1. Activate the plugin through the *Plugins* menu in WordPress.

The *Marketo Connector* menu is found in the sidebar.

Shortcode generators are found in the **Edit Post** or **Edit Page** panels.

== Frequently Asked Questions ==

= Do I need a Marketo account to fully enjoy the features of this plugin?  =

Yes.

= Where do I find my Munchkin Account ID and Marketo Instance Name? =

A detailed discussion on how to get these values are found <a href="http://hooshmarketing.com.au/internal/wordpress-free-plugin-documentation/">here</a>.

== Screenshots ==

1. The Administration Panel for Marketo Connector Public
2. Shortcode generator on Posts, Page and Custom Posts to easily add shortcodes to articles and pages.
3. Administration Panel for Marketo Connector for Wordpress. Available on the premium edition only.
4. Blog Update Panel of Marketo Connector. This is where we set options on what the plugin does whenever a new post is made.Leads in marketo may receive emails informing them that a new post is made. Available on the premium edition only.
5. Whether to sync subscribers logging in to the Site or not. Available on the premium edition only.
6. The premium version comes with its own custom post generator to show to users (offers) based on Lead Information.   

== Changelog ==

= 1.0.0 =
* Initial Version.

== Upgrade Notice ==

None