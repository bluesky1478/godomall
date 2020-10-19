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

use Component\Mall\Mall;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Logger;

class BaseGuideInfoController extends \Controller\Admin\Controller
{
    public function index()
    {
        Logger::info(__METHOD__);
        $this->callMenu('policy', 'basic', 'guideInfo');

        $mall = new Mall();
        $mallSno = gd_isset(\Request::get()->get('mallSno'), 1);

        $mallList = $mall->getListByUseMall();
        if (count($mallList) > 1) {
            $this->setData('mallCnt', count($mallList));
            $this->setData('mallList', $mallList);
            $this->setData('mallSno', $mallSno);
        }

        $buyerInform = new BuyerInform();
        $baseGuide = $buyerInform->getInformData(BuyerInformCode::BASE_GUIDE, $mallSno);
        $hackOutGuide = $buyerInform->getInformData(BuyerInformCode::HACK_OUT_GUIDE, $mallSno);

        $this->setData('mode', BuyerInformCode::BASE_GUIDE);
        $this->setData('baseGuideContent', gd_isset($baseGuide['content'], ''));
        $this->setData('hackOutGuideContent', gd_isset($hackOutGuide['content'], ''));

        //--- 회원 공통 스크립트
        $this->addScript(['member.js']);
    }
}
