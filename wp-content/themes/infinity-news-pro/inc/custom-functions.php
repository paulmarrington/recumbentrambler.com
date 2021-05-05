<?php
/**
* Custom Functions.
*
* @package Infinity News
*/

if( !function_exists( 'infinity_news_post_category_list' ) ) :

    // Post Category List.
    function infinity_news_post_category_list( $select_cat = true ){

        $post_cat_lists = get_categories(
            array(
                'hide_empty' => '0',
                'exclude' => '1',
            )
        );

        $post_cat_cat_array = array();
        if( $select_cat ){
            $post_cat_cat_array[''] = esc_html__( '--Select Category--','infinity-news' );
        }

        foreach ( $post_cat_lists as $post_cat_list ) {

            $post_cat_cat_array[$post_cat_list->slug] = $post_cat_list->name;

        }

        return $post_cat_cat_array;
    }

endif;

if( !function_exists( 'infinity_news_sanitize_sidebar_option' ) ) :

    // Sidebar Option Sanitize.
    function infinity_news_sanitize_sidebar_option( $input ){
        $metabox_options = array( 'global-sidebar','left-sidebar','right-sidebar','no-sidebar' );
        if( in_array( $input,$metabox_options ) ){
            return $input;
        }
        else{
            return '';
        }
    }

endif;

if( !function_exists( 'infinity_news_posts_navigation' ) ) :

     // Posts Navigations.
    function infinity_news_posts_navigation(){

        $default = infinity_news_get_default_theme_options();
        $pagination_layout = esc_html( get_theme_mod( 'pagination_layout',$default['pagination_layout'] ) );

        if( $pagination_layout == 'classic' ){
            the_posts_navigation();
        }else{
            the_posts_pagination();
        }

    }

endif;

if( !function_exists( 'infinity_news_breadcrumb' ) ) :

    // Trail Breadcrumb.
    function infinity_news_breadcrumb(){ ?>

        <div class="twp-inner-banner">
            <div class="wrapper">

                <?php 
                $default = infinity_news_get_default_theme_options();
                $breadcrumb_layout = get_theme_mod('breadcrumb_layout',$default['breadcrumb_layout']);
                if( $breadcrumb_layout != 'disable' && !is_front_page() ):
                        breadcrumb_trail();
                endif; ?>

                
                    <?php
                    if( is_search() ){ ?>
                        <div class="twp-banner-details">
                            <header class="page-header">
                                <h1 class="page-title">
                                    <?php
                                    /* translators: %s: search query. */
                                    printf( esc_html__( 'Search Results for: %s', 'infinity-news' ), '<span>' . get_search_query() . '</span>' );
                                    ?>
                                </h1>
                            </header><!-- .page-header -->
                        </div>
                    <?php } ?>

                    <?php
                    if( is_archive() && !is_author() ){ ?>

                        <div class="twp-banner-details">
                            <header class="page-header">
                                <?php
                                the_archive_title( '<h1 class="page-title">', '</h1>' );
                                the_archive_description( '<div class="archive-description">', '</div>' );
                                ?>
                            </header><!-- .page-header -->
                        </div>
                    <?php }

                    if( is_author() ){ ?>
                        <div class="twp-banner-details">
                            <header class="page-header">

                                <?php
                                $curauth = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );
                                $author_img = get_avatar( absint( $curauth->ID ),200, '', '', array('class' => 'avatar-img') ); ?>

                                <div class="author-image">
                                    <?php echo wp_kses_post( $author_img ); ?>
                                </div>

                                <div class="author-title-desc">
                                    <h1 class="page-title"><?php echo esc_html( $curauth->nickname ); ?></h1>
                                    <div class="archive-description"><?php echo esc_html( get_the_author_meta('description',absint( $curauth->ID ) ) ); ?></div>
                                </div>

                            </header><!-- .page-header -->
                        </div>
                    <?php } ?>

            </div>
        </div>
    <?php
    }

endif;
add_action( 'infinity_news_header_banner_x','infinity_news_breadcrumb',20 );

if( !function_exists('infinity_news_post_formate_icon') ):

    // Post Formate Icon.
    function infinity_news_post_formate_icon( $formate ){

        if( $formate == 'video' ){
            $icon = 'ion-ios-play';
        }elseif( $formate == 'audio' ){
            $icon = 'ion-ios-musical-notes';
        }elseif( $formate == 'gallery' ){
            $icon = 'ion-md-images';
        }elseif( $formate == 'quote' ){
            $icon = 'ion-md-quote';
        }elseif( $formate == 'image' ){
            $icon = 'ion-ios-camera';
        }else{
            $icon = '';
        }

        return $icon;
    }

endif;

if( !function_exists('infinity_news_check_woocommerce_page') ):
    
    // Check if woocommerce pages.
    function infinity_news_check_woocommerce_page(){

        if( !class_exists( 'WooCommerce' ) ):
            return false;
        endif;

        if( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ){
            return true;
        }else{
            return false;
        }

    }
endif;


