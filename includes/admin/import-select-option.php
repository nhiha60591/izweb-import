<?php
/**
 * Get Option Settings
 */
global $wpdb;
$table = $wpdb->prefix."izweb_import";
$list = $wpdb->get_results( "SELECT * FROM {$table}");
$list_option = apply_filters( 'izweb_list_option', $list );
$list_string = array();
foreach( $list_option as $row){
    $name = !empty($row->update_name) ? $row->update_name : $row->dom_name;
    $list_string[] = '<option value="'.$row->id.'">'.$name.'</option>';
}
$list_string = apply_filters( 'izweb_list_string', $list_string );
$list_string = implode( "", $list_string );
// Set Settings To Import
?>
<form name="import-file" action="" method="post" enctype="multipart/form-data">
    <table class="form-table">
        <tbody>
        <tr>
            <th scope="row"><?php _e( "Post title", __TEXTDOMAIN__ ); ?></th>
            <td>
                <select name="post_title">
                    <?php echo $list_string; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e( "Post Content", __TEXTDOMAIN__ ); ?></th>
            <td>
                <select name="post_content">
                    <?php echo $list_string; ?>
                </select>
            </td>
        </tr>
        </tbody>
    </table>
    <h3><?php _e( "Custom field", __TEXTDOMAIN__ ); ?></h3>
    <table class="form-table custom-filed">
        <tbody>
        <tr>
            <th scope="row"><?php _e( "Post title", __TEXTDOMAIN__ ); ?></th>
            <td>
                <select name="post_title">
                    <?php echo $list_string; ?>
                </select>
            </td>
        </tr>
        </tbody>
    </table>
    <button class="add-new-ctf"><?php _e( "Add new custom field", __TEXTDOMAIN__); ?></button>
    <?php submit_button( __( "Save Change", __TEXTDOMAIN__ ),'primary','hh_import'); ?>
</form>