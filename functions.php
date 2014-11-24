<?php
ob_start();
/**
 * Created by PhpStorm.
 * User: nhiha60591
 * Date: 11/20/14
 * Time: 8:32 AM
 */
/**
 * Check Node Exits
 * @param $node
 * @return int|null|string
 */
function check_node_exits($node){
    global $wpdb;
    $table = $wpdb->prefix."izweb_import";
    $return = 0;
    $return = $wpdb->get_var( "SELECT COUNT(ID) FROM {$table} WHERE `dom_name` = '{$node}'");
    return $return;
}

/**
 * Insert Node If Not Exits In Database
 */
function insert_data_option(){
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
                            $contents = $doc->getElementsByTagName( "clinical_study" );
                            foreach( $contents as $content ){
                                foreach( $content->childNodes as $item){
                                    if($i === 0){
                                        if(!check_node_exits($item->nodeName)){
                                            $wpdb->insert( $table, array( 'dom_name' => $item->nodeName ) );
                                        }
                                    }
                                }
                            }
                            $i++;
                            break;
                        }
                    }
                    wp_redirect( add_query_arg( array( 'page' => 'izweb-import-file', 'tab' => 'select-option' ), admin_url('admin.php') ) );
                    exit();
                } else {
                    die("There was a problem. Please try again!");
                }
            }
        }
        do_action( "izweb_after_process_import" );
    }
}
add_action( 'izweb_before_import_page', 'insert_data_option');
function get_same_data( $meta_key = '' ){
    global $wpdb;
    $content = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->postmeta} WHERE `meta_key` = '{$meta_key}'");
    return $content;
}
function _substr($str, $length, $minword = 3)
{
    $sub = '';
    $len = 0;
    foreach (explode(' ', $str) as $word)
    {
        $part = (($sub != '') ? ' ' : '') . $word;
        $sub .= $part;
        $len += strlen($part);
        if (strlen($word) > $minword && strlen($sub) >= $length)
        {
            break;
        }
    }
    return $sub . (($len < strlen($str)) ? '...' : '');
}