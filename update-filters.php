<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
    <script type="text/javascript" src="http://code.jquery.com/jquery-2.1.3.min.js"></script>
    <script type="text/javascript">
        // Closure
        (function() {
            /**
             * Decimal adjustment of a number.
             *
             * @param {String}  type  The type of adjustment.
             * @param {Number}  value The number.
             * @param {Integer} exp   The exponent (the 10 logarithm of the adjustment base).
             * @returns {Number} The adjusted value.
             */
            function decimalAdjust(type, value, exp) {
                // If the exp is undefined or zero...
                if (typeof exp === 'undefined' || +exp === 0) {
                    return Math[type](value);
                }
                value = +value;
                exp = +exp;
                // If the value is not a number or the exp is not an integer...
                if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
                    return NaN;
                }
                // Shift
                value = value.toString().split('e');
                value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
                // Shift back
                value = value.toString().split('e');
                return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
            }

            // Decimal round
            if (!Math.round10) {
                Math.round10 = function(value, exp) {
                    return decimalAdjust('round', value, exp);
                };
            }
            // Decimal floor
            if (!Math.floor10) {
                Math.floor10 = function(value, exp) {
                    return decimalAdjust('floor', value, exp);
                };
            }
            // Decimal ceil
            if (!Math.ceil10) {
                Math.ceil10 = function(value, exp) {
                    return decimalAdjust('ceil', value, exp);
                };
            }
        })();
        var izw_page = 1;
        <?php global $wpdb; ?>
        <?php
            $posts = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE `post_type` = 'program'");
        ?>
        var all_page = <?php echo ceil( $posts/10 ); ?>;
        var precent = (izw_page * 100) / all_page ;
        function ajaxFunction()
        {
            if( izw_page > all_page ){
                jQuery("#progress_bar").css({"width":"100%"});
                jQuery(".completed").html( '100' );
            }
            if( izw_page > all_page ) return;
            $.ajax( "<?php echo add_query_arg( array( 'update_sort' => 'update'), home_url() ); ?>&page="+izw_page )
                .done(function() {
                    if( izw_page <= all_page ){
                        var pr = Math.round10( (precent * izw_page), -2 );
                        if( pr > 0 ) {
                            jQuery("#progress_bar").css({"width": pr + "%"});
                            jQuery(".completed").html(pr);
                        }
                        var html = "Process " + izw_page+"/"+all_page + " success<br />";
                        jQuery(".step").append(html);
                        izw_page = izw_page + 1;
                        ajaxFunction();
                    }
                });
        }
    </script>
</head>
<body onLoad=ajaxFunction();>
<div class="wraper_pro">
    <div class="" id="progress_bar"></div>
</div>
<p style="text-align: center;margin-top: 10px; font-weight: bold;">
<span class="completed">0</span>
% Completed
</p>
<style type="text/css">
    @-webkit-keyframes progress
    {
        to {background-position: -27px 0;}
    }
    @-moz-keyframes progress
    {
        to {background-position: -27px 0;}
    }

    @keyframes progress
    {
        to {background-position: -27px 0;}
    }
    .wraper_pro{
        width: 100%;
        height: 23px;
        overflow: hidden;
        background: #d3d3d3;
        border-radius: 15px;
        text-align: center;
        position: relative;
    }
    #progress_bar{
        background: url('<?php echo __IZIPURL__ ?>progress_bar.png');
        background-color: green;
        height: 23px;
        width: 0;
        position: absolute;
        left: 0;
        top: 0;
        -webkit-animation: progress 1s linear infinite;
        -moz-animation: progress 1s linear infinite;
        animation: progress 1s linear infinite;
        background-repeat: repeat-x;
        background-size: 27px 23px;
        /*background-image: -webkit-linear-gradient(-45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
        background-image: linear-gradient(-45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);*/
    }
    .step {
        background: none repeat scroll 0 0 #d3d3d3;
        height: 500px;
        margin: auto;
        overflow: scroll;
        padding: 20px;
        width: 300px;
    }
</style>
<div class="step">

</div>
</body>
</html>