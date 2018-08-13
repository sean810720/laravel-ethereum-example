<?php

namespace App\Repositories;

// 套件已自動建立 Facade
use Ethereum;

// 用來包裹交易資料的工具物件
use Jcsofts\LaravelEthereum\Lib\EthereumMessage;
use Jcsofts\LaravelEthereum\Lib\EthereumTransaction;

// 用來產生合約方法與附加參數的hash
use kornrunner\Keccak;

// 用來做參數轉 hex 調整的物件
use kornrunner\Solidity;

/**
 * 以太坊呼叫
 */

class EthereumRepository extends Ethereum
{
    /**
     * 送出交易 (上鏈)
     *
     * @param  string  $from      (呼叫者錢包位址,            格式範例: 0xAc7833EF2E9e2a342fB1864C2d86e92E07F8E515)
     * @param  string  $to        (被呼叫合約位址/目標錢包位址, 格式範例: 0x5764CccBf12964daCcd6b1258CECF5eA0D86371E)
     * @param  integer $value     (以太坊最小單位貨幣 Wei, 呼叫合約時帶0)
     * @param  string  $method    (呼叫合約方法名稱, 帶空表示不呼叫合約方法)
     * @param  array   $params    (呼叫合約方法參數, 格式: [型態 => 值, 型態 => 值], 沒有或不呼叫合約方法則帶[])
     * @param  integer $gas       (發起人願意為交易出的瓦斯量, 預設 1000000)
     *
     * @return array   $result    (回傳交易相關資訊)
     */

    public static function transaction($from = '', $to = '', $value = 0, $method = '', $params = [], $gas = 1000000)
    {
        $result = [];

        try {

            // 呼叫合約方法與要塞的資料
            $data = '0x';

            if (!empty($method)) {

                if (!empty($params)) {

                    // 方法(型態,型態,型態), 透過 Keccak 轉好後取前 8 位
                    $data .= substr(Keccak::hash($method . '(' . implode(',', array_keys($params)) . ')', 256), 0, 8);

                    // 參數1參數2參數3....
                    foreach ($params as $k => $v) {
                        if (is_bool($v)) {
                            $data .= str_pad(dechex((int) $v), 63, '0', STR_PAD_LEFT);

                        } elseif (is_numeric($v)) {
                            $data .= Solidity::hex($v);

                        } else {
                            $data .= str_pad(Solidity::hex($v), 64 - strlen(Solidity::hex($v)), '0');
                        }
                    }

                } else {

                    // 方法(型態,型態,型態), 透過 Keccak 轉好後取前 8 位
                    $data .= substr(Keccak::hash($method . '()', 256), 0, 8);
                }
            }

            // 建立訊息物件 (用來估算瓦斯量)
            $message = new EthereumMessage(

                // 呼叫者錢包位址
                $from,

                // 被呼叫合約位址
                $to,

                // Value (以太坊最小單位貨幣 Wei, 預設帶0)
                '0x' . dechex($value),

                // Gas - 發起人願意為交易出的瓦斯量
                '0x' . dechex($gas),

                // Gas Price - 鏈上的瓦斯單價
                self::eth_gasPrice(),

                // 呼叫資料
                $data,

                // nonce
                self::eth_getTransactionCount($from)
            );

            // 建立交易物件
            $transaction = new EthereumTransaction(

                // 呼叫者錢包位址
                $from,

                // 被呼叫合約位址
                $to,

                // Value (以太坊最小單位貨幣 Wei, 預設帶0)
                '0x' . dechex($value),

                // Gas - 發起人願意為交易出的瓦斯量
                '0x' . dechex(hexdec(self::eth_estimateGas($message, 'latest')) * 10),

                // Gas Price - 鏈上的瓦斯單價
                self::eth_gasPrice(),

                // 呼叫資料
                $data,

                // nonce
                self::eth_getTransactionCount($from)
            );

            // 送出交易, 回傳交易相關資訊
            $tx_send    = self::eth_sendTransaction($transaction);
            $tx_receipt = collect(self::eth_getTransactionReceipt($tx_send));

            if ($tx_receipt['status'] == '0x1' && !empty($tx_receipt['blockHash'])) {

                // 交易成功 && 新區塊已完成挖礦, 回傳新區塊資訊
                $result = json_decode(json_encode(self::eth_getBlockByHash($tx_receipt['blockHash'])), 1);

                // 將數字欄位轉成 10 進位
                $result['number']                              = base_convert($result['number'], 16, 10);
                $result['nonce']                               = base_convert($result['nonce'], 16, 10);
                $result['size']                                = base_convert($result['size'], 16, 10);
                $result['gasLimit']                            = base_convert($result['gasLimit'], 16, 10);
                $result['gasUsed']                             = base_convert($result['gasUsed'], 16, 10);
                $result['timestamp']                           = base_convert($result['timestamp'], 16, 10);
                $result['transactions'][0]['nonce']            = base_convert($result['transactions'][0]['nonce'], 16, 10);
                $result['transactions'][0]['blockNumber']      = base_convert($result['transactions'][0]['blockNumber'], 16, 10);
                $result['transactions'][0]['gas']              = base_convert($result['transactions'][0]['gas'], 16, 10);
                $result['transactions'][0]['gasPrice']         = base_convert($result['transactions'][0]['gasPrice'], 16, 10);
                $result['transactions'][0]['value']            = base_convert($result['transactions'][0]['value'], 16, 10);
                $result['transactions'][0]['transactionIndex'] = base_convert($result['transactions'][0]['transactionIndex'], 16, 10);

            } else {

                // 只回傳 Transaction Receipt
                $result = json_decode(json_encode($tx_receipt), 1);

                if ($result['status'] == '0x1') {

                    // 將數字欄位轉成 10 進位
                    $result['nonce']            = base_convert($result['nonce'], 16, 10);
                    $result['blockNumber']      = base_convert($result['blockNumber'], 16, 10);
                    $result['gas']              = base_convert($result['gas'], 16, 10);
                    $result['gasPrice']         = base_convert($result['gasPrice'], 16, 10);
                    $result['value']            = base_convert($result['value'], 16, 10);
                    $result['transactionIndex'] = base_convert($result['transactionIndex'], 16, 10);

                } else {

                    // 將數字欄位轉成 10 進位
                    $result['transactionIndex']  = base_convert($result['transactionIndex'], 16, 10);
                    $result['blockNumber']       = base_convert($result['blockNumber'], 16, 10);
                    $result['gasUsed']           = base_convert($result['gasUsed'], 16, 10);
                    $result['cumulativeGasUsed'] = base_convert($result['cumulativeGasUsed'], 16, 10);
                }
            }

            return $result;

        } catch (ErrorException $e) {
            return $e->getMessage();
        }
    }

