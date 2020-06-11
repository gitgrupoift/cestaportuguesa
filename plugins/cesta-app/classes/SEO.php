<?php 

namespace Cesta;

class SEO {
        
    public function __construct() {
        
        add_action('wp_head', array($this, 'meta'));
        add_action('init', array($this, 'tags_support_all'));
        add_action('pre_get_posts', array($this, 'tags_support_query'));
        
    }
    
    public function meta() {
        
        echo '<meta name="keywords" content="frutas,legumes,frutas ao domicílio,cabazes,cabazes ao domicílio,frutas viana do castelo,frutas braga,legumes braga,legumes viana do castelo,produtos biológicos,cabazes biológicos,cabazes famalicão,cabazes barcelos"/>';
        echo '<link rel="alternate" href="https://www.cestaportuguesa.pt/" 
  hreflang="pt" /><link rel="alternate" href="https://www.cestaportuguesa.pt/" 
  hreflang="pt-br" />';
    }
    
    public function tags_support_all() {
        register_taxonomy_for_object_type('post_tag', 'page');
    }
    
    public function tags_support_query($wp_query) {
        if ($wp_query->get('tag')) $wp_query->set('post_type', 'any');
    }
    
}