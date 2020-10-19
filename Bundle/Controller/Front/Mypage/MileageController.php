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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Front\Mypage;

use Bundle\Component\Mileage\Mileage;
use Component\Mileage\MileageUtil;
use Component\Page\Page;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;

/**
 * 마이페이지-혜택 마일리지
 * @package Bundle\Controller\Front\Mypage
 * @author  yjwee
 */
class MileageController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        if ($session->has(SESSION_GLOBAL_MALL)) {
            throw new AlertBackException(__('잘못된 접근입니다.'));
        }

        if (is_numeric($request->get()->get('searchPeriod')) === true && $request->get()->get('searchPeriod') >= 0) {
            $selectDate = $request->get()->get('searchPeriod');
        } else {
            $selectDate = 7;
        }
        $regDt = DateTimeUtils::getBetweenDateString('-' . ($selectDate) . 'days');

        // 기간 조회
        if ($request->isMobile() === true) {
            $searchDate = [
                '1'   => __('오늘'),
                '7'   => __('최근 %d일', 7),
                '15'  => __('최근 %d일', 15),
                '30'  => __('최근 %d개월', 1),
                '90'  => __('최근 %d개월', 3),
                '180' => __('최근 %d개월', 6),
                '365' => __('최근 %d년', 1),
            ];
            $this->setData('searchDate', $searchDate);
            $this->setData('selectDate', $selectDate);
        }

        $regTerm = $request->get()->get('regTerm', 7);
        $regDt = $request->get()->get('regDt', $regDt);

        $active['regTerm'][$regTerm] = 'active';

        /**
         * 페이지 데이터 설정
         */
        $page = $request->get()->get('page', 1);
        $pageNum = $request->get()->get('pageNum', 10);

        // 보안취약점 요청사항 추가
        $regDt[0] = preg_replace("/([^0-9\-])/", "", $regDt[0]);
        $regDt[1] = preg_replace("/([^0-9\-])/", "", $regDt[1]);

        /**
         * 요청처리
         * @var \Bundle\Component\Mileage\Mileage $mileage
         */
        $mileage = \App::load('\\Component\\Mileage\\Mileage');
        $list = $mileage->listBySession($regDt, $page, $pageNum);

        $mileageReasons = MileageUtil::getReasons();

        // 괄호 내용 제거 (특정 사유 제외)
        $exceptType = [
            Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_USE_GOODS_BUY,
        ];
        foreach($list as &$val){
            if(empty($val['contents']) || $val['contents'] == ''){
                $val['contents'] = $mileageReasons[$val['reasonCd']];
            }
            if (in_array($val['reasonCd'], $exceptType) === false) {
                $bracketFl = strpos($val['contents'], '(');
                if ($bracketFl > 0) {
                    $val['contents'] = substr_replace($val['contents'], '', strpos($val['contents'], '('));
                }
            }
        }

        // 마일리지 소멸예정일을 저장된 데이터가 아닌 전날의 23:59:59 로 변경하여 노출되도록 수정
        $list = MileageUtil::changeDeleteScheduleDt($list, true);

        // 모바일 신규 회원가입 마일리지 문구 구분
        if ($request->isMobile() === true) {
            foreach ($list as $mKey => $mVal) {
                if ($mVal['handleNo'] == 'smsFl') {
                    $list[$mKey]['contents'] .= '<br/>Sms ' . __('수신동의');
                } elseif ($mVal['handleNo'] == 'mailingFl') {
                    $list[$mKey]['contents'] .= '<br/>' . __('이메일 수신동의');
                }
            }
        }

        /**
         * 페이징 처리
         */
        $p = new Page($page, $mileage->foundRowsByListSession(), null, $pageNum);
        $p->setPage();
        $p->setUrl($request->getQueryString());

        /**
         * View 데이터
         */
        $this->setData('list', $list);
        $this->setData('regTerm', $regTerm);
        $this->setData('regDt', $regDt);
        $this->setData('active', $active);
        $this->setData('page', $p);

        /**
         * css 추가
         */
        $this->addCss(
            [
                'plugins/bootstrap-datetimepicker.min.css',
                'plugins/bootstrap-datetimepicker-standalone.css',
            ]
        );

        /**
         * js 추가
         */
        $locale = \Globals::get('gGlobal.locale');
        $this->addScript(
            [
                'gd_board_list.js',
                'moment/moment.js',
                'moment/locale/' . $locale . '.js',
                'jquery/datetimepicker/bootstrap-datetimepicker.min.js',
            ]
        );
    }
}
