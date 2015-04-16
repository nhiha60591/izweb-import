<?php
    ob_start(); $countries = izw_all_countries();
$condition = get_query_var('advanced_search');
$country = get_query_var('country');
if ($condition == 'hh_search' || $condition == '0') {
    $condition = '';
}
$condition = trim(str_replace("-", " ", $condition));
$country = trim(str_replace("-", " ", @$country));
$data_search = array(
    'drug_condition' => @$condition,
    'country' => @$country,
    'study' => @$_REQUEST['study'],
    'gender' => @$_REQUEST['gender'],
    'age_group' => @$_REQUEST['age'],
);
?>
<form name="advanced-search" action="" method="get" id="advanced-search">
    <div id="hh_search" style="margin-bottom: 10px;">
        <input type="text" name="advanced_search" id="hh-search-key" value="<?php echo @$data_search['drug_condition'] ; ?>"
               placeholder="Search for Drug or Condition…"/>
        <input type="image" src="<?php echo __IZIPURL__; ?>assets/front-end/images/search.png"/>

        <div class="clear"></div>
    </div>
    <label for="study">Study:</label>
    <select name="study" id="study">
        <option <?php selected( $data_search['study'], '0'); ?> value="0">Clinical Trial & Early Access Program's</option>
        <?php
        $terms = get_terms('program_cat');
        foreach ($terms as $term):
            ?>
            <option <?php selected( $data_search['study'], $term->term_id ); ?> value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
        <?php endforeach; ?>
    </select>
    <label for="gender">Gender:</label>
    <select name="gender" id="gender">
        <option <?php selected( $data_search['gender'], 'Both' ); ?> value="Both">Both</option>
        <option <?php selected( $data_search['gender'], 'Male' ); ?> value="Male">Male</option>
        <option <?php selected( $data_search['gender'], 'Female' ); ?> value="Female">Female</option>
    </select>
    <label for="">Age:</label>
    <select name="age" id="age">
        <option <?php selected( $data_search['age_group'], '0' ); ?> value="0">Any</option>
        <option <?php selected( $data_search['age_group'], '0-17' ); ?> value="0-17">0-17</option>
        <option <?php selected( $data_search['age_group'], '18-65' ); ?> value="18-65">18-65</option>
        <option <?php selected( $data_search['age_group'], '65+' ); ?> value="65+">65+</option>
    </select>
    <label for="country">Country:</label>
    <select name="country" id="country">
        <option <?php selected( $data_search['country'], '0' ); ?> value="0">All</option>
        <?php foreach ($countries as $v): ?>
            <option <?php selected( $data_search['country'], $v ); ?> <?php selected(str_replace("\'", "'", $data_search['country']), str_replace("\'", "'", $v)); ?>
                value="<?php echo $v; ?>"><?php echo $v; ?></option>
        <?php endforeach; ?>
    </select>
</form>
<style type="text/css">
    #advanced-search {
        text-align: center;
    }

    #advanced-search .chosen-container {
        text-align: left;
    }

    #advanced-search select {
        width: auto;
        margin: 0 10px;
    }

    #advanced-search select:first-child {
        margin-left: 0px;
    }
    .hh-search-box{
        z-index: 999999;
    }
</style>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        function unique(list) {
            var result = [];
            $.each(list, function (i, e) {
                if ($.inArray(e, result) == -1) result.push(e);
            });
            return result;
        }

        var data = {
            'action': 'izw_search_ajax'
        };
        var k2 = null;
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function (response) {
            $(".izw-overLay").hide();
            k2 = response.split(',');
            k2 = unique(k2);
            $("#hh-search-key").autocomplete({
                source: function (request, response) {
                    var re = $.ui.autocomplete.escapeRegex(request.term);
                    var matcher = new RegExp("^" + re, "i");
                    var a = $.grep(k2, function (item, index) {
                        return matcher.test(item);
                    });
                    response(a.slice(0, 5));
                },
                autoFocus: true
            })
        });
        $("#country").chosen();
        $("#gender").chosen({disable_search_threshold: 10});
        $("#age").chosen({disable_search_threshold: 10});
        $("#study").chosen({disable_search_threshold: 10});
        $("#advanced-search").validate({
            rules: {
                search: "required"
            },
            messages: {
                search: "Please enter your drug or condition"
            }
        });
    });
</script>
<?php return ob_get_clean(); ?>