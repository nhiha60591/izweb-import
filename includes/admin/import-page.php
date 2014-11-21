<div id="izweb-setting-page" class="wrap">
    <h1><?php _e( "Settings", __TEXTDOMAIN__ ) ?></h1>
    <?php do_action( 'izweb_before_setting_page'); ?>
    <?php include "import-form.php"; ?>
    <?php do_action( 'izweb_after_setting_page'); ?>
</div>