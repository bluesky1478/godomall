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
namespace Bundle\Controller\Mobile\Board;

use Component\Goods\GoodsCate;
use Component\Page\Page;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectCloseException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertReloadException;
use Framework\Debug\Exception\RequiredLoginException;
use Request;
use View\Template;
use Component\Validator\Validator;
use Framework\Debug\Exception\AlertBackException;
use Globals;
use Component\Board\BoardWrite;
use Framework\Utility\Strings;
use Framework\StaticProxy\Proxy\Session;

class WriteController extends \Controller\Mobile\Controller
{

    public function index()
    {
        try {
            if(Request::get()->get('mypageFl') == 'y'){
                if (gd_is_login() === false) {
                    throw new RedirectLoginException();
                }
            }

            $this->addScript([
                'gd_board_common.js',
            ]);

            $qryStr = preg_replace(array("/mode=[^&]*/i", "/&[&]+/", "/(^[&]+|[&]+$)/"), array("", "&", ""), Request::getQueryString());
            $req = Request::get()->toArray();
            gd_isset($req['mode'],'write');

            $boardWrite = new BoardWrite($req);
            $boardWrite->checkUseMobile();
            $getData = gd_htmlspecialchars($boardWrite->getData());
            $goodsNo = $req['goodsNo']  ? $req['goodsNo'] : $getData['goodsNo'];
                /*
                 * 게시판은 글로벌이 안된다고 하여, 문의 게시판 말머리 중 "문의내용" 이라는 텍스트(기본값)가 들어가있을 경우에만 번역해달라는 요청으로 넣었습니다..
                 * 말머리에 4개 국어 언어가 추가되기 전까지만 임시로 넣어두겠습니다.. 2017-02-15
                */
            $translateTitle = ['문의내용'];
            if(in_array($boardWrite->cfg['bdCategoryTitle'], $translateTitle)) {
                $boardWrite->cfg['bdCategoryTitle'] = __($boardWrite->cfg['bdCategoryTitle']);
            }

            $bdWrite['cfg'] = $boardWrite->cfg;
            $bdWrite['isAdmin'] = $boardWrite->isAdmin;
            $bdWrite['data'] = $getData;
            if (gd_is_login() === false) {
                // 개인 정보 수집 동의 - 이용자 동의 사항
                $tmp = gd_buyer_inform('001008');
                $private = $tmp['content'];
                if (gd_is_html($private) === false) {
                    $bdWrite['private'] = $private;
                }
                unset($private);
            }

            if($req['mode'] == 'modify') {
                if($bdWrite['data']['isMobile'] == 'n') {
                    throw new AlertBackException(__('PC에서 작성하신 글은 PC에서만 수정 가능합니다.'));
                }
            }

            if(Request::post()->has('oldPassword')){
                $oldPassword = md5(Request::post()->get('oldPassword'));
                $this->setData('oldPassword', $oldPassword);
            }

        } catch (RequiredLoginException $e) {
            $url = '../member/login.php?returnUrl='.urlencode('/goods/goods_view.php?goodsNo='.Request::get()->get('goodsNo').'#reviews');
            $this->js("alert('".$e->getMessage()."');location.href='".$url."';");
        } catch (\Exception $e) {
            if(\Request::isAjax()){
                throw new AlertRedirectException($e->getMessage(),null,null,\Request::getReferer());
            }

            if($req['gboard'] == 'y') {
                throw new AlertCloseException($e->getMessage());
            }
            throw new AlertBackException($e->getMessage());
        }
        if(gd_isset($req['noheader'],'n') != 'n') {
            $this->getView()->setDefine('header', 'outline/_share_header.html');
            $this->getView()->setDefine('footer', 'outline/_share_footer.html');
        }

        if(\Request::isAjax()) {
            $this->getView()->setDefine('header', 'outline/_share_header.html');
            $this->getView()->setDefine('footer', 'outline/_share_footer.html');
        }
        $this->setData('bdWrite', $bdWrite);
        $this->setData('goodsNo', $goodsNo);
        $this->setData('queryString', $qryStr);
        $this->setData('req', gd_htmlspecialchars($boardWrite->req));
        $path = 'board/skin/'.$bdWrite['cfg']['themeId'].'/write.html';
        $this->getView()->setDefine('write', $path);

        $emailDomain = gd_array_change_key_value(gd_code('01004'));
        $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);
        $this->setData('emailDomain', $emailDomain); // 메일주소 리스팅
    }
}
