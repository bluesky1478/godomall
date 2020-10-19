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
namespace Bundle\Controller\Admin\Scm;

use Exception;
use Framework\Debug\Exception\AlertBackException;
use Request;

class ScmAdjustAfterDeliveryController extends \Controller\Admin\Controller
{
    /**
     * 공급사 상품 승인 관리
     * [관리자 모드] 공급사 상품 승인 관리리스트
     *
     * @author su
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     */

    /**
     * @var 기본 주문상태
     */
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('scm', 'adjustAfter', 'scmAdjustAfterDelivery');
            $this->addScript(
                [
                    'jquery/jquery.multi_select_box.js',
                ]
            );

            // --- 모듈 호출
            $adjust = \App::load('\\Component\\Scm\\ScmAdjust');

            // -- _GET 값
            $getValue = Request::get()->toArray();

            // 주문출력 범위 설정
            $getValue['statusMode'] = array('r3', 'e5'); // 환불완료 단계, 교환완료 단계

            // --- 리스트 설정
            $getData = $adjust->getScmAdjustAfterDeliveryList($getValue);
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);
            $this->setData('data', gd_isset($getData['data']));

            // 페이지 설정
            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($getData['data']));
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
