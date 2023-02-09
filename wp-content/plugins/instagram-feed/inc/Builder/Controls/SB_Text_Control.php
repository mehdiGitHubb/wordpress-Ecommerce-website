<?php
/**
 * Customizer Builder
 * Text Field Control
 *
 * @since 4.0
 */
namespace InstagramFeed\Builder\Controls;

if(!defined('ABSPATH'))	exit;

class SB_Text_Control extends SB_Controls_Base{

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
		return 'text';
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
		<div class="sb-control-input-ctn sbi-fb-fs">
			<div class="sb-control-input-info" v-if="control.fieldPrefix">{{control.fieldPrefix.replace(/ /g,"&nbsp;")}}</div>
			<input type="text" class="sb-control-input sbi-fb-fs" v-model="<?php echo $controlEditingTypeModel ?>[control.id]" @change.prevent.default="changeSettingValue(control.id, false,false, control.ajaxAction ? control.ajaxAction : false)"  :placeholder="control.placeholder ? control.placeholder : ''">
			<div class="sb-control-input-info" v-if="control.fieldSuffix">{{control.fieldSuffix.replace(/ /g,"&nbsp;")}}</div>
		</div>
		<?php
	}

}