<?php
/**
 * WordPress plugin admin.
 *
 * @package    WordPress
 * @subpackage VA MW WP Form send to chatwork
 * @since      1.0.0
 * @author     KUCKLU <kuck1u@visualive.jp>
 * @licenses   GNU General Public License v3.0
 */

namespace VAMWWPFSEND2CW\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin
 *
 * @package VAMWWPFSEND2CW\Modules
 */
class Admin {
	/**
	 * Holds the instance of this class.
	 *
	 * @var array
	 */
	private static $instance = array();

	/**
	 * Setting fields.
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Options.
	 *
	 * @var bool
	 */
	private $options = array();

	/**
	 * Option group.
	 *
	 * @var string
	 */
	private $group = null;

	/**
	 * Menu slug.
	 *
	 * @var string
	 */
	private $slug = null;

	/**
	 * Instance.
	 *
	 * @param array $settings If the set value is required, pass a value in an array.
	 *
	 * @return self
	 */
	public static function get_instance( $settings = array() ) {
		$class = self::get_called_class();

		if ( ! isset( self::$instance[ $class ] ) ) {
			self::$instance[ $class ] = new $class( $settings );
		}

		return self::$instance[ $class ];
	}

	/**
	 * Get my class.
	 *
	 * @return string
	 */
	private static function get_called_class() {
		return get_called_class();
	}

