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

namespace Bundle\Controller\Admin\Marketing;

use Component\PlusShop\PlusReview\PlusReviewConfig;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Utility\GodoUtils;
use Globals;

class NaverConfigController extends \Controller\Admin\Controller
{

    private $_defineMarketing = null;

    public function index()
    {

		/**
		 * 네이버 쇼핑 설정
		 *
		 * @author sj
		 * @version 1.0
		 * @since 1.0
		 * @copyright ⓒ 2016, NHN godo: Corp.
		 */

		//--- 메뉴 설정
		$this->callMenu('marketing','naver','config');

		//--- 페이지 데이터
		try {
			$dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
			$data = $dbUrl->getConfig('naver', 'config');

			$groups = gd_member_groups();
			$join = gd_policy('member.join');
			$joinGroup['name'] = gd_isset($groups[gd_isset($join['grpInit'], 1)]);

			$memberGroup = \App::load('\\Component\\Member\\MemberGroup');
			$groupData = $memberGroup->getGroupViewToArray($join['grpInit']);
			switch($groupData['dcType']) {
				case 'price' : {
					$joinGroup['dc'] = $groupData['dcPrice'] . __('원');
					break;
				}
				default : {
					$joinGroup['dc'] = $groupData['dcPercent'] . '%';
					break;
				}
			}

			// marketing 용 define 상수 가져 오기
            $this->_defineMarketing = \App::load('Component\\Marketing\\DefineMarketing');

			gd_isset($data['naverFl'], 'n');
			gd_isset($data['cpaAgreement'], 'n');
            gd_isset($data['dcTimeSale'], 'n');
			gd_isset($data['dcGoods'], 'n');
			gd_isset($data['dcCoupon'], 'n');
			gd_isset($data['naverEventCommon'], 'n');
			gd_isset($data['naverEventGoods'], 'n');
            gd_isset($data['naverVersion'], $data['naverFl'] =='y' && !$data['naverVersion'] ? '2' : '3');
            gd_isset($data['naverReviewChannel'], 'board');

            $checked = array();
            $checked['naverVersion'][$data['naverVersion']]= $checked['naverEventCommon'][$data['naverEventCommon']]= $checked['naverEventGoods'][$data['naverEventGoods']]= $checked['naverFl'][$data['naverFl']]= $checked['cpaAgreement'][$data['cpaAgreement']] = $checked['dcTimeSale'][$data['dcTimeSale']] = $checked['dcGoods'][$data['dcGoods']]= $checked['dcCoupon'][$data['dcCoupon']] = $checked['naverReviewChannel'][$data['naverReviewChannel']] = $checked['onlyMemberReviewUsed'][$data['onlyMemberReviewUsed']] = 'checked="checked"';
			unset($groups);
			unset($memberGroup);
			unset($groupData);
			unset($join);
		}
		catch (Exception $e) {
			throw new AlertOnlyException($e->getMessage());
		}

		$plusReviewConfig = new PlusReviewConfig();
		//--- 관리자 디자인 템플릿
		$this->setData('data',gd_isset($data));
		$this->setData('checked',gd_isset($checked));
        $this->setData('naverGrade',$this->_defineMarketing->getNaverGrade());
        $this->setData('naverGradeCount',$this->_defineMarketing->getNaverGradeMaxCount());
		$this->setData('joinGroup',gd_isset($joinGroup));
		$this->setData('godo',(Globals::get('gLicense')));
		$this->setData('useFlPlusReview',$plusReviewConfig->getConfig('useFl'));
		$this->setData('isPlusReview',GodoUtils::isPlusShop(PLUSSHOP_CODE_REVIEW));
		$this->setData('isPlusShopTimeSale',gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE));

		if(gd_policy('basic.info')['mallDomain']) $this->setData('mallDomain',"http://".gd_policy('basic.info')['mallDomain']."/");
		else $this->setData('mallDomain',URI_HOME);

        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);


    }
}
