<form id="frmOrderWriteForm" name="frmOrderWriteForm" target="ifrmProcess" action="./order_ps.php" method="post">
    <input type="hidden" name="mode" value="save_write_order" />
    <input type="hidden" name="memNo" value="0" />
    <input type="hidden" name="orderTypeFl" value="write" />
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <input type="submit" value="수기주문 등록" class="btn btn-red-line">
    </div>
    <div class="row">
        <div class="col-xs-6">
            <div class="table-title gd-help-manual">주문자 정보</div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>회원구분</th>
                    <td class="form-inline">
                        <label class="radio">
                            <input type="radio" name="tmp[memTypeFl]" value="0" class="js-address-layer" /> 비회원
                            <button type="button" class="btn btn-gray btn-sm js-address-layer">자주쓰는 주소</button>
                        </label>
                        <label class="radio mgl10">
                            <input type="radio" name="tmp[memTypeFl]" value="1" class="js-member-layer" /> 회원
                            <button type="button" class="btn btn-gray btn-sm js-member-layer">회원 선택</button>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>주문자명</th>
                    <td class="form-inline">
                        <input type="text" class="form-control" name="orderName" size="10" />
                    </td>
                </tr>
                <tr>
                    <th>구매자 IP</th>
                    <td class="font-num">
                        <?= gd_isset($orderIp); ?>
                    </td>
                </tr>
                <tr>
                    <th>전화번호</th>
                    <td>
                        <div class="form-inline">
                            <?= gd_select_box(null, 'orderPhone[]', gd_phone_area(false)); ?> -
                            <input type="text" name="orderPhone[]" value="" size="4" class="form-control width-2xs"/> -
                            <input type="text" name="orderPhone[]" value="" size="4" class="form-control width-2xs"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>휴대폰번호</th>
                    <td>
                        <div class="form-inline">
                            <?= gd_select_box(null, 'orderCellPhone[]', gd_phone_area()); ?> -
                            <input type="text" name="orderCellPhone[]" value="" size="4" class="form-control width-2xs"/> -
                            <input type="text" name="orderCellPhone[]" value="" size="4" class="form-control width-2xs"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>이메일</th>
                    <td>
                        <div class="form-inline js-email-select" data-target-name="orderEmail[]" data-origin-data="<?= $data['email'][1] ?>">
                            <input type="text" name="orderEmail[]" value="<?= $data['email'][0] ?>" class="form-control width-sm">
                            <label class="control-label">@</label>
                            <input type="text" name="orderEmail[]" value="<?= $data['email'][1] ?>" class="form-control width-sm">
                            <?= gd_select_box_by_mail_domain(null, null, null, $data['email'][1], '직접입력'); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>주소</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="orderZonecode" value="" size="5" class="form-control"/>
                            <input type="hidden" name="orderZipcode" value=""/>
                            <span id="orderZipcodeText" class="number display-none">()</span>
                            <input type="button" onclick="postcode_search('orderZonecode', 'orderAddress', 'orderZipcode');" value="우편번호찾기" class="btn btn-sm btn-gray"/>
                        </div>
                        <div class="mgt5">
                            <input type="text" name="orderAddress" value="" class="form-control"/>
                        </div>
                        <div class="mgt5">
                            <input type="text" name="orderAddressSub" value="" class="form-control"/>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-xs-6">
            <div class="table-title gd-help-manual">수령자 정보</div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>배송지확인</th>
                    <td>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="tmp[checkOrderSame]" value="y" class="js-order-same" /> 주문자 정보와 동일
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>수령자명</th>
                    <td>
                        <input type="text" name="receiverName" value="<?= gd_isset($data['receiverName']); ?>" class="form-control width-sm"/>
                    </td>
                </tr>
                <tr>
                    <th>전화번호</th>
                    <td>
                        <div class="form-inline">
                            <?= gd_select_box(null, 'receiverPhone[]', gd_phone_area(false)); ?> -
                            <input type="text" name="receiverPhone[]" value="" size="4" class="form-control width-2xs"/> -
                            <input type="text" name="receiverPhone[]" value="" size="4" class="form-control width-2xs"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>휴대폰번호</th>
                    <td>
                        <div class="form-inline">
                            <?= gd_select_box(null, 'receiverCellPhone[]', gd_phone_area()); ?> -
                            <input type="text" name="receiverCellPhone[]" value="" size="4" class="form-control width-2xs"/> -
                            <input type="text" name="receiverCellPhone[]" value="" size="4" class="form-control width-2xs"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>주소</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="receiverZonecode" value="" size="5" class="form-control"/>
                            <input type="hidden" name="receiverZipcode" value=""/>
                            <span id="receiverZipcodeText" class="number display-none">()</span>
                            <input type="button" onclick="postcode_search('receiverZonecode', 'receiverAddress', 'receiverZipcode');" value="우편번호찾기" class="btn btn-sm btn-gray"/>
                        </div>
                        <div class="mgt5">
                            <input type="text" name="receiverAddress" value="" class="form-control"/>
                        </div>
                        <div class="mgt5">
                            <input type="text" name="receiverAddressSub" value="" class="form-control"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>배송 메세지</th>
                    <td>
                        <textarea name="orderMemo" rows="3" class="form-control"><?= gd_isset($data['orderMemo']); ?></textarea>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="table-title gd-help-manual">주문상품</div>
            <table class="table table-rows" id="add-goods-result">
                <thead>
                <tr>
                    <th class="width3p"><input type="checkbox" class="js-checkall" name="tmp[checkAll]" data-target-name="sno[]" /></th>
                    <th class="width5p">공급사</th>
                    <th class="width5p"></th>
                    <th >주문상품</th>
                    <th class="width5p">수량</th>
                    <th class="width5p">판매가</th>
                    <th class="width5p">상품할인</th>
                    <th class="width5p">회원할인</th>
                    <th class="width5p">결제금액</th>
                    <th class="width10p">배송비</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="10" class="no-data">주문 할 상품을 추가해주세요.</td>
                </tr>
                </tbody>
                <tfoot class="display-none">
                <tr>
                    <td class="center" colspan="5">합계</td>
                    <td class="width5p center js-total-goods-price">판매가</td>
                    <td class="width5p center js-total-goods-dc-price" >상품할인</td>
                    <td class="width5p center js-total-sum-member-dc-price">회원할인</td>
                    <td class="width5p center js-total-settle-price">결제금액</td>
                    <td class="width10p center js-total-delivery-charge" >배송비</td>
                </tr>
                </tfoot>
            </table>
            <div class="table-action">
                <div class="pull-left">
                    <button type="button" class="btn btn-white js-goods-delete">선택삭제</button>
                </div>
                <div class="pull-right">
                    <button type="button" class="btn btn-white" onclick="goods_search_popup()">상품추가</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="table-title gd-help-manual">결제정보</div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>상품 판매금액</th>
                    <td>
                        <span class="js-total-goods-price">0</span>원
                    </td>
                    <th rowspan="4">쿠폰적용</th>
                    <td rowspan="4">
                        0원
                    </td>
                </tr>
                <tr>
                    <th>상품 할인금액</th>
                    <td class="js-total-dc-price">
                        <span class="js-total-goods-price">0</span>원
                    </td>
                </tr>
                <tr>
                    <th>배송비</th>
                    <td>
                        <span class="js-total-delivery-charge">0</span>원
                    </td>
                </tr>
                <tr>
                    <th>배송비 할인금액</th>
                    <td>
                        <span class="js-delivery-dc-charge">0</span>원
                    </td>
                </tr>
                <tr>
                    <th>사용 예치금</th>
                    <td>
                        <span class="js-use-deposit">0</span>원
                    </td>
                    <th>예치금</th>
                    <td>
                        0원
                    </td>
                </tr>
                <tr>
                    <th>사용 마일리지</th>
                    <td>
                        <span class="js-use-mileage">0</span>원
                    </td>
                    <th>마일리지</th>
                    <td>
                        0원
                    </td>
                </tr>
                <tr>
                    <th>총 결제금액</th>
                    <td>
                        <input type="hidden" name="settlePrice" value="" />
                        <span class="js-total-settle-price">0</span>원
                    </td>
                    <th>적립 마일리지</th>
                    <td>
                        <span class="js-total-give-mileage">0</span>원
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-6">
            <div class="table-title gd-help-manual">결제수단</div>
            <input type="hidden" name="settleKind" value="gb" />
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tbody>
                <tr>
                    <th>입금자명</th>
                    <td colspan="3">
                        <input type="text" name="bankSender" id="bankSenderSelector" value="" class="form-control width-md"/>
                    </td>
                </tr>
                <tr>
                    <th>입금계좌</th>
                    <td colspan="3">
                        <?= gd_select_box('bankAccountSelector', 'bankAccount', $bankData, null, null, '=입금 계좌 선택='); ?>
                    </td>
                </tr>
                <tr>
                    <th>영수증 신청</th>
                    <td colspan="3">
                        <div class="form-inline">
                            <label class="radio-inline">
                                <input type="radio" name="receiptFl" value="n" checked="checked" />
                                신청안함
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="receiptFl" value="r" />
                                현금영수증
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="receiptFl" value="t" />
                                세금계산서
                            </label>
                        </div>
                    </td>
                </tr>
                </tbody>
                <tbody class="js-receipt display-none" id="cash_receipt_info"">
                <tr>
                    <th>발행용도</th>
                    <td colspan="3">
                        <input type="hidden" name="cashCertFl" value="c" />
                        <label class="radio-inline">
                            <input type="radio" name="cashUseFl" value="d" checked="checked" />
                            소득공제용
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="cashUseFl" value="e" checked="checked" />
                            지출증빙용
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>인증종류</th>
                    <td colspan="3">
                        <label class="control-label" id="certNo_hp">
                            휴대폰번호
                            <input type="text" name="cashCertNo[c]" class="form-control js-number" />
                        </label>
                        <label class="control-label" id="certNo_bno">
                            사업자번호
                            <input type="text" name="cashCertNo[b]" class="form-control js-number" />
                        </label>
                    </td>
                </tr>
                </tbody>
                <tbody class="js-receipt display-none" id="tax_info"">
                <tr>
                    <th>사업자번호</th>
                    <td colspan="3">
                        <input type="text" name="taxBusiNo" placeholder="- 없이 입력하세요" class="form-control"/>
                    </td>
                </tr>
                <tr>
                    <th>회사명</th>
                    <td colspan="3">
                        <input type="text" name="taxCompany" class="form-control"/>
                    </td>
                </tr>
                <tr>
                    <th>대표자명</th>
                    <td colspan="3">
                        <input type="text" name="taxCeoNm" class="form-control"/>
                    </td>
                </tr>
                <tr>
                    <th>업태</th>
                    <td>
                        <input type="text" name="taxService" class="form-control"/>
                    </td>
                    <th>종목</th>
                    <td>
                        <input type="text" name="taxItem" class="form-control"/>
                    </td>
                </tr>
                <tr>
                    <th>사업장주소</th>
                    <td colspan="3">
                        <div class="form-inline">
                            <input type="text" name="taxZonecode" value="" size="5" class="form-control"/>
                            <input type="hidden" name="taxZipcode" value=""/>
                            <span id="taxrZipcodeText" class="number display-none">()</span>
                            <input type="button" onclick="postcode_search('taxZonecode', 'taxAddress', 'taxZipcode');" value="우편번호찾기" class="btn btn-sm btn-gray"/>
                        </div>
                        <div class="mgt5">
                            <input type="text" name="taxAddress" value="" class="form-control"/>
                        </div>
                        <div class="mgt5">
                            <input type="text" name="taxAddressSub" value="" class="form-control"/>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-xs-6">
            <div class="table-title gd-help-manual">관리자 메모</div>
            <textarea name="adminMemo" rows="5" class="form-control"><?= gd_isset($data['adminMemo']); ?></textarea>
        </div>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {

        // 상품삭제
        $('.js-goods-delete').click(function(e){

            if( $('input[name="cartSno[]"]:checked').length > 0 ) {
                var snoArr = [];
                $('input[name="cartSno[]"]:checked').each(function(idx){
                    snoArr.push($(this).data('sno'));
                });

                $.post('./order_ps.php', {'mode': 'order_write_delete_goods','cartSno':snoArr.join("<?=INT_DIVISION?>") }, function () {
                    set_goods();
                });


            } else {
                alert('삭제하실 주문상품을 선택해주세요.');
                return false;
            }

        });

        // 주문 폼 체크
        $('#frmOrderWriteForm').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                'orderName': {
                    required: true,
                    maxlength: 30
                },
                'orderPhone[]': {
                    required: true,
                    number: true
                },
                'orderCellPhone': {
                    required: true,
                    number: true
                },
                'orderAddress': {
                    required: true
                },
                'orderAddressSub': {
                    required: true
                },
                'receiverName': {
                    required: true,
                    maxlength: 30
                },
                'receiverPhone[]': {
                    required: true,
                    number: true
                },
                'receiverCellPhone': {
                    required: true,
                    number: true
                },
                'receiverAddress': {
                    required: true
                },
                'receiverAddressSub': {
                    required: true
                },
                'orderMemo': {
                    maxlength: 600
                },
            },
            messages: {
                'orderName': {
                    required: '주문자명을 입력하세요.'
                },
                'orderPhone[]': {
                    required: '전화번호를 입력하세요.'
                },
                'orderCellPhone[]': {
                    required: '휴대폰번호를 입력하세요.'
                },
                'orderAddress': {
                    required: '주소를 입력하세요.'
                },
                'orderAddressSub': {
                    required: '주소를 입력하세요.'
                },
                'receiverName': {
                    required: '주문자명을 입력하세요.'
                },
                'receiverPhone[]': {
                    required: '전화번호를 입력하세요.'
                },
                'receiverCellPhone[]': {
                    required: '휴대폰번호를 입력하세요.'
                },
                'receiverAddress': {
                    required: '주소를 입력하세요.'
                },
                'receiverAddressSub': {
                    required: '주소를 입력하세요.'
                },
            }
        });

        // 회원그룹 선택 레이어 호출
        $('.js-member-layer').click(function(e){
            $('input[name="tmp[memTypeFl]"]').eq(1).prop('checked', true);
            layer_member_search('');
        });

        // 회원선택시 팝업창 확인 누르는 경우 액션 처리
        $('body').on('click', '#btnConfirm', function () {
            var memNo = $(':radio:checked', '.modal-content').val();
            $.get('./order_ps.php?mode=member_info&memNo=' + memNo, function(data){
                data.memNo = memNo;
                insert_address_info(data);
            });
        });

        // 자주쓰는 주소 레이어 호출
        $('.js-address-layer').click(function(e){
            $('input[name="tmp[memTypeFl]"]').eq(0).prop('checked', true);
            $.get('./layer_frequency_address.php', function(data){
                BootstrapDialog.show({
                    size: BootstrapDialog.SIZE_WIDE,
                    title: '자주쓰는 주소',
                    message: $(data)
                });
            });
        });

        // 주문자 정보 동일 체크
        $('.js-order-same').click(function(e){
            if ($(this).is(':checked')) {
                $('input[name="receiverName"]').val($('input[name="orderName"]').val());
                $('select[name="receiverPhone[]"]').val($('select[name="orderPhone[]"]').val());
                $('input[name="receiverPhone[]"]').eq(0).val($('input[name="orderPhone[]"]').eq(0).val());
                $('input[name="receiverPhone[]"]').eq(1).val($('input[name="orderPhone[]"]').eq(1).val());
                $('select[name="receiverCellPhone[]"]').val($('select[name="orderCellPhone[]"]').val());
                $('input[name="receiverCellPhone[]"]').eq(0).val($('input[name="orderCellPhone[]"]').eq(0).val());
                $('input[name="receiverCellPhone[]"]').eq(1).val($('input[name="orderCellPhone[]"]').eq(1).val());
                $('input[name="receiverZipcode"]').val($('input[name="orderZipcode"]').val());
                $('input[name="receiverZonecode"]').val($('input[name="orderZonecode"]').val());
                $('input[name="receiverAddress"]').val($('input[name="orderAddress"]').val());
                $('input[name="receiverAddressSub"]').val($('input[name="orderAddressSub"]').val());
            } else {
                $('input[name="receiverName"]').val('');
                $('select[name="receiverPhone[]"]').val('02');
                $('input[name="receiverPhone[]"]').eq(0).val('');
                $('input[name="receiverPhone[]"]').eq(1).val('');
                $('select[name="receiverCellPhone[]"]').val('010');
                $('input[name="receiverCellPhone[]"]').eq(0).val('');
                $('input[name="receiverCellPhone[]"]').eq(1).val('');
                $('input[name="receiverZipcode"]').val('');
                $('input[name="receiverZonecode"]').val('');
                $('input[name="receiverAddress"]').val('');
                $('input[name="receiverAddressSub"]').val('');
            }
        });

        // 쿠폰 사용 보기 Ajax layer
        $('.js-order-coupon').click(function (e) {
            $.post('layer_order_coupon.php', {'orderNo': '<?= gd_isset($data['orderNo']);?>'}, function (data) {
                layer_popup(data, '쿠폰 정보 보기', 'wide');
            });
        });

        // 환불정보 수정 Ajax layer
        $('.js-refund-view').click(function (e) {
            $.post('layer_refund_view.php', {'orderNo': '<?= gd_isset($data['orderNo']);?>', 'handleSno': $(this).data('handle-sno')}, function (data) {
                layer_popup(data, '환불정보 수정', 'wide');
            });
        });

        // 입금 은행 변경 Ajax layer
        $('.js-bank-change').click(function (e) {
            $.post('layer_bank_selector.php', '', function (data) {
                layer_popup(data, '입금 은행 변경');
            });
        });

        // 영수증 관련 선택
        $('input[name="receiptFl"]').click(function(e){
            var useCode = {
                t: 'tax_info',
                r: 'cash_receipt_info'
            };
            var target = eval('useCode.' + $(this).val());

            $('.js-receipt').addClass('display-none');
            $('#' + target).removeClass('display-none');

            if ($(this).val() == 'r') {
                $('input[name="cashUseFl"]').eq(0).trigger('click');
            }
        });

        // 현금영수증 인증방법 선택 (소득공제용 - 휴대폰 번호(c), 지출증빙용 - 사업자번호(b))
        $('input[name="cashUseFl"]').click(function(e){
            var certCode = $(this).val();

            if (certCode == 'd') {
                $('input[name=\'cashCertFl\']').val('c');
                $('#certNo_hp').show();
                $('#certNo_bno').hide();
            } else {
                $('input[name=\'cashCertFl\']').val('b');
                $('#certNo_hp').hide();
                $('#certNo_bno').show();
            }
        });

        set_goods();
    });

    /**
     * 회원 및 자주쓰는 주소의 데이터를 받아서 처리
     *
     */
    function insert_address_info(data)
    {
        var phone = data.phone.split('-');
        var cellPhone = data.cellPhone.split('-');
        var email = data.email.split('@');
        console.log(email);

        $('input[name="memNo"]').val(data.memNo);
        $('input[name="orderName"]').val(data.memNm);
        $('input[name="info[orderEmail]"]').val(data.email);
        $('input[name="orderZipcode"]').val(data.zipcode);
        $('input[name="orderZonecode"]').val(data.zonecode);
        $('input[name="orderAddress"]').val(data.address);
        $('input[name="orderAddressSub"]').val(data.addressSub);
        if (data.zipcode != '') {
            $('#orderZipcodeText').show();
            $('#orderZipcodeText').html('(' + data.zipcode + ')');
        } else {
            $('#orderZipcodeText').hide();
        }

        $('select[name="orderPhone[]"]').val(phone[0]);
        $('input[name="orderPhone[]"]').eq(0).val(phone[1]);
        $('input[name="orderPhone[]"]').eq(1).val(phone[2]);

        $('select[name="orderCellPhone[]"]').val(cellPhone[0]);
        $('input[name="orderCellPhone[]"]').eq(0).val(cellPhone[1]);
        $('input[name="orderCellPhone[]"]').eq(1).val(cellPhone[2]);

        $('.js-email-select').attr('data-origin-data', email[1]);
        init_email_select();
        $('.js-email-select input').eq(0).val(email[0]);
        $('.js-email-select input').eq(1).val(email[1]);

        layer_close();
    }

    /**
     * 상품 선택
     *
     * @param string orderNo 주문 번호
     */
    function goods_search_popup()
    {
        var memTypeFl = $('input[name="tmp[memTypeFl]"]:checked').length;
        var orderName = $('input[name="orderName"]').val();

        if(memTypeFl == '1' && orderName !='' ) {
            window.open('./popup_order_goods.php', 'popup_order_goods', 'width=1130, height=710, scrollbars=no');
        } else {
            alert("주문자 정보를 작성해주세요.");
        }
        return false;
    }

    /**
     * 선택 상품 세팅
     *
     */
    function set_goods(){

        $.post('./order_ps.php', {'mode': 'order_write_search_goods' }, function (frmData) {


            $(".js-total-sum-member-dc-price").html(frmData.cartPrice.totalSumMemberDcPrice);
            $(".js-total-goods-dc-price").html(frmData.cartPrice.totalGoodsDcPrice);
            $(".js-total-goods-price").html(frmData.cartPrice.totalGoodsPrice);
            $(".js-total-delivery-charge").html(frmData.cartPrice.totalDeliveryCharge);
            $(".js-total-settle-price").html(frmData.cartPrice.totalSettlePrice);
            $(".js-total-dc-price").html(frmData.cartPrice.totalSumMemberDcPrice+frmData.cartPrice.totalGoodsDcPrice);

            $("input[name=settlePrice]").val(frmData.cartPrice.totalSettlePrice);

            var goodHtml = "";

            if(frmData.cartPrice.totalSettlePrice > 0) {

                $("#add-goods-result tfoot").show();

                $.each(frmData.cartInfo, function (key, val) {

                    $.each(val, function (key1, val1) {

                        $.each(val1, function (key2, val2) {
                            console.log(val2);

                            var tmp = $(goodHtml).clone();
                            var dataIndex  = tmp.find("input[name='cartSno[]']").length;

                            var goodsNm = val2.goodsNm;

                            if(val2.option.length) {
                                var optionInfo = [];
                                $.each(val2.option, function (optKey, optVal) {
                                    optionInfo.push(optVal.optionName+":"+optVal.optionValue);
                                });
                                goodsNm +=  "<br>"+optionInfo.join(",");
                            }

                            if(val2.optionText.length) {
                                var optionTextInfo = [];
                                $.each(val2.optionText, function (optTextKey, optTextVal) {
                                    optionTextInfo.push(optTextVal.optionName+":"+optTextVal.optionValue);
                                });
                                goodsNm +=  "<br>"+optionTextInfo.join(",");
                            }


                            var goodsPrice = val2.price.goodsPriceSum+val2.price.optionPriceSum+val2.price.optionTextPriceSum;
                            var memberDcPrice = val2.price.memberDcPrice+val2.price.memberOverlapDcPrice;

                            var deliveryText = "";
                            if(val2.goodsDeliveryFl =='y') {
                                deliveryText += frmData.setDeliveryInfo[key1]['goodsDeliveryMethod'];
                                if(frmData.setDeliveryInfo[key1]['fixFl'] =='free') {
                                    deliveryText += "무료배송";
                                } else {
                                    if(frmData.setDeliveryInfo[key1]['goodsDeliveryWholeFreeFl'] == 'y' ) {
                                        deliveryText += "조건에 따른 배송비 무료";
                                        if(frmData.setDeliveryInfo[key1]['goodsDeliveryWholeFreePrice']) {
                                            deliveryText += frmData.setDeliveryInfo[key1]['goodsDeliveryWholeFreePrice'];
                                        }
                                    } else {
                                        if(frmData.setDeliveryInfo[key1]['goodsDeliveryCollectFl'] === 'later' ) {
                                            if(frmData.setDeliveryInfo[key1_]['goodsDeliveryCollectPrice']) {
                                                deliveryText += frmData.setDeliveryInfo[key1]['goodsDeliveryCollectPrice']+"<br/>(상품수령 시 결제)";
                                            }
                                        } else {
                                            if(frmData.setDeliveryInfo[key1]['goodsDeliveryPrice']) {
                                                deliveryText += frmData.setDeliveryInfo[key1]['goodsDeliveryPrice'];

                                            } else{
                                                deliveryText += "무료배송";
                                            }
                                        }
                                    }
                                }
                            } else {
                                deliveryText +=val2.goodsDeliveryMethod;
                                if(val2.goodsDeliveryFixFl == 'free') {
                                    deliveryText += "무료배송";
                                } else {
                                    if(val2.goodsDeliveryWholeFreeFl === 'y') {
                                        deliveryText += "조건에 따른 배송비 무료";
                                        if(val2.price['goodsDeliveryWholeFreePrice']) {
                                            deliveryText += val2.price['goodsDeliveryWholeFreePrice'];
                                        }
                                    } else {
                                        if(val2.goodsDeliveryCollectFl === 'later' ) {
                                            if(val2.price['goodsDeliveryCollectPrice']) {
                                                deliveryText +=  val2.price['goodsDeliveryCollectPrice']+"<br>(상품수령 시 결제)";
                                            }
                                        } else {
                                            if(val2.price['goodsDeliveryPrice']) {
                                                deliveryText += val2.price['goodsDeliveryPrice'];
                                            } else {
                                                deliveryText += "무료배송";
                                            }
                                        }
                                    }
                                }
                            }


                            var complied = _.template($('#goodsTemplate').html());
                            goodHtml += complied({
                                cartSno : val2.sno,
                                dataIndex : dataIndex,
                                dataRowCount: 1+val2.addGoods.length,
                                dataScmNm: frmData.cartScmInfo[key]['companyNm'],
                                dataGoodsImage:val2.goodsImage,
                                dataGoodsInfo: goodsNm,
                                dataGoodsCount: val2.goodsCnt,
                                dataGoodsPrice: goodsPrice,
                                dataGoodsDcPrice: val2.goodsDcPrice,
                                dataMemberDcPrice : memberDcPrice,
                                dataSettlePrice: val2.price.goodsPriceSubtotal,
                                dataDelivery : deliveryText
                            });

                            if(val2.addGoods.length > 0 ) {

                                $.each(val2.addGoods, function (agKey, agVal) {
                                    var complied = _.template($('#addGoodsTemplate').html());
                                    goodHtml += complied({
                                        dataIndex : dataIndex,
                                        dataAddGoodsImage : agVal.addGoodsImage,
                                        dataAddGoodsInfo: agVal.addGoodsNm,
                                        dataAddGoodsCount: agVal.addGoodsCnt,
                                        dataAddGoodsPrice: agVal.addGoodsPrice
                                    });

                                });

                            }

                        });
                    });

                });
                    $("#add-goods-result tbody").html(goodHtml);
            } else {
                $("#add-goods-result tbody").html('<td colspan="10" class="no-data">주문 할 상품을 추가해주세요.</td>');
                $("#add-goods-result tfoot").hide();
            }
        });
    }
    //-->
