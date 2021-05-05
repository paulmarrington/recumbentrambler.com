<?php
function infinity_news_advertise( $infinity_news_home_section ){
$advertise_image = isset( $infinity_news_home_section->advertise_image ) ? $infinity_news_home_section->advertise_image : '' ;
$advertise_link = isset( $infinity_news_home_section->advertise_link ) ? $infinity_news_home_section->advertise_link : '' ;
$advertise_script = isset( $infinity_news_home_section->advertise_script ) ? $infinity_news_home_section->advertise_script : '' ;
		 ?>

<div class="home-lead-block twp-blocks">
    <div class="wrapper">
        <div class="twp-row">
            <div class="column">
                <?php if( $advertise_script ){
                    echo $advertise_script;
                }else{ 
                    if( $advertise_image ){ ?>
                        <a href="<?php echo esc_url( $advertise_link ); ?>" target="_blank" class="home-lead-link">
                            <img src="<?php echo esc_url( $advertise_image ); ?>" alt="<?php esc_attr_e('Advertise Image','infinity-news'); ?>">
                        </a>
                    <?php
                    }
                } ?>
                
            </div>
        </div>
    </div>
</div>

<?php } ?>