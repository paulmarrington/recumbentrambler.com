<?php
/**
 * Woocommerce Compatibility.
 *
 * @link https://woocommerce.com/
 *
 * @package BLog Prime
 */

/**
 * Remove WooCommerce Default Sidebar.
 */
remove_action( 'woocommerce_sidebar','woocommerce_get_sidebar',10 );

if( !function_exists('infinity_news_woocommerce_get_sidebar' ) ):

	// Woocommerce Custom Sidebars.
	function infinity_news_woocommerce_get_sidebar(){

		$default = infinity_news_get_default_theme_options();

		if( is_product() ){
			$sidebar_layout = esc_html( get_theme_mod( 'product_sidebar_layout',$default['product_sidebar_layout'] ) );
		}else{
			$sidebar_layout = esc_html( get_theme_mod( 'global_sidebar_layout', $default['global_sidebar_layout'] ) );
		}

		if( $sidebar_layout != 'no-sidebar' ){

			if ( ! is_active_sidebar( 'infinity-news-woocommerce-widget' ) ) {
			return;
			} ?>

			<aside id="secondary" class="widget-area">
				<?php dynamic_sidebar( 'infinity-news-woocommerce-widget' ); ?>
			</aside><!-- #secondary -->

		<?php }
	}

endif;
add_action( 'woocommerce_sidebar','infinity_news_woocommerce_get_sidebar',10 );

/**
 * Woocommerce support.
 */
function infinity_news_woocommerce_setup() {

	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support( 'woocommerce', array(
		'gallery_thumbnail_image_width' => 300,
	) );

}
add_action( 'after_setup_theme', 'infinity_news_woocommerce_setup' );
/**
* Woocommerce Widget Area.
*/
function infinity_news_woocommerc_widgets_init()
{   

    register_sidebar( array(
        'name' => esc_html__('WooCommerce Sidebar', 'infinity-news'),
        'id' => 'infinity-news-woocommerce-widget',
        'description' => esc_html__('Add widgets here.', 'infinity-news'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

}
add_action('widgets_init', 'infinity_news_woocommerc_widgets_init');

/**
 * Woocommerce Enqueue Scripts.
 */
function infinity_news_woocommerce_scripts() {

	wp_enqueue_style( 'infinity-news-woocommerce-style', get_template_directory_uri() . '/assets/lib/twp/css/woocommerce.css' );

}
add_action( 'wp_enqueue_scripts', 'infinity_news_woocommerce_scripts' );


if ( ! function_exists( 'infinity_news_woocommerce_cart_link_fragment' ) ) {
	/**
	 * Cart Fragments.
	 *
	 * Ensure cart contents update when products are added to the cart via AJAX.
	 *
	 * @param array $fragments Fragments to refresh via AJAX.
	 * @return array Fragments to refresh via AJAX.
	 */
	function infinity_news_woocommerce_cart_link_fragment( $fragments ) {
		ob_start();
		infinity_news_woocommerce_cart_link();
		$fragments['.cart-total-item'] = ob_get_clean();

		return $fragments;
	}
}
add_filter( 'woocommerce_add_to_cart_fragments', 'infinity_news_woocommerce_cart_link_fragment' );

if ( ! function_exists( 'infinity_news_woocommerce_cart_link' ) ) {
	/**
	 * Cart Link.
	 *
	 * Displayed a link to the cart including the number of items present and the cart total.
	 *
	 * @return void
	 */
	function infinity_news_woocommerce_cart_link() { ?>

		<div <?php if( WC()->cart->get_cart_contents_count() <= 0 ){ ?>style="opacity: 0" <?php } ?> class="cart-total-item">
			<a class="cart-contents" href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'infinity-news' ); ?>">
				<?php
				$item_count_text = sprintf(
					/* translators: number of items in the mini cart. */
					_n( '%d item', '%d items', WC()->cart->get_cart_contents_count(), 'infinity-news' ),
					WC()->cart->get_cart_contents_count()
				);
				?>
				<span class="amount"><?php echo wp_kses_data( WC()->cart->get_cart_subtotal() ); ?></span> <span class="count"><?php echo esc_html( $item_count_text ); ?></span>
			</a>
			<span class="item-count"><?php echo absint( WC()->cart->get_cart_contents_count() ); ?></span>
		</div>
	<?php
	}
}

if ( ! function_exists( 'infinity_news_woocommerce_header_cart()' ) ) {
	/**
	 * Display Header Cart.
	 *
	 * @return void
	 */
	function infinity_news_woocommerce_header_cart() {
		if ( is_cart() ) {
			$class = 'current-menu-item';
		} else {
			$class = '';
		}
		?>
			<div class="minicart-title-handle">
                <i class="twp-nav-icon ion ion-md-cart"></i>
				<?php infinity_news_woocommerce_cart_link() ?>
			</div>
	        <div class="minicart-content">
	            <ul class="site-header-cart">
	                <li class="total-details <?php echo esc_attr( $class ); ?>">
	                	<?php infinity_news_woocommerce_cart_link() ?>
	                </li>
	                <li>
	                    <?php
	                    $instance = array(
	                        'title' => '',
	                    );

	                    the_widget( 'WC_Widget_Cart', $instance );
	                    ?>
	                </li>
	            </ul>
	        </div>
		<?php
	}
}