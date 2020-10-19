<form id="frmSearchGift" name="frmSearchGift" method="get" class="mgt10">
    <input type="hidden" name="condition" value="<?=$condition?>" />
    <input type="hidden" name="detailSearch" value="<?=$search['detailSearch'];?>" />
    <input type="hidden" name="sort" />
    <input type="hidden" name="pageNum" />
    <input type="hidden" name="pagelink" />
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-xs"/>
                <col/>
            </colgroup>
            <tbody>
            <!--
            <tr>
                <th>공급사 구분</th>
                <td>
                    <div class="form-inline">
                        <input type="hidden" name="scmFl" value="<?=$search['scmFl']?>"  >
                        <label><input type="radio" name="scmFlChk"
                                      value="n" <?=gd_isset($checked['scmFl']['n']); ?>disabled = "disabled"  />본사</label>
                        <label><input type="radio" name="scmFlChk"
                                      value="y" <?=gd_isset($checked['scmFl']['y']); ?>

                                      onclick="layer_register('scm', 'checkbox')" disabled = "disabled"/>공급사
                           </label>


                        <div id="scmLayer" class="width100p ">
                            <?php if ($search['scmFl'] == 'y') {
                                foreach ($search['scmNo'] as $k => $v) { ?>
                                    <span id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                <span class="btn"><?= $search['scmNoNm'][$k] ?></span>

                                </span>

                                <?php }
                            } ?>
                        </div>

                    </div>
                </td>
            </tr> -->
            <tr>
                <th>검색어</th>
                <td>
                    <div class="form-inline">
                        <?=gd_select_box('key', 'key', array('all' => '=통합검색=', 'giftNm' => '사은품명', 'giftNo' => '고유키값', 'giftCd' => '사은품코드'), null, gd_isset($search['key']), null); ?>
                        <input type="text" name="keyword" value="<?=gd_isset($search['keyword']); ?>"
                               class="form-control width40p"/>
                    </div>
                </td>
            </tr>

            <tr>
                <th>기간검색</th>
                <td>
                    <div class="form-inline">
                        <select name="searchDateFl" class="form-control">
                            <option value="regDt" <?=gd_isset($selected['searchDateFl']['regDt']); ?>>등록일
                            </option>
                            <option value="modDt" <?=gd_isset($selected['searchDateFl']['modDt']); ?>>수정일
                            </option>
                        </select>

                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]"
                                   value="<?=$search['searchDate'][0]; ?>">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                        </div>

                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]"
                                   value="<?=$search['searchDate'][1]; ?>">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                        </div>


                </td>
            </tr>
            </tbody>
            <tbody class="js-search-detail" class="display-none">
            <tr>
                <th>브랜드</th>
                <td> <div class="form-inline"><?=$brand->getMultiCategoryBox(null, gd_isset($search['brand']), 'class="form-control"'); ?>
                </div></td>
            </tr>
            <tr>
                <th>품절여부</th>
                <td>
                    <label><input type="radio" name="stockFl"
                                  value="all" <?=gd_isset($checked['stockFl']['all']); ?> />전체</label>
                    <label><input type="radio" name="stockFl"
                                  value="n" <?=gd_isset($checked['stockFl']['n']); ?> />무제한</label>
                    <label><input type="radio" name="stockFl"
                                  value="y" <?=gd_isset($checked['stockFl']['y']); ?> />재고사용(재고있음)</label>
                    <label><input type="radio" name="stockFl"
                                  value="x" <?=gd_isset($checked['stockFl']['x']); ?> />재고사용(재고없음)</label>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <!--
    <button type="button" class="btn btn-sm btn-link js-search-toggle">상세검색
        <span>펼침</span></button> -->
    <div class="table-btn">
        <input type="button" value="검색" class="btn btn-lg btn-black"  onclick="layer_list_search();">
    </div>
</form>

<div class="table-header">
    <div class="pull-right">
        <ul>
            <li>
                <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null, null, 'form-control width-xs'); ?>
            </li>
            <li>
                <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
            </li>
        </ul>
    </div>
</div>

