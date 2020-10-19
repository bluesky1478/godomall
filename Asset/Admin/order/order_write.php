<form id="frmOrderWriteForm" name="frmOrderWriteForm" target="ifrmProcess" action="./order_ps.php" method="post">
    <input type="hidden" name="mode" value="save_write_order" />
    <input type="hidden" name="memNo" value="0" />
    <input type="hidden" name="orderTypeFl" value="write" />
    <input type="hidden" name="overseasSettleCurrency" value="KRW" />
    <input type="hidden" name="chooseMileageCoupon" value="" />
    <input type="hidden" name="deliveryFree" value="n" />
    <input type="hidden" name="useBundleGoods" value="1" />
    <!-- 복수배송지 사용 여부 -->
    <input type="hidden" name="isUseMultiShipping" value="<?=$isUseMultiShipping?>" />
    <input type="hidden" name="multiShippingFl" value="" />
    <input type="hidden" name="multiShippingCartSno" value="" />
    <input type="hidden" name="deliveryVisit" value="n" />

    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <div class="btn-group" id="selfOrderWriteSaveBtnArea">
            <input type="submit" value="저장" class="btn btn-red">
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="table-title gd-help-manual">주문상품</div>
            <table class="table table-rows" id="add-goods-result">
                <thead>
                <tr>
                    <th class="width3p"><input type="checkbox" class="js-checkall" name="tmp[checkAll]" data-target-name="cartSno[]" /></th>
                    <th class="width5p">공급사</th>
                    <th class="width5p"></th>
                    <th >주문상품</th>
                    <th class="width7p">수량</th>
                    <th class="width5p">판매가</th>
                    <th class="width10p">할인/적립</th>
                    <th class="width5p">합계금액</th>
                    <th class="width10p">배송비</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="9" class="no-data">주문 할 상품을 추가해주세요.</td>
                </tr>
                </tbody>
            </table>
            <div class="table-action">
                <div class="pull-left">
                    <button type="button" class="btn btn-white js-goods-delete">선택삭제</button>
                </div>
                <div class="pull-right">
                    <button type="button" class="btn btn-white display-none" id="selfOrderWriteMemberCart">회원 장바구니 상품추가</button>
                    <button type="button" class="btn btn-white" onclick="goods_search_popup()">상품추가</button>
                </div>
            </div>
        </div>
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
                    <th class="require">회원구분</th>
                    <td class="form-inline">
                        <label for="selfOrderWriteNonMember" class="radio hand">
                            <input type="radio" name="memberTypeFl" value="n" id="selfOrderWriteNonMember" checked="checked" /> 비회원
                            &nbsp;
                            <button type="button" class="btn btn-gray btn-sm js-address-layer" id="selfOrderWriteRepeatAddress">자주쓰는 주소</button>
                        </label>

                        <label for="selfOrderWriteMember" class="radio hand mgl20">
                            <input type="radio" name="memberTypeFl" value="y" id="selfOrderWriteMember" /> 회원
                            &nbsp;
                            <button type="button" class="btn btn-gray btn-sm js-member-layer" id="selfOrderWriteSelectMember" disabled="disabled">회원 선택</button>
                        </label>
                    </td>
                </tr>
                <tr class="self-order-member-relation-area display-none">
                    <th class="require">회원ID</th>
                    <td class="form-inline">
                        <input type="text" class="form-control width-2xl" name="memId" id="selfOrderWriteMemId" readonly="readonly" />
                    </td>
                </tr>
                <tr>
                    <th class="require">주문자명</th>
                    <td class="form-inline">
                        <input type="text" class="form-control width-md" name="orderName" />
                    </td>
                </tr>
                <tr>
                    <th>전화번호</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="orderPhone" value="" maxlength="14" class="form-control js-number-only width-md"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="require">휴대폰번호</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="orderCellPhone" value="" maxlength="14" class="form-control js-number-only width-md"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="require">이메일</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="orderEmail" value="" class="form-control width-md" />

                            <select id="emailDomain" class="form-control" style="width: 120px;">
                                <?php foreach($emailDomain as $key => $value){ ?>
                                    <option value="<?=$key?>"><?=$value?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="require">주소</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="orderZonecode" value="" size="5" class="form-control" readonly="readonly" />
                            <input type="hidden" name="orderZipcode" value=""/>
                            <span id="orderZipcodeText" class="number display-none">()</span>
                            <input type="button" onclick="postcode_search('orderZonecode', 'orderAddress', 'orderZipcode');" value="우편번호찾기" class="btn btn-sm btn-gray"/>
                        </div>
                        <div class="mgt5">
                            <input type="text" name="orderAddress" value="" class="form-control" readonly="readonly" />
                        </div>
                        <div class="mgt5">
                            <input type="text" name="orderAddressSub" value="" class="form-control"/>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="js-receiver-info-html-area-main"></div>
    </div>

    <!-- 복수배송지 사용시 추가 배송지가 들어갈 구역-->
    <div class="js-receiver-info-html-area-sub row"></div>

    <!-- 사은품 선택 -->
    <div class="row display-none" id="selfOrderGiftArea">
        <div class="col-xs-12">
            <div class="table-title gd-help-manual">사은품 선택</div>
            <table class="table table-cols self-order-gift-table">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <!-- 사은품 선택 -->

    <!-- 추가 정보 -->
    <div class="row display-none" id="selfOrderAddFieldArea">
        <div class="col-xs-12">
            <input type="hidden" name="addFieldConf" value="" />
            <div class="table-title gd-help-manual">추가 정보</div>
            <table class="table table-cols self-order-addField-table">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <!-- 추가 정보 -->

    <div class="row">
        <div class="col-xs-12">
            <div class="table-title gd-help-manual">결제정보</div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                    <!--                    <col class="width-md"/>-->
                    <!--                    <col/>-->
                </colgroup>
                <tr>
                    <th>상품 합계 금액</th>
                    <td>
                        <span class="js-total-goods-price">0</span>원
                    </td>
                    <!--                    <th rowspan="4">쿠폰적용</th>-->
                    <!--                    <td rowspan="4">-->
                    <!--                        0원-->
                    <!--                    </td>-->
                </tr>
                <tr>
                    <th>배송비</th>
                    <td>
                        <span class="js-total-goods-delivery-policy-charge">0</span>원
                        <span class="js-multi-shipping-policy-charge-text"></span>
                    </td>
                </tr>
                <tr class="js-total-delivery-area-charge-area display-none">
                    <th>지역별 배송비</th>
                    <td>
                        <span class="js-total-delivery-area-charge">0</span>원
                        <span class="js-multi-shipping-area-charge-text"></span>
                    </td>
                </tr>
                <tr>
                    <th>할인 및 적립</th>
                    <td>
                        <span class="js-total-dc-price">0</span>
                    </td>
                </tr>
                <tr class="self-order-member-relation-coupon-area display-none">
                    <th>쿠폰 사용</th>
                    <td>
                        <input type="hidden" name="couponApplyOrderNo" value="" />
                        <input type="hidden" name="totalCouponOrderDcPrice" value="" />
                        <input type="hidden" name="totalCouponOrderPrice" value="" />
                        <input type="hidden" name="totalCouponOrderMileage" value="" />
                        <input type="hidden" name="totalCouponDeliveryDcPrice" value="" />
                        <input type="hidden" name="totalCouponDeliveryPrice" value="" />
                        <div class="pay-benefits order-coupon-benefits display-none">
                            <p class="self-order-sale-icon">주문할인 : <strong>(-) <?=gd_currency_symbol()?><b id="useDisplayCouponDcPrice"><?=gd_money_format(0)?></b><?=gd_currency_string()?></strong></p>
                            <p class="self-order-sale-icon">배송비할인 : <strong>(-) <?=gd_currency_symbol()?><b id="useDisplayCouponDelivery"><?=gd_money_format(0)?></b><?=gd_currency_string()?></strong></p>
                            <p class="self-order-mileage-icon">
                                적립 <?=$mileage['name']?> : <strong>(+) <b id="useDisplayCouponMileage"><?=gd_money_format(0)?></b><?=$mileage['unit']?></strong>
                            </p>
                        </div>
                        <button type="button" class="btn btn-white" id="selfOrderWriteCouponOrder">쿠폰 조회 및 적용</button>
                        <span class="notice-info mgl10">쿠폰 수동발급은 <a href="/promotion/coupon_list.php" class="btn-link" target="_blank">프로모션>쿠폰 관리>쿠폰 리스트</a>에서 가능합니다.</span>
                    </td>
                </tr>
                <?php if($mileage['payUsableFl'] === 'y'){ ?>
                    <tr class="self-order-member-relation-mileage-area display-none">
                        <th><?=$mileage['name']?> 사용</th>
                        <td>
                            <input type="text" name="useMileage" id="selfOrderUseMileage" value="0" class="form-control width-md display-inline js-number"/> <?=$mileage['unit']?>
                            <label class="checkbox-inline mgl10">
                                <input type="checkbox" id="selfOrderUseMileageAll" value="y" />전액 사용하기
                            </label>
                            <span style="color: #117ef9;">(보유 <?=$mileage['name']?> : <span id="selfOrderHaveMileage" data-mileagePrice="0">0</span><?=$mileage['unit']?>)</span>
                            <div style="color: #117ef9"><span id="selfOrderWriteMileageText"></span></div>
                        </td>
                    </tr>
                <?php } ?>
                <?php if($depositUse['payUsableFl'] === 'y'){ ?>
                    <tr class="self-order-member-relation-deposit-area display-none">
                        <th><?=$depositUse['name']?> 사용</th>
                        <td>
                            <input type="text" name="useDeposit" id="selfOrderUseDeposit" value="0" class="form-control width-md display-inline js-number"/> <?=$depositUse['unit']?>
                            <label class="checkbox-inline mgl10">
                                <input type="checkbox" id="selfOrderUseDepositAll" value="y" />전액 사용하기
                            </label>
                            <span style="color: #117ef9;">(보유 <?=$depositUse['name']?> : <span id="selfOrderHaveDeposit" data-depositPrice="0">0</span><?=$depositUse['unit']?>)</span>
                        </td>
                    </tr>
                <?php } ?>


                <!--                <tr>-->
                <!--                    <th>배송비 할인금액</th>-->
                <!--                    <td>-->
                <!--                        <span class="js-delivery-dc-charge">0</span>원-->
                <!--                    </td>-->
                <!--                </tr>-->
                <!--                <tr>-->
                <!--                    <th>사용 예치금</th>-->
                <!--                    <td>-->
                <!--                        <span class="js-use-deposit">0</span>원-->
                <!--                    </td>-->
                <!--                    <th>예치금</th>-->
                <!--                    <td>-->
                <!--                        0원-->
                <!--                    </td>-->
                <!--                </tr>-->
                <!--                <tr>-->
                <!--                    <th>사용 마일리지</th>-->
                <!--                    <td>-->
                <!--                        <span class="js-use-mileage">0</span>원-->
                <!--                    </td>-->
                <!--                    <th>마일리지</th>-->
                <!--                    <td>-->
                <!--                        0원-->
                <!--                    </td>-->
                <!--                </tr>-->
                <tr>
                    <th>최종 결제 금액</th>
                    <td>
                        <input type="hidden" name="settlePrice" value="" />
                        <span class="js-total-settle-price">0</span>원
                    </td>
                    <!--                    <th>적립 마일리지</th>-->
                    <!--                    <td>-->
                    <!--                        <span class="js-total-give-mileage">0</span>원-->
                    <!--                    </td>-->
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
                <tr class="self-order-bank-area">
                    <th>입금자명</th>
                    <td colspan="3">
                        <input type="text" name="bankSender" id="bankSenderSelector" value="" class="form-control width-md"/>
                    </td>
                </tr>
                <tr class="self-order-bank-area">
                    <th>입금계좌</th>
                    <td colspan="3">
                        <?php if (empty($bankData) === false) { ?>
                            <?= gd_select_box('bankAccountSelector', 'bankAccount', $bankData, null, null, '=입금 계좌 선택='); ?>
                        <?php } else { ?>
                            <span class="notice-danger">기본설정 > 결제 정책 > 무통장 입금 은행 관리등록을 해주세요.</span>
                            <a href="../policy/settle_bank.php" target="_blank" class="btn btn-white btn-sm">무통장 입금 은행 관리 등록하기</a>
                        <?php } ?>
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
                <tbody class="js-receipt display-none" id="cash_receipt_info">
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
                            <input type="text" name="cashCertNo[c]" class="form-control js-number-only" />
                        </label>
                        <label class="control-label" id="certNo_bno">
                            사업자번호
                            <input type="text" name="cashCertNo[b]" class="form-control js-number-only" />
                        </label>
                    </td>
                </tr>
                </tbody>
                <tbody class="js-receipt display-none" id="tax_info">
                <tr>
                    <th>사업자번호</th>
                    <td colspan="3">
                        <input type="text" name="taxBusiNo" class="form-control js-number-only"/>
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
                <tr>
                    <th>발행 이메일</th>
                    <td colspan="3">
                        <div class="form-inline">
                            <input type="text" name="taxEmail" value="" placeholder="미입력 시 주문자의 이메일로 발행" class="form-control width-xl" />

                            <select id="taxEmailDomain" class="form-control" style="width: 120px;">
                                <?php foreach($emailDomain as $key => $value){ ?>
                                    <option value="<?=$key?>"><?=$value?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-xs-6">
            <div class="table-title gd-help-manual">관리자 메모</div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tbody>
                <tr class="self-order-memo-area">
                    <th>메모 구분</th>
                    <td colspan="3">
                        <?= gd_select_box('orderMemoCd', 'orderMemoCd', $memoCd, null, null, '=메모 구분='); ?>
                    </td>
                </tr>
                <tr class="self-order-memo-area">
                    <th>메모 내용</th>
                    <td colspan="3">
                        <textarea name="adminOrderGoodsMemo" rows="5" class="form-control" style="height: 131px;"><?= gd_isset($data['adminMemo']); ?></textarea>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</form>

<div class="display-none" id="selfOrderCartPriceData" data-totalGoodsDcPrice="0" data-totalSumMemberDcPrice="0" data-totalCouponGoodsDcPrice="0" data-totalGoodsMileage="0" data-totalMemberMileage="0" data-totalCouponGoodsMileage="0" data-totalDeliveryCharge="0" data-totalDeliveryAreaCharge="0" data-totalGoodsDeliveryPolicyCharge="0" data-totalGoodsPrice="0" data-totalMileage="0" data-deliveryFree="0"></div>

<script type="text/javascript">
    <!--
    var currencySymbol = '<?=$currencySymbol?>';
    var currencyString = '<?=$currencyString?>';
    var int_division = '<?=INT_DIVISION?>';
    var mileageInfo = {
        'name': '<?=$mileage['name']?>',
        'unit': '<?=$mileage['unit']?>',
        'payUsableFl': '<?=$mileage['payUsableFl']?>'
    };
    var depositUse = {
        'name' : '<?=$depositUse['name']?>',
        'unit' : '<?=$depositUse['unit']?>',
        'payUsableFl' : '<?=$depositUse['payUsableFl']?>'
    };
    var settleKindBankUseFl = '<?=$settleKindBankUseFl?>';
    //-->
</script>
<script type="text/html" id="goodsTemplate">
    <tr class="self-order-goods-layout order-add-goods-<%=dataIndex%>" data-index="<%=dataIndex%>" data-goodsNo="<%=dataGoodsNo%>" data-deliverysno="<%=dataGoodsDeliverySno%>" data-goodsDeliveryFl="<%=dataGoodsDeliveryFl%>" data-sameGoodsDeliveryFl="<%=dataSameGoodsDeliveryFl%>">
        <td class="center" rowspan="<%=dataRowCount%>">
            <input type="checkbox" name="cartSno[]" data-sno="<%=cartSno%>" value="<%=cartSno%>">
            <%=dataDeliveryInfo%>
        </td>
        <td class="center" rowspan="<%=dataRowCount%>"><%=dataScmNm%></td>
        <td><%=dataGoodsImage%></td>
        <td>
            <%=dataOrderPossibleMessageList%>
            <%=dataCouponButton%>
            <%=dataGoodsNm%>
            <%=dataOptionChangeButton%>
        </td>
        <td class="center">
            <%=dataGoodsCount%>
            <input type="button" value="수정" class="btn btn-sm btn-white js-goods-cnt-change" data-sno="<%=cartSno%>" data-goodsNo="<%=dataGoodsNo%>" data-coupon='<%=dataCouponUse%>' />
        </td>
        <td class="center"><%=dataGoodsPrice%></td>
        <td rowspan="<%=dataRowCount%>">
            <%=dataTotalDcContent%>
            <%=dataTotalSaveContent%>
        </td>>
        <td class="center"  rowspan="<%=dataRowCount%>"><%=dataSettlePrice%></td>
        <td class="center self-order-write-delivery-area" rowspan="<%=dataRowCount%>"><%=dataDelivery%></td>
    </tr>
</script>
<script type="text/html" id="addGoodsTemplate">
    <tr class="order-add-goods-<%=dataIndex%>" data-index="<%=dataIndex%>">
        <td><%=dataAddGoodsImage%></td>
        <td><span class="label label-default" title="6574">추가</span> <%=dataAddGoodsInfo%></td>
        <td class="center">
            <%=dataAddGoodsCount%>
            <input type="button" value="수정" class="btn btn-sm btn-white js-goods-cnt-change" data-sno="<%=cartSno%>" data-goodsNo="<%=dataGoodsNo%>" data-addGoodsNo="<%=dataAddGoodsNo%>" data-coupon='<%=dataCouponUse%>'/>
        </td>
        <td class="center"><%=dataAddGoodsPrice%></td>
    </tr>
</script>

<script type="text/html" id="giftTemplate">
    <tr>
        <th class="gift-condition">
            <div><%=dataGiftTitle%></div>
            <div>(<strong><%=dataGiftSelectCnt%></strong>/<%=dataGiftTotal%>)</div>
        </th>
        <td class="gift-choice">
            <ul><%=dataGiftContents%></ul>
        </td>
    </tr>
</script>
<script type="text/html" id="giftContentsTemplate">
    <li>
        <input type="hidden" name="gift[<%=dataGiftArrKey%>][<%=dataGiftArrIndex%>][goodsNo]" value="<%=dataGiftGoodsNo%>" />
        <input type="hidden" name="gift[<%=dataGiftArrKey%>][<%=dataGiftArrIndex%>][scmNo]" value="<%=dataGiftScmNo%>" />
        <input type="hidden" name="gift[<%=dataGiftArrKey%>][<%=dataGiftArrIndex%>][selectCnt]" value="<%=dataGiftSelectCnt%>" class="gift-select-cnt" />
        <input type="hidden" name="gift[<%=dataGiftArrKey%>][<%=dataGiftArrIndex%>][giveCnt]" value="<%=dataGiftGiveCnt%>" />
        <input type="hidden" name="gift[<%=dataGiftArrKey%>[<%=dataGiftArrIndex%>][minusStockFl]" value="<%=dataGiftStockFl%>" />
        <div><%=dataGiftImageUrl%></div>
        <div class="ta-c mgt5"><input type="checkbox" name="gift[<%=dataGiftArrKey%>][<%=dataGiftArrIndex%>][giftNo]" value="<%=dataGiftGiftNo%>" class="checkbox" <%=dataGiftCheckboxReadonly%>/></div>
        <div class="ta-c mgt5"><span class="txt"><%=dataGiftGiftNm%></span></div>
        <div class="ta-c mgt5">(<small><%=dataGiftGiveCnt%>개)</small></div>
    </li>
</script>
<script type="text/html" id="addFieldTemplate">
<tr>
        <th class="display-none"></th>
        <th class="ta-l" style="height:26px;"><%=dataAddFieldName%></th>
</tr>
<tr>
        <td class="display-none"></td>
        <td><%=dataAddFieldHtml%></td>
</tr>
</script>

<script type="text/html" id="receiverInfoTemplate">
    <div class="js-receiver-parent-info-area col-xs-6" data-receiver-info-index="<%=dataReceiverInfoIndex%>">
        <div class="table-title gd-help-manual">
            수령자 정보 <%=dataReceiverInfoSubject%>

            <button type='button' class='btn btn-sm btn-black flo-right js-receiver-info-add-btn <%=dataReceiverInfoAddBtnDisplay%>'> + 추가 </button>
            <button type='button' class='btn btn-sm btn-white flo-right js-receiver-info-delete-btn <%=dataReceiverInfoDeleterBtnDisplay%>'> - 삭제 </button>
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>배송지확인</th>
                <td>
                    <label class="checkbox-inline">
                        <span class="js-order-same-area">
                            <input type="checkbox" name="tmp[checkOrderSame]" value="y" class="js-order-same" /> 주문자 정보와 동일
                        </span>

                        <button type="button" class="btn btn-gray btn-sm display-none js-self-order-write-delivery-list">배송지 목록</button>
                    </label>
                </td>
            </tr>
            <tr>
                <th class="require">수령자명</th>
                <td>
                    <input type="text" name="receiverInfo[<%=dataReceiverInfoIndex%>][receiverName]" value="<?= gd_isset($data['receiverName']); ?>" class="form-control width-md js-receiver-name"/>
                </td>
            </tr>
            <tr>
                <th>전화번호</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="receiverInfo[<%=dataReceiverInfoIndex%>][receiverPhone]" value="" maxlength="14" class="form-control js-number-only width-md js-receiver-phone"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="require">휴대폰번호</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="receiverInfo[<%=dataReceiverInfoIndex%>][receiverCellPhone]" value="" maxlength="14" class="form-control js-number-only width-md js-receiver-cell-phone"/>
                        <?php if ($useSafeNumberFl == 'y') { ?>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="receiverInfo[<%=dataReceiverInfoIndex%>][receiverUseSafeNumberFl]" value="w" checked="checked" /> 안심번호 사용
                        </label>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="require">주소</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="receiverInfo[<%=dataReceiverInfoIndex%>][receiverZonecode]" value="" size="5" class="form-control js-receiver-zonecode" readonly="readonly" />
                        <input type="hidden" name="receiverInfo[<%=dataReceiverInfoIndex%>][receiverZipcode]" value="" class="js-receiver-zipcode" />
                        <input type="hidden" name="receiverInfo[<%=dataReceiverInfoIndex%>][deliveryVisit]" class="shipping-delivery-visit" value="n" />
                        <span class="number display-none js-receiver-zipcode-text" id="receiverInfo<%=dataReceiverInfoIndex%>receiverZipcodeText">()</span>
                        <input type="button" value="우편번호찾기" class="btn btn-sm btn-gray js-post-search-btn"/>
                    </div>
                    <div class="mgt5">
                        <input type="text" name="receiverInfo[<%=dataReceiverInfoIndex%>][receiverAddress]" value="" class="form-control js-receiver-address" readonly="readonly" />
                    </div>
                    <div class="mgt5">
                        <input type="text" name="receiverInfo[<%=dataReceiverInfoIndex%>][receiverAddressSub]" value="" class="form-control js-receiver-address-sub"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>배송 메세지</th>
                <td>
                    <textarea name="receiverInfo[<%=dataReceiverInfoIndex%>][orderMemo]" rows="3" class="form-control js-receiver-order-memo"><?= gd_isset($data['orderMemo']); ?></textarea>
                </td>
            </tr>
            <tr class="delivery-visit-tr display-none">
                <th>방문수령 정보</th>
                <td>
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-md"/>
                            <col/>
                        </colgroup>
                        <tr>
                            <th>방문 수령지 주소</th>
                            <td>
                                <span class="delivery-method-visit-area-txt"></span>
                                <input type="hidden" name="receiverInfo[<%=dataReceiverInfoIndex%>][visitAddress]" value="" class="form-control width-sm">
                            </td>
                        </tr>
                        <tr>
                            <th>방문자정보</th>
                            <td>
                                <div class="form-inline">
                                    방문자명 <input type="text" name="receiverInfo[<%=dataReceiverInfoIndex%>][visitName]" value="" class="form-control width-sm"> 방문자연락처 <input type="text" name="receiverInfo[<%=dataReceiverInfoIndex%>][visitPhone]" value="" class="form-control width-sm">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>메모</th>
                            <td><input type="text" name="receiverInfo[<%=dataReceiverInfoIndex%>][visitMemo]" class="form-control" maxlength="250"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="select-goods-tr display-none">
                <th>배송 상품</th>
                <td>
                    <button type='button' class='btn btn-sm btn-black js-receiver-goods-multi-shipping-btn <%=dataReceiverGoodsMultiShippingDisplay%>'> 상품 선택 </button>
                    <input type="hidden" name="selectGoods[<%=dataReceiverInfoIndex%>]" value="" />
                    <input type="hidden" name="multiDelivery[<%=dataReceiverInfoIndex%>]" value="0" />
                    <input type="hidden" name="multiAreaDelivery[<%=dataReceiverInfoIndex%>]" value="0" />
                    <input type="hidden" name="multiPolicyDelivery[<%=dataReceiverInfoIndex%>]" value="0" />
                    <div class="select-goods-area table1 display-none">
                        <table class="table table-cols">
                            <colgroup>
                                <col class="ta-l width60p" />
                                <col class="ta-c width10p" />
                                <col class="ta-c width25p" />
                                <col class="ta-c width5p" />
                            </colgroup>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</script>
