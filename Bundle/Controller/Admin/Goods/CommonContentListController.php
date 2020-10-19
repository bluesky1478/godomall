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
class CommonContentListController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {

        $commonContent = \App::load('\\Component\\Goods\\CommonContent');

        // --- 메뉴 설정
        $this->callMenu('goods', 'displayConfig', 'commonContentList');

        $getData = $commonContent->getData(null, [], true);
        $targetFl = $commonContent->getTargetFl();
        $useFl = $commonContent->getObject('useFl');
        $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('targetFl', $targetFl);
        $this->setData('useFl', $useFl);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);
    }
}
