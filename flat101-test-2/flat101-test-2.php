<?php

/*
  Plugin Name: PARTE 2
  Plugin URI: http://#
  Description: Desarrolla un plugin de WordPress que genera un custom post “Tienda” en WordPress y consumelo mediante la API de WordPress
  Author: Pavlo Borysenko
  Version: 0.0.1
  Author URI: http://#
 */

if (!defined('ABSPATH')) {
	die('No direct access allowed!');
}

define('PARTE2_PATH', plugin_dir_path(__FILE__));
define('PARTE2_LINK', plugin_dir_url(__FILE__));
define('PARTE2_VERSION', '0.0.1');

require_once PARTE2_PATH . 'classes/custom-post.php';
require_once PARTE2_PATH . 'classes/custom-api.php';


/* data setting */
$post_slug = 'tienda';

$labels = array(
	'name' => 'tienda',
	'singular_name' => 'Tienda',
	'menu_name' => 'Tiendas',
	'name_admin_bar' => 'Tienda',
	'add_new' => 'Agregar nueva',
	'add_new_item' => 'Agregar nueva tienda',
	'new_item' => 'Nueva tipo tienda',
	'edit_item' => 'Editar tienda',
	'view_item' => 'Ver tienda',
	'all_items' => 'Todas las tiendas',
	'search_items' => 'Buscar tipos de publicaciones',
	'parent_item_colon' => 'Tiendas padre:',
	'not_found' => 'No se encontraron tiendas.',
	'not_found_in_trash' => 'No se encontraron tiendas en la papelera.'
);

$meta_fields = array(
	'flat101_name' => 'Nombre',
	'flat101_address' => 'Dirección',
	'flat101_description' => 'Descripción',
);

$flat_post_tienda = new FlatCustomPost($post_slug, $labels, $meta_fields);
new FlatCustomApi($flat_post_tienda);