<div id="izweb-setting-page" class="wrap">
    <?php
        if( isset( $_POST['izw-save-field'] ) ){
            do_action( 'izweb_update_custom_field', $_POST );
            $option = array_filter( $_POST['field-item'] );
            update_option( 'izweb_custom_fields', $option );
            $caption = array_filter( $_POST['field-caption'] );
            update_option( 'izweb_custom_fields_cation', $caption );
            update_option( 'izweb_import_title', $_POST['post_title'] );
            update_option( 'izweb_import_content', $_POST['post_content'] );
            update_option( 'izweb_post_excerpt', $_POST['post_excerpt'] );
        }
    ?>
    <?php do_action( 'izweb_before_setting_page'); ?>
    <form name="import-file" action="" method="post" enctype="multipart/form-data">
        <h1>
            <?php _e( "Select/edit fields", __TEXTDOMAIN__ ) ?>
            <input type="submit" name="izw-add-field" id="izw-add-field" class="button button-primary" value="<?php _e( "Add a field manually", __TEXTDOMAIN__ ) ?>">
            <!--<input type="submit" name="izw-load-field-same" id="izw-load-field-same" class="button button-primary" value="<?php /*_e( "Load File Same", __TEXTDOMAIN__ ) */?>">-->
            <input type="submit" name="izw-save-field" id="izw-save-field" class="button button-primary" value="<?php _e( "Save selected fields", __TEXTDOMAIN__ ) ?>">
        </h1>
        <p class="des">
            <?php _e( "Select which of the imported fields will be displayed on myTomorrows website. Only checked rows will be displayed. Manually added fields have to filled manually using 'Edit Programs'", __TEXTDOMAIN__ ); ?>
        </p>
        <div id="upload-same-data" class="upload-same-data" style="display: none;">
            <input type="file" id="select-same-file" name="select-same-file">
            <input type="submit" id="upload-same-data-button" value="Upload Same Data">
        </div>
        <table id="form-custom-fields" class="wp-list-table widefat fixed posts">
            <thead>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></th>
                <th scope="col" id="title" class="manage-column"><?php _e( "Node name", __TEXTDOMAIN__ ); ?></th>
                <th scope="col" id="title" class="manage-column"><?php _e( "Caption", __TEXTDOMAIN__ ); ?></th>
                <th scope="col" class="manage-column" style=""><?php _e( "Field Description ( same data )", __TEXTDOMAIN__ ); ?><button id="delete-fields" class="button button-primary">Delete selected fields</button></th>
            </tr>
            </thead>
            <tfoot>
            <tr>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></th>
                <th scope="col" id="title" class="manage-column"><?php _e( "Node name", __TEXTDOMAIN__ ); ?></th>
                <th scope="col" id="title" class="manage-column"><?php _e( "Caption", __TEXTDOMAIN__ ); ?></th>
                <th scope="col" class="manage-column" style=""><?php _e( "Field Description ( same data )", __TEXTDOMAIN__ ); ?><button id="delete-fields" class="button button-primary">Delete selected fields</button></th>
            </tr>
            </tfoot>

            <tbody id="the-list">
            <?php
                do_action( 'izweb_before_list_fields');
                $data = get_option( 'izweb_custom_fields' );
                $caption = get_option( 'izweb_custom_fields_cation' );
                if( !empty( $data ) && is_array($data) ){
                    $i=0;
                    foreach($data as $row){
                        $class = $i%2!=0 ? 'alternate' : '';
                        ?>
                        <tr class="<?php echo $class; ?>">
                            <th scope="row" class="check-column">
                                <input id="cb-select-<?php echo $i; ?>" type="checkbox" value="1">
                                <div class="locked-indicator"></div>
                            </th>
                            <td>
                                <input type="text" name="field-item[<?php echo $row; ?>]" value="<?php echo stripslashes( $row ); ?>" />
                            </td>
                            <td>
                                <input type="text" name="field-caption[<?php echo $i; ?>]" value="<?php echo stripslashes( $caption[$i] ); ?>" />
                            </td>
                            <td>
                                <?php echo get_same_data( $row ); ?>
                            </td>
                        </tr>
                        <?php
                        $i++;
                    }
                }
                do_action( 'izweb_after_list_fields' );
            ?>
            </tbody>
        </table>
        <div class="more-fields">
            <h2><?php _e( "More fields", __TEXTDOMAIN__ ); ?></h2>
            <table class="form-table">
                <?php
                $post_title = get_option( 'izweb_import_title' );
                $post_content = get_option( 'izweb_import_content' );
                $post_excerpt = get_option( 'izweb_post_excerpt' );
                ?>
                <tbody>
                    <?php do_action( 'izweb_before_main_fields' ); ?>
                    <tr>
                        <th><label form="post_title"><?php _e( "Post title", __TEXTDOMAIN__ ); ?></label></th>
                        <td><input type="text" name="post_title" id="post_title" value="<?php echo (@$post_title); ?>"></td>
                    </tr>
                    <tr>
                        <th><label form="post_title"><?php _e( "Post Excerpt", __TEXTDOMAIN__ ); ?></label></th>
                        <td><input type="text" name="post_excerpt" id="post_excerpt" value="<?php echo stripslashes(@$post_excerpt); ?>"></td>
                    </tr>
                    <tr>
                        <th><label form="post_content"><?php _e( "Post content", __TEXTDOMAIN__ ); ?></label></th>
                        <td><textarea name="post_content" cols="40" rows="8" id="post_content"><?php echo stripslashes(@$post_content); ?></textarea></td>
                    </tr>
                    <?php do_action( 'izweb_after_main_fields' ); ?>
                </tbody>
            </table>
        </div>
        <input type="submit" name="izw-save-field" id="izw-save-field" class="button button-primary" value="<?php _e( "Save selected fields", __TEXTDOMAIN__ ) ?>">
    </form>
    <?php do_action( 'izweb_after_setting_page'); ?>
</div>