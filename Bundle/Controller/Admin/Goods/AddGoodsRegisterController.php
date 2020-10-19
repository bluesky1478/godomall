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

use Request;

/**
 * 추가상품 등록
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class AddGoodsRegisterController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {

        $addGoods = \App::load('\\Component\\Goods\\AddGoodsAdmin');
        $getValue = Request::get()->toArray();
        $addGroup = htmlspecialchars(Request::get()->get('addGroup'), ENT_QUOTES, 'UTF-8');

        // --- 메뉴 설정
        if (Request::get()->has('addGoodsNo')) {
            $this->callMenu('goods', 'addGoods', 'addGoodsModify');
        } else {
            $this->callMenu('goods', 'addGoods', 'addGoodsRegister');
        }

        $tmp = gd_policy('basic.storage'); // 저장소 설정
        $defaultImageStorage = '';
        foreach ($tmp['storageDefault'] as $index => $item) {
            if (in_array('goods', $item)) {
                if (is_null($getValue['addGoodsNo'])) {
                    $defaultImageStorage = $tmp['httpUrl'][$index];
                }
            }
        }
        foreach ($tmp['httpUrl'] as $key => $val) {
            $conf['storage'][$val] = $tmp['storageName'][$key];
        }

        // --- 추가상품 데이터
        try {
            $data = $addGoods->getDataAddGoods(Request::get()->get('addGoodsNo'));

            $scmAdmin = \App::load('\\Component\\Scm\\ScmAdmin');
            $tmpData = $scmAdmin->getScmInfo($data['data']['scmNo'], 'companyNm');
            $data['data']['scmNoNm'] = $tmpData['companyNm'];

            $conf['tax'] = gd_policy('goods.tax'); // 과세/비과세 설정

            if ($data['data']['brandCd'] != '') {
                $brand = \App::load('\\Component\\Category\\CategoryAdmin', 'brand');
                $tmpData = $brand->getCategoryData($data['data']['brandCd'], '', 'cateNm');
                $data['data']['brandCdNm'] = $tmpData[0]['cateNm'];
            } else {
                $data['data']['brandCdNm'] = '';
            }


            // --- 관리자 디자인 템플릿
            if (isset($getValue['popupMode']) === true) {
                $this->getView()->setDefine('layout', 'layout_blank.php');
            }

            $this->addScript([
                'jquery/jquery.multi_select_box.js',
            ]);

            if (empty($defaultImageStorage) === false) {
                $data['data']['imageStorage'] = $defaultImageStorage;
            }

            $this->setData('data', gd_htmlspecialchars($data['data']));
            $this->setData('addGroup', $addGroup);
            $this->setData('checked', $data['checked']);
            $this->setData('conf', $conf);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('goods/add_goods_register.php');


        } catch (\Exception $e) {
            throw $e;
        }

    }
}
