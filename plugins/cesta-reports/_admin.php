<style>
#wpfooter {display: none;}
</style>

<div class="wrap container-fluid">
<div class="row">
        <div class="col-md-7">
            <h4 class="display-4" style="font-size: 2em;">Relatórios - Cesta Portuguesa</h4>
        </div>
        <div class="col-md-5">
            <p class="description text-right">
            Os relatórios devem de ser configurados à medida - as definições por defeito, no entanto, somente poderão ser alteradas via solicitação ao desenvolvimento.
            </p>
        </div>
    
    </div>
<hr>
<div style="height: 20px;"></div>
    
<style>
    label {font-size: 0.75em; text-transform: uppercase; margin-top: 8px;}
</style>

            <form class="row" action="#hm_sbp_table" method="post">
					
                <input type="hidden" name="hm_sbp_do_export" value="1" />
          
<fieldset class="form-group col-md-4">
    <h4 class="display-4" style="font-size: 1em;">Período a Cobrir</h4>
    <hr>
<?php
             
wp_nonce_field('hm_sbpf_do_export');

echo ('<select name="report_time" id="hm_sbp_field_report_time">
							<option value="0d"'.($reportSettings['report_time'] == '0d' ? ' selected="selected"' : '').'>Apenas Hoje</option>
							<option value="1d"'.($reportSettings['report_time'] == '1d' ? ' selected="selected"' : '').'>Apenas Ontem</option>
							<option value="7d"'.($reportSettings['report_time'] == '7d' ? ' selected="selected"' : '').'>7 dias a excluir hoje</option>
							<option value="30d"'.($reportSettings['report_time'] == '30d' ? ' selected="selected"' : '').'>30 dias a excluir hoje</option>
							<option value="0cm"'.($reportSettings['report_time'] == '0cm' ? ' selected="selected"' : '').'>Mês atual</option>
							<option value="1cm"'.($reportSettings['report_time'] == '1cm' ? ' selected="selected"' : '').'>Mês passadp</option>
							<option value="+7d"'.($reportSettings['report_time'] == '+7d' ? ' selected="selected"' : '').'>Os próximos 7 dias</option>
							<option value="+30d"'.($reportSettings['report_time'] == '+30d' ? ' selected="selected"' : '').'>Os próximos 30 dias</option>
							<option value="+1cm"'.($reportSettings['report_time'] == '+1cm' ? ' selected="selected"' : '').'>O próximo mês</option>
							<option value="all"'.($reportSettings['report_time'] == 'all' ? ' selected="selected"' : '').'>Tudo</option>
							<option value="custom"'.($reportSettings['report_time'] == 'custom' ? ' selected="selected"' : '').'>Customizar</option>
						</select>
				</fieldset><div class="hm_sbp_custom_time form-group col-md-4 form-control-sm"><div style="height: 40px;"></div>
						<label for="hm_sbp_field_report_start">Start Date:</label><br>
						<input type="date" name="report_start" id="hm_sbp_field_report_start" value="'.$reportSettings['report_start'].'" />
				</div><div style="height: 40px;"></div>
				<div class="hm_sbp_custom_time form-group col-md-4 form-control-sm"><div style="height: 40px;"></div>
						<label for="hm_sbp_field_report_end">End Date:</label><br>
						<input type="date" name="report_end" id="hm_sbp_field_report_end" value="'.$reportSettings['report_end'].'" /></div></div>');
                
echo('
			<div style="height: 20px;"></div><div class="row">
	
				');
?>
<fieldset class="form-group col-md-6">
    <h4 class="display-4" style="font-size: 1em;">Incluir Encomendas nos Estados</h4>
    <hr><table class="form-table">
<?php
                
foreach (wc_get_order_statuses() as $status => $statusName) {
	echo('<label><input type="checkbox" name="order_statuses[]"'.(in_array($status, $reportSettings['order_statuses']) ? ' checked="checked"' : '').' value="'.$status.'" /> '.$statusName.'</label><br />');
}

?>
</td>
</tr></table>
                </fieldset>
<fieldset class="form-group col-md-6">
    <h4 class="display-4" style="font-size: 1em;">Organização</h4>
    <hr><table class="form-inline">
    
<?php

			echo('
				<tr valign="top">

					<td>
						<select name="orderby" id="hm_sbp_field_orderby">
							<option value="product_id"'.($reportSettings['orderby'] == 'product_id' ? ' selected="selected"' : '').'>ID dos Produtos</option>
							<option value="quantity"'.($reportSettings['orderby'] == 'quantity' ? ' selected="selected"' : '').'>Quantidades</option>
							<option value="gross"'.($reportSettings['orderby'] == 'gross' ? ' selected="selected"' : '').'>Vendas Brutas</option>
							<option value="gross_after_discount"'.($reportSettings['orderby'] == 'gross_after_discount' ? ' selected="selected"' : '').'>Vendas Após Descontos</option>
						</select>
						<select name="orderdir">
							<option value="asc"'.($reportSettings['orderdir'] == 'asc' ? ' selected="selected"' : '').'>ASCENDENTE</option>
							<option value="desc"'.($reportSettings['orderdir'] == 'desc' ? ' selected="selected"' : '').'>DESCENDENTE</option>
						</select>
					</td>
				</tr></table>');

?>

<div style="height: 20px;"></div>
    <h4 class="display-4" style="font-size: 1em;">Campos a Incluir</h4>
    <hr><table class="form-inline">
    <tr valign="top">
        <td id="hm_psr_report_field_selection">
<?php

$fieldOptions2 = $fieldOptions;
foreach ($reportSettings['fields'] as $fieldId) {
	if (!isset($fieldOptions2[$fieldId]))
		continue;
	echo('<label><input type="checkbox" name="fields[]" checked="checked" value="'.$fieldId.'"'.(in_array($fieldId, array('variation_id', 'variation_attributes')) ? ' class="variation-field"' : '').' /> '.$fieldOptions2[$fieldId].'</label>');
	unset($fieldOptions2[$fieldId]);
}
foreach ($fieldOptions2 as $fieldId => $fieldDisplay) {
	echo('<label><input type="checkbox" name="fields[]" value="'.$fieldId.'"'.(in_array($fieldId, array('variation_id', 'variation_attributes')) ? ' class="variation-field"' : '').' /> '.$fieldDisplay.'</label>');
}
unset($fieldOptions2);
			echo('</td>
				</tr>
				<tr valign="top">
					<th scope="row" colspan="2" class="th-full">
						<label>
							<input type="checkbox" name="exclude_free"'.(empty($reportSettings['exclude_free']) ? '' : ' checked="checked"').' />
							Exclude free products
						</label>
						<p class="description">If checked, order line items with a total amount of zero (after discounts) will be excluded from the report calculations.</p>
					</th>
				</tr>
				<tr valign="top">
					<th scope="row" colspan="2" class="th-full">
						<label>
							<input type="checkbox" name="limit_on"'.(empty($reportSettings['limit_on']) ? '' : ' checked="checked"').' />
							Show only the first
							<input type="number" name="limit" value="'.$reportSettings['limit'].'" min="0" step="1" class="small-text" />
							products
						</label>
					</th>
				</tr>
				<tr valign="top">
					<th scope="row" colspan="2" class="th-full">
						<label>
							<input type="checkbox" name="include_header"'.(empty($reportSettings['include_header']) ? '' : ' checked="checked"').' />
							Include header row
						</label>
					</th>
				</tr>
			</table>');
			
			echo('<p class="submit">
				<button type="submit" class="button-primary" onclick="jQuery(this).closest(\'form\').attr(\'target\', \'\'); return true;">View Report</button>
				<button type="submit" class="button-primary" name="hm_sbp_download" value="1" onclick="jQuery(this).closest(\'form\').attr(\'target\', \'_blank\'); return true;">Download Report as CSV</button>
			</p>
		</form></div>
		');
		
		
		if (!empty($_POST['hm_sbp_do_export'])) {
			echo('<table id="hm_sbp_table">');
			if (!empty($_POST['include_header'])) {
				echo('<thead><tr>');
				foreach (hm_sbpf_export_header(null, true) as $rowItem)
					echo('<th>'.htmlspecialchars($rowItem).'</th>');
				echo('</tr></thead>');
			}
			echo('<tbody>');
			foreach (hm_sbpf_export_body(null, true) as $row) {
				echo('<tr>');
				foreach ($row as $rowItem) {
					echo('<td>'.htmlspecialchars($rowItem).'</td>');
				}
				echo('</tr>');
			}
			echo('</tbody></table>');
			
		}


		
echo('
	</div>
	
	<script type="text/javascript" src="'.plugins_url('js/hm-product-sales-report.js', __FILE__).'"></script>
');
?>