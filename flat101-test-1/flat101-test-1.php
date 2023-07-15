<?php
/*
  Plugin Name: PARTE 1
  Plugin URI: http://#
  Description: Que añada al final del título de todas las páginas | Que añada el meta "og:image" en el head
  Author: Pavlo Borysenko
  Version: 0.0.1
  Author URI: http://#
 */

if (!defined('ABSPATH')) {
    die('No direct access allowed!');
}

define('PARTE1_PATH', plugin_dir_path(__FILE__));

require_once PARTE1_PATH . 'classes/add-meta-header.php';
require_once PARTE1_PATH . 'classes/add-word-title.php';


add_action('init', function() {
	/*1*/
	new FlatAddWord('Flat101', '-');
	
	/*2*/
	$metaProperties = array(
		'og:image' => 'image'
	); 	
	new Flat101AddMeta($metaProperties);
	
});