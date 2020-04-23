=== Draw Attention ===
Contributors: tylerdigital, nataliemac, croixhaug
Tags: interactive hotspot images, floor plans hotspots, image maps, real estate, highlightable areas, highlight images, trade shows, virtual tour, product images, conferences, call to action, responsive, responsive image map, infographic
Requires at least: 3.5.1
Tested up to: 5.4
Stable tag: 1.8.28
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create interactive images with clickable hotspots in WordPress. Perfect for floor plans and real estate photos, trade show booths, group photos, product features, annotated image tutorials, or any image map.

== Description ==

### New in 1.8: Improved interactive image rendering ###
We re-wrote the display of the plugin to use SVG for faster rendering of hotspots and better browser support

### Responsive Design ###
Interactive images resize to fit your theme and the available screen size

### Accessible ###
Image info is accessible to everyone who visits your site, regardless of device or capabilities.

### Progressively Enhanced ###
Your annotations & content are accessible even to users who have JavaScript disabled - SEO friendly too! Draw Attention uses canvas elements in modern browsers, and falls back to image maps in older browsers.

### Customizable Colors ###
Choose your own custom color scheme to match your site

### Highlight on Hover ###
Highlight different hotspot areas of your image when your site visitor moves their mouse over the interactive image

### Easy to Draw ###
Easy to draw the highlightable areas of your image - and easy to edit the shapes later too!

### More Info on Click ###
When a highlighted area is clicked, show more information. Great to highlight points of interest on your image.

### Go to a URL ###
Optionally send a site visitor to another URL when clicking highlightable hotspots on your image

