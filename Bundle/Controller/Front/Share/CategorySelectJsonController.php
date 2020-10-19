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
namespace Bundle\Controller\Front\Share;


use Request;


class CategorySelectJsonController   extends \Controller\Front\SimpleController
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
            $cate	= \App::load('\\Component\\Category\\Category',$getValue['cateType']);

            // 사용자 모드 여부
            if (gd_isset($getValue['userMode']) == 'y') {
                $userWhere	= ' AND cateDisplayFl = \'y\'';
            }

            //현재 그룹 정보
            $myGroup = \Session::get('member.groupSno');

            //--- 카테고리 정보
            $data	= $cate->getCategoryData(null,$postValue['value'],'cateCd,cateNm,catePermission,catePermissionGroup,cateOnlyAdultFl','divisionFl = "n" AND length(cateCd) = \''.(strlen($postValue['value']) + $cateLength).'\''.gd_isset($userWhere),'cateSort asc');
            $i		= 0;
            $tmp	= array();
            foreach ($data as $key => $val) {

                $disabledFl = false;
                if ($val['cateOnlyAdultFl'] =='y' && gd_check_adult() === false) {
                    $disabledFl = true;
                }

                // 현재 카테고리 권한 체크
                if ($val['catePermission'] > 0) {
                    if (gd_is_login() === false) {
                        $disabledFl = true;
                    }

                    if($val['catePermission'] =='2' && $val['catePermissionGroup'] && !in_array( $myGroup,explode(INT_DIVISION,$val['catePermissionGroup']))) {
                        $disabledFl = true;
                    }
                }
                $disabledStr = "";
                if( $disabledFl) {
                    $disabledStr = "disabled='disabled'";
                }

                $tmp[$i]['optionValue']	= $val['cateCd'];
                $tmp[$i]['optionText']	= $val['cateNm'];
                $tmp[$i]['disabledStr']	= $disabledStr;
                $i++;
            }
            if (!empty($tmp)) {
                echo json_encode($tmp);
            }
        }
        exit;
    }
}

