<?php

/*
Plugin Name: Attributes for external links
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Test task for marakasdesign interview. Adds attributes for external links
Version: 1.0
Author: Viktor Oleksiukh
Author URI: http://URI_Of_The_Plugin_Author
License: GPL2
*/


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );


// Start up the engine
class AEL
{

    /**
     * Static property to hold our singleton instance
     *
     */
    static $instance = false;

    /**
     * array with options from admin settings page
     *
     */
    public $options;

    /**
     * This is our constructor
     *
     * @return void
     */
    function __construct()
    {
        // back end
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        // front end
        add_filter('the_content', array($this, 'ael_filtering'));
        add_action( 'ael_filtering',  array($this, 'ael_filtering') );


    }

    /**
     * If an instance exists, this returns it.  If not, it creates one and
     * retuns it.
     *
     * @return AEL
     */
    public static function getInstance()
    {
        if (!self::$instance)
            self::$instance = new self;
        return self::$instance;
    }


    /**
     * The actual actions for adding the tags.
     *
     * @return array
     */
    public function ael_filtering($content) {
        return preg_replace_callback('/<a[^>]+/', function($matches) {

            $link = $matches[0];
            $site_link = get_bloginfo('url');
            //get current options state for conditional logic
            $this->options = get_option( 'ael_option_name' );

            //Add target _blank filtering
            if(isset($this->options['blank'] ) && $this->options['blank'] == 'true') {
                if (strpos($link, 'target') === false) {
                    $link = preg_replace("%(href=\S(?!$site_link))%i", 'target="_blank" $1', $link);
                } elseif (preg_match("%href=\S(?!$site_link)%i", $link)) {
                    $link = preg_replace('/target=\S(?!_blank)\S*/i', 'target="_blank"', $link);
                }
            }

            //Add rel="nofollow" filtering
            if(isset($this->options['nofollow'] ) && $this->options['nofollow'] == 'true') {
                if (strpos($link, 'rel') === false) {
                    $link = preg_replace("%(href=\S(?!$site_link))%i", 'rel="nofollow" $1', $link);
                } elseif (preg_match("%href=\S(?!$site_link)%i", $link)) {
                    $link = preg_replace('/rel=\S(?!nofollow)\S*/i', 'rel="nofollow"', $link);
                }
            }
            return $link;

        }, $content);
    }


    /**
     * Admin settings page init and render.
     *
     * @return array
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings page"
        add_options_page(
            'Attributes for External Links settings page',
            'Attributes for External Links settings page',
            'manage_options',
            'ael-setting-admin',
            array( $this, 'create_admin_page' )
        );
    }
    /**
    * Options page callback
    */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'ael_option_name' );
        ?>
        <div class="wrap">
            <h1>Attributes for External Links settings page</h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'ael_option_group' );
                do_settings_sections( 'ael-setting-admin' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'ael_option_group', // Option group
            'ael_option_name', // Option name
            array( $this ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Here you can choose what tags to use', // Title
            array( $this, 'print_section_info' ), // Callback
            'ael-setting-admin' // Page
        );

        add_settings_field(
            'nofollow', // ID
            'Nofollow attribute for external links (rel="nofollow")', // Title
            array( $this, 'nofollow_callback' ), // Callback
            'ael-setting-admin', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'blank',
            'Open links in a new tab (target="_blank")',
            array( $this, 'blank_callback' ),
            'ael-setting-admin',
            'setting_section_id'
        );
    }


    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Choose your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function nofollow_callback()
    {
        if(isset($this->options['nofollow']) && $this->options['nofollow']){
            $checked = 'checked';
        }
        else{
            $checked = '';
        }
        echo '<input type="checkbox" id="nofollow" name="ael_option_name[nofollow]" value="true" '.$checked.' />';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function blank_callback()
    {
        if(isset($this->options['blank']) && $this->options['blank']){
            $checked = 'checked';
        }
        else{
            $checked = '';
        }
        echo '<input type="checkbox" id="blank" name="ael_option_name[blank]" value="true"  '.$checked.' />';
    }


}


$ael = AEL::getInstance();
