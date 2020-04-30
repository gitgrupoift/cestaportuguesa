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
            </style>

        <?php
        
    } 
    
}