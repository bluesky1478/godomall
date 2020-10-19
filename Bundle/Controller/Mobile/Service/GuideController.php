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

namespace Bundle\Controller\Mobile\Service;

use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Component\Design\ReplaceCode;

/**
 * Class 프론트-이용안내
 * @package Bundle\Controller\Front\Service
 * @author  yjwee
 */
class GuideController extends \Controller\Mobile\Controller
{
    /**
     * {@inheritDoc}
     */
    public function index()
    {
        $buyerInform = new BuyerInform();
        $baseGuide = $buyerInform->getInformData(BuyerInformCode::BASE_GUIDE);

        $replaceCode = new ReplaceCode();
        $replaceCode->initWithUnsetDiff(['{rc_mallNm}']);
        $content = $replaceCode->replace($baseGuide['content'], $replaceCode->getDefinedCode());

        $this->setData('baseGuideContent', gd_isset($content, ''));
    }
}
