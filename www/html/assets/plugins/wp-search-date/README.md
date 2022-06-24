# WP-Search-Dates
日時情報での検索を軽量化するため、検索用のtableを新たに作成する

## テーブル構造
table: `{$prefix}_search_dates`

* id            : int(11)   : PRIMALY KEY
* post_id       : int(11)   : 投稿ID
* start_date    : int(11)   : value
* end_date      : int(11)   : value

## `wp_search_dates.php`
### `WP_Search_Dates`
* table名の定義
* 各classのinclude
* `WP_Search_Dates`の初期化

### `function register_activation_hook`
プラグイン有効化と同時にtable `{$prefix}_search_dates` 作成


## `class.config.php` : キー設定
<<<<<<< HEAD
* `$options`: `post_type`, `category`, `keys`のslugの初期値設定
=======
* `$options`: `post_type`, `category`, `key`のslugの初期値設定
>>>>>>> 15760cb10aecbd58435a27edf92b6f2dac318f07
* `function set`: `$options`の設定
  * theme側のfunction.phpでの実行例

```php
add_action('search_date_config_init', function(){
  $search_date_options   = array(
    'post_type' => array('post'),
    'category'  => array('event'),
<<<<<<< HEAD
    'keys'      => array(
                    'start_date'  => 'acf_start_date',
                    'end_date'    => 'acf_end_date'
                    ),
  );
  WP_Search_Date_Config::set(null, $search_date_options);
=======
    'keys' => array('start_date' => 'acf_start_date', 'end_date' => 'acf_end_date'),
  );
  WP_Search_Dates_Config::set(null, $search_date_options);
>>>>>>> 15760cb10aecbd58435a27edf92b6f2dac318f07
});
```
* `function get`: `$options`の取得


## `class.admin.php` : 登録処理
* `$options`で設定した`post_type`のsave時、`{$prefix}_search_dates`に`start_date`, `end_date`をUnix タイムスタンプ形式でsaveする


## `class.function.php` : 検索処理
### `function get_wp_search_dates($statuses, $relation, $orderby)`
select用関数

* `$statuses`から、各statusごとにWHERE文を作成して `{$prefix}_search_index`を検索し、  
各検索結果を`$relation`にそってmergeしてpost_idを配列で返す
* `$relation`には`AND`か`OR`が入り、各statusの検索結果の連結方法を指定する
* `$status`によって検索条件は下記のようになる
<<<<<<< HEAD
  * `future`:   `time() < {start_time}`
  * `opened`:   `{start_time} <= time() AND time() <= {end_time}`
  * `finished`: `{end_time} < time()`
* `$orderby`がある場合はsql文に追加

### `function get_wp_search_index_value($post_id, $culmun)`
* `wp_search_dates`から、`WHERE post_id=$postid` の `$culmun` の値を取得する
* `$culumn`の指定がない場合は全てのカラムをSELECTする



=======
  * `future`:   `time() < {start_date}`
  * `opened`:   `{start_date} <= time() AND time() <= {end_date}`
  * `finished`: `{end_date} < time()`

### `function get_wp_search_index_value($post_id, $culmun)`
`wp_search_dates`から、`WHERE post_id=$post_id` の `$culmun` の値を取得する
>>>>>>> 15760cb10aecbd58435a27edf92b6f2dac318f07
