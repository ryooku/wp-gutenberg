# WP Metatag Builder

> メタタグを簡単に設定できます

## 1. 実装仕様

### 出力項目

- `wp_head()`で自動的にメタタグが出力されます
- メタタグの設定は、`$wp_query->meta_conductor->set()`で行います

---

## 2. 実装内容

> 実装する内容を具体的に記述する

### ヘルパー／関数

#### set_meta_data( $key, $content, $key_attr )

|Atribute|Description|Default|
|---|---|---|
|$key|Meta Nameの値||
|$content|Meta Contentの値||
|$key_attr|Mata Nameを変更する|`name`|
|$content_attr|Mata Contentを変更する|`content`|
|$tagname|タグ名を変更する|`meta`|

**使用例**

```php
<?php

// wp_head()の前に実行すると、デフォルト設定を上書きできる
set_meta_data( 'og:title', get_the_title(), 'property' );
set_meta_data( 'keywords', '後から設定したキーワード' );

get_header();

?>
```

**出力例**

```html
<meta name="keywords" content="後から設定したキーワード">
<meta property="og:title" content="記事のタイトル">
```

#### render_meta_data()

- セットしてあるメタタグが出力されます
- `wp_head()`で自動的に出力されるので、通常は明示的に実行する必要なし

### モデル／クラス

#### MetaConductor

##### MetaConductor::set( $key, $content, $key_attr, $content_attr, $tagname )

- メタタグの値を設定します。$keyに配列を指定すると、複数の値を一気に設定可能です

|Atribute|Description|Default|
|---|---|---|
|$key|Meta Nameの値||
|$content|Meta Contentの値||
|$key_attr|Mata Nameを変更する|`name`|
|$content_attr|Mata Contentを変更する|`content`|
|$tagname|タグ名を変更する|`meta`|

##### MetaConductor::get( $key, $default )

- メタタグの値を取得します

|Atribute|Description|Default|
|---|---|---|
|$key|Meta Nameの値||
|$default|値が空だった場合に返すデフォルト値||

##### MetaConductor::conduct()

- 設定されたメタタグをHTMLとして返します

##### MetaConductor::render()

- 設定されたメタタグを出力します

### フィルターフック

#### meta_conductor_init

- `MetaConductor`を初期化した時に通すフィルターフック
- `$wp_query->meta_conductor`を引数として渡すので、その場でメタタグをセットできる

#### meta_conductor_conduct

- **MetaConductor::conduct()**実行時に生成されたHTMLを通すフィルターフック

### 定数

#### META_CONDUCTOR_AUTO_RENDER

- `true`をセットすると、`wp_head()`で自動的にメタタグが出力されます。デフォルトは`true`