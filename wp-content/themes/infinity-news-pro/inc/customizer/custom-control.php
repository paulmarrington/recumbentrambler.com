<?php
/**
* Layouts Settings.
*
* @package Infinity News
*/

/** Customizer Custom Control **/
if ( class_exists( 'WP_Customize_Control' ) ) {
    
    // Radio Image Custom Control Class.
    class Infinity_News_Custom_Radio_Image_Control extends WP_Customize_Control {

    	public $type = 'radio-image';
    
    	public function render_content() {
    	   
    		if ( empty( $this->choices ) ) {
    			return;
    		}			
    		
    		$name = '_customize-radio-' . $this->id; ?>
            
    		<span class="customize-control-title">
    			<?php echo esc_attr( $this->label ); ?>
    			<?php if ( ! empty( $this->description ) ) : ?>
    				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
    			<?php endif; ?>
    		</span>
            
    		<div id="input_<?php echo esc_attr($this->id); ?>" class="image radio-image-buttenset">
    			<?php foreach ( $this->choices as $value => $label ) : ?>
    				<input class="image-select" type="radio" value="<?php echo esc_attr( $value ); ?>" id="<?php echo esc_attr($this->id) . esc_attr($value); ?>" name="<?php echo esc_attr( $name ); ?>" <?php $this->link(); checked( $this->value(), $value ); ?>>
    					<label for="<?php echo esc_attr($this->id) . esc_attr($value); ?>">
    						<img src="<?php echo esc_html( $label ); ?>" alt="<?php echo esc_attr( $value ); ?>" title="<?php echo esc_attr( $value ); ?>">
    					</label>
    				</input>
    			<?php endforeach; ?>
    		</div>
            
   		<?php }
    }
    
}

/** Customizer Custom Control **/
if ( class_exists( 'WP_Customize_Control' ) ) {
    
    // Radio Image Custom Control Class.
    class Infinity_News_Info_Notiece_Control extends WP_Customize_Control {

        public $type = 'infonotice';
    
        public function render_content() {
           
            $name = '_customize-radio-' . $this->id; ?>
            
            <span class="customize-control-title">
                <span class="twp-notiect-bar"><?php echo esc_html( $this->label ); ?></span>

                <div class="twp-info-icon">
                    <div class="icon-notice-wrap">
                        <span class="dashicons dashicons-move twp-filter-icon"></span>
                        <p><?php esc_html_e( 'Click hold and drag to re-order.', 'infinity-news' ); ?></p>
                    </div>
                    <div class="icon-notice-wrap">
                        <span class="dashicons dashicons-arrow-down twp-filter-icon"></span>
                        <p><?php esc_html_e( 'Click to expand settings.', 'infinity-news' ); ?></p>
                    </div>
                </div>
            </span>
            
        <?php }
    }
    
}

/**
 * Repeater Custom Control
*/
class infinity_news_Repeater_Controler extends WP_Customize_Control {
    /**
     * The control type.
     *
     * @access public
     * @var string
    */
    public $type = 'repeater';

    public $infinity_news_box_label = '';

    public $infinity_news_box_add_control = '';

    private $cats = '';

    /**
     * The fields that each container row will contain.
     *
     * @access public
     * @var array
     */
    public $fields = array();

    /**
     * Repeater drag and drop controler
     *
     * @since  1.0.0
     */
    public function __construct( $manager, $id, $args = array(), $fields = array() ) {
        $this->fields = $fields;
        $this->infinity_news_box_label = $args['infinity_news_box_label'] ;
        $this->infinity_news_box_add_control = $args['infinity_news_box_add_control'];
        $this->cats = get_categories(array( 'hide_empty' => false ));
        parent::__construct( $manager, $id, $args );
    }

    public function render_content() {

        $values = json_decode($this->value());
        ?>
        <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>

        <?php if($this->description){ ?>
            <span class="description customize-control-description">
            <?php echo wp_kses_post($this->description); ?>
            </span>
        <?php } ?>

        <ul class="infinity-news-repeater-field-control-wrap">
            <?php
            $this->infinity_news_get_fields();
            ?>
        </ul>

        <input type="hidden" <?php esc_attr( $this->link() ); ?> class="infinity-news-repeater-collector" value="<?php echo esc_attr( $this->value() ); ?>" />
        <button type="button" class="button infinity-news-add-control-field"><?php echo esc_html( $this->infinity_news_box_add_control ); ?></button>
        <?php
    }

    private function ToObject($Array) { 
      
        // Create new stdClass object 
        $object = new stdClass(); 
          
        // Use loop to convert array into 
        // stdClass object 
        foreach ($Array as $key => $value) { 
            if (is_array($value)) { 
                $value = $this->ToObject($value); 
            } 
            $object->$key = $value; 
        } 
        return $object; 
    } 

