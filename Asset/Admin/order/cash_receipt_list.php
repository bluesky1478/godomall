<div class="page-header js-affix">
	<h3><?php echo end($naviMenu->location); ?>
		<small>현금영수증 신청/대기/완료 처리된 주문 리스트 입니다.</small>
	</h3>
	<div class="btn-group">
		<a href="./cash_receipt_register.php" class="btn btn-red" role="button">현금영수증 개별발급</a>
	</div>
</div>
<form id="frmSearchManager" name="frmSearchManager" method="get" class="js-form-enter-submit">
	<input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch'];?>" />
	<input type="hidden" name="sort[name]" value="<?=$sort['fieldName']?>">
	<input type="hidden" name="sort[mode]" value="<?=$sort['sortMode']?>" />
    <input type="hidden" name="excelMode" value="" />

    <div class="table-title gd-help-manual">현금영수증 검색</div>

	<div class="search-detail-box">
		<table class="table table-cols">
			<colgroup>
				<col class="width-sm"/>
				<col/>
			</colgroup>
			<tbody>
			<tr>
				<th>검색어</th>
				<td colspan="3" >
					<div class="form-inline">
						<?php echo gd_select_box('key','key',$search['combineSearch'],null,$search['key'],null);?>
						<input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control width-md" />
					</div>
				</td>
			</tr>
			<tr>
				<th>기간검색</th>
				<td colspan="3">
					<div class="form-inline">
						<?php echo gd_select_box('treatDateFl', 'treatDateFl', ['firstRegDt' => '신청일', 'processDt' => '처리일'], '', $search['treatDateFl']); ?>
						<div class="input-group js-datepicker">
							<input type="text" name="treatDate[start]" value="<?php echo $search['treatDate']['start'];?>" class="form-control width-xs" placeholder="수기입력 가능" />
							<span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
						</div>
						~
						<div class="input-group js-datepicker">
							<input type="text" name="treatDate[end]" value="<?php echo $search['treatDate']['end'];?>" class="form-control width-xs" placeholder="수기입력 가능" />
							<span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
						</div>
                        <?= gd_search_date(gd_isset($search['periodFl'], 6), 'treatDate', false) ?>
					</div>
				</td>
			</tr>
			</tbody>
			<colgroup>
				<col class="width-sm"/>
				<col/>
				<col class="width-sm"/>
				<col class="width-xl"/>
			</colgroup>
			<tbody class="js-search-detail" class="display-none">
            <tr>
                <th>주문상태</th>
                <td colspan="3" class="form-inline">
                    <label class="radio-inline"><input type="radio" name="ordStatusFl" value="" <?php echo gd_isset($checked['ordStatusFl']['']); ?> />전체</label>
                    <?php echo gd_radio_box('ordStatusFl', $arrOrdStatus, $search['ordStatusFl']);?>
                </td>
            </tr>
            <tr>
                <th>발행상태</th>
                <td colspan="3" class="form-inline">
                    <label class="radio-inline"><input type="radio" name="statusFl" value="" <?php echo gd_isset($checked['statusFl']['']); ?> />전체</label>
                    <?php echo gd_radio_box('statusFl', $arrStatus, $search['statusFl']);?>
                </td>
            </tr>
			<tr>
				<th>발행용도</th>
				<td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="useFl" value="" <?php echo gd_isset($checked['useFl']['']); ?> />전체</label>
					<?php echo gd_radio_box('useFl', $arrUseFl, $search['useFl']);?>
				</td>
				<th>인증 종류</th>
				<td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="certFl" value="" <?php echo gd_isset($checked['certFl']['']); ?> />전체</label>
					<?php echo gd_radio_box('certFl', $arrCertFl, $search['certFl']);?>
				</td>
			</tr>
            <tr>
                <th>과세/면세</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="taxFl" value="" <?php echo gd_isset($checked['taxFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="taxFl" value="a" <?php echo gd_isset($checked['taxFl']['a']); ?> />과세</label>
                    <label class="radio-inline"><input type="radio" name="taxFl" value="n" <?php echo gd_isset($checked['taxFl']['n']); ?> />면세</label>
                    <label class="radio-inline"><input type="radio" name="taxFl" value="y" <?php echo gd_isset($checked['taxFl']['y']); ?> />복합과세</label>
                </td>
                <th>금액변경확인</th>
                <td class="form-inline">
                    <label class="checkbox-inline"><input type="checkbox" name="priceChangeChk" value="y" <?php echo gd_isset($checked['priceChangeChk']['y']); ?>>발급금액 변경건만 보기</label>
                </td>
            </tr>
			<tr>
                <th>상품가격</th>
                <td class="form-inline">
                    <input type="text" name="settlePrice[]" value="<?php echo $search['settlePrice'][0];?>" class="form-control width-md" /> ~
                    <input type="text" name="settlePrice[]" value="<?php echo $search['settlePrice'][1];?>" class="form-control width-md" />
                </td>
				<th>거래번호</th>
				<td class="form-inline"><input type="text" name="pgTid" value="<?php echo $search['pgTid'];?>" class="form-control width-md" /></td>
			</tr>
			</tbody>
		</table>
		<button type="button" class="btn btn-sm btn-link js-search-toggle bold">상세검색<span>펼침</span></button>
	</div>
	<div class="table-btn">
		<input type="submit" value="검색" class="btn btn-lg btn-black"/>
	</div>

    <div class="table-header">
        <div class="pull-left">
            <?= gd_display_search_result($page->recode['total'], $page->recode['amount'], '개'); ?>
        </div>
        <div class="pull-right form-inline">
            <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $sort, null); ?>
            <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>
</form>

<form id="frmList" name="frmList" action="" method="post">
	<input type="hidden" name="mode" />
	<table class="table table-rows table-fixed js-excel-data">
		<thead>
		<tr>
			<th class="width3p"><input class="js-checkall" type="checkbox" data-target-name="sno"></th>
            <th class="width3p">번호</th>
            <th class="width7p">신청일자</th>
            <th class="width7p">처리일자</th>
            <th class="width7p display-none">주문일자</th>
			<th class="width10p">주문번호</th>
			<th class="width7p">신청자</th>
            <th class="width7p display-none">아이디</th>
            <th class="width7p">과세/면세</th>
			<th class="width7p">발급금액</th>
			<th class="width7p">결제방법</th>
            <th class="width7p">주문상태</th>
			<th class="width7p">발행상태</th>
            <th class="width7p display-none">승인번호</th>
            <th class="width7p display-none">취소승인번호</th>
            <th class="width7p display-none">입금자</th>
			<th class="width5p">정보</th>
			<th class="width5p">영수증</th>
            <th class="width5p">처리</th>
		</tr>
		</thead>
		<tbody>
<?php

    if (gd_isset($getData['data'])) {
        // 발행 상태 변경
        foreach ($arrStatus as $sKey => $sVal) {
            if ($sKey === 'y') {
                $arrStatus[$sKey] = '<span class="text-blue">' . $sVal . '</span>';
            }
            if ($sKey === 'c') {
                $arrStatus[$sKey] = '<span class="text-gray">' . $sVal . '</span>';
            }
            if ($sKey === 'd') {
                $arrStatus[$sKey] = '<span class="text-darkred">' . $sVal . '</span>';
            }
            if ($sKey === 'f') {
                $arrStatus[$sKey] = '<span class="text-orange-red">' . $sVal . '</span>';
            }
        }

        // 현금 영수증 리스트
        foreach ($getData['data'] as $key => $val) {
            foreach($val as $data) {
                if ($data['issueMode'] === 'a') {
                    $strSettleKind = '<span class="notice-ref font12">' . $arrIssue[$data['issueMode']] . '</span>';
                    $strOrderStatus = '<span class="notice-ref font12">' . $arrIssue[$data['issueMode']] . '</span>';
                    $strIssueStatus = '';
                    $strOrderNo = '<span class="text-darkgray">' . $data['orderNo'] . '</span>';
                } else {
                    $strSettleKind = gd_isset($setSettleKind[$data['settleKind']]['name']);
                    $strOrderStatus = gd_isset($setStatus[$data['orderStatus']]);
                    if ($data['issueMode'] === 'p') {
                        $strIssueStatus = '<br/><span class="notice-ref notice-sm">(' . $arrIssue[$data['issueMode']] . ')</span>';
                    } else {
                        $strIssueStatus = '';
                    }
                    $strOrderNo = '<a href="./order_view.php?orderNo=' . $data['orderNo'] . '" target="_blank">' . $data['orderNo'] . '</a>';
                }

                // 일괄 발급 처리
                if ($data['statusFl'] === 'r' || $data['statusFl'] === 'f') {
                    $selectedApprovalChk = true;
                } else {
                    $selectedApprovalChk = false;
                }

                $orderStatus = substr($data['orderStatus'], 0,1);
                ?>
                <tr class="text-center" <?php if($bgChk[$data['orderNo']] === true){ echo "style=background-color:#dbe7f0;"; } ?>>
                    <td class="js-sno"><input name="sno[]" type="checkbox"
                                              value="<?php echo $data['rSno']; ?>" <?php if ($selectedApprovalChk === false) {
                            echo 'disabled="disabled"';
                        } ?>/></td>
                    <td class="font-num"><?php echo number_format($page->idx--); ?></td>
                    <td class="font-date"><?php echo gd_date_format('Y-m-d H:i', $data['regDt']); ?></td>
                    <td class="note date"><?php echo gd_date_format('Y-m-d H:i', $data['processDt']); ?></td>
                    <td class="note date display-none"><?php echo gd_date_format('Y-m-d H:i', $data['oRegDt']); ?></td>
                    <td class="number bold"><?php echo $strOrderNo; ?></td>
                    <td class="notice-ref"><?php echo $data['requestNm']; ?></td>
                    <td class="notice-ref display-none"><?php echo $data['memId']; ?></td>
                    <td class="notice-ref"><?php echo $data['taxStatus']; ?></td>
                    <td class="font-num">
                        <?php if($data['priceChangeFl'] == 'y'){?>
                            <a href="./popup_order_cash_receipt_reissue.php?popupMode=yes&orderNo=<?php echo $data['orderNo'];?>&sno=<?php echo $data['rSno'];?>" onclick="window.open(this.href, 'cash_receipt_reissue', 'width=600,height=500');return false;" target="_blank" style="color:#ff0000;"><?php echo gd_currency_display($data['settlePrice']); ?></a>
                        <?php }else{?>
                            <span><?php echo gd_currency_display($data['settlePrice']); ?></span>
                        <?php }?>
                    </td>
                    <td class=""><?php echo $strSettleKind; ?></td>
                    <td class=""><?php echo $strOrderStatus; ?></td>
                    <td class=""><?php echo $arrStatus[$data['statusFl']] . $strIssueStatus; ?></td>
                    <td class="display-none"><?php echo $data['pgAppNo']; ?></td>
                    <td class="display-none"><?php echo $data['pgAppNoCancel']; ?></td>
                    <td class="display-none"><?php echo $data['bankSender']; ?></td>
                    <td class="js-info-view">
                        <button type="button" onclick="cash_receipt_process('<?php echo $data['orderNo']; ?>', '<?php echo $data['rSno']; ?>');"
                                class="btn-dark-gray btn-5">보기
                        </button>
                    </td>
                    <td class="js-receipt-view">
                        <?php
                        if ($data['statusFl'] === 'y' || ($data['statusFl'] === 'c' && $data['pgAppNoCancel'])) {
                            echo '<button type="button" onclick="pg_receipt_view_admin(\'cash\',\'' . $data['orderNo'] . '\',\'' . $data['rSno'] . '\');" class="btn-dark-gray btn-5">영수증</button>';
                        }
                        ?>
                    </td>
                    <td class="js-disposal-view">
                        <?php
                        if ($data['statusFl'] === 'r') {
                            if ($data['issueMode'] === 'a' || ($data['issueMode'] === 'u' && in_array(substr($data['orderStatus'], 0, 1), $statusReceiptPossible))) {
                                echo '<button type="button" data-orderno="' . $data['orderNo'] . '" data-reissue="' . $data['reIssueFl'] . '" data-sno="' . $data['rSno'] . '" class="btn btn-black btn-5 js-approval ">발급</button> ';
                            }
                            echo '<button type="button" data-orderno="' . $data['orderNo'] . '" data-reissue="' . $data['reIssueFl'] . '" data-sno="' . $data['rSno'] . '" class="btn btn-white btn-5 js-deny">거절</button> ';
                        } elseif ($data['statusFl'] === 'y' && $data['issueMode'] != 'p') {
                            echo '<button type="button" data-orderno="' . $data['orderNo'] . '" data-reissue="' . $data['reIssueFl'] . '" data-sno="' . $data['rSno'] . '" class="btn btn-white btn-5 js-cancel">취소</button> ';
                        } elseif ($data['statusFl'] === 'c') {
                            echo '<button type="button" data-orderno="' . $data['orderNo'] . '" data-reissue="' . $data['reIssueFl'] . '" data-sno="' . $data['rSno'] . '" class="btn btn-white btn-5 js-delete">삭제</button> ';
                        } elseif ($data['statusFl'] === 'd') {
                            echo '<button type="button" data-orderno="' . $data['orderNo'] . '" data-reissue="' . $data['reIssueFl'] . '" data-sno="' . $data['rSno'] . '" class="btn btn-black btn-5 js-request">재요청</button> ';
                            echo '<button type="button" data-orderno="' . $data['orderNo'] . '" data-reissue="' . $data['reIssueFl'] . '" data-sno="' . $data['rSno'] . '" class="btn btn-white btn-5 js-delete">삭제</button> ';
                        } elseif ($data['statusFl'] === 'f') {
                            echo '<button type="button" data-orderno="' . $data['orderNo'] . '" data-reissue="' . $data['reIssueFl'] . '" data-sno="' . $data['rSno'] . '" class="btn btn-black btn-5 js-approval">재발급</button> ';
                            echo '<button type="button" data-orderno="' . $data['orderNo'] . '" data-reissue="' . $data['reIssueFl'] . '" data-sno="' . $data['rSno'] . '" class="btn btn-white btn-5 js-delete">삭제</button> ';
                        }

                        ?>
                    </td>
                </tr>
                <?php
            }
        }
    } else {
?>
        <tr>
            <td class="no-data" colspan="12">검색된 정보가 없습니다.</td>
        </tr>
<?php
    }
?>
        </tbody>
    </table>
    <div class="table-action">
        <div class="pull-left">
            <span class="action-title">선택한 현금영수증을</span>
            <button type="button" class="btn btn-white js-approval-selected">일괄발급</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel btn-excel" data-excel-data="<?php echo $data;?>">엑셀 다운로드</button>
        </div>
    </div>
</form>

<div class="center"><?php echo $page->getPage(); ?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function (n) {
        // 현금영수증 발급
        $('.js-approval').click(function (e) {
            var orderNo = $(this).data('orderno');
            var reIssue = $(this).data('reissue');
            var sno = $(this).data('sno');
            BootstrapDialog.show({
                title: '현금영수증 발급',
                message: '[' + orderNo + '] 현금영수증 발급 처리를 하겠습니까?',
                buttons: [{
                    id: 'btn-approval',
                    label: '현금영수증 발급',
                    cssClass: 'btn-red',
                    action: function(dialog) {
                        var $approvalButton = this;
                        var $closeButton = dialog.getButton('btn-close');
                        $approvalButton.disable();
                        $closeButton.disable();
                        $approvalButton.spin();
                        dialog.setClosable(false);
                        dialog.setMessage('현금영수증 발급 중입니다.');
                        $.ajax({
                            type: 'POST'
                            , url: '../order/cash_receipt_ps.php'
                            , data: {'mode': 'pg_approval', 'modeType': 'list', 'orderNo': orderNo, 'reIssueFl': reIssue, 'sno': sno}
                            , success: function (res) {
                                console.log(res);
                                dialog.setClosable(true);
                                $closeButton.enable();
                                if (res == 'SUCCESS') {
                                    dialog.getModalBody().html('<?php echo __('현금영수증 발급 되었습니다.');?>');
                                } else {
                                    dialog.setTitle('현금영수증 발급 실패');
                                    dialog.setType(BootstrapDialog.TYPE_DANGER);
                                    dialog.getModalBody().html('<?php echo __('현금영수증 발급에 실패 하였습니다.');?>');
                                }
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            }
                        });
                    }
                },
                    {
                        id: 'btn-close',
                        label: '닫기',
                        action: function(dialogItself){
                            dialogItself.close();
                        }
                    }]
            });
        });

        // 현금영수증 발급 취소
        $('.js-cancel').click(function (e) {
            var orderNo = $(this).data('orderno');
            var reIssue = $(this).data('reissue');
            var adminChk = 'y';
            var sno = $(this).data('sno');
            BootstrapDialog.show({
                title: '현금영수증 발급 취소',
                type: BootstrapDialog.TYPE_WARNING,
                message: '[' + orderNo + '] 현금영수증 발급 취소 처리를 하겠습니까?',
                buttons: [{
                    id: 'btn-cancel',
                    label: '현금영수증 발급 취소',
                    cssClass: 'btn-warning',
                    action: function(dialog) {
                        var $cancelButton = this;
                        var $closeButton = dialog.getButton('btn-close');
                        $cancelButton.disable();
                        $closeButton.disable();
                        $cancelButton.spin();
                        dialog.setClosable(false);
                        dialog.setMessage('현금영수증 발급 취소 중입니다.');
                        $.ajax({
                            type: 'POST'
                            , url: '../order/cash_receipt_ps.php'
                            , data: {'mode': 'pg_cancel', 'modeType': 'list', 'orderNo': orderNo, 'reIssueFl': reIssue, 'sno': sno, 'adminChk': adminChk}
                            , success: function (res) {
                                dialog.setClosable(true);
                                $closeButton.enable();
                                if (res == 'SUCCESS') {
                                    dialog.getModalBody().html('<?php echo __('현금영수증 발급 취소 되었습니다.');?>');
                                } else {
                                    dialog.setTitle('현금영수증 발급 취소 실패');
                                    dialog.setType(BootstrapDialog.TYPE_DANGER);
                                    dialog.getModalBody().html('<?php echo __('현금영수증 발급 취소에 실패 하였습니다.');?>');
                                }
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            }
                        });
                    }
                },
                    {
                        id: 'btn-close',
                        label: '닫기',
                        action: function(dialogItself){
                            dialogItself.close();
                        }
                    }]
            });
        });

        // 현금영수증 거절
        $('.js-deny').click(function (e) {
            var orderNo = $(this).data('orderno');
            var reIssue = $(this).data('reissue');
            var sno = $(this).data('sno');
            BootstrapDialog.confirm({
                title: '현금영수증 신청 거절',
                type: BootstrapDialog.TYPE_WARNING,
                message: '[' + orderNo + '] 현금영수증 신청 거절 처리를 하겠습니까? 거절 후 삭제나 재발행이 가능합니다.',
                closable: false,
                callback: function(result) {
                    if (result) {
                        $.ajax({
                            type: 'POST'
                            , url: '../order/cash_receipt_ps.php'
                            , data: {'mode': 'cash_receipt_deny', 'orderNo': orderNo, 'reIssueFl': reIssue, 'sno': sno}
                            , success: function (res) {
                                if (res == 'SUCCESS') {
                                    alert('거절 처리 되었습니다.');
                                } else {
                                    alert('거절 처리에 실패 하였습니다.');
                                }
                                location.reload(true);
                            }
                        });
                    }
                }
            });
        });

        // 현금영수증 재요청
        $('.js-request').click(function (e) {
            var orderNo = $(this).data('orderno');
            var reIssue = $(this).data('reissue');
            var sno = $(this).data('sno');
            BootstrapDialog.confirm({
                title: '현금영수증 재요청',
                type: BootstrapDialog.TYPE_INFO,
                message: '[' + orderNo + '] 현금영수증을 재요청 하시겠습니까?',
                closable: false,
                callback: function(result) {
                    if (result) {
                        $.ajax({
                            type: 'POST'
                            , url: '../order/cash_receipt_ps.php'
                            , data: {'mode': 'cash_receipt_request', 'orderNo': orderNo, 'reIssueFl': reIssue, 'sno': sno}
                            , success: function (res) {
                                if (res == 'SUCCESS') {
                                    alert('재요청 처리 되었습니다.');
                                } else {
                                    alert('재요청 처리에 실패 하였습니다.');
                                }
                                location.reload(true);
                            }
                        });
                    }
                }
            });
        });

        // 현금영수증 삭제
        $('.js-delete').click(function (e) {
            var orderNo = $(this).data('orderno');
            var reIssue = $(this).data('reissue');
            var sno = $(this).data('sno');
            BootstrapDialog.confirm({
                title: '현금영수증 삭제',
                type: BootstrapDialog.TYPE_DANGER,
                message: '[' + orderNo + '] 현금영수증을 정말로 삭제 하시겠습니까?<br/>삭제시 복구가 불가능합니다.',
                closable: false,
                callback: function(result) {
                    if (result) {
                        $.ajax({
                            type: 'POST'
                            , url: '../order/cash_receipt_ps.php'
                            , data: {'mode': 'cash_receipt_delete', 'orderNo': orderNo, 'reIssueFl': reIssue, 'sno': sno}
                            , success: function (res) {
                                if (res == 'SUCCESS') {
                                    alert('삭제 되었습니다.');
                                } else {
                                    alert('삭제에 실패 하였습니다.');
                                }
                                location.reload(true);
                            }
                        });
                    }
                }
            });
        });

        // 선택한 현금영수증 일괄 발급
        $('.js-approval-selected').click(function () {
            //var chkCnt = $('input[name=\'orderNo[]\']:checkbox:checked').length;
            var chkCnt = $('input[name=\'sno[]\']:checkbox:checked').length;

            if (chkCnt < 1) {
                BootstrapDialog.show({
                    title: '선택한 현금영수증 일괄 발급',
                    type: BootstrapDialog.TYPE_WARNING,
                    message: '일괄 발급할 현금영수증을 선택해 주세요.',
                });
                return;
            }

            BootstrapDialog.show({
                title: '선택한 현금영수증 일괄 발급',
                message: '선택한 ' + chkCnt + ' 개의 현금영수증을 일괄 발급 처리 하시겠습니까?',
                buttons: [{
                    id: 'btn-approval',
                    label: '일괄 발급',
                    cssClass: 'btn-red',
                    action: function(dialog) {
                        var $approvalButton = this;
                        var $closeButton = dialog.getButton('btn-close');
                        $approvalButton.disable();
                        $closeButton.disable();
                        $approvalButton.spin();
                        //dialog.setClosable(false);
                        dialog.setMessage('선택한 ' + chkCnt + ' 개의 현금영수증을 일괄 발급 처리 중입니다.');

                        $('#frmList input[name=\'mode\']').val('pg_approval_selected');
                        $('#frmList').attr('method', 'post');
                        $('#frmList').attr('target', 'ifrmProcess');
                        $('#frmList').attr('action', './cash_receipt_ps.php');
                        $('#frmList').submit();
                    }
                },
                    {
                        id: 'btn-close',
                        label: '닫기',
                        action: function(dialogItself){
                            dialogItself.close();
                        }
                    }]
            });
            return;
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchManager').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchManager').submit();
        });

        $('.btn-excel').click(function () {
            //var arrData = $(this).data('excel-data');

            $('input[name=\'excelMode\']').attr('value', 'y');
            $('#frmSearchManager').attr('target', 'ifrmProcess');
            $('#frmSearchManager').submit();
            $('input[name=\'excelMode\']').attr('value', '');
            $('#frmSearchManager').attr('target', '');
        });

        /*$('.js-cash-receipt-popup').click(function (){
            var orderNo = $(this).data('order-no');
            window.open('./popup_order_cash_receipt_reissue.php?popupMode=yes&orderNo=' + orderNo, 'cash_receipt_reissue', 'width=600,height=500');
        });*/

    });



    function receipt_excel_download(excelName) {
        var arrData = <?php echo json_encode($getData['excelData'])?>;   // 검색 데이터
        var arrIssue = <?php echo json_encode($arrIssue)?>; // 영수증 발행상태
        var arrOrdStatus = <?php echo json_encode($setStatus)?>;    // 주문상태
        var arrCashStatus = <?php echo json_encode($arrStatus)?>;   //
        var setSettleKind = <?php echo json_encode($setSettleKind)?>;
        var $form = $('<form></form>');
        $form.attr('action', './excel_ps.php');
        $form.attr('method', 'post');
        $form.attr('target', 'ifrmProcess');
        $form.appendTo('body');

        var mode = $('<input type="hidden" name="mode" value="receipt_excel_download">');
        var excel_name = $('<input type="hidden" name="excel_name" value="'+excelName+'">');

        // 페이드인 div 추가
        var addHtml = "<style>td{mso-number-format:'\@';}</style><table border='1'>";
        addHtml += '<thead>';
        addHtml += '<tr>';
        addHtml += '<th>번호</th>';
        addHtml += '<th>신청일자</th><br>';
        addHtml += '<th>처리일자</th><br>';
        addHtml += '<th>주문일자</th><br>';
        addHtml += '<th>주문번호</th><br>';
        addHtml += '<th>신청자</th><br>';
        addHtml += '<th>아이디</th><br>';
        addHtml += '<th>과세/면세</th><br>';
        addHtml += '<th>발급금액</th><br>';
        addHtml += '<th>결제방법</th><br>';
        addHtml += '<th>주문상태</th><br>';
        addHtml += '<th>발행상태</th><br>';
        addHtml += '<th>승인번호</th><br>';
        addHtml += '<th>취소승인번호</th><br>';
        addHtml += '<th>입금자</th><br>';
        addHtml += '</tr><br>';
        addHtml += '</thead><br>';
        addHtml += '<tbody><br>';

        var icx = 1;
        if(arrData != null) {
            $.each(arrData, function (key, val) {
                $.each(val, function (k, v) {
                    var index = icx++;
                    var floorSettlePrice = String(Math.floor(v.settlePrice));
                    var settlePrice = floorSettlePrice.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
                    var strIssueTxt = arrCashStatus[v.statusFl];
                    // 결제방법
                    if (typeof setSettleKind[v.settleKind] == "undefined") {
                        if (v.issueMode == 'a') {
                            var strSettleTxt = arrIssue[v.issueMode];
                        } else if (v.issueMode == 'p') {
                            var strSettleTxt = arrIssue[v.issueMode];
                        } else {
                            var strSettleTxt = '';
                        }
                    } else {
                        var strSettleTxt = setSettleKind[v.settleKind]['name'];
                    }

                    // 주문상태
                    if (v.orderStatus == null) {
                        if (v.issueMode == 'a') {
                            var orderStatus = arrIssue[v.issueMode];
                        } else if (v.issueMode == 'p') {
                            var orderStatus = arrIssue[v.issueMode];
                        } else {
                            var orderStatus = '';
                        }
                    } else {
                        var orderStatus = arrOrdStatus[v.orderStatus];
                    }

                    // 빈값처리
                    if (!v.memId) {
                        v.memId = '';
                    }
                    if (!v.oRegDt) {
                        v.oRegDt = '';
                    }
                    if (!orderStatus) {
                        orderStatus = '';
                    }
                    if (!v.bankSender) {
                        v.bankSender = '';
                    }

                    // 과세/면세
                    if(v.taxPrice > 0 && v.freePrice == 0){
                        v.taxStatus = '과세';
                    }
                    if(v.taxPrice == 0 && v.freePrice > 0){
                        v.taxStatus = '면세';
                    }
                    if(v.taxPrice > 0 && v.freePrice > 0){
                        v.taxStatus = '복합과세';
                    }

                    addHtml += '<tr><br>';
                    addHtml += '<td>' + index + '</td><br>';
                    addHtml += '<td>' + v.regDt + '</td><br>';
                    addHtml += '<td>' + v.processDt + '</td><br>';
                    addHtml += '<td>' + v.oRegDt + '</td><br>';
                    addHtml += '<td>' + v.orderNo + '</td><br>';
                    addHtml += '<td>' + v.requestNm + '</td><br>';
                    addHtml += '<td>' + v.memId + '</td><br>';
                    addHtml += '<td>' + v.taxStatus + '</td><br>';
                    addHtml += '<td>' + settlePrice + '</td><br>';
                    addHtml += '<td>' + strSettleTxt + '</td><br>';
                    addHtml += '<td>' + orderStatus + '</td><br>';
                    addHtml += '<td>' + strIssueTxt + '</td><br>';
                    addHtml += '<td>' + v.pgAppNo + '</td><br>';
                    addHtml += '<td>' + v.pgAppNoCancel + '</td><br>';
                    addHtml += '<td>' + v.bankSender + '</td><br>';
                    addHtml += '</tr><br>';
                });
            });

            addHtml += '</tbody><br></table>';

            addHtml += '<br><br>';
            addHtml += "<table border='1'>";
            addHtml += '<thead>';
            addHtml += '<tr>';
            addHtml += '<th style="background-color: #444444; color: #ffffff;">항목명</th>';
            addHtml += '<th style="background-color: #444444; color: #ffffff;">표시 정책</th>';
            addHtml += '<th style="background-color: #444444; color: #ffffff;">비고</th>';
            addHtml += '</tr>';
            addHtml += '</thead>';
            addHtml += '<tbody>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">번호</th><td>다운로드 개수 표시</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">신청일자</th><td>현금영수증 신청일 (년-월-일 시:분)</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">처리일자</th><td>현금영수증 발급완료 /발급취소 처리일 (년-월-일 시:분)</td><td>발급거절은 처리일자에 적용되지 않음</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">주문일자</th><td>주문 완료일 (년-월-일 시:분)</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">주문번호</th><td>현금영수증 발급 신청한 주문의 주문번호 표시</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">신청자</th><td>현금영수증 발급 신청자명 표시</td><td>현금영수증 개별 발급한 경우, 주문자와 신청자명이 상이 할 수 있음.</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">아이디</th><td>현금영수증 발급 신청한 주문의 회원 아이디 표시</td><td>비회원인 경우 비회원으로 표시</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">과세/면세</th><td>과세 / 면세 / 복합과세 중 표시</td><td>현금영수증 재 신청한 경우, 최종 신청건의 과세/면세 정보로 표시</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">발급금액</th><td>현금영수증 신청 금액 표시</td><td>현금영수증 재 신청하여 발급금액이 변경된 경우, 최종 신청 금액으로 표시</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">결제방법</th><td>현금영수증 발급 신청한 주문의 결제방법 표시</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">주문상태</th><td>현 시점의 주문 처리상태 표시</td><td>정상 상태 우선 표시(정상상태:입금대기/결제완료/상품준비중/배송중/배송완료/구매확정)<br>' +
                '주문에 포함된 일부 상품의 처리상태만 변경된 경우에도 업데이트 됨<br>' +
                '클레임 주문처리 상태만 존재할 경우 클레임 상태로 표시<br>' +
                '주문처리 단계 중 후순위 단계가 표시<br>' +
                ' - 정상 상태:구매확정>배송완료>배송>상품준비>결제>미입금<br>' +
                ' - 클레임 상태:환불>반품>교환>취소<br>' +
                '<span style="color: #FF0000;">정상과 클레임 상태 모두 포함된 경우, 클레임 상태 우선 표시 됨</span></td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">발행상태</th><td>현금영수증 발행상태 표시</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">승인번호</th><td>승인번호 표시</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">취소승인번호</th><td>취소승인번호 표시</td></tr>';
            addHtml += '<tr><th style="background-color:#dbe7f0;">입금자</th><td>입금자명 표시</td></tr>';
            addHtml += '</tbody><br></table>';

            var data = $('<input type="hidden" name="data" value="' + encodeURI(addHtml) + '">');

            $form.append(mode).append(excel_name).append(data);
            $form.submit();
        }
    }

    <?php
    if(json_encode($getData['excelData']) != null){
    ?>
    receipt_excel_download('현금영수증 리스트');
    <?php
    }
    ?>
    //-->
</script>
