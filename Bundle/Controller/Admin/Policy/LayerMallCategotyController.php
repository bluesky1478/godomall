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
namespace Bundle\Controller\Admin\Policy;

/**
 * 대표 카테고리
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class LayerMallCategotyController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // 카테고리 종류 (2107-10-01 이전)
        /*
        $data[] = __('여성의류');
        $data[] = __('가구/인테리어');
        $data[] = __('가전/휴대폰');
        $data[] = __('남자의류');
        $data[] = __('침구/커튼/수예');
        $data[] = __('컴퓨터/주변기기');
        $data[] = __('패션/잡화');
        $data[] = __('생활/주방/문구');
        $data[] = __('스포츠/레져');
        $data[] = __('액세서리/시계');
        $data[] = __('유아동/출산');
        $data[] = __('자동차');
        $data[] = __('속옷');
        $data[] = __('분유/기저귀');
        $data[] = __('취미');
        $data[] = __('화장품/미용');
        $data[] = __('식품/슈퍼마켓');
        $data[] = __('꽃/케이크배달');
        $data[] = __('명품');
        $data[] = __('건강/다이어트');
        $data[] = __('기타');
        */


        // 변경 카테고리 종류 (2107-10-01 이후)
        $data[] = __('여성의류');
        $data[] = __('남성의류');
        $data[] = __('패션잡화');
        $data[] = __('화장품/미용');
        $data[] = __('디지털/가전');
        $data[] = __('가구/인테리어');
        $data[] = __('출산/유아');
        $data[] = __('식품');
        $data[] = __('스포츠/레져');
        $data[] = __('생활/건강/취미');
        $data[] = __('여행/문화/도서');
        $data[] = __('도매/종합몰');
        $data[] = __('구매대행');
        $data[] = __('서비스/컨텐츠');


        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');

        $this->setData('data', $data);
    }
}
