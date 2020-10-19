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

namespace Bundle\Controller\Admin\Mobile;

use Framework\Debug\Exception\AlertOnlyException;
use Framework\Utility\StringUtils;

/**
 * 디자인 스킨 선택
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class DesignSkinListController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws AlertOnlyException
     */
    public function index()
    {
        //--- 메뉴 설정
        $this->callMenu('mobile', 'designConf', 'skinList');
        $request = \App::getInstance('request');
        //--- 페이지 데이터
        try {
            $getValue = $request->get()->all();
            $mall = \App::load('Component\\Mall\\Mall');
            $skinAll = $mallSelect = [];

            //--- skinBase 정의
            $skinBase = \App::load('Component\\Design\\SkinBase', 'mobile');
            $skinList = $skinBase->getSkinList();

            // 고도 공지서버 모듈 호출
            $godoApi = \App::load('Component\\Godo\\GodoGongjiServerApi');
            $freeSkinBanner = $godoApi->getGodoSkinServerData('mobile');

            if ($mall->isUsableMall() === true) {
                $mallIconType = gd_policy('design.mallIconType');
                $mallIconType['iconType'] = gd_isset($mallIconType['iconTypeMobile'], 'check');

                $mallList = $mall->getListByUseMall();

                foreach ($mallList as $mallInfo) {
                    $mallSelect[$mallInfo['sno']] = $mallInfo['mallName'];
                }
            } else {
                $skinAll = gd_policy('design.skin');
                $mallSelect[1] = '기준몰';
            }

            $mallListAll = $mall->getList();
            foreach ($mallListAll as $mallInfo) {
                $skinAll[$mallInfo['sno']] = gd_policy('design.skin', $mallInfo['sno']);
                $mallIconType['mallIcon'][$mallInfo['sno']] = gd_isset($mallIconType['mallIconMobile'][$mallInfo['sno']], 'ico_' . $mallInfo['domainFl'] . '_mobile.png');
            }

            // 현재 사용중인 스킨 배열로 저장
            $useSkin = [];
            foreach ($skinAll as $k => $skinData) {
                if (in_array(
                    $k, [
                        'frontLive',
                        'frontWork',
                    ]
                )) {
                    continue;
                }
                if (is_array($skinData) === true) {
                    if (empty($skinData['mobileWork']) === true) {
                        $skinData['mobileWork'] = $skinData['mobileLive'];
                    }
                    $mallData = $mall->getMall($k, 'sno');

                    if (in_array($k, array_keys($mallSelect)) === true) {
                        $useSkin[$skinData['mobileLive']]['delFl'] =
                        $useSkin[$skinData['mobileWork']]['delFl'] = 'n';
                    }
                    if (empty($skinAll[$mallData['sno']]['mobileLive']) === false) {
                        $useSkin[$skinData['mobileLive']]['mobileLiveName'][$mallData['sno']] = $mallData['mallName'];
                        $useSkin[$skinData['mobileLive']]['mobileLiveLanguageFl'][$mallData['sno']] = $mallData['domainFl'];
                    }
                    if (empty($skinAll[$mallData['sno']]['mobileWork']) === false) {
                        $useSkin[$skinData['mobileWork']]['mobileWorkName'][$mallData['sno']] = $mallData['mallName'];
                        $useSkin[$skinData['mobileWork']]['mobileWorkLanguageFl'][$mallData['sno']] = $mallData['domainFl'];
                    }
                } else {
                    $useSkin[$skinData]['delFl'] = 'n';

                    $useSkin[$skinData][$k . 'Name'][1] = '기준몰';
                    $useSkin[$skinData][$k . 'LanguageFl'][1] = 'kr';
                }
            }

            // 멀티상점용 스킨 load
            $mallSno = gd_isset($getValue['mallSno'], 1);
            $session = \App::getInstance('session');
            $session->set('mallSno', $mallSno);
            if (count(array_merge(\App::getConfig('app.cache.page')->toArray(), \App::getConfig('app.cache.widget')->toArray())) > 0) {
                $this->setData('cacheUrl', '../mobile/design_skin_list_ps.php?mode=clearCache&mallSno=' . $mallSno);
            }
            $mobileLive = StringUtils::strIsSet($skinAll['mobileLive'], $skinAll[$mallSno]['mobileLive']);
            $mobileWork = StringUtils::strIsSet($skinAll['mobileWork'], $skinAll[$mallSno]['mobileWork']);

            // 사용중 & 작업중인 스킨
            $skinConf['liveInfo'] = $skinBase->getSkinInfo($mobileLive);
            $skinConf['workInfo'] = $skinBase->getSkinInfo($mobileWork);

            $checked['iconType'][$mallIconType['iconType']] = 'checked="checked"';
        } catch (\Exception $e) {
            throw new AlertOnlyException($e->getMessage());
        }

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layoutMenu', 'menu_design_mobile.php');
        $this->getView()->setDefine('layoutContent', 'design/' . $request->getFileUri());

        $this->addCss(['design.css',]);
        $this->addScript(
            [
                'jquery/jstree/jquery.tree.js',
                'jquery/jstree/plugins/jquery.tree.contextmenu.js',
                'design/designTree.js',
                'design/design.js',
            ]
        );

        $this->setData('mallSno', $mallSno);
        $this->setData('mallCnt', count($mallList));
        $this->setData('mallList', $mallList);
        $this->setData('mallListAll', $mallListAll);
        $this->setData('mallSelect', $mallSelect);
        $this->setData('freeSkinBanner', $freeSkinBanner);
        $this->setData('skinList', $skinList);
        $this->setData('skinCnt', count($skinList));
        $this->setData('skinConf', $skinConf);
        $this->setData('skinType', $skinBase->skinType);
        $this->setData('deviceType', $skinBase->deviceType);
        $this->setData('skinPreviewUrl', URI_MOBILE . '?tplMobileSkin=');
        $this->setData('skinPreviewPage', '/main/index.php?tplMobileSkin=');
        $this->setData('uriHome', URI_MOBILE);
        $this->setData('useSkin', $useSkin);
        $this->setData('uriCommon', \UserFilePath::data('commonimg')->www());
        $this->setData('checked', $checked);
        $this->setData('mallIcon', $mallIconType['mallIcon']);
        $this->setData('menuType', 'mobile');
        $this->setData('gReferrer', true);
    }
}
