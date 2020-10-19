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
namespace Bundle\Widget\Front\Proc;

use App;

/**
 * Class AutocompleteSearchWidget
 *
 * @package Bundle\Widget\Front\Proc
 * @yoonar
 */

class AutocompleteSearchWidget extends \Widget\Front\Widget
{

    public function index()
    {
        $recentKeyword = gd_policy('search.recentKeyword');
        $recom = gd_policy('goods.recom');
        $recentCount = empty($recentKeyword) === false ? $recentKeyword['pcCount'] : 10;

        $this->setData('recentCount', gd_isset($recentCount));
        $this->setData('recomDisplayFl', $recom['pcDisplayFl']);
    }
}
