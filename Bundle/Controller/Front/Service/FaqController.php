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

namespace Bundle\Controller\Front\Service;
use Component\Faq\FaqAdmin;
use Component\Faq\Faq;
use Request;

class FaqController extends \Controller\Front\Controller
{
    public function index()
    {
        try {
            $req = Request::get()->toArray();
            foreach($req as $key => $val) {
                $req[$key] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
            }

            if(Request::post()->get('mode') == 'getAnswer') {
                $faqAdmin = new FaqAdmin();
                $data = $faqAdmin->getFaqView(Request::post()->get('sno'));
                echo $this->json([questionContents =>$data['data']['contents'] ,answerContents => $data['data']['answer'] ]);
                exit;
            }

            $faq = new Faq();
            $getData = $faq->getFaqList($req);

            $mallSno = \SESSION::get(SESSION_GLOBAL_MALL)['sno'] ? \SESSION::get(SESSION_GLOBAL_MALL)['sno'] : DEFAULT_MALL_NUMBER;
            $faqCode = gd_code('03001',$mallSno);

            if(gd_isset($req['noheader']) == 'y') {
                $this->getView()->setDefine('header', 'outline/_share_header.html');
                $this->getView()->setDefine('footer', 'outline/_share_footer.html');
                $this->setData('title','BEST FAQ');
            }
            else {
                $this->setData('title','FAQ');
            }

            $this->setData('req',$req);
            $this->setData('faqList',$getData);
            $this->setData('faqCode',$faqCode);
        }
        catch(\Exception $e) {
            $this->alert($e->getMessage());
        }
    }
}
