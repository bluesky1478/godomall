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
namespace Bundle\Controller\Admin\Board;

use App;
use Component\Board\BoardAdmin;
use Component\Board\BoardTheme;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\Except;
use Message;
use Globals;
use Framework\Utility\Strings;
use Framework\Cache\CacheableProxyFactory;
use Cache;
use Request;
use Session;

class BoardListController extends \Controller\Admin\Controller
{
    /**
     * Description
     */
    public function index()
    {
        $this->callMenu('board', 'board', 'list');
        try {
            $boardAdmin = new BoardAdmin();
            $getData = $boardAdmin->getBoardList(Request::get()->all(),true);
        } catch (\Exception $e) {
            throw new AlertOnlyException($e->getMessage());
        }
        // --- 관리자 디자인 템플릿
        $this->setData('data', $getData['data']);
        $this->setData('cnt', $getData['cnt']);
        $this->setData('pagination', $getData['pagination']);
        $this->setData('search', gd_htmlspecialchars(gd_isset($getData['search'])));
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
    }
}
