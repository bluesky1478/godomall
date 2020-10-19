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
namespace Bundle\Controller\Admin\Share;

use Globals;
use Request;

class LayerTermsViewController extends \Controller\Admin\Controller
{

    /**
     * {@inheritdoc}
     */
    public function index()
    {
        // --- 모듈 호출
        $postValue = Request::post()->toArray();

        $giftAdmin = \App::load('\\Component\\Gift\\GiftAdmin');

        $data = $giftAdmin->setGiftPresentTerms($postValue['mode'],$postValue['sno']);

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');

        $this->setData('data', $data);
        $this->setData('mode', $postValue['mode']);

        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('share/layer_terms_view.php');
    }
}
