<?php ob_start(); $countries = izw_all_countries(); ?>
    <form name="advanced-search" action="" method="get" id="advanced-search">
        <div id="hh_search">
            <div class="row">
                <div class="col col_first span_8" style="text-align: left;">
                    <h4>Search Early Access Programs and Clinical Trials</h4>
                </div>
                <div class="col col_last span_4" style="text-align: right">
                    <a href="https://itunes.apple.com/us/app/mytomorrows/id967260185?ls=1&mt=8" target="_blank"
                       class="nectar-button small regular-button" data-color-override="false"
                       data-hover-color-override="false" data-hover-text-color-override="#fff">Iphone App</a>
                </div>
            </div>
            <input type="text" name="advanced_search" id="hh-search-key" value=""
                   placeholder="Search for Drug or Condition…"/>
            <input type="image" src="<?php echo __IZIPURL__; ?>assets/front-end/images/search.png"/>

            <div class="clear"></div>
        </div>
        <label for="study">Study:</label>
        <select name="study" id="study">
            <option value="0">Clinical Trial & Early Access Program's</option>
            <?php
            $terms = get_terms('program_cat');
            foreach ($terms as $term):
                ?>
                <option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="gender">Gender:</label>
        <select name="gender" id="gender">
            <option value="Both">Both</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
        <label for="">Age:</label>
        <select name="age" id="age">
            <option value="0">Any</option>
            <option value="0-17">0-17</option>
            <option value="18-65">18-65</option>
            <option value="65+">65+</option>
        </select>
        <label for="country">Country:</label>
        <select name="country" id="country">
            <option value="0">All</option>
            <?php foreach ($countries as $v): ?>
                <option <?php selected(str_replace("\'", "'", $data_search['country']), str_replace("\'", "'", $v)); ?>
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
            $("#gender").chosen();
            $("#age").chosen();
            $("#study").chosen();
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
    <div class="row" style="z-index: 1;position: relative;min-height: 300px;">
        <?php
        $condition = get_query_var('advanced_search');
        $country = get_query_var('country');
        if (isset($condition)) {
            global $is_search_program, $wpdb;
            if ($condition == 'hh_search' || $condition == '0') {
                $condition = '';
            }
            $condition = trim(str_replace("-", " ", $condition));
            $country = trim(str_replace("-", " ", @$country));
            $post_ids = array();
            $is_search_program = true;
            $data_search = array(
                'drug_condition' => @$condition,
                'country' => @$country,
                'study' => @$_REQUEST['study'],
                'gender' => @$_REQUEST['gender'],
                'age_group' => @$_REQUEST['age'],
            );
            $izw_sort_filter = $wpdb->prefix . 'izw_sort_filter';
            $search_sql = 'SELECT `post_id` FROM ' . $izw_sort_filter . ' WHERE 1=1';
            $where = '';
            if (isset($data_search['drug_condition']) && !empty($data_search['drug_condition'])) {
                $where .= ' AND ( `drug` LIKE "%' . $data_search['drug_condition'] . '%"';
                $where .= ' OR `condition` LIKE "%' . $data_search['drug_condition'] . '%" )';
            }

            if (isset($data_search['country']) && $data_search['country'] != '0') {
                $where .= ' AND country LIKE "%' . $data_search['country'] . '%"';
            }


            if (isset($data_search['gender']) && $data_search['gender'] != 'Both') {
                $where .= ' AND gender = "' . $data_search['gender'] . '"';
            }
            if (isset($data_search['age_group']) && $data_search['age_group'] != '0') {
                if (strpos($data_search['age_group'], '-') != FALSE) {
                    $bt = explode('-', $data_search['age_group']);
                    $where .= ' AND ( min_age BETWEEN ' . $bt[0] . ' AND ' . $bt[1] . ' )';
                } elseif (strpos($data_search['age_group'], '+') != FALSE) {
                    $bt = explode('+', $data_search['age_group']);
                    $where .= ' AND min_age >= ' . $bt[0];
                }
            }
            $exc_count = 0;
            if (isset($data_search['study']) && $data_search['study'] != '0') {
                $where .= ' AND study = ' . $data_search['study'];
            } else {
                $exc = get_term_by('name', 'Early Access Program', 'program_cat');
                $SQL = "SELECT count(*) FROM `{$izw_sort_filter}` WHERE `study` = {$exc->term_id}" . $where;
                $exc_count = $wpdb->get_var($SQL);
            }
            $count_p = $wpdb->get_col($search_sql . $where);
            $page = $_REQUEST['pg'] ? $_REQUEST['pg'] : 1;
            if ((int)$page < 0) $page = 1;
            $page = ceil($page) - 1;
            $perpage = 20;
            $hh_p = $page;
            $start = $page * $perpage;
            $post_ids = $wpdb->get_col($search_sql . $where . " ORDER BY `study` DESC LIMIT {$start},{$perpage}");
            $search_results = '';
            if (isset($data_search['study']) && $data_search['study'] != '0') {
                $exc = get_term_by('id', $data_search['study'], 'program_cat');
                if ($exc->name == "Early Access Program") {
                    $exc_count = count($count_p);
                }
            }
            ?>
            <script type="text/javascript">
                <?php foreach( $data_search as $k=>$v): if( empty( $v ) ) continue; ?>
                _gaq.push(['_trackEvent', 'common-web', 'search', '<?php echo $k; ?>']);
                <?php endforeach; ?>
            </script>
        <?php
        if (sizeof($post_ids) > 0) {
        $include = count($count_p) - $exc_count;
        ?>
            <h3 class="izw-found-mes">We have found <?php echo $exc_count; ?> Early Access Programs (green) and  <?php echo $include; ?> Clinical Trials (red)</h3>
            <div class="izweb-search-results">
                <?php dynamic_sidebar('izw-below-search'); ?>
                <?php foreach ($post_ids as $id) {
                    $post = get_post($id);
                    setup_postdata($post);
                    $terms = wp_get_post_terms(get_the_ID(), 'program_cat');
                    $termslug = array();
                    foreach ($terms as $term) {
                        $termslug[] = $term->slug;
                    } ?>
                    <div class="izweb-item <?php echo implode(" ", $termslug); ?>">
                        <div class="izweb-item-left">
                            <div class="post-title">
                                <a class="izw-title-link"
                                   href="<?php echo get_the_permalink( $id ); ?>"><?php echo _substr($post->post_title, 70); ?></a>
                            </div>
                            <div class="post-content">
                                <?php do_action('izweb_before_search_content', $id); ?>
                                <?php
                                $exc_text = get_post_meta($id, 'post_excerpt', true);
                                print_r($exc_text);
                                if (!empty($exc_text)) {
                                    echo _substr(strip_tags($exc_text, '<p><a>'), 300);
                                } elseif (!empty($post->post_excerpt)) {
                                    echo _substr(strip_tags($post->post_excerpt, '<p><a>'), 300);
                                }
                                ?>
                                <?php izweb_show_custom_field($id); ?>
                                <?php do_action('izweb_after_search_content', $id); ?>
                            </div>
                        </div>
                        <div class="izweb-item-right">
                            <a class="izw-detail" href="<?php echo get_the_permalink( $id ); ?>">Details</a>
                        </div>
                    </div>
                <?php }; ?>
            </div>
            <div class="search_pagination">
                <div class="wp-pagenavi">
                    <?php
                    $prev_page = (int)$hh_p;
                    $next_page = (int)$hh_p + 2;
                    $iz_page = ceil(count($count_p) / $perpage);
                    if ($iz_page > 1) {
                        for ($i = 1; $i <= $iz_page; $i++) {
                            if ($i == 1 && ($hh_p + 1) != 1) {
                                ?>
                                <a data-page="<?php echo $i; ?>" class="page larger izw_first"
                                   href="<?php echo add_query_arg(array('pg' => 1)); ?>">First</a>
                                <a data-page="<?php echo $i; ?>" class="page larger izw_prev"
                                   href="<?php echo add_query_arg(array('pg' => $prev_page)); ?>">Prev</a>
                            <?php
                            }
                            if ((($hh_p + 1) - $i) == 3) {
                                ?>
                                <span class="izw_more">...</span>
                            <?php
                            } elseif ((($hh_p + 1) - $i) < 3 && (($hh_p + 1) - $i) > 0) {
                                ?>
                                <a data-page="<?php echo $i; ?>" class="page larger"
                                   href="<?php echo add_query_arg(array('pg' => $i)); ?>"><?php echo $i; ?></a>
                            <?php
                            } elseif ($i == ($hh_p + 1)) {
                                ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php
                            } elseif ($i - ($hh_p + 1) > 0 && $i - ($hh_p + 1) < 3) {
                                ?>
                                <a data-page="<?php echo $i; ?>" class="page larger"
                                   href="<?php echo add_query_arg(array('pg' => $i)); ?>"><?php echo $i; ?></a>
                            <?php
                            } elseif (($i - ($hh_p + 1)) == 3) {
                                ?>
                                <span class="izw_more">...</span>
                            <?php
                            }
                            if ($i == $iz_page && ($hh_p + 1) != $iz_page) {
                                ?>
                                <a data-page="<?php echo $i; ?>" class="page larger izw_next"
                                   href="<?php echo add_query_arg(array('pg' => $next_page)); ?>">Next</a>
                                <a data-page="<?php echo $i; ?>" class="page larger izw_last"
                                   href="<?php echo add_query_arg(array('pg' => $iz_page)); ?>">Last</a>
                            <?php
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            <?php
            $search_results = ob_get_clean();
            $search_results = $search_results;
        } else {
            if ($data_search['country'] == '0') {
                $country = 'All';
            } else {
                $country = $data_search['country'];
            }
            ?>
            <div class="izw-error-mes">
                <h3>Sorry, no results were found!</h3>
                <ul class="izw-error-mes-ul">
                    <li>Try other keywords or check your spelling</li>
                    <li>Check the "include available Clinic Trials in search" option</li>
                    <li>Get notified when we have information for
                        <strong><?php print htmlspecialchars($data_search['drug_condition']); ?></strong> in
                        <strong><?php print htmlspecialchars($country); ?></strong>

                        <p style="color: green;font-size: 1.4em;"><?php echo $noti_mess; ?></p>

                        <form name="notification" action="" method="post" id="notification">
                            <p>
                                <label for="noti_condition">Condition:</label>
                                <input type="text" name="noti_condition" id="noti_condition"
                                       value="<?php print str_replace("\'", "'", $data_search['drug_condition']); ?>"/>
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
                                <input type="submit" name="send_mail" value="Send"/>
                            </p>
                        </form>
                    </li>
                </ul>
            </div>
            <?php
            $error = ob_get_clean();
        }
        // Restore original Post Data
            wp_reset_postdata();
        }
        ?>
    </div>
<?php return @$error . @$search_results;
?>