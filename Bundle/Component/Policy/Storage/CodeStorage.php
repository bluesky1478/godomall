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

/**
 * Class CodeStorage
 * @package Bundle\Component\Policy\Storage
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class CodeStorage extends \Component\Policy\Storage\DatabaseStorage
{
    /**
     * @inheritdoc
     *
     */
    public function getValue($name, $mallSno = 1)
    {
        $codes = explode('.', $name);
        $params = [
            'groupCode' => $codes[0],
            'code'      => $codes[1],
            'mallSno'   => $mallSno,
        ];
        $config = $this->selectConfigByCode($params);
        $policy = json_decode($config['data'], true);
        $isShared = $config['shareFl'] == 'y';
        $hasPolicy = (empty($policy) === false);

        if ($isShared && !$hasPolicy) {
            return $this->getStandardConfig($name);
        }
        if ($isShared && $hasPolicy) {
            $standard = $this->getStandardConfig($name);
            foreach ($policy as $index => $item) {
                if (key_exists($index, $standard)) {
                    $standard[$index] = $item;
                }
            }

            return $standard;
        }

        return $policy;
    }
}
