<style>
    p{margin:0px !important;}
</style>
<div class="table-title gd-help-manual">게시글 보기</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tr>
        <th>게시판</th>
        <td colspan="3">
            <?= $bdView['cfg']['bdNm'] ?>
        </td>
    </tr>
    <tr>
        <th>제목</th>
        <td>
            <?php if ($bdView['data']['isNotice'] == 'y')
                echo gd_isset($bdView['cfg']['bdIconNotice']);
            ?>
            <?php if ($bdView['data']['isSecret'] == 'y')
                echo gd_isset($bdView['cfg']['bdIconSecret']);
            ?>
            <?php if ($bdView['data']['isNew'] == 'y')
                echo gd_isset($bdView['cfg']['bdIconNew']); ?>
            <?php if ($bdView['data']['isHot'] == 'y')
                echo gd_isset($bdView['cfg']['bdIconHot']); ?>
            <?php if ($bdView['data']['isFile'] == 'y')
                echo gd_isset($bdView['cfg']['bdIconFile']); ?>

            <?= $bdView['data']['subject'] ?></td>
        <th>등록시간</th>
        <td><?= $bdView['data']['regDt']; ?></td>
    </tr>

    <?php if ($bdView['cfg']['bdEventFl'] == 'y') { ?>
        <tr>
            <th>부가설명</th>
            <td colspan="3"><?= $bdView['data']['subSubject'] ?></td>
        </tr>
        <tr>
            <th>이벤트 기간</th>
            <td colspan="3"><?= $bdView['data']['eventStart'] ?> ~ <?= $bdView['data']['eventEnd'] ?></td>
        </tr>
    <?php } ?>
    <?php if ($bdView['cfg']['bdCategoryFl'] == 'y') { ?>
        <tr>
            <th>말머리</th>
            <td colspan="3"><?= gd_isset($bdView['data']['category'], '-') ?></td>
        </tr>
    <?php } ?>

    <tr>
        <th>작성자</th>
        <td class="width50p">
            <?php if ($bdView['data']['memNo'] > 0 && !gd_is_provider()) {
                echo "<a   class='js-layer-crm hand' data-member-no='" . $bdView['data']['memNo'] . "' >";
                $aTagClose = '</a>';
            } ?>
                <?= $bdView['data']['writer'] .$aTagClose ?>
        </td>
        <th class="width-md">아이피</th>
        <td colspan="3"><?= $bdView['data']['writerIp'] ?></td>
    </tr>

    <?php
    if ($bdView['cfg']['bdMobileFl'] == 'y') {
        ?>
        <tr>
            <th>휴대폰</th>
            <td colspan="3"><?= gd_isset($bdView['data']['writerMobile']) ?>
        </tr>
        <?php
    }
    if ($bdView['cfg']['bdEmailFl'] == 'y') {
        ?>
        <tr>
            <th>이메일</th>
            <td colspan="3"><?= gd_isset($bdView['data']['writerEmail']) ?></td>
        </tr>
        <?php
    }
    ?>
    <?php if ($bdView['cfg']['goodsType'] == 'goods' && $bdView['data']['goodsNo']) { ?>
        <tr>
            <th>상품정보</th>
            <td colspan="3">
                <table id="selectGoodsTbl" class="table table-cols mgt15">
                    <colgroup>
                        <col class="width10p"/>
                        <col class="width30p"/>
                        <col class="width60p"/>
                    </colgroup>
                    <tbody>
                        <tr>
                            <td>
                                <a href="<?=URI_HOME?>goods/goods_view.php?goodsNo=<?=$bdView['data']['goodsNo']?>" target="_blank">
                                    <img src="<?= $bdView['data']['goodsData']['goodsImageSrc']; ?>" width="100" height="100"></a></td>
                            <td>
                                <div onclick="goods_register_popup('<?=$bdView['data']['goodsNo']; ?>' <?php if(gd_is_provider()) { echo ",'1'"; } ?>);"  class="hand">
                                <b><?=$bdView['data']['goodsData']['goodsNm']?></b></div></td>
                            <td align="left"><?=gd_currency_display($bdView['data']['goodsData']['goodsPrice'])?></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    <?php } ?>

    <?php if($bdView['cfg']['goodsType'] == 'order' && $bdView['data']['extraData']['arrOrderGoodsData']) {?>
        <tr>
            <th>주문 정보</th>
            <td colspan="3">
                <table  class="table table-cols mgt15">
                    <colgroup>
                        <col class="width10p"/>
                        <col class="width80p"/>
                        <col class="width10p"/>
                    </colgroup>
                    <tbody>
                    <?php
                        foreach($bdView['data']['extraData']['arrOrderGoodsData'] as $val) {
                            $addGoodsInfo = '';
                           if($val['goodsType'] == 'addGoods') {
                               $addGoodsIcon = '<span class="label label-default">추가</span>&nbsp;';
                           }
                            ?>
                            <tr>
                                <td>
                                    <a href="<?=URI_HOME?>goods/goods_view.php?goodsNo=<?=$val['goodsNo']?>" target="_blank">
                                        <img src="<?=$val['goodsImageSrc']?>" width="100" height="100">
                                    </a>
                                </td>
                                <td><a href="../order/order_view.php?orderNo=<?= $val['orderNo']; ?>" title="상품주문번호" target="_blank"><?=$val['orderNo']?></a> | <?=$val['orderGoodsRegDt']?><br>
                                    <?php if($val['goodsType'] == 'addGoods') {?>
                                    <a href="javascript:void(0)" onclick="addgoods_register_popup('<?=$bdView['data']['goodsNo']; ?>' <?php if(gd_is_provider()) { echo ",'1'"; } ?>);">
                                    <?php } else {?>
                                    <a href="javascript:void(0)" onclick="goods_register_popup('<?=$bdView['data']['goodsNo']; ?>' <?php if(gd_is_provider()) { echo ",'1'"; } ?>);">
                                    <?php }?>
                                        <b><?=$addGoodsIcon.$val['goodsNm']?></b>
                                    </a>
                                    <br>
                                    <?=$val['optionName']?>
                                </td>
                                <td><?=$val['orderStatusText']?><br><?=gd_currency_display($val['totalGoodsPrice'])?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </td>
        </tr>
    <?php }?>
    <?php
    if ($bdView['cfg']['bdGoodsPtFl'] == 'y') {?>
        <tr>
            <th>별점</th>
            <td>
                <span class="rating"><span style="width:<?=$bdView['data']['goodsPt']*20?>%;">별</span></span>
            </td>
        </tr>
    <?php }?>

    <?php if ($bdView['data']['uploadedFile']) { ?>
        <tr>
            <th>파일첨부</th>
            <td colspan="3">
                <ul style="padding:0px">
                    <?php foreach ($bdView['data']['uploadedFile'] as $val) { ?>
                        <li><a href="../board/download.php?bdId=<?= $bdView['cfg']['bdId'] ?>&sno=<?= $bdView['data']['sno'] ?>&fid=<?= $val['fid'] ?>"><?= $val['name'] ?></a></li>
                    <?php } ?>
                </ul>
            </td>
        </tr>
    <?php } ?>
    <?php
    if ($bdView['cfg']['bdLinkFl'] == 'y') {
        ?>
        <tr>
            <th>링크</th>
            <td colspan="3">
                <a href="<?= gd_isset($bdView['data']['urlLink']) ?>"><?= gd_isset($bdView['data']['urlLink']) ?></a>
            </td>
        </tr>
        <?php
    }
    ?>
    <tr>
        <th>내용</th>
        <td colspan="3" style="margin:0px"><?= $bdView['data']['workedContents'] ?></td>
    </tr>
    <?php if ($bdView['cfg']['bdMemoFl'] == 'y') { ?>
        <tr>
            <th>댓글</th>
            <td colspan="3">
                <table class="table table-rows table-fixed">
                    <colgroup>
                        <col class="width-sm">
                        <col>
                        <col class="width-sm">
                        <col class="width-sm">
                    </colgroup>
                    <?php if ($bdView['cfg']['bdMemoFl'] == 'y' && $bdView['data']['memoList']) { ?>
                        <?php foreach ($bdView['data']['memoList'] as $val) {
                            ?>
                            <tr>
                                <td style="vertical-align: top">
                                    <?= $val['writer'] ?>
                                </td>
                                <td>
                                    <form name="frmMemo<?= $val['sno'] ?>" action="memo_ps.php" method="post" target="ifrmProcess">
                                        <input type="hidden" name="mode">
                                        <input type="hidden" name="bdId" value="<?= $bdView['cfg']['bdId'] ?>">
                                        <input type="hidden" name="bdSno" value="<?= $req['sno'] ?>">
                                        <input type="hidden" name="sno" value="<?= $val['sno'] ?>">
                                        <input type="hidden" name="memo" value="">
                                        <div class="js-text-memo">
                                            <?php
                                            if ($val['isSecretReply'] == 'y') {
                                                echo gd_isset($bdView['cfg']['bdIconSecret']);
                                            } ?>

                                            <?php if ($val['gapReply']) {
                                                echo $val['gapReply'] . $bdView['cfg']['bdIconReply'];
                                            } ?>
                                            <?= $val['workedMemo'] ?></div>
                                        <div class="display-none js-textarea-modify-memo form-inline">
                                            <div>
                                                <?php echo $hiddenCheckboxInModify?>
                                                <input type="checkbox" name="isSecretReplyInModify" value="y" <?php echo $checkSecretReplyView?>><span>비밀댓글</span>
                                            </div>
                                            <textarea class="form-control pull-left" name="modifyMemo" required><?= gd_htmlspecialchars_stripslashes($val['memo']) ?></textarea>
                                            <button class="btn btn-white pull-left js-btn-modify">저장</button>
                                            <div class="clear-both"></div>
                                        </div>
                                        <div class="display-none js-textarea-reply-memo">
                                            <div>
                                                <?php echo $hiddenCheckboxInReply?>
                                                <input type="checkbox" name="isSecretReplyInReply" value="y" <?php echo $checkSecretReplyView?>><span>비밀댓글</span>
                                            </div>
                                            <textarea class="form-control pull-left" name="replyMemo" required></textarea>
                                            <button class="btn btn-white pull-left js-btn-reply">저장</button>
                                        </div>
                                    </form>
                                </td>
                                <td style="vertical-align:top">
                                    <?= $val['regDt'] ?>
                                </td>
                                <td style="vertical-align:top">
                                    <button class="btn btn-white btn-sm js-btn-memo-modify">수정</button>
                                    <?php if (!$val['groupThread']) { ?>
                                        <button class="btn btn-white btn-sm js-btn-memo-reply">답글</button>
                                    <?php } ?>
                                    <button class="btn btn-white btn-sm js-btn-memo-delete" title="확인" data-message="정말로 삭제하시겠습니까?">삭제</button>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr>
                        <td>
                            <?= $writer['writer'] ?>
                        </td>
                        <td colspan="3">
                            <form name="frmMemoWrite" action="memo_ps.php" method="post" target="ifrmProcess">
                                <input type="hidden" name="bdId" value="<?= $bdView['cfg']['bdId'] ?>">
                                <input type="hidden" name="bdSno" value="<?= $req['sno'] ?>">
                                <input type="hidden" name="mode" value="write">
                                <div>
                                    <?php echo $hiddenCheckboxInWrite?>
                                    <input type="checkbox" name="isSecretReplyInWrite" value="y" <?php echo $checkSecretReplyView?>><span>비밀댓글</span>
                                </div>
                                <textarea class="form-control pull-left" name="memo" required></textarea>
                                <button type="submit" class="btn btn-white js-btn-memo-save pull-left">저장</button>
                            </form>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    <?php } ?>
</table>
