<?php
/**
 * Customizer Builder
 * CheckBox Section Control
 *
 * @since 4.0
 */
namespace InstagramFeed\Builder\Controls;

if(!defined('ABSPATH'))	exit;

class SB_Checkboxsection_Control extends SB_Controls_Base{

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
		return 'checkboxsection';
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
		<div class="sb-control-checkboxsection-header" v-if="control.header">
			<div class="sb-control-checkboxsection-name">
				<div v-html="svgIcons['preview']"></div>
				<strong class="">{{genericText.name}}</strong>
			</div>
			<strong>{{genericText.edit}}</strong>
		</div>
		<div class="sb-control-checkbox-ctn sbi-fb-fs" @click.prevent.default="switchNestedSection(control.section.id, control.section)">
			<div class="sb-control-checkbox-hover sb-tr-2"></div>
			<div class="sb-control-checkbox" @click.stop.prevent.default="changeCheckboxSectionValue(control.id, control.value)" :data-active="checkboxSectionValueExists(control.id, control.value)"></div>
			<div class="sbi-fb-fs" :data-active="checkboxSectionValueExists(control.id, control.value)">
				<strong class="sb-control-label">{{control.label}}</strong>
			</div>
			<div class="sb-control-checkboxsection-btn"></div>
		</div>
		<?php
	}

}