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

namespace Bundle\Component\Agreement;

/**
 * Class BuyerInformVo
 * @package Bundle\Component\Agreement
 * @author  yjwee
 */
class BuyerInformVo
{
    private $sno;
    private $scmNo;
    private $informCd;
    private $groupCd;
    private $informNm;
    private $modeFl;
    private $content;
    private $regDt;
    private $modDt;
    private $displayShopFl;

    function __construct($code = null)
    {
        if (is_null($code) === false) {
            $code = new BuyerInformCode($code);
            $this->setInformCd($code->getInformCd());
            $this->setGroupCd($code->getGroupCd());
            $this->setInformNm($code->getInformNm());
        }
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getInformCd()
    {
        return $this->informCd;
    }

    /**
     * @param mixed $informCd
     */
    public function setInformCd($informCd)
    {
        $this->informCd = $informCd;
    }

    /**
     * @return mixed
     */
    public function getGroupCd()
    {
        return $this->groupCd;
    }

    /**
     * @param mixed $groupCd
     */
    public function setGroupCd($groupCd)
    {
        $this->groupCd = $groupCd;
    }

    /**
     * @return mixed
     */
    public function getInformNm()
    {
        return $this->informNm;
    }

    /**
     * @param mixed $informNm
     */
    public function setInformNm($informNm)
    {
        $this->informNm = $informNm;
    }

    /**
     * @return mixed
     */
    public function getModDt()
    {
        return $this->modDt;
    }

    /**
     * @param mixed $modDt
     */
    public function setModDt($modDt)
    {
        $this->modDt = $modDt;
    }

    /**
     * @return mixed
     */
    public function getModeFl()
    {
        return $this->modeFl;
    }

    /**
     * @param mixed $modeFl
     */
    public function setModeFl($modeFl)
    {
        gd_isset($modeFl, 'y');
        $this->modeFl = $modeFl;
    }

    /**
     * @return mixed
     */
    public function getRegDt()
    {
        return $this->regDt;
    }

    /**
     * @param mixed $regDt
     */
    public function setRegDt($regDt)
    {
        $this->regDt = $regDt;
    }

    /**
     * @return mixed
     */
    public function getScmNo()
    {
        return $this->scmNo;
    }

    /**
     * @param mixed $scmNo
     */
    public function setScmNo($scmNo)
    {
        $this->scmNo = $scmNo;
    }

    /**
     * @return mixed
     */
    public function getSno()
    {
        return $this->sno;
    }

    /**
     * @param mixed $sno
     */
    public function setSno($sno)
    {
        $this->sno = $sno;
    }

    /**
     * @return mixed
     */
    public function getDisplayShopFl()
    {
        return $this->displayShopFl;
    }

    /**
     * @param mixed $displayShopFl
     */
    public function setDisplayShopFl($displayShopFl)
    {
        $this->displayShopFl = $displayShopFl;
    }

    /**
     * object return array
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
