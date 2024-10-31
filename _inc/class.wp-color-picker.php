<?php

class Wp_Color_Picker{
 
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array($this, 'wpd_enqueue_color_picker' ));
	}
	
	public $pickers = array();
	
	public function wpd_enqueue_color_picker($hook) {
		if ( 'widgets.php' != $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wpd-scripts', POSTSNIPPET__PLUGIN_URL . 'js/wpd.js', array( 'wp-color-picker' ), false, true );
	}
	
	public function draw($elements) {
	
		foreach($elements as $el) {
			if( !empty( $el['id'] ) ) {
				$el['name'] = !empty( $el['name'] ) ? $el['name'] : 'color_picker';
				$el['value'] = !empty( $el['value'] ) ? $el['value'] : '';
				$el['class'] = !empty( $el['class'] ) ? $el['class'] : '';
				$el['container_id'] = !empty( $el['container_id'] ) ? $el['container_id'] : 'body';
				$el['built_in'] = array(
					'defaultColor' => (!empty( $el['defaultColor'] ) ? $el['defaultColor'] : '#fff'),
					'change' => !empty( $el['change'] ) ? $el['defaultColor'] : 'function(event, ui){}',
					'clear' => !empty( $el['clear'] ) ? $el['clear'] : 'function() {}',
					'hide' => !empty( $el['hide'] ) ? $el['hide'] : 'false',
					'palettes' => !empty( $el['palettes'] ) ? $el['palettes'] : 'true'
				);
				
				$this->pickers[] = $el;
			}
		}
		wp_localize_script( 'wpd-scripts', 'colorPickerJS', $this->pickers );	
	}
	
	public function localize_js(){
		wp_localize_script( 'wpd-scripts', 'colorPickerJS', $this->pickers );		
	}
}

?>