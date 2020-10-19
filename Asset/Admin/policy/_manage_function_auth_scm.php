<div class="permission-item">
    <table class="table table-cols">
        <thead>
        <tr>
            <th>메뉴 구분</th>
            <th>항목명</th>
            <th><label class="checkbox-inline"><input type="checkbox" id="chkAllFunctionAuth" value="" class="js-checkall" data-target-name="functionAuth" />기능권한</label></th>
            <th>관련페이지</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td rowspan="9">상품</td>
            <td>상품삭제</td>
            <td class="center"><input type="checkbox" name="functionAuth[goodsDelete]" value="y" <?= gd_isset($checked['functionAuth']['goodsDelete']['y']); ?> /></td>
            <td rowspan="2">
                <a href="../goods/goods_list.php" target="_blank">상품 > 상품관리 > 상품리스트</a><br/>
                <a href="../goods/excel_goods_down.php" target="_blank">상품 > 상품 엑셀 관리 > 상품 다운로드</a>
            </td>
        </tr>
        <tr>
            <td>상품정보 엑셀 다운로드</td>
            <td class="center"><input type="checkbox" name="functionAuth[goodsExcelDown]" value="y" <?= gd_isset($checked['functionAuth']['goodsExcelDown']['y']); ?> /></td>
        </tr>
        <tr>
            <td>판매수수료</td>
            <td class="center"><input type="checkbox" name="functionAuth[goodsCommission]" value="y" <?= gd_isset($checked['functionAuth']['goodsCommission']['y']); ?> /></td>
            <td rowspan="4"><a href="../goods/goods_register.php" target="_blank">상품 > 상품관리 > 상품등록(수정)</a></td>
        </tr>
        <tr>
            <td>상품명</td>
            <td class="center">
                <label class="checkbox-inline"><input type="checkbox" name="functionAuth[goodsNm]" value="y" <?= gd_isset($checked['functionAuth']['goodsNm']['y']); ?> />수정권한</label>
            </td>
        </tr>
        <tr>
            <td>판매기간</td>
            <td class="center"><input type="checkbox" name="functionAuth[goodsSalesDate]" value="y" <?= gd_isset($checked['functionAuth']['goodsSalesDate']['y']); ?> /></td>
        </tr>
        <tr>
            <td>판매가</td>
            <td class="center">
                <label class="checkbox-inline"><input type="checkbox" name="functionAuth[goodsPrice]" value="y" <?= gd_isset($checked['functionAuth']['goodsPrice']['y']); ?> />수정권한</label>
            </td>
        </tr>
        <tr>
            <td>추가상품 판매수수료</td>
            <td class="center"><input type="checkbox" name="functionAuth[addGoodsCommission]" value="y" <?= gd_isset($checked['functionAuth']['addGoodsCommission']['y']); ?> /></td>
            <td rowspan="2"><a href="../goods/add_goods_register.php" target="_blank">상품 > 추가상품 관리 > 추가상품 등록(수정)</a></td>
        </tr>
        <tr>
            <td>추가상품명</td>
            <td class="center">
                <label class="checkbox-inline"><input type="checkbox" name="functionAuth[addGoodsNm]" value="y" <?= gd_isset($checked['functionAuth']['addGoodsNm']['y']); ?> />수정권한</label>
            </td>
        </tr>
        <tr>
            <td>상품 재고</td>
            <td class="center">
                <label class="checkbox-inline"><input type="checkbox" name="functionAuth[goodsStockModify]" value="y" <?= gd_isset($checked['functionAuth']['goodsStockModify']['y']); ?> />수정권한</label></td>
            <td><a href="../goods/goods_list.php" target="_blank">상품 > 상품 관리 > 상품수정</a></td>
        </tr>
        <tr>
            <td rowspan="2">주문/배송</td>
            <td>주문상태 변경</td>
            <td class="center"><input type="checkbox" name="functionAuth[orderState]" value="y" <?= gd_isset($checked['functionAuth']['orderState']['y']); ?> /></td>
            <td rowspan="2">
                <a href="../order/order_list_all.php" target="_blank">주문/배송 > 주문관리 > 주문통합리스트</a><br/>
                <a href="../order/order_list_order.php" target="_blank">주문/배송 > 주문관리 > 입금대기리스트</a><br/>
                <a href="../order/order_list_pay.php" target="_blank">주문/배송 > 주문관리 > 결제완료리스트</a><br/>
                <a href="../order/order_list_goods.php" target="_blank">주문/배송 > 주문관리 > 상품준비중리스트</a><br/>
                <a href="../order/order_list_delivery.php" target="_blank">주문/배송 > 주문관리 > 배송중리스트</a><br/>
                <a href="../order/order_list_delivery_ok.php" target="_blank">주문/배송 > 주문관리 > 배송완료리스트</a><br/>
                <a href="../order/order_list_settle.php" target="_blank">주문/배송 > 주문관리 > 구매확정리스트</a><br/>
                <a href="../order/order_list_fail.php" target="_blank">주문/배송 > 주문관리 > 결제중단/실패리스트</a><br/>
                <a href="../order/order_list_cancel.php" target="_blank">주문/배송 > 취소/교환/반품/환불 관리 > 취소 리스트</a><br/>
                <a href="../order/order_list_exchange.php" target="_blank">주문/배송 > 취소/교환/반품/환불 관리 > 교환 리스트</a><br/>
                <a href="../order/order_list_back.php" target="_blank">주문/배송 > 취소/교환/반품/환불 관리 > 반품 리스트</a><br/>
                <a href="../order/order_list_refund.php" target="_blank">주문/배송 > 취소/교환/반품/환불 관리 > 환불 리스트</a><br/>
                <a href="../order/order_view.php" target="_blank">주문/배송 > 주문상세정보</a>
            </td>
        </tr>
        <tr>
            <td>주문정보 엑셀 다운로드</td>
            <td class="center"><input type="checkbox" name="functionAuth[orderExcelDown]" value="y" <?= gd_isset($checked['functionAuth']['orderExcelDown']['y']); ?> /></td>
        </tr>
        <tr>
            <td>게시판</td>
            <td>게시글 삭제</td>
            <td class="center"><input type="checkbox" name="functionAuth[boardDelete]" value="y" <?= gd_isset($checked['functionAuth']['boardDelete']['y']); ?> /></td>
            <td><a href="../board/article_list.php" target="_blank">게시판 > 게시판관리 > 게시글관리</a></td>
        </tr>
        </tbody>
    </table>
</div>
