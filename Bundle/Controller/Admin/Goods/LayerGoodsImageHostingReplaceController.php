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

class LayerGoodsImageHostingReplaceController extends \Controller\Admin\Controller
{

    /**
     * 이미지호스팅 전환하기 ftp 정보 레이어
     *
     * @author jwno
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @throws Except
     */
    public function index()
    {
        try {
            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

            // 페이지
            $this->getView()->setPageName('goods/layer_goods_image_hosting_replace.php');
        } catch (Exception $e) {
            throw $e;
        }

    }
}
