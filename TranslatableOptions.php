<?php
/**
 * Translatable Options Functionality
 *
 * Class to handle creating translatable options.
 *
 * @package WordPress
 * @subpackage ProjectNamespace
 * @since 0.1.0
 * @version 0.1.0
 */

namespace ProjectNamespace;

/**
 * Class to handle translatable options fields for other languages.
 */
class TranslatableOptions {
	/**
	 * Option names  that are allowed to be translated (option_name).
	 *
	 * @var array
	 */
	const TRANSLATABLE_OPTIONS = [
		'footer-option',
	];

	/**
	 * Determines if the object should be registered.
	 *
	 * @return bool True if the object should be registered, false otherwise.
	 */
	public function can_register() {
		return function_exists( '\pll_current_language' );
	}

	/**
	 * Registration method for the object.
	 *
	 * @return void
	 */
	public function register() {
		// Add a filter for each option that can be translated.
		foreach ( self::TRANSLATABLE_OPTIONS as $option ) {
			add_filter( "pre_update_option_{$option}", [ $this, 'pre_update_option' ], 10, 3 );
			add_filter( "pre_option_{$option}", [ $this, 'pre_option' ], 10, 3 );
		}
	}

	/**
	 * Runs before an option is updated to determine if the language option should be updated instead.
	 *
	 * @param mixed  $value     The new, unserialized option value.
	 * @param mixed  $old_value The old option value.
	 * @param string $option    The name of the option.
	 * @return mixed            The value of the option being updated.
	 */
	public function pre_update_option( $value, $old_value, $option ) {
		// Bail early if the helper doesn't exist.
		if ( ! function_exists( 'pll_current_language' ) ) {
			return $value;
		}

		// Get the current language for the site.
		$language = pll_current_language();

		// Bail early if no language.
		if ( empty( $language ) ) {
			return $value;
		}

		// Bail early if this is English.
		if ( 'en' === $language ) {
			return $value;
		}

		// Update the translated option value.
		update_option( "{$option}_{$language}", $value );

		// Return the old option value to avoid updating the non-translated option.
		return $old_value;
	}

	/**
	 * Overrides an option's value if it is translatable and not English.
	 *
	 * @param bool|mixed $pre_option The value to return instead of the option value. This differs from
	 *                               `$default`, which is used as the fallback value in the event the option
	 *                               doesn't exist elsewhere in get_option(). Default false (to skip past the
	 *                               short-circuit).
	 * @param string     $option     Option name.
	 * @param mixed      $default    The fallback value to return if the option does not exist. Default is false.
	 * @return mixed                 Value for the option.
	 */
	public function pre_option( $pre_option, $option, $default ) {
		// Bail early if the helper doesn't exist.
		if ( ! function_exists( 'pll_current_language' ) ) {
			return $pre_option;
		}

		// Bail early if the option isn't in our list of translatable options.
		if ( ! in_array( $option, self::TRANSLATABLE_OPTIONS, true ) ) {
			return $pre_option;
		}

		// Get the current language for the site.
		$language = pll_current_language();

		// Bail early if no language.
		if ( empty( $language ) ) {
			return $pre_option;
		}

		// Bail early if this is English.
		if ( 'en' === $language ) {
			return $pre_option;
		}

		$value = get_option( "{$option}_{$language}" );

		return ( false !== $value ) ? $value : $pre_option;
	}
}
