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

namespace Bundle\Widget\Mobile\Board;

use Component\PlusShop\PlusReview\PlusReviewArticleFront;

class PlusReviewArticleWidget extends \Widget\Mobile\Widget
{
    public function index()
    {
        $plusReviewArticle = new PlusReviewArticleFront();
        $template = $this->getData('template');
        $req = [
            'template'=> $template,
            'rows'=> $this->getData('rows'),
        ];
        $params = [
            'pageNum' => $req['rows'],
            'reviewType' => 'article'
        ];
        $data = $plusReviewArticle->getList($params, true, false);
        $this->setData('template', $template);
        $this->setData('data', $data);
        $this->setData('req', $req);
    }
}
