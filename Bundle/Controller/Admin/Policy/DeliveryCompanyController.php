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
use Globals;

class DeliveryCompanyController extends \Controller\Admin\Controller
{

    /**
     * 배송 업체 설정 리스트 페이지
     * [관리자 모드] 배송 업체 설정 리스트 페이지
     *
     * @author    artherot
     * @version   1.0
     * @since     1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     *
     * @param array $get
     * @param array $post
     * @param array $files
     *
     * @throws Except
     */
    public function index()
    {
        // --- 모듈 호출

        // --- 메뉴 설정
        $this->callMenu('policy', 'delivery', 'company');
        $this->addScript([
            'bootstrap/bootstrap-table.js',
            'jquery/jquery.tablednd.js',
            'bootstrap/bootstrap-table-reorder-rows.js',
        ]);

        // --- 모듈 호출
        $delivery = \App::load('\\Component\\Delivery\\Delivery');

        // --- 배송 업체 데이터
        try {
            // 배송 업체
            $data = $delivery->getDeliveryCompany();
            $dataCnt = count($data);

        } catch (Exception $e) {
            throw $e;
        }

        $this->setData('data', $data);
        $this->setData('dataCnt', $dataCnt);
    }
}
