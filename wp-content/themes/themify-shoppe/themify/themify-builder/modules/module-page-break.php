<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Page break
 * Description: Page breaker and pagination
 */
class TB_Page_Break_Module extends Themify_Builder_Component_Module {
    
	public function __construct() {
		parent::__construct('page-break');
	}
        
	public function get_name(){
            return __('Page Break', 'themify');
        }
        
	public function get_icon(){
	    return false;
	}

	protected function _visual_template() { 
		?>
		<div class="module module-<?php echo $this->slug ; ?>">
		<?php _e('PAGE BREAK - ', 'themify') ?>
		<span class="page-break-order"></span>
		</div>
	<?php
	}
}

new TB_Page_Break_Module();
