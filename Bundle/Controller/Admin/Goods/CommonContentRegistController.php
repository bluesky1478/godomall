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
namespace Bundle\Controller\Admin\Goods;

use Globals;
use Request;

/**
  * @author Bag YJ <kookoo135@godo.co.kr>
 */
class CommonContentRegistController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        $commonContent = \App::load('\\Component\\Goods\\CommonContent');
        $getValue = Request::get()->all();

        $targetFl = $commonContent->getTargetFl();
        unset($targetFl['']);
        $exTargetFl = $commonContent->getTargetFl('targetFl', '예외');
        unset($exTargetFl['']);unset($exTargetFl['all']);

        // --- 메뉴 설정
        if (empty($getValue['sno']) === true) {
            $this->callMenu('goods', 'displayConfig', 'commonContentRegist');

            $checked['commonStatusFl']['n'] =
            $checked['commonUseFl']['y'] =
            $checked['commonTargetFl']['all'] =
            $checked['commonHtmlContentSameFl']['y'] = 'checked = "checked"';

        } else {
            $this->callMenu('goods', 'displayConfig', 'commonContentModify');
            $getData = $commonContent->getData($getValue['sno']);
            $getData = gd_htmlspecialchars_stripslashes($getData['data'][0]);

            $checked['commonStatusFl'][$getData['commonStatusFl']] =
            $checked['commonUseFl'][$getData['commonUseFl']] =
            $checked['commonTargetFl'][$getData['commonTargetFl']] =
            $checked['commonHtmlContentSameFl'][$getData['commonHtmlContentSameFl']] = 'checked = "checked"';

            foreach ($exTargetFl as $key => $value) {
                if (is_array($getData['commonEx' . ucwords($key)]) === true) $checked['commonExTargetFl'][$key] = 'checked = "checked"';
            }
        }

        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);

        $this->setData('data', $getData);
        $this->setData('targetFl', $targetFl);
        $this->setData('exTargetFl', $exTargetFl);
        $this->setData('checked', $checked);
    }
}
