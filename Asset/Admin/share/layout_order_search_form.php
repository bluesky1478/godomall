<?php
/**
 * 주문리스트내 검색 폼 레이아웃
 *
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */
?>

<!-- 검색을 위한 form -->
<form id="frmSearchOrder" method="get">
    <input type="hidden" name="detailSearch" value="<?= $search['detailSearch']; ?>"/>
    <input type="hidden" name="checkType" value="<?= $checkType; ?>"/>

    <div class="table-title">
        주문 내역
    </div>

    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm">
                <col>
                <col class="width-sm">
                <col>
            </colgroup>
            <tbody>
            <?php if(gd_use_provider() === true) { ?>
            <?php if (!isset($isProvider) && $isProvider != true) { ?>
            <tr>
                <th>공급사 구분</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="all" <?= gd_isset($checked['scmFl']['all']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="0" <?= gd_isset($checked['scmFl']['0']); ?>/>본사
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="1" class="js-layer-register" <?= gd_isset($checked['scmFl']['1']); ?> data-type="scm" data-mode="checkbox"/> 공급사
                    </label>
                    <input type="button" value="공급사 선택" class="btn btn-sm btn-gray js-layer-register" data-type="scm" data-mode="search"/>

                    <div id="scmLayer" class="selected-btn-group <?=$search['scmFl'] == '1' && !empty($search['scmNo']) ? 'active' : ''?>">
                        <h5>선택된 공급사 : </h5>
                        <?php if ($search['scmFl'] == '1' && empty($search['scmNo']) === false) { ?>
                            <?php foreach ($search['scmNo'] as $k => $v) { ?>
                                <div id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                    <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                    <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                    <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <?php } ?>
            <?php } ?>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null, null, 'form-control '); ?>
                        <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('treatDateFl', 'treatDateFl', $search['combineTreatDate'], null, $search['treatDateFl'], null, null, 'form-control '); ?>
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[]" value="<?= $search['treatDate'][0]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[]" value="<?= $search['treatDate'][1]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>

                        <div class="btn-group js-dateperiod" data-toggle="buttons" data-target-name="treatDate[]">
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="0">오늘
                            </label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="6">7일
                            </label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="14">15일
                            </label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="29">1개월
                            </label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="89">3개월
                            </label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="364">1년
                            </label>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>


    <div class="table-header">
        <div class="pull-left">
            검색 <strong class="text-danger"><?= number_format(gd_isset($page->recode['total'], 0)); ?></strong>개 /
            전체 <strong class="text-danger"><?= number_format(gd_isset($page->recode['amount'], 0)); ?></strong>개
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                <?= gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10,20,30,40,50,60,70,80,90,100,200,300,500,]), '개 보기', $page->page['list']); ?>
            </div>
        </div>
    </div>
    <input type="hidden" name="searchFl" value="y">
    <input type="hidden" name="applyPath" value="<?=gd_php_self()?>">
</form>
<!-- // 검색을 위한 form -->
