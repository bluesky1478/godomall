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
use Framework\Utility\UrlUtils;

class GoodsDetailInfoGlobalRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'goods', 'infoReg');

        // --- 모듈 설정
        $inform = \App::load('\\Component\\Agreement\\BuyerInform');

        // --- 페이지 데이터
        try {
            $mall = new Mall();
            $mallList = $mall->getListByUseMall();
            unset($mallList[DEFAULT_MALL_NUMBER]);

            $mallSno = gd_isset(\Request::get()->get('mallSno'), array_keys($mallList)[0]);
            $data = [];
            foreach (['2', '3', '4', '5'] as $value) {
                $getData = $inform->getGoodsInfo(gd_isset(\Request::get()->get('informCd'), sprintf('%03d', $value) . '001'), $mallSno);
                $data['informNm'][sprintf('%03d', $value)] = $getData['data']['informNm'];
                $data['content'][sprintf('%03d', $value)] = $getData['data']['content'];
            }

            if (count($mallList) >= 1) {
                $this->setData('mallCnt', count($mallList));
                $this->setData('mallList', $mallList);
                $this->setData('mallSno', $mallSno);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', gd_htmlspecialchars($data));
        $this->setData('checked', $data['checked']);
        $this->setData('adminList', UrlUtils::getAdminListUrl('.goods_detail_info.php'));
    }
}
