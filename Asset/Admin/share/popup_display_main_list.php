<div class="popup-display-main-list">
    <div class="popup-page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
    </div>

    <form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit">
        <input type="hidden" name="detailSearch" value="<?=$search['detailSearch']; ?>"/>
        <input type="hidden" name="popupMode" value="<?=$popupMode;?>">
        <input type="hidden" name="orderBy" value="<?=$orderBy?>">
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
                    <th>검색어</th>
                    <td colspan="3">
                        <div class="form-inline">
                            <?=gd_select_box('key', 'key', $search['cateSearch'], null, $search['key'], null, ''); ?>
                            <input type="text" name="keyword" value="<?=$search['keyword']; ?>" class="form-control"/>
                        </div>
                    </td>
                    <td rowspan="2"><input type="submit" value="검색" class="btn btn-lg btn-black"></td>
                </tr>
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
                    <td class="contents">
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
        </div>
    </form>

    <form id="frmDisplayMainList" action="../goods/display_ps.php" method="get" target="ifrmProcess">
        <input type="hidden" name="mode" value="">
        <input type="hidden" name="goodsNo" value="">
        <table class="table table-rows" style="width:100%; max-width:100%;">
            <thead>
            <tr>
                <th class="width5p"><input type="checkbox" class="js-checkall" data-target-name="sno"/></th>
                <th class="width5p">번호</th>
                <th class="width10p">쇼핑몰 유형
                    <div class="btn-box" style="float: right;">
                        <div style="width:10px; height:10px;">
                            <span <?php if($orderBy == 'dt.mobileFl asc'){ ?> class="sort-btn mobile-up up-on"<?php }else{ ?> class="sort-btn mobile-up up-off" <?php }?> data-sort="dt.mobileFl asc"></span>
                        </div>
                        <div style="width:10px; height:10px;">
                            <span <?php if($orderBy == 'dt.mobileFl desc'){ ?> class="sort-btn mobile-down down-on" <?php }else{ ?> class="sort-btn mobile-down down-off" <?php } ?> data-sort="dt.mobileFl desc"></span>
                        </div>
                    </div>
                </th>
                <th class="width10p">분류명
                    <div class="btn-box">
                        <div style="width:10px; height:10px;">
                            <span <?php if($orderBy == 'dt.themeNm asc'){  ?> class="sort-btn theme-up up-on" <?php }else{ ?> class="sort-btn theme-up up-off" <?php } ?> data-sort="dt.themeNm asc"></span>
                        </div>
                        <div style="width:10px; height:10px;">
                            <span <?php if($orderBy == 'dt.themeNm desc'){ ?> class="sort-btn theme-down down-on" <?php }else{ ?> class="sort-btn theme-down down-off" <?php } ?> data-sort="dt.themeNm desc"></span>
                        </div>
                    </div>
                </th>
                <th class="width15p">분류 설명</th>
                <th class="width10p">노출상태
                    <div class="btn-box">
                        <div style="width:10px; height:10px;">
                            <span <?php if($orderBy == 'dt.displayFl asc'){ ?> class="sort-btn display-up up-on" <?php }else{ ?> class="sort-btn display-up up-off" <?php } ?> data-sort="dt.displayFl asc"></span>
                        </div>
                        <div style="width:10px; height:10px;">
                            <span <?php if($orderBy == 'dt.displayFl desc'){ ?> class="sort-btn display-down down-on" <?php }else{ ?> class="sort-btn display-down down-off" <?php }  ?> data-sort="dt.displayFl desc"></span>
                        </div>
                    </div>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data)) {

                foreach ($data as $key => $val) {
            ?>
                <tr>
                  <td class="center"><input type="checkbox" name="sno[<?=$val['sno']; ?>]" value="<?=$val['sno']; ?>" data-goodsNo="<?=$val['goodsNo']?>"/></td>
                  <td class="center number"><?=number_format($page->idx--); ?></td>
                  <td><?= $val['mobileFl'] == "y" ? "모바일쇼핑몰" : "PC쇼핑몰"; ?></td>
                  <td><?=$val['themeNm']; ?></td>
                  <td class="center"><?=$val['themeDescription']; ?></td>
                  <td class="center"><?= $val['displayFl'] == "y" ? "노출함" : "노출안함"; ?></td>
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

        <div class="center"><?=$page->getPage(); ?></div>

        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>처리방법 선택</th>
                <td>
                    선택된 메인 진열분류를 일괄로
                    <input type="radio" name="displayStatusChk" value="popup_display_change"/>변경
                    <input type="radio" name="displayStatusChk" value="popup_display_add">추가
                    <input type="radio" name="displayStatusChk" value="popup_display_delete">삭제
                    합니다.
                </td>
            </tr>
            </tbody>
        </table>

        <div class="text-center">
            <input type="button" class="btn btn-white js-check-close" value="닫기">
            <input type="button" class="btn btn-black js-check-save" value="적용">
        </div>
    </form>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $('.sort-btn').click(function(){
            var sort = $(this).data('sort');
            $('#frmSearchBase input[name="orderBy"]').val(sort);
            $('#frmSearchBase').submit();
        });

        $('.up-off').click(function () {
            var sort = $(this).data('sort');
            if (sort == 'dt.mobileFl asc'){
                $('.mobile-up').attr('class', 'up-on');
            }else if(sort == 'dt.themeNm asc'){
                $('.theme-up').attr('class', 'up-on');
            }else if(sort == 'dt.displayFl asc'){
                $('.display-up').attr('class', 'up-on');
            }
        });

        $('.down-off').click(function () {
            var sort = $(this).data('sort');
            if (sort == 'dt.mobileFl desc'){
                $('.mobile-down').attr('class', 'down-on');
            }else if(sort == 'dt.themeNm desc'){
                $('.theme-down').attr('class', 'down-on');
            }else if(sort == 'dt.displayFl desc'){
                $('.display-down').attr('class', 'down-on');
            }
        });

        $('.up-on').click(function () {
            var sort = $(this).data('sort');
            if (sort == 'dt.mobileFl asc'){
                $('.mobile-up').attr('class', 'up-on');
            }
        });

        $('.js-check-save').click(function () {
            frmSubmit();
        });

        $('.js-check-close').click(function () {
            close();
        });

        function frmSubmit(){

            // 부모창 상품선택값
            var cntGoodsNo = $('input[name*="goodsNo"]:checked', opener.document).map(function(){
                return this.value;
            }).get().join(',');

            // 자식창 상품진열 선택값
            var sno = $('input[name*="sno["]:checkbox:checked').map(function(){
                return this.value;
            }).get().join(',');

            var chkStatus = $('input[name="displayStatusChk"]:checked').val();
            var chkSnoCnt = $('input[name*="sno["]:checkbox:checked').length;
            var chkStatusLength = $('input:radio[name="displayStatusChk"]').is(':checked');

            if(chkSnoCnt == 0 || chkStatusLength == false){
                alert('메인 진열분류의 처리방법을 선택해 주세요.');
                return false;
            }

            $('input[name="goodsNo"]').attr('value',cntGoodsNo);
            $('input[name="mode"]').attr('value',chkStatus);
            $('input[name="sno"]').attr('value',sno);

            var params = jQuery("#frmDisplayMainList").serializeArray();

            $.ajax({
                method: "POST",
                url: "../goods/display_ps.php",
                data : params,
                success : function(data){
                    window.close();
                    opener.parent.location.reload();
                },
                error: function (data){
                    console.log(data);
                }
            });
        }
    });
    //-->
</script>