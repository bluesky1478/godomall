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

use Request;

/**
 * 상품 이미지 사이즈 설정 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class GoodsImagesController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'goods', 'image');

        // --- 상품 이미지 설정 config 불러오기
        $data['config'] = gd_policy('goods.image');
        $types = empty($data['config']['imageType']) ? 'auto' : $data['config']['imageType'];
        $imageType[$types] = 'checked="checked"'; // 설정방법선택
        $data['title_base'] = array(
            array('id' => 'magnify', 'name' => __('확대 이미지'), 'addKey' => 'y', 'addNo' => ''),
            array('id' => 'detail', 'name' => __('상세 이미지'), 'addKey' => 'y', 'addNo' => ''),
            array('id' => 'list', 'name' => __('썸네일 이미지'), 'addKey' => 'n', 'addNo' => '')
        );

        $data['title_add'] = array(
            array('id' => 'add', 'name' => __('추가 이미지'), 'addKey' => 'n', 'addNo' => '1'),
            array('id' => 'add', 'name' => __('추가 이미지'), 'addKey' => 'n', 'addNo' => '2'),
            array('id' => 'add', 'name' => __('추가 이미지'), 'addKey' => 'n', 'addNo' => '3'),
            array('id' => 'add', 'name' => __('추가 이미지'), 'addKey' => 'n', 'addNo' => '4'),
            array('id' => 'add', 'name' => __('추가 이미지'), 'addKey' => 'n', 'addNo' => '5')
        );
        $data['fieldID'] = 'imageAdd_';
        $data['idTitle'] = __('추가 이미지');

        // --- 관리자 디자인 템플릿
        $this->setData('data', $data);
        $this->setData('imageType', $imageType);
    }
}
