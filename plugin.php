<?php
/**
 * Created by PhpStorm.
 * User: nhiha60591
 * Date: 11/20/14
 * Time: 8:32 AM
 */
/*
Plugin Name: Izweb Import Plugin
Plugin URI: http://izweb.biz/
Description: Import File from zip file
Version: 1.0.1
Author: LuckyStar
Author URI: https://github.com/nhiha60591/izweb-import/
Text Domain: izweb-import
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'Izweb_Import' ) ) :
    class Izweb_Import{
        function __construct(){
            add_action( 'init', array( $this, 'init') );
        }
        function init(){
            $this->defines();
            $this->includes();
        }
        function includes(){

        }
        function defines(){

        }
        function plugin_scripts(){

        }
    }
endif;