<form name="import-file" action="" method="post" enctype="multipart/form-data">
    <div class="izweb-import-content">
        <p><?php _e( "Select location of zip file ( =collection of XML files )", __TEXTDOMAIN__ ); ?></p>
        <input type="file" name="file-import" />
        <?php submit_button( __( "Load XML", __TEXTDOMAIN__ ), 'primary', 'izw-import' ); ?>
        <p><?php _e( "You can manually remove all posts by pressing the button below", __TEXTDOMAIN__ ); ?></p>
        <?php submit_button( __( "Remove all posts", __TEXTDOMAIN__ ), 'primary', 'izw-remove-all-posts' ); ?>
    </div>
</form>