    private function infinity_news_get_fields(){

        $fields = $this->fields;

        $values = json_decode( $this->value() );

        if( is_array( $values ) ){
        foreach($values as $value){
        ?>
        <li class="infinity-news-repeater-field-control">

        <div class="title-rep-wrap">
            <span class="dashicons dashicons-move twp-filter-icon"></span>
            <h3 class="infinity-news-repeater-field-title"><?php echo esc_html( $this->infinity_news_box_label ); ?></h3>
            <span class="dashicons dashicons-arrow-down twp-filter-icon"></span>
        </div>

        <div class="infinity-news-repeater-fields">
        <?php
            foreach ($fields as $key => $field) {
            $class = isset($field['class']) ? $field['class'] : '';
            ?>
            <div class="infinity-news-fields infinity-news-type-<?php echo esc_attr($field['type']).' '.$class; ?>">
                <?php 
                    $label = isset($field['label']) ? $field['label'] : '';
                    $description = isset($field['description']) ? $field['description'] : '';
                    if($field['type'] != 'checkbox'){ ?>
                        <span class="customize-control-title"><?php echo esc_html( $label ); ?></span>
                        <span class="description customize-control-description"><?php echo esc_html( $description ); ?></span>
                    <?php 
                    }

                    $new_value = isset($value->$key) ? $value->$key : '';
                    $default = isset($field['default']) ? $field['default'] : '';

                    switch ($field['type']) {
                        case 'text':
                            echo '<input data-default="'.esc_attr($default).'" data-name="'.esc_attr($key).'" type="text" value="'.esc_attr($new_value).'"/>';
                            break;

                        case 'link':
                            echo '<input data-default="'.esc_attr($default).'" data-name="'.esc_attr($key).'" type="text" value="'.esc_url($new_value).'"/>';
                            break;

                        case 'textarea':
                            echo '<textarea data-default="'.esc_attr($default).'"  data-name="'.esc_attr($key).'">'.$new_value.'</textarea>';
                            break;

                        case 'select':
                            $options = $field['options'];
                            echo '<select  data-default="'.esc_attr($default).'"  data-name="'.esc_attr($key).'">';
                                foreach ( $options as $option => $val )
                                {
                                    printf('<option value="%s" %s>%s</option>', esc_attr($option), selected($new_value, $option, false), esc_html($val));
                                }
                            echo '</select>';
                            break;

                        case 'checkbox':
                            echo '<label>';
                            echo '<input data-default="'.esc_attr($default).'" value="'.$new_value.'" data-name="'.esc_attr($key).'" type="checkbox" '.checked($new_value, 'yes', false).'/>';
                            echo esc_html( $label );
                            echo '<span class="description customize-control-description">'.esc_html( $description ).'</span>';
                            echo '</label>';
                            break;

                        case 'selector':
                            $options = $field['options'];
                            echo '<div class="selector-labels">';
                            foreach ( $options as $option => $val ){
                                $class = ( $new_value == $option ) ? 'selector-selected': '';
                                echo '<label class="'.$class.'" data-val="'.esc_attr($option).'">';
                                echo '<img src="'.esc_url($val).'"/>';
                                echo '</label>'; 
                            }
                            echo '</div>';
                            echo '<input data-default="'.esc_attr($default).'" type="hidden" value="'.esc_attr($new_value).'" data-name="'.esc_attr($key).'"/>';
                            break;

                        case 'seperator':
                            echo '<span class="seperator-control-title">'.esc_html( $field['seperator_text'] ).'</span>';
                            break;

                        case 'colorpicker':
                            echo '<input data-default="'.esc_attr($default).'" class="customizer-color-picker" data-alpha="true" data-name="'.esc_attr($key).'" type="text" value="'.esc_attr($new_value).'"/>';
                            break;

                        case 'cache_button':
                            echo '<input class="button customizer-cache-clear" value="'.esc_html__('Clear Cache','infinity-news').'" type="button"/>';
                            break;

                        case 'colorpicker':
                            echo '<input data-default="'.esc_attr($default).'" class="customizer-color-picker" data-alpha="true" data-name="'.esc_attr($key).'" type="text" value="'.esc_attr($new_value).'"/>';
                            break;
                            
                        case 'upload':
                                $image = $image_class= "";
                                if($new_value){ 
                                    $image = '<img src="'.esc_url($new_value).'" style="max-width:100%;"/>';    
                                    $image_class = ' hidden';
                                }
                                echo '<div class="twp-img-fields-wrap">';
                                echo '<div class="attachment-media-view">';
                                echo '<div class="placeholder'.$image_class.'">';
                                esc_html_e('No image selected', 'infinity-news');
                                echo '</div>';
                                echo '<div class="thumbnail thumbnail-image">';
                                echo $image;
                                echo '</div>';
                                echo '<div class="actions clearfix">';
                                echo '<button type="button" class="button twp-img-delete-button align-left">'.esc_html__('Remove', 'infinity-news').'</button>';
                                echo '<button type="button" class="button twp-img-upload-button alignright">'.esc_html__('Select Image', 'infinity-news').'</button>';
                                echo '<input data-default="'.esc_attr($default).'" class="upload-id" data-name="'.esc_attr($key).'" type="hidden" value="'.esc_attr($new_value).'"/>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                                break;

                        default:
                            break;
                    }
                ?>
            </div>
            <?php
            } ?>

            <div class="clearfix infinity-news-repeater-footer">
                <div class="alignright">
                <a class="infinity-news-repeater-field-remove" href="#remove"><?php esc_html_e('Delete', 'infinity-news') ?>|</a> 
                <a class="infinity-news-repeater-field-close" href="#close"><?php esc_html_e('Close', 'infinity-news') ?></a>
                </div>
            </div>
        </div>
        </li>
        <?php   
        }
        }
    }

}