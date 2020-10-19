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

namespace Bundle\Controller\Admin\Order;

use Exception;
use Request;
use App;

/**
 * 입금내역 주문서 수동매칭 리스트
 *
 * @author    sf2000
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2018, NHN godo: Corp.
 */
class BankdaManualMatchController extends \Controller\Admin\Controller
{

    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('order', 'bankda', 'manualMatch');

        //입금내역 검색 값 - 뱅크다
        $search['bkdate'][0] = date('Ymd', strtotime('-6 day'));
        $search['bkdate'][1] = date('Ymd');

        // 페이지 레코드수
        $search['page_num'] = 10;

        // 입금대기 주문 검색 - 솔루션
        $ordSearch['treatDate'][0] = date('Ymd', strtotime('-6 day'));
        $ordSearch['treatDate'][1] = date('Ymd');

        $this->setData('search', gd_isset($search));
        $this->setData('ordSearch', gd_isset($ordSearch));

        $this->addScript(
            [
                'BankdaMatch.js',
                'BankdaManualMatch.js',
                'ajaxGraphMethod.js',
            ]
        );
        $bankda = App::load('\\Component\\Bankda\\Bankda');
        $rBank = $bankda->getUseBank();
        $bankda->getIsUseBankda();
        $this->setData('rBank', gd_isset($rBank));
    }
}
