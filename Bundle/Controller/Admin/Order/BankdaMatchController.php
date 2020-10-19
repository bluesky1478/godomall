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
 * 입금조회/실시간입금확인
 *
 * @author    sunny
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class BankdaMatchController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('order', 'bankda', 'match');

        // --- 페이지 데이터
        try {

            // 검색
            $search['key'] = Request::get()->get('key');
            $search['keyword'] = Request::get()->get('keyword');
            // 현재상태/은행명
            $selected['gdstatus'][Request::get()->get('gdstatus')] = 'selected';
            $selected['bkname'][Request::get()->get('bkname')] = 'selected';
            // 입금일(default 7일)

            $bkdate = Request::get()->get('bkdate');
            if (!isset($bkdate)) {
                $bkdate[0] = date('Ymd', strtotime('-6 day'));
                $bkdate[1] = date('Ymd');
            }
            $search['bkdate'][0] = $bkdate[0];
            $search['bkdate'][1] = $bkdate[1];
            // 최종매칭일
            $gddate = Request::get()->get('gddate');
            if (!isset($gddate)) {
                $gddate[0] = '';
                $gddate[1] = '';
            }
            $search['gddate'][0] = $gddate[0];
            $search['gddate'][1] = $gddate[1];

            // 페이지 레코드수
            $pageNum = Request::get()->get('page_num');
            $search['page_num'] = gd_isset($pageNum, 10);
            $selected['page_num'][$search['page_num']] = 'selected';
            // 정렬
            $sort = Request::get()->get('sort');
            $orderby = gd_isset($sort, 'bkdate desc'); // 정렬 쿼리
            $selected['sort'][$orderby] = 'selected';

            $bankda = App::load('\\Component\\Bankda\\Bankda');
            $rBank = $bankda -> getUseBank();
            $bankda->getIsUseBankda();

            // @formatter:on
        } catch (Exception $e) {
            echo($e->ectMessage);
        }

        // --- 관리자 디자인 템플릿
        $this->addScript(
            [
                'BankdaMatch.js',
                'ajaxGraphMethod.js',
            ]
        );
        $this->setData('search', gd_isset($search));
        $this->setData('rBank', gd_isset($rBank));
        $this->setData('selected', $selected);
    }
}
