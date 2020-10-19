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
namespace Bundle\Controller\Api\Share;

use Framework\Utility\ComponentUtils;
use Framework\Utility\HttpUtils;
use Request;

/**
 * 치환코드등록/수정
 *
 * @author <kookoo135@godo.co.kr>
 */
class LayerG5SubstitutionRegistController extends \Controller\Api\Controller
{
    public function index()
    {
        $search = Request::get()->all();
        $search['mode'] = 'regist';
        if (empty($search['sno']) === false) {
            $search['mode'] = 'modify';
            $data = ComponentUtils::getDesignCodeData($search);
        }

        $designFileName = ComponentUtils::getDesignFileName();
        $designFileName['add'] = '신규추가';
        $this->setData('designFileName', $designFileName);


        $this->setData('search', $search);
        $this->setData('data', $data['data'][0]);
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
