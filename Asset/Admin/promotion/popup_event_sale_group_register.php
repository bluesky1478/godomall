<form id="frmEventSaleGoods" name="frmEventSaleGoods" target="ifrmProcess" action="../goods/display_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?php echo $data['mode']; ?>" />
    <input type="hidden" name="loadType" value="<?php echo $eventGroupGetInfo['loadType']; ?>" />
    <input type="hidden" name="eventGroupSno" value="<?php echo $eventGroupGetInfo['eventGroupSno']; ?>" />
    <div class="page-header">
        <h3>기획전 그룹 <?php echo $popupTitle; ?></h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <!-- 기본 정보 -->
    <div class="table-title gd-help-manual">
        기본정보
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">그룹명</th>
            <td colspan="3">
                <label title=""><input type="text" name="groupName" id="eventGroupName" value="<?= gd_isset($data['groupName']); ?>" class="form-control js-maxlength" maxlength="30"/></label>
            </td>
        </tr>
        <tr>
            <th>그룹이미지</th>
            <td colspan="3">
                <div class="notice-info">이미지 등록 시 그룹명을 텍스트 대신 이미지로 노출합니다.</div>
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>PC 쇼핑몰</th>
                        <td>
                            <div class="form-inline">
                                <input type="file" name="groupNameImagePc" />
                                <?php
                                if(trim($data['groupNameImagePcTag']) !== ''){
                                    echo $data['groupNameImagePcTag'];
                                ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="deleteImagePc" value="<?php echo $data['groupNameImagePc']; ?>" /> 삭제
                                    </label>
                                <?php
                                }
                                ?>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th>모바일 쇼핑몰</th>
                        <td>
                            <div class="form-inline">
                                <input type="file" name="groupNameImageMobile" />
                                <?php
                                if(trim($data['groupNameImageMobileTag']) !== ''){
                                    echo $data['groupNameImageMobileTag'];
                                    ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="deleteImageMobile" value="<?php echo $data['groupNameImageMobile']; ?>" /> 삭제
                                    </label>
                                    <?php
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th>진열방법선택</th>
            <td colspan="3">
                <div class="form-inline">
                    <?= gd_select_box('groupSort', 'groupSort', $data['sortList'], null, $data['groupSort'], null); ?>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">PC쇼핑몰 테마선택</th>
            <td>
                <div class="form-inline">
                    <input type="hidden" name="themeCdChk" value="<?= $data['groupThemeCd'] ?>">
                    <select name="groupThemeCd" onchange="viewThemeConfig(this.value, 'p');" class="form-control input-sm"></select>
                    <input type="button" class="btn btn-sm btn-gray" value="테마등록" onclick="add_theme_popup('n')"/>
                </div>
            </td>
            <th class="require">모바일 쇼핑몰 테마선택</th>
            <td>
                <div class="form-inline">
                    <input type="hidden" name="mobileThemeCdChk" value="<?= $data['groupMobileThemeCd'] ?>">
                    <select name="groupMobileThemeCd" onchange="viewThemeConfig(this.value, 'm');" class="form-control input-sm"></select>
                    <input type="button" class="btn btn-sm btn-gray" value="테마등록" onclick="add_theme_popup('y')"/>
                </div>
            </td>
        </tr>
        <tr>
            <th>상단 더보기 노출 상태</th>
            <td> <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="groupMoreTopFl" value="y" <?=gd_isset($checked['groupMoreTopFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="groupMoreTopFl" value="n"  <?=gd_isset($checked['groupMoreTopFl']['n']);?>/>노출안함</label>
                </div>
            </td>
            <th>하단 더보기 노출 상태</th>
            <td><div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="groupMoreBottomFl" value="y" <?=gd_isset($checked['groupMoreBottomFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="groupMoreBottomFl" value="n"  <?=gd_isset($checked['groupMoreBottomFl']['n']);?>/>노출안함</label>
                </div>
            </td>
        </tr>
    </table>
    <!-- 기본 정보 -->

    <!-- 선택된 PC 쇼핑몰 테마정보 -->
    <div class="theme-info" id="event-group-pc-theme-info">
        <div class="table-title gd-help-manual">
            선택된 PC쇼핑몰 테마정보
            <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="event-group-pc-theme-info"><span>닫힘</span></button></span>
        </div>
        <input type="hidden" id="depth-toggle-hidden-event-group-pc-theme-info" value="<?=$toggle['event-group-pc-theme-info_'.$SessScmNo]?>">
        <div id="depth-toggle-line-event-group-pc-theme-info" class="depth-toggle-line display-none"></div>
        <div id="depth-toggle-layer-event-group-pc-theme-info">
            <table class="table table-cols" id="event-group-pc-theme-info">
                <colgroup>
                    <col class="width-md" />
                    <col />
                    <col class="width-md" />
                    <col />
                </colgroup>
                <tr>
                    <th>이미지 설정</th>
                    <td colspan="3" id="tbl_p_theme_imageCdNm"></td>
                </tr>
                <tr>
                    <th>상품 노출 개수</th>
                    <td colspan="3" id="tbl_p_theme_cntNm"></td>
                </tr>
                <tr>
                    <th>품절상품 노출</th>
                    <td id="tbl_p_theme_soldOutFlNm"></td>
                    <th>품절상품 진열 상품</th>
                    <td id="tbl_p_theme_soldOutDisplayFlNm"></td>
                </tr>
                <tr>
                    <th>품절 아이콘 노출</th>
                    <td id="tbl_p_theme_soldOutIconFlNm"></td>
                    <th>아이콘 노출</th>
                    <td id="tbl_p_theme_iconFlNm"></td>
                </tr>
                <tr>
                    <th>노출항목 설정</th>
                    <td colspan="3" id="tbl_p_theme_displayFieldNm"></td>
                </tr>
                <tr>
                    <th>갤러리형</th>
                    <td colspan="3" id="tbl_p_theme_displayTypeNm"></td>
                </tr>
            </table>
        </div>
    </div>
    <!-- 선택된 PC 쇼핑몰 테마정보 -->

    <!-- 선택된 모바일 쇼핑몰 테마정보 -->
    <div class="theme-info" id="event-group-mobile-theme-info">
        <div class="table-title gd-help-manual">
            선택된 모바일 쇼핑몰 테마정보
            <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="event-group-mobile-theme-info"><span>닫힘</span></button></span>
        </div>
        <div>
            <input type="hidden" id="depth-toggle-hidden-event-group-mobile-theme-info" value="<?=$toggle['event-group-mobile-theme-info_'.$SessScmNo]?>">
            <div id="depth-toggle-line-event-group-mobile-theme-info" class="depth-toggle-line display-none"></div>
            <div id="depth-toggle-layer-event-group-mobile-theme-info">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md" />
                        <col/>
                        <col class="width-md" />
                        <col/>
                    </colgroup>
                    <tr>
                        <th>이미지 설정</th>
                        <td colspan="3" id="tbl_m_theme_imageCdNm"></td>
                    </tr>
                    <tr>
                        <th>상품 노출 개수</th>
                        <td colspan="3" id="tbl_m_theme_cntNm"></td>
                    </tr>
                    <tr>
                        <th>품절상품 노출</th>
                        <td id="tbl_m_theme_soldOutFlNm"></td>
                        <th>품절상품 진열 상품</th>
                        <td id="tbl_m_theme_soldOutDisplayFlNm"></td>
                    </tr>
                    <tr>
                        <th>품절 아이콘 노출</th>
                        <td id="tbl_m_theme_soldOutIconFlNm"></td>
                        <th>아이콘 노출</th>
                        <td id="tbl_m_theme_iconFlNm"></td>
                    </tr>
                    <tr>
                        <th>노출항목 설정</th>
                        <td colspan="3" id="tbl_m_theme_displayFieldNm"></td>
                    </tr>
                    <tr>
                        <th>갤러리형</th>
                        <td colspan="3" id="tbl_m_theme_displayTypeNm"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <!-- 선택된 모바일 쇼핑몰 테마정보 -->

    <!-- 진열 상품 설정 -->
    <div class="form-inline">
        <div class="table-title gd-help-manual flo-left">
            진열 상품 설정
        </div>
        <?php if($data['mode'] === 'event_group_modify'){ ?>
            <div class="flo-right">
                <input type="button" class="btn btn-sm btn-gray js-goods-price-modify" value="빠른 가격 수정" />
                <input type="button" class="btn btn-sm btn-gray js-goods-mileage-modify" value="빠른 마일리지/할인 수정" />
            </div>
        <?php } ?>
    </div>
    <div>
        <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_set" class="table table-rows">
            <thead>
            <tr id="goodsRegisteredTrArea">
                <th class="width2p"><input type="checkbox" id="allCheck" value="y" class="js-checkall" data-target-name="itemGoodsNo[]"/></th>
                <th class="width2p center">진열순서</th>
                <th class="width5p center">이미지</th>
                <th>상품명</th>
                <th class="width10p center">판매가</th>
                <th class="width10p center">공급사</th>
                <th class="width5p center">재고</th>
                <th class="width5p center">품절여부</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (count(gd_isset($data['goodsNo']))) {
                $cnt = 1;
                foreach ($data['goodsNo'] as $key => $val) {
                    $val = $val[0];
                    $stockText = "";

                    if ($val['soldOutFl'] == 'y') {
                        $stockText = "품절";
                    }
                    else {
                        $stockText = "정상";
                    }

                    // 상품 재고
                    if ($val['stockFl'] == 'n') {
                        $totalStock = '∞';
                    }
                    else {
                        $totalStock = number_format($val['totalStock']);
                        if ($val['totalStock'] == 0) {
                            $stockText = "품절";
                        }
                    }
                    ?>
                    <tr id="tbl_add_goods_<?= $val['goodsNo']; ?>"
                        <?php if ($data['fixGoodsNo'] && in_array($val['goodsNo'], array_values($data['fixGoodsNo']))) { ?>style='background:#d3d3d3' class="add_goods_fix"
                        <?php } else { ?>class="add_goods_free" <?php } ?>>
                        <td class="center">
                            <input type="hidden" name="itemGoodsNm[]" value="<?= strip_tags($val['goodsNm']) ?>"/>
                            <input type="hidden" name="itemGoodsPrice[]" value="<?= gd_currency_display($val['goodsPrice']) ?>"/>
                            <input type="hidden" name="itemScmNm[]" value="<?= $val['scmNm'] ?>"/>
                            <input type="hidden" name="itemTotalStock[]" value="<?= $val['totalStock'] ?>"/>
                            <input type="hidden" name="itemImage[]"
                                   value="<?= rawurlencode(gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank')); ?>"/>
                            <input type="hidden" name="itemBrandNm[]" value="<?= gd_isset($val['brandNm']) ?>"/>
                            <input type="hidden" name="itemMakerNm[]" value="<?= gd_isset($val['makerNm']) ?>"/>
                            <input type="hidden" name="itemSoldOutFl[]" value="<?= gd_isset($val['soldOutFl']) ?>"/>
                            <input type="hidden" name="itemStockFl[]" value="<?= gd_isset($val['stockFl']) ?>"/>
                            <input type="checkbox" name="itemGoodsNo[]" id="layer_goods_<?= $val['goodsNo']; ?>" value="<?= $val['goodsNo']; ?>"/></td>
                        <td class="center number" id="addGoodsNumber_<?= $val['goodsNo']; ?>"><?= $cnt++ ?></td>
                        <td class="center"><?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?></td>
                        <td>
                            <a href="../goods/goods_register.php?goodsNo=<?= $val['goodsNo'] ?>" target="_blank"><?= $val['goodsNm'] ?></a>
                            <input type="hidden" name="goodsNoData[]" value="<?= $val['goodsNo'] ?>"/>
                            <input type="checkbox" name="sortFix[]" id="layer_sort_fix_<?= $val['goodsNo']; ?>"
                                   value="<?= $val['goodsNo']; ?>" <?php if ($data['fixGoodsNo'] && in_array($val['goodsNo'], $data['fixGoodsNo'])) {
                                echo "checked='true'";
                            } ?> style="display:none">
                        </td>
                        <td class="center"><?= gd_currency_display($val['goodsPrice']) ?></td>
                        <td class="center"><?= $val['scmNm']; ?></td>
                        <td class="center"><?= $totalStock ?></td>
                        <td class="center"><?= $stockText ?></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr id="tbl_add_goods_tr_none">
                    <td colspan="11" class="no-data">선택된 상품이 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>

        <div class="table-action">
            <div class="pull-left">
                <button class="btn btn-white checkDelete" type="button" onclick="delete_option()">선택 삭제</button>
            </div>
            <div class="pull-right">
                <button class="btn btn-white checkRegister" type="button" onclick="goods_search_popup()">상품 선택</button>
            </div>
        </div>
    </div>
    <!-- 진열 상품 설정 -->
</form>

<script type="text/javascript">
<!--
    $(document).ready(function () {
        $('#frmEventSaleGoods').submit(function() {
            if($.trim($("#eventGroupName").val()) === ''){
                alert("그룹명을 입력해 주세요.");
                $("#eventGroupName").focus();
                return false;
            }

            if ($('input[name="itemGoodsNo[]"]').length == 0) {
                alert("상품을 선택해 주세요.");
                return false;
            }

            if($.trim($("select[name='groupThemeCd']").val()) === ''){
                alert("PC 테마를 선택해 주세요.");
                return false;
            }

            if($.trim($("select[name='groupMobileThemeCd']").val()) === ''){
                alert("MOBILE 테마를 선택해 주세요.");
                return false;
            }
        });

        //빠른 가격 수정
        $(document).on("click",  ".js-goods-price-modify",function(){
            window.open("../goods/goods_batch_price.php?detailSearch=y&event_text=" + "<?php echo $eventThemeData['themeNm']; ?>" + "&eventThemeSno=" + "<?php echo $eventThemeData['sno']; ?>" + "&eventGroup=" + $("input[name='eventGroupSno']").val() + "#eventSearchArea", '_blank');
        });
        //빠른 마일리지 수정
        $(document).on("click",  ".js-goods-mileage-modify",function(){
            window.open("../goods/goods_batch_mileage.php?locateOther=y&detailSearch=y&event_text=" + "<?php echo $eventThemeData['themeNm']; ?>" + "&eventThemeSno=" + "<?php echo $eventThemeData['sno']; ?>" + "&eventGroup=" + $("input[name='eventGroupSno']").val() + "#eventSearchArea", '_blank');
        });

        set_theme_list('y');
        set_theme_list('n');
        initDepthToggle(<?=$SessScmNo?>);
    });

    function setAddGoods(frmData) {
        var addHtml = "";
        $.each(frmData.info, function (key, val) {
            var stockText = "";

            if (val.soldOutFl == 'y') {
                stockText = "품절";
            }
            else {
                stockText = "정상";
            }

            // 상품 재고
            if (val.stockFl == 'n') {
                totalStock = '∞';
            }
            else {
                totalStock = val.totalStock;
                if (val.totalStock == 0) {
                    stockText = "품절";
                }
            }

            if (val.sortFix == true) {
                sortFix = "checked = 'checked'";
                tableCss = "style='background:#d3d3d3' class='add_goods_fix'";
            }
            else {
                sortFix = '';
                tableCss = "class='add_goods_free'";
            }

            addHtml += '<tr id="tbl_add_goods_' + val.goodsNo + '" ' + tableCss + '>';
            addHtml += '<td class="center">';

            addHtml += '<input type="hidden" name="itemGoodsNm[]" value="' + val.goodsNm + '" />';
            addHtml += '<input type="hidden" name="itemGoodsPrice[]" value="' + val.goodsPrice + '" />';
            addHtml += '<input type="hidden" name="itemScmNm[]" value="' + val.scmNm + '" />';
            addHtml += '<input type="hidden" name="itemTotalStock[]" value="' + val.totalStock + '" />';
            addHtml += '<input type="hidden" name="itemBrandNm[]" value="' + val.brandNm + '" />';
            addHtml += '<input type="hidden" name="itemMakerNm[]" value="' + val.makerNm + '" />';
            addHtml += '<input type="hidden" name="itemImage[]" value="' + val.image + '" />';
            addHtml += '<input type="hidden" name="itemSoldOutFl[]" value="' + val.soldOutFl + '" />';
            addHtml += '<input type="hidden" name="itemStockFl[]" value="' + val.stockFl + '" />';
            addHtml += '<input type="checkbox" name="itemGoodsNo[]" id="layer_goods_' + val.goodsNo + '"  value="' + val.goodsNo + '"/></td>';
            addHtml += '<td class="center number" id="addGoodsNumber_' + val.goodsNo + '">' + (key + 1) + '</td>';
            addHtml += '<td class="center">' + decodeURIComponent(val.image) + '</td>';
            addHtml += '<td><a href="../goods/goods_register.php?goodsNo='+ val.goodsNo +'" target="_blank">' + val.goodsNm + '</a><input type="hidden" name="goodsNoData[]" value="' + val.goodsNo + '" /><input type="checkbox" name="sortFix[]" id="layer_sort_fix_' + val.goodsNo + '"  value="' + val.goodsNo + '" ' + sortFix + ' style="display:none"></td>';
            addHtml += '<td class="center">' + val.goodsPrice + '</td>';
            addHtml += '<td class="center">' + val.scmNm + '</td>';
            addHtml += '<td class="center">' + totalStock + '</td>';
            addHtml += '<td class="center">' + stockText + '</td>';
            addHtml += '</tr>';

        });
        $("#tbl_add_goods_set tbody").html(addHtml);
    }

    function delete_option() {

        var chkCnt = $('input[name="itemGoodsNo[]"]:checked').length;
        if (chkCnt == 0) {
            alert('선택된 상품이 없습니다.');
            return;
        }
        if (confirm('선택한 ' + chkCnt + '개 상품을 삭제하시겠습니까?')) {
            $('input[name="itemGoodsNo[]"]:checked').each(function () {
                field_remove('tbl_add_goods_' + $(this).val());
            });

            $('input[name="itemGoodsNo[]"]').each(function (idx) {
                $("#addGoodsNumber_" + $(this).val()).html(idx+1);
            });
        }
    }

    /**
     * 상품 선택
     *
     * @author artherot
     * @param string orderNo 주문 번호
     */
    function goods_search_popup() {
        window.open('../share/popup_goods.php', 'popup_goods_search', 'width=1255, height=790, scrollbars=no, resizable=yes');
    }

    /**
     * 테마보기
     */
    function viewThemeConfig(themeCd, device) {
        var parameters = {
            'mode': 'theme_ajax',
            'themeCd': themeCd
        };
        $.post("../goods/display_config_ps.php", parameters,
            function (data) {
                $.each(data, function (i, item) {
                    $("#tbl_" + device + "_theme_" + i).html(item);
                });
            }, "json");
    }

    function set_theme_list(mobileFl) {
        $.post('../goods/display_ps.php', {'mode': 'search_theme', 'mobileFl': mobileFl, 'themeCd': 'F'}, function (data) {

            var themeinfo = $.parseJSON(data);
            if ($.type(themeinfo) != 'array') var themeinfo = {};
            var addHtml = "<option value=''>==== 테마 선택 ====</option>";
            if (themeinfo) {
                $.each(themeinfo, function (key, val) {
                    addHtml += "<option value='" + val.themeCd + "'>" + val.themeNm + "</option>";
                });
            }

            if (mobileFl == 'y') {
                $('select[name="groupMobileThemeCd"]').html(addHtml);
                <?php if($data['groupMobileThemeCd']) { ?>
                $('select[name="groupMobileThemeCd"]').val("<?=$data['groupMobileThemeCd']?>");
                <?php } ?>
                viewThemeConfig($('select[name="groupMobileThemeCd"]').val(), 'm');
            }
            else {
                $('select[name="groupThemeCd"]').html(addHtml);
                <?php if($data['groupThemeCd']) { ?>
                $('select[name="groupThemeCd"]').val("<?=$data['groupThemeCd']?>");
                <?php } ?>
                viewThemeConfig($('select[name="groupThemeCd"]').val(), 'p');
            }
        });
    }

    function add_theme_popup(mobileFl) {
        if (mobileFl =="n") {
            var addTheme = "themeCd";
        }
        else {
            var addTheme = "mobileThemeCd";
        }
        window.open('../goods/display_config_theme_register.php?popupMode=yes&themeCate=F&addTheme='+addTheme+'&mobileFl='+mobileFl, 'member_crm', 'width=1210, height=700, scrollbars=yes');
    }

    //등록 후 부모창으로 레이아웃 내려 줌
    function setEventGroupThemeLayout(actionMode, groupSno)
    {
        var eventGroupThemeLayout = _.template($('#eventGroupThemeLayout').html());

        if(actionMode === 'event_group_register'){
            //등록
            var param = {
                eventGroupTmpNo : groupSno,
                groupIndex: $("#eventGroupLayout>tbody>tr", opener.document).length + 1,
                groupName: $("#eventGroupName").val(),
                groupGoodsCnt: parseInt($("#tbl_add_goods_set>tbody>tr").length),
            };
            var html = eventGroupThemeLayout(param);
            $("#eventGroupLayout>tbody", opener.document).append(html);
        }
        else if(actionMode === 'event_group_modify'){
            //수정
            var loadType = $("input[name='loadType']").val();
            if(loadType === 'real'){
                var eventGroupSno = $("input[name='eventGroupSno']").val();
                var actionRowEl = $("input[name='eventGroupNo[]'][value='"+eventGroupSno+"']", opener.document).closest('tr');
                $("#frmGoods", opener.document).prepend('<input type="hidden" value="'+eventGroupSno+'" name="eventGroupDeleteNo[]">');
                actionRowEl.find("input[name='eventGroupTmpNo[]']").val(groupSno);
                actionRowEl.find("input[name='eventGroupNo[]']").val('');
            }
            else {
                var actionRowEl = $("input[name='eventGroupTmpNo[]'][value='"+groupSno+"']", opener.document).closest('tr');
            }

            actionRowEl.find(".js-eventGroupNameArea").html($("#eventGroupName").val());
            actionRowEl.find(".js-eventGroupCountArea").html(parseInt($("#tbl_add_goods_set>tbody>tr").length));
        }
        else { }

        window.close();
    }
//-->
</script>
<script id="eventGroupThemeLayout" type="text/html">
    <tr class="center">
        <td>
            <input type="checkbox" name="eventGroupCheckNo[]" value="" />
            <input type="hidden" name="eventGroupNo[]" value="" />
            <input type="hidden" name="eventGroupTmpNo[]" value="<%=eventGroupTmpNo%>" />
        </td>
        <td class="js-eventGroupIndexArea"><%=groupIndex%></td>
        <td class="js-eventGroupNameArea"><%=groupName%></td>
        <td class="js-eventGroupCountArea"><%=groupGoodsCnt%></td>
        <td><button type="button" class="btn btn-white btn-sm js-eventGroupModify">정보수정</button></td>
        <td><button type="button"  class="btn btn-white btn-sm js-eventGroupCopy">복사</button></td>
    </tr>
</script>
