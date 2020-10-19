<form id="frmTaxInvoice" name="frmTaxInvoice" target="ifrmProcess" action="tax_invoice_ps.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="mode" value="tax_invoice_config" />
<input type="hidden" name="taxStampIamgeTmp" value="<?php echo $data['taxStampIamge'];?>" />

	<div class="page-header js-affix">
		<h3><?php echo end($naviMenu->location); ?>
		</h3>
		<input type="submit" value="저장" class="btn btn-red"/>
	</div>

	<div class="table-title gd-help-manual">
			기본설정
		</div>
		<table class="table table-cols tax-bill">
			<colgroup>
				<col class="width-md" />
				<col />
			</colgroup>
		<tr>
			<th>발행 사용 설정</th>
			<td>
				<div class="radio"><label title=""><input type="radio" name="taxInvoiceUseFl" value="y" <?php echo gd_isset($checked['taxInvoiceUseFl']['y']);?> />사용함</label>
					<span class="pd5 pdl15">
						<label class="checkbox-inline">
							<input type="checkbox" name="gTaxInvoiceFl" value="y" <?php echo gd_isset($checked['gTaxInvoiceFl']['y']);?> > 일반 세금계산서
						</label>
						<label class="checkbox-inline">
							<input type="checkbox" name="eTaxInvoiceFl" value="y" <?php echo gd_isset($checked['eTaxInvoiceFl']['y']);?> >전자 세금계산서
							<span class="notice-info">법인사업자 및 직전년도 매출액 3억원 이상의 개인사업자는 의무발행 대상입니다.</span>
						</label>
					</span>
				</div>
				<div class="radio"><label title=""><input type="radio" name="taxInvoiceUseFl" value="n" <?php echo gd_isset($checked['taxInvoiceUseFl']['n']);?> />사용안함</label><span class="notice-info">간이과세자는 세금계산서 발행이 불가능하므로 “사용안함“ 설정하시기 바랍니다.</span></div>
			</td>
		</tr>
		<tr>
			<th>세금계산서<br/>신청기간제한</th>
			<td>
				<div class="radio form-inline"><label title=""><input type="radio" name="taxInvoiceLimitFl" value="y" <?php echo gd_isset($checked['taxInvoiceLimitFl']['y']);?> />결제완료 기준 다음 달
					<select name="taxInvoiceLimitDate" class="form-control js-number">
						<option value="1" <?php echo gd_isset($selected['taxInvoiceLimitDate']['1']);?> >1</option>
						<option value="5" <?php echo gd_isset($selected['taxInvoiceLimitDate']['5']);?> >5</option>
						<option value="10" <?php echo gd_isset($selected['taxInvoiceLimitDate']['10']);?> >10</option>
					</select>
					일까지 신청가능 </label>
				</div>
				<div class="radio"><label title=""><input type="radio" name="taxInvoiceLimitFl" value="n" <?php echo gd_isset($checked['taxInvoiceLimitFl']['n']);?> />제한안함 </label><span class="notice-info">재화, 용역의 공급시기가 속하는 달의 다음달 10일 경과 후 세금계산서 발행 시 가산세가 부과됩니다.