if( !function_exists('infinity_news_import_files') ):

    // Demo Import Function.
    function infinity_news_import_files() {
      return array(
        array(
          'import_file_name'             => esc_html__('Default Demo','infinity-news'),
          'local_import_file'            => trailingslashit( get_template_directory() ) . 'demo-content/default/demo-content.xml',
          'local_import_widget_file'     => trailingslashit( get_template_directory() ) . 'demo-content/default/widgets.wie',
          'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'demo-content/default/customizer.dat',
          'import_preview_image_url'     =>  trailingslashit( get_template_directory_uri() ) . 'assets/images/demo-1-screenshot.jpg',
        ),
        array(
          'import_file_name'             => esc_html__('Sport Demo','infinity-news'),
          'local_import_file'            => trailingslashit( get_template_directory() ) . 'demo-content/sport/demo-content.xml',
          'local_import_widget_file'     => trailingslashit( get_template_directory() ) . 'demo-content/sport/widgets.wie',
          'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'demo-content/sport/customizer.dat',
          'import_preview_image_url'     =>  trailingslashit( get_template_directory_uri() ) . 'assets/images/demo-2-screenshot.jpg',
        ),
      );
    }

endif;
add_filter( 'pt-ocdi/import_files', 'infinity_news_import_files' );

if( !function_exists('infinity_news_assign_menu') ):
   
    // Assign menus to their locations.
    function infinity_news_assign_menu() {
        
        $main_menu = get_term_by( 'name', 'Primary Menu', 'nav_menu' );
        $footer_menu = get_term_by( 'name', 'Footer Menu', 'nav_menu' );
        $social_menu = get_term_by( 'name', 'Social Menu', 'nav_menu' );
        set_theme_mod( 'nav_menu_locations', array(
                'twp-primary-menu' => $main_menu->term_id,
                'twp-footer-menu' => $footer_menu->term_id,
                'twp-social-menu' => $social_menu->term_id,
            )
        );
    }
endif;
add_action( 'pt-ocdi/after_import', 'infinity_news_assign_menu' );


if ( ! function_exists( 'infinity_news_meta_sanitize_metabox' ) ) :

    /**
     * Sanitize Meta Bozes.
     */
    function infinity_news_meta_sanitize_metabox( $input ) {

        $allowed_html = array(
                            'meta' => array(
                                'property' => array(),
                                'content' => array(),
                            ),
                        );

        return wp_kses( $input, $allowed_html );

    }

endif;

if( class_exists( 'Booster_Extension_Class') ){

    add_filter('booster_extension_filter_ss_ed','infinity_news_booster_social_share_disable');

    if( !function_exists('infinity_news_booster_social_share_disable') ):

        function infinity_news_booster_social_share_disable(){
            return false;
        }

    endif;

}

if( class_exists( 'Booster_Extension_Class') ){

    add_filter('booster_extension_filter_like_ed','infinity_news_booster_like_dislike_disable');

    if( !function_exists('infinity_news_booster_like_dislike_disable') ):

        function infinity_news_booster_like_dislike_disable(){
            return false;
        }

    endif;

}

if( !function_exists('infinity_news_post_floating_nav') ):

    function infinity_news_post_floating_nav(){

        $default = infinity_news_get_default_theme_options();
        $ed_floating_next_previous_nav = get_theme_mod( 'ed_floating_next_previous_nav',$default['ed_floating_next_previous_nav'] );

        if( 'post' === get_post_type() && $ed_floating_next_previous_nav ){

            $next_post = get_next_post();
            $prev_post = get_previous_post();

            if( isset( $prev_post->ID ) ){

                $prev_link = get_permalink( $prev_post->ID );?>

                <div class="floating-post-navigation floating-navigation-prev">
                    <?php if( get_the_post_thumbnail( $prev_post->ID,'medium' ) ){ ?>
                            <?php echo wp_kses_post( get_the_post_thumbnail( $prev_post->ID,'medium' ) ); ?>
                    <?php } ?>
                    <a href="<?php echo esc_url( $prev_link ); ?>">
                        <span class="floating-navigation-label"><?php echo esc_html__('Previous post', 'infinity-news'); ?></span>
                        <span class="floating-navigation-title"><?php echo esc_html( get_the_title( $prev_post->ID ) ); ?></span>
                    </a>
                </div>

            <?php }

            if( isset( $next_post->ID ) ){

                $next_link = get_permalink( $next_post->ID );?>

                <div class="floating-post-navigation floating-navigation-next">
                    <?php if( get_the_post_thumbnail( $next_post->ID,'medium' ) ){ ?>
                        <?php echo wp_kses_post( get_the_post_thumbnail( $next_post->ID,'medium' ) ); ?>
                    <?php } ?>
                    <a href="<?php echo esc_url( $next_link ); ?>">
                        <span class="floating-navigation-label"><?php echo esc_html__('Next post', 'infinity-news'); ?></span>
                        <span class="floating-navigation-title"><?php echo esc_html( get_the_title( $next_post->ID ) ); ?></span>
                    </a>
                </div>

            <?php
            }

        }

    }

endif;

add_action( 'infinity_news_navigation_action','infinity_news_post_floating_nav',10 );