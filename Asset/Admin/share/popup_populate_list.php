<div class="popup-display-main-list">
    <div class="popup-page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
    </div>

    <form id="frmSearchBase" name="frmSearchBase" method="get" class="js-popup-submit">
        <input type="hidden" name="detailSearch" value="<?=$search['detailSearch']; ?>"/>
        <input type="hidden" name="popupMode" value="<?=$popupMode;?>">
        <input type="hidden" name="orderBy" value="<?=$orderBy?>">
        <div  class="search-detail-box">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-sm"/>
                    <col/>
                    <col class="width-sm"/>
                    <col/>
                </colgroup>
                <tbody>
                <tr>
                    <th>인기상품 노출명</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="keyword" value="<?=$search['keyword']; ?>" class="form-control"/>
                        </div>
                    </td>
                    <td rowspan="2"><input type="submit" value="검색" class="btn btn-lg btn-black"></td>
                </tr>
                <tr>
                    <th>순위타입</th>
                    <td><select name="searchTypeFl" class="form-control">
                            <option value=""> = 선택 =</option>
                            <option value="sell" <?=gd_isset($selected['searchTypeFl']['sell']); ?>>상품 판매순위 - 판매금액</option>
                            <option value="hit" <?=gd_isset($selected['searchTypeFl']['hit']); ?>>상품 클릭수 순위</option>
                            <option value="sellCnt" <?=gd_isset($selected['searchTypeFl']['sellCnt']); ?>>상품 판매순위 - 판매횟수</option>
                            <option value="view" <?=gd_isset($selected['searchTypeFl']['view']); ?>>상품 조회수 순위</option>
                            <option value="cart" <?=gd_isset($selected['searchTypeFl']['cart']); ?>>장바구니 담기 순위</option>
                            <option value="wishlist" <?=gd_isset($selected['searchTypeFl']['wishlist']); ?>>찜리스트 담기 순위</option>
                            <option value="review" <?=gd_isset($selected['searchTypeFl']['review']); ?>>상품 후기 작성 순위</option>
                            <option value="score" <?=gd_isset($selected['searchTypeFl']['score']); ?>>상품 후기 평점 순위</option>
                        </select></td>
                </tr>
                </tbody>
            </table>
        </div>
        <!--<div class="table-btn">
            <input type="submit" value="검색" class="btn btn-lg btn-black">
        </div>-->
    </form>

    <form id="frmDisplayMainList" action="../goods/goods_ps.php" method="get" target="ifrmProcess">
        <input type="hidden" name="mode" value="populate_goods">
        <input type="hidden" name="goodsNo" value="">
        <table class="table table-rows">
            <thead>
            <tr>
                <th class="width5p"><input type="checkbox" class="js-checkall" data-target-name="sno"/></th>
                <th class="width10p">순위타입</th>
                <th class="width10p">인기상품 노출명</th>
                <th class="width15p">노출상태</th>
                <th class="width10p">PC페이지</th>
                <th class="width10p">모바일페이지</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data)) {
                $arrPopulateType = array('sell'=>'상품 판매순위(판매금액)', 'hit'=>'상품 클릭수 순위', 'sellCnt'=>'상품 판매순위(판매횟수)', 'view'=>'상품 조회수 순위', 'cart'=>'장바구니 담기 순위', 'wishlist'=>'찜리스트 담기 순위', 'review'=>'상품 후기 작성 순위', 'score'=>'상품 후기 평점 순위');
                $arrPopulateDisplay = array('' => '노출안함', 'y' => '노출함', 'n' => '노출안함');
                $arrPopulateUse = array('' => '사용안함', 'y' => '사용', 'n' => '사용안함');

                foreach ($data as $key => $val) {
            ?>
                <tr>
                  <td class="center"><input type="checkbox" name="sno[<?=$val['sno']; ?>]" value="<?=$val['sno']; ?>" data-goodsNo="<?=$val['goodsNo']?>"/></td>
                  <td class="center"><?=$arrPopulateType[$val['type']]?></td>
                  <td><?=$val['populateName']?></td>
                  <td><?=$arrPopulateDisplay[$val['displayFl']]?></td>
                  <td class="center"><?=$arrPopulateUse[$val['useFl']]?></td>
                  <td class="center"><?=$arrPopulateUse[$val['mobileUseFl']]?></td>
                </tr>
            <?php
                }
            } else {
            ?>
                <tr>
                    <td class="center" colspan="11">검색된 정보가 없습니다.</td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>

        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>포함여부</th>
                <td>
                    <div class="form-inline">선택된 인기상품 노출 순위에 일괄로
                    <select name="batchChange" class="form-control">
                        <option value="y">포함</option>
                        <option value="n">미포함</option>
                    </select>
                        합니다.</div>
                </td>
            </tr>
            </tbody>
        </table>

        <div class="text-center">
            <input type="button" class="btn btn-white js-check-close" value="닫기">
            <input type="button" class="btn btn-black js-check-save" value="적용">
        </div>
    </form>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $('.sort-btn').click(function(){
            var sort = $(this).data('sort');
            $('#frmSearchBase input[name="orderBy"]').val(sort);
            $('#frmSearchBase').submit();
        });

        $('.up-off').click(function () {
            var sort = $(this).data('sort');
            if (sort == 'dt.mobileFl asc'){
                $('.mobile-up').attr('class', 'up-on');
            }else if(sort == 'dt.themeNm asc'){
                $('.theme-up').attr('class', 'up-on');
            }else if(sort == 'dt.displayFl asc'){
                $('.display-up').attr('class', 'up-on');
            }
        });

        $('.down-off').click(function () {
            var sort = $(this).data('sort');
            if (sort == 'dt.mobileFl desc'){
                $('.mobile-down').attr('class', 'down-on');
            }else if(sort == 'dt.themeNm desc'){
                $('.theme-down').attr('class', 'down-on');
            }else if(sort == 'dt.displayFl desc'){
                $('.display-down').attr('class', 'down-on');
            }
        });

        $('.up-on').click(function () {
            var sort = $(this).data('sort');
            if (sort == 'dt.mobileFl asc'){
                $('.mobile-up').attr('class', 'up-on');
            }
        });

        $('.js-check-save').click(function () {
            frmSubmit();
        });

        $('.js-check-close').click(function () {
            close();
        });

        function frmSubmit(){

            // 부모창 상품선택값
            var cntGoodsNo = $('input[name*="goodsNo"]:checked', opener.document).map(function(){
                return this.value;
            }).get().join(',');

            // 자식창 상품진열 선택값
            var sno = $('input[name*="sno["]:checkbox:checked').map(function(){
                return this.value;
            }).get().join(',');

            // 배열
            var arrChkSno = sno.split(',');

            var chkStatus = $('input[name="displayStatusChk"]:checked').val();
            var chkSnoCnt = $('input[name*="sno["]:checkbox:checked').length;

            if(chkSnoCnt == 0){
                alert('노출여부를 변경할 인기상품 노출 관리를 선택해 주세요.');
                return false;
            }

            $('input[name="goodsNo"]').attr('value',cntGoodsNo);
            $('input[name="mode"]').attr('value',chkStatus);
            $('input[name="sno"]').attr('value',sno);

            var params = jQuery("#frmDisplayMainList").serializeArray();

            $.ajax({
                method: "POST",
                url: "../goods/goods_ps.php",
                data : params,
                success : function(data){
                    window.close();
                    opener.parent.location.reload();
                },
                error: function (data){
                    console.log(data);
                }
            });
        }
    });
    //-->
</script>