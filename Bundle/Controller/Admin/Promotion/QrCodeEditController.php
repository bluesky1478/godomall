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
 * Class QrCodeEditController
 * @package Bundle\Controller\Admin\Promotion
 * @author  yjwee
 */
class QrCodeEditController extends \Controller\Admin\Controller
{

    /**
     * QrCodeEditController
     *
     * @author yjwee
     */
    public function index()
    {
        /** @var \Bundle\Component\Promotion\QrCode $qr */
        $qr = \App::load('\\Component\\Promotion\\QrCode');

        // --- 페이지 데이터
        $requestGetParams = Request::get()->toArray();

        $result = $qr->getContent($requestGetParams);

        // --- 조회 결과 값 검증 및 초기화
        if (empty($result['qrSize'])) {
            $result['qrSize'] = '3';
        }
        if (empty($result['qrVersion'])) {
            $result['qrVersion'] = '5';
        }
        if (!empty($result['qrString'])) {
            if (substr($result['qrString'], 0, 6) == 'MECARD') {
                $result['useType'] = 'MECARD';
                $nameCard = str_replace("MECARD:", "", $result['qrString']);
                $nameCardArray = explode(';', $nameCard);
                foreach ($nameCardArray as $object) {
                    $objectArray = explode(':', $object);
                    $result[$objectArray[0]] = $objectArray[1];
                }
            } else {
                $result['useType'] = 'url';
                $result['contentText'] = $result['qrString'];
            }
        }

        $result['qrSizeHtml'] = gd_select_box('qrSize', 'qrSize', array_combine(range(1, 8), range(1, 8)), null, $result['qrSize']);
        $result['qrVersionHtml'] = gd_select_box('qrVersion', 'qrVersion', array_combine(range(1, 12), range(1, 12)), null, $result['qrVersion']);

        // --- 메뉴 설정
        if ($result['sno'] > 0) {
            $this->callMenu('promotion', 'qrCode', 'qrCodeModify');
        } else {
            $this->callMenu('promotion', 'qrCode', 'qrCodeEdit');
        }

        // --- 관리자 디자인 템플릿
        $this->setData('requestGetParams', $requestGetParams);
        $this->setData('data', $result);
        $this->setData('qrCode', $qr);
    }
}
