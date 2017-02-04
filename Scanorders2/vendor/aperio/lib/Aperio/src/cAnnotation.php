<?

class cAnnotation
{
	public $Id			= -1;
	public $Attributes 	= array();
}

class cAttribute
{
	public $Name = "";
	public $Value = "";
	
	// constructor
	function cAttribute($Name, $Value)
	{
		$this->Name = $Name;
		$this->Value = $Value;
	}
}
?>