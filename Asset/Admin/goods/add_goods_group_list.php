<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 삭제
        $('button.checkDelete').click(function () {
            var chkCnt = $('input[name*="sno["]:checkbox:checked').length;
            if (chkCnt == 0) {
                alert('선택된 그룹이 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개 그룹을  정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('group_delete');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './add_goods_ps.php');
                    $('#frmList').submit();
                }
            });

        });

        $('button.checkCopy').click(function () {
            var chkCnt = $('input[name*="sno["]:checkbox:checked').length;
            if (chkCnt == 0) {
                alert('선택된 그룹이 없습니다.');
                return;
            }
            dialog_confirm('선택한 ' + chkCnt + '개 그룹을  정말로 복사하시겠습니까?', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('group_copy');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './add_goods_ps.php');
                    $('#frmList').submit();
                }
            });

        });

        // 등록
        $('#checkRegister').click(function () {
            location.href = './add_goods_group_register.php';
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchBase').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchBase').submit();
        });
    });

    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
        };

        if (typeStr == 'scm') {
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }
    //-->
</script>

<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <input type="button" id="checkRegister" value="추가상품 그룹 등록" class="btn btn-red-line" />
    </div>
</div>

<form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit">
    <div class="table-title gd-help-manual">
        추가상품 그룹 검색
    </div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <?php if(gd_use_provider() === true) { ?>
            <?php if(gd_is_provider() === false) { ?>
            <tr>
                <th>공급사 구분</th>
                <td colspan="3">
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="all" <?=gd_isset($checked['scmFl']['all']); ?> onclick="$('#scmLayer').html('');"/>전체
                        </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="n" <?=gd_isset($checked['scmFl']['n']); ?> onclick="$('#scmLayer').html('')" ;/>본사
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?> onclick="layer_register('scm','checkbox')"/>공급사
                    </label>
                    <label>
                        <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm','checkbox')">공급사 선택</button>
                    </label>

                    <div id="scmLayer" class="selected-btn-group <?=$search['scmFl'] == 'y' && !empty($search['scmNo']) ? 'active' : ''?>">
                        <h5>선택된 공급사 : </h5>
                        <?php if ($search['scmFl'] == 'y') {
                            foreach ($search['scmNo'] as $k => $v) { ?>
                                <span id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>
                                </span>
                            <?php }
                        } ?>
                    </div>
                </td>
            </tr>
            <?php } ?>
            <?php } ?>
            <tr>
                <th>검색어</th>
                <td><div class="form-inline">
                        <?=gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                        <input type="text" name="keyword" value="<?=$search['keyword']; ?>" class="form-control"/>
                        </div>
                </td>
            </tr>
            <tr>
                <th >기간검색</th>
                <td> <div class="form-inline">
                        <select name="searchDateFl" class="form-control">
                            <option value="regDt" <?=gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
                            <option value="modDt" <?=gd_isset($selected['searchDateFl']['modDt']); ?>>수정일</option>
                        </select>

                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][0]; ?>" >
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                        </div>

                        ~  <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][1]; ?>" >
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                        </div>
                        <?=gd_search_date($search['searchPeriod'])?>
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
            검색 <strong><?=number_format($page->recode['total']);?></strong>개 /
            전체 <strong><?=number_format($page->recode['amount']);?></strong>개
        </div>
        <div class="pull-right form-inline">
            <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
            <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>

</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
        <table class="table table-rows">
            <thead>
        <tr>
            <th class="width3p center"><input type="checkbox" class="js-checkall" data-target-name="sno"></th>
            <th class="width5p">번호</th>
            <th class="width10p">그룹코드</th>
            <th >그룹명</th>
            <th class="width15p">공급사</th>
            <th class="width10p">추가상품 개수</th>
            <th class="width10p">등록일/수정일</th>
            <th class="width5p">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {

            foreach ($data as $key => $val) {
                ?>

                <tr>
                    <td class="center"><input type="checkbox" name="sno[<?=$val['sno']; ?>]" value="<?=$val['sno']; ?>"/></td>
                    <td class="center number"><?=number_format($page->idx--); ?></td>
                    <td  class="center"><?=$val['groupCd']; ?><input type="hidden" name="groupCd[<?=$val['sno']; ?>]" value="<?=$val['groupCd']?>"></td>
                    <td onclick="show_popup('./add_goods_group_register.php?popupMode=yes&sno=<?=$val['sno']; ?>')" class="hand"><?=$val['groupNm']; ?></td>
                    <td  class="center"><?=$val['scmNm']; ?></td>
                    <td class="center"><?=$val['addGoodsCnt']; ?></td>
                    <td class="center date"><?=gd_date_format('Y-m-d', $val['regDt']); ?><?php if ($val['modDt']) {
                            echo "<br/>" . gd_date_format('Y-m-d', $val['modDt']);
                        } ?></td>
                    <td class="center padlr10"><a
                                href="./add_goods_group_register.php?sno=<?=$val['sno']; ?>" class="btn btn-white btn-xs">수정</a></span>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="11">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white checkCopy">선택 복사</button>
            <button type="button" class="btn btn-white checkDelete">선택 삭제</button>
        </div>
    </div>

</form>

<div class="center"><?=$page->getPage(); ?></div>
