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
Version: 4.0.1
Author: Izweb Team
Author URI: https://github.com/nhiha60591
Text Domain: izweb-import
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
define( '__TEXTDOMAIN__', 'izweb-import' );
define( '__IZIPPATH__', plugin_dir_path( __FILE__ ) );
define( '__IZIPURL__', plugin_dir_url( __FILE__ ) );
define( '__DBVERSION', '3.1.0' );
if ( ! class_exists( 'Izweb_Import' ) ) :
    class Izweb_Import{
        function __construct(){
            add_action( 'init', array( __CLASS__, 'init'), 1 );
            add_action( 'admin_print_scripts', array( __CLASS__, 'admin_plugin_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( __CLASS__, 'front_plugin_scripts' ), 10 );
            add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
            add_action( 'izw_tab_import', array( __CLASS__, 'import_tab_import' ) );
            add_action( 'izw_tab_select-option', array( __CLASS__, 'import_tab_select_option' ) );
            add_action( 'izweb_before_setting_page', array( __CLASS__, 'process_import' ) );
            add_filter( 'template_include', array( __CLASS__, 'template_include' ), 99 );
            add_action( 'widgets_init', array( __CLASS__, 'plugin_widgets_init' ) );
            //add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
            add_filter( 'the_content', array( __CLASS__, 'change_content' ), 999 );
            add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
			add_filter( 'posts_where' , array( __CLASS__, 'posts_where_statement') );
			add_filter('posts_orderby', array( __CLASS__, 'edit_posts_orderby'));
			add_filter('posts_join_paged', array( __CLASS__, 'edit_posts_join_paged'));
			//add_filter('posts_groupby', array( __CLASS__, 'edit_posts_groupby'));

            add_action( 'wp_ajax_izw_search_ajax', array( __CLASS__, 'izw_search_ajax' ) );
            add_action( 'wp_ajax_nopriv_izw_search_ajax', array( __CLASS__, 'izw_search_ajax' ) );
            //add_action( 'izw_cron_notification', array( __CLASS__, 'do_cron_notification' ) );

            add_action('init', array( __CLASS__, 'custom_rewrite_rule' ), 10, 0);
            add_filter('query_vars', array( __CLASS__, 'hh_add_query_vars' ) );
            //add_filter('template_include', array( __CLASS__, 'hh_search_display' ) );
            add_action( 'template_redirect', array( __CLASS__, 'hh_redirect' ) );
            add_filter( 'wp_title', array( __CLASS__, 'hh_change_title' ), 999, 2 );

            add_action( 'after_setup_theme', array( __CLASS__, 'my_ag_child_theme_setup' ) );

            register_activation_hook( __FILE__, array( __CLASS__, 'install' ) );
            register_deactivation_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
		
        }
        function add_boxes(){
            add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
        }
        function add_meta_box(){
            add_meta_box(
                'postcustom',
                __('Custom Fields'),
                array(__CLASS__, 'post_custom_meta_box'),
                'program'
            );
        }
        function do_cron_notification(){
            global $wpdb;
            $izw_notification = $wpdb->prefix.'subscription';
            $izw_sort_filter = $wpdb->prefix.'izw_sort_filter';
            $all_subscription = $wpdb->get_results( "SELECT * FROM `{$izw_notification}`" );
            foreach( $all_subscription as $u ){
                $sql = "SELECT COUNT(*) FROM `$izw_sort_filter`
                WHERE `condition` LIKE '%{$u->search_condition}%'
                AND `country` LIKE '%{$u->search_country}%'";
                if( $wpdb->get_var( $sql ) ){
                    $body = '<h1>Your search notification has update</h1>';
                    $body .= 'The condition <b>'.$u->search_condition.'</b> in '. $u->search_country . ' have update!';
                    wp_mail( $u->email, 'Your search notification has update', $body );
                    $wpdb->delete( $izw_notification, array( 'id'=> $u->id));
                }
            }
        }

		function edit_posts_groupby($groupby) {
			global $wpdb, $is_search_program;
			if($is_search_program ){
				$groupby = "{$wpdb->prefix}term_relationships.term_taxonomy_id";
			}
			return $groupby;
		}		
		
		function edit_posts_join_paged($join_paged_statement) {
			global $wpdb, $is_search_program;
			
			if($is_search_program && !empty($_GET['include_trial']) ){
				$join_paged_statement .= " INNER JOIN {$wpdb->prefix}term_relationships ON ({$wpdb->prefix}posts.ID = {$wpdb->prefix}term_relationships.object_id)";
			}
			return $join_paged_statement;	
		}

		function edit_posts_orderby($orderby_statement) {
			global $wpdb, $is_search_program;
			if($is_search_program ){
				$orderby_statement = "{$wpdb->prefix}term_relationships.term_taxonomy_id ASC";
			}
			return $orderby_statement;
		}

 
		function posts_where_statement( $where ) {
			//gets the global query var object
			global $wp_query;
			 
			if(isset( $_REQUEST['izweb-search'] ) && !empty($_REQUEST['condition']) ){
				$where = str_replace("wp_postmeta.meta_key = 'condition'", "( wp_postmeta.meta_key = 'condition' OR wp_postmeta.meta_key = 'intervention_name')", $where);
			}
			 
			return $where;
		}

        /**
         * Init Plugin
         */
        function init(){
            Izweb_Import::defines();
            Izweb_Import::includes();
            Izweb_Import::register_post_type();
            Izweb_Import::register_taxonomy();
            Izweb_Import::load_plugin_textdomain();
            if( isset( $_REQUEST['update_sort']) && $_REQUEST['update_sort'] == 'yes'){
                wp_enqueue_script( 'jquery' );
                include("update-filters.php");
                die();
            }
            if( isset( $_REQUEST['update_auto_complete'] ) && $_REQUEST['update_auto_complete'] == 'yes' ){
                $page = $_REQUEST['page'] ? $_REQUEST['page'] : 1;
                //wirte_text_autocomplete( 'condition', $page );
                hh_update_autocomplete_file();
                die();
            }
            if( isset( $_REQUEST['update_sort']) && $_REQUEST['update_sort'] == 'update'){
                $page = $_REQUEST['page'] ? $_REQUEST['page'] : 1;
                update_data_filter($page);
                die();
            }
            if( isset( $_REQUEST['check_cron']) ){
                Izweb_Import::do_cron_notification();
                die();
            }

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
        }

        /**
         * Admin Plugin Style And Script
         */
        function admin_plugin_scripts(){
            wp_enqueue_script( 'izweb-import', plugin_dir_url( __FILE__ )."assets/admin/js/izweb-import.js", array( 'jquery' ),'1.0.0',true );
            wp_enqueue_style( 'izweb-import', plugin_dir_url( __FILE__ )."assets/admin/css/style.css" );
        }

        /**
         * Front-End Plugin Style And Script
         */
        function front_plugin_scripts(){
            wp_enqueue_style( 'izweb-import-front', plugin_dir_url( __FILE__ )."assets/front-end/css/style.css" );
            wp_enqueue_style( 'izweb-import-ui', plugin_dir_url( __FILE__ )."assets/front-end/css/jquery-ui.css" );
            wp_enqueue_style( 'izweb-chosen', plugin_dir_url( __FILE__ )."assets/front-end/css/chosen.css" );
            wp_enqueue_script( 'izweb-import-ui', plugin_dir_url( __FILE__ )."assets/front-end/js/jquery-ui.js", array( 'jquery' ) );
            wp_enqueue_script( 'izweb-import-overwrite', plugin_dir_url( __FILE__ )."assets/front-end/js/izw-import.js", array( 'jquery' ), '1.0.0', false );
            wp_enqueue_script( 'izweb-validate', plugin_dir_url( __FILE__ )."assets/front-end/js/jquery.validate.js", array( 'jquery' ), '1.0.0', true );
            wp_enqueue_script( 'izweb-chosen', plugin_dir_url( __FILE__ )."assets/front-end/js/chosen.jquery.js", array( 'jquery' ), '1.0.0', true );
            wp_enqueue_script( 'izweb-prism', plugin_dir_url( __FILE__ )."assets/front-end/js/prism.js", array( 'jquery' ), '1.0.0', true );
        }

        /**
         * Admin Menu
         */
        function admin_menu(){
            if ( is_admin() ) {
                remove_meta_box('postcustom', 'program', 'normal');
                add_action( 'load-post.php', array( __CLASS__, 'add_boxes' ) );
                add_action( 'load-post-new.php', array( __CLASS__, 'add_boxes' ) );
            }
            add_submenu_page( 'edit.php?post_type=program', __( 'Import Settings', __TEXTDOMAIN__ ), __( 'Import Settings', __TEXTDOMAIN__ ), 'manage_options', 'izweb-import-setting', array( __CLASS__, 'import_page' ));
            add_submenu_page( 'edit.php?post_type=program', __( 'Select fields', __TEXTDOMAIN__ ), __( 'Select fields', __TEXTDOMAIN__ ), 'manage_options', 'izweb-import-fields', array( __CLASS__, 'setting_page' ) );
            add_submenu_page( 'edit.php?post_type=program', __( 'Remove posts', __TEXTDOMAIN__ ), __( 'Remove posts', __TEXTDOMAIN__ ), 'manage_options', 'izweb-import-remove-posts', array( __CLASS__, 'remove_posts_page' ) );
            add_submenu_page( 'edit.php?post_type=program', __( 'Search filters', __TEXTDOMAIN__ ), __( 'Search filters', __TEXTDOMAIN__ ), 'manage_options', 'izweb-import-search-filters', array( __CLASS__, 'search_filters_page' ) );
            add_submenu_page( 'edit.php?post_type=program', __( 'Template Settings', __TEXTDOMAIN__ ), __( 'Template Settings', __TEXTDOMAIN__ ), 'manage_options', 'izweb-template-settings', array( __CLASS__, 'template_settings' ) );
        }
        function post_custom_meta_box($post) {
            ?>
            <div id="postcustomstuff">
                <div id="ajax-response"></div>
                <?php
                $metadata = has_meta($post->ID);
                foreach ( $metadata as $key => $value ) {
                    if ( is_protected_meta( $metadata[ $key ][ 'meta_key' ], 'post' ) || ! current_user_can( 'edit_post_meta', $post->ID, $metadata[ $key ][ 'meta_key' ] ) )
                        unset( $metadata[ $key ] );
                }
                list_meta( $metadata );
                hh_meta_form( $post ); ?>
            </div>
            <p><?php _e('Custom fields can be used to add extra metadata to a post that you can <a href="http://codex.wordpress.org/Using_Custom_Fields" target="_blank">use in your theme</a>.'); ?></p>
        <?php
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
         * Display Search Fields Page
         */
        function search_filters_page(){
            wp_enqueue_style('dashboard');
            wp_enqueue_script('dashboard');
            include "includes/admin/search-filters.php";
        }
        function template_settings(){
            include "templates/template-settings.php";
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
         * Register Taxonomy
         */
        function register_taxonomy(){
            do_action( 'izweb_before_register_taxonomy');
            // Add new taxonomy, NOT hierarchical (like tags)
            $labels = array(
                'name'                       => _x( 'Categories', 'program_cat' ),
                'singular_name'              => _x( 'Category', 'program_cat' ),
                'search_items'               => __( 'Search Categories' ),
                'popular_items'              => __( 'Popular Categories' ),
                'all_items'                  => __( 'All Categories' ),
                'parent_item'                => null,
                'parent_item_colon'          => null,
                'edit_item'                  => __( 'Edit Category' ),
                'update_item'                => __( 'Update Category' ),
                'add_new_item'               => __( 'Add New Category' ),
                'new_item_name'              => __( 'New Category Name' ),
                'separate_items_with_commas' => __( 'Separate categories with commas' ),
                'add_or_remove_items'        => __( 'Add or remove categories' ),
                'choose_from_most_used'      => __( 'Choose from the most used categories' ),
                'not_found'                  => __( 'No categories found.' ),
                'menu_name'                  => __( 'Categories' ),
            );

            $args = array(
                'hierarchical'          => true,
                'labels'                => $labels,
                'show_ui'               => true,
                'show_admin_column'     => true,
                'query_var'             => true,
                'rewrite'               => array( 'slug' => 'program_cat' ),
            );

            register_taxonomy( 'program_cat', 'program', apply_filters( 'izweb_register_taxonomy_program_cat', $args ) );
            do_action( 'izweb_after_register_taxonomy');
        }

        /**
         * Install Plugin
         */
        function install(){
            global $wpdb;
            $db_version = get_option( 'izweb_import_db_version');
            if( empty( $db_version ) || version_compare( __DBVERSION, $db_version ) > 0 ){
                $table = $wpdb->prefix.'remove_posts';
                $izw_sort_filter = $wpdb->prefix.'izw_sort_filter';
                $SQL = "CREATE TABLE IF NOT EXISTS `{$table}` (
                          `ID` int(11) NOT NULL AUTO_INCREMENT,
                          `date_import` datetime NOT NULL,
                          `type` int(11) NOT NULL,
                          `amount` int(11) NOT NULL,
                          `status` int(11) NOT NULL,
                          PRIMARY KEY (`ID`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
                $alterTable = "ALTER TABLE {$table}
                               ADD file_name varchar(255)";
                $wpdb->query( $alterTable );

                $SQL2 = "CREATE TABLE IF NOT EXISTS `{$izw_sort_filter}` (
                          `ID` int(11) NOT NULL AUTO_INCREMENT,
                          `post_id` int(11) NOT NULL,
                          `post_status` varchar(255) NOT NULL,
                          `drug` varchar(255) NOT NULL,
                          `condition` varchar(255) NOT NULL,
                          `country` varchar(255) NOT NULL,
                          `study` int(11) NOT NULL DEFAULT '0',
                          `gender` varchar(255) NOT NULL,
                          `min_age` int(11) NOT NULL DEFAULT '0',
                          `max_age` int(11) NOT NULL DEFAULT '0',
                          `sponsor` varchar(255) NOT NULL,
                          PRIMARY KEY (`ID`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

                $izw_notification = $wpdb->prefix."subscription";
                $SQL3 = "CREATE TABLE IF NOT EXISTS `{$izw_notification}` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `email` varchar(255) NULL,
                          `search_condition` varchar(255) NULL,
                          `search_country` varchar(255) NOT NULL,
                          `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          `device_token` varchar(255) NULL,
                          `device_type` varchar(255) NULL,
                          PRIMARY KEY (`ID`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $SQL );
                dbDelta( $SQL2 );
                dbDelta( $SQL3 );
                update_option( 'izweb_import_db_version', __DBVERSION );
            }
            /**
             * Cron Job
             */
            //wp_schedule_event( time(), 'hourly', 'izw_cron_notification' );
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
            global $wpdb;
            $table = $wpdb->prefix.'remove_posts';
            $current_user = wp_get_current_user();
            if( isset( $_POST['izw-remove-all-posts'] ) ){
                $args = array(
                    'post_type' => 'program',
                    'posts_per_page' => -1
                );
                $pr = new WP_Query( $args );
                if($pr->have_posts()){
                    while($pr->have_posts()){
                        $pr->the_post();
                        wp_delete_post( get_the_ID() );
                    }
                }
            }
            if( isset( $_POST['izw-import'] ) ){
                $custom_fields = get_option( 'izweb_custom_fields' );
                $post_title = get_option( 'izweb_import_title' );
                $post_excerpt = get_option( 'izweb_post_excerpt' );
                $post_content = get_option( 'izweb_import_content' );
                preg_match_all("/\[[^\]]*\]/", $post_content, $matches);
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
                    /*if ($_FILES["file-import"]["size"] > 500000) {
                        $error->add( 'file_size', __( "Sorry, your file is too large", __TEXTDOMAIN__ ) );
                    }*/
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
                            $wpdb->insert( $table, array('date_import'=> date( 'Y-m-d H:i:s' ), 'type' => $_POST['cat'], 'status' => 0,'amount' => 0,'file_name' => basename($_FILES["file-import"]["name"]) ) );
                            $remove_id = $wpdb->insert_id;
                            $zip->extractTo($folder);
                            $zip->close();
                            unlink($folder."/".basename($_FILES["file-import"]["name"]));
                            $handle = opendir($folder);
                            $i=0;
                            while ($f = readdir($handle)) {
                                if ($f != "." && $f != "..") {
                                    if( is_dir( $folder."/".$f )) continue;
                                    $doc = new DOMDocument();
                                    $doc->load( $folder."/".$f );
                                    if( !empty( $custom_fields ) && is_array( $custom_fields ) && !empty($post_title) && !empty( $post_content ) ){
                                        $defaults = array(
                                            'post_status'           => 'publish',
                                            'post_type'             => 'program',
                                            'post_excerpt'          => trim( stripslashes( $doc->getElementsByTagName( $post_excerpt )->item(0)->nodeValue ) ),
                                            'post_author'           => $current_user->ID,
                                            'ping_status'           => get_option('default_ping_status'),
                                            'post_title'            => trim( stripslashes( $doc->getElementsByTagName( $post_title )->item(0)->nodeValue ) ),
                                            'post_content'          => stripslashes( $post_content )
                                        );
                                        $defaults = apply_filters( 'izweb_insert_arg_default', $defaults );
                                        $postid = wp_insert_post( $defaults );
                                        wp_set_post_terms( $postid, array( (int)$_POST['cat'] ), 'program_cat' );
                                        if( $postid ){
                                            $i++;
                                            foreach( $custom_fields as $field=>$v){
                                                update_post_meta( $postid, $v, trim( stripslashes( $doc->getElementsByTagName( $v )->item(0)->nodeValue ) ) );
                                            }
                                            foreach( $matches[0] as $row){
                                                $field_key = ltrim($row, "[");
                                                $field_key = rtrim($field_key, "]");
                                                update_post_meta( $postid, $field_key, trim(stripslashes( $doc->getElementsByTagName( $field_key )->item(0)->nodeValue ) ) );
                                                update_post_meta( $postid, 'izweb_remove_id', $remove_id );
                                            }
                                            update_data_filter(1, $postid);
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
                wirte_text_autocomplete( 'condition' );
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

        /**
         * Load plugin textdomain
         */
        public function load_plugin_textdomain() {
            $locale = apply_filters( 'plugin_locale', get_locale(), 'izweb-import' );
            $dir    = trailingslashit( WP_LANG_DIR );

            /**
             * Admin Locale. Looks in:
             *
             * 		- WP_LANG_DIR/woocommerce/woocommerce-admin-LOCALE.mo
             * 		- WP_LANG_DIR/plugins/woocommerce-admin-LOCALE.mo
             */
            if ( is_admin() ) {
                load_textdomain( 'woocommerce', $dir . 'izweb-import/izweb-import-admin-' . $locale . '.mo' );
                load_textdomain( 'woocommerce', $dir . 'plugins/izweb-import-admin-' . $locale . '.mo' );
            }

            /**
             * Frontend/global Locale. Looks in:
             *
             * 		- WP_LANG_DIR/woocommerce/woocommerce-LOCALE.mo
             * 	 	- woocommerce/i18n/languages/woocommerce-LOCALE.mo (which if not found falls back to:)
             * 	 	- WP_LANG_DIR/plugins/woocommerce-LOCALE.mo
             */
            load_textdomain( 'izweb-import', $dir . 'izweb-import/izweb-import-' . $locale . '.mo' );
            load_plugin_textdomain( 'izweb-import', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
        }

        /**
         * Widget Init
         */
        function plugin_widgets_init(){
            register_sidebar( array(
                'name' => __( 'Program Sidebar', 'izw-import' ),
                'id' => 'izw-program',
                'description' => __( 'Widgets in this area will be shown below program single page', 'izw-import' ),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div>',
                'before_title' => '<h4>',
                'after_title' => '</h4>',
            ) );
            register_sidebar( array(
                'name' => __( 'Below Search Sidebar', 'izw-import' ),
                'id' => 'izw-below-search',
                'description' => __( 'Widgets in this area will be shown below program search page', 'izw-import' ),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div>',
                'before_title' => '<h1>',
                'after_title' => '</h1>',
            ) );
            register_sidebar( array(
                'name' => __( 'Result Sidebar', 'izw-import' ),
                'id' => 'izw-result-sidebar',
                'description' => __( 'Widgets in this area will be shown in search results', 'izw-import' ),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div>',
                'before_title' => '<h4>',
                'after_title' => '</h4>',
            ) );
            register_sidebar( array(
                'name' => __( 'Footer Program', 'izw-import' ),
                'id' => 'izw-footer-program',
                'description' => __( 'Widgets in this area will be shown in footer of program page', 'izw-import' ),
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget'  => '</div>',
                'before_title' => '<h4>',
                'after_title' => '</h4>',
            ) );
        }

        /**
         * Add Break Line for Content
         * @param $content
         * @return mixed|string
         */
        function change_content( $content ){
            if ( is_singular('program') ){
                global $post;
                preg_match_all("/\[[^\]]*\]/", $content, $matches );
                $fields_key = array();
                $fields_value = array();
                foreach( $matches[0] as $row){
                    $field_key = ltrim($row, "[");
                    $field_key = rtrim($field_key, "]");
                    $fields_key[] =  $row;
                    $fields_value[] =  nl2br( preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", get_post_meta( $post->ID, $field_key, true ) ) );
                }
                $content = stripslashes( str_replace( $fields_key, $fields_value, $content ) );
            }
            return $content;
        }
        /**
         * Display Remove Posts
         */
        function remove_posts_page(){
             include "includes/admin/remove-posts-page.php";
        }

        /**
         * Add Admin Notices
         */
        function admin_notices(){
            if( isset( $_REQUEST['remove']) && $_REQUEST['remove'] == 'yes'){
                ?>
                <div class="updated">
                    <p><?php esc_html_e( 'Remove successfully!', 'izweb-import' ); ?></p>
                </div>
            <?php
            }
        }

        /**
         * Search Ajax Function
         */
        function izw_search_ajax(){
            global $wpdb;

            $sql = "SELECT A.meta_value FROM  `{$wpdb->postmeta}` A, `{$wpdb->posts}` B
                WHERE A.post_id=B.ID
                AND B.post_type='program'
                AND A.meta_key='condition'";
            $results = $wpdb->get_col($sql);
            $conditions =  array_unique( $results );
            echo implode( ",", $conditions );
            die();
        }

        /**
         * Add Rewrite Rule
         */
        function custom_rewrite_rule() {
            $page_id = get_option( '_izw_template_id');
            $page_id = $page_id ? $page_id : 0;
            $post_name = get_post( $page_id )->post_name;
            add_rewrite_rule('^'.$post_name.'/([^/]*)/([^/]*)/?','index.php?page_id='.$page_id.'&advanced_search=$matches[1]&country=$matches[2]','top');
            add_rewrite_rule('^'.$post_name.'/([^/]*)/?','index.php?page_id='.$page_id.'&advanced_search=$matches[1]','top');
            add_rewrite_endpoint( 'advanced_search', EP_PERMALINK | EP_PAGES );
            add_rewrite_endpoint( 'country', EP_PERMALINK | EP_PAGES );
            flush_rewrite_rules();
        }

        /**
         * Add Query Var From New Rewrite Rule
         *
         * @param $vars
         * @return array
         */
        function hh_add_query_vars($vars) {
            $vars[] = 'advanced_search';
            $vars[] = 'country';
            return $vars;
        }

        /**
         * Include Search Page
         *
         * @param $include
         * @return string
         */
        function hh_search_display( $include ) {
            $standard_search = get_query_var('advanced_search');
            if( $standard_search != ''){
                $include = __IZIPPATH__.'templates/search.php';
            }
            return $include;
        }

        /**
         * Redirect To Search Advanced Page
         */
        function hh_redirect(){
            $permalink = get_option('permalink_structure');
            if( empty( $permalink )  ) return;
            $page_id = get_option( '_izw_template_id'); $page_id = $page_id ? $page_id : 0;
            if( $page_id == 0 ) return;
            if( isset( $_REQUEST['advanced_search'] ) ){
                $key = str_replace( " ", "-", $_REQUEST['advanced_search'] );
                $data = array(
                    'study' => @$_REQUEST['study'],
                    'gender' => @$_REQUEST['gender'],
                    'age' => @$_REQUEST['age'],
                );
                $key_search = array();
                foreach( $data as $k=>$v ){
                    if( !empty( $v ) ){
                        $key_search[$k] = str_replace( " ", "-", $v );
                    }
                }
                $url = '/'.get_post( $page_id )->post_name.'/'.$key;
                if( isset( $_REQUEST['country'] ) ){
                    $url .= '/'.str_replace( " ", "-", $_REQUEST['country'] );
                }
                wp_redirect( add_query_arg( $key_search, home_url( $url ) ) );
                exit();
            }
        }

        /**
         * Change Title in Search Advanced Page
         * @param $title
         * @param $sep
         * @return string
         */
        function hh_change_title( $title, $sep ){
            $condition = get_query_var( 'advanced_search' );
            if( !empty( $condition ) ){
                $country = get_query_var( 'country' );
                if( $country == '0'){
                    $country = "All countries";
                }
                if( $condition == 'hh_search'){
                    $condition = "All";
                }
                $title = 'Search Results for ';
                $title .= str_replace( "-", " ", $condition );
                $title .= ", ".str_replace( "-", " ", $country );
                $title .= " ";
            }
            return $title;
        }

        /**
         * Setup milestone shortcode
         */
        function my_ag_child_theme_setup() {
            remove_shortcode( 'milestone' );
            add_shortcode('milestone', array( __CLASS__, 'my_nectar_milestone' ) );
        }

        /**
         * Change milestone shortcode
         * @param $atts
         * @param null $content
         * @return string
         */
        function my_nectar_milestone($atts, $content = null) {
            extract(shortcode_atts(array("subject" => '', 'symbol' => '', 'symbol_position' => 'after','terms'=>'', 'counter_type'=>'', 'number' => '0', 'color' => 'Default'), $atts));

            if(!empty($symbol)) {
                $symbol_markup = 'data-symbol="'.$symbol.'" data-symbol-pos="'.$symbol_position.'"';
            } else {
                $symbol_markup = null;
            }
            if( $terms != ''){
                $number = do_shortcode( "[counter_program terms='{$terms}']" );
            }

            $number_markup = '<div class="number '.strtolower($color). " " .$counter_type.'"><span>'.$number.'</span></div>';
            $subject_markup = '<div class="subject">'.$subject.'</div>';

            return do_shortcode( '<div class="nectar-milestone" '.$symbol_markup.'> '.$number_markup.' '.$subject_markup.' </div>' );
        }
    }
    new Izweb_Import();
endif;
