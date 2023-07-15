<?php
/**
 * Сlass for changing the title of pages
 *
 * @version  0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FlatAddWord class
 * 
 */
class FlatAddWord{
	
	/**
	 * the word that is appended to the title.
	 *
	 * @var string
	 */	
	private $word = '';
	
	/**
	 * Separator between word and tiltle.
	 *
	 * @var string
	 */		
	private $separator = '';
	
	/**
	 * Constructor.
	 * 
	 * @param string $word
	 */
	public function __construct(string $word = 'Flat101', string $separator = '-') {
		$this->word = trim(sanitize_text_field($word));
		$this->separator = sanitize_text_field($separator);

		add_filter( 'document_title', array($this, 'addWord'), 20 );
		
		/*Maybe if you add it inside the content*/
		add_action( 'get_template_part', array($this, 'initTheTitle'), 10, 4 );

	}
	
	/**
	 * Lazy hook initialization so that only the title in the content changes
	 * 
	 * @param string $slug
	 * @param string $name
	 * @param string|array $templates
	 * @param array $args
	 */
	public function initTheTitle($slug, $name, $templates, $args) : void {
		
		if ('content' == $slug && 'page' == $name) {
			add_filter('the_title', array($this, 'addPageTitle'), 20, 2);
		}
		
	}
	
	/**
	 * preparation of string
	 * 
	 * @return string
	 */
	public function getText() : string {
		
		return $this->word? $this->separator . $this->word : '';
		
	}
	
	/**
	 * 
	 * @param string $title
	 * @param string|int $id
	 * @return string
	 */
	public function addPageTitle(string $title, $id) : string {
		
		if (is_page($id)){ 
			return $this->addWord($title);
		}
		
		return $title;
	}
	/**
	 * Hook Processing Method wp_title
	 * 
	 * @param string $title
	 * @return string
	 */
	public function addWord( string $title) : string {
		
		$title .= $this->getText();
		
		return $title;
	}
}