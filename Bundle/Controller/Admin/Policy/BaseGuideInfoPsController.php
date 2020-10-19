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


use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Exception;
use Framework\Debug\Exception\LayerException;
use Logger;
use Message;
use Request;

class BaseGuideInfoPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        Logger::info(__METHOD__);
        $buyerInform = new BuyerInform();

        try {
            switch (Request::post()->get('mode', '')) {
                case BuyerInformCode::BASE_GUIDE:
                    // --- 이용약관&탈퇴안내
                    $mallSno = gd_isset(Request::post()->get('mallSno'), 1);
                    $buyerInform->saveInformData(BuyerInformCode::BASE_GUIDE, Request::post()->get('baseGuideContent'), $mallSno);
                    $buyerInform->saveInformData(BuyerInformCode::HACK_OUT_GUIDE, Request::post()->get('hackOutGuideContent'), $mallSno);
                    $this->json("저장하였습니다.");
                    break;
                default:
                    throw new Exception(__('요청을 처리할 페이지를 찾을 수 없습니다.'), 404);
                    break;
            }
        } catch (Exception $e) {
            if (Request::isAjax()) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new LayerException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}
