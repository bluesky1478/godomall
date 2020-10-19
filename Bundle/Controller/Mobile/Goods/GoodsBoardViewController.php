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

namespace Bundle\Controller\Mobile\Goods;

use Component\Board\Board;
use Component\Board\BoardView;
use Request;
use Framework\Debug\Exception\AlertOnlyException;
use Globals;

class GoodsBoardViewController extends \Controller\Mobile\Controller
{
    public function index()
    {
        if(strpos(Request::getHeaders()->get('ACCEPT'),'application/json') !== false) {
            //권한체크
            $req = array_merge((array)Request::get()->toArray(), (array)Request::post()->toArray());
            try {
                $boardView = new BoardView($req);
                $getData = $boardView->getView();
            } catch(\Exception $e) {
                echo $this->json(['result'=>'fail' , 'contents'=>$e->getMessage()]);
                exit;
            }
            echo $this->json(['result'=>'ok' ]);

        } else {

            try {
                $this->addScript([
                    'gd_board_common.js',
                ]);

                $req = array_merge((array)Request::get()->toArray(), (array)Request::post()->toArray());
                $boardView = new BoardView($req);
                $boardView->checkUseMobile();
                $getData = $boardView->getView();
                $bdView['cfg'] = gd_isset($boardView->cfg);
                $bdView['data'] = gd_isset($getData);
                $bdView['member'] = gd_isset($boardView->member);

                $boardView->canReadSecretReply($bdView['data']);
                $boardSecretReplyCheck = $boardView->setSecretReplyView($bdView['cfg']);
                if (gd_is_login() === false) {
                    // 개인 정보 수집 동의 - 이용자 동의 사항
                    $tmp = gd_buyer_inform('001009');
                    $private = $tmp['content'];
                    if (gd_is_html($private) === false) {
                        $bdView['private'] = $private;
                    }
                }
                $this->setData('req',gd_isset($req));
                $this->setData('bdView', $bdView);
                $this->setData('secretReplyCheck', $boardSecretReplyCheck);
                $this->setData('bdListCfg' , $bdView['cfg']);


            } catch (\Exception $e) {
                throw new AlertOnlyException($e->getMessage());
            }
        }


    }
}
