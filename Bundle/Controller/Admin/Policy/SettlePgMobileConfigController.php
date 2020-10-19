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
namespace Bundle\Controller\Admin\Policy;

use Component\Database\DBTableField;
use Component\Payment\PG;
use Component\Payment\CashReceipt;
use Globals;
use Request;
use App;

/**
 * PG 통합 설정
 * @author Lee Nam Ju <lnjts@godo.co.kr>
 */
class SettlePgMobileConfigController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws Except
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'settle', 'pgMobile');
        try {
            // --- PG 설정 불러오기
            $pg = new PG();
            $pgConf = gd_mpgs();

            // 기본 값을 다날로
            if (empty($pgConf['pgName']) === true) {
                $pgConf['pgName'] = 'danal';
            }
            $pgMode = empty(Request::get()->get('pgMode')) === false ? htmlspecialchars(Request::get()->get('pgMode')) : $pgConf['pgName'];

            // _GET 으로 pgMode가 있는 경우
            if (Request::get()->has('pgMode')) {
                if (Request::get()->get('pgMode') != $pgConf['pgName']) {
                    $pgConf = [];
                    $pgConf['pgCode'] = $pgMode;
                }
            }

            // --- 결제수단 설정 config 불러오기
            $settle = gd_policy('order.settleKind');

            $useFl = $pgConf['useFl'] ?? 'n';
            $checked['useFl'][$useFl] = 'checked';
        } catch (\Exception $e) {
             echo ($e->getMessage());
        }

        $pgMode = $pgMode ?? 'danal';   //아무것도 설정안돼있을 시 디폴트값

        $this->getView()->setDefine('layoutPgContent', Request::getDirectoryUri() . '/settle_pg_mobile_' . $pgMode . '.php');
        $this->setData('pgMode',$pgMode);
        $this->setData('gPg', Globals::get('gPgMobile'));
        $this->setData('pgNm', Globals::get('gPgMobile.' . $pgConf['pgCode']));
        $this->setData('settle', $settle);
        $this->setData('data', gd_htmlspecialchars($pgConf));
        $this->setData('checked', $checked);
    }
}
