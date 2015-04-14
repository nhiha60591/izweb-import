<?php get_header(); ?>

<?php

global $nectar_theme_skin, $options;

$bg = get_post_meta($post->ID, '_nectar_header_bg', true);
$bg_color = get_post_meta($post->ID, '_nectar_header_bg_color', true);
$fullscreen_header = (!empty($options['blog_header_type']) && $options['blog_header_type'] == 'fullscreen' && is_singular('post')) ? true : false;
$fullscreen_class = ($fullscreen_header == true) ? "fullscreen-header full-width-content" : null;
$theme_skin = (!empty($options['theme-skin']) && $options['theme-skin'] == 'ascend') ? 'ascend' : 'default';
$hide_sidebar = (!empty($options['blog_hide_sidebar'])) ? $options['blog_hide_sidebar'] : '0';
$blog_type = $options['blog_type'];
?>
    <div class="container-wrap">

        <div class="container main-content">
            <?php if(get_post_format() != 'quote' && get_post_format() != 'status' && get_post_format() != 'aside') { ?>

                <?php if(have_posts()) : while(have_posts()) : the_post();

                    if((empty($bg) && empty($bg_color)) && $fullscreen_header != true) { ?>

                        <div class="row heading-title">
                            <div class="col span_12 section-title blog-title">
                                <h1 class="entry-title"><?php the_title(); ?></h1>

                                <div id="single-below-header">
                                    <span class="meta-author vcard author"><span class="fn"><?php echo __('By', NECTAR_THEME_NAME); ?> <?php the_author_posts_link(); ?></span></span>
                                    <?php if( !empty($options['blog_social']) && $options['blog_social'] == 1) { ?>
                                        <span class="meta-date date updated"><?php echo get_the_date(); ?></span>
                                    <?php } ?>
                                    <span class="meta-category"><?php the_category(', '); ?></span>
                                    <span class="meta-comment-count"><a href="<?php comments_link(); ?>"><?php comments_number( __('No Comments', NECTAR_THEME_NAME), __('One Comment ', NECTAR_THEME_NAME), __('% Comments', NECTAR_THEME_NAME) ); ?></a></span>

                                    </ul><!--project-additional-->
                                </div><!--/single-below-header-->

                                <div id="single-meta" data-sharing="<?php echo ( !empty($options['blog_social']) && $options['blog_social'] == 1 ) ? '1' : '0'; ?>">
                                    <ul>

                                        <?php if( empty($options['blog_social']) || $options['blog_social'] == 0 ) { ?>

                                            <li>
                                                <?php echo '<span class="n-shortcode">'.@nectar_love('return').'</span>'; ?>
                                            </li>
                                            <li>
                                                <?php echo get_the_date(); ?>
                                            </li>

                                        <?php } ?>

                                    </ul>

                                    <?php @nectar_blog_social_sharing(); ?>

                                </div><!--/single-meta-->
                            </div><!--/section-title-->
                        </div><!--/row-->

                    <?php }

                endwhile; endif; ?>

            <?php } ?>
            <div class="row">

                <?php $options = get_option('salient');

                global $options;

                if($blog_type == 'std-blog-fullwidth' || $hide_sidebar == '1'){
                    echo '<div id="post-area" class="col span_12 col_last">';
                } else {
                    echo '<div id="post-area" class="col span_9">';
                }
                ?>
                <?php

                if(have_posts()) : while(have_posts()) : the_post();


                    if ( floatval(get_bloginfo('version')) < "3.6" ) {
                        //old post formats before they got built into the core
                        get_template_part( 'includes/post-templates-pre-3-6/entry', get_post_format() );
                    } else {
                        //WP 3.6+ post formats
                        get_template_part( 'includes/post-templates/entry', get_post_format() );
                    }

                endwhile; endif;

                wp_link_pages();


                $options = get_option('salient');

                if($theme_skin != 'ascend') {
                    if( !empty($options['author_bio']) && $options['author_bio'] == true){
                        $grav_size = 80;
                        $fw_class = null;
                        ?>

                        <div id="author-bio" class="<?php echo $fw_class; ?>">
                            <div class="span_12">
                                <?php if (function_exists('get_avatar')) { echo get_avatar( get_the_author_meta('email'), $grav_size ); }?>
                                <div id="author-info">
                                    <h3><span><?php if(!empty($options['theme-skin']) && $options['theme-skin'] == 'ascend') { _e('Author', NECTAR_THEME_NAME); } else { _e('About', NECTAR_THEME_NAME); } ?></span> <?php the_author(); ?></h3>
                                    <p><?php the_author_meta('description'); ?></p>
                                </div>
                                <?php if(!empty($options['theme-skin']) && $options['theme-skin'] == 'ascend'){ echo '<a href="'. get_author_posts_url(get_the_author_meta( 'ID' )).'" data-hover-text-color-override="#fff" data-hover-color-override="false" data-color-override="#000000" class="nectar-button see-through-2 large"> More posts by '.get_the_author().' </a>'; } ?>
                                <div class="clear"></div>
                            </div>
                        </div>

                    <?php } ?>

                    <div class="comments-section">
                        <?php comments_template(); ?>
                    </div>


                <?php } ?>

            </div><!--/span_9-->

            <?php
            $terms = wp_get_post_terms( get_the_ID(), 'program_cat' );
            $orange = false;
            $term_title = '';
            foreach( $terms as $term ){
                $term_title = $term->name;
                if( $term->slug == 'include-clinical-trials' ) { $orange = true; break;}
            }
            if( $orange ){
                $class = 'izw-orange';
                $hashtag = 'clinicaltrials';
                $sidebar_class = 'orange';
            }else{
                $class = 'izw-blue';
                $hashtag = 'expandedaccess';
                $sidebar_class = 'blue';
            }
            ?>
            <div id="sidebar" class="col span_3 col_last izweb <?php echo $sidebar_class; ?>">
                <div id="izweb-program-widget" class="widget izweb-program-widget">
                    <div style="display: none;" class="program-hastag">@myTomorrows%20%23<?php echo $hashtag; ?></div>
                    <h4 class="<?php echo $class; ?> box-shadow" style="font-size: 0.8em !important;"><?php echo $term_title; ?></h4>
                    <h4 class="<?php echo $class; ?>">Program Details</h4>
                    <div class="widget-contents">
                        <?php global $post; izweb_show_custom_field( $post->ID ); ?>
                    </div>
                    <h5 ><a href="<?php echo get_the_permalink( search_link( 'search_program') ); ?>"> &laquo; &nbsp;Back to search</a></h5>
                </div>
                <!--<div class="widget widget_tag_cloud">
                    <h4 class="izw-blue">Categories</h4>
                    <div class="tagcloud">
                        <?php
                            $terms = wp_get_post_terms( get_the_ID(), 'program_cat');
                            if( sizeof( $terms) > 0 ){
                                foreach($terms as $term ){
                                    echo '<a href="'.get_term_link($term).'" class="tag-link-'.$term->term_id.'" title="'.$term->name .'" style="font-size: 8pt;">'.$term->name .'</a>';
                                }
                            }
                        ?>
                    </div>
                </div>-->
                <?php dynamic_sidebar('izw-program'); ?>
            </div><!--/sidebar-->


        </div><!--/row-->


        <!--ascend only author/comment positioning-->
        <div class="row">

            <?php if($theme_skin == 'ascend' && $fullscreen_header == true) { ?>

                <div id="single-below-header" class="<?php echo $fullscreen_class; ?> custom-skip">
                    <span class="meta-share-count"><i class="icon-default-style steadysets-icon-share"></i> <?php echo '<a href=""><span class="share-count-total">0</span> <span class="plural">'. __('Shares',NECTAR_THEME_NAME) . '</span> <span class="singular">'. __('Share',NECTAR_THEME_NAME) .'</span> </a>'; nectar_blog_social_sharing(); ?> </span>
                    <span class="meta-category"><i class="icon-default-style steadysets-icon-book2"></i> <?php the_category(', '); ?></span>
                    <span class="meta-comment-count"><i class="icon-default-style steadysets-icon-chat-3"></i> <a href="<?php comments_link(); ?>"><?php comments_number( __('No Comments', NECTAR_THEME_NAME), __('One Comment ', NECTAR_THEME_NAME), __('% Comments', NECTAR_THEME_NAME) ); ?></a></span>
                </div><!--/single-below-header-->

            <?php }

            if($theme_skin == 'ascend') @nectar_next_post_display(); ?>

            <?php if( !empty($options['author_bio']) && $options['author_bio'] == true && $theme_skin == 'ascend'){
                $grav_size = 80;
                $fw_class = 'full-width-section ';
                $next_post = get_previous_post();
                $next_post_button = (!empty($options['blog_next_post_link']) && $options['blog_next_post_link'] == '1') ? 'on' : 'off';
                ?>

                <div id="author-bio" class="<?php echo $fw_class; if(empty($next_post) || $next_post_button == 'off' || $fullscreen_header == false && $next_post_button == 'off') echo 'no-pagination'; ?>">
                    <div class="span_12">
                        <?php if (function_exists('get_avatar')) { echo get_avatar( get_the_author_meta('email'), $grav_size ); }?>
                        <div id="author-info">
                            <h3><span><?php if(!empty($options['theme-skin']) && $options['theme-skin'] == 'ascend') {  echo '<i>' . __('Author', NECTAR_THEME_NAME) . '</i>'; } else { _e('About', NECTAR_THEME_NAME); } ?></span> <?php the_author(); ?></h3>
                            <p><?php the_author_meta('description'); ?></p>
                        </div>
                        <?php if(!empty($options['theme-skin']) && $options['theme-skin'] == 'ascend'){ echo '<a href="'. get_author_posts_url(get_the_author_meta( 'ID' )).'" data-hover-text-color-override="#fff" data-hover-color-override="false" data-color-override="#000000" class="nectar-button see-through-2 large"> More posts by '.get_the_author().' </a>'; } ?>
                        <div class="clear"></div>
                    </div>
                </div>

            <?php } ?>


            <?php if($theme_skin == 'ascend') { ?>

                <div class="comments-section">
                    <?php comments_template(); ?>
                </div>

            <?php } ?>

        </div>


        <?php if($theme_skin != 'ascend') @nectar_next_post_display(); ?>

    </div><!--/container-->

    </div><!--/container-wrap-->
    <div id="program-footer" class="program-footer">
        <?php dynamic_sidebar('izw-footer-program'); ?>
    </div>
<?php get_footer(); ?>