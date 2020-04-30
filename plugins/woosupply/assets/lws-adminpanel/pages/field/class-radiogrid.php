<?php
namespace LWS\Adminpanel\Pages\Field;

if (!defined('ABSPATH')) {
    exit();
}


/** Designed to be used inside Wizard only.
 * Behavior is similar to a radio,
 * But choices looks like tiles with a grid layout. */
class RadioGrid extends \LWS\Adminpanel\Pages\Field
{
    public function input()
    {
        $name = \esc_attr($this->id());
        $value = $this->readOption(true);
        $type= (null !== $this->getExtraValue('type')) ? $this->getExtraValue('type') : '';

        if ($type == 'large') {
            echo "<input id='{$name}' name='{$name}' value='{$value}' type='hidden'>";
            foreach ($this->getExtraValue('source', array()) as $opt) {
                $v = isset($opt['value']) ? esc_attr($opt['value']) : '';
                $img = isset($opt['img']) ? esc_attr($opt['img']) : '';
                $selected = ($v == $value ? ' selected' : '');
                $texts = isset($opt['texts']) ? $opt['texts'] : array();
                $labels = isset($opt['labels']) ? $opt['labels'] : array();
                $stars = isset($opt['stars']) ? esc_attr($opt['stars']) : '';
                $time = isset($opt['time']) ? esc_attr($opt['time']) : '';
                $proonly  = isset($opt['pro-only']) ? boolval($opt['pro-only']) : false;

                if ($proonly) {
                    $bottom = <<<EOT
					<div class="lws-wizard-large-pro-only">{$labels['pro']}</div>
EOT;
                } else {
                    $bottom = <<<EOT
					<div class="lws-wizard-large-diff-title">{$labels['diff']}</div>
					<div class="lws-wizard-large-time-title">{$labels['time']}</div>
					<div class="lws-wizard-large-diff-grid">
						<div class="lws-wizard-large-diff-text">{$texts['diff']}</div>
						<div class="lws-wizard-large-diff-stars"><img src='{$stars}' /></div>
					</div>
					<div class="lws-wizard-large-time-grid">
						<div class="lws-wizard-large-time-number">{$time}</div>
						<div class="lws-wizard-large-time-unit">{$labels['unit']}</div>
					</div>
EOT;
                }

                echo <<<EOT
<div class="lws-wizard-large-container lws_wizard_radio{$selected}" data-input='#{$name}' data-value='{$v}'>
	<div class="lws-wizard-large-image"><img src='{$img}'/></div>
	<div class="lws-wizard-large-desc-grid">
		<div class="lws-wizard-large-title">{$texts['title']}</div>
		<div class="lws-wizard-large-desc">{$texts['descr']}</div>
		{$bottom}
	</div>
</div>
EOT;
            }

        } else {
            echo "<input id='{$name}' name='{$name}' value='{$value}' type='hidden'>";
            foreach ($this->getExtraValue('source', array()) as $opt) {
                $v = isset($opt['value']) ? esc_attr($opt['value']) : '';
                $extraclass = isset($opt['class']) ? esc_attr($opt['class']) : '';
                $i = isset($opt['icon']) ? esc_attr($opt['icon']) : '';
                $selected = ($v == $value ? ' selected' : '');
                if (!empty($i)) {
                    echo <<<EOT
					<div class='lws-wizard-grid-button {$extraclass} lws_wizard_radio{$selected} ' data-input='#{$name}' data-value='{$v}'>
						<div class='lws-wizard-grid-button-icon {$i}'></div>
						<div class='lws-wizard-grid-button-label'>{$opt['label']}</div>
					</div>
EOT;
                } else {
                    echo <<<EOT
						<div class='lws-wizard-grid-button no-icon {$extraclass} lws_wizard_radio{$selected} ' data-input='#{$name}' data-value='{$v}'>
							<div class='lws-wizard-grid-button-label'>{$opt['label']}</div>
						</div>
EOT;
                }
            }
        }
    }
}
