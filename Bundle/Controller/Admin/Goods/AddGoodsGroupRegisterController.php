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
 * 자주쓰는 추가상품 관리 등록
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class AddGoodsGroupRegisterController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {

        $addGoods = \App::load('\\Component\\Goods\\AddGoodsAdmin');

        // --- 메뉴 설정
        if (Request::get()->has('sno')) {
            $this->callMenu('goods', 'addGoods', 'addGoodsGroupModify');
        } else {
            $this->callMenu('goods', 'addGoods', 'addGoodsGroupRegister');
        }

        // --- 자주쓰는 추가상품 그룹 관리
        try {
            $data = $addGoods->getDataAddGoodsGroup(Request::get()->get('sno'));

            if ($data['data']['scmNo'] != DEFAULT_CODE_SCMNO) {
                $scmAdmin = \App::load('\\Component\\Scm\\ScmAdmin');
                $tmpData = $scmAdmin->getScmInfo($data['data']['scmNo'], 'companyNm');
                $data['data']['scmNoNm'] = $tmpData['companyNm'];
            }

            // --- 관리자 디자인 템플릿
            if (Request::get()->get('popupMode')) {
                $this->getView()->setDefine('layout', 'layout_blank.php');
            }

            $this->addScript([
                'jquery/jquery.multi_select_box.js',
            ]);

            $this->setData('data', $data['data']);
            $this->setData('addGoodsList', $data['addGoodsList']);
            $this->setData('checked', $data['checked']);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('goods/add_goods_group_register.php');

        } catch (\Exception $e) {
            throw $e;
        }

    }
}
