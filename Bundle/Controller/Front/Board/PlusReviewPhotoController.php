<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Front\Board;


use Bundle\Component\PlusShop\PlusReview\PlusReviewArticleFront;

class PlusReviewPhotoController extends \Controller\Front\Controller
{
    public function index()
    {
        if(\SESSION::get(SESSION_GLOBAL_MALL)){
            $this->redirect('../main/index.php');
        }

        $req = \Request::get()->all();
        gd_isset($req['page'], 1);
        $this->addScript(['jquery/pinterest-grid/pinterest_grid.js']);
        $this->addCss(['gd_plus_review.css']);
        $plusReviewArticle = new PlusReviewArticleFront();
        if($plusReviewArticle->getConfig('useFl') === 'n'){
            return true;
        }

        $this->addScript([
            'gd_multi_select_box.js',
            'jquery/validation/jquery.validate.js'
        ]);

        $cate = \App::load('\\Component\\Category\\Category');
        $cate->setCateDepth(2);
        $cateDisplay = $cate->getMultiCategoryBox(null,$req['cateGoods'],'addDiv',true);
        $cate->setCateDepth(DEFAULT_DEPTH_CATE);
        $this->setData('cateDisplay', gd_isset($cateDisplay));

        $data = $plusReviewArticle->getListPhotoByGoodsNo(null,$req['page'], null, null, $req);

        $this->setData('data',$data);
        $this->setData('isSkinDivison',gd_is_skin_division());
        $this->setData('req',$req);
    }
}
