<?php

?>

	<div class="row">
	
	
	<fieldset class="col-md-7 form-group">
    		
    		<?php 
    		// Actions TinyMCE editor and replaces textarea for it
    		$settings = array(
    			'textarea_name' => 'wc-gdpr-aan[wc_gdpr_aan_message]',
		    	'textarea_rows' => 22,
		    	'tabindex' => 1
		);
    		
    		wp_editor($wc_gdpr_aan_message, $this->plugin_name . '-wc_gdpr_aan_message', $settings); 
    		?>
	        <legend class="screen-reader-text">
	            <span><?php _e( 'Example Text', $this->plugin_name ); ?></span>
	        </legend>
	        <!-- <textarea class="wc_gdpr_aan_message form-control" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_message" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_message]"><?php echo $wc_gdpr_aan_message; ?>
	        </textarea> -->
	       	        

	</fieldset>
	
	<fieldset class="col-md-5" style="border-left: solid 1px #ccc;">
	        
	<div class="form-group">
		<h5>Página da Loja</h5>
	<div class="row">
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb1">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb1" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb1]" value="1" <?php checked( $wc_gdpr_aan_cb1, 1 ); ?> />
	            <span><?php esc_attr_e('Antes do conteúdo principal', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb2">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb2" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb2]" value="1" <?php checked( $wc_gdpr_aan_cb2, 1 ); ?> />
	            <span><?php esc_attr_e('Abaixo do título da página', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb3">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb3" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb3]" value="1" <?php checked( $wc_gdpr_aan_cb3, 1 ); ?> />
	            <span><?php esc_attr_e('Em cada produto, antes da foto', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb4">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb4" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb4]" value="1" <?php checked( $wc_gdpr_aan_cb4, 1 ); ?> />
	            <span><?php esc_attr_e('Em cada produto, após preço', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb5">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb5" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb5]" value="1" <?php checked( $wc_gdpr_aan_cb5, 1 ); ?> />
	            <span><?php esc_attr_e('Logo após a lista de produtos', $this->plugin_name); ?></span>
	        </label>
	        </div>
  
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb6">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb6" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb6]" value="1" <?php checked( $wc_gdpr_aan_cb6, 1 ); ?> />
	            <span><?php esc_attr_e('Após todo o conteúdo principal', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	</div>
	<div style="height: 20px;"></div>
	<div class="form-group">
	<h5>Página do Carrinho</h5>
	<div class="row">
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb7">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb7" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb7]" value="1" <?php checked( $wc_gdpr_aan_cb7, 1 ); ?> />
	            <span><?php esc_attr_e('Antes de itens do carrinho', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb8">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb8" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb8]" value="1" <?php checked( $wc_gdpr_aan_cb8, 1 ); ?> />
	            <span><?php esc_attr_e('Após itens do carrinho', $this->plugin_name); ?></span>
	        </label>
	        </div>

	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb9">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb9" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb9]" value="1" <?php checked( $wc_gdpr_aan_cb9, 1 ); ?> />
	            <span><?php esc_attr_e('Antes da tabela de totais', $this->plugin_name); ?></span>
	        </label>
	        </div>

	</div>
	<div class="col">
		<div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb10">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb10" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb10]" value="1" <?php checked( $wc_gdpr_aan_cb10, 1 ); ?> />
	            <span><?php esc_attr_e('Depois da tabela de totais', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb11">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb11" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb11]" value="1" <?php checked( $wc_gdpr_aan_cb11, 1 ); ?> />
	            <span><?php esc_attr_e('Após botão de finalizar compra', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb12">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb12" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb12]" value="1" <?php checked( $wc_gdpr_aan_cb12, 1 ); ?> />
	            <span><?php esc_attr_e('No rodapé da página', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	</div>
		<div style="height: 20px;"></div>
		<div class="form-group">
		<h5>Página de Checkout</h5>
	<div class="row">
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb13">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb13" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb13]" value="1" <?php checked( $wc_gdpr_aan_cb13, 1 ); ?> />
	            <span><?php esc_attr_e('Antes do cupão', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb14">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb14" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb14]" value="1" <?php checked( $wc_gdpr_aan_cb14, 1 ); ?> />
	            <span><?php esc_attr_e('Antes do título da página', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb15">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb15" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb15]" value="1" <?php checked( $wc_gdpr_aan_cb15, 1 ); ?> />
	            <span><?php esc_attr_e('No início do formulário', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb16">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb16" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb16]" value="1" <?php checked( $wc_gdpr_aan_cb16, 1 ); ?> />
	            <span><?php esc_attr_e('Após o formulário', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb17">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb17" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb17]" value="1" <?php checked( $wc_gdpr_aan_cb17, 1 ); ?> />
	            <span><?php esc_attr_e('Antes de detalhes da encomenda', $this->plugin_name); ?></span>
	        </label>
	        </div>
  
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb18">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb18" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb18]" value="1" <?php checked( $wc_gdpr_aan_cb18, 1 ); ?> />
	            <span><?php esc_attr_e('Após detalhes da encomenda', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	</div>
	
	<div style="height: 20px;"></div>
		<div class="form-group">
		<h5>Página da Minha Conta</h5>
	<div class="row">
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb19">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb19" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb19]" value="1" <?php checked( $wc_gdpr_aan_cb19, 1 ); ?> />
	            <span><?php esc_attr_e('Antes de campos de registo e login', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb20">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb20" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb20]" value="1" <?php checked( $wc_gdpr_aan_cb20, 1 ); ?> />
	            <span><?php esc_attr_e('No início do formulário de registo', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb21">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb21" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb21]" value="1" <?php checked( $wc_gdpr_aan_cb21, 1 ); ?> />
	            <span><?php esc_attr_e('No início do formulário de login', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb22">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb22" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb22]" value="1" <?php checked( $wc_gdpr_aan_cb22, 1 ); ?> />
	            <span><?php esc_attr_e('Após formulário de login', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb23">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb23" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb23]" value="1" <?php checked( $wc_gdpr_aan_cb23, 1 ); ?> />
	            <span><?php esc_attr_e('Abaixo do menu de navegação', $this->plugin_name); ?></span>
	        </label>
	        </div>
  
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb24">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cb24" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cb24]" value="1" <?php checked( $wc_gdpr_aan_cb24, 1 ); ?> />
	            <span><?php esc_attr_e('Após formulário de moradas', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	</div>

    	</fieldset>
    	</div>
    	<div style="height: 20px;"></div>
    	
    	
    	<div class="row">
	<div class="col-md-12">

	<h4 class="display-4 align-text-bottom" style="font-size: 1.6em;">Outras considerações e alertas ao usuário</h4>
	
	</div>
	</div>
	<div style="height: 20px;"></div>
    	<div class="row">
	
		
	
	
	<fieldset class="col-md-7 form-group">
    		
    		<?php 
    		// Actions TinyMCE editor and replaces textarea for it
    		$settings = array(
    			'textarea_name' => 'wc-gdpr-aan[wc_gdpr_aan_messagea]',
		    	'textarea_rows' => 22,
		    	'tabindex' => 1
		);
    		
    		wp_editor($wc_gdpr_aan_messagea, $this->plugin_name . '-wc_gdpr_aan_messagea', $settings); 
    		?>
	        <legend class="screen-reader-text">
	            <span><?php _e( 'Example Text', $this->plugin_name ); ?></span>
	        </legend>
	        <!-- <textarea class="wc_gdpr_aan_messagea form-control" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_messagea" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_messagea]"><?php echo $wc_gdpr_aan_messagea; ?>
	        </textarea> -->
	       	        

	</fieldset>
	
	<fieldset class="col-md-5" style="border-left: solid 1px #ccc;">
	        
	<div class="form-group">
		<h5>Página da Loja</h5>
	<div class="row">
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba1">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba1" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba1]" value="1" <?php checked( $wc_gdpr_aan_cba1, 1 ); ?> />
	            <span><?php esc_attr_e('Antes do conteúdo principal', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba2">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba2" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba2]" value="1" <?php checked( $wc_gdpr_aan_cba2, 1 ); ?> />
	            <span><?php esc_attr_e('Abaixo do título da página', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba3">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba3" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba3]" value="1" <?php checked( $wc_gdpr_aan_cba3, 1 ); ?> />
	            <span><?php esc_attr_e('Em cada produto, antes da foto', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba4">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba4" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba4]" value="1" <?php checked( $wc_gdpr_aan_cba4, 1 ); ?> />
	            <span><?php esc_attr_e('Em cada produto, após preço', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba5">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba5" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba5]" value="1" <?php checked( $wc_gdpr_aan_cba5, 1 ); ?> />
	            <span><?php esc_attr_e('Logo após a lista de produtos', $this->plugin_name); ?></span>
	        </label>
	        </div>
  
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba6">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba6" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba6]" value="1" <?php checked( $wc_gdpr_aan_cba6, 1 ); ?> />
	            <span><?php esc_attr_e('Após todo o conteúdo principal', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	</div>
	<div style="height: 20px;"></div>
	<div class="form-group">
	<h5>Página do Carrinho</h5>
	<div class="row">
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba7">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba7" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba7]" value="1" <?php checked( $wc_gdpr_aan_cba7, 1 ); ?> />
	            <span><?php esc_attr_e('Antes de itens do carrinho', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba8">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba8" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba8]" value="1" <?php checked( $wc_gdpr_aan_cba8, 1 ); ?> />
	            <span><?php esc_attr_e('Após itens do carrinho', $this->plugin_name); ?></span>
	        </label>
	        </div>

	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba9">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba9" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba9]" value="1" <?php checked( $wc_gdpr_aan_cba9, 1 ); ?> />
	            <span><?php esc_attr_e('Antes da tabela de totais', $this->plugin_name); ?></span>
	        </label>
	        </div>

	</div>
	<div class="col">
		<div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba10">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba10" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba10]" value="1" <?php checked( $wc_gdpr_aan_cba10, 1 ); ?> />
	            <span><?php esc_attr_e('Depois da tabela de totais', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba11">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba11" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba11]" value="1" <?php checked( $wc_gdpr_aan_cba11, 1 ); ?> />
	            <span><?php esc_attr_e('Após botão de finalizar compra', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba12">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba12" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba12]" value="1" <?php checked( $wc_gdpr_aan_cba12, 1 ); ?> />
	            <span><?php esc_attr_e('No rodapé da página', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	</div>
		<div style="height: 20px;"></div>
		<div class="form-group">
		<h5>Página de Checkout</h5>
	<div class="row">
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba13">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba13" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba13]" value="1" <?php checked( $wc_gdpr_aan_cba13, 1 ); ?> />
	            <span><?php esc_attr_e('Antes do cupão', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba14">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba14" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba14]" value="1" <?php checked( $wc_gdpr_aan_cba14, 1 ); ?> />
	            <span><?php esc_attr_e('Antes do título da página', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba15">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba15" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba15]" value="1" <?php checked( $wc_gdpr_aan_cba15, 1 ); ?> />
	            <span><?php esc_attr_e('No início do formulário', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba16">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba16" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba16]" value="1" <?php checked( $wc_gdpr_aan_cba16, 1 ); ?> />
	            <span><?php esc_attr_e('Após o formulário', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba17">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba17" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba17]" value="1" <?php checked( $wc_gdpr_aan_cba17, 1 ); ?> />
	            <span><?php esc_attr_e('Antes de detalhes da encomenda', $this->plugin_name); ?></span>
	        </label>
	        </div>
  
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba18">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba18" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba18]" value="1" <?php checked( $wc_gdpr_aan_cba18, 1 ); ?> />
	            <span><?php esc_attr_e('Após detalhes da encomenda', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	</div>
	
	<div style="height: 20px;"></div>
		<div class="form-group">
		<h5>Página da Minha Conta</h5>
	<div class="row">
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba19">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba19" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba19]" value="1" <?php checked( $wc_gdpr_aan_cba19, 1 ); ?> />
	            <span><?php esc_attr_e('Antes de campos de registo e login', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba20">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba20" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba20]" value="1" <?php checked( $wc_gdpr_aan_cba20, 1 ); ?> />
	            <span><?php esc_attr_e('No início do formulário de registo', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba21">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba21" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba21]" value="1" <?php checked( $wc_gdpr_aan_cba21, 1 ); ?> />
	            <span><?php esc_attr_e('No início do formulário de login', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	<div class="col">
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba22">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba22" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba22]" value="1" <?php checked( $wc_gdpr_aan_cba22, 1 ); ?> />
	            <span><?php esc_attr_e('Após formulário de login', $this->plugin_name); ?></span>
	        </label>
	        </div>
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba23">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba23" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba23]" value="1" <?php checked( $wc_gdpr_aan_cba23, 1 ); ?> />
	            <span><?php esc_attr_e('Abaixo do menu de navegação', $this->plugin_name); ?></span>
	        </label>
	        </div>
  
	        <div class="form-check form-control-sm">
	        <label class="form-check-label" for="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba24">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-wc_gdpr_aan_cba24" name="<?php echo $this->plugin_name; ?>[wc_gdpr_aan_cba24]" value="1" <?php checked( $wc_gdpr_aan_cba24, 1 ); ?> />
	            <span><?php esc_attr_e('Após formulário de moradas', $this->plugin_name); ?></span>
	        </label>
	        </div>
	</div>
	</div>

    	</fieldset>
    	
    	

</div>

<div style="height: 100px;"></div>