<?php 

namespace Cesta\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;

class OneProduct extends \Elementor\Widget_Base {

	public function get_name() {
        return 'one-product';
    }

	public function get_title() {
        return __( 'Um Só Produto', 'cesta-app' );
    }

	public function get_icon() {
        return 'fas fa-dice-one';
    }

	public function get_categories() {
        return [ 'cesta-app' ];
    }

	protected function _register_controls() { 
    }

	protected function render() {
        
        
    }

	protected function _content_template() {}

}