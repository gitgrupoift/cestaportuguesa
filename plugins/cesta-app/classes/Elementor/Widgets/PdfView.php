<?php 

namespace Cesta\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class PdfView extends \Elementor\Widget_Base {

	public function get_name() {
        return 'pdf-view';
    }

	public function get_title() {
        return __( 'Visualizador PDF', 'cesta-app' );
    }

	public function get_icon() {
        return 'far fa-file-pdf';
    }

	public function get_categories() {
        return [ 'cesta-app' ];
    }

	protected function _register_controls() {
        
            $this->start_controls_section(
                        'pdf_viewer_docs',
                        [
                                'label' => __( 'Visualizador PDF', 'cesta-app' ),
                        ]
                );
                $this->add_control(
                        'pdf_type',
                        [
                                'label' => __( 'Tipo de PDF', 'cesta-app' ),
                                'type' => \Elementor\Controls_Manager::SELECT,
                                'default' => 'url',
                                'options' => [
                                        'url'  => __( 'URL', 'cesta-app' ),
                                        'file' => __( 'Ficheiro', 'cesta-app' ),
                                ],
                        ]
                );
               
                $this->add_control(
                        'pdf_url',
                        [
                                'label' => __( 'PDF URL', 'cesta-app' ),
                                'type' => \Elementor\Controls_Manager::URL,
                                'placeholder' => __( 'http://www.pdf995.com/samples/pdf.pdf', 'cesta-app' ),
                                'show_external' => true,
                                'default' => [
                                        'url' => 'http://www.pdf995.com/samples/pdf.pdf',
                                        'is_external' => true,
                                        'nofollow' => true,
                                ],
                                'dynamic' => [
                                        'active' => true,
                                ],
                                'condition' => [
                                        'pdf_type' => 'url',
                                ]
                        ]
                );
                $this->add_control(
                        'pdf_file',
                        [
                                'label' => __( 'Escolher o PDF', 'cesta-app' ),
                                'type' => \Elementor\Controls_Manager::MEDIA,
                                'media_type' => 'application/pdf',
                                'default' => [
                                        'url' => \Elementor\Utils::get_placeholder_image_src(),
                                ],
                                'dynamic' => [
                                        'active' => true,
                                ],
                                'condition' => [
                                        'pdf_type' => 'file',
                                ],
                        ]
                );
                $this->add_control(
                        'width',
                        [
                                'label' => __( 'Largura', 'cesta-app' ),
                                'type' => Controls_Manager::SLIDER,
                                'size_units' => [ '%', 'px' ],
                                'range' => [
                                        'px' => [
                                                'min' => 0,
                                                'max' => 1500,
                                                'step' => 5,
                                        ],
                                        '%' => [
                                                'min' => 0,
                                                'max' => 100,
                                        ],
                                ],
                                'default' => [
                                        'unit' => 'px',
                                        'size' => 640,
                                ],
                        ]
                );
                $this->add_control(
                        'height',
                        [
                                'label' => __( 'Altura', 'cesta-app' ),
                                'type' => Controls_Manager::SLIDER,
                                'size_units' => [ '%', 'px' ],
                                'range' => [
                                        'px' => [
                                                'min' => 0,
                                                'max' => 1500,
                                                'step' => 5,
                                        ],
                                        '%' => [
                                                'min' => 0,
                                                'max' => 100,
                                        ],
                                ],
                                'default' => [
                                        'unit' => 'px',
                                        'size' => 1020,
                                ],
                        ]
                );
                $this->add_control(
                        'text_align',
                        [
                                'label' => __( 'Alinhamento', 'cesta-app' ),
                                'type' => \Elementor\Controls_Manager::CHOOSE,
                                'options' => [
                                        'left' => [
                                                'title' => __( 'Esquerda', 'cesta-app' ),
                                                'icon' => 'fa fa-align-left',
                                        ],
                                        'center' => [
                                                'title' => __( 'Centro', 'cesta-app' ),
                                                'icon' => 'fa fa-align-center',
                                        ],
                                        'right' => [
                                                'title' => __( 'Direita', 'cesta-app' ),
                                                'icon' => 'fa fa-align-right',
                                        ],
                                ],
                                'default' => 'center',
                                'toggle' => true,
                        ]
                );
               
               
                $this->end_controls_section();
        
    }

	protected function render() {
        
        $settings = $this->get_settings_for_display();
                
        $align = 'display: block; margin-left: auto; margin-right: auto;';
               
                
        if ($settings['text_align'] === 'left') {
                        
            $align = 'display: block; float: left;';
                
        }
                
        if ($settings['text_align'] === 'right') {
                        
            $align = 'display: block; float: right;';
                
        }
                
        if (isset($settings['width'])) {
                        
            $width = ' width: ' . $settings['width']['size'] . $settings['width']['unit'] . ';';
                
        }
                
        if (isset($settings['height'])) {
                        
            $height = ' height: ' . $settings['height']['size'] . $settings['height']['unit'] . ';';
                
        }
                
        if ($settings['pdf_type'] == 'url' AND isset($settings['pdf_url'])) {
                        
            $pdf_url = $settings['pdf_url']['url'];
                
        }
                
        if ($settings['pdf_type'] == 'file' AND isset($settings['pdf_file']['url'])) {
                        
            $pdf_url = $settings['pdf_file']['url'];
                
        }
                
        echo '<iframe src="https://docs.google.com/viewer?url=' . $pdf_url . '&amp;embedded=true" style="' . $align . $width . $height . '" frameborder="1" marginheight="0px" marginwidth="0px" allowfullscreen></iframe>';       
        
        
    }

	protected function _content_template() {}

}