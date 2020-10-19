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

namespace Bundle\Controller\Admin\Share;

use Component\Godo\GodoGongjiServerApi;
use Request;

/**
 * 관리자 페이지내 고도 Panel / API
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class AdminPanelApiController extends \Controller\Admin\Controller
{
    public function index()
    {
        // 고도 공지서버 모듈 호출
        $godoApi = new GodoGongjiServerApi();

        // 페이지 코드 (메뉴얼의 메뉴 코드를 이용, get / post로 받아옴)
        $pageCode = Request::request()->toArray();

        // panel data
        $panel = [];

        // 관리자 로그인
        if ($pageCode['menuCode'] === 'base' && empty($pageCode['menuKey']) === true && $pageCode['menuFile'] === 'login') {
            // 배너
            $loginPanel = $godoApi->getGodoServerData('loginPanel');

            if (empty($loginPanel) === false) {
                $panel['banner'][] = [
                    'panelCode' => 'loginPanel',
                    'panelData' => $loginPanel,
                ];
            }
        }

        // 관리자 메인
        if ($pageCode['menuCode'] === 'base' && $pageCode['menuKey'] === 'index' && $pageCode['menuFile'] === 'index') {

            // 배너와 게시판
            $arrPanel = [
                'banner' => [
                    'mainTop',
                    'mainMiddle',
                    'mainBottom',
                    'mainSide',
                ],
                'board'  => [
                    'noticeAPI',
                    'patchAPI',
                    'betterAPI',
                    'eduAPI',
                ],
            ];

            foreach ($arrPanel as $pKey => $pVal) {
                foreach ($pVal as $aVal) {
                    if ($pKey === 'banner') {
                        $setData = $godoApi->getGodoServerData($aVal);
                    }
                    if ($pKey === 'board') {
                        if ($aVal == 'eduAPI') {
                            $setData = $godoApi->getGodoServerData($aVal, 2);
                        } else {
                            $setData = $godoApi->getGodoServerData($aVal);
                        }
                    }
                    if (empty($setData) === false) {
                        $panel[$pKey][] = [
                            'panelCode'   => $aVal,
                            'panelData'   => $setData,
                            'gdSharePath' => PATH_ADMIN_GD_SHARE,
                        ];
                    }
                }
            }

            // 게시판등의 더보기의 링크
            $arrLink = [
                'noticeLink',
                'patchLink',
                'betterLink',
                'eduLink',
                'customerLink',
            ];
            $setData = $godoApi->getGodoServerBoardUrl();

            foreach ($arrLink as $pVal) {
                $panel['link'][] = [
                    'panelCode' => $pVal,
                    'panelData' => $setData[$pVal],
                ];
            }

            // 고객센터 정보
            $setData = $godoApi->getGodoServerCustomerCenter();
            $panel['customer'][] = [
                'panelCode' => 'customerAPI',
                'panelData' => $setData,
            ];

        }

        // 페이지 패널
        if (empty($pageCode['menuCode']) === false && empty($pageCode['menuFile']) === false) {
            // 페이지 카테고리
            $pageCategory = $pageCode['menuCode'] . '/' . $pageCode['menuFile'];
            $popupPanel = $godoApi->getGodoServerData('popupPanel', null, $pageCategory);

            if (empty($popupPanel) === false) {
                $panel['popup'][] = [
                    'panelCode' => 'popupPanel',
                    'panelData' => $popupPanel,
                ];
            }
        }

        // json data
        $panelData = '{}';
        if (empty($panel) === false) {
            $panelData = json_encode($panel, JSON_UNESCAPED_UNICODE);
        }

        echo $panelData;
        exit();
    }
}
