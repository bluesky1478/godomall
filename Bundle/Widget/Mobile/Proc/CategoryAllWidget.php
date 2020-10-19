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
namespace Bundle\Widget\Mobile\Proc;

use App;

/**
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Front\Outline
 * @author  Young Eun Jung <atomyang@godo.co.kr>
 */

class CategoryAllWidget extends \Widget\Mobile\Widget
{

    public function index()
    {

        if($this->getData('cateType') =='brand') {
            $category = App::load(\Component\Category\Brand::class);// 싱글턴 객체 생성 (중복호출 되지 않도록)
            $this->setData('cateCd', 'brandCd');
        } else {
            $category = App::load(\Component\Category\Category::class);// 싱글턴 객체 생성 (중복호출 되지 않도록)
            $this->setData('cateCd', 'cateCd');
        }

        $cateDepth = gd_isset($this->getData('cateDepth'),4);
        $getData = $category->getCategoryCodeInfo(null, $cateDepth,true,false,'mobile');

        $this->setData('data', $getData);

    }
}