	/**
	 * This hook is called once any activated plugins have been loaded.
	 *
	 * @param array $settings Input fields.
	 */
	private function __construct( $settings ) {
		$this->settings = $settings;
		$this->group    = VA_MWWPF_SEND2CW_PLUGIN_PREFIX . 'settings';
		$this->options  = get_option( VA_MWWPF_SEND2CW_OPTION_NAME, array() );

		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

	/**
	 * Add admin menu
	 *
	 * @return void
	 */
	public function admin_menu() {
		$this->slug = add_options_page(
			format_to_edit( $this->settings['name']['page'] ),
			format_to_edit( $this->settings['name']['menu'] ),
			'manage_options',
			str_replace( '_', '-', VA_MWWPF_SEND2CW_OPTION_NAME ),
			array( &$this, 'render_admin_page' )
		);
	}

	/**
	 * Admin init.
	 */
	public function admin_init() {
		register_setting(
			$this->group,
			VA_MWWPF_SEND2CW_OPTION_NAME,
			array( &$this, 'sanitize_option' )
		);
		add_settings_section(
			VA_MWWPF_SEND2CW_PLUGIN_PREFIX . 'section',
			null,
			null,
			$this->group
		);

		foreach ( $this->settings['fields'] as $name => $field ) {
			$field['id'] = VA_MWWPF_SEND2CW_PLUGIN_PREFIX . $name;

			add_settings_field(
				$name,
				"<label for='{$field['id']}'>{$field['label']}</label>",
				array( &$this, "render_input_{$field['input']['type']}" ),
				$this->group,
				VA_MWWPF_SEND2CW_PLUGIN_PREFIX . 'section',
				array(
					'name'  => $name,
					'field' => $field,
				)
			);
		}
	}

	/**
	 * Render form wrapper HTML.
	 */
	public function render_admin_page() {
		?>
		<div class="wrap">
			<h2><?php echo apply_filters( 'the_title', $this->settings['name']['page'] ); ?></h2>

			<form method="post" action="options.php" novalidate="novalidate">
				<?php
				settings_fields( $this->group );
				do_settings_sections( $this->group );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render input[type=text].
	 *
	 * @param array $input Data required to generate input elements.
	 */
	public function render_input_text( $input = array() ) {
		$value   = $input['field']['input']['default_value'];
		$options = $this->options;

		if ( isset( $options[ $input['name'] ] ) ) {
			$value = $options[ $input['name'] ];
		}

		$output = sprintf(
			'<input id="%s" class="regular-text" type="text" name="%s[%s]" value="%s">',
			esc_attr( $input['field']['id'] ),
			esc_attr( VA_MWWPF_SEND2CW_OPTION_NAME ),
			esc_attr( $input['name'] ),
			esc_attr( $value )
		);
		$output .= self::_render_description( $input['field']['description'] );

		echo $output;
	}

	/**
	 * Render input[type=checkbox].
	 *
	 * @param array $input Data required to generate input elements.
	 */
	public function render_input_checkbox( $input = array() ) {
		$output  = null;
		$value   = $input['field']['input']['default_value'];
		$options = $this->options;

		if ( isset( $options[ $input['name'] ] ) ) {
			$value = $options[ $input['name'] ];
		}

		foreach ( $input['field']['input']['items'] as $item ) {
			$checked = ( is_array( $value ) && in_array( (string) $item['value'], $value, true ) ) ? ' checked="checked"' : '';
			$output  = sprintf(
				'<label><input id="%s" type="checkbox" name="%s[%s][]" value="%s"%s> %s</label><br>',
				esc_attr( $input['field']['id'] ),
				esc_attr( VA_MWWPF_SEND2CW_OPTION_NAME ),
				esc_attr( $input['name'] ),
				esc_attr( $item['value'] ),
				$checked,
				esc_html( $item['label'] )
			);
		}

		$output .= self::_render_description( $input['field']['description'] );

		echo $output;
	}

	/**
	 * Render select post type.
	 *
	 * @param array $input Data required to generate input elements.
	 */
	public function render_input_post_type( $input = array() ) {
		$value   = $input['field']['input']['default_value'];
		$options = $this->options;

		if ( isset( $options[ $input['name'] ] ) ) {
			$value = $options[ $input['name'] ];
		}

		$query = new \WP_Query( array(
			'post_type' => sanitize_key( $input['field']['input']['post_type'] ),
			'nopaging'  => true,
			'orderby'   => array(
				'ID' => 'ASC',
			),
		) );

		if ( $query->have_posts() ) :
			$output = sprintf(
				'<select id="%s" name="%s[%s]">',
				esc_attr( $input['field']['id'] ),
				esc_attr( VA_MWWPF_SEND2CW_OPTION_NAME ),
				esc_attr( $input['name'] )
			);

			$output .= '<option>' . __( 'Please select', 'va-mwwpf-send2cw' ) . '</option>';

			while ( $query->have_posts() ) : $query->the_post();
				$output .= sprintf(
					'<option value="%d" %s>%s</option>',
					get_the_ID(),
					selected( $value, get_the_ID(), false ),
					get_the_title()
				);
			endwhile;

			$output .= '</select>';

			wp_reset_postdata();
		else :
			$output = "<p>{$input['field']['input']['not_found']}</p>";
		endif;

		echo $output;
	}

	/**
	 * Render description.
	 *
	 * @param string $description description text.
	 *
	 * @return string
	 */
	private static function _render_description( $description = '' ) {
		if ( ! empty( $description ) ) {
			$description = wp_kses_post( "<p class='description'>{$description}</p>" );
		}

		return $description;
	}

	/**
	 * Sanitize option value.
	 *
	 * @param array $options Options data.
	 *
	 * @return mixed
	 */
	public function sanitize_option( $options ) {
		foreach ( $options as $name => $option ) {
			$sanitize = $this->settings['fields'][ $name ]['sanitize'];

			if ( false === $this->settings['fields'][ $name ]['sanitize_self'] ) {
				$sanitize = array( $this, $sanitize );
			}

			if ( in_array( $this->settings['fields'][ $name ]['input']['type'], array( 'checkbox' ), true ) ) {
				$options[ $name ] = array_values( array_filter( array_map( $sanitize, $option ), 'strlen' ) );
			} else {
				$options[ $name ] = call_user_func( $sanitize, $option );
			}
		}

		return $options;
	}

	/**
	 * Sanitizes a alphanumeric.
	 *
	 * @param string $str Text to validate.
	 *
	 * @return string
	 */
	protected static function sanitize_alphanumeric( $str ) {
		return filter_var(
			$str,
			FILTER_VALIDATE_REGEXP,
			array(
				'options' => array(
					'regexp'  => '/^[a-zA-Z0-9]+$/',
					'default' => '',
				),
			)
		);
	}

	/**
	 * Sanitizes a numeric.
	 *
	 * @param string $str Text to validate.
	 *
	 * @return int
	 */
	protected static function sanitize_numeric( $str ) {
		return filter_var(
			$str,
			FILTER_VALIDATE_REGEXP,
			array(
				'options' => array(
					'regexp'  => '/^[0-9]+$/',
					'default' => '',
				),
			)
		);
	}

	/**
	 * Sanitizes a alphanumeric.
	 *
	 * @param string $str Text to validate.
	 *
	 * @return string
	 */
	protected static function sanitize_word( $str ) {
		return filter_var(
			$str,
			FILTER_VALIDATE_REGEXP,
			array(
				'options' => array(
					'regexp'  => '/^[\w-]+$/',
					'default' => '',
				),
			)
		);
	}
}
