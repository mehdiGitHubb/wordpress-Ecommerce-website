<?php
/**
 * SBI Tooltip Wizard
 *
 *
 * @since 6.0
 */
namespace InstagramFeed\Builder;

class SBI_Tooltip_Wizard {

	/**
	 * Constructor.
	 *
	 * @since 6.0
	 */
	function __construct(){
		$this->init();
	}


	/**
	 * Initialize class.
	 *
	 * @since 6.0
	 */
	public function init() {
		/*
		if (
			! wpforms_is_admin_page( 'builder' ) &&
			! wp_doing_ajax() &&
			! $this->is_form_embed_page()
		) {
			return;
		}
		*/

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 6.0
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
		add_action( 'admin_footer', [ $this, 'output' ] );
	}


	/**
	 * Enqueue assets.
	 *
	 * @since 6.0
	 */
	public function enqueues() {

		wp_enqueue_style(
			'sbi_tooltipster',
			SBI_PLUGIN_URL . 'admin/builder/assets/css/tooltipster.css',
			null,
			SBIVER
		);

		wp_enqueue_script(
			'sbi_tooltipster',
			SBI_PLUGIN_URL . 'admin/builder/assets/js/jquery.tooltipster.min.js',
			[ 'jquery' ],
			SBIVER,
			true
		);

		wp_enqueue_script(
			'sbi-admin-tooltip-wizard',
			SBI_PLUGIN_URL . 'admin/builder/assets/js/tooltip-wizard.js',
			[ 'jquery' ],
			SBIVER
		);

		$wp_localize_data = [];
		if( $this->check_gutenberg_wizard() ){
			$wp_localize_data['sbi_wizard_gutenberg'] = true;
		}

		wp_localize_script(
			'sbi-admin-tooltip-wizard',
			'sbi_admin_tooltip_wizard',
			$wp_localize_data
		);
	}

	/**
	 * Output HTML.
	 *
	 * @since 6.0
	 */
	public function output() {
		if( $this->check_gutenberg_wizard() ){
			$this->gutenberg_tooltip_output();
		}

	}

	/**
	 * Gutenberg Tooltip Output HTML.
	 *
	 * @since 6.0
	 */
	public function check_gutenberg_wizard() {
		global $pagenow;
		return  (	( $pagenow == 'post.php' ) || (get_post_type() == 'page') )
				&& ! empty( $_GET['sbi_wizard'] );
	}


	/**
	 * Gutenberg Tooltip Output HTML.
	 *
	 * @since 6.0
	 */
	public function gutenberg_tooltip_output() {
		?>
		<div id="sbi-gutenberg-tooltip-content">
			<div class="sbi-tlp-wizard-cls sbi-tlp-wizard-close"></div>
			<div class="sbi-tlp-wizard-content">
				<strong class="sbi-tooltip-wizard-head"><?php echo __('Add a Block','instagram-feed') ?></strong>
				<p class="sbi-tooltip-wizard-txt"><?php echo __('Click the plus button, search for Instagram Feed','instagram-feed'); ?>
                    <br/><?php echo __('Feed, and click the block to embed it.','instagram-feed') ?> <a href="https://smashballoon.com/doc/wordpress-5-block-page-editor-gutenberg/?instagram&utm_campaign=instagram-free&utm_source=add-block-tooltip" rel="noopener" target="_blank"><?php echo __('Learn More','instagram-feed') ?></a></p>
				<div class="sbi-tooltip-wizard-actions">
					<button class="sbi-tlp-wizard-close"><?php echo __('Done','instagram-feed') ?></button>
				</div>
			</div>
		</div>
		<?php
	}


}
