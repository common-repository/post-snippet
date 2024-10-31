<?php
/**
 * Plugin Name: Post Snippet
 * Plugin URI: https://wordpress.org/plugins/wp-order-by/
 * Description: Display snippets of data from a post as a widget.
 * Version: 1.0.2
 * Author: Uri Weil
 * Text Domain: post_snippet_plugin
 * Domain Path: /lang/
 * License: GPL2
 */
/*  Copyright  2015  by WEIL URI  (email : weiluri@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/ 

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'POSTSNIPPET__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'POSTSNIPPET__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

//check for theme support, add if necessary and add new image size
function addFeaturedImageSupport()
{
    $supportedTypes = get_theme_support( 'post-thumbnails' );
	
    if( $supportedTypes === false ) {
        add_theme_support( 'post-thumbnails' );               
    }
}
add_action( 'after_setup_theme',     'addFeaturedImageSupport' , 11 );

/**
* Register scripts and styles
*/

// front-end scripts and styles
function post_snippet_enqueue_admin_scripts_and_styles(){
	wp_enqueue_script('jquery');
	wp_enqueue_script( 'post_snippet_scripts',  POSTSNIPPET__PLUGIN_URL . 'js/post-snippet.js', array( 'jquery' ) );
	wp_enqueue_style( 'post_snippet_styles',  POSTSNIPPET__PLUGIN_URL . 'css/post-snippet.css' );
}
add_action('wp_enqueue_scripts', 'post_snippet_enqueue_admin_scripts_and_styles');

// back-end scripts and styles
function post_snippet_enqueue_scripts_and_styles(){
	wp_enqueue_style( 'post_snippet_styles',  POSTSNIPPET__PLUGIN_URL . 'css/post-snippet-admin.css' ); 
}
add_action('admin_enqueue_scripts', 'post_snippet_enqueue_scripts_and_styles');


/**
 * Register our sidebars and widgetized areas.
 *
 */
