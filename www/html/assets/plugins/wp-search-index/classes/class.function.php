<?php

function get_wp_search_index($get = array(), $terms = array(), $relation = 'AND', $orderby = 'post_id')
{
  $options = array();

  foreach($terms as $key => $value)
  {
    if(isset($get[$key]) && $get[$key])
    {
      $terms[$key]['query'] = $get[$key];

      if(is_array($terms[$key]['query']))
      {
        array_walk($terms[$key]['query'], function(&$i){ $i = esc_html($i); });
      }
      else
      {
        $terms[$key]['query'] = array(esc_html($terms[$key]['query']));
      }
    }

    if (!empty($terms[$key]['query']))
    {
      $options[] = array(
        'relation'    => 'OR',
        'key'         => $value['column'],
        'value'       => $terms[$key]['query'],
        'orderby'     => $orderby,
        'unified_key' => (is_array($value['column'])) ? $key : false,
      );
    }
  }

  if (!$options) return false;

  $results = get_posts_by_terms($options);
  if (!$results) return array();

  $ids = array();
  $i = 0;

  foreach ($results as $result)
  {
    if ($i === 0 || strtoupper($relation) === 'OR')
    {
      $ids = array_merge($ids, $result);
    }
    else
    {
      $ids = array_intersect($ids, $result);
    }

    $i++;
  }

  return $ids;
}

function get_posts_by_terms($options = array())
{
  if(!$options) return false;

  global $wpdb;
  $table_name = SEARCH_INDEX_TABLE;

  $ids = array();

  foreach($options as $option)
  {
    if(!is_array($option['key'])) $ids[$option['key']] = array();
    $where = '';
    $arr_where = array();

    if(!$option['key'] || !$option['value']) break;
    $relation = (empty($option['relation']) || strtoupper($option['relation']) === 'AND') ? 'AND' : 'OR';

    if(is_array($option['key']) && $option['unified_key'])
    {
      $s = 0;
      if(is_array($option['value'])) $option['value'] = $option['value'][0];

      foreach($option['key'] as $k)
      {
        if($s < 1)
        {
          $where = "`" . $k . "` LIKE '%" . esc_sql($option['value']) . "%'";
        }
        else
        {
          $where .= ' OR `'. $k . "` LIKE '%" . esc_sql($option['value']) . "%'";
        }
        $s++;
      }
      $option['key'] = $option['unified_key'];
    }
    elseif(is_array($option['value']))
    {
      foreach($option['value'] as $v)
      {
        if(isset($option['string']) && $option['string'] === true)
        {
          $arr_where[] = "`" . $option['key'] . "` = '" . esc_sql($v) . "'";
        }
        else
        {
          $arr_where[] = "`" . $option['key'] . "` LIKE '%*[" . esc_sql($v) . "]*%'";
        }
      }
      $where = implode(' '.$relation.' ', $arr_where);
    }
    else
    {
      if(isset($option['string']) && $option['string'] === true)
      {
        $where = "`" . $option['key'] . "` = '" . esc_sql($option['value']) . "'";
      }
      else
      {
        $where = "`" . $option['key'] . "` LIKE '%*[" . esc_sql($option['value']) . "]*%'";
      }
    }

    $orderby = (isset($option['orderby']) && $option['orderby']) ? "`" . $option['orderby'] . "`" : 'post_id';

    $ids[$option['key']] = $wpdb->get_col("SELECT post_id FROM {$table_name} WHERE {$where} ORDER BY {$orderby}");

  }

  return $ids;
}

function get_wp_search_index_related_person_ids()
{
  global $wpdb;
  $table_name = SEARCH_INDEX_TABLE;
  $results = array();
  $post_ids = array();
  $related_post_ids = array();

  $post_ids = get_wp_search_index(array('sca' => 'person'), array('sca' => array('column' => 'taxonomy_category')), 'AND', 'acf_kana');

  $values = $wpdb->get_col("SELECT `acf_related-people` FROM {$table_name} WHERE `acf_related-people` IS NOT NULL");
  if (!$values) return $results;

  foreach ($values as $val)
  {
    $val = preg_replace('/^\*\[(.+)\]\*$/', '$1', trim($val));
    $related_post_ids = array_merge($related_post_ids, explode(']* *[', $val));
  }

  $related_post_ids = array_unique($related_post_ids);

  return array_intersect($post_ids, $related_post_ids);
}


/*
$optionフォーマット

$options = array(
                array(
                      'relation'  => 'OR',//AND
                      'key'       => 'acf_service',
                      'value'     => array('sv_1', 'sv_2'),
                      ),
                array(
                      'relation'  => 'OR',//AND
                      'key'       => 'acf_tag',
                      'value'     => array('tg_1', 'tg_2'),
                      ),
                );
                array(
                      'relation'  => 'OR',
                      'key'       => 'acf_en_title',
                      'value'     => 'TEST',
                      'string'    => true,
                      ),
                array(
                      'relation'  => 'OR',
                      'key'       => 'acf_type',
                      'value'     => array('01', '02'),
                      'orderby'   => 'acf_en_title ASC',
                      ),
                );
 */