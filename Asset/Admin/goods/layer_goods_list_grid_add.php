<tr>
	<td class="goods-add-display-list" colspan="<?= count($goodsGridConfigList) - 1 ?>">
		<div class="pull-left">
			<ul>
				<?php
				// 인기상품진열
				if ($goodsGridConfigList['display']['best'] === 'y' && gd_is_provider() === false) { ?>
					<li>
						<table class="grid-table-cols">
							<thead>
							<tr>
								<th>
									· 인기상품진열
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
							if(count($val['goodsGridDisplayData']['best']) > 0) {
								foreach ($val['goodsGridDisplayData']['best'] as $bestKey => $bestVal) {
									?>
									<tr>
										<td class="pd0">
											<label class="goods_grid_<?=$val['goodsNo'];?> hand grid-margin-reset">
												<input type="checkbox" name="bestCode[]" value="<?=$bestVal['sno']?>"> <?=$bestVal['populateName'];?>
											</label>
										</td>
									</tr>
									<?php
								}
							} else {
								?>
								<tr>
									<td class="pd0" style="margin: 0 0 0 40px;">
										<div class="grid-margin-reset">인기상품 진열 미지정 상품</div>
									</td>
								</tr>
								<?php
							}
							?>
							</tbody>
						</table>
					</li>
					<?php
				} // 메인분류진열
				if ($goodsGridConfigList['display']['main'] === 'y' && gd_is_provider() === false) { ?>
					<li>
						<table class="grid-table-cols">
							<thead>
							<tr>
								<th>
									· 메인분류
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
							if(count($val['goodsGridDisplayData']['main']) > 0) {
								foreach ($val['goodsGridDisplayData']['main'] as $mainKey => $mainVal) {
									?>
									<tr>
										<td class="pd0">
											<label class="goods_grid_<?=$val['goodsNo'];?> hand grid-margin-reset">
												<input type="checkbox" name="mainCode[]" value="<?=$mainVal['themeCode']?>"> <?=$mainVal['themeNm'];?>
											</label>
										</td>
									</tr>
									<?php
								}
							} else {
								?>
								<tr>
									<td class="pd0">
										<div class="grid-margin-reset"> 메인분류 미지정 상품</div>
									</td>
								</tr>
								<?php
							}
							?>
							</tbody>
						</table>
					</li>
					<?php
				}  // 카테고리진열
				if ($goodsGridConfigList['display']['cate'] === 'y') { ?>
					<li>
						<table class="grid-table-cols">
							<thead>
							<tr>
								<th>
									· 카테고리
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
							// 카테고리 데이터가 있을 경우
							if(count($val['goodsGridDisplayData']['cate']) > 0) {
								foreach ($val['goodsGridDisplayData']['cate'] as $cateKey => $cateVal) {
									?>
									<tr>
										<td class="pd0">
											<label class="goods_grid_<?=$val['goodsNo'];?> hand grid-margin-reset">
												<input type="checkbox" name="cateCode[]" value="<?=$cateKey?>">
												<?php
												// 대표카테고리 체크
												if($cateKey == $val['cateCd']) echo "(대표카테고리)";
												?>
												<?=$cateVal['cateNm'];?>
												<?php
												// 국가분류
												if(count($cateVal['cateFlag']) > 0) {
													foreach ($cateVal['cateFlag'] as $flagKey => $flagValue) {
														?>
														<span class="js-popover flag flag-16 flag-<?= $flagKey; ?>" data-content="<?= $flagValue ?>"></span>
														<?php
													}
												}
												?>
											</label>
										</td>
									</tr>
									<?php
								}
							} else {
								?>
								<tr>
									<td class="pd0">
										<div class="grid-margin-reset">카테고리 미지정 상품</div>
									</td>
								</tr>
								<?php
							}
							?>
							</tbody>
						</table>
					</li>
				<?php } ?>
			</ul>
		</div>
		<div class="pull-left" style="padding:8px;">
			<button type="button" class="js-delete-grid-display btn btn-sm btn-white btn-icon-minus" data-add-list-catecd="<?= $val['cateCd'] ?>" data-add-list-goodsno="<?= $val['goodsNo'] ?>">삭제</button>
		</div>
	</td>
</tr>