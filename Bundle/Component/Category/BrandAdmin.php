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

namespace Bundle\Component\Category;

/**
 * 브랜드 객체가 별도로 필요하여 wrapping 클래스로 생성하고 강제로 brand를 생성자에 넘겨주도록 추가
 *
 * @package Bundle\Component\Category
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class BrandAdmin extends \Component\Category\CategoryAdmin
{
    /**
     * 생성자
     *
     * @param string $cateType 카테고리 종류(goods,brand) , null인 경우 상품 카테고리 , (기본 null)
     */
    public function __construct()
    {
        parent::__construct('brand');
    }
}
