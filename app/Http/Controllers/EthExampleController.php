<?php

namespace App\Http\Controllers;

use App\Helplers\ToolsHelpler as Tools;
use App\Repositories\EthereumRepository as Ethereum;

/**
 * 以太坊操作範例
 */

class EthExampleController extends Controller
{
    // 查詢鏈上帳號錢包餘額
    public function account_balance()
    {
        try {

            // 撈出目前鏈上帳號
            $accounts = Ethereum::personal_listAccounts();

            // 撈出錢包餘額 (Eth)
            $balances = [];

            foreach ($accounts as $k => $v) {
                $balances[$k] = Ethereum::from_wei(base_convert(Ethereum::eth_getBalance($v), 16, 10));
            }

            return response()->json($balances);

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // 送錢給某帳戶
    public function send_money()
    {
        $result = Ethereum::transaction(

            // 呼叫者錢包位址
            '0xAc7833EF2E9e2a342fB1864C2d86e92E07F8E515',

            // 目標錢包位址
            '0x273644d70cc382FAf4df1eb2d6829B1a7629292F',

            // 要送的錢 (單位:Wei)
            Ethereum::to_wei(0.1)
        );

        return response()->json($result);
    }

    // 呼叫合約並建立交易
    public function send_contract()
    {
        $result = Ethereum::transaction(

            // 呼叫者錢包位址
            '0xAc7833EF2E9e2a342fB1864C2d86e92E07F8E515',

            // 被呼叫的合約或錢包位址
            '0x827bca53727463f6fa54e603835e4ac09aa7eaf6',

            // 要送的錢 (單位:Wei, 合約交易帶0)
            0,

            // 呼叫合約方法名稱
            'set',

            // 呼叫合約方法參數 [型態 => 值, 型態 => 值]
            [
                'bytes32' => base64_encode('陳尚恩'),
            ]
        );

        return response()->json($result);
    }

    // 呼叫合約不建立交易
    public function call_contract()
    {
        $result = Ethereum::call(

            // 呼叫者錢包位址
            '0xAc7833EF2E9e2a342fB1864C2d86e92E07F8E515',

            // 被呼叫的合約或錢包位址
            '0x827bca53727463f6fa54e603835e4ac09aa7eaf6',

            // 要送的錢 (單位:Wei, 合約交易帶0)
            0,

            // 呼叫合約方法名稱
            'get',

            // 呼叫合約方法參數 [型態 => 值, 型態 => 值]
            []
        );

        return base64_decode(Tools::hexToStr($result));
    }
}
