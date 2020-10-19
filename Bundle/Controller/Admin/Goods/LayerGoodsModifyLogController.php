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
use Globals;
use Request;

class LayerGoodsModifyLogController extends \Controller\Admin\Controller
{
    public function index()
    {

        // --- 모듈 호출
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        $modeList = [
            'category'    => __('카테고리'),
            'addInfo'   => __('추가정보'),
            'option'  => __('옵션'),
            'optionText'  => __('텍스트옵션'),
            'goods'  => __('상품정보'),
            'image'  => __('이미지'),
        ];

        // --- 상품 데이터
        try {
            // 본사 관리자 상품 수정 로그
            $getData = $goods->getAdminListGoodsLog(Request::post()->get('goodsNo'), true);
        } catch (Exception $e) {
            throw $e;
        }

        // --- 관리자 디자인 템플릿

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData('data', $getData);
        $this->setData('regDt', Request::post()->get('regDt'));
        $this->setData('modeList', $modeList);
    }
}
