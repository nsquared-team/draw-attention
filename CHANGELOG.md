# Changelog

## 2.0.16 - 2023-10-29

### Features and Improvements

- Add nonce checks for additional security

## 2.0.15 - 2023-10-19

### Fixes

- Fix disclosed vulnerability affecting Contributor-level users

## 2.0.14 - 2023-08-12

### Features and Improvements

- Tested up to WP 6.3

## 2.0.13 - 2023-06-25

### Fixes

- Fix PHP warning in CMB2 library

### Features and Improvements

- Prevent conflicts with other plugins that use the leaflet library

## 2.0.12 - 2023-05-26

### Features and Improvements

- Improved nonce verification and capability checks

## 2.0.11 - 2023-05-12

### Fixes

- Fix Elementor popup duplicating DA images

### Features and Improvements

- Update gutenberg block to use api version 2

## 2.0.9 - 2023-04-11

### Fixes

### Features and Improvements

## 2.0.8 - 2023-03-28

### Fixes

### Features and Improvements

## 2.0.7 - 2023-03-28

- no changes

## 2.0.6 - 2023-03-28

### Fixes

### Features and Improvements

## 2.0.5 - 2023-03-28

### Fixes

### Features and Improvements

## 2.0.4

- Fixed: SVG desc markup

## 2.0.3

- Fixed: PHP Warning in import function

## 2.0.2

- Improved: Accessibility (suppress hidden image from assistive technology)

## 2.0.1

- WP 6.1 Compatibility

## 2.0.0

- Improved: New drawing tool for hotspots

## 1.9.34

- Improved: SEO attribute support for Google Search Console

## 1.9.33

- Fixed: Conflict with MainWP bug

## 1.9.32

- Improved: Added notice to warn about minimum PHP version 5.6

## 1.9.31

- Improved: Compatibility with WP 6.0

## 1.9.30

- Improved: Updated plugin author contact details

## 1.9.29

- Fixed: Unexpected behavior on hover

## 1.9.28

- Fixed: Plugin URI
- Fixed: Accessibility typo

## 1.9.27

- Improved: Translation support

## 1.9.26

- Improved: Show a warning to administrators when a featured image has not been uploaded

## 1.9.25

- Improved: Tested for compatibility with latest WordPress trunk

## 1.9.24

- Added: webp images can be used for Draw Attention images
- Improved: Allow editors to import/export images

## 1.9.23

- Fixed: Remove blank H2 if Draw Attention image has no title

## 1.9.22

- Fixed: Conflict with unmaintainted plugin: WP Category Permalink

## 1.9.21

- Fixed: CMB2 deprecation error

## 1.9.20

- Improved: Compatibility with WooCommerce tabs

## 1.9.19

- Fixed: Escape hotspot title

## 1.9.18

- Improved: Pause playing audio/video when changing hotspots

## 1.9.17

- Fixed: Undefined index notice

## 1.9.16

- Improved: Don't output unneeded HTML for hotspots that link to a URL
- New: Support for image optimization plugins that use the webp image format
- New: Support for embedding interactive images in Beaver Builder Ultimate Add-ons Accordions and Tabs

## 1.9.15

- Improved: Handling of tooltip display for links to hotspots

## 1.9.12

- Fixed: CMB2 incompatibilies with other plugins

## 1.9.10

- Fixed: Unable to display images when unused image sizes are removed by a media cleaner plugin
- Improved: Support for embedding Draw Attention images in Beaver Builder tabs

## 1.9.9

- Fixed: Bug affecting WPML-translated pages with Draw Attention images
- WP 5.6 Compatibility

## 1.9.7

- Fixed: Bug when importing multiple times

## 1.9.6

- Fixed: PHP warning
- Fixed: Error when trying to minify javascript

## 1.9.5

- Fixed: Remove SVG styles that conflicted with zooming on Safari

## 1.9.4

- Fixed: Really fix ability to link to a hotspot

## 1.9.3

- Fixed: Ability to link to a hotspot

## 1.9.2

- Improved: Jetpack compatibility
- Improved: Sop videos automatically when switching hotspots

## 1.9.1

- Improved: Attempt to avoid conflicts with themes that apply unexpected CSS styles to SVGs

