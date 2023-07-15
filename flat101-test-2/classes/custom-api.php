<?php
/**
 * Сlass for custom API registration
 *
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FlatCustomApi class
 * 
 */
class FlatCustomApi{
	
	/**
	 * Object for getting custom post type data
	 * 
	 * @var FlatPostData
	 */
	private $custom_post = null;
	
	/**
	 * constructor
	 * 
	 * @param FlatPostData $custom_post
	 */
	public function __construct(FlatPostData $custom_post) {
		
		$this->custom_post = $custom_post;
		
		add_action( 'wp_enqueue_scripts', array($this, 'addScripts'));
		
		add_action('rest_api_init', array($this, 'customApiRoutes'));		
		
	}
	
	/**
	 * Adds JS only on the post page
	 * 
	 * @return void
	 */
	public function addScripts() : void {
		
		if (is_singular($this->custom_post->getPostType())) {
			/* Add js only when it makes sense */
			wp_enqueue_script('flat101_api_js', PARTE2_LINK . 'js/api-js.js', array('jquery'), PARTE2_VERSION);

			$api_url = '/wp-json/flat101-api-' . $this->custom_post->getPostType() . '/v1/post-data/'; 
			wp_localize_script('flat101_api_js', 'flat101_post_data', array(
				'api_url' => site_url($api_url),
				'post_id' => get_the_ID()
			));
		}
	}
	
	/**
	 * route registration for custom API
	 * 
	 * @return void
	 */
	public function customApiRoutes() : void {
		register_rest_route('flat101-api-' . $this->custom_post->getPostType() . '/v1', '/post-data/(?P<post_id>\d+)', array(
			'methods' => 'GET',
			'callback' => array($this, 'sendData'),
		));		
	}
	
	/**
	 * Sending post data. Сallback for register_rest_route
	 * 
	 * @param WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function sendData( WP_REST_Request $request) : WP_REST_Response {
		$post_id = (int)$request['post_id'];
				
		$meta_data = ($post_id)? $this->custom_post->getPostData($post_id): array();
		
		return rest_ensure_response($meta_data);
	}	
}
