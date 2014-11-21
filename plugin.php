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
            add_action( 'admin_print_scripts', array( $this, 'plugin_scripts' ) );
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            add_action( 'izw_tab_import', array( $this, 'import_tab_import' ) );
            add_action( 'izw_tab_select-option', array( $this, 'import_tab_select_option' ) );
            add_action( 'wp_ajax_load_same_data', array( $this, 'load_same_data' ) );
            add_action( 'wp_ajax_nopriv_load_same_data', array( $this, 'load_same_data' ) );

            register_activation_hook( __FILE__, array( $this, 'install' ) );
            register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );

        }

        /**
         * Init Plugin
         */
        function init(){
            $this->defines();
            $this->includes();
            $this->register_post_type();
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
            wp_enqueue_script( 'izweb-import', plugin_dir_url( __FILE__ )."assets/admin/js/izweb-import.js", array( 'jquery' ) );
            wp_enqueue_style( 'izweb-import', plugin_dir_url( __FILE__ )."assets/admin/css/style.css" );
        }

        /**
         * Admin Menu
         */
        function admin_menu(){
            add_submenu_page( 'edit.php?post_type=program', __( 'Import Settings', __TEXTDOMAIN__ ), __( 'Import Settings', __TEXTDOMAIN__ ), 'manage_options', 'izweb-import-setting', array( $this, 'import_page' ));
            add_submenu_page( 'edit.php?post_type=program', __( 'Select fields', __TEXTDOMAIN__ ), __( 'Select fields', __TEXTDOMAIN__ ), 'manage_options', 'izweb-import-fields', array( $this, 'setting_page' ) );
        }

        /**
         * Display Main Setting Page
         */
        function setting_page(){
            include "includes/admin/setting-fields-page.php";
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
        /**
         * Register Post Type Program
         */
        function register_post_type(){
            do_action( 'izweb_before_register_post_type' );
            $labels = array(
                'name'               => _x( 'Programs', 'post type general name', 'your-plugin-textdomain' ),
                'singular_name'      => _x( 'Program', 'post type singular name', 'your-plugin-textdomain' ),
                'menu_name'          => _x( 'Programs', 'admin menu', 'your-plugin-textdomain' ),
                'name_admin_bar'     => _x( 'Program', 'add new on admin bar', 'your-plugin-textdomain' ),
                'add_new'            => _x( 'Add New', 'book', 'your-plugin-textdomain' ),
                'add_new_item'       => __( 'Add New Program', 'your-plugin-textdomain' ),
                'new_item'           => __( 'New Program', 'your-plugin-textdomain' ),
                'edit_item'          => __( 'Edit Program', 'your-plugin-textdomain' ),
                'view_item'          => __( 'View Program', 'your-plugin-textdomain' ),
                'all_items'          => __( 'All Programs', 'your-plugin-textdomain' ),
                'search_items'       => __( 'Search Programs', 'your-plugin-textdomain' ),
                'parent_item_colon'  => __( 'Parent Programs:', 'your-plugin-textdomain' ),
                'not_found'          => __( 'No Programs found.', 'your-plugin-textdomain' ),
                'not_found_in_trash' => __( 'No Programs found in Trash.', 'your-plugin-textdomain' )
            );

            $args = array(
                'labels'             => $labels,
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'query_var'          => true,
                'rewrite'            => array( 'slug' => 'program' ),
                'capability_type'    => 'post',
                'has_archive'        => true,
                'hierarchical'       => false,
                'menu_position'      => null,
                'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
            );

            register_post_type( 'program', apply_filters( 'izweb_register_post_type_program', $args ) );
            do_action( 'izweb_after_register_post_type' );
        }

        function install(){

        }
        function uninstall(){

        }
        function load_same_data(){
            print_r( $_FILES['same_file'] );
            die();
        }

    }
    new Izweb_Import();
endif;