<table class="table table-rows">
    <thead>
    <tr>
        <th class="width5p center"><input type="checkbox" id="allCheck" value="y"
                                    onclick="check_toggle(this.id,'gift_');"/></th>
        <th class="width8p center">번호</th>
        <th class="width50p">상품명</th>
        <th class="width20p">등록일</th>
        <th class="width10p">재고</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (gd_isset($data) && is_array($data)) {
        $i = 0;
        foreach ($data as $key => $val) {
            if ($val['stockFl'] == 'n') {
                $strStockFl = '무제한';
            } else {
                if ($val['stockCnt'] > 0) {
                    $strStockFl = number_format($val['stockCnt']);
                } else {
                    $strStockFl = '품절';
                }
            }
            ?>
            <tr>
                <td class="center"><input type="checkbox" id="gift_<?=$val['giftNo']; ?>"
                                          name="gift_<?=$i; ?>" value="<?=$val['giftNo']; ?>"/></td>
                <td class="center"><?=number_format($page->idx--); ?></td>
                <td>
                    <?=gd_html_gift_image($val['imageNm'], $val['imagePath'], $val['imageStorage'], 30, $val['giftNm'], 'id="giftImage_' . $val['giftNo'] . '"'); ?>
                    <?=$val['giftNm']; ?>
                    <input type="hidden" id="giftNm_<?=$val['giftNo']; ?>"
                           value="<?=gd_htmlspecialchars($val['giftNm']); ?>"/>
                </td>
                <td><?=gd_date_format('Y-m-d', $val['regDt']); ?></td>
                <td><?=$strStockFl; ?></td>
            </tr>
            <?php
            $i++;
        }
    } else {
        ?>
        <tr>
            <td class="no-data" colspan="8">검색된 사은품이 없습니다.</td>
        </tr>
        <?php
    }
    ?>

    </tbody>
</table>

<div class="text-center"><?=$page->getPage('layer_list_search(\'PAGELINK\')'); ?></div>

<div class="text-center"><input type="button" value="확인" class="btn btn-lg btn-black" onclick="select_code();" /></div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $('input').keydown(function(e) {
            if (e.keyCode == 13) {
                layer_list_search();
                return false
            }
        });

        init_datetimepicker();

        // 등록
        $('#btn_save_all').click(function () {
            $("#allCheck").click();
            select_code();
        });

        $('select[name=\'pageNum\']').change(function () {
            $('input[name=\'pageNum\']').val($(this).val());
            layer_list_search();
        });

        $('select[name=\'sort\']').change(function () {
            $('input[name=\'sort\']').val($(this).val());
            layer_list_search();
        });
    });

    function layer_list_search(pagelink) {
        $('input[name=\'pagelink\']').val(pagelink);
        var parameters =  $("#frmSearchGift").serialize();

        $.get('layer_gift_select.php', parameters, function (data) {
            $('#addPresentForm').html(data);
        });
    }

    var arrCodeID = '<?=$condition;?>'.split('_');
    var codeString = arrCodeID[1] + '_' + arrCodeID[2];
    var codeID = arrCodeID[1] + 'GiftNo';
    var modeID = arrCodeID[1];
    var modeString = codeID + '_' + arrCodeID[2];

    function select_code() {
        var checkboxCnt = $('input[id*=\'gift_\']').length;
        var applyGiftCnt = 0;
        var chkGiftCnt = 0;
        var selectGiftCnt = $('tr[id*="' + codeString + '"]:last-child');
        var giftIndex = 0;

        if (selectGiftCnt.length > 0) {
            giftIndex = parseInt(selectGiftCnt.children('td:first-child').text());
        }

        for (var i = 0; i < checkboxCnt; i++) {
            if ($('input[name=\'gift_' + i + '\']:checked').length == 1) {
                var giftNo = $('input[name=\'gift_' + i + '\']').val();
                var giftNm = $('#giftNm_' + giftNo).val();
                var giftImg = $('#giftImage_' + giftNo).attr('src');

                if ($('#' + codeString + '_' + giftNo).length == 0) {
                    applyGiftCnt++;

                    var addHtml = '';
                    addHtml += '<tr id="' + codeString + '_' + giftNo + '">';
                    addHtml += '<td class="center">' + (giftIndex + applyGiftCnt) + '<input type="hidden" name="gift[' + codeID + '][' + arrCodeID[2] + '][]" value="' + giftNo + '" /></td>';
                    addHtml += '<td class="center"><img src="' + giftImg + '" align="absmiddle" width="50" alt="' + giftNm + '" title="' + giftNm + '" /></td>';
                    addHtml += '<td >' + giftNm + '</td>';
                    addHtml += '<td class="center"><input type="button" onclick="field_remove(\'' + codeString + '_' + giftNo + '\');';
                    if (modeID == 'multi') {
                        addHtml += 'mode_selectbox_reset(modeString,modeID);';
                    }
                    addHtml += '" value="삭제" class="btn btn-sm btn-gray"/></td>';
                    addHtml += '</tr>';

                    $('#<?=$condition;?>').append(addHtml);
                }

                chkGiftCnt++;
            }
        }

        if (modeID == 'multi') {
            mode_selectbox_reset(modeString, modeID);
        }

        if (applyGiftCnt > 0) {
            if (applyGiftCnt != chkGiftCnt) {
                alert('선택한 ' + chkGiftCnt + '개의 사은품중 ' + applyGiftCnt + '개의 사은품이 추가 되었습니다.');
            }

            $('div.bootstrap-dialog-close-button').click();
        } else {
            alert('동일한 사은품이 이미 존재합니다.');
        }
    }
    //-->
</script>