[vimeo https://vimeo.com/138407309]

[Upgrade to Draw Attention Pro](http://wpdrawattention.com/) to get more features and customization:

### Have Multiple Interactive Images (Pro only) ###
Need more than one interactive image on your site? The Pro version allows unlimited highlightable areas on unlimited interactive images

### Layout Options (Pro only) ###
Show more info about highlighted map areas in a variety of different layouts, including lightbox and tooltip.

### 20 Pre-Defined Color Palettes (Pro only) ###
Choose from one of 20 pre-defined color palettes or use your own custom color scheme

### What could you make with Draw Attention? ###
* Showcase real estate or a new property development. Give your visitors a virtual tour where they can explore floor plans and photos
* Sell booths to exhibitors at your tradeshow/conference by showing them an interactive map of the trade show floor
* Make a product tour or great help documentation - explain your product by highlighting features in a visual way
* Interactive infographic - annotate/callout important areas on your infographic and show more information about those points of interest
* If you're familiar with HTML image maps, we help you make those in a modern way that's compatible with today's devices & browsers


== Installation ==

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'draw attention'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Manual Install =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `draw-attention.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= After Installation =

1. You'll see a new Draw Attention menu item on the left menu
1. Create your interactive image. We have a [video walkthrough](https://wpdrawattention.com/document/create-your-first-draw-attention-image/) showing how to create the image.
1. After your image is created, you can embed it into any page or post with the `[drawattention]` shortcode.

== Frequently Asked Questions ==

= What's added in Draw Attention Pro? =

**The Pro version includes:**

- Ability to create more than 1 interactive image
- Unlimited number of highlightable areas for each image
- 20 preset color schemes
- Custom layout options (change position of more info box or use a lightbox or a tooltip)

= Where can I find documentation and learn how to use the plugin? =
We have tutorials, videos and other helpful information on the [Draw Attention website](https://wpdrawattention.com)

= How do I draw my first image? =
We have a video walkthrough of creating your first image available in [our documentation](https://wpdrawattention.com/document/create-your-first-draw-attention-image/).

== Screenshots ==
1. Draw your highlightable areas right in the dasbhoard
2. Site visitors can click to learn more about your highlighted areas
3. Upload a floor plan and show detail photos for each room
4. Customize the colors and appearance of the highlighted areas
5.

== Changelog ==

= 1.8.28 =
* Improved: Updated JS rendering library, better positioning for tooltips

= 1.8.27 =
* Improved: Added support for nesting Draw Attention images in Elementor tabs

= 1.8.26 =
* Improved: Performance of BigCommerce integration

= 1.8.25 =
* Fixed: Conflict introduced by latest Yoast SEO release

= 1.8.24 =
* Fixed: Conflict with Sections page template in Genesis Lifestyle theme

= 1.8.23 =
* Improved: Removed warning about number of hotspots

= 1.8.22 =
* Added: BigCommerce integration

= 1.8.21 =
* Fixed: Showing error message when first clickable area has not been drawn

= 1.8.20 =
* Fixed: Compatibility with the "lazy-load" plugin

= 1.8.18 =
* Fixed: Prevent potential overlapping between Draw Attention and fixed elements in some themes

= 1.8.17 =
* Fixed: Bug in Mobile Safari 12 with showing more info area

= 1.8.15 =
* Fixed: Allow hotspots re-ordering by drag and drop
* Improved: Better CSS support for older browsers

= 1.8.13 =
* Improved: Increased allowed hotspots to 50 (was 20)
* Improved: Cleaned up confusing buttons in UI

= 1.8.12 =
* Improved: Added support for embedding Draw Attention in Visual Composer accordions

= 1.8.11 =
* Fixed: Image too large for drawing area was overflowing, now scrolls
* Fixed: Escape alt attributes for images and areas

= 1.8.10 =
* Improved: User experience for changing featured image
* Improved: Handle more cases of hash links for hotspots

= 1.8.9 =
# Warning for WPML and Non-English users: please test before updating #
* Improved: Internationalization using new text domain matching plugin slug (draw-attention instead of drawattention). We made this change to allow translators to use wordpress.org's translation system. If this breaks any existing translations for people using WPML or other approaches, please contact us at support@wpdrawattention.com so we can help you resolve it as quickly as possible.

= 1.8.7 =
* Improved: Hide tooltips if there is no title for a hotspot
* Fixed: Support for WMPL

= 1.8.6 =
* Fixed: Broken URL hotspots (introduced in v1.8.5)

= 1.8.5 =
* Improved: Add smooth scrolling for URL action to an element on the same page
* Fixed: Unable to scroll or zoom page over image on Chrome on iOS

= 1.8.4 =
* Improved: Add right-click to get link to hotspot for logged-in admins
* Fixed: Unable to scroll or zoom page over image on iOS

= 1.8.3 =
* Fixed: PHP Notice on some images

= 1.8.2 =
* Fixed: Background color setting not applying
* Fixed: Fixed width of URL-only images

= 1.8.1 =
* Improved: New SVG-powered front-end rendering for better cross browser support and improved performance

= 1.7.7 =
* Improved: Default text color on new images

= 1.7.6 =
* Fixed: Bad reference to VML behavior in the plugin CSS
* Fixed: Hid the Draw Attention menu item for users without permissions to create/edit

= 1.7.5 =
* Added: Gutenberg Draw Attention block for WP 5.0

= 1.7.4 =
* Fixed: PHP Notice

= 1.7.3 =
* Improved: Gutenberg compatibility

= 1.7.2 =
* Fixed: Issue with 'Array' text showing when first saving a Draw Attention image

= 1.7.1 =
* Fixed: Incompatibility with Jetpack lazy load feature

= 1.7.0 =
* Improved: Drawing tool to include handle to reposition hotspots

= 1.6.14 =
* Fixed: Potential Undefined Index Error when first creating an image

= 1.6.13 =
* Improved: Improve handling for Photon in recent Jetpack update

= 1.6.12 =
* Improved: Mobile scrolling over active hotspots

= 1.6.11 =
* Improved: Moved wpautop() on description text to a filter 'da_description'

= 1.6.10 =
* Fixed: Conflict with Ninja Forms that prevented users from being able to use the Ninja Forms WYSIWYG editor (Thanks Ninja Forms team!)
* Fixed: Handle error when 'Go to URL' is selected but no URL is entered
* Fixed: Undefined index error

= 1.6.9 =
* Improved: Support for WP 4.8.x

= 1.6.8 =
* Fixed: Strict PHP Notice when saving
* Fixed: Missing detail images on some server environments

= 1.6.7 =
* Fixed: Edge case with missing detail image ID

= 1.6.6 =
* New: Added troubleshooting note for admins when there's a theme or plugin conflict
* New: Improved compatibility for site migrations

= 1.6.5 =
* Fixed: Make canvas for drawing hotspots fill the available width

= 1.6.4 =
* Fixed: Conflict with Unveil Lazy Load plugin
* Fixed: Compatibility with popup blocking in Mobile Safari (iOS 9+)

= 1.6.2 =
* Added: Support for interactive images to work within jQuery UI tabs
* Improved: Window resizing and redrawing of image hotspots
* Fixed: Safari bug when navigating back to an image with a clickable area using the URL action

= 1.6.1 =
* Added: Support for placing Draw Attention inside Divi theme Tabs & Toggles
* Added: Support for Visual Composer AJAX Page Transitions
* Added: Filter to load Draw Attention scripts early & everywhere (for themes using ajax page transitions)
* Fixed: PHP (strict mode) warning when `post_type` is unset

= 1.5.3 =
* Fixed: Conflict between post ID and hotspot ID caused image to disappear when clicking hotspot

= 1.5.2 =
* Fixed: Incompatibility with 3rd party theme/plugin javascript in the admin
* Fixed: Scrolling issue with deep-linked hotspots on tall vertical images

= 1.5.1 =
* Fixed: PHP warnings when WP_DEBUG was on
* Fixed: Scrolling issue with deep-linked hotspots


= 1.5 =
* New: Customize background color (behind image) with new color picker added to interface
* New: Link directly to a hotspot (right-click and copy link) - area will be highlighted automatically

= 1.4.5 =
* Fixed: Mobile/Touch events required 4 taps for areas with URL action

= 1.4.4 =
* Fixed: "the_content" always displaying in the default more info area

= 1.4.3 =
* Fixed: Loading more info in IE9 and IE10. Note that these browsers do not support the area highlights
* Fixed: Clicking off a selected area will display the default placeholder text in the more info area

= 1.4.2 =
* Fixed: Allow shortcodes in more info area, without using the_content which caused some conflicts with other plugins (ie. showing sharing buttons)

= 1.4.1 =
* New: Ability to link an area to a URL (instead of showing more info)
* New: Added Visual/WYSIWYG editor to area descriptions (more info box)
* Improved: Usability on mobile phones and touch devices
* Improved: Portuguese translation
* Fixed: Preview not working in some cases
* Fixed: Compatibility issue with Jetpack Photon

= 1.3 =
* New: Easily preview your interactive image using "Preview Changes" or "View Post" in the dashboard
* Improved: Better handling of mobile device changing orientation after interactive image is loaded
* Fixed: Interactive features not working in older versions of Internet Explorer

= 1.2 =
* New: Improve internationalization
* New: Add Portuguese translation
* Improved: Optimize detail image size (don't load full size image for more info box)
* Improved: Handle JS conflicts with other plugins better
* Improved: Add warning message for old servers running PHP 5.2 (Draw Attention requires PHP 5.3+)
* Improved: Better handling of window resizing after interactive image is loaded
* Fixed: More info content not updating in some situations
* Fixed: Conflict with some themes causing highlighted areas to "jump" when clicked

= 1.1.3 =
* Fixed: Default color scheme always applying when re-editing an existing image

= 1.1.2 =
* Fixed: PHP Warnings when PHP is in Strict Standards mode

= 1.1.1 =
* New: Add ability "click off" highlighted areas
* New: Add confirmation alert before deleting highlightable area in the dashboard
* New: CPT icon in dashboard
* New: Set a default color scheme when activating Draw Attention
* Fixed: default layout to show More Info on Left rather than on top
* Fixed: PHP Warnings visible with WP_Debug
* Fixed: issues with selected highlight color not always displaying properly

= 1.0.2 =
* Fixed: issue with saving data

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 1.0 =
Initial Release
