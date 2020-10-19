<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location);?></h3>
</div>

<form id="frmExcelUpload" action="order_ps.php" method="post" target="ifrmProcess" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="externalOrderExcelRegist" />
    <div class="table-title gd-help-manual">외부채널 주문 일괄등록</div>
    <table class="table table-cols mgb5">
        <colgroup>
            <col class="width-xl"/>
            <col/>
        </colgroup>
        <tr>
            <th>
                엑셀파일 업로드
            </th>
            <td>
                <div class="form-inline">
                    <input type="file" name="externalExcelFile" class="form-control input-group-item">
                    <button class="btn btn-sm btn-white input-group-item" type="submit">등록하기</button>
                    <span class="notice-info mgl10">1회 최대 1,000건까지 등록하실 수 있습니다.</span>
                </div>
            </td>
        </tr>
    </table>
</form>

<div class="mgt30">
    <div class="bold">외부채널 주문 업로드 방법</div>
    <div class="mgt10 mgl10">
        <li>1. “외부채널 주문 엑셀 업로드 샘플” 버튼을 눌러 샘플을 참고하시기 바랍니다.</li>
        <li>2. 샘플 파일 내 각 항목은 “외부채널 주문 엑셀 항목설명”내 기재된 내용을 기준으로 작성합니다.</li>
        <li>3. 엑셀 파일 저장은 반드시 "Excel 97-2003 통합문서(xls)"로 저장을 하셔야 합니다. 그외 csv 나 xlsx파일 등은 지원이 되지 않습니다.</li>
        <li>4. 작성된 엑셀 파일을 업로드 합니다.</li>
    </div>
</div>

<div class="mgt10">

    <a href="../order/order_ps.php?mode=external_order_download" class="btn btn-sm btn-gray">외부채널 주문 엑셀 업로드 샘플</a>

    <button type="button" class="btn btn-sm btn-gray js-external-order-description">외부채널 주문 엑셀 항목설명</button>
</div>

<div class="mgt30">
    <div class="bold">엑셀 업로드 시 주의사항</div>
    <div class="mgt10 mgl10">
        <li>1. 엑셀 2003 사용자의 경우는 그냥 저장을 하시면 되고, 엑셀 2007 이나 엑셀 2010 인 경우는 새이름으로 저장을 선택해서 "Excel 97-2003 통합문서"로 저장을 하십시오.</li>
        <li>2. 엑셀의 내용이 너무 많은 경우, 업로드가 불가능 할 수 있으므로 100개나 200개 단위로 나누어 올리시기 바랍니다.</li>
        <li>3. 엑셀 파일이 작성이 완료 되었다면 하나의 주문만 테스트로 올려보고 확인 후 이상이 없으시면 나머지를 올리시기 바랍니다.</li>
        <li>4. 엑셀 내용중 "1번째 줄은 설명", "2번째 줄은 excel DB 코드", "3번째 줄은 설명" 이며, "4번째 줄부터" 필수 데이터 입니다.</li>
        <li class="mgl15">그리고 반드시 내용은 "4번째 줄부터" 작성 하셔야 합니다.</li>
        <li>5.엑셀샘플파일 내 필수항목을 삭제하고 업로드 시 주문정보 등록이 불가능하니 유의 바랍니다.</li>
        <li>6. 이미 생성된 주문을 엑셀로 일괄 수정하는 기능은 지원하지 않습니다.</li>
    </div>
</div>

<div class="mgt30">
    <div class="bold">엑셀 파일 작성 시 주의사항</div>
    <div class="mgt10 mgl10">
        <li>- <span class="c-gdred bold">다수의 상품을 하나의 주문으로 등록하기 위해서는 주문 그룹 번호를 동일하게 입력</span>해야 합니다.</li>
        <li>- 샘플 파일 내 붉은 글자색으로 표시된 항목은 반드시 입력해야 하는 필수항목입니다.</li>
        <li>- 샘플 파일 내 초록배경으로 표시된 항목은 주문 그룹 번호 기준으로 가장 첫 번째 행에 입력된 값만 주문에 등록됩니다.</li>
        <li class="c-gdred bold">- 주문하고자 하는 상품은 고도몰5의 상품에 모두 등록되어 있어야 하며, 상품의 '자체상품코드'와 '자체옵션코드'를 기준으로 주문이 가능합니다.</li>
        <li>- 주문하고자 하는 상품에 옵션이 있는 경우, 반드시 자체옵션코드를 입력해야 합니다. 옵션이 없는 상품의 경우 '옵션없음'이라고 입력하시기 바랍니다.</li>
    </div>
</div>

<script type="text/javascript">
    <!--
    $(function(){
        $('#frmExcelUpload').validate({
            dialog: false,
            submitHandler: function(form) {
                BootstrapDialog.confirm({
                    title: '외부채널 주문등록',
                    message: '외부채널 주문등록 엑셀파일을 업로드 하시겠습니까?',
                    nl2br: false,
                    callback: function(result) {
                        if(result === true){
                            form.submit();
                        }
                    }
                });
            },
            rules: {
                externalExcelFile: 'required'
            },
            messages: {
                externalExcelFile: '업로드 할 엑셀파일을 등록해 주세요.'
            }
        });

        // 외부채널 주문 엑셀 항목설명
        $('.js-external-order-description').click(function(e){
            $.get('../order/layer_external_order_description.php', function(data){
                layer_popup(data, '외부채널 주문 엑셀 항목설명', 'wide');
            });
        });
    });
    //-->
</script>
