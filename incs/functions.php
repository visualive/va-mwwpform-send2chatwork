<?php
/**
 * WordPress plugin functions.
 *
 * @package    WordPress
 * @subpackage VA MW WP Form send to chatwork
 * @since      1.0.0
 * @author     KUCKLU <kuck1u@visualive.jp>
 * @licenses   GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin init.
 *
 * @since 1.0.0
 */
\VAMWWPFSEND2CW\Modules\Admin::get_instance( array(
	'prefix' => VA_MWWPF_SEND2CW_PLUGIN_PREFIX,
	'name'   => array(
		'menu' => 'MW WP Form send 2 chatwork',
		'page' => 'MW WP Form send 2 chatwork Settings',
	),
	'fields' => array(
		'api_key'   => array(
			'label'         => __( 'chatwork API Key', 'va-mwwpf-send2cw' ),
			'description'   => 'Issue chatwork API Key <a href="https://www.chatwork.com/service/packages/chatwork/subpackages/api/apply_beta.php">here</a>.',
			'name'          => 'api_key',
			'input'         => array(
				'type'          => 'text',
				'default_value' => '',
			),
			'sanitize'      => 'sanitize_alphanumeric',
			'sanitize_self' => false,
		),
		'room_id'   => array(
			'label'         => __( 'chatwork Room ID', 'va-mwwpf-send2cw' ),
			'description'   => 'Numeric part of "#!rid*****" of each Room URL is Room ID.<br>ex: In case of "#!rid12345" it is 12345.',
			'name'          => 'room_id',
			'input'         => array(
				'type'          => 'text',
				'default_value' => '',
			),
			'sanitize'      => 'sanitize_numeric',
			'sanitize_self' => false,
		),
		'form_id'   => array(
			'label'         => __( 'Select Form', 'va-mwwpf-send2cw' ),
			'description'   => '',
			'name'          => 'form_id',
			'input'         => array(
				'type'          => 'post_type',
				'post_type'     => 'mw-wp-form',
				'not_found'     => __( 'No form has been created.', 'va-mwwpf-send2cw' ),
				'default_value' => '',
			),
			'sanitize'      => 'sanitize_numeric',
			'sanitize_self' => false,
		),
		'skip_send' => array(
			'label'         => __( 'Skip mail transmission to the administrator', 'va-mwwpf-send2cw' ),
			'description'   => __( 'It can not be used under "MW WP Form" v3.2.0 or lower.', 'va-mwwpf-send2cw' ),
			'name'          => 'skip_send',
			'input'         => array(
				'type'          => 'checkbox',
				'items'         => array(
					array(
						'label' => 'ON',
						'value' => 1,
					),
				),
				'default_value' => 0,
			),
			'sanitize'      => 'sanitize_numeric',
			'sanitize_self' => false,
		),
	),
) );

/**
 * Send2cw init.
 *
 * @since 1.0.0
 */
\VAMWWPFSEND2CW\Modules\Send2cw::get_instance();
