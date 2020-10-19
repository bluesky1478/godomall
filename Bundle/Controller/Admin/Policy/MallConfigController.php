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

namespace Bundle\Controller\Admin\Policy;

use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;

/**
 * Class MallConfigController
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class MallConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $this->callMenu('policy', 'multiMall', 'mallConfig');
        if (!$request->get()->has('domainFl')) {
            $request->get()->set('domainFl', 'us');
        }
        $domainFl = $request->get()->get('domainFl');
        $mallService = new \Component\Mall\Mall();
        $mall = $mallService->getMall($domainFl, 'domainFl');
        StringUtils::strIsSet($mall['recommendCountryCode'], 'kr');
        $exchangeRate = new \Component\ExchangeRate\ExchangeRate();
        $currencies = $exchangeRate->getGlobalCurrency();
        $currencies[999] = [
            'globalCurrencySymbol' => '₩',
            'globalCurrencyString' => 'KRW',
            'globalCurrencyName'   => 'Won',
        ];
        $globalCurrencyNo = intval($mall['globalCurrencyNo']);
        $addGlobalCurrencyNo = intval($mall['addGlobalCurrencyNo']);
        $mall['globalCurrencySymbol'] = $currencies[$globalCurrencyNo]['globalCurrencySymbol'];
        $mall['globalCurrencyString'] = $currencies[$globalCurrencyNo]['globalCurrencyString'];
        $mall['globalCurrencyName'] = $currencies[$globalCurrencyNo]['globalCurrencyName'];
        $mall['globalCurrencyName2'] = $currencies[$globalCurrencyNo]['globalCurrencyName2'];
        $mall['globalCurrencyDecimal'] = $currencies[$globalCurrencyNo]['globalCurrencyDecimal'];
        $mall['globalCurrencyDecimalFormat'] = $currencies[$globalCurrencyNo]['globalCurrencyDecimalFormat'];
        $mall['addGlobalCurrencySymbol'] = $currencies[$addGlobalCurrencyNo]['globalCurrencySymbol'];
        $mall['addGlobalCurrencyString'] = $currencies[$addGlobalCurrencyNo]['globalCurrencyString'];
        $mall['addGlobalCurrencyName'] = $currencies[$addGlobalCurrencyNo]['globalCurrencyName'];
        $mall['addGlobalCurrencyDecimalFormat'] = $mall['addGlobalCurrencyDecimalFormat'];
        $checked['addGlobalCurrencyNo'][$mall['addGlobalCurrencyNo']] = $checked['useFl'][$mall['useFl']] =
        $checked['globalCurrencyDecimal'][$mall['globalCurrencyDecimal']] =$checked['addGlobalCurrencyDecimal'][$mall['addGlobalCurrencyDecimal']] = 'checked="checked"';
        $selected['currencyDisplayFl'][$mall['currencyDisplayFl']]
            = $selected['globalCurrencyDecimal'][$currencies[$globalCurrencyNo]['globalCurrencyDecimal']]
            = $selected['globalCurrencyDecimalDetail'][$currencies[$globalCurrencyNo]['globalCurrencyDecimalDetail']]
            = $selected['addGlobalCurrencyDecimal'][$mall['addGlobalCurrencyDecimal']]
            = $selected['addGlobalCurrencyDecimalDetail'][$mall['addGlobalCurrencyDecimalDetail']]
            = 'selected="selected"';
        $useShopDomain = $mall['connectDomain']['connect'];
        $checked['useShopDomain'][$mall['useShopDomain']] = $useShopDomain[$mall['useShopDomain']] = 'checked="checked"';

        // 스킨 리스트
        $skinList = $this->_skinList('front', $domainFl, $mallService);
        $skinListMobile = $this->_skinList('mobile', $domainFl, $mallService);

        // 현재 해외 상품 스킨
        $mallSkin = ComponentUtils::getPolicy('design.skin', $mall['sno']);

        // 상점 기본 정보
        $mallInfo = gd_policy('basic.info');

        $this->setData('recommendCountries', $mallService->getRecommendCountries());
        $this->setData('currencies', $currencies);
        $this->setData('globalMallDomains', GLOBAL_MALL_DOMAINS);
        $this->setData('mallSkin', $mallSkin);
        $this->setData('mall', $mall);
        $this->setData('tempDomain', $mallService->getTempDomain($mall['domainFl']));
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('connectDomainList', json_encode($mall['connectDomain']));
        $this->setData('skinList', $skinList);
        $this->setData('skinListMobile', $skinListMobile);
        $this->setData('mallDomain', $mallInfo['mallDomain']);
        $this->setData('msgFl', gd_installed_date('2017-07-26'));
        $this->setData('useShopDomain', gd_isset($mall['useShopDomain'], 0));
    }

    /**
     * 스킨 리스트
     * @param string $skinType 스킨타입
     * @param string $domainFl 현재 설정중인 국가코드
     * @param object $mallService 몰 정보
     * @return array 보유스킨 리스트
     */
    private function _skinList($skinType, $domainFl, $mallService)
    {
        // 각 국가별 default 스킨 여부
        $defaultSkinFl = true;
        $defaultMallName = '';

        // 스킨 리스트
        $skinBase = new \Component\Design\SkinBase($skinType);
        $skinList = [];
        foreach ($skinBase->getSkinList() as $index => $item) {
            // 국가전용 스킨
            if (empty($item['skin_country']) === false) {
                $countryCode = explode('_', $item['skin_country'])[0];
            } else {
                $countryCode = 'kr';
            }

            // default 스킨 여부
            if ($domainFl === $countryCode) {
                $defaultSkinFl = false;
            }

            // 스킨 리스트
            $skinList[$item['skin_code']] = $item['skin_name'] . ' (' . $item['skin_code'] . ' - ' . $item['skin_language'] . ')';
        }

        // 사용중인 스킨 제외 (사용스킨, 작업스킨)
        foreach ($mallService->getListByUseMall() as $value) {
            if ($domainFl == $value['domainFl']) {
                $defaultMallName = $mallService->getList()[$value['sno']]['mallName'];
                continue;
            }

            // 각 국가코드별 스킨 정보
            $skinData = ComponentUtils::getPolicy('design.skin', $value['sno']);

            // 사용중인 스킨 제외 (사용스킨, 작업스킨)
            $arrSkinMode = ['Live', 'Work'];
            foreach ($arrSkinMode as $sVal) {
                if (empty($skinList[$skinData[$skinType . $sVal]]) === false) {
                    unset($skinList[$skinData[$skinType . $sVal]]);
                }
            }
        }

        // 각 국가별 default 스킨
        if ($defaultSkinFl === true) {
            foreach ($mallService->getList() as $value) {
                if ($domainFl == $value['domainFl']) {
                    $defaultMallName = $mallService->getList()[$value['sno']]['mallName'];
                    break;
                }
            }
            $skinList[STR_DIVISION. 'default' . STR_DIVISION] = $defaultMallName . ' Default 스킨';
        }

        // Sort an array by key
        ksort($skinList);

        return $skinList;
    }
}
