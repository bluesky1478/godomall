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
namespace Bundle\Controller\Admin\Share;

use App;
use Request;

/**
 * Class ReplaceCodeController
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class ReplaceCodeController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     */
    public function index()
    {
        /** @var \Bundle\Component\Design\ReplaceCode $replaceCode */
        $replaceCode = App::load('\\Component\\Design\\ReplaceCode');
        $type = Request::get()->get('type', null);
        $defaultCode = Request::get()->get('defaultCode', []);
        $replaceCode->initWithUnsetDiff($defaultCode);
        if (is_null($type) === false && empty($type) === false) {
            $funcName = 'setReplaceCodeBy' . ucfirst($type);
            if ($type === 'findpassword' || $type === 'wake') {
                $funcName = 'setReplaceCodeByCertification';
            }
            if ($type === 'agreement2yperiod') {
                $funcName = 'setReplaceCodeByAgreement';
            }
            call_user_func(
                [
                    $replaceCode,
                    $funcName,
                ], []
            );
        }

        $defineCode = $replaceCode->getDefinedCode();

        /*
         * 주문일 경우 주문상품정보는 배열이기때문에 치환코드 표기를 하지 않고 상품명, 상품가격, 상품수량 역시 일반 치환코드로 처리가 안되기 때문에 보기에 나타나지 않는다.
         */
        if ($type === 'order') {
            unset($defineCode['{rc_goods}']);
        }
        /*
         * 회원등급 변경 안내에서 일반 치환코드로 처리가 안되는 부분 제거
         */
        if ($type === 'groupchange') {
            unset($defineCode['{rc_dcExScm}'], $defineCode['{rc_dcExCategory}'], $defineCode['{rc_dcExBrand}'], $defineCode['{rc_dcExGoods}'], $defineCode['{rc_overlapDcScm}'], $defineCode['{rc_overlapDcCategory}'], $defineCode['{rc_overlapDcBrand}'], $defineCode['{rc_overlapDcGoods}']);
        }

        $this->getView()->setDefine('layout', 'layout_blank.php');
        $this->setData('defineCode', $defineCode);
    }
}
