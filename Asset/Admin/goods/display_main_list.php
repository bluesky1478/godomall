<script type="text/javascript">
    <!--
    $(document).ready(function () {

        // 삭제
        $('button.checkDelete').click(function () {

            var chkCnt = $('input[name*="sno["]:checkbox:checked').length;
            if (chkCnt == 0) {
                alert('선택된 분류가 없습니다.');
                return;
            }


            dialog_confirm('선택한 ' + chkCnt + '개 분류를 정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('main_delete');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './display_ps.php');
                    $('#frmList').submit();
                }
            });

        });

        // 등록
        $('#checkRegister').click(function () {
            location.href = './display_main_register.php';
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchBase').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchBase').submit();
        });

        $('.js-main-list-modify ').click(function () {
            location.href = './display_main_register.php?sno='+$(this).data('sno');
        });

    });

    /**
     * 테마 수정
     *
     * @param string themeCd 테마코드
     */
    function modify_theme_popup(themeCd)
    {
        window.open('../goods/display_config_theme_register.php?popupMode=yes&themeCate=B&addTheme=themeCd&callFunc=update_theme_info&themeCd='+themeCd, 'theme_popup', 'width=1210, height=700, scrollbars=yes');
    };

    /**
     * 테마 수정 정보 업데이트
     *
     * @param string themeCd 테마코드
     * @param string themeNm 테마명
     */
    function update_theme_info(themeCd,themeNm)
    {
        $(".js-theme-"+themeCd).html(themeNm);
    };

    //-->
</script>

<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?>
    </h3>
    <div class="btn-group">
        <input type="button" id="checkRegister" value="메인페이지 분류 등록" class="btn btn-red-line"/>

    </div>
</div>

<form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?=$search['detailSearch']; ?>"/>

    <div class="table-title gd-help-manual">
        분류 검색
    </div>
    <div  class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>분류명</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?=gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null, ''); ?>
                        <input type="text" name="keyword" value="<?=$search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3"><div class="form-inline">
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

                        <?=gd_search_date($search['searchPeriod'], 'searchDate', true)?>
                    </div>
                </td>
            </tr>
            </tbody>
            <tbody class="js-search-detail" style="display: none;">
            <tr>
                <th>쇼핑몰 유형</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="mobileFl"
                                                       value="all" <?=gd_isset($checked['mobileFl']['all']); ?>/>전체</label>
                    <label class="radio-inline"><input type="radio" name="mobileFl"
                                                       value="n" <?=gd_isset($checked['mobileFl']['n']); ?>/>PC쇼핑몰</label>
                    <label class="radio-inline"><input type="radio" name="mobileFl"
                                                       value="y" <?=gd_isset($checked['mobileFl']['y']); ?>/>모바일쇼핑몰</label>
                </td>
                <th>노출상태</th>
                <td class="contents" colspan="3">
                    <label class="radio-inline"><input type="radio" name="displayFl"
                                                       value="all" <?=gd_isset($checked['displayFl']['all']); ?>/>전체</label>
                    <label class="radio-inline"><input type="radio" name="displayFl"
                                                       value="y" <?=gd_isset($checked['displayFl']['y']); ?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="displayFl"
                                                       value="n" <?=gd_isset($checked['displayFl']['n']); ?>/>노출안함</label>
                </td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-link js-search-toggle bold">상세검색 <span>펼침</span></button>
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
            <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
            <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>

</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width5p"><input type="checkbox" class="js-checkall" data-target-name="sno"/></th>
            <th class="width5p">번호</th>
            <th class="width10p">쇼핑몰 유형</th>
            <th class="width10p">분류명</th>
            <th class="width15p">분류 설명</th>
            <th class="width10p">선택테마</th>
            <th class="width10p">노출상태</th>
            <th class="width15p">등록일</th>
            <th class="width5p">상품진열</th>
            <th class="width5p">코드복사</th>
            <th class="width10p">치환코드</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {

            foreach ($data as $key => $val) {
                ?>

                <tr>
                    <td class="center"><input type="checkbox" name="sno[<?=$val['sno']; ?>]"
                                              value="<?=$val['sno']; ?>"/></td>
                    <td class="center number"><?=number_format($page->idx--); ?></td>
                    <td><?= $val['mobileFl'] == "y" ? "모바일쇼핑몰" : "PC쇼핑몰"; ?></td>
                    <td><?=$val['themeNm']; ?></td>

                    <td onclick="show_popup('./display_main_register.php?popupMode=yes&sno=<?=$val['sno']; ?>')" class="hand center">
                        <?=$val['themeDescription']; ?></a>
                    </td>
                    <td class="center">
                        <a href="#" onclick="modify_theme_popup('<?=$val['themeCd']; ?>')" style="background:none;" class="js-theme-<?=$val['themeCd']; ?>"><?=$val['displayThemeNm']; ?></a>
                        <input type="hidden" name="themeCd[<?=$val['sno']; ?>]" value="<?=$val['themeCd']; ?>"/>
                    </td>
                    <td class="center"><?= $val['displayFl'] == "y" ? "노출함" : "노출안함"; ?></td>
                    <td class="center date"><?=gd_date_format('Y-m-d', $val['regDt']); ?><?php if ($val['modDt']) {
                            echo "<br/>" . gd_date_format('Y-m-d', $val['modDt']);
                        } ?></td>
                    <td class="center">
                        <input type="button"  class="btn js-main-list-modify btn-white btn-xs" value="상품진열" data-sno="<?=$val['sno']; ?>"></td>
                    <td class="center"><button type="button"
                                               data-clipboard-target="#tblMainCode<?= $val['sno'] ?>"
                                               class="js-clipboard btn btn-white btn-xs" title="<?=$val['themeNm']; ?> 치환코드">복사
                        </button></td>
                    <td id="tblMainCode<?= $val['sno'] ?>">{=includeWidget('goods/_goods_display_main.html','sno','<?= $val['sno'] ?>')}</td>
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
            <button type="button" class="btn btn-white checkDelete">선택 삭제</button>
        </div>
        <div class="pull-right">
            <!-- <button type="button" class="btn btn-white btn-icon-excel">엑셀다운로드</button> -->
        </div>
    </div>

</form>

<div class="center"><?=$page->getPage(); ?></div>
<script>
    $( ".btn-white" ).mouseover(function() {
        $(this).css("border-color", "#666666")
    });
    $( ".btn-white" ).mouseout(function() {
        $(this).css("border-color", "#CCCCCC");
    });
</script>
