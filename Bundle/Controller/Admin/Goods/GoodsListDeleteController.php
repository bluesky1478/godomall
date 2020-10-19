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
use Component\Category\CategoryAdmin;
use Component\Category\BrandAdmin;
use Globals;
use Request;

/**
 * 상품 리스트 페이지
 */
class GoodsListDeleteController extends \Controller\Admin\Goods\GoodsListController
{
    /**
     * index
     *
     * @throws Except
     */
    public function index()
    {
        Request::get()->set('delFl','y');
        parent::index();
    }
}
