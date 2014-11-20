<div id="izweb-setting-page" class="wrap">
    <h1><?php _e( "Import", __TEXTDOMAIN__ ) ?></h1>
    <?php do_action( 'izweb_before_import_page'); ?>
    <?php $tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'import'; ?>
    <?php do_action( 'izw_tab_' . $tab); ?>
    <?php do_action( 'izweb_after_import_page'); ?>
</div>