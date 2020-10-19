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
namespace Bundle\Controller\Admin\Board;

use Component\PlusShop\PlusReview\PlusReviewArticleAdmin;
use Request;

class PlusPreviewController extends \Controller\Admin\Controller
{

    public function index()
    {

        $sno = Request::get()->get('sno');
        $plusReviewArticle = new PlusReviewArticleAdmin();
        $data = $plusReviewArticle->get($sno);
        $this->setData('formCheckMinLength', $plusReviewArticle->getConfig('formCheckMinLength'));
        $this->setData('data', $data);
        $this->getView()->setDefine('layout', 'layout_layer.php');

    }
}