## 1.9.0

- Improved: Keyboard accessibility for Draw Attention interactive images
- Fixed: Updated contact information for support

## 1.8.31

- Improved: Hide non-clickable featured image when previewing Draw Attention interactive image

## 1.8.30

- Improved: Add compatibility with lazy loading in WP 5.5

## 1.8.29

- Improved: Add support for nesting Draw Attention images in Elementor popups

## 1.8.28

- Improved: Updated JS rendering library, better positioning for tooltips

## 1.8.27

- Improved: Added support for nesting Draw Attention images in Elementor tabs

## 1.8.26

- Improved: Performance of BigCommerce integration

## 1.8.25

- Fixed: Conflict introduced by latest Yoast SEO release

## 1.8.24

- Fixed: Conflict with Sections page template in Genesis Lifestyle theme

## 1.8.23

- Improved: Removed warning about number of hotspots

## 1.8.22

- Added: BigCommerce integration

## 1.8.21

- Fixed: Showing error message when first clickable area has not been drawn

## 1.8.20

- Fixed: Compatibility with the "lazy-load" plugin

## 1.8.18

- Fixed: Prevent potential overlapping between Draw Attention and fixed elements in some themes

## 1.8.17

- Fixed: Bug in Mobile Safari 12 with showing more info area

## 1.8.15

- Fixed: Allow hotspots re-ordering by drag and drop
- Improved: Better CSS support for older browsers

## 1.8.13

- Improved: Increased allowed hotspots to 50 (was 20)
- Improved: Cleaned up confusing buttons in UI

## 1.8.12

- Improved: Added support for embedding Draw Attention in Visual Composer accordions

## 1.8.11

- Fixed: Image too large for drawing area was overflowing, now scrolls
- Fixed: Escape alt attributes for images and areas

## 1.8.10

- Improved: User experience for changing featured image
- Improved: Handle more cases of hash links for hotspots

## 1.8.9

# Warning for WPML and Non-English users: please test before updating

- Improved: Internationalization using new text domain matching plugin slug (draw-attention instead of drawattention). We made this change to allow translators to use wordpress.org's translation system. If this breaks any existing translations for people using WPML or other approaches, please contact us at support@wpdrawattention.com so we can help you resolve it as quickly as possible.

## 1.8.7

- Improved: Hide tooltips if there is no title for a hotspot
- Fixed: Support for WMPL

## 1.8.6

- Fixed: Broken URL hotspots (introduced in v1.8.5)

## 1.8.5

- Improved: Add smooth scrolling for URL action to an element on the same page
- Fixed: Unable to scroll or zoom page over image on Chrome on iOS

## 1.8.4

- Improved: Add right-click to get link to hotspot for logged-in admins
- Fixed: Unable to scroll or zoom page over image on iOS

## 1.8.3

- Fixed: PHP Notice on some images

## 1.8.2

- Fixed: Background color setting not applying
- Fixed: Fixed width of URL-only images

## 1.8.1

- Improved: New SVG-powered front-end rendering for better cross browser support and improved performance

## 1.7.7

- Improved: Default text color on new images

## 1.7.6

- Fixed: Bad reference to VML behavior in the plugin CSS
- Fixed: Hid the Draw Attention menu item for users without permissions to create/edit

## 1.7.5

- Added: Gutenberg Draw Attention block for WP 5.0

## 1.7.4

- Fixed: PHP Notice

## 1.7.3

- Improved: Gutenberg compatibility

## 1.7.2

- Fixed: Issue with 'Array' text showing when first saving a Draw Attention image

## 1.7.1

- Fixed: Incompatibility with Jetpack lazy load feature

## 1.7.0

- Improved: Drawing tool to include handle to reposition hotspots

## 1.6.14

- Fixed: Potential Undefined Index Error when first creating an image

## 1.6.13

- Improved: Improve handling for Photon in recent Jetpack update

## 1.6.12

- Improved: Mobile scrolling over active hotspots

## 1.6.11

- Improved: Moved wpautop() on description text to a filter 'da_description'

## 1.6.10

