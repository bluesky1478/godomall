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
 * 사은품 증정 설정
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class GiftPresentRegisterController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 모듈 호출
        $getValue = Request::get()->toArray();

        // --- 메뉴 설정
        if ($getValue['sno'] > 0) {
            $this->callMenu('goods', 'gift', 'presentModify');
        } else {
            $this->callMenu('goods', 'gift', 'presentRegister');
        }

        // --- 사은품 설정
        $conf['gift'] = gd_policy('goods.gift');

        // --- 모듈 설정
        $gift = \App::load('\\Component\\Gift\\GiftAdmin');

        $group = array('all' => __('전체(회원+비회원)'), 'member' => __('회원전용(비회원제외)'), 'group' => __('특정 회원등급'));

        // --- 사은품 증정 데이터
        try {
                $data = $gift->getDataGiftPresent(gd_isset($getValue['sno']));

                $scmAdmin = \App::load('\\Component\\Scm\\ScmAdmin');
                $tmpData = $scmAdmin->getScmInfo($data['data']['scmNo'], 'companyNm');
                $data['data']['scmNoNm'] = $tmpData['companyNm'];

                // --- 관리자 디자인 템플릿
                if (Request::get()->get('popupMode')) {
                    $this->getView()->setDefine('layout', 'layout_blank.php');
                }

                $this->addScript([
                    'jquery/jquery.multi_select_box.js',
                ]);

                $this->setData('group', $group);
                $this->setData('conf', $conf);
                $this->setData('data', gd_htmlspecialchars($data['data']));
                $this->setData('checked', $data['checked']);


                // 공급사와 동일한 페이지 사용
                $this->getView()->setPageName('goods/gift_present_register.php');

        } catch (\Exception $e) {
            throw $e;
        }

    }
}
