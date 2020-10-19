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
namespace Bundle\Controller\Admin\Promotion;

use Framework\StaticProxy\Proxy\Request;

/**
 * Class QrCodeListController
 * @package Bundle\Controller\Admin\Promotion
 * @author  yjwee
 */
class QrCodeListController extends \Controller\Admin\Controller
{

    /**
     * QrCodeListController
     *
     * @author yjwee
     */
    public function index()
    {
        // --- 모듈 호출
        $qr = \App::load('\\Component\\Promotion\\QrCode');

        // --- 메뉴 설정
        $this->callMenu('promotion', 'qrCode', 'qrCodeList');

        // --- 페이지 데이터
        $requestData = array_merge(Request::get()->toArray(), Request::post()->toArray());
        $result = $qr->lists($requestData);

        // --- 관리자 디자인 템플릿
        $this->setData('requestData', $requestData);
        $this->setData('data', $result['data']);
        $this->setData('page', $result['page']);
    }
}
