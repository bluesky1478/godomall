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

namespace Bundle\Controller\Mobile\Popup;

use Component\Design\DesignMultiPopup;
use Message;
use Globals;
use Request;
use Exception;

/**
 * 팝업 데이터 처리
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class MultiPopupPsController extends \Controller\Mobile\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $postValue = Request::request()->toArray();

        //--- DesignPopup 정의
        $designMultiPopup = new DesignMultiPopup();

        switch (Request::request()->get('mode')) {
            // 사용 가능한 팝업 체크
            case 'popupOpen':
                try {
                    // 팝업 테이터
                    $getData = $designMultiPopup->getUseMultiPopupData($postValue['currentUrl']);
                    if (empty($getData) === false) {
                        echo json_encode($getData);
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                break;
        }
        exit();
    }
}
