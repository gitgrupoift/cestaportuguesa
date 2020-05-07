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

<?php

// Print form
echo('<div id="poststuff">
                <div id="post-body" class="row">
                        <div id="post-body-content" style="position: relative;">
                                <form action="#hm_sbp_table" method="post">
                                        <input type="hidden" name="hm_sbp_do_export" value="1" />
        ');
wp_nonce_field('hm_sbpf_do_export');
echo('
                        <table class="form-table">
                                <tr valign="top" style="display: none;">
                                        <th scope="row">
                                                <label for="hm_sbp_field_report_time">Report Period:</label>
                                        </th>
                                        <td>
                                                <select name="report_time" id="hm_sbp_field_report_time">
                                                        <option value="0d"'.($reportSettings['report_time'] == '0d' ? ' selected="selected"' : '').'>Today</option>
                                                        <option value="1d"'.($reportSettings['report_time'] == '1d' ? ' selected="selected"' : '').'>Yesterday</option>
                                                        <option value="7d"'.($reportSettings['report_time'] == '7d' ? ' selected="selected"' : '').'>Previous 7 days (excluding today)</option>
                                                        <option value="30d"'.($reportSettings['report_time'] == '30d' ? ' selected="selected"' : '').'>Previous 30 days (excluding today)</option>
                                                        <option value="0cm"'.($reportSettings['report_time'] == '0cm' ? ' selected="selected"' : '').'>Current calendar month</option>
                                                        <option value="1cm"'.($reportSettings['report_time'] == '1cm' ? ' selected="selected"' : '').'>Previous calendar month</option>
                                                        <option value="+7d"'.($reportSettings['report_time'] == '+7d' ? ' selected="selected"' : '').'>Next 7 days (future dated orders)</option>
                                                        <option value="+30d"'.($reportSettings['report_time'] == '+30d' ? ' selected="selected"' : '').'>Next 30 days (future dated orders)</option>
                                                        <option value="+1cm"'.($reportSettings['report_time'] == '+1cm' ? ' selected="selected"' : '').'>Next calendar month (future dated orders)</option>
                                                        <option value="all"'.($reportSettings['report_time'] == 'all' ? ' selected="selected"' : '').'>All time</option>
                                                        <option value="custom"'.($reportSettings['report_time'] == 'custom' ? ' selected="selected"' : '').'>Custom date range</option>
                                                </select>
                                        </td>
                                </tr>
                                <tr valign="top" class="hm_sbp_custom_time">
                                        <th scope="row">
                                                <label for="hm_sbp_field_report_start">Start Date:</label>
                                        </th>
                                        <td>
                                                <input type="date" name="report_start" id="hm_sbp_field_report_start" value="'.$reportSettings['report_start'].'" />
                                        </td>
                                </tr>
                                <tr valign="top" class="hm_sbp_custom_time">
                                        <th scope="row">
                                                <label for="hm_sbp_field_report_end">End Date:</label>
                                        </th>
                                        <td>
                                                <input type="date" name="report_end" id="hm_sbp_field_report_end" value="'.$reportSettings['report_end'].'" />
                                        </td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row">
                                                <label>Incluir os Seguintes Estados:</label>
                                        </th>
                                        <td>');
foreach (wc_get_order_statuses() as $status => $statusName) {
        echo('<label><input type="checkbox" name="order_statuses[]"'.(in_array($status, $reportSettings['order_statuses']) ? ' checked="checked"' : '').' value="'.$status.'" /> '.$statusName.'</label><br />');
}
                        echo('</td>
                                </tr>
                                <tr valign="top" style="display: none;">
                                        <th scope="row">
                                                <label>Include Products:</label>
                                        </th>
                                        <td>
                                                <label><input type="radio" name="products" value="all"'.($reportSettings['products'] == 'all' ? ' checked="checked"' : '').' /> All products</label><br />
                                                <label><input type="radio" name="products" value="cats"'.($reportSettings['products'] == 'cats' ? ' checked="checked"' : '').' /> Products in categories:</label><br />
                                                <div style="padding-left: 20px; width: 300px; max-height: 200px; overflow-y: auto;">
                                        ');
foreach (get_terms('product_cat', array('hierarchical' => false)) as $term) {
        echo('<label><input type="checkbox" name="product_cats[]"'.(in_array($term->term_id, $reportSettings['product_cats']) ? ' checked="checked"' : '').' value="'.$term->term_id.'" /> '.htmlspecialchars($term->name).'</label><br />');
}
                        echo('
                                                </div>
                                                <label><input type="radio" name="products" value="ids"'.($reportSettings['products'] == 'ids' ? ' checked="checked"' : '').' /> Product ID(s):</label>
                                                <input type="text" name="product_ids" style="width: 400px;" placeholder="Use commas to separate multiple product IDs" value="'.htmlspecialchars($reportSettings['product_ids']).'" /><br />
                                        </td>
                                </tr>
                                <tr valign="top" style="display: none;">
                                        <th scope="row">
                                                <label>Product Variations:</label>
                                        </th>
                                        <td>
                                                <label>
                                                        <input type="radio" name="variations" value="0"'.(empty($reportSettings['variations']) ? ' checked="checked"' : '').' class="variations-fld" />
                                                        Group product variations together
                                                </label><br />
                                                <label>
                                                        <input type="radio" name="variations" value="1" disabled="disabled" class="variations-fld" />
                                                        Report on each variation separately<sup style="color: #f00;">PRO</sup>
                                                </label>
                                        </td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row">
                                                <label for="hm_sbp_field_orderby">Ordenar por:</label>
                                        </th>
                                        <td>
                                                <select name="orderby" id="hm_sbp_field_orderby">
                                                        <option value="product_id"'.($reportSettings['orderby'] == 'product_id' ? ' selected="selected"' : '').'>ID do Produto</option>
                                                        <option value="quantity"'.($reportSettings['orderby'] == 'quantity' ? ' selected="selected"' : '').'>Quantidades</option>
                                                        <option value="gross"'.($reportSettings['orderby'] == 'gross' ? ' selected="selected"' : '').'>Vendas Brutas</option>
                                                        <option value="gross_after_discount"'.($reportSettings['orderby'] == 'gross_after_discount' ? ' selected="selected"' : '').'>Venda Após Descontos</option>
                                                </select>
                                                <select name="orderdir">
                                                        <option value="asc"'.($reportSettings['orderdir'] == 'asc' ? ' selected="selected"' : '').'>ascendente</option>
                                                        <option value="desc"'.($reportSettings['orderdir'] == 'desc' ? ' selected="selected"' : '').'>descendente</option>
                                                </select>
                                        </td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row">
                                                <label>Campos a Incluir:</label>
                                        </th>
                                        <td id="hm_psr_report_field_selection">');
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
                                <tr valign="top" style="display: none;">
                                        <th scope="row" colspan="2" class="th-full">
                                                <label>
                                                        <input type="checkbox" name="exclude_free"'.(empty($reportSettings['exclude_free']) ? '' : ' checked="checked"').' />
                                                        Exclude free products
                                                </label>
                                                <p class="description">If checked, order line items with a total amount of zero (after discounts) will be excluded from the report calculations.</p>
                                        </th>
                                </tr>
                                <tr valign="top" style="display: none;">
                                        <th scope="row" colspan="2" class="th-full">
                                                <label>
                                                        <input type="checkbox" name="limit_on"'.(empty($reportSettings['limit_on']) ? '' : ' checked="checked"').' />
                                                        Show only the first
                                                        <input type="number" name="limit" value="'.$reportSettings['limit'].'" min="0" step="1" class="small-text" />
                                                        products
                                                </label>
                                        </th>
                                </tr>
                                <tr valign="top" style="display: none;">
                                        <th scope="row" colspan="2" class="th-full">
                                                <label>
                                                        <input type="checkbox" name="include_header"'.(empty($reportSettings['include_header']) ? '' : ' checked="checked"').' />
                                                        Include header row
                                                </label>
                                        </th>
                                </tr>
                        </table>');
                       
                        echo('<nav class="navbar fixed-bottom navbar-light bg-light form-inline">
	<ul class="navbar-nav ml-auto">
        	<li class="nav-item">
                                <button type="submit" class="btn btn-success" onclick="jQuery(this).closest(\'form\').attr(\'target\', \'\'); return true;">Ver o Relatório</button></li>
                                <li class="nav-item"><button type="submit" class="btn btn-success" name="hm_sbp_download" value="1" onclick="jQuery(this).closest(\'form\').attr(\'target\', \'_blank\'); return true;">Transferir em CSV</button></li></ul>
                        </nav>
                </form>
               
                </div> <!-- /post-body-content -->
               
                <div id="postbox-container-1" class="postbox-container">

                </div><!-- /postbox-container-1 -->
               
                </div> <!-- /post-body -->
                <br class="clear" />
                </div> <!-- /poststuff -->
               
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
               
                $potent_slug = 'product-sales-report-for-woocommerce';
  
               
echo('
        </div>
       
        <script type="text/javascript" src="'.plugins_url('js/hm-product-sales-report.js', __FILE__).'"></script>
');
?>
<script>

// Wordpress button style override
var element = document.getElementById("submit"); 
element.classList.remove("button","button-primary");
element.classList.add("btn","btn-success");


</script>