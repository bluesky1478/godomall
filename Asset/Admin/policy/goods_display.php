<form id="frmGoodsToday" name="frmGoodsToday" action="./goods_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="goods_display" />
    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location);?> </h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red">
        </div>
    </div>
    <?php
    if($data['option_1903']['use'] != 'y'){
    ?>
    <div style="border:#ff9f9f 5px solid; padding:15px;" class="center table">
        <p style="padding-bottom:10px; margin-top:5px;" class="table-title">새롭게 개선된 옵션/재고 설정 기능을 확인하세요</p>
        <div style="height:250px;" id="option1903_1">
            <p style="color:red; padding-top:80px;" class="table-title">새로운 옵션/재고 설정은 배송상태와 품절상태를 관리할 수 있습니다.<br />새로운 기능을 적용하겠습니까?</p>
        </div>
        <div style="height:250px;" id="option1903_2" class="display-none">
            <input type="button" value="새로운 기능 확인" class="btn btn-white btn-large" onclick="goods_option_1903_info()" style="margin-top:30px">
            <p style="color:red; padding-top:40px" class="table-title">새로운 옵션/재고 설정은 배송상태와 품절상태를 관리할 수 있습니다.<br />새로운 기능을 적용하겠습니까?</p>
        </div>
        <div style="height:250px;" id="option1903_3" class="display-none">
            <div style="width:620px; margin:auto; background-color:#f2f2f2; padding:5px;" class="left">
                <p>※ 주의사항</p>
                <p>① 새로운 기능 적용시 기존 기능으로 변경할 수 없습니다.<br />
                    ② 상품등록 관리자 화면의 소스를 튜닝한 경우 기능이 정상 동작하지 않을 수 있습니다.<br />
                    ③ 쇼핑몰 상품구매 / 상세화면과 관련된 스킨 소스를 튜닝한 경우 기능이 정상 작동하지 않을 수 있습니다.<br />
                    ④ 관리자 화면 또는 쇼핑몰 스킨을 튜닝한 경우 튜닝소스를 백업 후 새로운 기능으로 적용하는 것을 권장합니다.<br />
                    ⑤ 새로운 옵션/재고 설정 기능 이용시 아래 스킨패치 파일을 적용하셔야만 정상적인 이용이 가능합니다.<br />
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>- 패치 & 업그레이드 게시판 > 상품 옵션/재고 설정 기능 개선 - 2019.04.03</strong></p>
            </div>
            <p style="color:red; padding-top:20px;" class="table-title">새로운 옵션/재고 설정은 배송상태와 품절상태를 관리할 수 있습니다.<br />새로운 기능을 적용하겠습니까?</p>
        </div>
        <div style="height:250px;" id="option1903_4" class="display-none">
            <p style="color:red; padding-top:90px" class="table-title">새로운 기능으로 적용하면 기존 기능으로 변경은 불가능합니다.<br/>새로운 기능을 적용하시겠습니까?</p>
        </div>
        <div>
            <input type="button" value="◀ 이전" class="btn btn-black btn-large js-prev-btn display-none">&nbsp;
            <input type="button" value="다음 ▶" class="btn btn-black btn-large js-next-btn">
            <input type="button" value="새로운 기능으로 적용하기" class="btn btn-red btn-large js-final-btn display-none">
        </div>
        <div style="color:red;margin-top: -20px;text-align: right;" id="option1903_navi">
            <span>●</span> <span>○</span> <span>○</span> <span>○</span>
        </div>
    </div>
    <?php
        }
    ?>
    <div class="table-title gd-help-manual">
        상품관리 운영설정
    </div>
    <table class="table table-cols">
        <colgroup><col class="width-md" /><col/></colgroup>
        <tr>
            <th>상품수정일 변경 <br />범위 설정</th>
            <td>  <div class="form-inline">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="goodsModDtTypeUp" value="y" <?=$data['checked']['goodsModDtTypeUp']['y']?>>상품 수정
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="goodsModDtTypeList" value="y" <?=$data['checked']['goodsModDtTypeList']['y']?>>상품 리스트
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="goodsModDtTypeAll" value="y" <?=$data['checked']['goodsModDtTypeAll']['y']?>>상품 일괄 관리
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="goodsModDtTypeExcel" value="y" <?=$data['checked']['goodsModDtTypeExcel']['y']?>>상품 엑셀 업로드
                    </label>
                    <?php if (gd_use_provider() === true) { ?>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="goodsModDtTypeScm" value="y" <?=$data['checked']['goodsModDtTypeScm']['y']?>>공급사 상품 승인
                    </label>
                    <?php } ?>
                </div>
                <div class="notice-info">체크 한 범위 내에서 상품 정보 수정 시 상품수정일이 수정한 시간으로 변경됩니다.</div>
            </td>
        </tr>
        <tr>
            <th>상품수정일 변경 <br />확인 팝업</th>
            <td>  <div class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="goodsModDtFl" value="y" <?=$data['checked']['goodsModDtFl']['y']?>>사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="goodsModDtFl" value="n" <?=$data['checked']['goodsModDtFl']['n']?>>사용안함
                    </label>
                </div>
                <div class="notice-info">'사용함'의 경우, 상품 정보 수정 시 상품수정일을 현재시간으로 변경 할 것인지 확인하는 팝업이 노출됩니다.</div>
                <div class="notice-info">'사용안함'의 경우, 상품수정일 업데이트 범위 내에서 상품 정보 수정 시 항상 업데이트 됩니다.</div>
            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual">
        이미지 출력 설정
    </div>
    <table class="table table-cols">
        <colgroup><col class="width-md" /><col/></colgroup>
        <tr>
            <th>이미지 로딩 향상</th>
            <td>  <div class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="imageLazyFl" value="y" <?=$data['checked']['imageLazyFl']['y']?>>사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="imageLazyFl" value="n" <?=$data['checked']['imageLazyFl']['n']?>>사용안함
                    </label>
                </div>
            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual">
        대체문구 표시설정
    </div>
    <table class="table table-cols">
        <colgroup><col class="width-md" /><col/></colgroup>
        <tr>
            <th>가격정보 노출여부</th>
            <td style="padding-left:25px; padding-right:40px;">  <div class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="priceFl" value="y" <?=$data['checked']['priceFl']['y']?>>노출함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="priceFl" value="n" <?=$data['checked']['priceFl']['n']?>>노출안함
                    </label>
                </div>
                <div class="notice-info">가격 대체문구로 출력시 “정가와 구매혜택”의 노출여부를 설정합니다.</div>
            </td>
            <td>
                <img src="<?=PATH_ADMIN_GD_SHARE?>img/goods_display.png" class="">
            </td>
        </tr>
    </table>
    <div class="table-title gd-help-manual">
        옵션가 표시설정
    </div>
    <table class="table table-cols">
        <colgroup><col class="width-md" /><col/></colgroup>
        <tr>
            <th>선택옵션 옵션가 노출여부</th>
            <td style="padding-right:0px;">  <div class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="optionPriceFl" value="y" <?=$data['checked']['optionPriceFl']['y']?>>노출함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="optionPriceFl" value="n" <?=$data['checked']['optionPriceFl']['n']?>>노출안함
                    </label>
                </div>
                <div class="notice-info">쇼핑몰 "상품 상세정보 / 장바구니 / 찜 리스트 / 주문서작성 / 마이페이지" 등에 <br /> 선택된 "상품 옵션 / 텍스트 옵션”의 옵션가 금액 노출여부를 설정합니다.</div>
            </td>
            <td>
                <img src="<?=PATH_ADMIN_GD_SHARE?>img/goods_option_display.png" class="">
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        <?php if(gd_isset($data['config']['goodsModDtTypeUp']) == 'n' && gd_isset($data['config']['goodsModDtTypeList']) == 'n' && gd_isset($data['config']['goodsModDtTypeAll']) && gd_isset($data['config']['goodsModDtTypeExcel']) == 'n' && gd_isset($data['config']['goodsModDtTypeScm']) == 'n') { ?>
        $('input[name=\'goodsModDtFl\']').prop('disabled', true);
        $('input[name=\'goodsModDtFl\']').prop('checked', true);
        <?php } ?>

        // 상품 수정
        $('input:checkbox[name="goodsModDtTypeUp"]').click(function () {
            if ($('input:checkbox[name="goodsModDtTypeUp"]').prop("checked") === true) {
                $('input[name=\'goodsModDtFl\']').prop('disabled', false);
            } else {
                //모두 미체크시 상품수정일 변경 확인팝업 사용안함 체크 및 disabled
                if ($('input:checkbox[name="goodsModDtTypeList"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeAll"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeExcel"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeScm"]').prop("checked") === false) {
                    $('input[name=\'goodsModDtFl\']').prop('disabled', true);
                    $('input[name=\'goodsModDtFl\']').prop('checked', true);
                }
            }
        });

        // 상품 리스트
        $('input:checkbox[name="goodsModDtTypeList"]').click(function () {
            if ($('input:checkbox[name="goodsModDtTypeList"]').prop("checked") === true) {
                $('input[name=\'goodsModDtFl\']').prop('disabled', false);
            } else {
                //모두 미체크시 상품수정일 변경 확인팝업 사용안함 체크 및 disabled
                if ($('input:checkbox[name="goodsModDtTypeUp"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeAll"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeExcel"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeScm"]').prop("checked") === false) {
                    $('input[name=\'goodsModDtFl\']').prop('disabled', true);
                    $('input[name=\'goodsModDtFl\']').prop('checked', true);
                }
            }
        });

        // 상품 일괄 관리
        $('input:checkbox[name="goodsModDtTypeAll"]').click(function () {
            if ($('input:checkbox[name="goodsModDtTypeAll"]').prop("checked") === true) {
                $('input[name=\'goodsModDtFl\']').prop('disabled', false);
            } else {
                //모두 미체크시 상품수정일 변경 확인팝업 사용안함 체크 및 disabled
                if ($('input:checkbox[name="goodsModDtTypeList"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeUp"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeExcel"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeScm"]').prop("checked") === false) {
                    $('input[name=\'goodsModDtFl\']').prop('disabled', true);
                    $('input[name=\'goodsModDtFl\']').prop('checked', true);
                }
            }
        });

        // 상품 엑셀 업로드
        $('input:checkbox[name="goodsModDtTypeExcel"]').click(function () {
            if ($('input:checkbox[name="goodsModDtTypeExcel"]').prop("checked") === true) {
                $('input[name=\'goodsModDtFl\']').prop('disabled', false);
            } else {
                //모두 미체크시 상품수정일 변경 확인팝업 사용안함 체크 및 disabled
                if ($('input:checkbox[name="goodsModDtTypeUp"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeList"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeAll"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeScm"]').prop("checked") === false) {
                    $('input[name=\'goodsModDtFl\']').prop('disabled', true);
                    $('input[name=\'goodsModDtFl\']').prop('checked', true);
                }
            }
        });

        // 공급사 상품 승인
        $('input:checkbox[name="goodsModDtTypeScm"]').click(function () {
            if ($('input:checkbox[name="goodsModDtTypeScm"]').prop("checked") === true) {
                $('input[name=\'goodsModDtFl\']').prop('disabled', false);
            } else {
                //모두 미체크시 상품수정일 변경 확인팝업 사용안함 체크 및 disabled
                if ($('input:checkbox[name="goodsModDtTypeUp"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeList"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeAll"]').prop("checked") === false
                    && $('input:checkbox[name="goodsModDtTypeExcel"]').prop("checked") === false) {
                    $('input[name=\'goodsModDtFl\']').prop('disabled', true);
                    $('input[name=\'goodsModDtFl\']').prop('checked', true);
                }
            }
        });

        $('.js-prev-btn').click(function () {
            var index = 4;

            if($('#option1903_4').css('display') == 'block') index = 3;
            if($('#option1903_3').css('display') == 'block') index = 2;
            if($('#option1903_2').css('display') == 'block') index = 1;

            $('#option1903_1').css('display', 'none');
            $('#option1903_2').css('display', 'none');
            $('#option1903_3').css('display', 'none');
            $('#option1903_4').css('display', 'none');
            $('#option1903_navi > span')[0].innerText = '○';
            $('#option1903_navi > span')[1].innerText = '○';
            $('#option1903_navi > span')[2].innerText = '○';
            $('#option1903_navi > span')[3].innerText = '○';

            $('#option1903_' + index).css('display', 'block');

            $('#option1903_navi > span')[index - 1].innerText = '●';

            //첫 내용이면 이전 버튼 가리기
            if(index == 1){
                $('#option1903_' + index).addClass('display-none');
                $('.js-prev-btn').addClass('display-none');
            }
            $('.js-next-btn').removeClass('display-none');
            $('.js-final-btn').addClass('display-none');
        });

        $('.js-next-btn').click(function () {
            var index = 0;

            if($('#option1903_1').css('display') == 'block') index = 2;
            if($('#option1903_2').css('display') == 'block') index = 3;
            if($('#option1903_3').css('display') == 'block') index = 4;

            $('#option1903_1').css('display', 'none');
            $('#option1903_2').css('display', 'none');
            $('#option1903_3').css('display', 'none');
            $('#option1903_4').css('display', 'none');
            $('#option1903_navi > span')[0].innerText = '○';
            $('#option1903_navi > span')[1].innerText = '○';
            $('#option1903_navi > span')[2].innerText = '○';
            $('#option1903_navi > span')[3].innerText = '○';

            $('#option1903_navi > span')[index - 1].innerText = '●';

            $('#option1903_' + index).css('display', 'block');

            $('.js-prev-btn').removeClass('display-none');
            if(index == 4) {
                $('.js-final-btn').removeClass('display-none');
                $('.js-next-btn').addClass('display-none');
            }
        });

        $('.js-final-btn').click(function () {
            if(confirm('새로운 기능을 적용하면 이전 기능으로 변경할 수 없습니다.\n\n새로운 기능을 적용하시겠습니까?')){
                $('#frmGoodsToday input[name="mode"]').val('goods_option_1903_change');
                $('#frmGoodsToday').submit();
            }
        });
    });

    function goods_option_1903_info() {
        var loadChk = $('#layerGoodsOption').length;

        $.get('../policy/goods_display_option_info.php', null, function (data) {
            if (loadChk == 0) {
                data = '<div id="layerGoodsOption">' + data + '</div>';
            }
            var layerForm = data;
            layer_popup(layerForm, '새로운 기능 보기');
        });
    }
    //-->
</script>
