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

namespace Bundle\Controller\Front\Mypage;


use Framework\Debug\Exception\RedirectLoginException;

class MypagePlusReviewArticleController extends \Controller\Front\Board\PlusReviewArticleController
{
    public function index()
    {
        if (gd_is_login() == false) {
            throw new RedirectLoginException();
        }

        $this->setData('isMypage', 'y');
        parent::index();
        $this->setData('includePlusReviewFile', 'board/plus_review_article.html');
    }
}
