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

class LayerGoodsOptionRegisterController extends \Controller\Admin\Controller
{

    /**
     * 자주쓰는 상품 옵션 레이어 등록 페이지
     * [관리자 모드] 자주쓰는 상품 옵션 레이어 등록 페이지
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

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $scmNo = Request::post()->get('scmNo');
        gd_isset($scmNo, DEFAULT_CODE_SCMNO);
        $this->setData('scmNo', $scmNo);


        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('goods/layer_goods_option_register.php');

    }
}
