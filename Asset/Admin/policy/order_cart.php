<form id="frmCart" name="frmCart" action="order_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="updateOrderCart"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small>장바구니에 대한 기본 설정 및 제한 설정 등을 하실 수 있습니다.</small>
        </h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="table-title gd-help-manual">
        장바구니 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tr>
            <th>상품보관기간</th>
            <td>
                <div class="form-inline radio">
                    <label class="radio-inline" title="로그아웃시 장바구니를 비우게 하시려면 &quot;사용안함&quot;을 체크를 하시면 됩니다.!">
                        <input type="radio" name="periodFl" value="y" <?php echo gd_isset($checked['periodFl']['y']); ?> />
                        <input type="text" name="periodDay" value="<?php echo $data['periodDay']; ?>" class="form-control input-sm width-2xs js-number"/> 일동안 보관
                    </label>

                    <label class="radio-inline" title="다음 로그인시에도 장바구니를 계속 사용하시려면 &quot;사용함&quot;을 체크후 기간을 입력하세요.">
                        <input type="radio" name="periodFl" value="n" <?php echo gd_isset($checked['periodFl']['n']); ?> /> 고객이 삭제할 때까지 보관
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>보관상품 개수제한</th>
            <td>
                <div class="form-inline radio">
                    <label class="radio-inline" title="장바구니에 상품 갯수 제한을 사용하지 않으려면 &quot;사용안함&quot;을 체크를 하시면 됩니다.!">
                        <input type="radio" name="goodsLimitFl" value="y" <?php echo gd_isset($checked['goodsLimitFl']['y']); ?> />
                        <input type="text" name="goodsLimitCnt" value="<?php echo $data['goodsLimitCnt']; ?>" class="form-control input-sm width-2xs js-number"/> 개까지 보관
                    </label>

                    <label class="radio-inline" title="장바구니에 담을 수 있는 상품 갯수를 설정하시려면 &quot;사용함&quot;을 체크후 갯수를 입력하세요.!">
                        <input type="radio" name="goodsLimitFl" value="n" <?php echo gd_isset($checked['goodsLimitFl']['n']); ?> /> 보관상품 개수제한 없음
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>보관중인 상품과<br />같은 상품을 담을 경우</th>
            <td>
                <div class="form-inline radio">
                    <label class="radio-inline" title="이미 장바구니에 담겨 있는 동일 상품에 대해서 다시한번 장바구니에 담을때 설정한 수량만큼 더 담기게 하시려면 체크를 하세요.!">
                        <input type="radio" name="sameGoodsFl" value="p" <?php echo gd_isset($checked['sameGoodsFl']['p']); ?> /> 상품 수량 증가
                    </label>

                    <label class="radio-inline" title="이미 장바구니에 담겨 있는 동일 상품에 대해서는 또다시 장바구니에 담을때 수량을 늘리지 않게 하시려면 체크를 하세요.!">
                        <input type="radio" name="sameGoodsFl" value="n" <?php echo gd_isset($checked['sameGoodsFl']['n']); ?> /> 상품 수량 변화 없음
                    </label>
                </div>
                <div class="notice-info">
                    텍스트 옵션이 선택된 경우에는 수량이 증가되지 않고 분리되어 담깁니다.
                </div>
            </td>
        </tr>
        <tr>
            <th>가격이 0원인 상품을<br />담을 경우</th>
            <td>
                <div class="form-inline radio">
                    <label class="radio-inline" title="">
                        <input type="radio" name="zeroPriceOrderFl" value="y" <?php echo gd_isset($checked['zeroPriceOrderFl']['y']); ?> /> 장바구니에 담음
                    </label>

                    <label class="radio-inline" title="">
                        <input type="radio" name="zeroPriceOrderFl" value="n" <?php echo gd_isset($checked['zeroPriceOrderFl']['n']); ?> /> 장바구니에 담지 않음
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>품절상품 보관설정</th>
            <td>
                <div class="form-inline radio">
                    <label class="radio-inline" title="">
                        <input type="radio" name="soldOutFl" value="y" <?php echo gd_isset($checked['soldOutFl']['y']); ?> /> 보관상품 품절 시에도 보관유지
                    </label>

                    <label class="radio-inline" title="">
                        <input type="radio" name="soldOutFl" value="n" <?php echo gd_isset($checked['soldOutFl']['n']); ?> /> 보관상품 품절 시 자동삭제
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>상품 장바구니 보관시<br />페이지 이동방법</th>
            <td>
                <div class="form-inline radio">
                    <label class="radio-inline" title="">
                        <input type="radio" name="moveCartPageFl" value="y" <?php echo gd_isset($checked['moveCartPageFl']['y']); ?> /> 장바구니 페이지로 바로 이동
                    </label>

                    <label class="radio-inline" title="">
                        <input type="radio" name="moveCartPageFl" value="n" <?php echo gd_isset($checked['moveCartPageFl']['n']); ?> /> 장바구니 페이지 이동여부 확인팝업 노출
                    </label>
                    <span>
                        <select name="moveCartPageDeviceFl" class="mgl10">
                            <option value="pc" <?php echo gd_isset($selected['moveCartPageDeviceFl']['pc']); ?>>PC</option>
                            <option value="mobile" <?php echo gd_isset($selected['moveCartPageDeviceFl']['mobile']); ?>>모바일</option>
                            <option value="all" <?php echo gd_isset($selected['moveCartPageDeviceFl']['all']); ?>>PC+모바일</option>
                        </select>
                    </span>
                </div>
            </td>
        </tr>
        <tr>
            <th>바로구매 설정</th>
            <td>
                <div class="form-inline radio">
                    <label class="radio-inline" title="바로구매 버튼으로 주문시에 해당 상품만 주문을 하게 하려면 &quot;해당 상품만 구매&quot;를 체크 하세요.!">
                        <input type="radio" name="directOrderFl" value="y" <?php echo gd_isset($checked['directOrderFl']['y']); ?> />해당 상품만 구매
                    </label>

                    <label class="radio-inline" title="바로구매 버튼으로 주문시에 기존에 장바구니에 담기 상품과 같이 주문을 하려면 &quot;기존 장바구니 상품과 같이 구매&quot;를 체크 하세요.!">
                        <input type="radio" name="directOrderFl" value="n" <?php echo gd_isset($checked['directOrderFl']['n']); ?> />기존 장바구니 상품과 같이 구매
                    </label>
                </div>
            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual">
        관심상품 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tr>
            <th>상품개수 제한</th>
            <td>
                <div class="form-inline radio">
                    <label class="radio-inline" title="로그아웃시 장바구니를 비우게 하시려면 &quot;사용안함&quot;을 체크를 하시면 됩니다.!">
                        <input type="radio" name="wishLimitFl" value="y" <?php echo gd_isset($checked['wishLimitFl']['y']); ?> />
                        <input type="text" name="wishDay" value="<?php echo $data['wishDay']; ?>" class="form-control input-sm width-2xs js-number"/> 개까지 보관
                    </label>

                    <label class="radio-inline" title="다음 로그인시에도 장바구니를 계속 사용하시려면 &quot;사용함&quot;을 체크후 기간을 입력하세요.">
                        <input type="radio" name="wishLimitFl" value="n" <?php echo gd_isset($checked['wishLimitFl']['n']); ?> /> 보관상품 개수제한 없음
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>장바구니로 상품이동시</th>
            <td>
                <div class="form-inline radio">
                    <label class="radio-inline" title="">
                        <input type="radio" name="moveWishPageFl" value="y" <?php echo gd_isset($checked['moveWishPageFl']['y']); ?> /> 상품 남김
                    </label>

                    <label class="radio-inline" title="">
                        <input type="radio" name="moveWishPageFl" value="n" <?php echo gd_isset($checked['moveWishPageFl']['n']); ?> /> 상품 삭제
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>품절상품 보관설정</th>
            <td>
                <div class="form-inline radio">
                    <label class="radio-inline" title="">
                        <input type="radio" name="wishSoldOutFl" value="y" <?php echo gd_isset($checked['wishSoldOutFl']['y']); ?> /> 보관상품 품절 시에도 보관유지
                    </label>
                    <label class="radio-inline" title="">
                        <input type="radio" name="wishSoldOutFl" value="n" <?php echo gd_isset($checked['wishSoldOutFl']['n']); ?> /> 보관상품 품절 시 자동삭제
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>상품 관심상품 보관시<br />페이지 이동방법</th>
            <td>
                <div class="form-inline radio">
                    <label class="radio-inline" title="">
                        <input type="radio" name="wishPageMoveDirectFl" value="y" <?php echo gd_isset($checked['wishPageMoveDirectFl']['y']); ?> /> 관심상품 페이지로 바로 이동
                    </label>

                    <label class="radio-inline" title="">
                        <input type="radio" name="wishPageMoveDirectFl" value="n" <?php echo gd_isset($checked['wishPageMoveDirectFl']['n']); ?> /> 관심상품 페이지 이동여부 확인팝업 노출
                    </label>
                    <span>
                        <select name="moveWishPageDeviceFl" class="mgl10">
                            <option value="pc" <?php echo gd_isset($selected['moveWishPageDeviceFl']['pc']); ?>>PC</option>
                            <option value="mobile" <?php echo gd_isset($selected['moveWishPageDeviceFl']['mobile']); ?>>모바일</option>
                            <option value="all" <?php echo gd_isset($selected['moveWishPageDeviceFl']['all']); ?>>PC+모바일</option>
                        </select>
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <?php if (gd_is_plus_shop(PLUSSHOP_CODE_CARTTAB) === true) {?>
    <div class="table-title">
        쇼핑카트탭 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tr>
            <th>쇼핑카트탭 사용 설정</th>
            <td>
                <div class="form-inline radio">
                    <label class="radio-inline" title="">
                        <input type="radio" name="cartTabUseFl" value="y" <?php echo gd_isset($checked['cartTabUseFl']['y']); ?> /> 사용함
                    </label>

                    <label class="radio-inline" title="">
                        <input type="radio" name="cartTabUseFl" value="n" <?php echo gd_isset($checked['cartTabUseFl']['n']); ?> /> 사용안함
                    </label>
                </div>
            </td>
        </tr>
    </table>
    <?php }?>

    <?php if (gd_is_plus_shop(PLUSSHOP_CODE_CARTESTIMATE) === true) {?>
        <div class="table-title">
            견적서 설정
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>견적서 사용 설정</th>
                <td>
                    <div class="form-inline radio">
                        <label class="radio-inline" title="">
                            <input type="radio" name="estimateUseFl" value="y" <?php echo gd_isset($checked['estimateUseFl']['y']); ?> /> 사용함
                        </label>

                        <label class="radio-inline" title="">
                            <input type="radio" name="estimateUseFl" value="n" <?php echo gd_isset($checked['estimateUseFl']['n']); ?> /> 사용안함
                        </label>
                    </div>
                </td>
            </tr>
            <tr id="estimatePriceSetting" class="<?php if ($data['estimateUseFl'] == 'n') echo 'display-none'?>">
                <th>판매가 적용 설정</th>
                <td>
                    <div class="form-inline radio">
                        <label class="checkbox-inline" title="">
                            <input type="checkbox" name="memberDiscount" value="y" <?php echo gd_isset($checked['memberDiscount']['y']); ?>/> 회원등급할인
                        </label>

                        <label class="checkbox-inline" title="">
                            <input type="checkbox" name="goodsDiscount" value="y" <?php echo gd_isset($checked['goodsDiscount']['y']); ?>/> 상품할인
                        </label>
                    </div>
                </td>
            </tr>
        </table>
    <?php } ?>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {

        <?php if (gd_is_plus_shop(PLUSSHOP_CODE_CARTESTIMATE) === true) {?>
        // 견적서 사용 설정
        $('input[name="estimateUseFl"]').on('change', function() {
            if (this.value == 'y') {
                $('#estimatePriceSetting').removeClass('display-none');
                $('#estimatePriceSetting').find(':checkbox').prop('checked', true);
            } else {
                $('#estimatePriceSetting').addClass('display-none');
            }
        });
        <?php } ?>
    });
    //-->
</script>
