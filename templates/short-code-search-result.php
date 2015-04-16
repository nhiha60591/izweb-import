<?php ob_start(); ?>
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
            $perpage = 10;
            $hh_p = $page;
            $start = $page * $perpage;
            $post_ids = $wpdb->get_col($search_sql . $where . " ORDER BY `study` ASC LIMIT {$start},{$perpage}");
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
                    $sprogram = get_post($id);
                    $terms = wp_get_post_terms( $id , 'program_cat');
                    $termslug = array();
                    foreach ($terms as $term) {
                        $termslug[] = $term->slug;
                    } ?>
                    <div class="izweb-item <?php echo implode(" ", $termslug); ?>">
                        <div class="izweb-item-left">
                            <div class="post-title">
                                <a class="izw-title-link"
                                   href="<?php echo get_the_permalink( $id ); ?>"><?php echo _substr($sprogram->post_title, 70); ?></a>
                            </div>
                            <div class="post-content">
                                <?php do_action('izweb_before_search_content', $id); ?>
                                <?php
                                $exc_text = get_post_meta($id, 'post_excerpt', true);
                                print_r($exc_text);
                                if (!empty($exc_text)) {
                                    echo _substr(strip_tags($exc_text, '<p><a>'), 300);
                                } elseif (!empty($sprogram->post_excerpt)) {
                                    echo _substr(strip_tags($sprogram->post_excerpt, '<p><a>'), 300);
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
<?php return @$error . @$search_results;
?>