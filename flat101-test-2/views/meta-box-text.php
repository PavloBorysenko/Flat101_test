<?php
/**
 * Text field template for metabox
 *
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


wp_nonce_field( 'flat101_meta_nonce_' . $post->ID, 'flat101_meta_nonce' );

foreach ($meta_fields as $meta_key => $meta_title) {
	
	$value = get_post_meta( $post->ID, $meta_key, 1 );
	?>
	<div class="flat101_meta_item">
		<label for="flat101_<?php echo esc_attr($meta_key); ?>"><?php echo esc_html($meta_title); ?></label> 
		<input type="text" id="flat101_<?php echo esc_attr($meta_key) ?>" name="<?php echo esc_attr($meta_key) ?>" value="<?php echo esc_attr($value) ?>" size="25" />
	</div>
	<?php			 
	
}