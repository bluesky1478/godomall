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

class LayerGiftSelectController extends \Controller\Admin\Controller
{

    /**
     * 사은품 증정 등록 - 사은품 선택 페이지
     * [관리자 모드] 사은품 증정 등록 - 사은품 선택 등록 페이지
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
        $gift = \App::load('\\Component\\Gift\\GiftAdmin');
        $brand = \App::load('\\Component\\Category\\CategoryAdmin', 'brand');

        // --- 사은품 데이터
        try {

            $getData = $gift->getAdminListGift('layer', 10);
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

        } catch (Exception $e) {
            throw $e;
        }

        // --- 관리자 디자인 템플릿

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());


        $this->setData('condition', Request::get()->get('condition'));
        $this->setData('data', $getData['data']);
        $this->setData('brand', $brand);
        $this->setData('search', $getData['search']);
        $this->setData('checked', $getData['checked']);
        $this->setData('page', $page);
    }
}
