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

namespace Bundle\Controller\Front\Board;
use Component\Faq\FaqAdmin;
use Component\Faq\Faq;
use Request;

class FaqListController extends \Controller\Front\Controller
{
    public function index()
    {
        try {
            $req = Request::get()->toArray();
            $mallSno = \SESSION::get(SESSION_GLOBAL_MALL)['sno'] ? \SESSION::get(SESSION_GLOBAL_MALL)['sno'] : DEFAULT_MALL_NUMBER;
            if(Request::post()->get('mode') == 'getAnswer') {
                $faqAdmin = new FaqAdmin();
                $data = $faqAdmin->getFaqView(Request::post()->get('sno'));
                echo $this->json([questionContents =>$data['data']['contents'] ,answerContents => $data['data']['answer'] ]);
                exit;
            }

            $faq = new Faq();
            $getData = $faq->getFaqList($req);
            $faqCode = gd_code('03001',$mallSno);

            $this->setData('req',$req);
            $this->setData('faqList',$getData);
            $this->setData('faqCode',$faqCode);
        }
        catch(\Exception $e) {
            $this->alert($e->getMessage());
        }
    }
}
