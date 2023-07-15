<?php
/**
 * Сlass for registering and managing a custom post type
 *
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * guarantee of data receipt
 */
interface FlatPostData
{
    public function getPostType();
    public function getPostData( int $id );
}


/**
 * FlatCustomPost class
 * 
 */
class FlatCustomPost implements FlatPostData{
	
	/**
	 * list of labels to post
	 *
	 * @var array 
	 */
	private $labels = array();
	
	/**
	 * slug for custom post type
	 * 
	 * @var string
	 */
	private $post_slug = '';
	
	/**
	 * list of custom fields
	 *
	 * @var array
	 */
	private $meta_fields = array();

	/**
	 * list of labels to post
	 *
	 * @var array 
	 */
	private $first_init = true;
	
	/**
	 * Part of the cache key group for metadata
	 * 
	 * @var string
	 */
	private $cache_group = 'flat_post_data';


	/**
	 * constructor
	 * 
	 * @param string $post_slug
	 * @param array $labels
	 * @param array $meta_fields
	 */
	public function __construct(string $post_slug, array $labels, array $meta_fields) {
		
		$post_slug = sanitize_key($post_slug);
		if (post_type_exists($post_slug)){
			return;
		}
		$this->post_slug = $post_slug;
		$this->labels = $this->cleanProperties($labels);
		$this->meta_fields = $this->cleanProperties($meta_fields);
		
		add_action('init', array($this, 'init'));
		
		/*init admin hooks*/
		add_action('save_post', array($this, 'savePostData'));
		add_action('add_meta_boxes', array($this, 'addCustomBox'));		
		
	}
	
	/**
	 * Handling the init hook
	 * 
	 * @return void
	 */
	public function init() : void {
		$this->registerPostType();
		
		/* Need to reset permalinks for new post type */
		if ($this->isFirstInit()) {
			flush_rewrite_rules();
		}
			
	}
	
	/**
	 * register a new post type
	 * 
	 * @return void
	 */
	protected function registerPostType() : void {
		$args = array(
			'labels'             => $this->labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $this->post_slug ), 
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
			'show_in_rest'       => true, /* possible to use the standard API */
			'rest_base'          => $this->post_slug,
			'rest_controller_class' => 'WP_REST_Posts_Controller', 		
		);

		$args = apply_filters( 'flat101_custom_post_type_args', $args );	
		
		register_post_type( $this->post_slug , $args );	
		
	}
	
	/**
	 * Add  custom field box
	 * 
	 * @return void
	 */
	public function addCustomBox() : void {
		$screens = array( $this->post_slug );
		add_meta_box( 'flat101_' . $this->post_slug, esc_html__('Custom field', 'flat101-test-2'),
				array($this, 'drawMetaBox'),
				$screens
				);		
	}
	
	/**
	 * rendering a form with multiple fields
	 * 
	 * @param WP_Post $post
	 * @param array $meta
	 * @return void
	 */
	public function drawMetaBox( WP_Post $post, array $meta ) : void {
		$meta_fields = $this->meta_fields;
		include(PARTE2_PATH . 'views/meta-box-text.php');
	}
	
	/**
	 * hook processing save_post
	 * 
	 * @param int|string $post_id
	 * @return void
	 */
	public function savePostData($post_id) : void {
		if (!isset($_POST['flat101_meta_nonce']) || !wp_verify_nonce( $_POST['flat101_meta_nonce'], 'flat101_meta_nonce_' . $post_id ) ) {
			return;
		}
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		if( ! current_user_can( 'edit_post', $post_id ) ) {
			return;	
		}
		
		foreach ($this->meta_fields as $meta_key => $meta_title) {
			if (isset($_POST[$meta_key])) {
				update_post_meta($post_id, $meta_key, $this->cleanProperties($_POST[$meta_key]));
			}
		}
		wp_cache_delete( $post_id, $this->cache_group . $this->post_slug );
		
	}
	
	/**
	 * Get custom post slug
	 * 
	 * @return string
	 */
	public function getPostType() : string {
		return $this->post_slug;
	}

	/**
	 * 
	 * @param int $post_id
	 * @return array
	 */
	public function getPostData(int $post_id) : array {				
		$meta_data = array();
		
		if (!$post_id) {
			return $meta_data;
		}
		/* get_post_meta already has a cache. But it still makes sense, especially if a lot of data is transmitted */
		$cached_data = wp_cache_get( $post_id, $this->cache_group . $this->post_slug );
		
		if (!$cached_data) {
			foreach ($this->meta_fields as $meta_key => $meta_title) {
				$meta_data[] = array(
					'key' => $meta_key,
					'title' => $meta_title,
					'value' => get_post_meta( $post_id, $meta_key, 1)
				);
			}
			wp_cache_set( $post_id, $meta_data, $this->cache_group . $this->post_slug );
		} else {
			$meta_data = $cached_data;
		}
		/*Ability to process post data for custom changesl*/
		return apply_filters('flat101_custom_post_type_get_data', $meta_data, $post_id, $this->post_slug);
	}

	/**
	 * Checking if a new post type has just been created
	 * 
	 * @return bool
	 */
	private function isFirstInit() : bool {		
		if (!get_option('flat101_is_reinitialization_' . $this->post_slug, false)) {
			update_option('flat101_is_reinitialization_' . $this->post_slug, true);
			return true;
		}
		return false;
	}

	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 * source \woocommerce\includes\wc-formatting-functions.php
	 *
	 * @param string|array $var Data to sanitize.
	 * @return string|array
	 */
	protected function cleanProperties( $var ) : mixed {
		if ( is_array( $var ) ) {
			return array_map( array($this, 'cleanProperties'), $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}	
}
