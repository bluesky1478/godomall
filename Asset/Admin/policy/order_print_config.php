<form id="frmOrderPrint" name="frmOrderPrint" action="order_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="updateOrderPrint" />

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <!-- 거래명세서 출력 설정 -->
    <div class="table-title gd-help-manual">
        거래명세서 출력 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tr>
            <th>쇼핑몰 동시 적용</th>
            <td>
                <div class="form-inline">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="orderPrintSameDisplay" value="y" <?php echo gd_isset($checked['orderPrintSameDisplay']['y']); ?>/>
                        쇼핑몰에 적용 (고객 동일 조건 출력)
                    </label>
                    <div class="notice-info">
                        쇼핑몰 마이페이지에서 고객이 직접 출력할 수 있는 거래명세서에도 아래 조건이 동일하게 적용되며,<br />
                        <span style="color: red;">체크 해제시, 아래 설정된 조건들은 어드민 출력시에만 적용</span>되며, 고객은 기본값 조건으로만 적용되어(하단 도움말 참조) 출력됩니다.
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>수량 합계 표시</th>
            <td>
                <div class="form-inline mgb15">
                    <label class="radio-inline">
                        <input type="radio" name="orderPrintQuantityDisplay" value="y" <?php echo gd_isset($checked['orderPrintQuantityDisplay']['y']); ?> /> 사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="orderPrintQuantityDisplay" value="n" <?php echo gd_isset($checked['orderPrintQuantityDisplay']['n']); ?> /> 사용안함
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>상품코드 항목</th>
            <td>
                <div class="form-inline mgb15">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="orderPrintGoodsNo" value="y" <?php echo gd_isset($checked['orderPrintGoodsNo']['y']); ?> /> 상품코드
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="orderPrintGoodsCd" value="y" <?php echo gd_isset($checked['orderPrintGoodsCd']['y']); ?> /> 자체상품코드
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>합계 금액 포함 여부</th>
            <td>
                <div class="form-inline mgb15">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="orderPrintAmountDelivery" value="y" <?php echo gd_isset($checked['orderPrintAmountDelivery']['y']); ?>/>
                        배송비
                    </label>

                    <label class="checkbox-inline">
                        <input type="checkbox" name="orderPrintAmountDiscount" value="y" <?php echo gd_isset($checked['orderPrintAmountDiscount']['y']); ?>/>
                        할인금액
                    </label>

                    <label class="checkbox-inline">
                        <input type="checkbox" name="orderPrintAmountMileage" value="y" <?php echo gd_isset($checked['orderPrintAmountMileage']['y']); ?>/>
                        마일리지
                    </label>

                    <label class="checkbox-inline">
                        <input type="checkbox" name="orderPrintAmountDeposit" value="y" <?php echo gd_isset($checked['orderPrintAmountDeposit']['y']); ?>/>
                        예치금
                    </label>
                </div>

                <div class="notice-info">
                    거래 명세서 하단에 출력되는 [합계 금액]에 포함되는 금액 항목을 설정합니다.<br />
                    체크하지 않으면 세부항목에는 나오나, 합계금액에 해당 금액은 합산되어 출력되지 않습니다.
                </div>
            </td>
        </tr>
        <tr>
            <th>사업자 회원</th>
            <td>
                <div class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="orderPrintBusinessInfo" value="y" <?php echo gd_isset($checked['orderPrintBusinessInfo']['y']); ?>/>
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="orderPrintBusinessInfo" value="n" <?php echo gd_isset($checked['orderPrintBusinessInfo']['n']); ?>/>
                        사용안함
                    </label>
                    <div class="notice-info">주문자가 사업자 회원인 경우, 거래명세서 내 [공급받는자] 영역에 사업자 정보를 표기하여 출력합니다.</div>
                </div>
                <table id="orderPrintBusinessInfo" class="table table-cols" style="display:<?php echo gd_isset($checked['orderPrintBusinessInfo']['y']) ? '' : 'none' ?>">
                    <colgroup>
                        <col class="width-lg"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>사용 항목</th>
                        <td>
                            <div class="mgb5">
                                <label class="radio-inline">
                                    <input type="radio" name="orderPrintBusinessInfoType" value="companyWithNumber" <?php echo gd_isset($checked['orderPrintBusinessInfoType']['companyWithNumber']); ?> /> 상호명 + 등록번호
                                </label>

                                <label class="radio-inline">
                                    <input type="radio" name="orderPrintBusinessInfoType" value="companyWithOrder" <?php echo gd_isset($checked['orderPrintBusinessInfoType']['companyWithOrder']); ?> /> 상호명 + 주문자명
                                </label>

                                <label class="radio-inline">
                                    <input type="radio" name="orderPrintBusinessInfoType" value="company" <?php echo gd_isset($checked['orderPrintBusinessInfoType']['company']); ?> /> 상호명
                                </label>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th>하단 추가 정보 표기</th>
            <td>
                <div class="form-inline mgb15">
                    <label class="radio-inline">
                        <input type="radio" name="orderPrintBottomInfo" value="y" <?php echo gd_isset($checked['orderPrintBottomInfo']['y']); ?> /> 사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="orderPrintBottomInfo" value="n" <?php echo gd_isset($checked['orderPrintBottomInfo']['n']); ?> /> 사용안함
                    </label>
                </div>

                <table id="orderPrintBottomInfo" class="table table-cols" style="display:<?php echo gd_isset($checked['orderPrintBottomInfo']['y']) ? '' : 'none' ?>">
                    <colgroup>
                        <col class="width-lg"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>사용 항목</th>
                        <td>
                            <div class="mgb5">
                                <label class="radio-inline">
                                    <input type="radio" name="orderPrintBottomInfoType" value="c" <?php echo gd_isset($checked['orderPrintBottomInfoType']['c']); ?> /> 고객요청사항
                                </label>

                                <label class="radio-inline">
                                    <input type="radio" name="orderPrintBottomInfoType" value="a" <?php echo gd_isset($checked['orderPrintBottomInfoType']['a']); ?> /> 관리자메모
                                </label>

                                <label class="radio-inline">
                                    <input type="radio" name="orderPrintBottomInfoType" value="s" <?php echo gd_isset($checked['orderPrintBottomInfoType']['s']); ?> /> 직접 입력
                                </label>

                                <textarea name="orderPrintBottomInfoText" id="orderPrintBottomInfoText" rows="3" class="form-control mgt15" placeholder="[직접 입력] 선택 시, 작성해 주세요."><?= gd_isset($data['orderPrintBottomInfoText']); ?></textarea>

                                <div class="notice-info">거래명세서 하단에 필요에 따라 추가정보를 출력하실 수 있습니다.</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- 거래명세서 출력 설정 -->

    <!-- 주문내역서 출력 설정 -->
    <div class="table-title gd-help-manual">
        주문내역서 출력 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tr>
            <th>쇼핑몰 동시 적용</th>
            <td>
                <div class="form-inline">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="orderPrintOdSameDisplay" value="y" <?php echo gd_isset($checked['orderPrintOdSameDisplay']['y']); ?>/>
                        주문내역서 (고객용) 동일 적용
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>상품코드 항목</th>
            <td>
                <div class="form-inline">
                    <div class="form-inline">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="orderPrintOdGoodsCode" value="y" <?php echo gd_isset($checked['orderPrintOdGoodsCode']['y']); ?>/> 상품코드
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="orderPrintOdSelfGoodsCode" value="y" <?php echo gd_isset($checked['orderPrintOdSelfGoodsCode']['y']); ?>/> 자체상품코드
                        </label>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>공급사명 표시</th>
            <td>
                <div class="form-inline">
                    <div class="form-inline mgb15">
                        <label class="radio-inline">
                            <input type="radio" name="orderPrintOdScmDisplay" value="y" <?php echo gd_isset($checked['orderPrintOdScmDisplay']['y']); ?> /> 표시
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="orderPrintOdScmDisplay" value="n" <?php echo gd_isset($checked['orderPrintOdScmDisplay']['n']); ?> /> 표시안함
                        </label>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>상품이미지 표시</th>
            <td>
                <div class="form-inline">
                    <div class="form-inline mgb15">
                        <label class="radio-inline">
                            <input type="radio" name="orderPrintOdImageDisplay" value="y" <?php echo gd_isset($checked['orderPrintOdImageDisplay']['y']); ?> /> 표시
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="orderPrintOdImageDisplay" value="n" <?php echo gd_isset($checked['orderPrintOdImageDisplay']['n']); ?> /> 표시안함
                        </label>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>결제정보 & 수단표시</th>
            <td>
                <div class="form-inline">
                    <div class="form-inline mgb15">
                        <label class="radio-inline">
                            <input type="radio" name="orderPrintOdSettleInfoDisplay" value="y" <?php echo gd_isset($checked['orderPrintOdSettleInfoDisplay']['y']); ?> /> 표시
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="orderPrintOdSettleInfoDisplay" value="n" <?php echo gd_isset($checked['orderPrintOdSettleInfoDisplay']['n']); ?> /> 표시안함
                        </label>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>관리자메모 표시</th>
            <td>
                <div class="form-inline">
                    <div class="form-inline mgb15">
                        <label class="radio-inline">
                            <input type="radio" name="orderPrintOdAdminMemoDisplay" value="y" <?php echo gd_isset($checked['orderPrintOdAdminMemoDisplay']['y']); ?> /> 표시
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="orderPrintOdAdminMemoDisplay" value="n" <?php echo gd_isset($checked['orderPrintOdAdminMemoDisplay']['n']); ?> /> 표시안함
                        </label>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>하단 추가 정보 표시</th>
            <td>
                <div class="form-inline mgb15">
                    <label class="radio-inline">
                        <input type="radio" name="orderPrintOdBottomInfo" value="y" <?php echo gd_isset($checked['orderPrintOdBottomInfo']['y']); ?> /> 사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="orderPrintOdBottomInfo" value="n" <?php echo gd_isset($checked['orderPrintOdBottomInfo']['n']); ?> /> 사용안함
                    </label>
                </div>

                <textarea name="orderPrintOdBottomInfoText" id="orderPrintOdBottomInfoText" rows="3" class="form-control mgt15"><?= gd_isset($data['orderPrintOdBottomInfoText']); ?></textarea>

                <div class="notice-info">주문내역서 하단에 필요에 따라 추가정보를 출력하실 수 있습니다.</div>
            </td>
        </tr>
    </table>
    <!-- 주문내역서 출력 설정 -->

    <!-- 주문내역서 (고객용) 출력 설정 -->
    <div id="orderPrintOdCustomerArea" class="display-none">
        <div class="table-title gd-help-manual">
            주문내역서(고객용) 출력 설정
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>상품코드 항목</th>
                <td>
                    <div class="form-inline">
                        <div class="form-inline">
                            <label class="checkbox-inline">
                                <input type="checkbox" name="orderPrintOdCsGoodsCode" value="y" <?php echo gd_isset($checked['orderPrintOdCsGoodsCode']['y']); ?>/> 상품코드
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="orderPrintOdCsSelfGoodsCode" value="y" <?php echo gd_isset($checked['orderPrintOdCsSelfGoodsCode']['y']); ?>/> 자체상품코드
                            </label>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>상품이미지 표시</th>
                <td>
                    <div class="form-inline">
                        <div class="form-inline mgb15">
                            <label class="radio-inline">
                                <input type="radio" name="orderPrintOdCsImageDisplay" value="y" <?php echo gd_isset($checked['orderPrintOdCsImageDisplay']['y']); ?> /> 표시
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="orderPrintOdCsImageDisplay" value="n" <?php echo gd_isset($checked['orderPrintOdCsImageDisplay']['n']); ?> /> 표시안함
                            </label>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>결제정보 & 수단표시</th>
                <td>
                    <div class="form-inline">
                        <div class="form-inline mgb15">
                            <label class="radio-inline">
                                <input type="radio" name="orderPrintOdCsSettleInfoDisplay" value="y" <?php echo gd_isset($checked['orderPrintOdCsSettleInfoDisplay']['y']); ?> /> 표시
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="orderPrintOdCsSettleInfoDisplay" value="n" <?php echo gd_isset($checked['orderPrintOdCsSettleInfoDisplay']['n']); ?> /> 표시안함
                            </label>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>관리자메모 표시</th>
                <td>
                    <div class="form-inline">
                        <div class="form-inline mgb15">
                            <label class="radio-inline">
                                <input type="radio" name="orderPrintOdCsAdminMemoDisplay" value="y" <?php echo gd_isset($checked['orderPrintOdCsAdminMemoDisplay']['y']); ?> /> 표시
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="orderPrintOdCsAdminMemoDisplay" value="n" <?php echo gd_isset($checked['orderPrintOdCsAdminMemoDisplay']['n']); ?> /> 표시안함
                            </label>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>하단 추가 정보 표시</th>
                <td>
                    <div class="form-inline mgb15">
                        <label class="radio-inline">
                            <input type="radio" name="orderPrintOdCsBottomInfo" value="y" <?php echo gd_isset($checked['orderPrintOdCsBottomInfo']['y']); ?> /> 사용함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="orderPrintOdCsBottomInfo" value="n" <?php echo gd_isset($checked['orderPrintOdCsBottomInfo']['n']); ?> /> 사용안함
                        </label>
                    </div>

                    <textarea name="orderPrintOdCsBottomInfoText" id="orderPrintOdCsBottomInfoText" rows="3" class="form-control mgt15"><?= gd_isset($data['orderPrintOdCsBottomInfoText']); ?></textarea>

                    <div class="notice-info">주문내역서 하단에 필요에 따라 추가정보를 출력하실 수 있습니다.</div>
                </td>
            </tr>
        </table>
    </div>
    <!-- 주문내역서 (고객용) 출력 설정 -->
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 폼체크
        $('#frmOrderPrint').validate({
            rules: {
                orderPrintBottomInfo: {
                    required: true
                },
                orderPrintBottomInfoType: {
                    required : function(){
                        return $("input[name='orderPrintBottomInfo']:checked").val() === 'y';
                    }
                },
                orderPrintBottomInfoText: {
                    required : function(){
                        return $("input[name='orderPrintBottomInfoType']:checked").val() === 's';
                    },
                    minlength: 1
                },
                orderPrintOdBottomInfoText: {
                    required : function(){
                        return $("input[name='orderPrintOdBottomInfo']:checked").val() === 'y';
                    },
                    minlength: 1
                },
                orderPrintOdCsBottomInfoText: {
                    required : function(){
                        return $("input[name='orderPrintOdCsBottomInfo']:checked").val() === 'y';
                    },
                    minlength: 1
                }
            },
            messages: {
                orderPrintBottomInfo: '하단 추가 정보 표기를 선택해 주세요.',
                orderPrintBottomInfoType: '사용 항목을 선택해 주세요.',
                orderPrintBottomInfoText: '입력할 문구를 작성해 주세요.',
                orderPrintOdBottomInfoText: '하단 추가 정보 표기를 선택해 주세요.',
                orderPrintOdCsBottomInfoText: '하단 추가 정보 표기를 선택해 주세요.',
            }
        });

        $('input:checkbox[name="orderPrintOdSameDisplay"]').click(function() {
            customerDisplay($(this));
        });

        customerDisplay($('input:checkbox[name="orderPrintOdSameDisplay"]'));

        $('input:radio[name="orderPrintBusinessInfo"]').click(function() {
            if($(this).val() == 'y') {
                $("#orderPrintBusinessInfo").show();
            }
            else {
                $("#orderPrintBusinessInfo").hide();
            }
        })
        $('input:radio[name="orderPrintBottomInfo"]').click(function() {
            if($(this).val() == 'y') {
                $("#orderPrintBottomInfo").show();
            }
            else {
                $("#orderPrintBottomInfo").hide();
            }
        })
    });

    function customerDisplay(el)
    {
        if(el.prop("checked") !== true){
            $("#orderPrintOdCustomerArea").removeClass("display-none");
        }
        else {
            $("#orderPrintOdCustomerArea").addClass("display-none");
        }
    }
    //-->
</script>
