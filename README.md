# WordPress Image Base64 Generation
Generate base64 encode versions of images

**Contributors:** lightningspirit\
**Tags:** image, media, base64, custom\
**Requires at least:** 6.7.1\
**Tested up to:** 6.7.1\
**Requires PHP:** 7.4\
**Stable tag:** 0.1.0\
**License:** GPLv2 or later\
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

## Installation ##

This section describes how to install the plugin and get it working.

1. Upload `wp-image-base64-main` directory to the `/wp-content/plugins/` directory
1. Activate the plugin `Image base64` through the 'Plugins' menu in WordPress
1. It will start a cron job that will generate base64 versions for existing media
1. Use `wp_get_attachment_metadata`, each size has a `base64` key in `sizes` property.