</script>
<script type="text/html" id="goodsTemplate">
    <tr class="order-add-goods-<%=dataIndex%>" data-index="<%=dataIndex%>">
        <td class="center" rowspan="<%=dataRowCount%>">
            <input type="checkbox" name="cartSno[]" data-sno="<%=cartSno%>">
        </td>
        <td class="center" rowspan="<%=dataRowCount%>"><%=dataScmNm%></td>
        <td ><%=dataGoodsImage%></td>
        <td ><%=dataGoodsInfo%></td>
        <td class="center"><%=dataGoodsCount%></td>
        <td class="center"><%=dataGoodsPrice%></td>
        <td class="center" rowspan="<%=dataRowCount%>"><%=dataGoodsDcPrice%></td>
        <td class="center" rowspan="<%=dataRowCount%>"><%=dataMemberDcPrice%></td>
        <td class="center"  rowspan="<%=dataRowCount%>"><%=dataSettlePrice%></td>
        <td class="center" rowspan="<%=dataRowCount%>"><%=dataDelivery%></td>
    </tr>
</script>
<script type="text/html" id="addGoodsTemplate">
    <tr class="order-add-goods-<%=dataIndex%>" data-index="<%=dataIndex%>">
        <td><%=dataAddGoodsImage%></td>
        <td><%=dataAddGoodsInfo%></td>
        <td class="center"><%=dataAddGoodsCount%></td>
        <td class="center"><%=dataAddGoodsPrice%></td>
    </tr>
</script>
