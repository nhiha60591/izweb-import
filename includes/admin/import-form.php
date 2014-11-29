<form name="import-file" action="" method="post" enctype="multipart/form-data">
    <div class="izweb-import-content">
        <p><?php _e( "Select location of zip file ( =collection of XML files )", __TEXTDOMAIN__ ); ?></p>
        <input type="file" name="file-import" />
        <p>
        <label>Select category: </label>
        <?php $args = array(
            'show_option_all'    => '',
            'show_option_none'   => '',
            'orderby'            => 'ID',
            'order'              => 'ASC',
            'show_count'         => 0,
            'hide_empty'         => 0,
            'child_of'           => 0,
            'exclude'            => '',
            'echo'               => 1,
            'selected'           => 0,
            'hierarchical'       => 0,
            'name'               => 'cat',
            'id'                 => '',
            'class'              => 'postform',
            'depth'              => 0,
            'tab_index'          => 0,
            'taxonomy'           => 'program_cat',
            'hide_if_empty'      => false,
        );
        wp_dropdown_categories( $args );
        ?>
        </p>
        <?php submit_button( __( "Load XML", __TEXTDOMAIN__ ), 'primary', 'izw-import' ); ?>
        <p><?php _e( "You can manually remove all posts by pressing the button below", __TEXTDOMAIN__ ); ?></p>
        <?php submit_button( __( "Remove all posts", __TEXTDOMAIN__ ), 'primary', 'izw-remove-all-posts' ); ?>
    </div>
</form>