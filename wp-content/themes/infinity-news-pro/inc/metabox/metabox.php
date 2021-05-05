<?php
/**
* Metabox.
*
* @package Infinity News
*/
 
add_action( 'add_meta_boxes', 'infinity_news_metabox' );

if( ! function_exists( 'infinity_news_metabox' ) ):


    function  infinity_news_metabox() {
        
        add_meta_box(
            'infinity_news_post_metabox',
            esc_html__( 'Single Post/Page Settings', 'infinity-news' ),
            'infinity_news_post_metafield_callback',
            'post', 
            'normal', 
            'high'
        );
        add_meta_box(
            'infinity_news_page_metabox',
            esc_html__( 'Single Post/Page Settings', 'infinity-news' ),
            'infinity_news_post_metafield_callback',
            'page',
            'normal', 
            'high'
        ); 
    }

endif;


$infinity_news_post_image_fields = array(
    'global-image' => array(
                    'id'        => 'post-global-image',
                    'value' => 'global-image',
                    'label' => esc_html__( 'Global image', 'infinity-news' ),
                ),
    'enable-image' => array(
                    'id'        => 'post-enable-image',
                    'value' => 'enable-image',
                    'label' => esc_html__( 'Enable image', 'infinity-news' ),
                ),
    'disable-image' => array(
                    'id'        => 'post-disable-image',
                    'value'     => 'disable-image',
                    'label'     => esc_html__( 'Disable image', 'infinity-news' ),
                ),
);

$infinity_news_post_sidebar_fields = array(
    'global-sidebar' => array(
                    'id'        => 'post-global-sidebar',
                    'value' => 'global-sidebar',
                    'label' => esc_html__( 'Global sidebar', 'infinity-news' ),
                ),
    'right-sidebar' => array(
                    'id'        => 'post-left-sidebar',
                    'value' => 'right-sidebar',
                    'label' => esc_html__( 'Right sidebar', 'infinity-news' ),
                ),
    'left-sidebar' => array(
                    'id'        => 'post-right-sidebar',
                    'value'     => 'left-sidebar',
                    'label'     => esc_html__( 'Left sidebar', 'infinity-news' ),
                ),
    'no-sidebar' => array(
                    'id'        => 'post-no-sidebar',
                    'value'     => 'no-sidebar',
                    'label'     => esc_html__( 'No sidebar', 'infinity-news' ),
                ),
);

