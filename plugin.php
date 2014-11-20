<?php
/**
 * Created by PhpStorm.
 * User: nhiha60591
 * Date: 11/20/14
 * Time: 8:32 AM
 */
/*
Plugin Name: Izweb Import Plugin
Plugin URI: https://github.com/nhiha60591/izweb-import/
Description: Import File from zip file
Version: 1.0.1
Author: Izweb Team
Author URI: https://github.com/nhiha60591
Text Domain: izweb-import
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'Izweb_Import' ) ) :
    class Izweb_Import{
        function __construct(){
            add_action( 'init', array( $this, 'init') );
            add_action( 'wp_enqueue_scripts', array( $this, 'plugin_scripts' ) );
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            add_action( 'izw_tab_import', array( $this, 'import_tab_import' ) );
            add_action( 'izw_tab_select-option', array( $this, 'import_tab_select_option' ) );
        }

        /**
         * Init Plugin
         */
        function init(){
            $this->defines();
            $this->includes();
        }

        /**
         * Includes File
         */
        function includes(){
            require_once( "functions.php" );
        }

        /**
         * Define
         */
        function defines(){
            define( '__TEXTDOMAIN__', 'izweb-import' );
            define( '__IZIPPATH__', plugin_dir_path( __FILE__ ) );
            define( '__IZIPURL__', plugin_dir_url( __FILE__ ) );
            define( '__DBVERSION', '1.0.1' );
        }

        /**
         * Plugin Style And Script
         */
        function plugin_scripts(){
            //wp_enqueue_style( 'style-name', plugin_dir_url( __FILE__ ) );
            //wp_enqueue_script( 'script-name', plugin_dir_url( __FILE__ ) . '/js/example.js', array(), '1.0.0', true );
        }

        /**
         * Admin Menu
         */
        function admin_menu(){
            add_menu_page( __( 'Import Settings', __TEXTDOMAIN__ ), __( 'Import Settings', __TEXTDOMAIN__ ), 'manage_options', 'izweb-import', array( $this, 'setting_page' ), '', 6 );
            add_submenu_page( 'izweb-import', __( 'Import File', __TEXTDOMAIN__ ), __( 'Import File', __TEXTDOMAIN__ ), 'manage_options', 'izweb-import-file', array( $this, 'import_page' ) );
        }

        /**
         * Display Main Setting Page
         */
        function setting_page(){
            include "includes/admin/setting-page.php";
        }

        /**
         * Display Main Import Page
         */
        function import_page(){
            include "includes/admin/import-page.php";
        }

        /**
         * Display Tab Import
         */
        function import_tab_import(){
            include "includes/admin/import-form.php";
        }

        /**
         * Display Tab Select Options
         */
        function import_tab_select_option(){
            include "includes/admin/import-select-option.php";
        }
    }
    new Izweb_Import();
endif;