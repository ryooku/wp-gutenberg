<?php

function get_wp_search_date($statuses = array(), $relation = 'AND', $orderby = 'post_id')
{
  if(!$statuses) return false;
  $results = get_posts_by_date_status($statuses, $orderby);
  if($results == false) return false;

  switch (count($results)) {
    case 1:
      $ids = $results[0]['ids'];
      break;

    case 2:
      if($relation == "OR")
      {
        $ids = array_merge($results[0]['ids'], $results[1]['ids']);
      }
      else
      {
        $ids = array_intersect($results[0]['ids'], $results[1]['ids']);
      }
      break;

    default:
      return false;
      break;
  }
  return $ids;
}

function get_posts_by_date_status($statuses, $orderby)
{
  global $wpdb;
  $table_name = SEARCH_DATE_TABLE;
  $now = time();

  foreach($statuses as $status)
  {
    $where = '';
    switch ($status) {
      case 'future':
        $where = "start_date > ". $now;
        break;

      case 'opened':
        $where = "start_date <= ". $now. " AND end_date >= ". $now;
        break;

      case 'finished':
        $where = "end_date < ". $now;
        break;

      default:
        return false;
        break;
    }
    $ids = $wpdb->get_col("SELECT post_id FROM {$table_name} WHERE {$where} ORDER BY {$orderby}");
    $results[] = array(
                       'status' => $status,
                       'ids' => $ids,
                       );
  }
  return $results;
}


function get_wp_search_index_value($post_id = null, $culmun = array())
{
  if(!$post_id) return false;
  $select = ($culumn) ? implode(', ', $culumn) : '*';

  return $wpdb->get_col("SELECT {$select} FROM {$table_name} WHERE post_id = {$post_id} ORDER BY {$orderby}");
}

function get_post_ids_by_date_terms($search)
{
  global $wpdb;
  $table_name = SEARCH_DATE_TABLE;
  $ids = array();
  $where = null;
  $values = array();

  if (!empty($search['start_date']) && !empty($search['end_date']))
  {
    $where = "(end_date >= %d OR end_date = 0) AND (start_date <= %d OR start_date = 0)";
    $values = array($search['start_date'], $search['end_date']);
  }
  elseif (!empty($search['start_date']))
  {
    $where = "end_date >= %d OR end_date = 0";
    $values = array($search['start_date']);
  }
  elseif (!empty($search['end_date']))
  {
    $where = "start_date <= %d OR start_date = 0";
    $values = array($search['end_date']);
  }

  if (!empty($where))
  {
    $prepare_sql = $wpdb->prepare("SELECT DISTINCT post_id FROM {$table_name} WHERE {$where} ORDER BY start_date", $values);
    $ids = $wpdb->get_col($prepare_sql);
  }

  return $ids;
}

function get_start_date_list($group = 'month', $format = 'Y年n月', $past_include = false)
{
  global $wpdb;
  $table_name = SEARCH_DATE_TABLE;
  $where = null;
  $list = array();

  if (!$past_include)
  {
    $where = "start_date >= " . current_time('timestamp');
  }

  if ($group === 'month')
  {
    $sql = "SELECT FROM_UNIXTIME(start_date, '%Y%m') as ym FROM {$table_name} WHERE {$where} GROUP BY ym ORDER BY ym";
    $results = $wpdb->get_col($sql);

    foreach ($results as $result)
    {
      $list[] = date($format, strtotime($result . '01'));
    }
  }

  return $list;
}
