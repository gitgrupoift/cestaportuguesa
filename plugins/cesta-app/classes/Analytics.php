<?php 

namespace Cesta;

class Analytics {
    
    public $analytics;
        
    public function __construct() {
        
        $this->analytics = $analytics;
        add_action('wp_head', array($this, 'analytics_main'));
  
        
    }
    
    public static function get() {
        return $this->analytics;    
    }
    
    public function set( $tracking_code ) {
        $this->analytics = $tracking_code;    
    }
    
    public static function analytics_main() {
        
        ?>
            <!-- Global site tag (gtag.js) - Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $this->analytics; ?>"></script>
            <script>
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag("js", new Date());

              gtag("config", "<?php echo $this->analytics; ?>");
            </script>
        <?php 
                
    }
    
}