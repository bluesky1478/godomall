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

class GoodsSelectJsonController extends \Controller\Admin\Controller
{
    /**
     * 다중 카테고리 JSON 데이타 출력 페이지
     * [관리자 모드] 다중 카테고리 JSON 데이타 출력 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     */
    public function index()
    {

        $getValue = Request::get()->toArray();
        $postValue = Request::post()->toArray();

        if (gd_isset($postValue['mode']) == 'next_select' && gd_isset($postValue['value'])) {
            // --- 카테고리 타입에 따른 설정 (상품,브랜드)
            gd_isset($getValue['cateType'], 'goods');
            if ($getValue['cateType'] == 'goods') {
                $objName = 'CategoryAdmin';
                $cateLength = DEFAULT_LENGTH_BRAND;
            } elseif ($getValue['cateType'] == 'brand') {
                $objName = 'BrandCategoryAdmin';
                $cateLength = DEFAULT_LENGTH_CATE;
            }

            // --- 카테고리 class
            $cate = \App::load($objName, 'CategoryAdmin', $getValue['cateType']);

            // --- 카테고리 정보
            $data = $cate->getCategoryData(null, $postValue['value'], 'cateCd,cateNm', 'length(cateCd) = \'' . (strlen($postValue['value']) + $cateLength) . '\'');
            $i = 0;
            $tmp = array();
            foreach ($data as $key => $val) {
                $tmp[$i]['optionValue'] = $val['cateCd'];
                $tmp[$i]['optionText'] = $val['cateNm'];
                $i++;
            }
            if (!empty($tmp)) {
                echo json_encode($tmp);
            }
        }
    }
}
