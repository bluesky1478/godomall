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
namespace Bundle\Controller\Admin\Policy;

use Component\Godo\GodoCenterServerApi;

/**
 * Class 관리자-운영정책-본인확인인증서비스-휴대폰본인확인 컨트롤러
 * @package Bundle\Controller\Admin\Policy
 * @author  yjwee
 */
class MemberAuthCellphoneController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        /**
         *   page navigation
         */
        $this->callMenu('policy', 'auth', 'authCellphone');
        $godoCenterServiceApi = new GodoCenterServerApi();
        $dreamsecurityPrefix = $godoCenterServiceApi->lookupPrefixDreamsecurity();
        $data = gd_policy('member.auth_cellphone');
        $data = gd_htmlspecialchars_stripslashes($data);
        $dataKcp = gd_policy('member.auth_cellphone_kcp');
        $dataKcp = gd_htmlspecialchars_stripslashes($dataKcp);
        gd_isset($data['useFl'], 'n');
        gd_isset($data['useDataJoinFl'], 'y');
        gd_isset($data['useDataModifyFl'], 'n');
        gd_isset($data['minorFl'], 'n');
        gd_isset($data['codeValue'], '6');
        gd_isset($data['cpCode'], '');
        gd_isset($dataKcp['serviceCode'], '');
        gd_isset($dataKcp['serviceId'], '');
        gd_isset($dataKcp['serviceStatus'], '');
        gd_isset($dataKcp['timestamp'], '');
        gd_isset($dataKcp['token'], '');
        gd_isset($dataKcp['useFlKcp'], 'n');
        gd_isset($dataKcp['useDataJoinFlKcp'], 'y');
        gd_isset($dataKcp['useDataModifyFlKcp'], 'n');

        if ($data['useFl'] == 'y') {
            $useTab =  'dream';
        } else {
            $useTab =  'kcp';
        }

        /**
         *   set checkbox, select property
         */
        $checked['useFl'][$data['useFl']] = $checked['useDataJoinFl'][$data['useDataJoinFl']] = $checked['useDataModifyFl'][$data['useDataModifyFl']] = $checked['minorFl'][$data['minorFl']] = $checked['codeValue'][$data['codeValue']] = 'checked="checked"';
        $checked['useFlKcp'][$dataKcp['useFlKcp']] = $checked['useDataJoinFlKcp'][$dataKcp['useDataJoinFlKcp']] = $checked['useDataModifyFlKcp'][$dataKcp['useDataModifyFlKcp']] = 'checked="checked"';

        /**
         *   set view data
         */
        $this->setData('data', gd_htmlspecialchars($data));
        $this->setData('dataKcp', gd_htmlspecialchars($dataKcp));
        $this->setData('useTab', $useTab);
        $this->setData('dreamsecurityPrefix', gd_htmlspecialchars($dreamsecurityPrefix));
        $this->setData('checked', $checked);

        /**
         *   add javascript
         */
        $this->addScript(['member.js']);
    }
}
