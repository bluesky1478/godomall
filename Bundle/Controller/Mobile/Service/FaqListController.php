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

namespace Bundle\Controller\Mobile\Service;
use Component\Faq\FaqAdmin;
use Component\Faq\Faq;
use Request;

class FaqListController extends \Controller\Mobile\Controller
{
    public function index()
    {
        try {
            $req = Request::get()->toArray();
            $faq = new Faq();

            if($req['mode'] == 'getAnswer') {
                $data = $faq->getFaqView($req['sno']);
                $this->json([questionContents =>$data['data']['contents'] ,answerContents => $data['data']['answer'] ]);
            }

            foreach($req as $key => $val) {
                $req[$key] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
            }

            $req['isBest'] = 'y';
            if($req['category'] || $req['searchWord'])  {
                $req['isBest'] = 'n';
            }

            $getData = $faq->getFaqList($req);
            $mallSno = \SESSION::get(SESSION_GLOBAL_MALL)['sno'] ? \SESSION::get(SESSION_GLOBAL_MALL)['sno'] : DEFAULT_MALL_NUMBER;
            $faqCode['00'] = '전체';
            $faqCode = array_merge($faqCode, gd_code('03001',$mallSno));
            $this->setData('req',$req);
            $this->setData('faqList',$getData);
            $this->setData('gPageName', 'FAQ');
            $this->setData('faqCode',$faqCode);

            if(\Request::isAjax()){
                $this->json($getData['data']);
            }

        }
        catch(\Exception $e) {
            $this->alert($e->getMessage());
        }
    }
}
