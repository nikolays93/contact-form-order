<?php
/**
 * Plugin options
 *
 * @package Newproject.WordPress.plugin
 */

namespace NikolayS93\ContactFormOrders;

/**
 * Class for works with plugin options
 */
class Option {

	/**
	 * Fetch option values here
	 *
	 * @var array option values.
	 */
	private $option = array();

	/**
	 * Use several options for plugin.
	 *
	 * @var string options category.
	 */
	private $context;

	private static $instance = array();

	public static function get_instance( $context = '' ) {
		if ( ! isset( static::$instance[ $context ] ) ) {
			static::$instance[ $context ] = new static( $context );
		}

		return static::$instance[ $context ];
	}

	/**
	 * Fill properties
	 *
	 * @param string $context  options category.
	 * @param string $autoload yes|no preload options.
	 */
	private function __construct( $context = '' ) {
		$this->context = $context;

		/**
		 * Option name by context
		 *
		 * @var string
		 */
		$option_name = self::get_option_name( $this->context );

		/**
		 * Get field value from wp_options
		 *
		 * @link https://developer.wordpress.org/reference/functions/get_option/
		 * @var mixed
		 */
		$this->option = get_option( $option_name, $this->option );
	}

	/**
	 * Get option name for a options in the WordPress database
	 *
	 * @param string $suffix option name suffix "plugin_$suffix".
	 *
	 * @return string
	 */
	public static function get_option_name( $suffix = '' ) {
		$option_name = $suffix ? PREFIX . $suffix : DOMAIN;

		return (string) apply_filters( PREFIX . 'option_name', $option_name, $suffix );
	}

	/**
	 * [@todo write save description]
	 *
	 * @return [type] [description].
	 */
	public function save( $autoload = 'yes' ) {
		if ( empty( $this->option ) ) {
			return null;
		}

		return update_option(
			self::get_option_name( $this->context ),
			$this->option,
			$autoload
		);
	}

	/**
	 * [@todo write get description]
	 *
	 * @param  [type]  $key     [description].
	 * @param  boolean $default [description].
	 * @return [type]           [description].
	 */
	public function get( $key, $default = false ) {
		return isset( $this->option[ $key ] ) ? $this->option[ $key ] : $default;
	}

	/**
	 * [@todo write get_array description]
	 *
	 * @return [type] [description].
	 */
	public function get_array() {
		return $this->option;
	}

	/**
	 * [@todo write set description]
	 *
	 * @param [type] $key   [description].
	 * @param [type] $value [description].
	 */
	public function set( $key, $value ) {
		if ( is_array( $key ) ) {
			foreach ( $key as $k => $v ) {
				$this->set( $k, $v );
			}
		}

		$this->option[ $key ] = $value;
	}
}
