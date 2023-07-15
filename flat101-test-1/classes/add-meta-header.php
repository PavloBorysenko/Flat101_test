<?php
/**
 *  Add meta to  head Class
 *
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flat101AddMeta class
 */
class Flat101AddMeta{
	
	/**
	 * for which post to perform
	 * 
	 * @var string 
	 */
	private $postType = '';

	/**
	 *list of properties to add to head
	 * 
	 * @var array 
	 */
	private $properties = array();
	
	/**
	 * list of prepared content for metadata
	 *
	 * @var array 
	 */
	private $preparedProperties = array();
	
	/**
	 * constructor
	 * 
	 * @param array $properties
	 * @param string $postType
	 */
	public function __construct(array $properties, string $postType = 'post') {
		$this->properties = $this->cleanProperties($properties);
		$this->postType = $this->cleanProperties($postType);
		
		add_action('wp_head', array($this, 'addMeta'));
	}
	
	/**
	 * Hook processing wp-head
	 * 
	 * @return void
	 */
	public function addMeta() : void {				
		if (is_singular($this->postType)) {
			$this->derawMeta();
		}
	}
	
	/**
	 * renders meta
	 * 
	 * @return void
	 */
	public function derawMeta() : void{
		$prepared_data = $this->getPreparedProperties();
		foreach ($prepared_data as $property => $content) {
			
			$content = apply_filters('float101_add_content', $content, $property);
			
			if (empty($content)) {
				continue;
			}
					
			echo sprintf('<meta property="%s" content="%s" />', esc_attr($property), esc_attr($content)) . "\r\n";
			
		}
		
		
	}

	/**
	 * Preparing content for Properties list
	 * 
	 * @return array
	 */
	private function getPreparedProperties() : array {
		$prepared = array();
		
		foreach($this->properties as $property_name => $fc_name) {
			if (!$fc_name) {
				continue;
			}
			$prepared[$property_name] = $this->getContent($fc_name);
		}
		
		return $prepared;
	}
	
	/**
	 * Preparing content for Property list
	 * 
	 * @param string $fc_name
	 * @return string
	 */
	private function getContent( string $fc_name) : string {
		$callback = 'getContent' . ucfirst(strtolower($fc_name));
		if (method_exists($this, $callback)) {
			return $this->$callback();
		} else {
			/*for static content or for hook customization*/
			return $fc_name;
		}
		
	}
	
	/**
	 * 
	 * @return string
	 */
	private function getContentImage() : string {
		$url = '';
		if (is_singular() && has_post_thumbnail()) {
			$thumbnail_id = get_post_thumbnail_id();
			$thumbnail_url = wp_get_attachment_image_src($thumbnail_id, 'full');
			if (!is_wp_error($thumbnail_url) && isset($thumbnail_url[0])) {
				$url = $thumbnail_url[0];
			}
			
		}
		return $url;
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