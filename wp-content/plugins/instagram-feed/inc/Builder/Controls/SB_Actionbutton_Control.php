<?php
/**
 * Customizer Builder
 * Action Button Control
 *
 * @since 4.0
 */
namespace InstagramFeed\Builder\Controls;

if(!defined('ABSPATH'))	exit;

class SB_Actionbutton_Control extends SB_Controls_Base{

	/**
	 * Get control type.
	 *
	 * Getting the Control Type
	 *
	 * @since 4.0
	 * @access public
	 *
	 * @return string
	*/
	public function get_type(){
		return 'actionbutton';
	}

	/**
	 * Output Control
	 *
	 *
	 * @since 4.0
	 * @access public
	 *
	 * @return HTML
	*/
	public function get_control_output($controlEditingTypeModel){
		?>
		<button class="sb-control-action-button sb-btn sbi-fb-fs sb-btn-grey">
			<div v-if="control.buttonIcon" v-html="svgIcons[control.buttonIcon]"></div>
			<span class="sb-small-p sb-bold sb-dark-text">{{control.label}}</span>
		</button>
		<?php
	}

}