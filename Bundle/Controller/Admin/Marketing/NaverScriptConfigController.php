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
namespace Bundle\Controller\Admin\Marketing;

use Exception;
use Globals;

/**
 * 네이버 공통 유입 스크립트 설정 페이지
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class NaverScriptConfigController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('marketing', 'naver', 'scriptconfig');

        try {
            $data = gd_policy('naver.common_inflow_script');
            gd_isset($data['naverCommonInflowScriptFl'], 'n');
            gd_isset($data['accountId']);

            $checked = array();
            $checked['naverCommonInflowScriptFl'][$data['naverCommonInflowScriptFl']] = 'checked="checked"';
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', $data);
        $this->setData('checked', $checked);
    }
}
