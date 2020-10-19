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
namespace Bundle\Controller\Admin\Policy;

use Component\Godo\MyGodoSmsServerApi;
use Request;
use Exception;

/**
 * seo태그 기타 페이지 추가
 *
 * @package Bundle\Controller\Admin\Policy
 * @author  atomyang
 */
class LayerSeoTagRegisterController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            $getValue =\Request::get()->toArray();
            // 은행정보
            $seoTag = \App::load('\\Component\\Policy\\SeoTag');
            $getData = $seoTag->getSeoTagView(Request::request()->get('sno'));

            $seoConfig['tag'] = $seoTag->seoConfig['tag'];

            //몰도메인
            $mallSno = gd_isset($getValue['mallSno'], 1);
            $mallInfo = gd_policy('basic.info',$mallSno);

            // 템플릿 변수 설정
            $this->setData('data', $getData['data']);
            $this->setData('seoConfig',$seoConfig);
            $this->setData('page',$getValue['page']);
            $this->setData('targetDiv',$getValue['targetDiv']);
            $this->setData('mallDomain',$mallInfo['mallDomain']);
            $this->setData('checked', $getData['checked']);

            // --- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');


        } catch (Exception $e) {
            throw $e;
        }
    }
}
