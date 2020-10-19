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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Policy\Storage;

interface StorageInterface
{
    /**
     * Description.
     *
     * @param       $name
     * @param mixed $value
     * @param int   $mallNo
     *
     * @return bool
     */
    public function setValue($name, $value, $mallNo = DEFAULT_MALL_NUMBER);

    /**
     * Description.
     *
     * @param     $name
     * @param int $mallNo
     *
     * @return mixed
     */
    public function getValue($name, $mallNo = DEFAULT_MALL_NUMBER);
}
