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
namespace Bundle\Widget\Front\Proc;

use App;
use Request;

/**
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Front\Outline
 * @author  Young Eun Jung <atomyang@godo.co.kr>
 */

class CategorySideWidget extends \Widget\Front\Widget
{

    public function index()
    {
        if($this->getData('cateType') =='brand') {
            $category = App::load(\Component\Category\Brand::class);// 싱글턴 객체 생성 (중복호출 되지 않도록)
            $cateCd = Request::get()->has('brandCd') ? Request::get()->get('brandCd') : null;
            $this->setData('cateType', 'brand');
        } else {
            $category = App::load(\Component\Category\Category::class);// 싱글턴 객체 생성 (중복호출 되지 않도록)
            $cateCd = Request::get()->has('cateCd') ? Request::get()->get('cateCd') : null;
            $this->setData('cateType', 'cate');
        }

        $menuType = $this->getData('menuType');
        if(gd_isset($menuType) && $menuType =='all') {
            unset($cateCd);
        }

        $cateDepth = gd_isset($this->getData('cateDepth'),4);
        $getData = $category->getCategoryCodeInfo($cateCd, $cateDepth,true,false,'pc');
        $this->setData('data', $getData);

        if($this->getData('type') =='top') {
            $this->getView()->setPageName('proc/_category_top');
        } else {
            $this->getView()->setPageName('proc/_category_side');
        }

    }
}
