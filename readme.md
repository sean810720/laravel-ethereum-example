<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>
<p align="center"><img height="200" src="https://img.jinse.com/139170_image3.png"></p>
<p align="center"><h1>Ethereum@Laravel 開發範例</h1></p><br /><br />

## 1. 安裝必備 composer 套件

- 在專案根目錄下執行:<br />
composer install<br /><br />


## 2. 編輯 .env 檔

- 在專案根目錄下執行:<br />
cp .env.example .env

- 編輯 .env<br />
vi .env

- 加入以下內容:<br />
ETH_HOST='http://localhost'<br />
ETH_PORT=8545<br /><br />


## 3. 以太坊私鏈架設

- Ganache 下載連結:<br />
https://truffleframework.com/ganache

- 安裝完啟動後本機就會預設有一條私鏈:<br />
http://127.0.0.1:8545<br /><br />


## 3. 安裝 Truffle 環境

- 執行以下指令:<br />
npm install -g truffle<br /><br />


## 4. 編譯智慧合約並配置上私鏈

- 在 Laravel 專案根目錄下執行:<br />
cd truffle<br />
truffle compile<br />
truffle migrate --reset<br /><br />

## 5. 修改 EthExampleController.php

- 調整 app/Http/Controllers/EthExampleController.php 中的「呼叫者錢包位址」與「被呼叫的合約或錢包位址」<br /><br />


## 6. 測試看看第一筆交易

- 在 Laravel 專案根目錄下執行:<br />
php artisan serve

- 執行測試連結:<br />
查詢鏈上帳號錢包餘額:  http://127.0.0.1:8000/account_balance<br />
送錢給某帳戶:         http://127.0.0.1:8000/send_money<br />
呼叫合約並建立交易:    http://127.0.0.1:8000/send_contract<br />
呼叫合約不建立交易:    http://127.0.0.1:8000/call_contract<br /><br />

有出現 JSON 結果表示交易呼叫成功<br /><br />


## 相關連結

- Truffle 用法<br />
https://truffleframework.com/docs<br />

- 以太坊 RPC API 文件<br />
https://github.com/ethereum/wiki/wiki/JSON-RPC

- Ethereum Package for Laravel<br />
https://github.com/jcsofts/laravel-ethereum/blob/master/README.md<br />



