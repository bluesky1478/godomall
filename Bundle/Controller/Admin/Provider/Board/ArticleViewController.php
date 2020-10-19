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

namespace Bundle\Controller\Admin\Provider\Board;

use App;
use Component\Board\Board;
use Component\Page\Page;
use Component\Board\ArticleListAdmin;
use Component\Board\BoardAdmin;
use Component\Board\BoardTheme;
use Framework\Utility\Strings;
use Request;
use Session;

class ArticleViewController extends \Controller\Admin\Board\ArticleViewController
{
    /**
     * Description
     */
    public function index()
    {
        parent::index();
    }
}
