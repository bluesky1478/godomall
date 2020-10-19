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

use Component\Board\BoardList;
use Component\Board\Board;
use Framework\Debug\Exception\AlertBackException;
use Request;
use Component\Faq\Faq;
use Framework\Cache\CacheableProxyFactory;
use Framework\StaticProxy\Proxy\Cache;

class IndexController extends \Controller\Front\Controller
{
    const CACHE_USE = true;
    const CACHE_EXPIRE = 60 * 10;

    public function index()
    {
        try {
            $req = Request::get()->toArray();
            $mallSno = \SESSION::get(SESSION_GLOBAL_MALL)['sno'] ? \SESSION::get(SESSION_GLOBAL_MALL)['sno'] : DEFAULT_MALL_NUMBER;
            if (Request::post()->get('mode') == 'getAnswer') {
                $faqAdmin = new FaqAdmin();
                $data = $faqAdmin->getFaqView(Request::post()->get('sno'));
                echo $this->json([questionContents => $data['data']['contents'], answerContents => $data['data']['answer']]);
                exit;
            }

            $req['isBest'] = 'y';
            $faq = new Faq();
            $getData = $faq->getFaqList($req);
            $faqCode = gd_code('03001',$mallSno);

            $this->setData('pageView', true);
            $this->setData('title', 'BEST FAQ');
            $this->setData('req', $req);
            $this->setData('faqList', $getData);
            $this->setData('faqCode', $faqCode);
            $this->getView()->setDefine('faq', 'service/faq.html');

            $boardList = new BoardList(['bdId' => Board::BASIC_NOTICE_ID]);
            if ($boardList->canUsePc()) {
                $canNoticeList = $boardList->canList();
                if ($canNoticeList == 'y') {
                    $noticeList = $boardList->getList(false, 5);
                    $notice['list'] = $noticeList;
                    $notice['cfg'] = $boardList->cfg;
                }
            }

            $boardList = new BoardList(['bdId' => Board::BASIC_EVENT_ID]);
            if ($boardList->canUsePc()) {
                $canEventList = $boardList->canList();
                if ($canEventList == 'y') {
                    $eventList = $boardList->getList(false, 5);
                    $event['list'] = $eventList;
                    $event['cfg'] = $boardList->cfg;
                }
            }
        } catch(\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
        $this->setData('canNoticeList', $canNoticeList);
        $this->setData('canEventList', $canEventList);
        $this->setData('notice', $notice);
        $this->setData('event', $event);
    }
}
