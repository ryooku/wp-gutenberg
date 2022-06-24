<?php
/*
Plugin Name: WP Search Date
Plugin URI: http://www.cinra.co.jp/
Description: db-table for search using ACF
Version: 1.0.0
Author: NIKAI, yasuyo
Author URI: http://www.cinra.co.jp/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

define('SEARCH_DATE_URL', plugin_dir_url(__FILE__));
define('SEARCH_DATE_PATH', plugin_dir_path(__FILE__));

global $wpdb;
if(!defined('SEARCH_DATE_TABLE')) define('SEARCH_DATE_TABLE', $wpdb->prefix.'search_date');

class WP_Search_Date
{
  public function __construct()
  {
    add_action('plugins_loaded', array($this, 'initialize'));
  }

  public function initialize()
  {
    include_once SEARCH_DATE_PATH . 'classes/class.config.php';
    include_once SEARCH_DATE_PATH . 'classes/class.function.php';

    if (is_admin())
    {
      include_once SEARCH_DATE_PATH . 'classes/class.admin.php';
      new WP_Search_Date_Admin();
    }
  }
}

new WP_Search_Date();


register_activation_hook( __FILE__, function()
{
  include_once SEARCH_DATE_PATH . 'classes/class.config.php';
  include_once SEARCH_DATE_PATH . 'classes/class.admin.php';

  new WP_Search_Date_Config();
  $sd_admin = new WP_Search_Date_Admin();

  $options = WP_Search_Date_Config::$options;

  $keys = $options['keys'];

  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE " .SEARCH_DATE_TABLE ." (
    id int(11) NOT NULL AUTO_INCREMENT,
    post_id int(11) NOT NULL,
    start_date int(11),
    end_date int(11)";

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
      $sd_admin->save_date_meta($post->ID);
    }
  }
});

register_deactivation_hook(__FILE__, function()
{
  global $wpdb;
  $wpdb->query( "DROP TABLE IF EXISTS ".SEARCH_DATE_TABLE );
});
