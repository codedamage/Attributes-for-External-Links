# Attributes-for-External-Links
Test task for interview

# Description
Adds target="_blank" and rel="nofollow" to external links

# Installation

This section describes how to install the plugin and get it working.

1. Upload `AEL` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. That's it.

# How to use #

1. Go to settings -> Attributes for External Links settings page
2. Choose what attributes to add to external links
3. Click s"Save changes"
4. Done

# apply_filters()

You can use plugin functional via apply_filters().
**Example:**
```php
global $post;
echo apply_filters( 'ael_filtering', $post->post_content );
```
# TODO:

1. Add activate/deactivate hooks
2. Add uninstall.php
3. Add post types options.
