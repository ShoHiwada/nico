# NicoShift（仮） - シフト管理システム

## 📝 概要

NicoShift は、シフト作成・管理の業務負担を軽減するための Web アプリケーションです。職員は自分のシフトを簡単に確認でき、管理者は月ごとのシフトを一括で作成・編集できます。

---

## 👤 想定ユーザー

| ロール | できること |
|-------|-------------|
| 職員（スタッフ） | 自分のシフトを確認 |
| 管理者（マネージャー） | 全職員のシフトを作成・編集・管理 |

---

## 🔐 ユーザー認証

- ログイン/ログアウト：Laravel Breeze
- ユーザー登録：管理者が職員を追加（メール＆パスワード）
- 権限分岐：`is_admin` フラグでロール制御

---

## 📅 職員向け：自分のシフト表示

| 項目 | 内容 |
|------|------|
| 表示内容 | 自分のシフトのみ（ログインユーザーIDでフィルタ） |
| 表示形式 | 月ごとのテーブル（勤務種別：日勤/夜勤/休） |
| 対応端末 | スマホ／PC（レスポンシブ対応） |

---

## 🧑‍💼 管理者向け：月間シフト管理

| 項目 | 内容 |
|------|------|
| 職員一覧表示 | 日付 × 職員のマトリクス表で表示 |
| 勤務入力 | 日付ごとに勤務内容を選択（ドロップダウン等） |
| データ保存 | ボタンクリックでDBへ保存（バリデーションあり） |
| PDF出力 | barryvdh/laravel-dompdf を使用予定（任意） |

---

## 📤 シフト共有＆希望申請

| 項目 | 方法 |
|------|------|
| シフト共有 | LINEで画像 or PDFを送信（後日実装） |
| シフト希望 | 各職員が来月の希望シフトを入力（後日追加） |

---

## 🛠 実装優先度（MVP）

1. Laravelプロジェクト作成＋ログイン機能（Breeze）
2. シフトDB設計＆マイグレーション
3. 職員向け：自分のシフト表示画面
4. 管理者向け：月間シフト入力画面
5. シンプルなUI調整（Tailwind）
6. PDF出力機能（余裕があれば）

---

## 📌 運用想定

| ユーザー | 操作内容 |
|---------|----------|
| 職員 | 自分のシフトをスマホで確認 |
| 管理者 | 月末に翌月のシフトを作成＆保存＋PDFで配布 |
| 管理者 | 必要に応じて職員の追加・編集 |

---

## 💡 導入効果

- 紙やLINE配布から脱却し、修正も即時反映
- 確認ミスや更新漏れの削減

---

## 🔭 今後の拡張予定

- シフト希望の入力機能（来月分）
- 勤務実績の管理・CSV出力
- 出退勤管理・資格管理 など

---

## 🧰 使用技術

- **PHP**: バックエンドのフレームワークとして使用
- **Laravel**: 高速なWebアプリケーション開発を実現するために使用
  - **Laravel Breeze**: 認証機能（ログイン/ログアウト、ユーザー登録）を実装
  - **Eloquent ORM**: データベースとのやり取りを簡単に実現
- **MySQL**: データベースとして使用（シフト情報などの保存）
- **Tailwind CSS**: シンプルで美しいレスポンシブデザインを提供
- **JavaScript**: フロントエンドのインタラクション（FullCalendarなど）
- **FullCalendar**: シフト表示やカレンダー機能を提供
- **barryvdh/laravel-dompdf**: PDF出力（オプション）

---


## セットアップ方法（ローカル開発用）

```bash
# 1. リポジトリをクローン
git clone https://github.com/ユーザー名/nico.git
cd nico

# 2. 依存関係をインストール
composer install
npm install

# 3. 環境設定
cp .env.example .env
php artisan key:generate

# 4. DB接続情報を .env に記載

# 5. マイグレーションとシーディング（必要なら）
php artisan migrate --seed

# 6. サーバー起動
php artisan serve
これで、http://localhost:8000 でアプリケーションにアクセスできます。

## 📂 ディレクトリ構成

nico-shift-app/
├── app/                    # アプリケーションのソースコード
│   ├── Http/               # コントローラやリクエストの処理
│   ├── Models/             # Eloquentモデル
├── resources/              # フロントエンドのリソース
│   ├── js/                 # JavaScriptファイル
│   ├── sass/               # SCSS（TailwindCSS設定）
│   └── views/              # Bladeテンプレート
├── routes/                 # アプリケーションのルーティング
│   └── web.php             # HTTPルート設定
├── storage/                # アプリケーションのログやキャッシュ
├── tests/                  # テストコード
├── .env                    # 環境設定ファイル
├── artisan                 # Laravelコマンド
├── composer.json           # Composer設定ファイル
├── package.json            # npm設定ファイル
└── README.md               # プロジェクトの仕様書


