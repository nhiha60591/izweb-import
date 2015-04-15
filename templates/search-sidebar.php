<?php ob_start(); ?>
<div class="hh_notice">
    <img src="<?php echo __IZIPURL__; ?>assets/front-end/images/mt_alert_banner.gif">
    <h3>Be the first to know when a new drug for this condition is available</h3>
</div>
<?php
$condition = get_query_var('advanced_search');
$country = get_query_var('country');
if ($condition == 'hh_search' || $condition == '0') {
    $condition = '';
}
$condition = trim(str_replace("-", " ", $condition));
$country = trim(str_replace("-", " ", @$country));
?>
<!--END .hh_notice -->
<div class="hh_notification">
    <form name="notification" action="" method="post" id="notification">
        <p>
            <label for="noti_condition">Condition:</label>
            <input type="text" name="noti_condition" id="noti_condition"
                   value="<?php print str_replace("\'", "'", $condition ); ?>"/>
        </p>

        <p>
            <label for="noti_country">Country:</label>
            <input type="text" name="noti_country" id="noti_country"
                   value="<?php print str_replace("\'", "'", $country); ?>"/>
        </p>

        <p>
            <label for="noti_email">Email:</label>
            <input type="text" name="noti_email" id="noti_email" value=""/>
        </p>

        <p>
            <input type="submit" name="send_mail" value="Sign-up"/>
        </p>
    </form>
</div>
<!-- END .hh_notification -->
<div class="hh_counter">
    <?php echo do_shortcode( '[milestone symbol_position="after" color="Accent-Color" terms="exclude-clinical-trials" counter_type="eap" subject="Ongoing Early Access Programs" symbol=""]'); ?>

    <?php echo do_shortcode( '[milestone symbol_position="after" color="Extra-Color-1" terms="include-clinical-trials" counter_type="c" subject="Ongoing Clinical Trials" symbol=""]');?>
    <?php dynamic_sidebar('izw-result-sidebar'); ?>
</div>
<!-- END .hh_counter -->
<?php return ob_get_clean(); ?>