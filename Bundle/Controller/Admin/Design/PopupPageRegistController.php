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

namespace Bundle\Controller\Admin\Design;

use Component\Design\DesignPopup;
use Component\Page\Page;
use Exception;
use Request;

/**
 *
 * @package Bundle\Controller\Admin\Design
 * @author  Bag YJ <kookoo135@godo.co.kr>
 */
class PopupPageRegistController extends \Controller\Admin\Controller
{
    public function index()
    {
        $getValue = Request::get()->all();
        $designPopup = new DesignPopup();

        if (empty($getValue['sno']) === false) {
            $getData = $designPopup->getPopupPage($getValue);
            $data = $getData['data'][0];
        } else {
            $data['pcDisplayFl'] = 'y';
        }

        $checked['pcDisplayFl'][$data['pcDisplayFl']] =
        $checked['mobileDisplayFl'][$data['mobileDisplayFl']] = 'checked = "checked"';

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('data', $data);
        $this->setData('checked', $checked);
    }
}
