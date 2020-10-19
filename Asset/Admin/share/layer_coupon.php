<div>
    <div class="mgt10"></div>
    <div>
        <form id="frmCouponSearch">
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col class="width-md"/>
                <col/>
                <col class="width-xs"/>
            </colgroup>
            <?php if($couponKindFl) { ?>
            <tr>
                <th>구분</th>
                <td colspan="3">
                    <input type="hidden" name="couponKindFl" value="true" />
                    <label class="radio-inline">
                        <input type="radio" name="couponKind" value="all" class="form-control" <?= gd_isset($checked['couponKind']['all']); ?> >전체
                    </label>
                    <?php if($offlineCouponUse) { ?>
                    <label class="radio-inline">
                        <input type="radio" name="couponKind" value="online" class="form-control" <?= gd_isset($checked['couponKind']['online']); ?> >일반쿠폰
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponKind" value="offline" class="form-control" <?= gd_isset($checked['couponKind']['offline']); ?> >페이퍼쿠폰
                    </label>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <th>쿠폰명</th>
                <td <?php if($couponSaveType == 'down') { ?>colspan="3"<?php } ?>>
                    <input type="text" name="couponNm" value="<?= $search['keyword']; ?>" class="form-control">
                </td>
                <?php if($couponSaveType != 'down') { ?>
                <th>쿠폰유형</th>
                <td>
                    <?= gd_select_box('couponUseType', 'couponUseType', $couponUseType, null, $search['couponUseType']); ?>
                </td>
                <?php } ?>
                <td rowspan="2">
                    <input type="submit" value="검색" class="btn btn-black btn-hf" onclick="layer_list_search();" />
                </td>
            </tr>
            <tr>
                <th>사용범위</th>
                <td <?= ($couponTypeY) ? 'colspan="3"':''?>>
                    <?= gd_select_box('couponDeviceType', 'couponDeviceType', $couponDeviceType, null, $search['couponDeviceType']); ?>
                </td>
                <?php if(!$couponTypeY) { ?>
                <th>발급상태</th>
                <td>
                    <?= gd_select_box('couponType', 'couponType', $couponType, null, $search['couponType']); ?>
                </td>
                <?php } ?>
            </tr>
        </table>
        </form>
    </div>
</div>

<div class="table-header">
    <div class="pull-left">
        검색 <strong class="text-danger"><?= number_format(gd_isset($page->recode['total'], 0)); ?></strong>개 /
        전체 <strong class="text-danger"><?= number_format(gd_isset($page->recode['amount'], 0)); ?></strong>개
    </div>
</div>

<table class="table table-rows">
    <thead>
    <tr>
        <th><?php if ($mode == 'checkbox' || $mode == 'simple' || $mode == 'couponzone' || $mode == 'couponzoneGroup') { ?><input type="checkbox" id="allCheck" value="y" onclick="check_toggle(this.id,'couponNo');"/><?php } else { ?>선택<?php } ?></th>
        <th>번호</th>
        <?php if($couponKindFl) { ?>
        <th>구분</th>
        <?php } ?>
        <th>쿠폰명</th>
        <th>사용기간</th>
        <th>쿠폰유형</th>
        <th>사용범위</th>
        <th>발급상태</th>
        <th>등록일</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($data) === false) {
        $sumCouponPrice = 0;
        $sumCouponMileage = 0;
        foreach ($data as $key => $val) {
            $sumCouponPrice = $sumCouponPrice + $val['couponPrice'];
            $sumCouponMileage = $sumCouponMileage + $val['couponMileage'];

        ?>
        <tr id="tr-<?=$val['couponNo']?>" class="text-center" style="cursor:pointer;">
            <td>
                <?php if ($mode == 'checkbox' || $mode == 'simple' || $mode == 'couponzone' || $mode == 'couponzoneGroup') { ?>
                    <input type="checkbox" id="couponNo-<?=$val['couponNo']?>" name="couponNo" value="<?=$val['couponNo']?>"/>
                <?php } else { ?>
                    <input type="radio" id="couponNo-<?=$val['couponNo']?>" name="couponNo" value="<?=$val['couponNo']?>">
                <?php } ?>
            </td>
            <td><?=number_format($page->idx--)?></td>
            <?php if($couponKindFl) { ?>
            <td><?= $val['couponKind']; ?></td>
            <?php } ?>
            <td>
                <input type="hidden" id="couponNm_<?php echo $val['couponNo'];?>" value="<?php echo gd_htmlspecialchars($val['couponNm']);?>" />
                <?= $val['couponNm']; ?>
            </td>
            <td><?= $val['useEndDate'] ?></td>
            <td>
                <input type="hidden" id="couponType_<?php echo $val['couponNo'];?>" value="<?php echo $val['couponType'];?>" />
                <input type="hidden" id="couponDisplayFl_<?php echo $val['couponNo'];?>" value="<?php echo gd_htmlspecialchars($val['displayFl']);?>" />
                <?= $val['couponUseType'] ?>
            </td>
            <td><?= $val['couponDeviceType'] ?></td>
            <td><?= $val['couponType'] ?></td>
            <td><?= gd_date_format('Y-m-d', $val['regDt']); ?></td>
        </tr>
        <?php
        }
    } else {
    ?>
        <tr>
            <td colspan="7" class="no-data">검색 할 쿠폰이 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')');?></div>
<div class="text-center">
    <button type="submit" class="btn btn-black btn-lg js-close">확인</button>
</div>

<script type="text/javascript">
    $(function(){
        // 쿠폰명입력후 엔터시
        $('input[name="couponNm"]').keyup(function(e) {
            if (e.keyCode == 13) layer_list_search();
        });

        // 쿠폰 저장하기 클릭시
        $('.js-close').click(function(e){
            if ($('input[name="couponNo"]:checked').length == 0) {
                alert('쿠폰을 선택해 주세요.');
                return false;
            }

            var applyGoodsCnt = 0,
                chkGoodsCnt = 0,
                resultJson = {
                    mode: "<?php echo $mode?>",
                    parentFormID: "<?php echo $parentFormID?>",
                    dataFormID: "<?php echo $dataFormID?>",
                    dataInputNm: "<?php echo $dataInputNm?>",
                    info: []
                };

            <?php if($mode == 'checkbox' || $mode == 'simple' || $mode == 'couponzoneGroup'){ ?>
            var max = 5;
            var currentCount = $("#couponLayer input[name='couponNo[]']").length;
            <?php if($mode == 'couponzoneGroup'){ ?>
            max = 9;
            currentCount = $('#' + resultJson.parentFormID + ' input[name^=groupCoupon]').length;
            <?php } ?>
            var checkedCount = $('input[name="couponNo"]:checked').length;
            if( currentCount > max){
                alert('더이상 쿠폰을 추가 할 수 없습니다.');
                return false;
            }else if (currentCount + checkedCount > max){
                alert('쿠폰은 '+max+'개 이상 추가 할 수 없습니다.');
                return false;
            }
            <?php } ?>

            $('input[name="couponNo"]:checked').each(function() {
                var couponNo = $(this).val();
                var couponNm = $('#couponNm_' + couponNo).val();
                var couponType = $('#couponType_' + couponNo).val();
                var couponDisplayFl = $('#couponDisplayFl_' + couponNo).val();

                if ($('#' + resultJson.dataFormID + '_' + couponNo).length == 0) {
                    resultJson.info.push({
                        couponNo: couponNo,
                        couponNm: couponNm,
                        couponType: couponType,
                        couponDisplayFl: couponDisplayFl
                    });
                    applyGoodsCnt++;
                }
                chkGoodsCnt++;
            });

            if (applyGoodsCnt > 0) {
                displayTemplate(resultJson);

                if (applyGoodsCnt == chkGoodsCnt) {
                    alert(applyGoodsCnt+'개의 쿠폰이 추가 되었습니다.');
                } else {
                    alert('선택한 '+chkGoodsCnt+'개의 쿠폰 중 '+applyGoodsCnt+'개의 쿠폰이 추가 되었습니다.');
                }

                // 선택된 버튼 div 토글
                if (chkGoodsCnt > 0) {
                    $('#' + resultJson.parentFormID).addClass('active');
                } else {
                    $('#' + resultJson.parentFormID).removeClass('active');
                }

                $('div.bootstrap-dialog-close-button').click();
            } else {
                alert('동일한 쿠폰이 이미 존재합니다.');
                return false;
            }
        });

        // 검색 submit시 처리
        $('#frmCouponSearch').submit(function(e){
            layer_list_search();
            e.preventDefault();
            return false;
        });
    });

    // 페이지 출력
    function layer_list_search(pagelink) {
        var couponNm = $('input[name=\'couponNm\']').val();

        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            layerFormID: '<?php echo $layerFormID?>',
            parentFormID: '<?php echo $parentFormID?>',
            dataFormID: '<?php echo $dataFormID?>',
            dataInputNm: '<?php echo $dataInputNm?>',
            callFunc: '<?php echo $callFunc?>',
            mode: '<?php echo $mode?>',
            couponSaveType: '<?php echo $couponSaveType?>',
            key: 'all',
            keyword: $('input[name=\'couponNm\']').val(),
            couponUseType: $('select[name=\'couponUseType\']').val(),
            couponDeviceType: $('select[name=\'couponDeviceType\']').val(),
            couponType: $('select[name=\'couponType\']').val(),
            couponTypeY: '<?php echo $couponTypeY?>',
            couponKind: $('input[name=\'couponKind\']:checked').val(),
            couponKindFl: $('input[name=\'couponKindFl\']').val(),
            pagelink: pagelink
        };

        $.get('<?php echo URI_ADMIN;?>share/layer_coupon.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    // 화면출력
    function displayTemplate(data) {
        <?php if($mode != 'couponzone' && $mode != 'couponzoneGroup'){ ?>
        $('#' + data.parentFormID).html('');
        <?php } ?>

        var addHtml = '';
        var complied = _.template($('#couponTemplate').html());

        <?php if($mode == 'checkbox'){ ?>
            addHtml = $('#' + data.parentFormID).html().replace('<h5>선택된 쿠폰:</h5>', '');
        <?php } ?>

        <?php if($mode == 'simple' || $mode == 'couponzoneGroup') { ?>
        var parentFormCount = $('#' + data.parentFormID+' tr').length;
        <?php } ?>
        $.each(data.info, function (key,val) {
            <?php if($mode =='simple' || $mode == 'couponzoneGroup') { ?>
            var childRow = data.childRow ? Number(data.childRow) : 0;
            <?php } ?>
            addHtml += complied({
                dataFormID: data.dataFormID,
                dataInputNm: data.dataInputNm,
                couponNo: val.couponNo,
                couponNoNm: val.couponNm,
                <?php if($mode =='simple' || $mode == 'couponzoneGroup') { ?>
                key: key + Number(childRow) + parentFormCount,
                <?php if($mode == 'couponzoneGroup') { ?>
                couponType: val.couponType,
                couponDisplayFl: val.couponDisplayFl,
                couponCss: (val.couponDisplayFl == '미노출' ? 'couponDisplayN' : '')
                <?php } ?>
                <?php } ?>
            });
        });
        $('#' + data.parentFormID).append(addHtml);

        if (data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5')) {
            <?php if($mode != 'simple' && $mode != 'couponzoneGroup') { ?>
            $('#' + data.parentFormID).prepend('<h5>선택된 쿠폰:</h5>');
            <?php } ?>
        }

        //브랜드 미지정 상품이 있을시 체크해제
        if($("input[name='couponAllFl']").length > 0){
            $("input[name='couponAllFl']").prop("checked", false);
        }
    }
</script>
<?php if ($mode == 'checkbox') { ?>
<script type="text/html" id="couponTemplate">
    <div id="<%=dataFormID%>_<%=couponNo%>" class="btn-group btn-group-xs">
        <input type="hidden" name="<%=dataInputNm%>[]" value="<%=couponNo%>">
        <input type="hidden" name="<%=dataInputNm%>Nm[]" value="<%=couponNoNm%>">
        <span class="btn"><%=couponNoNm%></span>
        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#<%=dataFormID%>_<%=couponNo%>">삭제</button>
    </div>
</script>
<?php } else if($mode == 'simple') { ?>
    <script type="text/html" id="couponTemplate">
        <tr id="<%=dataFormID%>_<%=couponNo%>">
            <td class="center"><span class="number"><%=(key + 1)%></span>
                <input type="hidden" name="<%=dataInputNm%>[]" value="<%=couponNo%>">
                <input type="hidden" name="<%=dataInputNm%>Nm[]" value="<%=couponNoNm%>">
            <td><%=couponNoNm%></td>
            <td class="center">
                <input type="button" class="btn btn-sm btn-gray" data-toggle="delete" data-target="#<%=dataFormID%>_<%=couponNo%>" value="삭제"/>
            </td>
        </tr>
    </script>
<?php } else if($mode == 'couponzoneGroup') { ?>
<script type="text/html" id="couponTemplate">
    <tr id="<%=dataFormID%>_<%=couponNo%>" class="<%=couponCss%>">
        <td class="center"><span class="number"><%=(key + 1)%></span>
        <td>
            <input type="hidden" name="<%=dataInputNm%>[]" value="<%=couponNo%>">
            <a href="../promotion/coupon_regist.php?couponNo=<%=couponNo%>" class="couponNoNm" target="_blank"><%=couponNoNm%></a>
        </td>
        <td class="center couponDisplayFl"><%=couponDisplayFl%></td>
        <td class="center"><%=couponType%></td>
        <td class="center">
            <input type="button" class="btn btn-sm btn-gray couponDelete" data-target="#<%=dataFormID%>_<%=couponNo%>" value="삭제"/>
        </td>
    </tr>
</script>
<?php } else if($mode == 'couponzone') { ?>
<script type="text/html" id="couponTemplate">
    <div id="<%=dataFormID%>_<%=couponNo%>" class="btn-group btn-group-xs">
        <input type="hidden" name="<%=dataInputNm%>[]" value="<%=couponNo%>">
        <input type="hidden" name="<%=dataInputNm%>Nm[]" value="<%=couponNoNm%>">
        <span class="btn"><%=couponNoNm%></span>
        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#<%=dataFormID%>_<%=couponNo%>">삭제</button>
    </div>
</script>
<?php }else{ ?>
<script type="text/html" id="couponTemplate">
    <div id="<%=dataFormID%>_<%=couponNo%>" class="btn-group btn-group-xs">
        <input type="hidden" name="<%=dataInputNm%>" value="<%=couponNo%>">
        <input type="hidden" name="<%=dataInputNm%>Nm" value="<%=couponNoNm%>">
        <span class="btn"><%=couponNoNm%></span>
        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#<%=dataFormID%>_<%=couponNo%>">삭제</button>
    </div>
</script>
<?php } ?>