- Fixed: Conflict with Ninja Forms that prevented users from being able to use the Ninja Forms WYSIWYG editor (Thanks Ninja Forms team!)
- Fixed: Handle error when 'Go to URL' is selected but no URL is entered
- Fixed: Undefined index error

## 1.6.9

- Improved: Support for WP 4.8.x

## 1.6.8

- Fixed: Strict PHP Notice when saving
- Fixed: Missing detail images on some server environments

## 1.6.7

- Fixed: Edge case with missing detail image ID

## 1.6.6

- New: Added troubleshooting note for admins when there's a theme or plugin conflict
- New: Improved compatibility for site migrations

## 1.6.5

- Fixed: Make canvas for drawing hotspots fill the available width

## 1.6.4

- Fixed: Conflict with Unveil Lazy Load plugin
- Fixed: Compatibility with popup blocking in Mobile Safari (iOS 9+)

## 1.6.2

- Added: Support for interactive images to work within jQuery UI tabs
- Improved: Window resizing and redrawing of image hotspots
- Fixed: Safari bug when navigating back to an image with a clickable area using the URL action

## 1.6.1

- Added: Support for placing Draw Attention inside Divi theme Tabs & Toggles
- Added: Support for Visual Composer AJAX Page Transitions
- Added: Filter to load Draw Attention scripts early & everywhere (for themes using ajax page transitions)
- Fixed: PHP (strict mode) warning when `post_type` is unset

## 1.5.3

- Fixed: Conflict between post ID and hotspot ID caused image to disappear when clicking hotspot

## 1.5.2

- Fixed: Incompatibility with 3rd party theme/plugin javascript in the admin
- Fixed: Scrolling issue with deep-linked hotspots on tall vertical images

## 1.5.1

- Fixed: PHP warnings when WP_DEBUG was on
- Fixed: Scrolling issue with deep-linked hotspots

## 1.5

- New: Customize background color (behind image) with new color picker added to interface
- New: Link directly to a hotspot (right-click and copy link) - area will be highlighted automatically

## 1.4.5

- Fixed: Mobile/Touch events required 4 taps for areas with URL action

## 1.4.4

- Fixed: "the_content" always displaying in the default more info area

## 1.4.3

- Fixed: Loading more info in IE9 and IE10. Note that these browsers do not support the area highlights
- Fixed: Clicking off a selected area will display the default placeholder text in the more info area

## 1.4.2

- Fixed: Allow shortcodes in more info area, without using the_content which caused some conflicts with other plugins (ie. showing sharing buttons)

## 1.4.1

- New: Ability to link an area to a URL (instead of showing more info)
- New: Added Visual/WYSIWYG editor to area descriptions (more info box)
- Improved: Usability on mobile phones and touch devices
- Improved: Portuguese translation
- Fixed: Preview not working in some cases
- Fixed: Compatibility issue with Jetpack Photon

## 1.3

- New: Easily preview your interactive image using "Preview Changes" or "View Post" in the dashboard
- Improved: Better handling of mobile device changing orientation after interactive image is loaded
- Fixed: Interactive features not working in older versions of Internet Explorer

## 1.2

- New: Improve internationalization
- New: Add Portuguese translation
- Improved: Optimize detail image size (don't load full size image for more info box)
- Improved: Handle JS conflicts with other plugins better
- Improved: Add warning message for old servers running PHP 5.2 (Draw Attention requires PHP 5.3+)
- Improved: Better handling of window resizing after interactive image is loaded
- Fixed: More info content not updating in some situations
- Fixed: Conflict with some themes causing highlighted areas to "jump" when clicked

## 1.1.3

- Fixed: Default color scheme always applying when re-editing an existing image

## 1.1.2

- Fixed: PHP Warnings when PHP is in Strict Standards mode

## 1.1.1

- New: Add ability "click off" highlighted areas
- New: Add confirmation alert before deleting highlightable area in the dashboard
- New: CPT icon in dashboard
- New: Set a default color scheme when activating Draw Attention
- Fixed: default layout to show More Info on Left rather than on top
- Fixed: PHP Warnings visible with WP_Debug
- Fixed: issues with selected highlight color not always displaying properly

## 1.0.2

- Fixed: issue with saving data

## 1.0

- Initial Release