/**
 * Callback function for post option.
*/
if( ! function_exists( 'infinity_news_post_metafield_callback' ) ):
    function infinity_news_post_metafield_callback() {
        global $post, $infinity_news_post_sidebar_fields, $infinity_news_post_image_fields;
        $post_type = get_post_type( $post->ID );
        wp_nonce_field( basename( __FILE__ ), 'infinity_news_post_meta_nonce' );
        $default = infinity_news_get_default_theme_options();
        $global_sidebar_layout = esc_html( get_theme_mod( 'global_sidebar_layout',$default['global_sidebar_layout'] ) );
        $infinity_news_post_sidebar = esc_html( get_post_meta( $post->ID, 'infinity_news_post_sidebar_option', true ) ); 
        if( $infinity_news_post_sidebar == '' ){ $infinity_news_post_sidebar = 'global-sidebar'; }
        $twp_ed_twitter_summary = get_theme_mod('twp_ed_twitter_summary');
        $twp_ed_open_graph = get_theme_mod('twp_ed_open_graph'); 
        ?>

        <div class="infinity-tab-main">

            <div class="infinity-metabox-tab">
                <ul>
                    <li>
                        <a id="twp-tab-sidebar" class="twp-tab-active" href="javascript:void(0)"><?php esc_html_e('Layout Settings', 'infinity-news'); ?></a>
                    </li>
                    <?php if( $twp_ed_open_graph ){ ?>
                        <li>
                            <a id="twp-tab-og" href="javascript:void(0)"><?php esc_html_e('Open Graph', 'infinity-news'); ?></a>
                        </li>
                    <?php } ?>

                    <?php if( $twp_ed_twitter_summary ){ ?>
                        <li>
                            <a id="twp-tab-ts" href="javascript:void(0)"><?php esc_html_e('Twitter Summary', 'infinity-news'); ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <div class="infinity-tab-content">
                
                <div id="twp-tab-sidebar-content" class="infinity-content-wrap infinity-tab-content-active">

                    <div class="infinity-meta-panels">

                        <div class="infinity-opt-wrap infinity-opt-wrap-alt">

                            <label><b><?php esc_html_e( 'Sidebar Layout','infinity-news' ); ?></b></label>

                            <select name="infinity_news_post_sidebar_option">

                                <?php
                                foreach ( $infinity_news_post_sidebar_fields as $infinity_news_post_sidebar_field) { ?>
                                    
                                    <option value="<?php echo esc_attr( $infinity_news_post_sidebar_field['value'] ); ?>" <?php if( $infinity_news_post_sidebar_field['value'] == $infinity_news_post_sidebar ){ echo "selected";} if( empty( $infinity_news_post_sidebar ) && $infinity_news_post_sidebar_field['value']=='right-sidebar' ){ echo "selected"; } ?> >
                                        <?php echo esc_html( $infinity_news_post_sidebar_field['label'] ); ?> 
                                    </option>

                                <?php } ?>


                            </select>

                        </div>


                    </div>
                </div>

                <?php if( $twp_ed_open_graph ){ ?>

                    <div id="twp-tab-og-content" class="infinity-content-wrap">
                        <h3 class="infinity-meta-title"><?php esc_html_e('Open Graph Option', 'infinity-news'); ?></h3>
                        <div class="infinity-meta-panels twp-twitter-panels">
                            <?php $twp_og_ed = esc_attr(get_post_meta($post->ID, 'twp_og_ed', true)); ?>
                            <div class="infinity-opt-wrap infinity-checkbox-wrap">
                                <input id="open-graph-checkbox" name="twp_og_ed" type="checkbox" <?php if ($twp_og_ed) { ?> checked="checked" <?php } ?> />
                                <label for="open-graph-checkbox"><?php esc_html_e('Disable Open Graph for this Post', 'infinity-news'); ?></label>
                            </div>
                            <div class="infinity-opt-wrap infinity-opt-wrap-alt">
                                <label><?php esc_html_e('Title', 'infinity-news'); ?></label>
                                <input name="twp_og_title" type="text" value="<?php echo esc_attr(get_post_meta($post->ID, 'twp_og_title', true)); ?>"/>
                            </div>
                            <div class="infinity-opt-wrap infinity-opt-wrap-alt">
                                <label><?php esc_html_e('Description', 'infinity-news'); ?></label>
                                <input name="twp_og_desc" type="text" value="<?php echo esc_attr(get_post_meta($post->ID, 'twp_og_desc', true)); ?>"/>
                            </div>
                            <div class="infinity-opt-wrap infinity-opt-wrap-alt">
                                <label><?php esc_html_e('URL', 'infinity-news'); ?></label>
                                <input name="twp_og_url" type="text" value="<?php echo esc_attr(get_post_meta($post->ID, 'twp_og_url', true)); ?>"/>
                            </div>
                            <div class="infinity-opt-wrap infinity-opt-wrap-alt">
                                <label><?php esc_html_e('Type', 'infinity-news'); ?></label>
                                <?php $twp_og_type = get_post_meta($post->ID, 'twp_og_type', true); ?>
                                <select name="twp_og_type">
                                    <option value=""><?php esc_html_e('--Select--', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_og_type == 'website') {
                                        echo 'selected';
                                    } ?> value="website"><?php esc_html_e('Website', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_og_type == 'video.episode') {
                                        echo 'selected';
                                    } ?> value="video.episode"><?php esc_html_e('video.episode', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_og_type == 'music.radio_station') {
                                        echo 'selected';
                                    } ?> value="music.radio_station"><?php esc_html_e('music.radio_station', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_og_type == 'music.song') {
                                        echo 'selected';
                                    } ?> value="music.song"><?php esc_html_e('music.song', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_og_type == 'music.playlist') {
                                        echo 'selected';
                                    } ?> value="music.playlist"><?php esc_html_e('music.playlist', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_og_type == 'video.movie') {
                                        echo 'selected';
                                    } ?> value="video.movie"><?php esc_html_e('video.movie', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_og_type == 'music.album') {
                                        echo 'selected';
                                    } ?> value="music.album"><?php esc_html_e('music.album', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_og_type == 'video.tv_show') {
                                        echo 'selected';
                                    } ?> value="video.tv_show"><?php esc_html_e('video.tv_show', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_og_type == 'article') {
                                        echo 'selected';
                                    } ?> value="article"><?php esc_html_e('Article', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_og_type == 'video.other') {
                                        echo 'selected';
                                    } ?> value="video.other"><?php esc_html_e('video.other', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_og_type == 'profile') {
                                        echo 'selected';
                                    } ?> value="profile"><?php esc_html_e('Profile', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_og_type == 'book') {
                                        echo 'selected';
                                    } ?> value="book"><?php esc_html_e('Book', 'infinity-news'); ?></option>
                                </select>
                            </div>
                            <div class="infinity-opt-wrap infinity-opt-wrap-alt">
                                <label><?php esc_html_e('Custom Tags', 'infinity-news'); ?></label>
                                <textarea name="twp_og_custom_meta"><?php echo infinity_news_meta_sanitize_metabox(get_post_meta($post->ID, 'twp_og_custom_meta', true)); ?></textarea>
                            </div>
                            <div class="infinity-opt-wrap infinity-opt-wrap-alt">
                                <label><?php esc_html_e('Image', 'infinity-news'); ?></label>
                                <?php
                                $twp_og_image = esc_url(get_post_meta($post->ID, 'twp_og_image', true));
                                $image = "";
                                if ($twp_og_image) {
                                    $image = '<img src="' . esc_url($twp_og_image) . '"/>';
                                } ?>
                                <div class="infinity-img-fields-wrap">
                                    <div class="attachment-media-view">
                                        <div class="infinity-img-fields-wrap">
                                            <div class="twp-attachment-media-view">
                                                <div class="infinity-attachment-child infinity-uploader">
                                                    <button type="button" class="infinity-img-upload-button">
                                                        <span class="dashicons dashicons-upload twp-icon twp-icon-large"></span>
                                                    </button>
                                                    <input class="upload-id" name="twp_og_image" type="hidden"
                                                           value="<?php echo esc_url($twp_og_image); ?>"/>
                                                </div>
                                                <div class="infinity-attachment-child infinity-thumbnail-image">
                                                    <button type="button"
                                                            class="infinity-img-delete-button <?php if ($twp_og_image) {
                                                                echo 'twp-img-show';
                                                            } ?>">
                                                        <span class="dashicons dashicons-no-alt twp-icon"></span>
                                                    </button>
                                                    <div class="twp-img-container">
                                                        <?php echo $image; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php } ?>

                <?php if( $twp_ed_twitter_summary ){ ?>

                    <div id="twp-tab-ts-content" class="infinity-content-wrap">
                        <h3 class="infinity-meta-title"><?php esc_html_e('Twitter Summary Option', 'infinity-news'); ?></h3>
                        <div class="infinity-meta-panels twp-twitter-panels">
                            <?php $twp_ts_ed = esc_attr(get_post_meta($post->ID, 'twp_ts_ed', true)); ?>
                            <div class="infinity-opt-wrap infinity-checkbox-wrap">
                                <input id="twitter-summery-checkbox" name="twp_ts_ed" type="checkbox" <?php if ($twp_ts_ed) { ?> checked="checked" <?php } ?> />
                                <label for="twitter-summery-checkbox"><?php esc_html_e('Disable Twitter Summary for this Post', 'infinity-news'); ?></label>
                            </div>
                            <div class="infinity-opt-wrap infinity-opt-wrap-alt">
                                <label><?php esc_html_e('Title', 'infinity-news'); ?></label>
                                <input name="twp_twitter_summary_title" type="text" value="<?php echo esc_attr(get_post_meta($post->ID, 'twp_twitter_summary_title', true)); ?>"/>
                            </div>
                            <div class="infinity-opt-wrap infinity-opt-wrap-alt">
                                <label><?php esc_html_e('Description', 'infinity-news'); ?></label>
                                <input name="twp_twitter_summary_desc" type="text" value="<?php echo esc_attr(get_post_meta($post->ID, 'twp_twitter_summary_desc', true)); ?>"/>
                            </div>
                            <div class="infinity-opt-wrap infinity-opt-wrap-alt">
                                <label><?php esc_html_e('Twitter Username', 'infinity-news'); ?></label>
                                <input name="twp_twitter_summary_username" type="text" value="<?php echo esc_attr(get_post_meta($post->ID, 'twp_twitter_summary_username', true)); ?>"/>
                            </div>
                            <div class="infinity-opt-wrap infinity-opt-wrap-alt">
                                <label><?php esc_html_e('Twitter Card', 'infinity-news'); ?></label>
                                <?php $twp_twitter_summary_type = get_post_meta($post->ID, 'twp_twitter_summary_type', true); ?>
                                <select name="twp_twitter_summary_type">
                                    <option value=""><?php esc_html_e('--Select--', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_twitter_summary_type == 'summary') {
                                        echo 'selected';
                                    } ?> value="summary"><?php esc_html_e('Summary', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_twitter_summary_type == 'summary_large_image') {
                                        echo 'selected';
                                    } ?> value="summary_large_image"><?php esc_html_e('Summary Large Image', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_twitter_summary_type == 'music.radio_station') {
                                        echo 'selected';
                                    } ?> value="music.radio_station"><?php esc_html_e('music.radio_station', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_twitter_summary_type == 'app') {
                                        echo 'selected';
                                    } ?> value="app"><?php esc_html_e('APP', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_twitter_summary_type == 'player') {
                                        echo 'selected';
                                    } ?> value="player"><?php esc_html_e('Player', 'infinity-news'); ?></option>
                                    <option <?php if ($twp_twitter_summary_type == 'lead_generation') {
                                        echo 'selected';
                                    } ?> value="lead_generation"><?php esc_html_e('Lead Generation', 'infinity-news'); ?></option>
                                </select>
                            </div>
                            <div class="infinity-opt-wrap infinity-opt-wrap-alt">
                                <label><?php esc_html_e('Custom Tags', 'infinity-news'); ?></label>
                                <textarea name="twp_twitter_summary_custom_meta"><?php echo infinity_news_meta_sanitize_metabox(get_post_meta($post->ID, 'twp_twitter_summary_custom_meta', true)); ?></textarea>
                            </div>
                            <div class="infinity-opt-wrap infinity-opt-wrap-alt">
                                <label><?php esc_html_e('Image', 'infinity-news'); ?></label>
                                <?php
                                $twp_twitter_summary_image = esc_url(get_post_meta($post->ID, 'twp_twitter_summary_image', true));
                                $image = "";
                                if ($twp_twitter_summary_image) {
                                    $image = '<img src="' . esc_url($twp_twitter_summary_image) . '"/>';
                                } ?>
                                <div class="infinity-img-fields-wrap">
                                    <div class="attachment-media-view">
                                        <div class="infinity-img-fields-wrap">
                                            <div class="twp-attachment-media-view">
                                                <div class="infinity-attachment-child infinity-uploader">
                                                    <button type="button" class="infinity-img-upload-button">
                                                        <span class="dashicons dashicons-upload twp-icon twp-icon-large"></span>
                                                    </button>
                                                    <input class="upload-id" name="twp_twitter_summary_image"
                                                           type="hidden"
                                                           value="<?php echo esc_url($twp_twitter_summary_image); ?>"/>
                                                </div>
                                                <div class="infinity-attachment-child infinity-thumbnail-image">
                                                    <button type="button"
                                                            class="infinity-img-delete-button <?php if ($twp_twitter_summary_image) {
                                                                echo 'twp-img-show';
                                                            } ?>">
                                                        <span class="dashicons dashicons-no-alt twp-icon"></span>
                                                    </button>
                                                    <div class="twp-img-container">
                                                        <?php echo $image; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php } ?>

            </div>
        </div>

    <?php }
