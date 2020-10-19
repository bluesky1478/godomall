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
namespace Bundle\Controller\Admin\Goods;

use Globals;
use Request;

/**
 * 사은품 등록 / 수정
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class GiftRegisterController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        if (Request::get()->has('giftNo')) {
            $this->callMenu('goods', 'gift', 'giftModify');
        } else {
            $this->callMenu('goods', 'gift', 'giftRegister');
        }

        // --- 사은품 설정
        $conf['gift'] = gd_policy('goods.gift');
        $tmp = gd_policy('basic.storage');
        foreach ($tmp['httpUrl'] as $key => $val) {
            $conf['storage'][$val] = $tmp['storageName'][$key];
        }
        unset($tmp);

        // --- 모듈 설정
        $gift = \App::load('\\Component\\Gift\\GiftAdmin');

        // --- 사은품 데이터
        try {
            $data = $gift->getDataGift(Request::get()->get('giftNo'));

            $scmAdmin = \App::load('\\Component\\Scm\\ScmAdmin');
            $tmpData = $scmAdmin->getScmInfo($data['data']['scmNo'], 'companyNm');
            $data['data']['scmNoNm'] = $tmpData['companyNm'];

            if ($data['data']['brandCd'] != '') {
                $brand = \App::load('\\Component\\Category\\CategoryAdmin', 'brand');
                $tmpData = $brand->getCategoryData($data['data']['brandCd'], '', 'cateNm');
                $data['data']['brandCdNm'] = $tmpData[0]['cateNm'];
            } else {
                $data['data']['brandCdNm'] = '';
            }


            // --- 관리자 디자인 템플릿
            if (Request::get()->get('popupMode')) {
                $this->getView()->setDefine('layout', 'layout_blank.php');
            }

            $this->addScript([
                'jquery/jquery.number_only.js',
                'jquery/jquery.multi_select_box.js',
            ]);

            $this->setData('conf', $conf);
            $this->setData('data', gd_htmlspecialchars($data['data']));
            $this->setData('checked', $data['checked']);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('goods/gift_register.php');

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
