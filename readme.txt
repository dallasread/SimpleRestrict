=== SimpleRestrict ===
Version: 1.0.2
URI: http://WPSimpleRestrict.com
Contributors: dallas22ca
Author: Dallas Read
Author URI: http://www.DallasRead.com
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NX9NZCDNRD3CC
Tags: restrict, permissions, restrict page
Requires at least: 3.6
Tested up to: 4.0.1
Stable tag: trunk
License: MIT

SimpleRestrict allows you to restrict access to specific user roles on a page-by-page basis.

== Description ==

**What can I do with SimpleRestrict?**

SimpleRestrict allows you to restrict access to specific user roles on a page-by-page basis. For unauthorized users, you can set where the page is redirected.

You can also manage who sees what content on a page using the **[restrict]** shortcode. It takes two attributes: only & except. Each attribute takes a comma separated list of user roles. For example:

**\[restrict only="administrator"\]My Restricted Content\[/restrict\]** - only the admin will see "My Restricted Content"

**\[restrict only="administrator,editor"\]My Restricted Content\[/restrict\]** - only the admin and editors will see "My Restricted Content**"**

**\[restrict only="administrator,editor" except="editor"\]My Restricted Content\[/restrict\]** - the only attribute can be overridden by the except attribute (the editor can no longer see the content)

**How do I use SimpleRestrict?**

* Go to the Edit page for the Page or Post you wish to protect (works for all "public" page/post types).
* On the right sidebar, you'll see the SimpleRestrict box.
* Check off which roles are allowed to see the page and save. If you want to redirect them to a particular page (eg. login), enter the URL in the Redirect URL field, otherwise it will simply redirect to the home page.
* Alternatively, use the **[restrict only="editor"]This is restricted[/restrict]** shortcode.

== Installation ==

1. Visit the Plugins page. Click "Add New", search for "SimpleRestrict", and click install. "Activate" SimpleRestrict.
1. Edit any page on your site. In the right sidebar, you'll find a "SimpleRestrict" widget. Check off the roles that are allowed to access that page, then "Save."

== Frequently Asked Questions ==

= How do I use SimpleRestrict? =
* Go to the Edit page for the Page or Post you wish to protect (works for all "public" page/post types).
* On the right sidebar, you'll see the SimpleRestrict meta box.
* Check off which roles are allowed to see the page and save. If you want to redirect them to a particular page (eg. login), enter the URL in the Redirect URL field, otherwise it will simply redirect to the home page.
* Alternatively, use the **[restrict only="editor"]This is restricted[/restrict]** shortcode.

= What if I don't see the SimpleRestrict meta box on the Edit page? =
* Make sure the plugin is Activated.
* In the top right corner of the page, click "Screen Options." Ensure "SimpleRestrict" is checked.

= What is SimpleRestrict? =
* SimpleRestrict allows you to restrict access to specific user roles on a page-by-page basis.

== Screenshots ==

1. The SimpleRestrict meta box.

== Upgrade Notice ==

= 1.0.2 =
* Updated documentation.

= 1.0.1 =
* Added the [restrict only="administrator,editor" except="public"] shortcode!

= 1.0.0 =
* Initial public release!