    /**
     * 送出呼叫 (不上鏈)
     *
     * @param  string  $from      (呼叫者錢包位址,            格式範例: 0xAc7833EF2E9e2a342fB1864C2d86e92E07F8E515)
     * @param  string  $to        (被呼叫合約位址/目標錢包位址, 格式範例: 0x5764CccBf12964daCcd6b1258CECF5eA0D86371E)
     * @param  integer $value     (以太坊最小單位貨幣 Wei, 呼叫合約時帶0)
     * @param  string  $method    (呼叫合約方法名稱, 帶空表示不呼叫合約方法)
     * @param  array   $params    (呼叫合約方法參數, 格式: [型態 => 值, 型態 => 值], 沒有或不呼叫合約方法則帶[])
     * @param  integer $gas       (發起人願意為交易出的瓦斯量, 預設 1000000)
     *
     * @return array   $result    (回傳呼叫結果)
     */

    public static function call($from = '', $to = '', $value = 0, $method = '', $params = [], $gas = 1000000)
    {
        $result = [];

        try {

            // 呼叫合約方法與要塞的資料
            $data = '0x';

            if (!empty($method)) {

                if (!empty($params)) {

                    // 方法(型態,型態,型態), 透過 Keccak 轉好後取前 8 位
                    $data .= substr(Keccak::hash($method . '(' . implode(',', array_keys($params)) . ')', 256), 0, 8);

                    // 參數1參數2參數3....
                    foreach ($params as $k => $v) {
                        if (is_bool($v)) {
                            $data .= str_pad(dechex((int) $v), 63, '0', STR_PAD_LEFT);

                        } elseif (is_numeric($v)) {
                            $data .= Solidity::hex($v);

                        } else {
                            $data .= str_pad(Solidity::hex($v), 64 - strlen(Solidity::hex($v)), '0');
                        }
                    }

                } else {

                    // 方法(型態,型態,型態), 透過 Keccak 轉好後取前 8 位
                    $data .= substr(Keccak::hash($method . '()', 256), 0, 8);
                }
            }

            // 建立訊息物件
            $message = new EthereumMessage(

                // 呼叫者錢包位址
                $from,

                // 被呼叫合約位址
                $to,

                // Value (以太坊最小單位貨幣 Wei, 預設帶0)
                '0x' . dechex($value),

                // Gas - 發起人願意為交易出的瓦斯量
                '0x' . dechex($gas),

                // Gas Price - 鏈上的瓦斯單價
                self::eth_gasPrice(),

                // 呼叫資料
                $data,

                // nonce
                self::eth_getTransactionCount($from)
            );

            // 回傳呼叫結果
            $result = json_decode(json_encode(self::eth_call($message, 'latest'), 1));
            return $result;

        } catch (ErrorException $e) {
            return $e->getMessage();
        }
    }

    /**
     * 單位轉換: 以太幣換算成 Wei
     *
     * @param  string  $eth     (以太幣)
     *
     * @return integer $result  (Wei)
     */

    public static function to_wei($eth = 0)
    {
        $result = 0;

        if (!empty($eth)) {
            $result = $eth * 1000000000000000000;
        }

        return $result;
    }

    /**
     * 單位轉換: Wei 換算成以太幣
     *
     * @param  string  $wei     (Wei)
     *
     * @return integer $result  (以太幣)
     */

    public static function from_wei($wei = 0)
    {
        $result = 0;

        if (!empty($wei)) {
            $result = $wei / 1000000000000000000;
        }

        return $result;
    }
}
