<div id="izweb-setting-page" class="wrap">
    <h1><?php _e( "Settings", __TEXTDOMAIN__ ) ?></h1>
    <?php do_action( 'izweb_before_setting_page'); ?>
    <form name="import-file" action="" method="post" enctype="multipart/form-data">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"></th>
                <td></td>
            </tr>
            </tbody>
        </table>
    </form>
    <?php do_action( 'izweb_after_setting_page'); ?>
</div>