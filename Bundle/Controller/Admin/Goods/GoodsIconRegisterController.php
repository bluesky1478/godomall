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

use Exception;
use Request;

class GoodsIconRegisterController extends \Controller\Admin\Controller
{
    /**
     * 상품 아이콘 등록 / 수정 페이지
     * [관리자 모드] 상품 아이콘 관리 등록 / 수정 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     */
    public function index()
    {

        // --- 모듈 호출
        $getValue = Request::get()->toArray();
        $getValue['iconType'] = htmlspecialchars($getValue['iconType'], ENT_QUOTES, 'UTF-8');

        // --- 메뉴 설정
        if ($getValue['sno'] > 0) {
            $this->callMenu('goods', 'goods', 'icon_modify');
        } else {
            $this->callMenu('goods', 'goods', 'icon_reg');
        }

        // --- 모듈 설정
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        // --- 상품 아이콘 데이터
        try {

            if (isset($getValue['mode']) === false) {
                $data = $goods->getDataManageGoodsIcon(gd_isset($getValue['sno']));
            } else {
                gd_isset($getValue['iconType'], 'mileage');
                if (isset($goods->etcIcon[$getValue['iconType']]) === true) {
                    $iconCheck = true;
                    $iconNm = $goods->etcIcon[$getValue['iconType']];
                } else {
                    $iconCheck = false;
                    $iconNm = '';
                }
            }

        } catch (Exception $e) {
            throw $e;
        }

        // --- 관리자 디자인 템플릿


        if (isset($getValue['mode']) === false) {
            $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());
        } else {
            $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/goods_icon_etc.php');
        }

        // --- 관리자 디자인 템플릿
        if (isset($getValue['popupMode']) === true) {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }

        if (isset($getValue['mode']) === false) {
            $this->setData('data', gd_htmlspecialchars($data['data']));
            $this->setData('checked', $data['checked']);
        } else {
            $this->setData('iconType', $getValue['iconType']);
            $this->setData('iconNm', $iconNm);
            $this->setData('iconCheck', $iconCheck);
        }
    }
}
