<?php
namespace AIOSEO\Plugin\Lite\Traits;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options trait.
 *
 * @since 4.0.0
 */
trait Options {
	/**
	 * Initialize the options.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function init() {
		parent::init();

		$dbOptions = $this->getDbOptions( $this->optionsName . '_lite' );

		// Refactor options.
		$this->defaultsMerged = array_replace_recursive( $this->defaults, $this->liteDefaults );

		$mergedDefaults = array_replace_recursive(
			$this->liteDefaults,
			$this->addValueToValuesArray( $this->liteDefaults, $dbOptions )
		);

		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$dbOptions     = array_replace_recursive(
			$cachedOptions,
			$mergedDefaults
		);

		aioseo()->core->optionsCache->setOptions( $this->optionsName, $dbOptions );
	}

	/**
	 * Merge defaults with liteDefaults.
	 *
	 * @since 4.1.4
	 *
	 * @return array An array of dafults.
	 */
	public function getDefaults() {
		return array_replace_recursive( parent::getDefaults(), $this->liteDefaults );
	}

	/**
	 * Updates the options in the database.
	 *
	 * @since 4.1.4
	 *
	 * @param  string     $optionsName An optional option name to update.
	 * @param  string     $defaults    The defaults to filter the options by.
	 * @param  array|null $options     An optional options array.
	 * @return void
	 */
	public function update( $optionsName = null, $defaults = null, $options = null ) {
		$optionsName = empty( $optionsName ) ? $this->optionsName . '_lite' : $optionsName;
		$defaults    = empty( $defaults ) ? $this->liteDefaults : $defaults;

		// We're creating a new array here because it was setting it by reference.
		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$optionsBefore = json_decode( wp_json_encode( $cachedOptions ), true );

		parent::update( $this->optionsName, $options );
		parent::update( $optionsName, $defaults, $optionsBefore );
	}

	/**
	 * Updates the options in the database.
	 *
	 * @since 4.1.4
	 *
	 * @param  boolean $force       Whether or not to force an immediate save.
	 * @param  string  $optionsName An optional option name to update.
	 * @param  string  $defaults    The defaults to filter the options by.
	 * @return void
	 */
	public function save( $force = false, $optionsName = null, $defaults = null ) {
		if ( ! $this->shouldSave && ! $force ) {
			return;
		}

		$optionsName = empty( $optionsName ) ? $this->optionsName . '_lite' : $optionsName;
		$defaults    = empty( $defaults ) ? $this->liteDefaults : $defaults;

		parent::save( $force, $this->optionsName );
		parent::save( $force, $optionsName, $defaults );
	}
}