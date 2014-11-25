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
            add_action( 'admin_print_scripts', array( $this, 'admin_plugin_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'front_plugin_scripts' ) );
            add_action( 'admin_menu', array( $this, 'admin_menu' ) );
            add_action( 'izw_tab_import', array( $this, 'import_tab_import' ) );
            add_action( 'izw_tab_select-option', array( $this, 'import_tab_select_option' ) );
            add_action( 'wp_ajax_load_same_data', array( $this, 'load_same_data' ) );
            add_action( 'wp_ajax_nopriv_load_same_data', array( $this, 'load_same_data' ) );
            add_action( 'izweb_before_setting_page', array( $this, 'process_import' ) );
            add_filter( 'template_include', array( $this, 'template_include' ), 99 );

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
            require_once( "includes/admin/short-codes.php" );
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
         * Admin Plugin Style And Script
         */
        function admin_plugin_scripts(){
            wp_enqueue_script( 'izweb-import', plugin_dir_url( __FILE__ )."assets/admin/js/izweb-import.js", array( 'jquery' ) );
            wp_enqueue_style( 'izweb-import', plugin_dir_url( __FILE__ )."assets/admin/css/style.css" );
        }

        /**
         * Front-End Plugin Style And Script
         */
        function front_plugin_scripts(){
            wp_enqueue_style( 'izweb-import-front', plugin_dir_url( __FILE__ )."assets/front-end/css/style.css" );
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
                'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'post-formats' )
            );

            register_post_type( 'program', apply_filters( 'izweb_register_post_type_program', $args ) );
            do_action( 'izweb_after_register_post_type' );
        }

        /**
         * Install Plugin
         */
        function install(){

        }

        /**
         * Uninstall Plugin
         */
        function uninstall(){

        }

        /**
         * Process Import
         */
        function process_import(){
            $current_user = wp_get_current_user();
            if( isset( $_POST['izw-import'] ) ){
                do_action( "izweb_before_process_import", $_POST );
                // Set file type allow
                $format_allows = array(
                    'type' => array( 'zip', 'xml' )
                );
                $format_allows = apply_filters( 'izweb_file_format_allow', $format_allows, $_POST );

                if( !empty( $_FILES['file-import']['name'] ) ){
                    $error = new WP_Error();
                    // Check file type
                    $filename = $_FILES['file-import']['name'];
                    $folder =  __IZIPPATH__."/uploads";
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    if(!in_array($ext,$format_allows['type']) ) {
                        $error->add( 'file_type', __( "Sorry, only <strong>".implode(" ,", $format_allows['type'])."</strong> files are allowed", __TEXTDOMAIN__ ) );
                    }
                    // Check file size
                    if ($_FILES["file-import"]["size"] > 500000) {
                        $error->add( 'file_size', __( "Sorry, your file is too large", __TEXTDOMAIN__ ) );
                    }
                    if( sizeof( $error->get_error_codes() ) > 0){
                        foreach( $error->get_error_messages() as $mes ){
                            echo '<div class="error">'.$mes.'</div>';
                        }
                    }else{
                        // Create Directory
                        if( !file_exists( $folder ) ){
                            mkdir($folder, 0777, true);
                        }
                        // Upload file to server
                        move_uploaded_file($_FILES["file-import"]["tmp_name"], $folder."/".basename($_FILES["file-import"]["name"]));
                        // Read zip first
                        $zip = new ZipArchive();
                        $x = $zip->open( $folder . "/".basename($_FILES["file-import"]["name"]));
                        if($x === true) {
                            global $wpdb;
                            $table = $wpdb->prefix."izweb_import";
                            $zip->extractTo($folder);
                            $zip->close();
                            unlink($folder."/".basename($_FILES["file-import"]["name"]));
                            $handle = opendir($folder);
                            $i=0;
                            while ($f = readdir($handle)) {
                                if ($f != "." && $f != "..") {
                                    $doc = new DOMDocument();
                                    $doc->load( $folder."/".$f );
                                    $custom_fields = get_option( 'izweb_custom_fields' );
                                    $post_title = get_option( 'izweb_import_title' );
                                    $post_content = get_option( 'izweb_import_content' );
                                    if( !empty( $custom_fields ) && is_array( $custom_fields ) && !empty($post_title) && !empty( $post_content ) ){
                                        $defaults = array(
                                            'post_status'           => 'publish',
                                            'post_type'             => 'program',
                                            'post_author'           => $current_user->ID,
                                            'ping_status'           => get_option('default_ping_status'),
                                            'post_title'            => $doc->getElementsByTagName( $post_title )->item(0)->nodeValue,
                                            'post_content'          => $doc->getElementsByTagName( $post_content )->item(0)->nodeValue
                                        );
                                        $defaults = apply_filters( 'izweb_insert_arg_default', $defaults );
                                        $postid = wp_insert_post( $defaults );
                                        if( $postid ){
                                            foreach( $custom_fields as $field){
                                                update_post_meta( $postid, $field, $doc->getElementsByTagName( $field )->item(0)->nodeValue );
                                            }
                                        }
                                    }
                                    unlink( $folder."/".$f );
                                }
                            }
                            echo "<h2>".__( "Import successfully", __TEXTDOMAIN__) .". </h2>";
                            //wp_redirect( add_query_arg( array( 'page' => 'izweb-import-file', 'tab' => 'select-option' ), admin_url('admin.php') ) );
                            exit();
                        } else {
                            die("There was a problem. Please try again!");
                        }
                    }
                }
                do_action( "izweb_after_process_import" );
            }
        }

        /**
         * Include Template Program
         *
         * @param $template
         * @return string
         */

        function template_include( $template ) {

            if ( is_post_type_archive( 'program' )  ) {
                $new_template = locate_template( array( 'archive-program.php' ) );
                if ( '' != $new_template ) {
                    return $new_template;
                }else{
                    return plugin_dir_path(__FILE__)."templates/archive-program.php";
                }
            }
            if( is_singular( 'program' ) ){
                $new_template = locate_template( array( 'single-program.php' ) );
                if ( '' != $new_template ) {
                    return $new_template;
                }else{
                    return plugin_dir_path(__FILE__)."templates/single-program.php";
                }
            }

            return $template;
        }
    }
    new Izweb_Import();
endif;