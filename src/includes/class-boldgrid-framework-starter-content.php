<?php
/**
 * Class: BoldGrid_Framework_Starter_Content
 *
 * This is used for the starter content functionality in the BoldGrid Theme Framework.
 *
 * @since    2.0.0
 * @category Customizer
 * @package  Boldgrid_Framework
 * @author   BoldGrid <support@boldgrid.com>
 * @link     https://boldgrid.com
 */

/**
 * BoldGrid_Framework_Starter_Content
 *
 * Responsible for the starter content import functionality in the BoldGrid Theme Framework.
 *
 * @since 2.0.0
 */
class BoldGrid_Framework_Starter_Content {

	/**
	 * The BoldGrid Theme Framework configurations.
	 *
	 * @since  2.0.0
	 * @access protected
	 * @var    string    $configs The BoldGrid Theme Framework configurations.
	 */
	protected $configs;

	/**
	 * Default value for starter content.
	 *
	 * @since 2.0.0
	 * @access protected
	 * @var    mixed     Value of default.
	 */
	protected $default = '';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.0
	 *
	 * @param string $configs The BoldGrid Theme Framework configurations.
	 */
	public function __construct( $configs ) {
		$this->configs = $configs;
	}

	/**
	 * Default palette to use for palette selector controls.
	 *
	 * @since 2.0.0
	 *
	 * @param  array $config  BGTFW Configuration.
	 * @return mixed $default Default Configiguraton.
	 */
	public function set_default( $config ) {
		// Default palette to use for palette selector controls.
		$default = array_filter( $config['customizer-options']['colors']['defaults'], function( $palette ) {
			return ! empty( $palette['default'] );
		} );

		return $this->default = $default;
	}
	/**
	 * Adds post meta to get_theme_starter_content filter.
	 *
	 * @since  2.0.0
	 *
	 * @param  array $content Processed content.
	 * @param  array $config  Starter content config.
	 *
	 * @return array $content Modified $content.
	 */
	public function add_post_meta( $content, $config ) {
		foreach ( $config['posts'] as $post => $configs ) {
			if ( isset( $configs['meta_input'] ) ) {
				$content['posts'][ $post ]['meta_input'] = $configs['meta_input'];
			}
		}

		return $content;
	}

	/**
	 * Declares theme support for starter content using the array of starter
	 * content found in the BGTFW configurations.
	 *
	 * @since 2.0.0
	 */
	public function add_theme_support() {
		if ( ! empty( $this->configs['starter-content'] ) ) {
			add_theme_support( 'starter-content', $this->configs['starter-content'] );
		}
	}

	/**
	 * Sets the starter content defaults that haven't been satisfied for a proper
	 * presentation of the starter content.
	 *
	 * @since 2.0.0
	 *
	 * @param array $config Array of BGTFW configuration options.
	 *
	 * @return array $config Array of BGTFW configuration options.
	 */
	public function set_configs( $config ) {
		foreach ( $config['customizer']['controls'] as $index => $control ) {
			$config = $this->set_menus( $config, $index, $control );
			$config = $this->set_colors( $config, $index, $control );
			$config = $this->set_defaults( $config, $index, $control );
		}

		return $config;
	}

	public function set_menus( $config, $index, $control ) {
		if ( strpos( $control['settings'], 'bgtfw_menu_' ) !== false && strpos( $control['settings'], 'main' ) !== false ) {

			$menus = $config['menu']['locations'];

			foreach ( $menus as $location => $description ) {

				// Add controls based on main menu's.
				$new_key = str_replace( 'main', $location, $control['settings'] );

				if ( $location !== 'main' && ! isset( $this->configs['customizer']['controls'][ $new_key ] ) ) {

					$config['customizer']['controls'][ $new_key ] = [];

					foreach( $control as $option => $value ) {
						$config['customizer']['controls'][ $new_key ][ $option ] = is_string( $value ) ? str_replace( 'main', $location, $value ) : $value;
					}

					// Only enable hamburgers on main menu unless otherwise explicitly set in configs.
					if ( strpos( $new_key, '_toggle' ) !== false ) {
						$config['customizer']['controls'][ $new_key ]['default'] = false;
					}

					// Active callbacks/ required.
					foreach ( ['active_callback', 'required' ] as $item ) {
						if ( isset( $config['customizer']['controls'][ $new_key ][ $item ] ) ) {
							foreach ( $config['customizer']['controls'][ $new_key ][ $item ] as $set => $opts ) {
								$config['customizer']['controls'][ $new_key ][ $item ][ $set ]['setting'] = str_replace( 'main', $location, $opts['setting'] );
							}
						}
					}

				}
			}
		}

		return $config;
	}

