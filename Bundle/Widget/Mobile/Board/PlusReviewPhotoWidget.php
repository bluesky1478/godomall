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

class PlusReviewPhotoWidget extends \Widget\Mobile\Widget
{
    public function index(){
        $req = [
            'cols'=> $this->getData('cols'),
            'rows'=> $this->getData('rows'),
            'thumSizeType'=> $this->getData('thumSizeType'),
            'thumWidth'=> $this->getData('thumWidth'),
        ];

        $plusReviewArticle = new PlusReviewArticleFront();
        $pageNum = $req['cols'] * $req['rows'];
        $data= $plusReviewArticle->getList(['pageNum'=>$pageNum,'reviewType'=>'photo'],false,false);
        $this->setData('data',$data['list']);
        $this->setData('req',$req);
    }
}
