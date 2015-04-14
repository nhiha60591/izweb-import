<?php
    if( isset( $_POST['izw-update'] ) ){
        update_option( '_izw_template_id', $_POST['page_id']);
    }
?>
<form name="template-settings" action="" method="post" enctype="multipart/form-data">
    <div class="template-settings-content">
        <p>
            <?php $page_id = get_option( '_izw_template_id'); $page_id = $page_id ? $page_id : 0;?>
            <label>Select page: </label>
            <?php $args = array(
                'depth'                 => 0,
                'child_of'              => 0,
                'selected'              => $page_id,
                'echo'                  => 1,
                'name'                  => 'page_id',
                'id'                    => null, // string
                'show_option_none'      => null, // string
                'show_option_no_change' => null, // string
                'option_none_value'     => null, // string
            );
            wp_dropdown_pages( $args );?>
        </p>
        <?php submit_button( __( "Update", __TEXTDOMAIN__ ), 'primary', 'izw-update' ); ?>
    </div>
</form>