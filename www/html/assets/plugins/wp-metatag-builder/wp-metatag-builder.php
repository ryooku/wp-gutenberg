<?php
/**
 * Plugin Name: Metatag Builder
 * Version: 1.1
 * Description: render meta data on wp header.
 * Author: CINRA Inc,.
 * Author URI: https://www.cinra.co.jp
 * Plugin URI: https://www.cinra.co.jp
 * Text Domain: meta-conductor
 * @package CINRA Wordpress Package
 */

if (!defined( 'METATAG_BUILDER_AUTO_RENDER' )) define( 'METATAG_BUILDER_AUTO_RENDER', true );

class MetatagBuilder
{

  private static $vals = [];

  public static function init()
  {
    do_action( 'metatag_builder_init' );
  }

  /**
   * set 値をセットする
   * @param array|string $vals 文字列の場合、MetaのNameの値となる
   * @param string $content      設定値
   * @param string $key_attr     MetaのNameにあたる部分。デフォルトは「name」
   * @param string $content_attr MetaのContentにあたる部分。デフォルトは「content」
   * @param string $tag          タグ。デフォルトは「meta」
   * @param boolean $ignore_sanitize サニタイズをスキップ。デフォルトはfalse
   */
  public static function set( $vals, $content = null, $key_attr = 'name', $content_attr = 'content', $tagname = 'meta', $ignore_sanitize = false )
  {
    if ( !is_array($vals) ) $vals = array(
      array(
        'key'               => $vals,
        'content'           => $content,
        'key_attr'          => $key_attr,
        'content_attr'      => $content_attr,
        'tagname'           => $tagname,
        'ignore_sanitize'   => $ignore_sanitize,
      ),
    );

    foreach ($vals as $val)
    {
      self::$vals[$val['key']] = array_merge(array(
        'key'               => null,
        'content'           => null,
        'key_attr'          => 'name',
        'content_attr'      => 'content',
        'tagname'           => 'meta',
        'ignore_sanitize'   => false,
      ), $val );
    }
  }

  /**
   * get 値を取得する
   * @param  string $key     キー。nullの場合は、配列全部返す
   * @param  string $default 値がなかった時のデフォルト値
   * @return array|string
   */
  public static function get( $key = null, $default = null )
  {
    if (!$key) return self::$vals;
    return isset( self::$vals[$key] ) && !empty( self::$vals[$key] ) ? self::$vals[$key]['content'] : $default;
  }

  public static function sanitize( $val = null, $limit = null )
  {
    if ( !$val ) return $val;
    $val = htmlspecialchars_decode($val);
    $val = strip_tags($val);
    $val = htmlspecialchars($val);
    $val = str_replace( array( "\n", "\r" ), '', $val );
    if ($limit) $val = mb_strimwidth($val, 0, (int)$limit, '…');
    return $val;
  }

  public static function conduct()
  {
    $html = "";
    if (self::get())
    {
      foreach(self::get() as $val)
      {
        $html .= sprintf(
          "<%s %s=\"%s\" %s=\"%s\">\n",
          self::sanitize($val['tagname']),
          self::sanitize($val['key_attr']),
          self::sanitize($val['key']),
          self::sanitize($val['content_attr']),
          !$val['ignore_sanitize'] ? self::sanitize($val['content']) : $val['content']
        );
      }
    }
    return apply_filters( 'metatag_builder_compose', $html);
  }

  public static function render()
  {
    echo self::conduct();
  }

}

add_action( 'wp', array( 'MetatagBuilder', 'init' ) );
if ( METATAG_BUILDER_AUTO_RENDER ) add_action( 'wp_head', array('MetatagBuilder', 'render') );

/**
 * set_meta_data()
 * 値をセットするヘルパーメソット
 *
 * @param array|string $vals 文字列の場合、MetaのNameの値となる
 * @param string $content      設定値
 * @param string $key_attr     MetaのNameにあたる部分。デフォルトは「name」
 * @param string $content_attr MetaのContentにあたる部分。デフォルトは「content」
 * @param string $tag          タグ。デフォルトは「meta」
 * @param boolean $ignore_sanitize サニタイズをスキップ。デフォルトはfalse
 */
function set_meta_data($key, $content = null, $key_attr = 'name', $content_attr = 'content', $tagname = 'meta', $ignore_sanitize = false )
{
  MetatagBuilder::set( $key, $content, $key_attr, $content_attr, $tagname, $ignore_sanitize );
}

/**
 * set_meta_descriptions()
 * meta description、og:description、twitter:description同時に設定できるヘルパーメソッド。長い文章も240文字で自動的に省略。自動サニタイズ
 * @param string $raw_content 文言
 */
function set_meta_descriptions( $raw_content )
{
  $content = MetatagBuilder::sanitize($raw_content, 240);
  set_meta_data( 'description', $content );
  set_meta_data( 'og:description', $content, 'property' );
  set_meta_data( 'twitter:description', $content );
}