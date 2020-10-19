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
namespace Bundle\Controller\Admin\Share;


use Request;
use Globals;


class CategorySelectJsonController  extends \Controller\Admin\Controller
{

    /**
     * 상품 QNA 쓰기
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     * @throws Except
     */
    public function index()
    {
        //--- 각 배열을 trim 처리
        $postValue = Request::post()->toArray();

        //--- 각 배열을 trim 처리
        $getValue = Request::get()->toArray();

        $gGlobal = Globals::get('gGlobal');
        $useMallList = array_combine(array_column($gGlobal['useMallList'], 'sno'), $gGlobal['useMallList']);

        if (gd_isset($postValue['mode']) == 'next_select' && gd_isset($postValue['value'])) {


            //--- 카테고리 타입에 따른 설정 (상품,브랜드)
            gd_isset($getValue['cateType'], 'goods');
            if ($getValue['cateType'] == 'goods') {
                $obj_name	= 'Category';
                $cateLength	= DEFAULT_LENGTH_BRAND;
            } else if ($getValue['cateType'] == 'brand') {
                $obj_name	= 'BrandCategory';
                $cateLength	= DEFAULT_LENGTH_CATE;
            }

            //--- 카테고리 class
            $cate	= \App::load('\\Component\\Category\\Category', $getValue['cateType']);

            // 사용자 모드 여부
            if (gd_isset($getValue['userMode']) == 'y') {
                $userWhere	= ' AND cateDisplayFl = \'y\'';
            }

            //--- 카테고리 정보
            $data	= $cate->getCategoryData(null,$postValue['value'],'cateCd,cateNm,mallDisplay','divisionFl = "n" AND length(cateCd) = \''.(strlen($postValue['value']) + $cateLength).'\''.gd_isset($userWhere),'cateSort asc');
            $i		= 0;
            $tmp	= array();
            foreach ($data as $key => $val) {

                foreach(explode(",",$val['mallDisplay']) as $k1 => $v1) {
                    if($useMallList[$v1]) {
                        $mallSno[$k1] = $useMallList[$v1]['domainFl'];
                        $mallName[$k1] = $useMallList[$v1]['mallName'];
                    }
                }
                $tmp[$i]['optionValue']	= $val['cateCd'];
                $tmp[$i]['optionText']	= $val['cateNm'];
                $tmp[$i]['mallName']	= implode(",",$mallName);
                $tmp[$i]['flag']	= implode(",",$mallSno);
                $i++;
            }
            if (!empty($tmp)) {
                echo json_encode($tmp);
            }
        }
        exit;
    }
}

