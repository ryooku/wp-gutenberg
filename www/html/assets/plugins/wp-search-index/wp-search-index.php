<?php
/*
Plugin Name: WP Search Index
Plugin URI: http://www.cinra.co.jp/
Description: db-table for search using ACF
Version: 1.0.0
Author: NIKAI, yasuyo
Author URI: http://www.cinra.co.jp/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

define('SEARCH_INDEX_URL', plugin_dir_url(__FILE__));
define('SEARCH_INDEX_PATH', plugin_dir_path(__FILE__));

global $wpdb;
if(!defined('SEARCH_INDEX_TABLE')) define('SEARCH_INDEX_TABLE', $wpdb->prefix.'search_index');

class WP_Search_Index
{
  public function __construct()
  {
    add_action('plugins_loaded', array($this, 'initialize'));
  }

  public function initialize()
  {
    include_once SEARCH_INDEX_PATH . 'classes/class.config.php';
    include_once SEARCH_INDEX_PATH . 'classes/class.function.php';

    if (is_admin())
    {
      include_once SEARCH_INDEX_PATH . 'classes/class.admin.php';
      new WP_Search_Index_Admin();
    }
  }
}

new WP_Search_Index();


register_activation_hook( __FILE__, function()
{
  include_once SEARCH_INDEX_PATH . 'classes/class.config.php';
  include_once SEARCH_INDEX_PATH . 'classes/class.admin.php';

  new WP_Search_Index_Config();
  $si_admin = new WP_Search_Index_Admin();

  $options = WP_Search_Index_Config::$options;

  $keys = $options['keys'];

  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE " .SEARCH_INDEX_TABLE ." (
    id int(11) NOT NULL AUTO_INCREMENT,
    post_id int(11) NOT NULL";

  foreach($keys as $column)
  {
    $sql .= ", `" .$column . "` TEXT";
  }

  $sql .= ", PRIMARY KEY(id)) $charset_collate;";
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta($sql);

  // activationの時は、CREATE TABLEの後にデータ登録も行う
  $args = array(
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'ID',
    'order'          => 'ASC',
  );

  if ($options['post_type']) $args['post_type'] = $options['post_type'];
  if ($options['category']) $args['category_name'] = implode(',', $options['category']);

  $posts = get_posts($args);

  if ($posts) {
    foreach ($posts as $post) {
      $si_admin->save_index_meta($post->ID);
    }
  }
});

register_deactivation_hook( __FILE__, 'my_plugin_deactivation' );
function my_plugin_deactivation() {
  //drop a custom db table
  global $wpdb;
  $wpdb->query( "DROP TABLE IF EXISTS ".SEARCH_INDEX_TABLE );
}
