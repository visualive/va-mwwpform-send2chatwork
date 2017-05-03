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
 * Class Send2cw
 *
 * @package VAMWWPFSEND2CW\Modules
 */
class Send2cw {
	/**
	 * Holds the instance of this class.
	 *
	 * @var array
	 */
	private static $instance = array();

	/**
	 * Options.
	 *
	 * @var bool
	 */
	private $options = array();

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
	 */
	private function __construct() {
		$this->options = get_option( VA_MWWPF_SEND2CW_OPTION_NAME, array() );

		if (
			isset( $this->options['form_id'], $this->options['api_key'], $this->options['room_id'] )
			&& ! empty( $this->options['form_id'] )
			&& ! empty( $this->options['api_key'] )
			&& ! empty( $this->options['room_id'] )
		) {
			$form_id = (int) $this->options['form_id'];

			add_filter( "mwform_admin_mail_mw-wp-form-{$form_id}", array( &$this, 'mwform_admin_mail' ) );
			add_action( "mwform_before_send_admin_mail_mw-wp-form-{$form_id}", array( &$this, 'mwform_before_send_admin_mail' ) );
		}
	}

	/**
	 * Skip mail of MW MP Form.
	 *
	 * @param object $mail Mail config data.
	 *
	 * @return object
	 */
	public function mwform_admin_mail( $mail ) {
		if ( isset( $this->options['skip_send'] ) && 1 === (int) $this->options['skip_send'] ) {
			$mail->to = false;
		}

		return $mail;
	}

	/**
	 * Skip mail of MW MP Form.
	 *
	 * @param object $mail Mail config data.
	 */
	public function mwform_before_send_admin_mail( $mail ) {
		$api_key = $this->options['api_key'];
		$room_id = (int) $this->options['room_id'];
		$message = self::_generate_message( $mail );
		$args    = array(
			'headers' => array(
				'X-ChatWorkToken' => $api_key,
			),
			'body'    => array(
				'body' => $message,
			),
		);

		$remote = wp_safe_remote_post( "https://api.chatwork.com/v2/rooms/{$room_id}/messages", $args );
		unset( $remote );
	}

	/**
	 * Generate message.
	 *
	 * @param object $mail Mail config data.
	 *
	 * @return string
	 */
	private function _generate_message( $mail ) {
		$form_id   = (int) $this->options['form_id'];
		$mwwpform  = get_post_meta( $form_id, 'mw-wp-form' );
		$subject   = '' !== $mail->subject ? $mail->subject : __( "You've Got Mail.", 'va-mwwpf-send2cw' );
		$message[] = '===< ' . __( 'Sender', 'va-mwwpf-send2cw' ) . ' >=====================';
		$message[] = $mail->sender . ' < ' . $mail->from . ' >' . PHP_EOL;
		$message[] = '===< ' . __( 'Content', 'va-mwwpf-send2cw' ) . ' >====================';
		$message[] = '' !== $mail->body ? $mail->body : __( 'None (There is a possibility that the mail setting for the administrator has not been set yet)', 'va-mwwpf-send2cw' );
		$message[] = '================================' . PHP_EOL;

		if ( isset( $mwwpform[0]['usedb'] ) && 1 === (int) $mwwpform[0]['usedb'] ) {
			$message[] = admin_url( "/edit.php?post_type=mwf_{$form_id}" );
		}

		$message = sprintf( '[info][title]%s[/title]%s[/info]', $subject, implode( PHP_EOL, $message ) );

		return $message;
	}
}
