=== Draw Attention: Pro ===
Contributors: tylerdigital, nataliemac, croixhaug
Tags: interactive images, image maps, highlightable areas, highlight images, product images, trade shows, floor plans, virtual tour, call to action
Requires at least: 3.5.1
Tested up to: 4.8
Stable tag: 1.8.12

Create interactive images in WordPress. Perfect for floor plans, trade shows, photo tagging, product features, and tutorials.

== Description ==

### Responsive Design ###
Interactive images resize to fit your theme and the available screen size

### Accessible ###
Map info is accessible to everyone who visits your site, regardless of device or capabilities.

### Progressively Enhanced ###
Your content is accessible even to users who have JavaScript disabled - SEO friendly too!

### Customizable Colors ###
Choose your own custom color scheme to match your site

### Highlight on Hover ###
Highlight different areas of your image when you site visitors moves their mouse over the image

### Easy to Draw ###
Easy to draw the highlightable areas of your image - and easy to edit the shapes later too!

### More Info on Click ###
When a highlighted map area is clicked, show more information.

[vimeo https://vimeo.com/118974102]

### Have Multiple Interactive Images ###
Need more than one interactive image on your site? The Pro version allows unlimited interactive images

### Layout Options ###
Show more info about highlighted map areas in a variety of different layouts or in a lightbox

### 20 Pre-Defined Color Palettes ###
Choose from one of 20 pre-defined color palettes or use your own custom color scheme

== Installation ==

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `draw-attention.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `draw-attention.zip`
2. Extract the `draw-attention` directory to your computer
3. Upload the `draw-attention` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Changelog ==

= 1.8.12 =
* Fixed: PHP Notice

= 1.8.11 =
* Improved: Compatibility with Beaver Builder and Elementor when editing
* Improved: Gutenberg compatibility
* Improved: Support for WP-native video embeds in More Info areas
* Fixed: display of image in Firefox with no content/title in lightbox

= 1.8.10 =
* Improved: Tooltips on IE11
* Fixed: Issue with "Array" shows up on newly created Draw Attention images

= 1.8.9 =
* Fixed: Incompatibility with Jetpack Lazy Load image feature

= 1.8.7 =
* Fixed: Unable to copy the export code for images in some browsers

= 1.8.6 =
* Improved: Mobile scrolling over active hotspots
* Improved: Moved wpautop() on description text to a filter 'da_description'
* Fixed: Undefined index errors for title and style
* Fixed: Mobile Safari cannot open links in new window (security). Detect Mobile Safari and open links in same window.
* Fixed: Internet Explorer display bugs for more info area

= 1.8.5 =
* Fixed: Undefined index notice
* Fixed: Handle error when 'Go to URL' is selected but no URL is entered
* Fixed: Conflict with Ninja Forms that prevented users from being able to use the Ninja Forms WYSIWYG editor (Thanks Ninja Forms team!)
* Improved: Compatibility with Visual Composer tabs
* Fixed: URL hotspots not opening on mobile browsers
* Fixed: PHP 7.1 type error

= 1.8.4 =
* Fixed: Browser compatibility
* Improved: Mobile event handling

= 1.8.3 =
* Fixed: Creeping More Info box in Internet Explorer
* Fixed: PHP Warning for user styles
* Fixed: Bug that prevented shortcode from being used more than once on a page
* Improved: Tap events on touch screens
* Fixed: Disappearing hotspots in lightbox
* Fixed: Unable to edit hotspots in Firefox
* Fixed: Bootstrap conflict that kept lightboxes and tooltips from working

= 1.8.2 =
* Improved: Refactored image highlighting to be more performant
* Added: Able to drag to re-order hotspots on an image
* Added: Import/Export functionality for moving all hotspots to another WP install
* Added: Beaver Builder module for adding images to page builder pages
* Improved: Hotspot drawing tool now allows for the entire hotspot to be moved - no more adjusting individual points
* Improved: Snappier animation for switching between hotspot more info content
* Improved: Updated qTip, Featherlight, Mobile Events, and Images Loaded scripts to latest versions
* Added: Always visible option for hotspots to help make them visible - needed for cases where the active areas of the image aren't immediately obvious
* Added: Able to create multiple color schemes for hotspots - have more than one color hotspot per image!

= 1.7.1 =
* Fixed: Missing detail images on some server environments

= 1.7 =
* Use wpdrawattention.com domain for updates and support

= 1.6.8 =
* New: Added troubleshooting note for admins when there's a theme or plugin conflict
* New: Improved compatibility for site migrations

= 1.6.7 =
* Fixed: Corrected tooltip positioning issues

= 1.6.6 =
* Fixed: Make canvas for drawing hotspots fill the available width

= 1.6.4 =
* Fixed: Compatibility with popup blocking in Mobile Safari (iOS 9+)
* Fixed: Mobile safari bug when using tooltip mode and landscape view

= 1.6.3 =
* Added: Support for interactive images to work within jQuery UI tabs
* Improved: Window resizing and redrawing of image hotspots
* Fixed: Safari bug when navigating back to an image with a clickable area using the URL action

= 1.6.1 =
* Added: Support for placing Draw Attention inside Divi theme Tabs & Toggles
* Added: Support for Visual Composer AJAX Page Transitions
* Added: Filter to load Draw Attention scripts early & everywhere (for themes using ajax page transitions)
* Fixed: Strict PHP warning for empty more info description

= 1.5.2 =
* Fixed: Incompatibility with 3rd party theme/plugin javascript in the admin
* Fixed: Scrolling issue with deep-linked hotspots on tall vertical images

= 1.5.1 =
* Fixed: PHP warnings when WP_DEBUG was on
* Fixed: Scrolling issue with deep-linked hotspots
* Fixed: Styling issue with multiple images on one page  (when one used tooltip layout)

= 1.5 =
* New: Customize background color (behind image) with new color picker added to interface
* New: Link directly to a hotspot (right-click and copy link) - area will be highlighted automatically
* Improved: Update lightbox javascript library

= 1.4.4 =
* Fixed: Mobile/Touch events required 4 taps for areas with URL action
* Fixed: Shortcode content broken in lightbox and tooltip layouts
* Fixed: Prevent accidental selection of lightbox with hover event

= 1.4.3 =
* Fixed: Loading more info in IE9 and IE10. Note that these browser do not support the area highlights
* Fixed: Clicking or mousing off a selected area will display the default placeholder text in the left, right, bottom, or top layouts
* Fixed: Potential conflict with other themes and plugins when using the tooltip layout

= 1.4.2 =
* Fixed: Allow shortcodes in more info area, without using the_content which caused some conflicts with other plugins (ie. showing sharing buttons)

= 1.4.1 =
* New: Tooltip Layout option
* New: Ability to link an area to a URL (instead of showing more info)
* New: Added image and shortcode columns to "All Images" section in admin
* New: Added Visual/WYSIWYG editor to area descriptions (more info box)
* Improved: Usability on mobile phones and touch devices
* Improved: Portuguese translation
* Fixed: Preview not working in some cases
* Fixed: Compatibility issue with Jetpack Photon
* Fixed: Lightbox ignoring color settings

= 1.3 =
* New: Easily preview your interactive image using "Preview Changes" or "View Post" in the dashboard
* Improved: Better handling of mobile device changing orientation after interactive image is loaded
* Fixed: Interactive features not working in older versions of Internet Explorer
* Fixed: Image distortion in lightbox layout (for portrait/vertical images)
* Fixed: Unexpected behavior when more than one interactive image is on the same page

= 1.2.1 =
* New: Add Portuguese translation
* Improved: Better handling of window resizing after interactive image is loaded
* Improved: Add warning message for old servers running PHP 5.2 (Draw Attention requires PHP 5.3+)
* Fixed: Conflict with some themes causing highlighted areas to "jump" when clicked

= 1.2 =
* New: Improve internationalization
* New: Add layout option to display more info on top
* Improved: Optimize detail image size (don't load full size image for smaller areas)
* Improved: Fade out highlighted area after closing lightbox (lightbox layout only)
* Improved: Handle JS conflicts with other plugins better
* Fixed: More info content not updating in some situations

= 1.1 =
* New: Add option to choose "Click" (default) or "Hover" event to display more info
* New: Add ability "click off" highlighted areas
* New: Add confirmation alert before deleting highlightable area in the dashboard
* New: CPT icon in dashboard
* New: Set a default color scheme for new images
* Improved: Large images are handled better in lightbox mode
* Fixed: PHP Warnings visible with WP_Debug
* Fixed: PHP Warnings when PHP is in Strict Standards mode

= 1.0 =
* Initial Release