endif;

// Save metabox value.
add_action( 'save_post', 'infinity_news_save_post_meta' );

if( ! function_exists( 'infinity_news_save_post_meta' ) ):

function infinity_news_save_post_meta( $post_id ) {

    global $post;
    $post_type = '';
    if (isset($post->ID)) {
        $post_type = get_post_type($post->ID);
    }
    
    if ( !isset( $_POST[ 'infinity_news_post_meta_nonce' ] ) || !wp_verify_nonce( wp_unslash( $_POST['infinity_news_post_meta_nonce'] ), basename( __FILE__ ) ) )
        return;

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  
        return;
        
    if ( 'page' == wp_unslash( $_POST['post_type'] ) ) {  
        if ( !current_user_can( 'edit_page', $post_id ) )  
            return $post_id;  
    } elseif ( !current_user_can( 'edit_post', $post_id ) ) {  
            return $post_id;  
    }
    
    

    $infinity_news_post_sidebar_option_old = esc_html( get_post_meta( $post_id, 'infinity_news_post_sidebar_option', true ) ); 
    $infinity_news_post_sidebar_option_new = infinity_news_sanitize_sidebar_option( wp_unslash( $_POST['infinity_news_post_sidebar_option'] ) );
    if ( $infinity_news_post_sidebar_option_new && $infinity_news_post_sidebar_option_new != $infinity_news_post_sidebar_option_old ) {  
        update_post_meta ( $post_id, 'infinity_news_post_sidebar_option', $infinity_news_post_sidebar_option_new );  
    } elseif ( '' == $infinity_news_post_sidebar_option_new && $infinity_news_post_sidebar_option_old ) {  
        delete_post_meta( $post_id,'infinity_news_post_sidebar_option', $infinity_news_post_sidebar_option_old );  
    }

    /**
     * Open Graph
     **/
    $twp_og_ed_old = esc_html(get_post_meta($post_id, 'twp_og_ed', true));
    $twp_og_ed_news = sanitize_text_field(wp_unslash($_POST['twp_og_ed']));
    $twp_og_title_old = esc_html(get_post_meta($post_id, 'twp_og_title', true));
    $twp_og_title_news = sanitize_text_field(wp_unslash($_POST['twp_og_title']));
    $twp_og_desc_old = esc_html(get_post_meta($post_id, 'twp_og_desc', true));
    $twp_og_desc_news = sanitize_text_field(wp_unslash($_POST['twp_og_desc']));
    $twp_og_url_old = esc_url(get_post_meta($post_id, 'twp_og_url', true));
    $twp_og_url_news = esc_url_raw(wp_unslash($_POST['twp_og_url']));
    $twp_og_type_old = esc_html(get_post_meta($post_id, 'twp_og_type', true));
    $twp_og_type_news = sanitize_text_field(wp_unslash($_POST['twp_og_type']));
    $twp_og_custom_meta_old = infinity_news_meta_sanitize_metabox(get_post_meta($post_id, 'twp_og_custom_meta', true));
    $twp_og_custom_meta_news = infinity_news_meta_sanitize_metabox(wp_unslash($_POST['twp_og_custom_meta']));
    $twp_og_image_old = esc_url(get_post_meta($post_id, 'twp_og_image', true));
    $twp_og_image_news = esc_url_raw(wp_unslash($_POST['twp_og_image']));
    if ($twp_og_ed_news && $twp_og_ed_news != $twp_og_ed_old) {
        update_post_meta($post_id, 'twp_og_ed', $twp_og_ed_news);
    } elseif ('' == $twp_og_ed_news && $twp_og_ed_old) {
        delete_post_meta($post_id, 'twp_og_ed', $twp_og_ed_old);
    }
    if ($twp_og_title_news && $twp_og_title_news != $twp_og_title_old) {
        update_post_meta($post_id, 'twp_og_title', $twp_og_title_news);
    } elseif ('' == $twp_og_title_news && $twp_og_title_old) {
        delete_post_meta($post_id, 'twp_og_title', $twp_og_title_old);
    }
    if ($twp_og_desc_news && $twp_og_desc_news != $twp_og_desc_old) {
        update_post_meta($post_id, 'twp_og_desc', $twp_og_desc_news);
    } elseif ('' == $twp_og_desc_news && $twp_og_desc_old) {
        delete_post_meta($post_id, 'twp_og_desc', $twp_og_desc_old);
    }
    if ($twp_og_url_news && $twp_og_url_news != $twp_og_url_old) {
        update_post_meta($post_id, 'twp_og_url', $twp_og_url_news);
    } elseif ('' == $twp_og_url_news && $twp_og_url_old) {
        delete_post_meta($post_id, 'twp_og_url', $twp_og_url_old);
    }
    if ($twp_og_type_news && $twp_og_type_news != $twp_og_type_old) {
        update_post_meta($post_id, 'twp_og_type', $twp_og_type_news);
    } elseif ('' == $twp_og_type_news && $twp_og_type_old) {
        delete_post_meta($post_id, 'twp_og_type', $twp_og_type_old);
    }
    if ($twp_og_custom_meta_news && $twp_og_custom_meta_news != $twp_og_custom_meta_old) {
        update_post_meta($post_id, 'twp_og_custom_meta', $twp_og_custom_meta_news);
    } elseif ('' == $twp_og_custom_meta_news && $twp_og_custom_meta_old) {
        delete_post_meta($post_id, 'twp_og_custom_meta', $twp_og_custom_meta_old);
    }
    if ($twp_og_image_news && $twp_og_image_news != $twp_og_image_old) {
        update_post_meta($post_id, 'twp_og_image', $twp_og_image_news);
    } elseif ('' == $twp_og_image_news && $twp_og_image_old) {
        delete_post_meta($post_id, 'twp_og_image', $twp_og_image_old);
    }
    /**
     * Twitter SUmmary
     **/
    $twp_ts_ed_old = esc_html(get_post_meta($post_id, 'twp_ts_ed', true));
    $twp_ts_ed_news = sanitize_text_field(wp_unslash($_POST['twp_ts_ed']));
    $twp_twitter_summary_title_old = esc_html(get_post_meta($post_id, 'twp_twitter_summary_title', true));
    $twp_twitter_summary_title_news = sanitize_text_field(wp_unslash($_POST['twp_twitter_summary_title']));
    $twp_twitter_summary_desc_old = esc_html(get_post_meta($post_id, 'twp_twitter_summary_desc', true));
    $twp_twitter_summary_desc_news = sanitize_text_field(wp_unslash($_POST['twp_twitter_summary_desc']));
    $twp_twitter_summary_username_old = esc_html(get_post_meta($post_id, 'twp_twitter_summary_username', true));
    $twp_twitter_summary_username_news = sanitize_text_field(wp_unslash($_POST['twp_twitter_summary_username']));
    $twp_twitter_summary_type_old = esc_html(get_post_meta($post_id, 'twp_twitter_summary_type', true));
    $twp_twitter_summary_type_news = sanitize_text_field(wp_unslash($_POST['twp_twitter_summary_type']));
    $twp_twitter_summary_custom_meta_old = infinity_news_meta_sanitize_metabox(get_post_meta($post_id, 'twp_twitter_summary_custom_meta', true));
    $twp_twitter_summary_custom_meta_news = infinity_news_meta_sanitize_metabox(wp_unslash($_POST['twp_twitter_summary_custom_meta']));
    $twp_twitter_summary_image_old = esc_url(get_post_meta($post_id, 'twp_twitter_summary_image', true));
    $twp_twitter_summary_image_news = esc_url_raw(wp_unslash($_POST['twp_twitter_summary_image']));
    if ($twp_ts_ed_news && $twp_ts_ed_news != $twp_ts_ed_old) {
        update_post_meta($post_id, 'twp_ts_ed', $twp_ts_ed_news);
    } elseif ('' == $twp_ts_ed_news && $twp_ts_ed_old) {
        delete_post_meta($post_id, 'twp_ts_ed', $twp_ts_ed_old);
    }
    if ($twp_twitter_summary_title_news && $twp_twitter_summary_title_news != $twp_twitter_summary_title_old) {
        update_post_meta($post_id, 'twp_twitter_summary_title', $twp_twitter_summary_title_news);
    } elseif ('' == $twp_twitter_summary_title_news && $twp_twitter_summary_title_old) {
        delete_post_meta($post_id, 'twp_twitter_summary_title', $twp_twitter_summary_title_old);
    }
    if ($twp_twitter_summary_desc_news && $twp_twitter_summary_desc_news != $twp_twitter_summary_desc_old) {
        update_post_meta($post_id, 'twp_twitter_summary_desc', $twp_twitter_summary_desc_news);
    } elseif ('' == $twp_twitter_summary_desc_news && $twp_twitter_summary_desc_old) {
        delete_post_meta($post_id, 'twp_twitter_summary_desc', $twp_twitter_summary_desc_old);
    }
    if ($twp_twitter_summary_username_news && $twp_twitter_summary_username_news != $twp_twitter_summary_username_old) {
        update_post_meta($post_id, 'twp_twitter_summary_username', $twp_twitter_summary_username_news);
    } elseif ('' == $twp_twitter_summary_username_news && $twp_twitter_summary_username_old) {
        delete_post_meta($post_id, 'twp_twitter_summary_username', $twp_twitter_summary_username_old);
    }
    if ($twp_twitter_summary_type_news && $twp_twitter_summary_type_news != $twp_twitter_summary_type_old) {
        update_post_meta($post_id, 'twp_twitter_summary_type', $twp_twitter_summary_type_news);
    } elseif ('' == $twp_twitter_summary_type_news && $twp_twitter_summary_type_old) {
        delete_post_meta($post_id, 'twp_twitter_summary_type', $twp_twitter_summary_type_old);
    }
    if ($twp_twitter_summary_custom_meta_news && $twp_twitter_summary_custom_meta_news != $twp_twitter_summary_custom_meta_old) {
        update_post_meta($post_id, 'twp_twitter_summary_custom_meta', $twp_twitter_summary_custom_meta_news);
    } elseif ('' == $twp_twitter_summary_custom_meta_news && $twp_twitter_summary_custom_meta_old) {
        delete_post_meta($post_id, 'twp_twitter_summary_custom_meta', $twp_twitter_summary_custom_meta_old);
    }
    if ($twp_twitter_summary_image_news && $twp_twitter_summary_image_news != $twp_twitter_summary_image_old) {
        update_post_meta($post_id, 'twp_twitter_summary_image', $twp_twitter_summary_image_news);
    } elseif ('' == $twp_twitter_summary_image_news && $twp_twitter_summary_image_old) {
        delete_post_meta($post_id, 'twp_twitter_summary_image', $twp_twitter_summary_image_old);
    }

}
endif;   