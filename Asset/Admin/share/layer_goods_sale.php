<form name="layerGoodsSaleFrm" id="layerGoodsSaleFrm">
    <input type="hidden" name="mode" value="goods_sale">
    <input type="hidden" name="goodsNo" value="">
    <input type="hidden" name="modDtUse" value="">
    <table class="table table-rows" style="margin-bottom:0;">
        <thead>
        <tr>
            <th colspan="2" class="ta-l">
                <div class="checkbox-inline"><label><input type="checkbox" class="all-chk" id="goodsDisplay" value="y" /> <span>노출상태</span></label></div>
            </th>
            <th colspan="2" class="ta-l">
                <div class="checkbox-inline"><label><input type="checkbox" class="all-chk" id="goodsSell" value="y" /> <span>판매상태</span></label></div>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th class="th">
                <div class="checkbox-inline"><label><input type="checkbox" class="part-chk goodsDisplay" name="goodsDisplay" value="y" /> PC쇼핑몰</label></div>
            </th>
            <td>
                <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="y" checked="checked" disabled="disabled"> 노출함</label>
                <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="n" disabled="disabled"> 노출안함</label>
            </td>
            <th class="th">
                <div class="checkbox-inline"><label><input type="checkbox" class="part-chk goodsSell" name="goodsSell" value="y" /> PC쇼핑몰</label></div>
            </th>
            <td>
                <label class="radio-inline"><input type="radio" name="goodsSellFl" value="y" checked="checked" disabled="disabled"> 판매함</label>
                <label class="radio-inline"><input type="radio" name="goodsSellFl" value="n" disabled="disabled"> 판매안함</label>
            </td>
        </tr>
        <tr>
            <th class="th">
                <div class="checkbox-inline"><label><input type="checkbox" class="part-chk goodsDisplay" name="goodsDisplayMobile" value="y" /> 모바일쇼핑몰</label></div>
            </th>
            <td>
                <label class="radio-inline"><input type="radio" name="goodsDisplayMobileFl" value="y" checked="checked" disabled="disabled"> 노출함</label>
                <label class="radio-inline"><input type="radio" name="goodsDisplayMobileFl" value="n" disabled="disabled"> 노출안함</label>
            </td>
            <th class="th">
                <div class="checkbox-inline"><label><input type="checkbox" class="part-chk goodsSell" name="goodsSellMobile" value="y" /> 모바일쇼핑몰</label></div>
            </th>
            <td>
                <label class="radio-inline"><input type="radio" name="goodsSellMobileFl" value="y" checked="checked" disabled="disabled"> 판매함</label>
                <label class="radio-inline"><input type="radio" name="goodsSellMobileFl" value="n" disabled="disabled"> 판매안함</label>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="notice-info mgb5">노출안함 상태여도 상품의 상세페이지 주소를 직접 입력하는 경우 접근이 가능합니다.<br />상품 상세페이지 접근 여부는 "상품 등록(수정) > 추가 정보 > 접근 권한"에서 설정할 수 있습니다.</div>
    <div class="notice-danger mgb20">체크박스가 체크된 경우에만 선택된 상품의 쇼핑몰 노출/판매 상태가 변경됩니다.</div>
    <div class="center">
        <input type="button" value="취소" class="btn btn-lg btn-white js-layer-close" />
        <input type="button" value="선택상품 일괄수정" class="btn btn-lg btn-black goods-sale-btn" />
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        var goodsNo = [];
        $('#frmList input[type="checkbox"][name^="goodsNo"]:checked').each(function(){
            goodsNo.push(this.value);
        });
        $('#layerGoodsSaleFrm input[name="goodsNo"]').val(goodsNo.join('<?php echo INT_DIVISION; ?>'));
        $('.all-chk').click(function(){
            var id = this.id;
            var checked = this.checked;

            if (checked == true) {
                $('input[type="checkbox"][name^="' + id + '"]').prop('checked', true);
                $('input[type="radio"][name^="' + id + '"]').prop('disabled', false);
            } else {
                $('input[type="checkbox"][name^="' + id + '"]').prop('checked', false);
                $('input[type="radio"][name^="' + id + '"]').prop('disabled', true);
            }
        });

        $('.part-chk').click(function(){
            var name = this.name;
            var checked = this.checked;
            var part = 'goodsDisplay';
            if ($(this).hasClass('goodsSell')) {
                part = 'goodsSell';
            }

            if (checked == true) {
                $('input[type="radio"][name="' + name + 'Fl"]').prop('disabled', false);
            } else {
                $('input[type="radio"][name="' + name + 'Fl"]').prop('disabled', true);
            }

            if ($('.' + part + ':checked').length > 0) {
                $('#' + part).prop('checked', true);
            } else {
                $('#' + part).prop('checked', false);
            }
        });

        $('.goods-sale-btn').click(function(){
            if ($('.all-chk:checked').length == 0) {
                alert('수정할 항목을 선택해주세요.');
                return false;
            }
            var target = [];
            $('.all-chk:checked').each(function(){
                target.push($(this).next().html());
            });
            var html = '<div class="center pdb10">선택된 내용으로 상품정보를 일괄수정 하시겠습니까?</div>';
            html += '<div class="center pdb10"><b>선택항목 : ' + target.join(', ') + '</b></div>';
            html += '[주의]<br />선택된 모든 상품의 정보가 동일한 내용으로 변경되며,<br />일괄적용 후에는 이전상태로 복원이 안되므로 신중하게 변경하시기 바랍니다.';
            dialog_confirm(html, function (result) {
                if (result) {
                    //상품수정일 변경 확인 팝업
                    <?php
                    // --- 상품  설정 config 불러오기
                    $goodsConfig = (gd_policy('goods.display'));
                    $goodsConfig['goodsModDtTypeList'] = gd_isset($goodsConfig['goodsModDtTypeList'], 'y');
                    $goodsConfig['goodsModDtFl'] = gd_isset($goodsConfig['goodsModDtFl'], 'n');
                    if ($goodsConfig['goodsModDtTypeList'] == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
                    dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?", function (result) {
                        if (result) {
                            $('input[name="modDtUse"]').val('y');
                        } else {
                            $('input[name="modDtUse"]').val('n');
                        }
                        $('#layerGoodsSaleFrm').attr({
                            'method': 'post',
                            'action': './goods_ps.php',
                            'target': 'ifrmProcess'
                        }).submit();
                    }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
                    <?php } else { ?>
                        //상품 수정일 변경 범위설정 체크
                        <?php if ($goodsConfig['goodsModDtTypeList'] == 'y') { ?>
                            $('input[name="modDtUse"]').val('y');
                        <?php } else { ?>
                            $('input[name="modDtUse"]').val('n');
                        <?php } ?>
                        $('#layerGoodsSaleFrm').attr({
                            'method': 'post',
                            'action': './goods_ps.php',
                            'target': 'ifrmProcess'
                        }).submit();
                    <?php } ?>
                }
            });
        });
    });
</script>
