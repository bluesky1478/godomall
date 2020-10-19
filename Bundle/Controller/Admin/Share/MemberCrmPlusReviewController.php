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

use Component\Validator\Validator;
use Component\PlusShop\PlusReview\PlusReviewArticleAdmin;
use Framework\Utility\ArrayUtils;
use Component\Page\Page;
use Exception;
use Request;

class MemberCrmPlusReviewController extends \Controller\Admin\Board\ArticleListController
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('member', 'member', 'crm');

        $getValue = Request::get()->toArray();

        gd_isset($getValue['bdId'], 'plus_review'); // 게시판 아이디가 없을 경우 플러스 리뷰 삽입

        if (!$getValue['memNo']) {
            throw new Exception(__('회원을 찾을 수 없습니다.'));
        }
        // 플러스 리뷰 활성화가 된 경우
        if (gd_is_plus_shop(PLUSSHOP_CODE_REVIEW) === true) {

            // 게시판 검색 selectBox
            $boardSearchField = [
                "goodsNm" => "상품명",
                "contents" => "내용",
            ];
            $this->setData('boardSearchField', $boardSearchField);


            // order by 얼리어스 수정 - 플러스 리뷰 pra
            foreach ($getValue as $getKey => $val) {
                if ($getKey == 'sort') {
                    $val = str_replace('b.', '', $val);
                }
                $getValue[$getKey] = $val;
            }
            $getValue['listLength'] = 50; // 리스트 길이
            if ($getValue['searchPeriod'] != -1 && (!$getValue['rangDate'][0] && !$getValue['rangDate'][1])) {
                $getValue['rangDate'][0] = date('Y-m-d', strtotime('-6 day'));
                $getValue['rangDate'][1] = date('Y-m-d');
                $getValue['searchPeriod'] = 6;
            }

            // 플러스 리뷰 컴포넌트 호출
            $plusReviewArticle = new PlusReviewArticleAdmin();
            // 리스트 데이터 추출
            $plusReviewList = $plusReviewArticle->getList($getValue, false);
            // 설정 데이터 추출
            $plusReviewConfig = $plusReviewArticle->getConfig();

            $plusReviewListData['bdList']['cnt'] = $plusReviewList['cnt']; // 카운트
            $plusReviewListData['bdList']['pagination'] = $plusReviewList['pagination']; // 페이징
            $plusReviewListData['bdList']['sort'] = $plusReviewList['sort']; // 정렬
            $plusReviewListData['bdList']['cfg'] = $plusReviewConfig; // 게시판 설정
            $plusReviewListData['bdList']['cfg']['bdId'] = 'plus_review'; // 게시판 플러스 리뷰 아이디 추가
            $plusReviewListData['bdList']['list'] = $plusReviewList['list']; // 리스트 데이터

            $this->setData('bdList', $plusReviewListData['bdList']); // 데이터셋팅
            $this->setData('req', $getValue);

        }

        // CRM 고객관리 게시판탭 공통사용
        $this->getView()->setPageName('share/member_crm_board.php');
    }
}
