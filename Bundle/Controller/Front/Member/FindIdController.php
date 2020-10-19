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
namespace Bundle\Controller\Front\Member;

/**
 * Class 아이디 찾기
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class FindIdController extends \Controller\Front\Controller
{

    /**
     * @inheritdoc
     */
    public function index()
    {
        $gGlobal = \Globals::get('gGlobal');
        $countries = \Component\Mall\MallDAO::getInstance()->selectCountries();
        $countryKey = [];
        $countryPhone = [];

        foreach ($countries as $key => $val) {
            if ($val['callPrefix'] > 0) {
                $countryKey[$key] = $val['code'];
                $countryPhone[$val['code']] = __($val['countryName']) . '(+' . $val['callPrefix'] . ')';
            }
        }

        $this->setData('countryKey', $countryKey);
        $this->setData('countryPhone', $countryPhone);
        $this->setData('gGlobal', $gGlobal);

        $emailDomain = gd_array_change_key_value(gd_code('01004'));
        $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);
        $this->setData('emailDomain', $emailDomain); // 메일주소 리스팅
    }
}
