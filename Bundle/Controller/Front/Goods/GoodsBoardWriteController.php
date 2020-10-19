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

namespace Bundle\Controller\Front\Goods;

use Bundle\Component\Member\Util\MemberUtil;
use Component\Board\Board;
use Component\Goods\Goods;
use Component\Board\BoardWrite;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertReloadException;
use Framework\Debug\Exception\RedirectLoginException;
use Framework\Debug\Exception\RequiredLoginException;
use Request;

class GoodsBoardWriteController extends \Controller\Front\Controller
{
    public function index()
    {
        try {
            $qryStr = preg_replace(array("/mode=[^&]*/i", "/&[&]+/", "/(^[&]+|[&]+$)/"), array("", "&", ""), Request::getQueryString());
            $req = Request::get()->toArray();
            gd_isset($req['mode'], 'write');

            $boardWrite = new BoardWrite($req);
            $getData = gd_htmlspecialchars($boardWrite->getData());
            $bdWrite['cfg'] = $boardWrite->cfg;
            $bdWrite['isAdmin'] = $boardWrite->isAdmin;
            $bdWrite['data'] = $getData;
            $request = $boardWrite->req;
            $goods = new Goods();
            if ($req['sno']) {  //수정
                $goodsNo = $getData['goodsNo'];
                $goodsView = $goods->getGoodsView($goodsNo);
            } else {
                $goodsNo = $req['goodsNo'];
                $goodsView = $goods->getGoodsView($goodsNo);
            }
            $request['goodsNo'] = $goodsNo;

            if (gd_is_login() === false) {
                // 개인 정보 수집 동의 - 이용자 동의 사항
                $tmp = gd_buyer_inform('001008');
                $private = $tmp['content'];
                if (gd_is_html($private) === false) {
                    $private = nl2br($private);
                    $bdWrite['private'] = $private;
                }
                unset($private);
            }

            if (Request::request()->has('oldPassword')) {
                $oldPassword = md5(Request::request()->get('oldPassword'));
                $this->setData('oldPassword', $oldPassword);
            }

            $this->setData('goodsView', $goodsView);
            $this->setData('bdWrite', $bdWrite);
            $this->setData('queryString', $qryStr);
            $this->setData('req', gd_htmlspecialchars($request));

            $emailDomain = gd_array_change_key_value(gd_code('01004'));
            $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);
            $this->setData('emailDomain', $emailDomain); // 메일주소 리스팅
        }
        catch(RequiredLoginException $e) {
            if(!\Request::isAjax()){
                $url = '../member/login.php?returnUrl='.urlencode('/goods/goods_view.php?goodsNo='.Request::get()->get('goodsNo').'#reviews');
                $this->js("alert('".$e->getMessage()."');opener.location.href='".$url."';self.close();");
            }
            else {
                throw new RedirectLoginException(null,null,null,'/goods/goods_view.php?goodsNo='.Request::get()->get('goodsNo'));
            }
        }
        catch (\Exception $e) {
            if(\Request::isAjax()){
                throw new AlertReloadException($e->getMessage());
            }
            else {
                throw new AlertCloseException($e->getMessage());
            }
        }
    }
}
