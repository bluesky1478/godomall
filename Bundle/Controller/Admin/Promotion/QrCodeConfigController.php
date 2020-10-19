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

use Request;
use Logger;

/**
 * Class QrCodeConfigController
 * @package Bundle\Controller\Admin\Promotion
 * @author  yjwee
 */
class QrCodeConfigController extends \Controller\Admin\Controller
{

    /**
     * QrCodeConfigController
     *
     * @author yjwee
     */
    public function index()
    {
        \Logger::info(__METHOD__);
        // --- 메뉴 설정
        $this->callMenu('promotion', 'qrCode', 'qrCodeConfig');

        // --- 페이지 데이터
        $requestData = array_merge(Request::get()->toArray(), Request::post()->toArray());

        // --- QR코드 설정 조회
        $result = gd_policy('promotion.qrcode');
        \Logger::debug(__METHOD__, $result);

        // --- 관리자 디자인 템플릿
        $this->setData('requestData', $requestData);
        $this->setData('data', $result);
    }
}
