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
namespace Bundle\Controller\Admin\Scm;

use Exception;
use Globals;
use Request;

class LayerAddGoodsApplyInfoController extends \Controller\Admin\Controller
{

    /**
     * 레이어 상품 리스트 페이지
     * [관리자 모드] 레이어 상품 리스트 페이지
     * 설명 : 상품 정보 수정 페이지에서 상단에 상품 리스트로 나오는 부분
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
        $addGoods = \App::load('\\Component\\Goods\\AddGoodsAdmin');

        $applyFlList = [
            'a'    => __('승인요청'),
            'y'   => __('승인완료'),
            'r'  => __('반려'),
            'n'  => __('철회'),
        ];


        // --- 상품 데이터
        try {

            $getData = $addGoods->getAdminListAddGoodsLog(Request::post()->get('addGoodsNo'));

        } catch (Exception $e) {
            throw $e;
        }


        // --- 관리자 디자인 템플릿

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData('data', $getData);
        $this->setData('applyFlList', $applyFlList);


    }
}
