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
        add_filter('the_content', array( $this, 'add_rel_nofollow' ));
        add_filter('the_content', array( $this, 'add_target__blank' ));
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
    public function add_target__blank($content) {
        return preg_replace_callback('/<a[^>]+/', function($matches) {

            $proceedLink = $matches[0];
            $SiteLink = get_bloginfo('url');

            if (strpos($link, 'rel') === false) {
                $proceedLink = preg_replace("%(href=\S(?!$SiteLink))%i", 'target="_blank" $1', $proceedLink);
            } elseif (preg_match("%href=\S(?!$SiteLink)%i", $proceedLink)) {
                $proceedLink = preg_replace('/target=\S(?!_blank)\S*/i', 'target="_blank"', $proceedLink);
            }

            return $proceedLink;

        }, $content);
    }

    public function add_rel_nofollow($content) {
        return preg_replace_callback('/<a[^>]+/', function($matches) {

            $proceedLink = $matches[0];
            $SiteLink = get_bloginfo('url');

            if (strpos($link, 'rel') === false) {
                $proceedLink = preg_replace("%(href=\S(?!$SiteLink))%i", 'rel="nofollow" $1', $proceedLink);
            } elseif (preg_match("%href=\S(?!$SiteLink)%i", $proceedLink)) {
                $proceedLink = preg_replace('/rel=\S(?!nofollow)\S*/i', 'rel="nofollow"', $proceedLink);
            }

            return $proceedLink;

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
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Here you can choose what tags to use', // Title
            array( $this, 'print_section_info' ), // Callback
            'ael-setting-admin' // Page
        );

        add_settings_field(
            'nofollow', // ID
            'Use rel "nofollow"', // Title
            array( $this, 'nofollow_callback' ), // Callback
            'ael-setting-admin', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'blank',
            'Use target "_blank"',
            array( $this, 'blank_callback' ),
            'ael-setting-admin',
            'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['nofollow'] ) )
            $new_input['nofollow'] = $input['nofollow'];

        if( isset( $input['blank'] ) )
            $new_input['blank'] =  $input['blank'];

        return $new_input;
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
        if($this->options['nofollow']){
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
        if($this->options['blank']){
            $checked = 'checked';
        }
        else{
            $checked = '';
        }
        echo '<input type="checkbox" id="blank" name="ael_option_name[blank]" value="true"  '.$checked.' />';
    }


}


$ael = AEL::getInstance();