</span></div>
			</td>
		</tr>
		<tr>
			<th>발행금액 포함항목</th>
			<td>
				<div class="checkbox form-inline">
					<label class="checkbox-inline">
						<input type="checkbox" name="taxDeliveryFl" value="y"  <?php echo gd_isset($checked['taxDeliveryFl']['y']);?>  > 배송비
					</label>
					<label class="checkbox-inline">
						<input type="checkbox" name="TaxMileageFl" value="y"  <?php echo gd_isset($checked['TaxMileageFl']['y']);?> >마일리지 사용금액
					</label>
					<label class="checkbox-inline">
						<input type="checkbox" name="taxDepositFl" value="y"  <?php echo gd_isset($checked['taxDepositFl']['y']);?>  >예치금 사용금액
					</label>
				</div>
				<div class="notice-info">
					<p>신용카드로 결제한 건은 세금계산서 발행이 불가능하며 신용카드 매출전표로 부가가치세를 신고해야 합니다.</p>
					<p>사업자가 고객에게 매출액의 일정 비율에 해당하는 마일리지를 적립해 주고, 향후 그 고객이 재화를 공급받고<br/>
					그 대가의 일부 또는 전부를 적립된 마일리지로 결제하는 경우 해당 마일리지 상당액은 공급가액에 포함됩니다.(부가가치세법 시행령)</p>
					<p>신용카드로 결제한 건을 예치금으로 환불 시 기존 결제수단이 취소되지 않은 상태에서 세금계산서에 예치금 사용 금액을<br/>
					포함하는 경우 이중 증빙 발급상태가 될 수 있으므로 주의해야 합니다. 또한 마일리지와 같이 예치금을 지급한 경우는<br/>
					위에서 설명하고 있는 대로 예치금 사용 금액이 공급가액에 포함되어야 합니다.</p>
				</div>
			</td>
		</tr>
		<tr>
			<th>발행일자</th>
			<td>
				<div class="radio"><label title=""><input type="radio" name="taxStepFl" value="p" <?php echo gd_isset($checked['taxStepFl']['p']);?> />결제완료 기준</label></div>
				<div class="radio">
                    <label title=""><input type="radio" name="taxStepFl" value="d" <?php echo gd_isset($checked['taxStepFl']['d']);?> />배송완료 기준</label>
                    <label><input type="checkbox" name="taxDeliveryCompleteFl" value="y" <?php echo gd_isset($checked['taxDeliveryCompleteFl']['y']) . gd_isset($disabled['taxDeliveryCompleteFl']['p']);?> />주문에 포함된 '모든 상품' 배송완료 기준</label>
                </div>
			</td>
		</tr>
        <tr>
            <th>주문서 작성페이지<br />노출여부</th>
            <td>
                <div class="radio"><label title=""><input type="radio" name="taxInvoiceOrderUseFl" value="y" <?php echo gd_isset($checked['taxInvoiceOrderUseFl']['y']);?> />노출함</label></div>
                <div class="radio"><label title=""><input type="radio" name="taxInvoiceOrderUseFl" value="n" <?php echo gd_isset($checked['taxInvoiceOrderUseFl']['n']);?> />노출안함</label></div>
                <div class="notice-info">
                    주문서 작성 페이지에서 고객이 세금계산서를 신청할 수 있도록 설정할 수 있습니다.<br />
                    “노출안함” 선택 시 고객은 마이페이지 주문상세에서만 신청이 가능합니다.
                </div>
                <div class="notice-danger">
                    “노출함” 설정 시에도, 계좌이체와 가상계좌, 에스크로 결제는 적용되지 않습니다.<br />
                    위의 결제수단의 경우 현금영수증과 세금계산서 중복신청이 가능하므로,<br />
                    세금계산서 발행 전에 반드시 현금영수증 발행여부를 체크하여 중복발행으로 인한 불이익이 없도록 주의하시기 바랍니다.
                </div>
            </td>
        </tr>
        <tr>
            <th>이용안내 문구</th>
            <td>
                <div class="radio">
                    <label class="radio-inline"><input type="radio" name="taxinvoiceInfoUseFl" value="y" <?php echo gd_isset($checked['taxinvoiceInfoUseFl']['y']);?> />사용함</label>
                    <label class="radio-inline"><input type="radio" name="taxinvoiceInfoUseFl" value="n" <?php echo gd_isset($checked['taxinvoiceInfoUseFl']['n']);?> />사용안함</label>
                </div>
                <textarea name="taxinvoiceInfo" rows="6" class="form-control width-3xl"><?php echo gd_isset($data['taxinvoiceInfo']);?></textarea>
                “이용안내 문구” 경우에는 ‘치환코드 : <b>{taxinvoiceInfo}</b>’ 를 이용하여 주문서 작성페이지 내 세금계산서 신청부분에 노출시킬 수 있습니다.
            </td>
        </tr>
        <tr>
            <th>발행 신청 마감<br />안내 문구</th>
            <td>
                <div class="radio">
                    <label class="radio-inline"><input type="radio" name="taxinvoiceDeadlineUseFl" value="y" <?php echo gd_isset($checked['taxinvoiceDeadlineUseFl']['y']);?> />사용함</label>
                    <label class="radio-inline"><input type="radio" name="taxinvoiceDeadlineUseFl" value="n" <?php echo gd_isset($checked['taxinvoiceDeadlineUseFl']['n']);?> />사용안함</label>
                </div>
                <textarea name="taxinvoiceDeadline" rows="6" class="form-control width-3xl"><?php echo gd_isset($data['taxinvoiceDeadline']);?></textarea>
                <div class="notice-info">
                    쇼핑몰 주문조회 상세화면에서 세금계산서 발행신청 기간이 지난 주문 건에 대하여 안내문구를 노출시킬 수 있습니다.
                </div>
            </td>
        </tr>
	</table>

	<div class="table-title gd-help-manual">
		전자 세금계산서 설정
	</div>
	<table class="table table-cols">
		<colgroup>
			<col class="width-md" />
			<col />
		</colgroup>
		<tr>
			<th>고도빌 회원ID</th>
			<td><div class="form-inline">
					<input type="text" name="godobillSiteId" value="<?php echo $data['godobillSiteId']?>" class="form-control">
				</div>
			</td>
		</tr>
		<tr>
			<th>고도빌 API_KEY</th>
			<td><div class="form-inline">
					<input type="text" name="godobillApiKey" value="<?php echo $data['godobillApiKey']?>" class="form-control">
				</div>
				<div class="notice-info">
					고도빌 홈페이지에서 로그인 후, 로그인박스에 있는 [API KEY] 버튼을 클릭하면 확인할 수 있습니다. <br/>
					API KEY 값을 복사하여, 삽입하시면 됩니다.
				</div>
			</td>
		</tr>
		<tr>
			<th>고도빌 바로가기</th>
			<td>
				<a href="https://www.godobill.com:444" target="_blank" class="btn btn-gray btn-sm">고도빌 바로가기</a>
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript">
    $(function(){
        $('input[name="taxStepFl"]').click(function(){
            var disabled = false;
            switch (this.value) {
                case 'p':
                    disabled = true;
                    break;
            }
            $('input[name="taxDeliveryCompleteFl"]').prop({'disabled':disabled});
        });
    })
</script>