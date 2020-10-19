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

use Exception;

/**
 * 무통장 입금 은행 리스트 페이지
 * [관리자 모드] 무통장 입금 은행 리스트 페이지
 *
 * @author artherot
 * @version 1.0
 */
class SettleBankController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('policy', 'settle', 'bank');

            $order = \App::load('\\Component\\Order\\OrderAdmin');
            $getData = $order->getBankPolicyList();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            // --- 관리자 디자인 템플릿
            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('sort', $getData['sort']);
            $this->setData('checked', $getData['checked']);
            $this->setData('page', $page);

        } catch (Exception $e) {
            throw $e;
        }
    }
}
