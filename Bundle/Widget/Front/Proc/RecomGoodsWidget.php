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
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Front\Outline
 * @author
 */

class RecomGoodsWidget extends \Widget\Mobile\Widget
{

    public function index()
    {

        $recom = App::load('\\Component\\Goods\\RecommendGoods');
        $getData = $recom->getGoodsDataUser();

        //품절상품 설정
        $soldoutDisplay = gd_policy('soldout.pc');

        $this->setData('config', $getData['config']);
        $this->setData('data', $getData['data']);
        $this->setData('soldoutDisplay', gd_isset($soldoutDisplay));

    }
}