	/**
	 * Sets the default colors for palette selector controls.
	 *
	 * @since 2.0.0
	 *
	 * @param array $config  Array of BGTFW configuration options.
	 * @param int   $index   Index of control in configuration.
	 * @param array $control Control settings from configuration.
	 *
	 * @return array $config Array of BGTFW configuration options.
	 */
	public function set_colors( $config, $index, $control ) {
		if ( empty( $this->default ) ) {
			$this->default = $this->set_default( $config );
		}

		if ( 'bgtfw-palette-selector' === $control['type'] ) {
			if ( empty( $control['default'] ) || 'none' === $control['default'] ) {

				// Headings default.
				if ( strpos( $control['settings'], 'headings' ) !== false ) {
					$config['customizer']['controls'][ $index ]['default'] = 'color-2:' . $this->default[0]['colors'][1];

				// Links default.
				} elseif ( strpos( $control['settings'], 'links' ) !== false ) {
					$config['customizer']['controls'][ $index ]['default'] = 'color-3:' . $this->default[0]['colors'][2];

				// Main page background color set to neutral if default palette has it.
				} elseif ( 'boldgrid_background_color' === $control['settings'] && isset( $this->default[0]['neutral-color'] ) ) {
					$config['customizer']['controls'][ $index ]['default'] = 'color-neutral:' . $this->default[0]['neutral-color'];

				// All other background color defaults.
				} else {
					$config['customizer']['controls'][ $index ]['default'] = 'color-1:' . $this->default[0]['colors'][0];
				}

			// Allow designers to set 'color-1' instead of needing to know the actual color for each control.
			} elseif ( preg_match( '/^color-([\d]|neutral)/', $control['default'], $color ) ) {
				if ( 'neutral' === $color[1] ) {
					$config['customizer']['controls'][ $index ]['default'] = $control['default'] . ':' . $this->default[0]['neutral-color'];
				} else {
					$config['customizer']['controls'][ $index ]['default'] = $control['default'] . ':' . $this->default[0]['colors'][ $color[1] - 1 ];
				}
			}
		}

		return $config;
	}

	/**
	 * Sets the starter content defaults that haven't been satisfied for a proper
	 * presentation of the starter content.
	 *
	 * @since 2.0.0
	 *
	 * @param array $config  Array of BGTFW configuration options.
	 * @param int   $index   Index of control in configuration.
	 * @param array $control Control settings from configuration.
	 *
	 * @return array $config Array of BGTFW configuration options.
	 */
	public function set_defaults( $config, $index, $control ) {
		if ( empty( $config['starter-content']['theme_mods'][ $control['settings'] ] ) && 'custom' !== $control['type'] ) {
			$config['starter-content']['theme_mods'][ $control['settings'] ] = $config['customizer']['controls'][ $index ]['default'];
		}

		return $config;
	}

	/**
	 * Adds default values for get_theme_mod calls if no value is
	 * passed.
	 *
	 * @since 2.0.0
	 */
	public function dynamic_theme_mod_filter() {
		global $boldgrid_theme_framework;
		$config = $boldgrid_theme_framework->get_configs();

		foreach ( $config['customizer']['controls'] as $index => $control ) {
			$settings = $control['settings'];

			add_filter( "theme_mod_{$settings}", function( $setting ) use ( $control ) {
				if ( false === $setting && isset( $control['default'] ) ) {
					if ( is_bool( $control['default'] ) ) {

						// Check stored theme_mods in db.
						$slug = get_option( 'stylesheet' );
						$stored = get_option( "theme_mods_{$slug}", array() );
						if ( ! isset( $stored[ $control['settings'] ] ) ) {
							$setting = $control['default'];
						}
					} else {
						$setting = $control['default'];
					}
				}
				return $setting;
			} );
		}
	}
}