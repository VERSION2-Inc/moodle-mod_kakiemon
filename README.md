# 一筆柿右衛門

Moodleの活動モジュールです。学生が作成したページ（ポートフォリオ）を相互に評価することができます。


# 動作環境

Moodle 2.5以上


# インストール

リポジトリをMoodleのmodディレクトリにチェックアウトしてください。

    cd moodle/mod
    git clone https://github.com/VERSION2-Inc/moodle-mod_kakiemon kakiemon

ページのPDF出力を行うにはwkhtmltopdfが必要ですので、http://wkhtmltopdf.org/ からダウンロードしてMoodleが稼働しているサーバーにインストールし、サイト管理＞プラグイン＞活動モジュール＞一筆柿右衛門 でインストールした場所を設定してください。

iOSからアップロードされた画像を自動回転させるにはPHPのImageMagick拡張をインストールしてください。

CentOSでのインストール方法

    yum install php-pecl-imagick
