<?php
/**
* Mailchimp Function.
*
* @package Infinity News
*/

if ( !function_exists( 'infinity_news_mailchimp' ) ):

    // Home Mailchimp.
    function infinity_news_mailchimp( $infinity_news_home_section  ){

        $mailchimp_title = isset( $infinity_news_home_section->section_title ) ? $infinity_news_home_section->section_title : '' ;
        $mailchimp_shortcode = isset( $infinity_news_home_section->mailchimp_shortcode ) ? $infinity_news_home_section->mailchimp_shortcode : '' ;
        $mailchimp_text_color = isset( $infinity_news_home_section->mailchimp_text_color ) ? $infinity_news_home_section->mailchimp_text_color : '' ;
        $section_bg_image = isset( $infinity_news_home_section->section_bg_image ) ? $infinity_news_home_section->section_bg_image : '' ;
        $section_bg_color = isset( $infinity_news_home_section->section_bg_color ) ? $infinity_news_home_section->section_bg_color : '' ;
        $mailchimp_image = isset( $infinity_news_home_section->mailchimp_image ) ? $infinity_news_home_section->mailchimp_image : '' ;
        $mailchimp_description = isset( $infinity_news_home_section->mailchimp_description ) ? $infinity_news_home_section->mailchimp_description : '' ;
        if ( $mailchimp_title || $mailchimp_shortcode ) { ?>

            <div class="site-mailchimp twp-blocks <?php if( $section_bg_image ){ echo 'data-bg'; } ?>" <?php if( $section_bg_image ){ echo 'data-background="'.esc_url( $section_bg_image ).'"'; }else{ if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; } } ?> >
                <div class="wrapper">
                    <div class="twp-row">

                        <?php if( $mailchimp_image ){ ?>
                            <div class="column column-three-1 column-full-sm">
                                <div class="mailchimp-imag">
                                    <img src="<?php echo esc_url( $mailchimp_image ); ?>" alt="">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="column column-six-1 column-full-sm">
                            <?php if( $mailchimp_title ){ ?>
                                <header class="block-title-wrapper">
                                    <h2 class="block-title block-title-1" <?php if( $mailchimp_text_color ){ echo 'style="color:'.esc_attr( $mailchimp_text_color).'"'; } ?>>
                                        <?php echo esc_html( $mailchimp_title ); ?>
                                    </h2>
                                </header>
                            <?php } ?>

                            <?php if( $mailchimp_description ){ ?>
                                <div class="mailchimp-description">
                                    <?php echo esc_html( $mailchimp_description ); ?>
                                </div>
                            <?php } ?>

                            <?php echo do_shortcode( $mailchimp_shortcode ); ?>

                        </div>
                    </div>
                </div>
            </div>

        <?php }
    }

endif;