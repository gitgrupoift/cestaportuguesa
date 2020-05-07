<?php 

namespace Cesta;

class AdminStyle {
    
    public function __construct() {
        
        add_action( 'admin_head', array($this, 'style_wc_additions'));
        
    }
    
    public function style_wc_additions() {
        
        ?>
            <style>
                .woocommerce-feature-enabled-activity-panels .localizacao {
                    display: inline-block;
                    position: relative;
                    text-align: center;
                }
                .woocommerce-feature-enabled-activity-panels .badge {
                    position: absolute;
                    text-transform: uppercase;
                    right: 0;
                    bottom: -10px;
                    font-size: 0.95em;
                    width: 27px;
                    color: white;
                    line-height: 27px;
                    height: 27px;
                    border-radius: 50px;
                    background-color: forestgreen;
                    font-weight: bold;
                }
                .woocommerce-feature-enabled-activity-panels .freguesia {
                    font-size: 0.95em;
                    width: 120px;
                    padding: 10px;
                    margin: 5px;
                    border: solid 1px #ddd;
                    background: white;
                }
                
                .wcpdf-extensions-ad { display: none; }
                .wc-action-button-processing, .wc-action-button-shipping_progress, .wc-action-button-at_courier, .wc-action-button-picking_progress, .wc-action-button-shipping_fail, .wc-action-button-completed, .wpo_wcpdf { border: none !important; }
                .widefat .column-wc_actions a.processing::after {
                    content: "\e031";
                    font-size: 16px;
                }
                .widefat .column-wc_actions a.completed::after {
                    font-family: woocommerce !important;
                    content: "\e015";
                    font-size: 16px;
                }
                .widefat .column-wc_actions a.shipping_progress::after {
                    font-family: woocommerce !important;
                    content: "\e029";
                    font-size: 16px;
                }
                .widefat .column-wc_actions a.at_courier::after {
                    font-family: woocommerce !important;
                    content: "\e01a";
                    font-size: 16px;
                }
                .widefat .column-wc_actions a.picking_progress::after {
                    font-family: woocommerce !important;
                    content: "\e006";
                    font-size: 16px;
                }
                .widefat .column-wc_actions a.shipping_fail::after {
                    font-family: woocommerce !important;
                    content: "\e013";
                    font-size: 16px;
                }
                .type-shop_order .column-wc_actions a.button.wpo_wcpdf.exists::after {
                    content: "\f12a";
                    top: -13px;
                    overflow: visible;
                    z-index: 999;
                }
                .widefat .column-wc_actions a.button.wpo_wcpdf {
                    overflow: visible;
                }
                
                mark.status-shipping-progress {
                    background-color: cornflowerblue;
                    color: white;
                }
                mark.status-picking-progress {
                    background-color:darkslategray;
                    color: white;
                }
                mark.status-at-courier {
                    background-color:coral;
                    color: white;
                }
                mark.status-shipping-fail {
                    background-color:crimson;
                    color: white;
                    font-weight: bold;
                }
                
            </style>

        <?php
        
    } 
    
}