function register_post_snippet_sidebars() {

	register_sidebar( array(
		'name'          => 'Before Content Area',
		'description'   => 'Appears before the content section of the page.',
		'id'            => 'before_content_sidebar',
		'before_widget' => '<div id="%1$s" class="%2$s ps_before">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="rounded">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => 'After Content Area',
		'description'   => 'Appears after the content section of the page.',
		'id'            => 'after_content_sidebar',
		'before_widget' => '<div id="%1$s" class="%2$s ps_after">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="rounded">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'register_post_snippet_sidebars',10,2 );


function display_before_content_widgets( $content ) {
	global $wp_query;	
		
		if ( is_active_sidebar( 'before_content_sidebar' ) ) {
			dynamic_sidebar('before_content_sidebar');
		}
		echo $content;
		if ( is_active_sidebar( 'after_content_sidebar' ) ) {
			dynamic_sidebar('after_content_sidebar');
		}
	return '';
}
add_filter( 'the_content', 'display_before_content_widgets', 9999 );


/****************************************************BEGIN WIDGET CLASS*****************************************************/

/**
 * Adds Post_snippet_Widget widget.
 */
class Post_snippet_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Post_snippet_Widget', // Base ID
			__( 'Post Snippet', 'post_snippet_plugin' ), // Name
			array( 'description' => __( 'Show Post as a Snippet with shortened content and with visual flexibility', 'post_snippet_plugin' ), ) // Args
		);
				
		include('_inc/class.wp-color-picker.php');
		$cp = new Wp_Color_Picker();
		$this->color_picker = $cp;
	}
	
	/**
	* Private helpers and vars.
	 */	
	const TEXT_DOMAIN = 'post_snippet_plugin';
	
	
	private $templates = array(
		array(
			'name' => 'narrow',
			'description' => 'Vertical Template'
		),
		array(
			'name' => 'wide',
			'description' => 'Horizontal Template'
		)
	);
	
	private function get_all_public_post_types_names() {  
		$args = array(
		   'public'   => true
		);
		return get_post_types( $args, 'names' ); 
	}
	
	public $color_picker;
	
	/**
	* Prints html options tags based on params of the class in the format of array with name and description (e.g. $templates)
	*/
	private function populate_template_selectbox( $name ) {
	
		$str = '';
		foreach($this->templates as $tmpl) {
			if( $tmpl['name'] == $name ) {
				$selected = ' selected ';
			} else {
				$selected = '';
			}
			$str .= '<option value="'.$tmpl['name'].'"'.$selected.'>'.$tmpl['description'].'</option>';
		}
		echo $str;
	}
	
	private function populate_post_type_selectbox( $arr_pt, $selected_arr ) {
	
		$str = '';
		foreach($arr_pt as $name) {
			if( in_array( $name, $selected_arr ) ){
				$selected = ' selected ';
			} else {
				$selected = '';
			}
			$str .= '<option value="'.$name.'"'.$selected.'>'.$name.'</option>';
		}
		echo $str;
	}
	
	private function draw_blog_categories($cats) {
		$args = array(
			'show_option_all'    => 'On all categories',
			'show_option_none'   => '',
			'option_none_value'  => '-1',
			'orderby'            => 'NAME', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => 0,
			'hierarchical'       => 1, 
			'name'               => $this->get_field_name( 'only_on_categories' ).'[]',
			'id'                 => $this->get_field_name( 'only_on_categories' ),
			'class'              => 'only_on_categories',
			'depth'              => 0,
			'tab_index'          => 0,
			'taxonomy'           => 'category',
			'hide_if_empty'      => false,
			'value_field'	     => 'term_id',	
		);
		$sb = wp_dropdown_categories($args);
		$sb = str_replace( '<select','<select multiple ', $sb );
		
		if($cats[0] != "0" && !empty($cats[0])) {
			$sb = str_replace( 'selected','', $sb );
			foreach($cats as $c) {
				$sb = str_replace( 'value="'.$c.'"', 'value="'.$c.'" selected ', $sb );
			}
		}
		return $sb;
	}
	
	private function custom_excerpt($text, $limit) {
		return wp_trim_words( $text, $limit, '').'...';
	}
	
	private function view( $instance, $args ) {
		$name = $instance['template'];
		$file = POSTSNIPPET__PLUGIN_DIR . 'views/'. $name . '.php';
		include( $file );
	}
	
	private function make_posts_select_box($type = '', $args = array('post_type' => 'post'), $post_id = array(''), $field_name = array(''), $select_default_text = '') {
		if( empty( $field_name) ) return;
		$posts = get_posts($args);
		$selected_default = '';
		if( empty( $post_id[0] ) ) $selected_default = ' selected ';
		echo '<select id="'.$this->get_field_id( $field_name ).'" name="'.$this->get_field_name( $field_name ).'[]" class="post_snippet_select" '.$type.'>';
		if( !empty($select_default_text) ) echo '<option value="" '.$selected_default.' >'. __($select_default_text, Post_snippet_Widget::TEXT_DOMAIN).'</option>';
			foreach($posts as $p) { //iterate posts
				$selected = '';
				foreach($post_id as $pid) { //iterate selected posts
					if($p->ID == $pid && !empty( $post_id[0] ) ) {
						$selected = ' selected ';
					}
				}
				echo '<option value="'.$p->ID.'"'.$selected.'>'.$p->post_title.'</option>';
			}
		echo '</select>';
	}
	
	private function get_post_cats($post_id) {
		$post_categories = wp_get_post_categories( $post_id );
		$cats = array();
		
		foreach($post_categories as $c){
			$cat = get_category( $c );
			$cats[] = $cat->term_id ;
		}
		return $cats;
	}
	
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */

	public function widget( $args, $instance ) {
		
		global $post;
		$temp = $post;
		$dont_display = false;
		
		$post_cats = $this->get_post_cats($temp->ID);
		$instance_cats = $instance['only_on_categories'];
		
		if( empty($instance['post_id']) ) {
			$dont_display = true;
		} elseif( !in_array( $post->ID, $instance['where_to_display'] ) && !empty($instance['where_to_display'][0]) ) {
			$dont_display = true;
		} elseif( in_array( $temp->post_type, $instance['exclude_post_types'] ) ) {
			$dont_display = true;
		} elseif( !empty( $instance_cats ) && $instance_cats[0] != "0" ) {
			foreach( $instance_cats as $ic) {
				if( !in_array( $ic, $post_cats ) ) {
					$dont_display = true;
				} else {
					$dont_display = false;
					break;
				}
			}
		} elseif( $instance['dont_display_on_self'] && $temp->ID == $instance['post_id'] ) {
			$dont_display = true;
		}
		
		if( !$dont_display ) {
			echo $args['before_widget'];			
			$instance['post_id'] = $instance['post_id'][0];
			$post = get_post( $instance['post_id'] );
			if( !empty($post) ) {
				
				setup_postdata( $post );
				$height = ($instance['template'] == 'wide') ? 'height:'.$instance['height'].'px;' : '';
				$style_bg = ( !empty($instance['bg_color']) ) ? ' style="background-color:'. $instance['bg_color'].';'.$height.'" ' : '';
				$style_title = ( !empty($instance['title_color']) ) ? ' style="color:'. $instance['title_color'].';" ' : '';
				$style_content = ( !empty($instance['content_color']) ) ? ' style="color:'. $instance['content_color'].';" ' : '';
				
				// content
				$instance['content_post'] = $post;
				$instance['content'] = $post->post_content;
				$tmpexcerpt = strip_tags(strip_shortcodes($post->post_content));
				$instance['excerpt'] = $this->custom_excerpt($tmpexcerpt, $instance['words']);				
				$instance['excerpt-responsive'] = $this->custom_excerpt($tmpexcerpt, $instance['words_responsive']);				
				$instance['alt_title'] = !empty( $instance['alt_title'] ) ? trim($instance['alt_title'], ' ') : ''; 
				$instance['title'] = !empty( $instance['alt_title'] ) ? $instance['alt_title'] : $instance['title'];
				$trim_exc = trim($instance['alt_excerpt'],' ');
				$instance['excerpt'] = !empty( $trim_exc ) ? $trim_exc : $instance['excerpt'];
				
				// style
				$instance['style_bg'] = $style_bg;
				$instance['style_title'] = $style_title;
				$instance['style_content'] = $style_content;
								
				$this->view( $instance, $args);
				
				wp_reset_postdata();
			}
			$post = $temp;		
			echo $args['after_widget'];
		}
	}
	
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		$args  = array(
			'posts_per_page'   => 99999,
			'post_type'		=> get_post_types(array( 'public'   => true ),'names'),
			'orderby'		=> 'title',
			'order'		=> 'ASC'
		);
		$posts = get_posts($args);
		$default_title = '#2b2b2b';
		$default_content = '#2b2b2b';
		$default_bg = '#7c8a93';
		$default_template = 'wide';
		$post_id = ! empty( $instance['post_id'] ) ? $instance['post_id'] : array('');
		$where_to_display = ! empty( $instance['where_to_display'] ) ? $instance['where_to_display'] : array('');
		$exclude_post_types = ! empty( $instance['exclude_post_types'] ) ? $instance['exclude_post_types'] : array('');
		$only_on_categories = ! empty( $instance['only_on_categories'] ) ? $instance['only_on_categories'] : array('');
		$alt_title = ! empty( $instance['alt_title'] ) ? $instance['alt_title'] : '';
		$alt_excerpt = ! empty( $instance['alt_excerpt'] ) ? $instance['alt_excerpt'] : '';
		$dont_display_excerpt = ! empty( $instance['dont_display_excerpt'] ) ? ' checked ' : '';
		$dont_display_excerpt_responsive = ! empty( $instance['dont_display_excerpt_responsive'] ) ? ' checked ' : '';
		$template = ! empty( $instance['template'] ) ? $instance['template'] : $default_template;
		$title_color = ! empty( $instance['title_color'] ) ? $instance['title_color'] : $default_title;
		$content_color = ! empty( $instance['content_color'] ) ? $instance['content_color'] : $default_content;
		$bg_color = ! empty( $instance['bg_color'] ) ? $instance['bg_color'] : $default_bg;
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$dont_display_title = ! empty( $instance['dont_display_title'] ) ? ' checked ' : '';
		$dont_display_title_responsive = ! empty( $instance['dont_display_title_responsive'] ) ? ' checked ' : '';
		$dont_display_image = ! empty( $instance['dont_display_image'] ) ? ' checked ' : '';
		$dont_display_image_responsive = ! empty( $instance['dont_display_image_responsive'] ) ? ' checked ' : '';
		$dont_display_on_self = ! empty( $instance['dont_display_on_self'] ) ? ' checked ' : '';
		$is_accordion = ! empty( $instance['is_accordion'] ) ? ' checked ' : '';
		$height = ! empty( $instance['height'] ) ? $instance['height'] : 100;
		$words = ! empty( $instance['words'] ) ? $instance['words'] : '15';
		$words_responsive = ! empty( $instance['words_responsive'] ) ? $instance['words_responsive'] : '10';
		?>
		
		<input class="widget_title" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="hidden" value="<?php echo $title; ?>">
		
		<div class="section_container sec<?php echo $this->number; ?>">
			<h4 class="content_section_title" style=" text-align: center;"><span  style="font-weight: bold;"><?php _e( 'Content Settings', Post_snippet_Widget::TEXT_DOMAIN ); ?></span></h4>
			<div class="ps_elements">
				<p>
				<label for="<?php echo $this->get_field_id( 'post_id' ); ?>"  class="ps_element_title post_id"><?php _e( 'Take content from:' ); ?></label> 
					<?php $this->make_posts_select_box(false, $args, $post_id, 'post_id', ''); ?>
				</p>
				<p class="option_or_container">		
					<label for="<?php echo $this->get_field_id( 'alt_title' ); ?>" class="ps_element_title alt_title"><?php _e( 'Override post title:', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
					<input class="alt_title" id="<?php echo $this->get_field_id( 'alt_title' ); ?>" name="<?php echo $this->get_field_name( 'alt_title' ); ?>" type="text" value="<?php echo $alt_title; ?>">
					<span class="option_or">- Or -</span>
					<input class="dont_display_title" id="<?php echo $this->get_field_id( 'dont_display_title' ); ?>" name="<?php echo $this->get_field_name( 'dont_display_title' ); ?>" type="checkbox" <?php echo $dont_display_title; ?>>		
					<label for="<?php echo $this->get_field_id( 'dont_display_title' ); ?>" class="ps_element_title dont_display_title"><?php _e( 'Do not display title', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
				</p>
				<p>
				<p class="option_or_container">		
					<label for="<?php echo $this->get_field_id( 'alt_excerpt' ); ?>" class="ps_element_title"><?php _e( 'Override post excerpt:', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
					<textarea class="alt_excerpt" id="<?php echo $this->get_field_id( 'alt_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'alt_excerpt' ); ?>"><?php echo $alt_excerpt; ?></textarea>		
					<label for="<?php echo $this->get_field_id( 'words' ); ?>" class="ps_element_title"><?php _e( 'Limit words of excerpt:', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
					<input class="widget_words" id="<?php echo $this->get_field_id( 'words' ); ?>" name="<?php echo $this->get_field_name( 'words' ); ?>" type="number" value="<?php echo $words; ?>">
					<span class="ps_remark">
						*Only for Horizontal template (default). You can change template under <i>Style &amp; Responsive Settings</i> 
					</span>
					<span class="option_or">- Or -</span>
					<input class="dont_display_excerpt" id="<?php echo $this->get_field_id( 'dont_display_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'dont_display_excerpt' ); ?>" type="checkbox" <?php echo $dont_display_excerpt; ?>>		
					<label for="<?php echo $this->get_field_id( 'dont_display_excerpt' ); ?>" class="ps_element_title dont_display_excerpt"><?php _e( 'Do not display excerpt', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
				</p>
				<p class="option_or_container">
					<input class="dont_display_image" id="<?php echo $this->get_field_id( 'dont_display_image' ); ?>" name="<?php echo $this->get_field_name( 'dont_display_image' ); ?>" type="checkbox" <?php echo $dont_display_image; ?>>		
					<label for="<?php echo $this->get_field_id( 'dont_display_image' ); ?>" class="ps_element_title dont_display_image"><?php _e( 'Do not display featured image', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
				</p>
			</div>
		</div>
		<div class="section_container sec<?php echo $this->number; ?>">
			<h4 class="display_section_title" style=" text-align: center;"><span  style="font-weight: bold;"><?php _e( 'Where-To-Display Settings', Post_snippet_Widget::TEXT_DOMAIN ); ?></span></h4>
			<div class="ps_elements">
				<p class="where_to_display">
				<label for="<?php echo $this->get_field_id( 'where_to_display' ); ?>" class="ps_element_title"><?php _e( 'Show only on these pages:' ); ?></label> 
					<?php $this->make_posts_select_box('multiple', $args, $where_to_display, 'where_to_display', 'On all pages'); ?>
				</p>
				<p>
				<label for="<?php echo $this->get_field_id( 'exclude_post_types' ); ?>"  class="ps_element_title exclude_post_types"><?php _e( 'Exclude from these post-types:' ); ?></label> 
					<select multiple id="<?php echo $this->get_field_id( 'exclude_post_types' ); ?>" name="<?php echo $this->get_field_name( 'exclude_post_types' ); ?>[]" class="exclude_post_types">
						<option value="" <?php if( empty( $exclude_post_types[0] ) ) echo ' selected '  ?>><?php _e( 'Do not exclude any post-type', Post_snippet_Widget::TEXT_DOMAIN ); ?></option>
						<?php $this->populate_post_type_selectbox($this->get_all_public_post_types_names(), $exclude_post_types); ?>
					</select>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id( 'only_on_categories' ); ?>"  class="ps_element_title only_on_categories"><?php _e( 'Show only on posts belong to these categories:' ); ?></label> 
					<?php echo $this->draw_blog_categories($only_on_categories); ?>
				</p>
				<p>
					<input class="dont_display_on_self" id="<?php echo $this->get_field_id( 'dont_display_on_self' ); ?>" name="<?php echo $this->get_field_name( 'dont_display_on_self' ); ?>" type="checkbox" <?php echo $dont_display_on_self; if(empty($instance)) echo 'checked'; ?> >		
					<label for="<?php echo $this->get_field_id( 'dont_display_on_self' ); ?>" class="ps_element_title dont_display_on_self"><?php _e( 'Do not display when on page', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
					<span class="option_or template"><?php _e( 'If checked then the widget is hidden when it is on the page that it displays its content or linked to.', Post_snippet_Widget::TEXT_DOMAIN ); ?></span>
				</p>
			</div>
		</div>
		
		<div class="section_container sec<?php echo $this->number; ?>">
			<h4 class="style_section_title" style=" text-align: center;"><span  style="font-weight: bold;"><?php _e( 'Style & Responsive Settings', Post_snippet_Widget::TEXT_DOMAIN ); ?></span></h4>
			<div class="ps_elements">
				<p class="title_color_container">		
				<label for="<?php echo $this->get_field_id( 'title_color' ); ?>" class="ps_element_title title_color"><?php _e( 'Title color:', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
				<input class="" id="<?php echo $this->get_field_id( 'title_color' ); ?>" name="<?php echo $this->get_field_name( 'title_color' ); ?>" type="text" value="<?php echo $title_color; ?>">
				</p>
				
				<p>
				<label for="<?php echo $this->get_field_id( 'content_color' ); ?>" class="ps_element_title content_color"><?php _e( 'Content color:', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
				<input class="" id="<?php echo $this->get_field_id( 'content_color' ); ?>" name="<?php echo $this->get_field_name( 'content_color' ); ?>" type="text" value="<?php echo $content_color; ?>">
				</p>
				
				<p>
				<label for="<?php echo $this->get_field_id( 'bg_color' ); ?>" class="ps_element_title bg_color"><?php _e( 'Background color:', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
				<input class="" id="<?php echo $this->get_field_id( 'bg_color' ); ?>" name="<?php echo $this->get_field_name( 'bg_color' ); ?>" type="text" value="<?php echo $bg_color; ?>">
				</p>
				
				<p>
				<label for="<?php echo $this->get_field_id( 'template' ); ?>" class="ps_element_title template"><?php _e( 'Widget template</b>:', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 				
				<select class="widget_template" id="<?php echo $this->get_field_id( 'template' ); ?>" name="<?php echo $this->get_field_name( 'template' ); ?>">
					<?php $this->populate_template_selectbox( $template ); ?>
				</select>
				</p>
				
				<p>
				<label for="<?php echo $this->get_field_id( 'height' ); ?>" class="ps_element_title widget_height"><?php _e( 'Widget height <span>(only for horizontal template)</span>:', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
				<input class="widget_height" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="number" max="250" value="<?php echo $height; ?>"> px
				</p>
				
				<p>
				<input class="is_accordion" type="checkbox" id="<?php echo $this->get_field_id( 'is_accordion' ); ?>" name="<?php echo $this->get_field_name( 'is_accordion' ); ?>" <?php echo $is_accordion; if(empty($instance)) echo 'checked'; ?> >
				<label for="<?php echo $this->get_field_id( 'is_accordion' ); ?>" class="ps_element_title accordion"><?php _e( 'Folding as accordion on click (instead of link)', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
				</p>
				
				<br>
				<span class="responsive_title">
					Responsive Settings (Phone sizes)
				</span>
				<br>
				<span class="responsive_remark"> 
					(Horizontal template)
				</span>
				<div class="option_or_container">
					<p class="">
						<input class="dont_display_title_responsive" id="<?php echo $this->get_field_id( 'dont_display_title_responsive' ); ?>" name="<?php echo $this->get_field_name( 'dont_display_title_responsive' ); ?>" type="checkbox" <?php echo $dont_display_title_responsive; ?>>		
						<label for="<?php echo $this->get_field_id( 'dont_display_title_responsive' ); ?>" class="ps_element_title dont_display_title"><?php _e( 'Do not display title', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
					</p> 
					<p class="">
						<input class="dont_display_excerpt_responsive" id="<?php echo $this->get_field_id( 'dont_display_excerpt_responsive' ); ?>" name="<?php echo $this->get_field_name( 'dont_display_excerpt_responsive' ); ?>" type="checkbox" <?php echo $dont_display_excerpt_responsive; ?>>		
						<label for="<?php echo $this->get_field_id( 'dont_display_excerpt_responsive' ); ?>" class="ps_element_title dont_display_excerpt"><?php _e( 'Do not display excerpt', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
					</p> 
					<p class="">
						<input class="dont_display_image_responsive" id="<?php echo $this->get_field_id( 'dont_display_image_responsive' ); ?>" name="<?php echo $this->get_field_name( 'dont_display_image_responsive' ); ?>" type="checkbox" <?php echo $dont_display_image_responsive; ?>>		
						<label for="<?php echo $this->get_field_id( 'dont_display_image_responsive' ); ?>" class="ps_element_title dont_display_image"><?php _e( 'Do not display featured image', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
					</p> 
					<p class="">
						<label for="<?php echo $this->get_field_id( 'words_responsive' ); ?>" class="ps_element_title"><?php _e( 'Limit words of excerpt:', Post_snippet_Widget::TEXT_DOMAIN ); ?></label> 
						<input class="widget_words" id="<?php echo $this->get_field_id( 'words_responsive' ); ?>" name="<?php echo $this->get_field_name( 'words_responsive' ); ?>" type="number" value="<?php echo $words_responsive; ?>">
					</p> 
				</div>
				<br>
			</div> 
		</div>
		<?php 
		if( 'post_snippet_widget-__i__' != $this->id ) {
			$elements = array(
				array(
					'id' => $this->get_field_id( 'title_color' ),
				),
				array(
					'id' => $this->get_field_id( 'content_color' ),
				),
				array(
					'id' => $this->get_field_id( 'bg_color' ),
				)
			);
			$this->color_picker->draw($elements); 
		}
	}


	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['where_to_display'] = array();

        if ( isset ( $new_instance['where_to_display'] ) )
        {
            foreach ( $new_instance['where_to_display'] as $value )
            {
                    $instance['where_to_display'][] = $value;
            }
        }
		
		$instance['exclude_post_types'] = array();

        if ( isset ( $new_instance['exclude_post_types'] ) )
        {
            foreach ( $new_instance['exclude_post_types'] as $value )
            {
                    $instance['exclude_post_types'][] = $value;
            }
        }
		
		$instance['only_on_categories'] = array();

        if ( isset ( $new_instance['only_on_categories'] ) )
        {
            foreach ( $new_instance['only_on_categories'] as $value )
            {
                    $instance['only_on_categories'][] = $value;
            }
        }
		
		$instance['post_id'] = array();
		if ( isset ( $new_instance['post_id'] ) ) {
			$instance['post_id'] = $new_instance['post_id'];
		}
		
		$instance['title_color'] = ( ! empty( $new_instance['title_color'] ) ) ? strip_tags( $new_instance['title_color'] ) : '';
		$instance['content_color'] = ( ! empty( $new_instance['content_color'] ) ) ? strip_tags( $new_instance['content_color'] ) : '';
		$instance['bg_color'] = ( ! empty( $new_instance['bg_color'] ) ) ? strip_tags( $new_instance['bg_color'] ) : '';
		$instance['template'] = ( ! empty( $new_instance['template'] ) ) ? strip_tags( $new_instance['template'] ) : '';
		$instance['height'] = ( ! empty( $new_instance['height'] ) ) ? strip_tags( $new_instance['height'] ) : 100;
		$instance['is_accordion'] = ( ! empty( $new_instance['is_accordion'] ) ) ? strip_tags( $new_instance['is_accordion'] ) : '';
		$instance['words'] = ( ! empty( $new_instance['words'] ) ) ? strip_tags( $new_instance['words'] ) : '';
		$instance['words_responsive'] = ( ! empty( $new_instance['words_responsive'] ) ) ? strip_tags( $new_instance['words_responsive'] ) : '';
		$instance['title'] = ( get_post($new_instance['post_id'][0]) ) ? get_post($new_instance['post_id'][0])->post_title : '';		
		$instance['alt_title'] = ( ! empty( $new_instance['alt_title'] ) ) ? strip_tags( $new_instance['alt_title'] ) : '';
		$instance['dont_display_title'] = ( ! empty( $new_instance['dont_display_title'] ) ) ? strip_tags( $new_instance['dont_display_title'] ) : '';
		$instance['dont_display_title_responsive'] = ( ! empty( $new_instance['dont_display_title_responsive'] ) ) ? strip_tags( $new_instance['dont_display_title_responsive'] ) : '';
		$instance['alt_excerpt'] = ( ! empty( $new_instance['alt_excerpt'] ) ) ? strip_tags( $new_instance['alt_excerpt'] ) : '';
		$instance['dont_display_excerpt'] = ( ! empty( $new_instance['dont_display_excerpt'] ) ) ? strip_tags( $new_instance['dont_display_excerpt'] ) : '';
		$instance['dont_display_excerpt_responsive'] = ( ! empty( $new_instance['dont_display_excerpt_responsive'] ) ) ? strip_tags( $new_instance['dont_display_excerpt_responsive'] ) : '';
		$instance['dont_display_image'] = ( ! empty( $new_instance['dont_display_image'] ) ) ? strip_tags( $new_instance['dont_display_image'] ) : '';
		$instance['dont_display_image_responsive'] = ( ! empty( $new_instance['dont_display_image_responsive'] ) ) ? strip_tags( $new_instance['dont_display_image_responsive'] ) : '';
		$instance['dont_display_on_self'] = ( ! empty( $new_instance['dont_display_on_self'] ) ) ? strip_tags( $new_instance['dont_display_on_self'] ) : '';
		return $instance;
	}

} // end class Post_snippet_Widget


// register Post_snippet_Widget widget
function register_Post_snippet_Widget() {
    register_widget( 'Post_snippet_Widget' );
}
add_action( 'widgets_init', 'register_Post_snippet_Widget' );

?>