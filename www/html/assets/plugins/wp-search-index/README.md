# WP-Search-Index
ACFやタクソノミーを用いた検索処理の軽量化のために検索用のtableを新たに作成する

## テーブル構造
table: `{$prefix}_search_index`

* id            : int(11) : PRIMALY KEY
* post_id       : int(11) : 投稿ID
* acf_xxx       : TEXT    : value  
※ 使うACFのスラッグを指定
* taxonomy_xxx      : TEXT    : value  
※ 使うタクソノミーのスラッグを指定


## `wp_search_index.php`
### `WP_Search_Index`
* table名の定義
* 各classのinclude
* `WP_Search_Index`の初期化

### `function register_activation_hook`
プラグイン有効化と同時にtable `{$prefix}_search_index` 作成


## `class.config.php` : キー設定
* `$options`: `post_type`, `category`のslug, 検索に使う項目の`keys`の初期値設定
* `function set`: `$options`の設定
  * theme側のfunction.phpでの実行例

```php
  add_action('search_index_config_init', function(){
  $search_index_options   = array(
    'post_type' => array('event'),
    'category'  => array(),
    'keys'  => array('acf_service', 'acf_tag'),
  );
  WP_Search_Index_Config::set(null, $search_index_options);
});
```
* `function get`: `$options`の取得


## `class.admin.php` : 登録処理
* `$options`で設定した`post_type`のsave時、`{$prefix}_search_index`に検索使用項目をsaveする
* 値が配列の場合は、`"*[値]*"` というように`*[]*`で囲う

## `class.function.php` : 検索処理
### `function get_wp_search_index($options)`：select用関数
* `$options` で指定されたrelation, key, valueで`{$prefix}_search_index`を検索し、post_idを配列で返す
* theme側での検索実行例

```php
if(isset($_GET['tg']) && $_GET['tg'])
{
  $get_query['tg'] = $_GET['tg'];
  if(is_array($get_query['tg']))
  {
    array_walk($get_query['tg'], function(&$i){ $i = esc_html($i); });
  }
  else
  {
    $get_query['tg'] = array($get_query['tg']);
  }

  $options = array(
                    'relation'  => 'AND',//AND
                    'key'       => 'acf_tag',
                    'value'     => $get_query['tg'],
                    );
  $results = get_wp_search_index($options);
}
```
* 一つの検索項目ごとに`get_wp_search_index` を実行する設計のため、  
複数条件で検索するときはtheme側でそれぞれのkeyごと検索結果IDを`array_merge`したり`array_intersect`したりすることになる  
→ 追加機能として実装したい

