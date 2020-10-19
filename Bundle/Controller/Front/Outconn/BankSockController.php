<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
namespace Bundle\Controller\Front\Outconn;

use Request;
use Logger;
use Component\Bankda\BankdaOrder;

class BankSockController extends \Controller\Front\Controller
{

    public function index()
    {
        \Logger::debug(__METHOD__, Request::request()->all());

        switch (gd_isset(Request::request()->get('mode'))) {
            case 'receiptOfMoney': // 주문입금확인 처리
                $bk = new BankdaOrder('receive', gd_isset(Request::request()->get('ordno')));
                exit;
            case 'filterOrderStep': // 주문상태기준으로 필터
                $bk = new BankdaOrder('filterOrderStep', '');
                exit;
            default:
                exit;

        }
    }
}
