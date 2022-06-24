<?php
/**
 * Plugin Name: Redirect Conductor
 * Version: 1.0
 * Description: change your permalink to the specific url.
 * Author: CINRA Inc,.
 * Author URI: https://www.cinra.co.jp
 * Plugin URI: https://www.cinra.co.jp
 * Text Domain: redirect-conductor
 * @package CINRA Wordpress Package
 * @depend Advanced Custom Fields
 */

if (!defined( 'REDIRECT_CONDUCTOR_IS_ACTIVE' )) define( 'REDIRECT_CONDUCTOR_IS_ACTIVE', true );
if (!defined( 'REDIRECT_CONDUCTOR_ACF_FIELD' )) define( 'REDIRECT_CONDUCTOR_ACF_FIELD', 'redirect_url' );

class RedirectConductor
{

  public static function init()
  {
    add_filter('post_link', array( 'RedirectConductor', 'revise_permalink') , 100);
    add_filter('page_link', array( 'RedirectConductor', 'revise_permalink'), 100);
    add_filter('post_type_link', array( 'RedirectConductor', 'revise_permalink'), 100);

    add_action('wp', array( 'RedirectConductor', 'do_redirect') );
  }

  /* ----------------------------------------------------------

    revise the permalink

  ---------------------------------------------------------- */

  static function revise_permalink($str = null, $post = null)
  {

    $post_id = null;

    if ($str)
    {

      if (!$post)
      {
        global $post;
        $post_id = $post->ID;
      }

      if (is_object($post)) $post_id = $post->ID;

      if ($redirect = self::get_redirect_url()) $str = $redirect;

    }

    return $str;

  }

  /* ----------------------------------------------------------

    do￼redirect

  ---------------------------------------------------------- */

  static function do_redirect()
  {
    // リダイレクト設定
    if (!is_archive() && $redirect = self::get_redirect_url())
    {
      if (preg_match('/^http/', $redirect))
      {
        header('location:' . $redirect);
        exit;
      }
      header('location:'.home_url( $redirect ));
    }

  }

  /* ----------------------------------------------------------

    ￼get redirect field

  ---------------------------------------------------------- */

  static function get_redirect_key()
  {
    return apply_filters('rc_get_redirect_key', REDIRECT_CONDUCTOR_ACF_FIELD);
  }

  /* ----------------------------------------------------------

    ￼get redirect url

  ---------------------------------------------------------- */

  static function get_redirect_url()
  {

    $url = null;

    if (class_exists('acf'))
    {
      return get_field(self::get_redirect_key());
    }
    else
    {
      $post_id = get_the_ID();
      return get_post_meta($post_id, self::get_redirect_key(), true);
    }

    return apply_filters( 'rc_get_redirect_url', $url );

  }

}

RedirectConductor::init();
