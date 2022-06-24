<?php

class WP_Search_Date_Admin
{

  public function __construct()
  {
    add_action('save_post', array($this, 'save_date_meta'));
  }

  public function save_date_meta($post_id)
  {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    global $wpdb;

    $table_name = SEARCH_DATE_TABLE;

    $options = WP_Search_Date_Config::get();
    if(!in_array(get_post_type($post_id), $options['post_type'])) return;
    if($options['category'] && !in_category($options['category'], $post_id)) return;

    $wpdb->delete($table_name, array('post_id' => $post_id));

    $post = get_post($post_id);
    if (empty($post)) return;

    $keys = $options['keys'];
    $repeater_dates = array();

    if (!empty($options['acf_repeater_key'])) $repeater_dates = get_field($options['acf_repeater_key'], $post_id);

    if ($repeater_dates)
    {
      foreach ($repeater_dates as $date)
      {
        $start_date = isset($date[str_replace('acf_', '', $keys['start'])]) ? $date[str_replace('acf_', '', $keys['start'])] : '';
        $end_date = isset($date[str_replace('acf_', '', $keys['end'])]) ? $date[str_replace('acf_', '', $keys['end'])] : '';

        if (!$start_date && !$end_date) continue;

        $wpdb->insert($table_name, array(
          'post_id'    => $post_id,
          'start_date' => ($start_date ? strtotime($start_date) : 0),
          'end_date'   => ($end_date ? strtotime($end_date) : 0),
        ));
      }
    }
    else
    {
      $start_date = (strpos($keys['start'], 'acf_') === 0) ? get_field(str_replace('acf_', '', $keys['start']), $post_id) : get_post_meta($post_id, $keys['start']);
      $end_date = (strpos($keys['end'], 'acf_') === 0) ? get_field(str_replace('acf_', '', $keys['end']), $post_id) : get_post_meta($post_id, $keys['end']);

      if (!$start_date && !$end_date) return;

      $wpdb->insert($table_name, array(
        'post_id'    => $post_id,
        'start_date' => ($start_date ? strtotime($start_date) : 0),
        'end_date'   => ($end_date ? strtotime($end_date) : 0),
      ));
    }
  }

}