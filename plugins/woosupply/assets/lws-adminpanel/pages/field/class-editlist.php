<?php
namespace LWS\Adminpanel\Pages\Field;
if( !defined( 'ABSPATH' ) ) exit();


class Editlist extends \LWS\Adminpanel\Pages\Field
{
	public function input()
	{
		if( isset($this->extra['editlist']) && is_a($this->extra['editlist'], 'LWS\Adminpanel\EditList') )
			$this->extra['editlist']->display();
	}

	public function __construct($id, $title, $extra=null)
	{
		parent::__construct($id, $title, $extra);
		$this->gizmo = true;
	}
}

?>
