<?php
/**
 * Registrierung des Rating-Blocks
 */

defined('ABSPATH') || exit;


function ud_register_rating_block() {
	register_block_type_from_metadata(
		__DIR__ . '/../',
		[
			'render_callback' => 'ud_rating_render_block', // kommt aus render.php
		]
	);
}
add_action('init', 'ud_register_rating_block');
