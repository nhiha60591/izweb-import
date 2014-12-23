<?php
/**
 * Created by PhpStorm.
 * User: nhiha60591
 * Date: 11/24/14
 * Time: 3:54 PM
 */
/**
 * Add short code to search program
 */
add_shortcode( 'search_program', 'hh_search_program' );
function hh_search_program( $atts ){
    global $post;
    // Attributes
    $exc = get_option( 'izweb_post_excerpt' );
    $exc = !empty($exc) ? $exc : 'brief_summary';
    extract( shortcode_atts(
            array(
                'key1' => 'condition',
                'caption1' => 'I am looking for EAP\'s for:',
                'placeholder1' => 'e.g. Depression',
                'key2' => 'living',
                'caption2' => 'I\'m living in:',
                'placeholder2' => 'e.g. United States',
                'excerpt' => $exc
            ), $atts )
    );
    $search_results = '';
    if(isset( $_REQUEST['izweb-search'] ) ){
		global $is_search_program;
		$is_search_program = true;
        $data = array(
            $key1     => $_REQUEST[$key1],
            $key2     => $_REQUEST[$key2],
        );
        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
        $args = array(
            'post_type' => 'program',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'paged' => $paged
        );	
		
		if (empty($_GET['include_trial'])) {
			$args['tax_query'] = array(
									array(
										'taxonomy' => 'program_cat',
										'field'    => 'slug',
										'terms'    => 'exclude-clinical-trials',
									));
		}
		
        if( !empty($data[$key1]) ){
            $args['meta_query'][] = array(
                'key'     => $key1,
                'value'   => $data[$key1],
                'compare' => 'LIKE',
            );
        }
        if( !empty($data[$key2]) ){
			$args['meta_query']['relation'] = 'AND';
            $args['meta_query'][] = array(
                'key'     => $key2,
                'value'   => $data[$key2],
                'compare' => 'LIKE',
            );
        }
        $args = apply_filters( 'izweb_arg_search', $args );
        $program = new WP_Query( $args );
        ob_start();
        if( $program->have_posts() ){
            ?>
            <div class="izweb-search-results">
                <?php dynamic_sidebar('izw-below-search'); ?>
                <?php while($program->have_posts()): $program->the_post();$terms = wp_get_post_terms( get_the_ID(), 'program_cat', $args ); $termslug = array(); foreach( $terms as $term){ $termslug[] = $term->slug;}  global $post;?>
                    <div class="izweb-item <?php echo implode(" ", $termslug ); ?>">
                        <div class="izweb-item-left">
                            <div class="post-title">
                                <a class="izw-title-link" href="<?php the_permalink(); ?>"><?php echo _substr(get_the_title(), 70); ?></a>
                            </div>
                            <div class="post-content">
                                <?php do_action( 'izweb_before_search_content', get_the_ID() ); ?>
                                <?php $exc_text = get_post_meta( get_the_ID(), $excerpt, true ); if( !empty( $exc_text ) ) echo _substr(strip_tags($exc_text, '<p><a>'),300); else echo _substr(strip_tags($post->post_excerpt, '<p><a>'),300); ?>
                                <?php izweb_show_custom_field( get_the_ID() ); ?>
                                <?php do_action( 'izweb_after_search_content', get_the_ID() ); ?>
                            </div>
                        </div>
                        <div class="izweb-item-right">
                            <a class="izw-detail" href="<?php the_permalink(); ?>">Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
			<div class="search_pagination">
            <?php if( function_exists('wp_pagenavi')){
                wp_pagenavi( array( 'query' => $program) );
            }else{
                ?>
                <?php if( $program->max_num_pages > 1 ){ ?>
                    <nav class="izweb-pagination">
                        <?php if( $paged > 1): ?>
                            <a class="prev" href="<?php echo add_query_arg( array( 'paged'=> $paged-1) ); ?>">Prev</a>
                        <?php endif; ?>
                        <?php for( $i=1; $i<=$program->max_num_pages; $i++){ ?>
                            <?php if( $paged == $i ): ?>
                                <span class="page-current"><?php echo $i ?></span>
                            <?php else: ?>
                                <a href="<?php echo add_query_arg( array( 'paged'=> $i) ); ?>"><?php echo $i ?></a>
                            <?php endif ?>
                        <?php } ?>
                        <?php if( $paged < $program->max_num_pages): ?>
                            <a class="next" href="<?php echo add_query_arg( array( 'paged'=> $paged+1) ); ?>">Next</a>
                        <?php endif; ?>
                    </nav>
                <?php
                }
            }
			?>
			</div>
			<?php
        }
        // Restore original Post Data
        wp_reset_postdata();

        $search_results = ob_get_clean();
    }
    ob_start();
    ?>
    <div id="izweb-search" class="izweb-search" style="width: 100%;">
        <div class="izweb-search-form">
            <form name="" action="<?php echo get_the_permalink( $post->ID ); ?>" method="get">
                <input type="hidden" name="page_id" value="<?php echo @$_REQUEST['page_id']; ?>">
                <div class="izw-left">
                    <label for="<?php echo $key1; ?>"><?php _e( $caption1, __TEXTDOMAIN__) ?></label>
                    <input type="text" name="<?php echo $key1; ?>" id="<?php echo $key1; ?>" value="" placeholder="<?php echo $placeholder1 ?>" />
                </div>
                <div class="izw-right">
                <label for="<?php echo $key2; ?>"><?php _e( $caption2, __TEXTDOMAIN__) ?></label>
                    <input type="text" name="<?php echo $key2; ?>" id="<?php echo $key2; ?>" value="" placeholder="<?php echo $placeholder2 ?>" />
                    <input type="submit" class="nectar-button large extra-color-1 has-icon regular-button" value="<?php _e( "SEARCH", __TEXTDOMAIN__) ?>" name="izweb-search" data-color-override="false" data-hover-color-override="false" data-hover-text-color-override="#fff" />
                    <label style="display: block;">include available Clinical Trials in search <input type="checkbox" name="include_trial" value="1" <?php if (!empty($_REQUEST['include_trial'])) echo 'checked="checked"';?> /></label>
                </div>
            </form>
            <script type="text/javascript">
                jQuery(document).ready(function( $ ){
                    $(".izweb-item.include-clinical-trials").hide();
                    <?php if (!empty($_REQUEST['include_trial'])) {?>
                    $(".izweb-item.include-clinical-trials").show();
                    <?php }?>
                    $('input[name="include_trial"]').change(function (){
                        if($(this).is(":checked")){
                            $(".izweb-item.include-clinical-trials").show();
							$(".search_pagination a").each(function(){
								 this.href += "&include_trial=1";
							});
                        }else{
                            $(".izweb-item.include-clinical-trials").hide();
							$(".search_pagination a").each(function(){
								this.href = this.href.replace("&include_trial=1", "");								 
							});
                        }
                    });
                    $( "#<?php echo $key1; ?>" ).autocomplete({
                        source: function( request, response ) {
                            var data = {
                                'action': 'izw_search_ajax',
                                'meta_key': '<?php echo $key1; ?>',
                                'meta_value': request.term
                            };

                            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                            $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(tags) {
                                response(JSON.parse(tags));
                            });
                        },
                        autoFocus: true,
                        minLength: 3
                    });
                    $( "#<?php echo $key2; ?>" ).autocomplete({
                        source: function( request, response ) {
                            var data = {
                                'action': 'izw_search_ajax',
                                'meta_key': '<?php echo $key2; ?>',
                                'meta_value': request.term
                            };

                            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                            $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(tags) {
                                response(JSON.parse(tags));
                            });
                        },
                        autoFocus: true,
                        minLength: 3
                    });
                });
            </script>
        </div>
    </div><!-- END #izweb-search -->
    <?php
    echo @$search_results;
    return ob_get_clean();
}
add_shortcode( 'counter_program', 'hh_counter_program' );
function hh_counter_program( $atts ){
    extract( shortcode_atts(
            array(
                'field' => 'slug',
                'taxonomy' => 'program_cat',
                'terms' => 'include-clinical-trials'
            ), $atts )
    );
    $args = array(
        'post_type' => 'program',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => $taxonomy,
                'field'    => $field,
                'terms'    => $terms,
            ),
        ),
    );
    // Program Counter
    $program_counter = new WP_Query( $args );
    return $program_counter->post_count;
}