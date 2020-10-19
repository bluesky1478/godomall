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
namespace Bundle\Controller\Admin\Board;

use Component\Board\BoardTemplate;
use Component\Board\BoardTheme;
use Component\Board\BoardAdmin;
use Component\Board\Board;
use Globals;
use Request;
use Cache;
use Message;
use Framework\Utility\Strings;
use Framework\Debug\Exception\AlertBackException;
use Exception;

class BoardRegisterController extends \Controller\Admin\Controller
{
    /**
     * Description
     */
    public function index()
    {
        try {
            $storageBox = [];
            $tmp = gd_policy('basic.storage');

            if (empty($tmp['httpUrl']) === false) {
                foreach ($tmp['httpUrl'] as $key => $val) {
//                if ($key == 'imageStorage1') {
//                    continue;
//                }
                    $storageBox[$val] = $tmp['storageName'][$key];
                }
            }
            unset($tmp);
            // --- 페이지 데이터
            $boardAdmin = new BoardAdmin();

            // 모드정의
            if ((Request::get()->get('sno'))) {
                $mode = 'modify';

                // --- 메뉴 설정
                // - 게시판 register / modify
                // - 게시물 boardWrite / boardView / boardReply 로 변경
                $this->callMenu('board', 'board', 'modify');
            } else {
                $mode = 'regist';

                // --- 메뉴 설정
                // - 게시판 register / modify
                // - 게시물 boardWrite / boardView / boardReply 로 변경
                $this->callMenu('board', 'board', 'register');
            }
            $modeTxt = ($mode != 'modify' ? '등록' : '수정');

            // 데이터
            $boardList = $boardAdmin->selectList();
            $getData = $boardAdmin->getBoardView(Request::get()->get('sno'));
            $boardTemplate = new BoardTemplate();
            $templateList = $boardTemplate->getSelectData('front');
            $boardTheme = new BoardTheme();
        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->addCss([
            '../script/jquery/colorpicker/colorpicker.css',
        ]);
        $this->addScript([
            'jquery/jquery.dataOverlapChk.js',
            'jquery/jquery-ui/jquery-ui.js',
            'jquery/colorpicker/colorpicker.js',
            'jquery/jquery.colorChart.js',
            'jquery/jquery.multi_select_box.js',
        ]);

        $this->setData('templateList', $templateList);
        $this->setData('mode', $mode);
        $this->setData('modeTxt', $modeTxt);
        $this->setData('storageBox', $storageBox);
        $this->setData('data', gd_htmlspecialchars($getData['data']));
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', gd_isset($getData['selected'], null));
        $this->setData('disabled', $getData['disabled']);
        $this->setData('themes', $boardTheme->getThemes());
        $this->setData('bdKindList', Board::KIND_LIST);
        $periodDay= [1 , 2 , 3 , 4 , 5 , 6 , 7 , 8 , 9 , 10 , 11 , 12 , 13 , 14 , 15 , 20 , 25 , 30];
        $this->setData('periodDay', $periodDay);
//        $this->setData('boardFieldList', Board::FIELD_LIST);
        $this->setData('uploadMaxSize', str_replace('M', '', ini_get('upload_max_filesize')));
        $this->setData('boardCnt', count($boardList));
        $isGoodsReview = ($getData['data']['bdId'] == Board::BASIC_GOODS_REIVEW_ID) ? true : false;    //상품후기만 보이게
        $this->setData('isGoodsReview', $isGoodsReview);
        $this->setData('isGoodsQa', ($getData['data']['bdId'] == Board::BASIC_GOODS_QA_ID) ? true : false);
        $goodsBoard = ($getData['data']['bdId'] == Board::BASIC_GOODS_REIVEW_ID || $getData['data']['bdId'] == Board::BASIC_GOODS_QA_ID) ? 'y' : 'n';    //상품문의,후기
        $this->setData('goodsBoard', $goodsBoard);
//        debug(\Globals::get('gGlobal'));

        //seo태그 개별설정
        $this->getView()->setDefine('seoTagFrm',  'share/seo_tag_each.php');
    }
}
