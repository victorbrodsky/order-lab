<?
/**
* These classes encapsulate the data types available as members in the cTableSchema objects
* 
* cField objects manage display, input, and validation of their respective fields in
* the database.  They do not, however, actually contain the data itself.
* 
* @package Tables
* 
* -04/14/08 msmaga	added cScore object05

* -04/22/08 msmaga	added cStain object
* -04/26/08 msmaga	added cTissue object
* -05/15/08 msmaga	user-defined fields
* -05/16/08 msmaga	changed cTissue to cBodySite to reflect new schema
* -05/30/08 thoare	added field-by-field auto-updating
* -06/11/08 msmaga	removed 'dropdown' & 'help' from cSsField
* -06/18/08 msmaga	added cCommentField object
* -06/25/08 thoare	Added 'Save All' buttons to the EditRecord page
* -06/26/08 msmaga	removed cScoreField, cScoreSet & cInterpretationField
* -08/27/08 msmaga	rename SsFieldConfig.ColumnName -> SsFieldConfig.FieldName
* -08/28/08 thoare	Added error recognition when displaying editable fields (Invalid entries appear red)
* -080911	vunger	Remove 2 unused classes.
* -09/26/08 msmaga	Field values saved until cTable write confirmed
* -12/11/08 rellis	add cFileNameField
* 090112	vunger	Added cIdentityLinkField
* 090113	vunger	Added cSSPConfigIdField
* 090224	vunger	Added GetOldValue(), IsSspConfigField()
* 090611	vunger	Added SetState()
* 090624	rellis	Added default values to constructors of cTextField, cFileNameField, cToggleField
* 100726	rellis	modify cTimeZoneField MakeValueLegal to remove GMT+ and leading/trailing zeros from hours
* 100922	rellis	Add length before flags to some TextFields 
* 101020	rellis	Correction to ToHTML to not attempt to call AppendThumbnailHTMLNode for records that are not imagerecords
* 101105	rellis	Add precision to cNumberField
* 110712	pkraft	Added cTextLinkField, cLogoField
* 030812    leonid  Updated cDataGroupField to use AccessLevelsEffective instead of AccessLevels
*
*/

/**
* cField is the root class for all Field representations.
* 
* No objects of this class should ever be created in the final code.
* 
* @package Tables
* 
* -05/30/08 thoare	added field-by-field auto-updating
* -09/02/08 msmaga	user-defined field prefix changed from 'UDF' to 'Column';  image fields added
*/

include_once '/Utils/Display.php';
include_once '/cTable.php';

// $FieldDateDisplayTypes array is keyed by the concatenation of TableName and ColumnName
static $FieldDateDisplayTypes = array(
	"Case.PatientDOB" 			 => cField::DisplayDate, 	 
	"Case.DateReported" 		 => cField::DisplayDate, 	 
	"Case.OutboundSharedDate" 	 => cField::DisplayDate, 	 
	"Case.InboundSharedDate" 	 => cField::DisplayDate, 	 
	"Case.Rv" 					 => cField::DisplayDateTime, 
	"Case.DownToSpecimenByParentId.CollectedDate" => cField::DisplayDateTime,
	"Case.DownToSpecimenByParentId.ReceivedDate" => cField::DisplayDateTime,
	"Case.DownToSpecimenByParentId.ReleasedDate" => cField::DisplayDateTime,
	"Case.DownToVwCaseAggregatesByCaseId.SpecimenReceivedDate"	 => cField::DisplayDateTime,
	"Case.DownToVwCaseAggregatesByCaseId.CollectedDate" => cField::DisplayDateTime,
	"Case.DownToVwCaseAggregatesByCaseId.ReleasedDate" => cField::DisplayDateTime,
	"Case.DownToCaseReviewResultByCaseId.ReviewedOn" => cField::DisplayDateTime,
	"Case.DownToCaseReviewResultByCaseId.CreatedOn" => cField::DisplayDateTime,
	"Course.OutboundSharedDate"  => cField::DisplayDate, 	 
	"Course.InboundSharedDate" 	 => cField::DisplayDate, 	 
	"Image.ScanDate" 			 => cField::DisplayDateTime, 
	"Project.StartDate" 		 => cField::DisplayDate, 	 
	"Project.CompletionDate" 	 => cField::DisplayDate, 	 
	"Project.OutboundSharedDate" => cField::DisplayDate, 	 
	"Project.InboundSharedDate"  => cField::DisplayDate, 	 
	"Reports.CreatedDate" 		 => cField::DisplayDate, 
	"Specimen.CollectedDate" 	 => cField::DisplayDateTime, 	 
	"Specimen.ReceivedDate" 	 => cField::DisplayDateTime, 	 
	"Specimen.ReleasedDate" 	 => cField::DisplayDateTime, 
	"TMA.CreatedDate" 			 => cField::DisplayDate, 
	"Users.LastLoginTime" 		 => cField::DisplayDate, 
)
;

abstract class cField
{
	/**#@+
	* Type flags for cFields
	* 
	* - FLAGS_DEFAULT: No flags set
	* - FLAGS_VISIBLE: Field will appear in the list and edit pages
	* - FLAGS_READONLY: The user may not edit the contents of the field
	* - FLAGS_HIDDEN: The field is never visible to the user
	* - FLAGS_USER_DEFINED: The field is user-defined
	* - FLAGS_VIRTUAL: The field is not in the database
	* - FLAGS_NO_VOCABULARY: The field is not allowed to have a vocabulary defined for it
	* - FLAGS_SAVED_ENCODED: The field's value should be saved xmlencoded
	* - FLAGS_NOT_NULL: The field cannot be left empty
	* - FLAGS_CRITICAL: The field will always be loaded from the database as part of the record
	* - FLAGS_CRITICAL2: The field will always be both saved & loaded to/from the database as part of the record
	* - FLAGS_NO_LINK: used with identify fields that should not be hyperlinks in lists  
	* @var int
	*/
	const FLAGS_DEFAULT			= 0x000;
	const FLAGS_VISIBLE			= 0x001;
	const FLAGS_NO_COMBINE		= 0x002;
	const FLAGS_READONLY		= 0x004;
	const FLAGS_HIDDEN			= 0x008;
	const FLAGS_USER_DEFINED 	= 0x010;
	const FLAGS_VIRTUAL			= 0x020;
	const FLAGS_NO_VOCABULARY	= 0x040;
	const FLAGS_SAVED_ENCODED	= 0x080;
	const FLAGS_NOT_NULL		= 0x100;
	const FLAGS_CRITICAL		= 0x200;
	const FLAGS_NO_ACCESS_AUDIT	= 0x400;
	const FLAGS_NO_LINK         = 0x800;
	const FLAGS_CRITICAL2		= 0x1000;

	/*
	* constants for date fields
	*/ 
	const DisplayDate 		= 0;  	// display date only
	const DisplayDateTime 	= 1;    // display date/time
	const DisplayTime 		= 2;	// display time only

	/**#@-*/
	/*
	 * When true, generates a unique identifer using uniqid
	 * All html identifiers must be unique and not contain any special characters
	 */
	const GENERATE_UNIQUE_IDENTIFIER = true;

	public $TableName = '';
	public $ColumnName = '';
	public $RefTableName = '';
	public $RefColumnName = '';
	public $CriticalViewColumn = NULL;    
	public $PrimaryKey = NULL;
	public $DisplayName = '';
	public $Default = '';
	public $Description = '';
	public $Flags = cField::FLAGS_DEFAULT;
	public $MaxLength = 255;
	public $Visible = false;	// deprecated
	protected	$IsVisible = false;
	public	$IsCritical = false;
	private	$IsCritical2 = false;
	public	$Sortable = true;
	public	$Searchable = true;			// ability to search
	protected	$FolderSearchable = true;	// ability to search on the FolderList page
	protected	$AjaxDropDown = true;		// AJAX drop-down menu
	public $ValidFilters = array(
		'-- All --' 	=> 	'All',
		'equal to'		=>	'=',
		'not equal to' 	=>	'<>',
		'less than'		=>	'<',
		'greater than'	=>	'>',
		'contains'		=>	'LIKE',
	   // 'does not contain'     =>     'NOTLIKE', // disabled from UI filters list in release 12.0 per Debra
		'between'		=>	'Range',
		'includes'		=>	'in');
	public $Grouped = true;
	public $FieldType = 'Text';
	public $IsIdentity = false;
	public $IsNeeded = false;			// Needed from the database for every access
	public $ReadOnly = false;
	public $Hidden = false;
	public $IsHidden = false;
	public $IsExportable = true;        // allow data in this field to be exported 
	public $UserDefined = false;		// User-Defined Field (except for Image table)
	public $IsImageField = false;		// Image Table Field
	public $DisplayOnlyInList = false;

	// XXX These filters should get obsoleted by cSearcher
	public $FilterOperator = 'All';
	public $FilterValue = '';
	public $FilterName = '';			// Name (from lookup of $FilterValue)
	public $FilterValue2 = '';

	public $AuditAccessAllowed = true;	// This field is access auditable
	public $AuditAccess = false;		// User has designated this field to be access audited
	public $Vocabulary = array();
	public $HasVocabulary = true;
	public $Position = 0;
	public $AlwaysDisplay = false;
	public $OnKeyUp = '';				// used for defining javascript to do ajax suggestive search
	public $FormatHint = '';			// Hint given to the user about how to format data
	public	$RoleAccess = 'N';			// Role based access
	public $EsigItem = false;			// True if item subject to Electronic Signature on change
	public $EsigRequired = false;		// True if user requires Electronic Signature to change.
	public $SavedEncoded = false;		// True if value should be saved xmlencoded ('&' = '&amp;' etc.)
	public $NotNull = false;			// True if the field cannot be empty
	public $AutoUpdate = false;			// True if the field updates itself every 2 seconds
	public $IsVirtual = false;			// Special fields that have no real database component
	public $IsSSP = false;				// Special fields for Slide Specific Processing
	public $IsDatabaseField = true;		// Field is in the database table
	public $IsStatic = false;			// Can field's attributes be modified (Data Tables page)?
	public $CombineValues = true;		// When combining multiple fields display one value (display '[various]' if values differ)
	public $Class = 'editRecord';		// Default html class for field
	public $DisplayWidth = NULL;		// Width of column in cList display
	private	$Attributes = array();

	private $FieldId = 0;
	
	/**
	* Next available Field Id value
	* @var int
	*/
	private static $NextFieldId		= 0;
	/**
	* Array of FieldId (int) => cField
	* @var array
	*/
	private static $FieldsById		= array ();
	/**
	* Array of TableName.ColumnName (string) => cField
	* @var array
	*/
	private static $FieldsByName	= array ();

	/**
	* Retrieve a cField object anywhere in the heirarchy based on an identifier
	* 
	* @param mixed $Identifier - Which field to retrieve, by Id or by TableName.ColumnName, depending on type
	* 
	* @return cField - Requested field or null if no matching field
	*/
	public static function GetField ($Identifier)
	{
		if (isset ($FieldsById [$Identifier]))
			return $FieldsById [$Identifier];
		elseif (isset ($FieldsByName [$Identifier]))
			return $FieldsByName [$Identifier];
		else
			return null;
	}

	public static function FieldCmp (cField $a, cField $b)
	{
		if ($a->Position == 0 && $b->Position == 0)
			return 0;
		elseif ($a->Position == 0)
			return 1;
		elseif ($b->Position == 0)
			return -1;
		elseif ($a->Position == $b->Position)
			return strcmp ($b->TableName, $a->TableName);
		else
			return $a->Position - $b->Position;
	}

	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table the field belongs to
	* @param string $ColumnName - Database name of the column this field represents
	* @param string $DisplayName - User defined display name shown in the UI
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	*/
	// cField
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT, $Default = '')
	{
		$this->TableName = $TableName;
		$this->ColumnName = $ColumnName;
		$this->RefTableName = $TableName;
		$this->RefColumnName = $ColumnName;
		$this->DisplayName = $DisplayName;
		$this->Flags = $Flags;
		$this->Default = $Default;
		$this->FieldPath = $ColumnName;
		$this->FieldPath2 = '';
		$this->SortPath = $ColumnName;

		// These fields may be overridding in cTable::LoadProperties()
		// Hidden implies not visible with no potential for override by DataField settings
		// XXX remove FLAGS_VISIBLE
		//$this->Visible = ($Flags & cField::FLAGS_VISIBLE ? true : false);

		$this->UserDefined = ($Flags & cField::FLAGS_USER_DEFINED ? true : false);
		if ($TableName == 'Image')
			$this->IsImageField = true;
		$this->SavedEncoded = ($Flags & cField::FLAGS_SAVED_ENCODED ? true : false);
		$this->NotNull = ($Flags & cField::FLAGS_NOT_NULL ? true : false);
		//$this->AutoUpdate = ($Flags & cField::FLAGS_AUTO_UPDATE ? true : false); // TH: Not in use yet
		$this->AuditAccessAllowed = ($Flags & cField::FLAGS_NO_ACCESS_AUDIT ? false : true);

		if ($Flags & cField::FLAGS_NO_COMBINE)
			$this->CombineValues = false;

		if ($Flags & cField::FLAGS_NO_VOCABULARY)
		{
			$this->HasVocabulary = false;
			$this->Vocabulary = null;
		}

		// catch unflagged user-defined fields (except for image field User01 .. User10)
		if (!$this->UserDefined)
			if ((strlen ($ColumnName) > 6) && (substr ($ColumnName, 0, 6) == 'Column'))
				$this->UserDefined = true;

		$this->AssignFieldId ();

		$this->SetState();

		// Flag field as modified if it has changed
		$this->AddAttribute ('onchange', 'CheckModified (this);');
		// Save page if user hits CarriageReturn. This should be a function call.
		$this->AddAttribute ('onkeypress', 'ProcessEnterKey(event, this);');
		$this->AddAttribute ('DownToSyntax', $this->FieldPath);
	}

	// cField
	//public function SetFlags($FlagsOn, $FlagsOff)
	public function ChangeFlags($FlagsOn, $FlagsOff)
	{
		$this->Flags |= $FlagsOn;
		$this->Flags &= ~($FlagsOff);
		$this->SetState();
	}


	// cField
	public function SetState()
	{
		// Determine visibility
		$this->IsHidden = ($this->Flags & cField::FLAGS_HIDDEN ? true : false);
		$this->Hidden = $this->IsHidden;
		if ($this->IsHidden)
			$this->Position = 0;
		if (($this->Position == 0) || ($this->RoleAccess == 'N') || ($this->IsHidden))
		{
			$this->IsVisible = false;
		}
		else
		{
			$this->IsVisible = true;
		}
		$this->Visible = $this->IsVisible;

		// Editability
		$this->ReadOnly = ($this->Flags & cField::FLAGS_READONLY ? true : false);
		if (($this->RoleAccess == 'F') && ($this->ReadOnly == false))
		{
			$this->Editable = true;
			$this->ReadOnly = false;
		}
		else
		{
			$this->Editable = false;
			$this->ReadOnly = true;
		}

		// Whether field is needed for the record (DataBase loads)
		$this->IsCritical = false;
		$this->IsCritical2 = false;
		if ($this->Flags & cField::FLAGS_CRITICAL2)
		{
			$this->IsCritical = true;
			$this->IsCritical2 = true;
		}
		else if ($this->Flags & cField::FLAGS_CRITICAL)
			$this->IsCritical = true;
		if ($this->IsDatabaseField && ($this->Visible || $this->IsCritical))
			$this->IsNeeded = true;
		else
			$this->IsNeeded = false;
	}

	// cField
	public function IsVisible($Page='List')
	{
		if ($this->IsVisible == false)
			return false;
		if (($Page != 'List') && ($this->DisplayOnlyInList))
			return false;
		return true;
	}

	// cField
	public function IsCritical()
	{
		if ($this->IsCritical || $this->IsCritical2)
			return true;
		return false;
	}

	// cField
	public function IsCritical2()
	{
		// Field is needed for both loads and saves
		return $this->IsCritical2;
	}

	// cField
	public function SetPosition($Position)
	{
		return $this->Position = $Position;
	}

	// cField
	public function FieldAccess()
	{
		if ($this->RoleAccess == 'N')
			return 'NoAccess';
		if ($this->ReadOnly)
			return 'ReadOnly';
		return 'Full';
	}

	// cField
	final private function AssignFieldId ()
	{
		if (!isset (self::$FieldsByName ["$this->TableName.$this->ColumnName"]))
			$this->FieldId = self::$NextFieldId++;
		else
			$this->FieldId = self::$FieldsByName ["$this->TableName.$this->ColumnName"]->FieldId;
		
		self::$FieldsById [$this->FieldId] = self::$FieldsByName ["$this->TableName.$this->ColumnName"] = $this;
	}

	/**
	* Validates the given value for this field
	*
	* @param string $Value - The value to test
	* @param string $Message - Output parameter to contain specific messages about the value
	* @param bool $RigidCheck - Forces more restrictive checks for what may be placed in the DB (rather than all values could potentially occur)
	* 
	* @return true if the value is valid, false otherwise
	*/
	// cField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if ($this->NotNull && trim ($Value) == '')
		{
			$Message = 'Cannot be empty';
			return false;
		}

		return true;
	}

	// Override to correct a field's value before being saved
	// ret; NULL if successful, error message if failed
	// cField
	public function ConvertValue(&$Value)
	{
		$Message;
		if ($this->IsLegalValue($Value, $Message, true))
			return NULL;
		if ($this->MakeValueLegal($Value))
			return NULL;
		return $Message;
	}

	// Override to correct a field's value before being saved
	// cField
	public function MakeValueLegal(&$Value)
	{
		return false;	// Requires content specific correction
	}

	// cField
	protected function GetIds($Record)
	{
		if (isset($Record['Id']))
			$Ids = $Record['Id'];
		elseif (isset($Record['MacroId']))
			$Ids = $Record['MacroId'];
		else
			return array();

		if (is_array($Ids) == false)
			$Ids = array($Ids);
		return $Ids;
	}

	// cField
	protected function GetImageIds($Record)
	{
		if (isset($Record['ImageId']) && ($Record['ImageId'] > 0))
			$Ids = $Record['ImageId'];
		else if (isset($Record['SpotImageId']))
			$Ids = $Record['SpotImageId'];
		else
			return array();

		if (is_array($Ids) == false)
			$Ids = array($Ids);

		// Only return valid image ids
		$ReturnIds = array();
		foreach ($Ids as $Id)
		{
			if ($Id != '')
				$ReturnIds[] = $Id;
		}

		return $ReturnIds;
	}

	// Return the value of the field
	// If this is an array of records, either return the common value or '[various]'
	// cField
	protected function GetValue($Record)
	{
		$Value = $this->Default;
		foreach ($Record as $RecordRow)
		{
			//If $Record is an array of records
			if (is_array($RecordRow))
			{
				$OldPrimaryKey = GetPrimaryKeyValueFromRecordArray($this->RefTableName, $this->FieldPath, $RecordRow);
				if ($RecordRow[$this->FieldPath] && ($OldPrimaryKey === $this->PrimaryKey))
				{
					$Value = $RecordRow[$this->FieldPath];                    
					break;
				}
			}
			else
			{
				$Value = isset ($Record[$this->FieldPath]) ? $Record[$this->FieldPath] : $this->Default;                            
				//$Record was only a single array item;  Exit out of the loop
				break;
			}
		}
		
		if ($this->SavedEncoded)
			$Value = xmldecode ($Value);

		return $Value;
	}

	// Return an array of values for the field indexed by the record id
	// cField
	protected function GetValues($Record)
	{
		$Values = isset ($Record[$this->FieldPath]) ? $Record[$this->FieldPath] : array();
		if (is_array($Values) == false)
			$Values = array($Values);
		return $Values;
	}


	// Return the current value of this field for the given ID/IDs
	protected function GetOldValue($OldRecord, $NewValue)
	{
		if ($OldRecord === null)
		{
			// Caller does not require value comparison
			return $NewValue;
		}

		return $this->GetValue($OldRecord);
	}

	public function IsReadOnly($RecordIsReadOnly=false)
	{
		return ($RecordIsReadOnly || $this->ReadOnly);
	}


	/**
	* Places generic node attributes into the parent DOM Node
	*  These are usually the <td> cells wrapping the HTML inputs
	* 
	* @param DOMElement $Node	- DOM Node to modify
	* @param int $Id			- Id of the record being displayed
	*/
	// cField
	final protected function SetNodeAttributes (DOMElement $Node, $Ids = null)
	{
		//ids in html need to be: unique, and obey html id rules  http://www.w3.org/TR/html4/types.html#type-id 
		if (is_array ($Ids) && !empty ($Ids))
			$Node->setAttribute ('id', $this->GetUniqueIdentifier() . '_' . $this->FieldId . '_' . implode ('_', $Ids) );
		else
			$Node->setAttribute ('id', $this->GetUniqueIdentifier() . '_' . $this->FieldId);
		$Node->setAttribute ('TableName', $this->TableName);
		$Node->setAttribute ('ColumnName', $this->ColumnName);
	}

	// XXX deprecated
	public function AppendHTML (DOMElement $Node, $Ids, $Record, $OldRecord, $Values, $ReadOnly = true, $ErrorMarkups = true)
	{
		$Options = array('ReadOnly' => $ReadOnly, 'ErrorMarkups' => $ErrorMarkups);
		$this->ToHTML($Node, $Record, $OldRecord, $Options);
	}
	
	/**
	* Output HTML needed to display this field in an Edit context
	*
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Record		- The current record which could contain this field
	* @param array $OldRecord	- The record obtained from the database (or NULL)
	* @param bool $ReadOnly		- Whether the field should be displayed as editable or read-only (DataGroup determined)
	*
	* @param array $Values		- What field values should get diplayed
	*/
	// cField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$this->PrimaryKey = GetPrimaryKeyValueFromRecordArray($this->RefTableName, $this->ColumnName, $Record);
		
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;
		$ErrorMarkups = isset($Options['ErrorMarkups']) ? $Options['ErrorMarkups'] : true;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$Value = $this->GetValue($Record);

		$DOM = $Node->ownerDocument;

		if ($this->IsReadOnly($ReadOnly))
		{
			$Node->appendChild ($DOM->createTextNode ($Value));
			return;
		}

		$OldValue = $this->GetOldValue($OldRecord, $Value);

		if ($this->EsigItem)
			$this->EsigRequired = IsConfigured('EnableESig');

		//	process vocabulary list?
		if (count ($this->Vocabulary) > 0)
		{
				$Input = $Node->appendChild ($DOM->createElement ('SELECT'));
				
				// if the actual value is not in our vocabulary list, then
				// we need to add an additional option for the value.
				if (!in_array ($Value, $this->Vocabulary))
				{
					$OptionNode = $Input->appendChild ($DOM->createElement ('OPTION'));
					$OptionNode->setAttribute ('value', $Value);
					$OptionNode->appendChild ($DOM->createTextNode ($Value));
				}

				// add an option for each item in the vocabulary list
				foreach($this->Vocabulary as $Option)
				{
					$OptionNode = $Input->appendChild ($DOM->createElement ('OPTION'));
					$OptionNode->setAttribute ('value', $Option);

					if ($Option == $Value)
						$OptionNode->setAttribute ('selected', 'true');

					$OptionNode->appendChild ($DOM->createTextNode ($Option));
				}
		}
		else
		{
				$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
				$Input->setAttribute ('type', 'text');
				$Input->setAttribute ('value', $Value);
				$Input->setAttribute ('maxLength', $this->MaxLength);
				$Input->setAttribute ('onkeyup', $this->OnKeyUp);
		}

		$Classes = $this->Class;
		$Message = '';
		if ($ErrorMarkups)
		{
			if ($OldValue !== null && $Value != $OldValue)
				$Classes .= ' Modified';
			if (!$this->IsLegalValue ($Value, $Message))
			{
				$Classes .= ' Invalid';
				$Node->setAttribute ('error', $Message);
			}
		}
		
		$Input->setAttribute ('name',  $this->ColumnName);
		//ids in html need to be: unique, and obey html id rules  http://www.w3.org/TR/html4/types.html#type-id 
		$Input->setAttribute ('id',  $this->GetUniqueIdentifier());
		if ($Classes)
			$Input->setAttribute ('class', $Classes);
		$Input->setAttribute ('oldvalue', $OldValue);
		
		$Input->setAttribute ('PrimaryKey', (is_array($this->PrimaryKey) ? join(",", $this->PrimaryKey) : $this->PrimaryKey)); 
		$Input->setAttribute ('TableName', $this->RefTableName); 
		$Input->setAttribute ('ColumnName', $this->RefColumnName); 
		$this->AssignAttributes($Input);
	}

	/**
	* Output HTML needed to display this field in a Search context
	*
	* @param DOMDocument $DOM	- DOM to export HTML to
	* @param DOMElement $Node	- DOM Element to populate
	* @param string $Value		- What field value should get diplayed
	* @param string $Value2     - What field value2 should get diplayed 
	* @param string $Operator   - 
	* @param string $Disabled   - 
	* @param string $ErrorString- 
	*/
	// cField
	public function GetSearchHTML (DOMDocument $DOM, DOMElement $Node, $Value, $Value2, $Operator, $Disabled, $ErrorString = '')
	{
		if ($this->SavedEncoded)
			$Value = xmldecode ($Value);

		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'hidden');
		$Input->setAttribute ('name', 'FieldName[]');
		$Input->setAttribute ('value', $this->ColumnName);
		if ($Disabled)
			$Input->setAttribute ('disabled', 'true');

		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'hidden');
		$Input->setAttribute ('name', 'Table[]');
		$Input->setAttribute ('value', $this->TableName);
		if ($Disabled)
			$Input->setAttribute ('disabled', 'true');

		$OperatorList = $Node->appendChild ($DOM->createElement ('SELECT'));
		$OperatorList->setAttribute ('name', 'FieldOperator[]');
		$OperatorList->setAttribute ('style', 'font-size: 8pt;');
		$OperatorList->setAttribute ('onchange', 'CheckValue2(this);');
		if ($Disabled)
			$OperatorList->setAttribute ('disabled', 'true');

		foreach ($this->ValidFilters as $OperatorText => $OperatorValue)
		{
			$Option = $OperatorList->appendChild ($DOM->createElement ('OPTION'));
			$Option->setAttribute ('class', 'Operator');
			$Option->setAttribute ('value', $OperatorValue);
			if ($OperatorValue == $Operator)
				$Option->setAttribute ('selected', 'true');

			$Option->appendChild ($DOM->createTextNode ($OperatorText));
		}

		$this->GetSearchInputHTML ($DOM, $Node, $Value, $Value2, $Disabled, $Operator != 'Range');
	
		// if there's an error, display it next to the field.
		//<div style="display: none; color: rgb(255, 68, 68);" id="shortError">Must be a positive, whole number</div>
		if ($ErrorString != '')
		{
			$ErrorDiv = $Node->appendChild($DOM->createElement("DIV"));
			$ErrorDiv->appendChild($DOM->createTextNode($ErrorString));
			$ErrorDiv->setAttribute("style", "color: rgb(255, 68, 68);");
			$ErrorDiv->setAttribute("id", "shortError");  
		}
			
	}

	/**
	* Output HTML needed to display this field in a Search context
	*
	* @param DOMDocument $DOM	- DOM to export HTML to
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Values		- What field value should get diplayed
	*/
	// cField
	public function GetSearchInputHTML (DOMDocument $DOM, DOMElement $Node, $Value, $Value2, $Disabled, $Disabled2)
	{
		if ($this->SavedEncoded)
			$Value = xmldecode ($Value);

		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'text');
		$Input->setAttribute ('value', $Value);
		$Input->setAttribute ('maxLength', $this->MaxLength);
		$Input->setAttribute ('name', 'FieldValue[]');
		$Input->setAttribute ('autocomplete', 'off');
		$Input->setAttribute ('class', 'searchinput');
		// perform  searching ''onkeyup'' only on fields that have lkup dropdown menu set 
		$EnableLkupSearch = $this->HasAjaxDropDown() ? 'true' : 'false';		
		$Input->setAttribute ('onkeyup', "SetSearchOperator('$this->RefTableName','$this->RefColumnName'); AjaxRequestMatches('$this->RefTableName','$this->RefColumnName',this, event, $EnableLkupSearch)");		
		if ($Disabled)
			$Input->setAttribute ('disabled', 'true');

		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'text');
		$Input->setAttribute ('value', $Value2);
		$Input->setAttribute ('maxLength', $this->MaxLength);
		$Input->setAttribute ('name', 'FieldValue2[]');
		$Input->setAttribute ('style', 'width: 225px;' . ($Disabled2 ? ' display: none;' : ''));
		$Input->setAttribute ('autocomplete', 'off');
		// perform  searching ''onkeyup'' only on fields that have lkup dropdown menu set 
		if ($this->HasAjaxDropDown())
		{
			$Input->setAttribute ('onkeyup', "AjaxRequestMatches('$this->RefTableName','$this->RefColumnName',this, event, true)");	
		}
		if ($Disabled)
			$Input->setAttribute ('disabled', 'true');
	}


	// Return an array of matches in the database for SearchString
	// cField
	public function GetMatches($SearchString, $NumToReturn)
	{
		//$TestTable = ($TableName == 'Image') ? 'Slide' : $TableName;

		// check whether this is an AjaxDropDown field || whether a vocabulary
		if (!empty($this->Vocabulary))		// use the vocabulary
		{
			$MatchArray = array();
			foreach ($this->Vocabulary as $Vocab)
			{
				if (stripos($Vocab, $SearchString) !== FALSE)
				{
					$MatchArray[] = $Vocab;
				}
			}
			return $MatchArray;
		}

		if ($this->HasAjaxDropDown() == false)
			return array();

		// if Special handling/ external data from Key fields - use those tables/columnNames
		$TableName = $this->TableName;
		$ColumnName = $this->ColumnName;
		if ($ColumnName == 'GenieProjectId')
		{
			$TableName = 'GenieProject';
			$ColumnName = 'Name';
		}
		else if ($ColumnName == 'ImageTypeId')
		{
			$TableName = 'ImageType';
			$ColumnName = 'Name';
		}

		// allow for unusual DataServer Id names
		$Id = 'Id';
		switch ($TableName)
		{
			case 'Image' :			$Id = 'ImageId';			break;
			case 'Macro' :			$Id = 'MacroId';			break;
			case 'Annotation':		$Id = 'AnnotationId';		break;
			case 'MacroParameter' :	$Id = 'MacroParameterId';	break;
			case 'JobQueue':		$Id = 'JobQueueId';			break;
		}
		$DBReader = new cDatabaseReader('GetFilteredRecordList', $TableName);
		$DBReader->AddColumn($ColumnName);
		$DBReader->SetSort($ColumnName, 'Ascending');
		$DBReader->SetRecordsPerPage($NumToReturn);
		$DBReader->SetFilter($TableName, $ColumnName, 'LIKE', $SearchString);
		$Matches = $DBReader->GetRecords(1);

		return $this->FormatMatches($Matches, $ColumnName, $SearchString);
	}

	// Format the array of matches
	// cField
	protected function FormatMatches($Matches, $ColumnName, $SearchString)
	{
		$SubString = htmlspecialchars($SearchString, ENT_QUOTES, 'UTF-8');

		$LeaderArray = array();
		$ContextArray = array();
		foreach ($Matches as $Match)
		{
			$Value = htmlspecialchars ($Match->$ColumnName, ENT_QUOTES, 'UTF-8');
			if ($Value != null && $Value != '')
			{
				$Pos = stripos ($Value, $SubString);	// locate the SearchString within a string

				// highlight the SearchString while maintaining context-case	
				$BoldValue	=	substr ($Value, 0, $Pos) . '<b>' .
				substr ($Value, $Pos, strlen($SubString)) . '</b>' .
				substr ($Value, $Pos + strlen($SubString));

				// sort the matches first by those leading with SearchString, then the rest
				if ($Pos == 0)
					$LeaderArray[] = $BoldValue;
				else
					$ContextArray[] = $BoldValue;
			}
		}
		$MatchArray = array_merge (array_unique($LeaderArray), array_unique($ContextArray));	// duplicate data removed

		return $MatchArray;
	}

	// cField
	public function SetFolderSearchable($Flag)
	{
		$this->FolderSearchable = $Flag;
	}

	// cField
	public function IsFolderSearchable()
	{
		if ($this->FolderSearchable && $this->Visible && $this->Searchable && ($this->AuditAccess == false))
			return true;
		return false;
	}

	// cField
	public function HasAjaxDropDown()
	{
		if ($this->AjaxDropDown && $this->Visible && $this->Searchable && ($this->AuditAccess == false))
			return true;
		return false;
	}

	/**
	* Output HTML needed to display the field in the folder view
	*
	* @param DOMDocument $DOM	- DOM to export HTML to
	* @param DOMElement $Node	- DOM Element to populate
	* @param mixed $Value		- What field value should get diplayed
	* @param bool $Open			- Should folder be displayed as open or closed
	*/
	// cField
	public function GetFolderHTML (DOMDocument $DOM, DOMElement $Node, $Index, $Value, $Open)
	{
		if ($this->SavedEncoded)
			$Value = xmldecode ($Value);

		if ($Open)
			$Node->setAttribute ('class', 'Icon-Open');
		else
			$Node->setAttribute ('class', 'Icon');

		$Link = $DOM->createElement ('A');
		$Node->appendChild ($Link);
		$Link->setAttribute ('onclick', 'OpenFolder (this);');
		$Link->setAttribute ('href', '#');
		$Link->setAttribute ('index', $Index);
		$Link->setAttribute ('column', $this->ColumnName);
		$Link->setAttribute ('value', $Value);

		$Img = $DOM->createElement ('IMG');
		$Link->appendChild ($Img);
		//$Img->setAttribute ('border', '0');
		if ($Open)
			$Img->setAttribute ('src', '/Images/OpenFolder.gif');
		else
			$Img->setAttribute ('src', '/Images/ClosedFolder.gif');

		$Link->appendChild ($DOM->createElement ('BR'));

		if ($Value != '')
			$Link->appendChild ($DOM->createTextNode ($Value));
		else
			$Link->appendChild ($DOM->createTextNode ('(empty)'));
	}

	/**
	* Given a value (from the DB) return the text that represents that value to the user
	* 
	* @param string $Value	DB Value
	* 
	* @return string		Display text
	*/
	// cField
	public function GetDisplayText ($Value)
	{
		return $Value;
	}

	/**
	* Get the Label element for an editable field
	*
	* @param DOMDocument $DOM
	*
	* @return DOMElement
	*/
	// cField
	public function GetLabel (DOMDocument $DOM)
	{
		return CreateLabel($DOM, $this->TableName, $this->ColumnName);
	}

	// cField
	public function ClearAttribute($Key)
	{
		unset($this->Attributes[$Key]);
	}

	// cField
	// Insert the attribute at the beginning of the chain
	// NOTE: You cannot chain events with returns, the first return will terminate the chain
	public function InsertAttribute($Key, $Value)
	{
		if (isset($this->Attributes[$Key]))
			$Value .= $this->Attributes[$Key];
		$this->Attributes[$Key] = $Value;
	}

	// cField
	// Append the attribute at the end of the chain
	// NOTE: You cannot chain events with returns, the first return will terminate the chain
	public function AddAttribute($Key, $Value)
	{
		if (isset($this->Attributes[$Key]))
			$Value = $this->Attributes[$Key] . $Value;
		$this->Attributes[$Key] = $Value;
	}

	// cField
	// Append all attributes to the passed 'Input' element
	protected function AssignAttributes($Input)
	{
		foreach ($this->Attributes as $Key => $Attribute)
		{
			$Input->setAttribute ($Key, $Attribute);
		}
	}

	// Return an SSP warning message if this field controls an SSP configuration
	protected function IsSspConfigField($Value, $Record)
	{
		if ($this->RefTableName == 'Slide')
		{
			if ((isset($Record['SsConfigId']) == false) || ($Record['SsConfigId'] == ''))
				return NULL;
		}

		$Table = GetTableObj('ScoreActions');
		$ConfigArray = $Table->GetRecords();
		foreach ($ConfigArray as $Config)
		{
			foreach ($Config['SsFieldValues'] as $FieldValue)
			{
				if (($FieldValue['FieldName'] == $this->ColumnName)
					&& ($FieldValue['Value'] == $Value))
				{
					if ($this->RefTableName == 'Slide')
					{
						return 'All previously calculated Analysis, Score, and Interpretation data for this slide will be erased. Please notify the responsible party IMMEDIATELY if this slide has already been analyzed';
					}
					else
					{
						return 'All previously calculated Analysis, Score, and Interpretation data for all slides that do not overridethis field will be erased.  Please notify the responsible party IMMEDIATELY if an affected  slide has already been analyzed';
					}
				}
			}
		}
		return NULL;
	}
	/**
	 * Create a unique string from html id attribute
	 * @return string Unique html identifier
	 */
	protected function GetUniqueIdentifier()
	{
		if (self::GENERATE_UNIQUE_IDENTIFIER)
		{
			return uniqid("id_" . $this->TableName . '_' . $this->ColumnName);              
		}
		else
		{
			return "id_" . $this->TableName . '_' . $this->ColumnName;
		}
	}
	protected function getDateDisplayType($Table, $Column)
	{
		global $FieldDateDisplayTypes;
		$key = $Table . '.' . $Column;
		if (isset($FieldDateDisplayTypes[$key]))
			return $FieldDateDisplayTypes[$key];
		return cField::DisplayDateTime;
	}
}

/**
* cIntegerField
* 
* Integer fields contain positive, whole-number (integer) data only.
* 
* @author Tristan Hoare <thoare@aperio.com>
* 
* @package Tables
*/
class cIntegerField extends cField
{
	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table the field belongs to
	* @param string $ColumnName - Database name of the column this field represents
	* @param string $DisplayName - User defined display name shown in the UI
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);
		
		$this->FieldType = 'Integer';
	}
	
	/**
	* Validates the given value for this field
	*
	* @param string $Value - The value to test
	* @param string $Message - Output parameter to contain specific messages about the value
	* @param bool $RigidCheck - Forces more restrictive checks for what may be placed in the DB (rather than all values could potentially occur)
	* 
	* @return true if the value is valid, false otherwise
	*/
	// cIntegerField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if (parent::IsLegalValue ($Value, $Message, $RigidCheck))
			$Message = '';
		else
			return false;
		
		if ($Value == '' || $Value == '[various]')
			return true;
		if ((preg_match('/\./', $Value)) || !is_numeric ($Value) || intval ($Value) != $Value)
		{
			$Message = 'Must be a positive, whole number';
			return false;
		}
		elseif (intval ($Value) <= 0)
		{
			$Message = 'Must be positive';
			return false;
		}
		elseif (intval ($Value) >= 2147483647)
		{
			$Message = 'Must be less than 2147483647';
			return false;
		}
		
		return true;
	}
}


/**
* cNumberField
* 
* Number fields contain floating point numerical data only.
* 
* @author Tristan Hoare <thoare@aperio.com>
* 
* @package Tables
*/
class cNumberField extends cField
{
	private $Precision = 0;
	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table the field belongs to
	* @param string $ColumnName - Database name of the column this field represents
	* @param string $DisplayName - User defined display name shown in the UI
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	* @param string $Default - Default value
	* @param int $Precision - Maximum decimal precision
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT, $Default = '', $Precision = 0)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags, $Default);
		
		$this->FieldType = 'Number';
		$this->Precision = $Precision;
	}
	
	/**
	* Validates the given value for this field
	*
	* @param string $Value - The value to test
	* @param string $Message - Output parameter to contain specific messages about the value
	* @param bool $RigidCheck - Forces more restrictive checks for what may be placed in the DB (rather than all values could potentially occur)
	* 
	* @return true if the value is valid, false otherwise
	*/
	// cNumberField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if (parent::IsLegalValue ($Value, $Message, $RigidCheck))
			$Message = '';
		else
			return false;
		
		if ($Value == '' || $Value == '[various]')
			return true;
		
		$Success = is_numeric ($Value);
		
		if (!$Success)
			$Message = 'Invalid Number';
		
		else if ($this->Precision > 0)
		{
			$Decimal = strrchr($Value, '.');
			if ($Decimal !== false)
			{
				// don't include decimal point in input string length
				if (strlen($Decimal) - 1 > $this->Precision)
				{
					$Success = false;
					$Message = 'More than ' . $this->Precision . ' decimal places';
				}
			}
		}
		
		return $Success;
	}
}

/**
* cToggleField
* 
* Toggle fields are represented by a simple checkbox and can be either on (1), or off (0).
* 
* @author Tristan Hoare <thoare@aperio.com>
* 
* @package Tables
*/
class cToggleField extends cField
{
	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table the field belongs to
	* @param string $ColumnName - Database name of the column this field represents
	* @param string $DisplayName - User defined display name shown in the UI
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	* @param string $Default - Default value
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT, $Default = '0')
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags, $Default);
		
		$ValidFilters = array
		(
			'-- All --' 	=> 	'All', 
			'equal to'		=>	'=', 
			'not equal to' 	=>	'<>', 
		);

		$this->FieldType = 'Toggle';
		$this->AjaxDropDown = false;
		$this->Vocabulary = null;
		$this->HasVocabulary = false;
		$this->Sortable = false;

		$this->AddAttribute ('onchange', 'this.value = (this.checked ? "1" : "0"); CheckModified (this);');
		$this->AddAttribute ('onkeypress', 'if ((event.which ? event.which : event.keyCode) == 13) {this.onchange (); SaveAll ();}');
	}

	/**
	* Validates the given value for this field
	*
	* @param string $Value - The value to test
	* @param string $Message - Output parameter to contain specific messages about the value
	* @param bool $RigidCheck - Forces more restrictive checks for what may be placed in the DB (rather than all values could potentially occur)
	* 
	* @return true if the value is valid, false otherwise
	*/
	// cToggleField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if (!in_array ($Value, array ('[various]', '0', '1')))
		{
			$Message = 'Invalid Selection';
			return false;
		}

		return true;
	}

	/**
	* Output HTML needed to display this field in an Edit context
	*
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Record		- The current record which could contain this field
	* @param array $OldRecord	- The record obtained from the database (or NULL)
	* @param bool $ReadOnly		- Whether the field should be displayed as editable or read-only
	*/
	// cToggleField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$this->PrimaryKey = GetPrimaryKeyValueFromRecordArray($this->RefTableName, $this->ColumnName, $Record);
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;

		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$Value = $this->GetValue($Record);

		if ($this->IsReadOnly($ReadOnly))
		{
			$Display = $Value == '[various]' ? $Value : ($Value == '1' ? 'Yes' : 'No');
			$Node->appendChild ($DOM->createTextNode ($Display));
		}
		else
		{		
			$OldValue = $this->GetOldValue($OldRecord, $Value);

			$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
			$Input->setAttribute ('type', 'checkbox');
			$Input->setAttribute ('value', $Value);

			if ($Value == '1')
				$Input->setAttribute ('checked', 'true');
			elseif ($Value == '[various]')
			{
				$Input->setAttribute ('disabled', 'true');
			}

			$Input->setAttribute ('name',  $this->ColumnName);
			//ids in html need to be: unique, and obey html id rules  http://www.w3.org/TR/html4/types.html#type-id 
			$Input->setAttribute ('id', $this->GetUniqueIdentifier());
			$Input->setAttribute ('oldvalue', $OldValue);
			if ($Value != $OldValue)
				$Input->setAttribute ('class', "$this->Class checkbox Modified");
			else
				$Input->setAttribute ('class', "$this->Class checkbox");

			$this->AssignAttributes($Input);
			$Input->setAttribute ('oldvalue', $OldValue);
			$Input->setAttribute ('onchange', 'this.value = (this.checked ? "1" : "0"); CheckModified (this);');
			$Input->setAttribute ('onkeypress', 'if ((event.which ? event.which : event.keyCode) == 13) {this.onchange (); SaveAll ();}');
			$Input->setAttribute ('PrimaryKey', (is_array($this->PrimaryKey) ? join(",", $this->PrimaryKey) : $this->PrimaryKey)); 
			$Input->setAttribute ('TableName', $this->RefTableName); 
			$Input->setAttribute ('ColumnName', $this->RefColumnName); 
		}
	}

	/**
	* Output HTML needed to display this field in a Search context
	*
	* @param DOMDocument $DOM	- DOM to export HTML to
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Values		- What field value should get diplayed
	*/
	// cToggleField
	public function GetSearchInputHTML (DOMDocument $DOM, DOMElement $Node, $Value, $Value2, $Disabled, $Disabled2)
	{
		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'checkbox');
		if ($Value == '1')
		{
			$Input->setAttribute ('checked', 'true');
		}
		else
		{
			// by default it is unchecked so need to set value to '0' 

			$Value = '0';
		}
		$Input->setAttribute ('onchange', 'javascript:GetNextSibling(event);');	
		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'hidden');
		$Input->setAttribute ('value', $Value);
		$Input->setAttribute ('name', 'FieldValue[]');
		if ($Disabled)
			$Input->setAttribute ('disabled', 'true');
		
		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'hidden');
		$Input->setAttribute ('name', 'FieldValue2[]');
		if ($Disabled)
			$Input->setAttribute ('disabled', 'true');
	}
}

/**
* cTextField
* 
* Text fields are the most basic field type.  They contain simple strings which
* may be limited in length by the $MaxLength property.  The only invalid string
* is a string which is too long.
* 
* @author Tristan Hoare <thoare@aperio.com>
* 
* @package Tables
*/
class cTextField extends cField
{
	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table the field belongs to
	* @param string $ColumnName - Database name of the column this field represents
	* @param string $DisplayName - User defined display name shown in the UI
	* @param int $MaxLength - Maximum character length of this field
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	* @param string $Default - Default value
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $MaxLength = 255, $Flags = cField::FLAGS_DEFAULT, $Default = '')
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags, $Default);
 
		$this->FieldType = 'Text';
		$this->MaxLength = $MaxLength;
	}

	/**
	* Validates the given value for this field
	*
	* @param string $Value - The value to test
	* @param string $Message - Output parameter to contain specific messages about the value
	* @param bool $RigidCheck - Forces more restrictive checks for what may be placed in the DB (rather than all values could potentially occur)
	* @return true if the value is valid, false otherwise
	*/
	// cTextField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if (parent::IsLegalValue ($Value, $Message, $RigidCheck))
			$Message = '';
		else
			return false;

		if ($Value == '' || $Value == '[various]')
			return true;

		// MSSQL allocates two bytes per character in a varchar(x) field.
		if (strlen ($Value) > $this->MaxLength * 2)
		{
			$Message = "'$Value' is too long";
			return false;
		}

		return true;
	}
}

/**
* cFileField
* This is a hygrid field;
* 	If the field is editable, then it is presented as an html file
* 	If the field is readonly, then it is presented like a cIdentityLinkField
*/
class cFileField extends cFileNameField
{
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Link, $Flags)
	{
		// Ensure required flags are set
		$Flags |= cField::FLAGS_NO_COMBINE;
		parent::__construct ($TableName, $ColumnName, $DisplayName, 255, $Flags);

		$this->Link = $Link;

		$this->IsIdentity = true;
	}
	
	/**
	 *Override the default GetIds() method to support fetching documents from views using their DownTo Syntax
	 * @param type $Record The Record to query
	 * @return array 
	 */
	protected function GetIds($Record)
	{       
		$Ids = GetPrimaryKeyValueFromRecordArray($this->RefTableName, $this->ColumnName, $Record);

		if (is_array($Ids) == false)
			$Ids = array($Ids);
		return $Ids;
	}    
	

	// cFileField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;

		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);
		$Id = $Ids[0];

		$Values = $this->GetValues($Record);
		$Value = $Values[0];

		if ($this->IsReadOnly($ReadOnly))
		{
			$Link = $Node->appendChild ($DOM->createElement('A'));
			$Link->setAttribute ('href', "$this->Link?Id=$Id");
			$Link->appendChild ($DOM->createTextNode ($Value));
		}
		else
		{
			$OldValue = $this->GetOldValue($OldRecord, $Value);
			$Classes = $this->Class;
			if ($Value != $OldValue)
				$Classes .= ' Modified';
			$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
			$Input->setAttribute ('type', 'file');
			$Input->setAttribute ('name',  $this->ColumnName);
			$Input->setAttribute ('value', $Value);
			$Input->setAttribute ('class', $Classes);
			$Input->setAttribute ('maxLength', '200');
			$Input->setAttribute ('onchange', 'CheckModified(this)');
		}
	}
}

/**
* cFileNameField
* 
* FileNameField fields are text fields that have the additional restriction that their
* value must only contain characters that are valid for filenames
* 
* @author Bob Ellis
* 
* @package Tables
*/
class cFileNameField extends cTextField
{
	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table the field belongs to
	* @param string $ColumnName - Database name of the column this field represents
	* @param string $DisplayName - User defined display name shown in the UI
	* @param int $MaxLength - Maximum character length of this field
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	* @param string $Default - Default value
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $MaxLength = 255, $Flags = cField::FLAGS_DEFAULT, $Default = '')
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $MaxLength, $Flags, $Default);

		$this->FieldType = 'FileName';
	}

	/**
	* Validates the given value for this field
	*
	* @param string $Value - The value to test
	* @param string $Message - Output parameter to contain specific messages about the value
	* @param bool $RigidCheck - Forces more restrictive checks for what may be placed in the DB (rather than all values could potentially occur)
	* @return true if the value is valid, false otherwise
	*/
	// cFileNameField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if (!parent::IsLegalValue($Value, $Message, $RigidCheck))
			return false;

		$BadChars = '\/:*?"<>|';
		if (strpbrk($Value, $BadChars) != false)
		{
			$Message = 'can not contain any of these characters  \ / : * ? " < > |';
			return false;
		}
		return true;
	}
}

/**
* cMemoField
* 
* @author Tristan Hoare <thoare@aperio.com>
* 
* @package Tables
*/
class cMemoField extends cField
{
	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table the field belongs to
	* @param string $ColumnName - Database name of the column this field represents
	* @param string $DisplayName - User defined display name shown in the UI
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);
		
		$this->FieldType = 'Memo';
		$this->AjaxDropDown = false;
		$this->Grouped = false;
		$this->Sortable = false;
		$this->Vocabulary = null;
		$this->HasVocabulary = false;
		$this->ValidFilters = array(
			'-- All --' 	=> 	'All', 
			'contains'		=>	'LIKE'
			);
			//'does not contain'     =>     'NOTLIKE'); // disabled from UI filters list in release 12.0 per Debra
	}
				  
				 

	
	/**
	* Validates the given value for this field
	*
	* @param string $Value - The value to test
	* @param string $Message - Output parameter to contain specific messages about the value
	* @param bool $RigidCheck - Forces more restrictive checks for what may be placed in the DB (rather than all values could potentially occur)
	* @return true if the value is valid, false otherwise
	*/
	// cMemoField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		return true;
	}
	
	/**
	* Output HTML needed to display this field in an Edit context
	*
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Record		- The current record which could contain this field
	* @param array $OldRecord	- The record obtained from the database (or NULL)
	* @param bool $ReadOnly		- Whether the field should be displayed as editable or read-only
	*/
	// cMemoField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$this->PrimaryKey = GetPrimaryKeyValueFromRecordArray($this->RefTableName, $this->ColumnName, $Record);
		
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;

		// UpToCaseStateByStateId.Description is a generic cMemoField and can be exposed by
		// enabling it in Addtional Info DataView.  There seems to be no place to ensure it is read only so
		// hard code it here so CaseAssembly AdditionalData view does not allow edit
		if (isset($this->RefTableName) && isset($this->RefColumnName))
		{
			if ($this->RefTableName == 'CaseState' && $this->RefColumnName == 'Description')
			{
				$ReadOnly = true;
			}
		}

		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$Value = $this->GetValue($Record);
		$Value = str_replace (array ("\r\n", "\r"), "\n", $Value);

		if ($this->IsReadOnly($ReadOnly))
		{
			if ($Value != '')
			{
				$Lines = preg_split ("/[\r\n]+/", $Value);
				
				$LastBR = null;
				
				foreach ($Lines as $Line)
				{
					$Node->appendChild ($DOM->createTextNode ($Line));
					$LastBR = $Node->appendChild ($DOM->createElement ('BR'));
				}
				
				if ($LastBR)
					$Node->removeChild ($LastBR);
			}
		}
		else
		{		
			$OldValue = $this->GetOldValue($OldRecord, $Value);
			$OldValue = str_replace (array ("\r\n", "\r"), "\n", $OldValue);
			
			$Message = '';
			$Classes = $this->Class;
			
			if ($Value != $OldValue)
				$Classes .= ' Modified';
			if (!$this->IsLegalValue ($Value, $Message))
			{
				$Classes .= ' Invalid';
				$Node->setAttribute ('error', $Message);
			}
			
			$Text = $Node->appendChild ($DOM->createElement ('TEXTAREA'));
			$Text->setAttribute ('name', $this->ColumnName);
			//ids in html need to be: unique, and obey html id rules  http://www.w3.org/TR/html4/types.html#type-id 
			$Text->setAttribute ('id', $this->GetUniqueIdentifier());
			$Text->setAttribute ('oldvalue', $OldValue);
			$Text->setAttribute ('class', $Classes);
			$this->AssignAttributes($Text);

			$Text->appendChild ($DOM->createTextNode ($Value));                        
			$Text->setAttribute ('TableName', $this->RefTableName); 
			$Text->setAttribute ('ColumnName', $this->RefColumnName);             
			$Text->setAttribute ('PrimaryKey', (is_array($this->PrimaryKey) ? join(",", $this->PrimaryKey) : $this->PrimaryKey)); 
		}
	}

	/**
	* Output HTML needed to display this field in a Search context
	*
	* @param DOMDocument $DOM	- DOM to export HTML to
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Values		- What field value should get diplayed
	*/
	// cMemoField
	public function GetSearchInputHTML (DOMDocument $DOM, DOMElement $Node, $Value, $Value2, $Disabled, $Disabled2)
	{
		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'text');
		$Input->setAttribute ('value', $Value);
		$Input->setAttribute ('name', 'FieldValue[]');
		$Input->setAttribute ('class', 'searchinput'); 
		// perform  searching ''onkeyup'' only on fields that have lkup dropdown menu set 
		$EnableLkupSearch = $this->HasAjaxDropDown() ? 'true' : 'false';		
		$Input->setAttribute ('onkeyup', "SetSearchOperator('$this->RefTableName','$this->RefColumnName'); AjaxRequestMatches('$this->RefTableName','$this->RefColumnName',this, event, $EnableLkupSearch)");
		$Input->setAttribute ('autocomplete', 'off');
		if ($Disabled)
			$Input->setAttribute ('disabled', 'true');
		
		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'hidden');
		$Input->setAttribute ('name', 'FieldValue2[]');
		if ($Disabled)
			$Input->setAttribute ('disabled', 'true');
	}
}

/**
* cLogoField	Create field to display a customer supplied logo image. The use of "logo"
*				is to distinguish this from a slide image field. 
*				Height is handled in CSS.
* 
* @author Patty Kraft
* 
* @package Tables
*/
class cLogoField extends cField
{
	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table to which the field belongs
	* @param string $ColumnName - Database name of the column this field represents
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	* @param string $LogoFileName - Name of the image file in the /Customers directory
	*/
	function __construct($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT, $LogoFileName = '')
	{
		$this->Visible = true; // Deprecated therefore unnecessary
		$this->IsDatabaseField = true; // together with IsVisible will include field in record from database
		$this->IsVisible = true;
		$this->IconLocation = $LogoFileName;
		$this->DisplayOnlyInList = true;

		parent::__construct($TableName, $ColumnName, $DisplayName);
	}

	// cLogoField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);
		// ReadOnly is false by default, no need to test
		if ((count($Ids) == 1) && ($Record['LogoFileName'] != ''))
		{
			$Id = $Ids[0];
			$Link = "/EditCustomers.php?Ids[]=$Id";
			$IconLocation = '/Customers/' . $Record['LogoFileName'];
			$Title = "Open $this->TableName #$Id";
			$ImageElement = $this->CreateIcon($Node, $IconLocation, $Link, $Title);
		}
		else
		{
			$Link = NULL;
			$Title = NULL;
			$Node->nodeValue = 'n/a';
		}
	}
	
	protected function CreateIcon($Cell, $Image, &$Link=NULL, $Title=NULL)
	{
		$DOM = $Cell->ownerDocument;

		$ImageElement = $DOM->createElement ('IMG');
		$ImageElement->setAttribute ('src', $Image);
		$ImageElement->setAttribute ('class','noBorderWithHeight');		// Let CSS control logo height
		$ImageElement->setAttribute ('alt', '');
		if ($Title)
			$ImageElement->setAttribute ('title', $Title);

		if ($Link)
		{
			$Anchor = $DOM->createElement ('A');
			$Anchor->setAttribute ('href', $Link);
			$Anchor->appendChild ($ImageElement);
			$Cell->appendChild ($Anchor);
			$Link = $Anchor;	// Overrwrite the string with the resultand DOMNode for client modifications
		}
		else
		{
			$Cell->appendChild ($ImageElement);
		}

		return $ImageElement;
	}
}

/**
* cDateTimeField
*
* @author Tristan Hoare <thoare@aperio.com>
*
* @package Tables
*/
class cDateTimeField extends cField
{
	/**
	* {@inheritdoc}
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		global $PALMode;
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);
		
	$this->ValidFilters = array(
		'-- All --' 	=> 	'All',
		'greater than'	=>	'>',
		'equal to'		=>	'=',
		'not equal to' 	=>	'<>',
		'less than'		=>	'<',
		'contains'		=>	'LIKE',
	   // 'does not contain'     =>     'NOTLIKE', // disabled from UI filters list in release 12.0 per Debra
		'between'		=>	'Range',
		'includes'		=>	'in');
		$this->FieldType = 'DateTime';
		$DateDisplayType = $this->getDateDisplayType($TableName, $ColumnName);
		$this->FormatHint = GetConfigValue('DateFormatHint');
		if (isset($PALMode))
		{
			if ($DateDisplayType == cField::DisplayDate)
			{
				$this->Class .= ' DateWatermark DatePicker';
			}
			elseif ($DateDisplayType == cField::DisplayDateTime)
			{
				if (strstr($this->FormatHint, ' hh:mm:ss') === false)
					$this->FormatHint .= " hh:mm:ss";
				$this->Class .= ' DateTimeWatermark DateTimePicker';
				// The DateTime picker adds an extra space to the field's value,
				// this needs to be removed before calling CheckModified
				$this->ClearAttribute('onchange');
				$this->AddAttribute ('onchange', 'this.value=$.trim(this.value);CheckModified (this);');
			}
			elseif ($DateDisplayType == cField::DisplayTime)
			{
				$this->Class .= ' TimeWatermark';
			}
		}
		$this->Vocabulary = null;
		$this->HasVocabulary = false;
		$this->AjaxDropDown = false; 
	}

	/**
	* {@inheritdoc}
	*/
	// cDateTimeField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if ($Value == '[various]')
			return true;

		$OldMessage = $Message;
		$DateDisplayType = $this->getDateDisplayType($this->TableName, $this->ColumnName);
		if ($DateDisplayType == cField::DisplayDateTime || $DateDisplayType == cField::DisplayTime)
		{
			$Len = strlen($Value);
			if ($Len === strlen('yyyy/mm/dd hh:mm') || $Len === strlen('hh:mm'))
				$Value .= ':00';
			else if ($Len === strlen('yyyy/mm/dd'))
				$Value .= ' 00:00:00';
		}
		if (CheckTimeFormat ($Value, $Message, ($DateDisplayType == cField::DisplayDateTime || $DateDisplayType == cField::DisplayTime), true, true) == false)
		{
			return false;
		}

		$Message = $OldMessage;
		return true;
	}

	/**
	* {@inheritdoc}
	*/
	// cDateTimeField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$this->PrimaryKey = GetPrimaryKeyValueFromRecordArray($this->RefTableName, $this->ColumnName, $Record);
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;

		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$DateDisplayType = $this->getDateDisplayType($this->TableName, $this->ColumnName);
		$this->FormatHint = GetConfigValue('DateFormatHint');

		$Value = $this->GetValue($Record);
		$IncludeTime = false;
		// DataServer returns this minimum date to indicate that this is an aggregate date field
		// with more than one value (e.g. two specimens for a case, each of which has a different date)
		if ($Value == '1753-01-01 00:00:00')
		{
			$Value = 'various';
			$this->MaxLength = strlen($Value);
		}
		else
		{
			if ($DateDisplayType === cField::DisplayDateTime)
			{
				$IncludeTime = true;
				if (strstr($this->FormatHint, ' hh:mm:ss') === false)
					$this->FormatHint .= ' hh:mm:ss';
//				if (strlen($Value) === strlen('yyyy/mm/dd hh:mm'))
//				{
//					$Value .= ':00';
//				}
			}
			else if ($DateDisplayType === cField::DisplayTime)
			{
				$IncludeTime = true;
				$this->FormatHint = 'hh:mm:ss';
				if (strlen($Value) === strlen('hh:mm'))
				{
					$Value .= ':00';
				}
			}
			if (strlen($Value) > strlen($this->FormatHint))
			{
				$Value = substr($Value, 0, strlen($this->FormatHint));
			}
			$Value = ConvertDateToCurrentConfigFormat($Value, $IncludeTime);
			$Record[$this->FieldPath] = $Value;
			if (isset($OldRecord))
				$OldRecord[$this->FieldPath] = $Value;
			$Value = substr ($Value, 0, $this->MaxLength);
		}
		
		if ($this->IsReadOnly($ReadOnly))
		{
			$Node->appendChild ($DOM->createTextNode ($Value));
		}
		else
		{			
			$OldValue = $this->GetOldValue($OldRecord, $Value);
			$OldValue = ConvertDateToCurrentConfigFormat($OldValue, $IncludeTime);
			$OldValue = substr ($OldValue, 0, $this->MaxLength);
			$Message = '';
				
			$Classes = $this->Class;
			if ($OldValue !== null && $Value != $OldValue)
			{
				$Classes .= ' Modified';
			}

			if ($this->IsLegalValue ($Value, $Message) == false)
			{
				if ($this->MakeValueLegal($Value) == false)
				{
					$Classes .= ' Invalid';
					$Node->setAttribute ('error', $Message);
				}
			}

			$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
			$Input->setAttribute ('type', 'text');
			$Input->setAttribute ('value', $Value);
			$Input->setAttribute ('maxLength', $this->MaxLength);
			$Input->setAttribute ('name',  $this->ColumnName);
			//ids in html need to be: unique, and obey html id rules  http://www.w3.org/TR/html4/types.html#type-id 
			$Input->setAttribute ('id', $this->GetUniqueIdentifier());
			$Input->setAttribute ('class', $Classes);
			$Input->setAttribute ('oldvalue', $OldValue);
			$Input->setAttribute ('PrimaryKey', (is_array($this->PrimaryKey) ? join(",", $this->PrimaryKey) : $this->PrimaryKey)); 

			$this->AssignAttributes($Input);
			$Input->setAttribute ('onkeyup', $this->OnKeyUp);
		}
	}

	// cDateTimeField
	public function MakeValueLegal(&$Value)
	{
		if ($Value != '' && $Value != '[various]' && strlen($Value) >= strlen('yyyy/mm/dd'))
		{ 
			if (strlen ($Value) < $this->MaxLength)
			{
				// Try adding the defaulted time for the user
				$TestValue = $Value . ' 00:00:00';
				$Message = '';
				if ($this->IsLegalValue ($TestValue, $Message))
				{
					// That was what it needed, fix it
					$Value = $TestValue;
					return true;
				}
			}
		}
		// Couldn't fix it
		return false;
	}
}

/**
* cDateField
*
* @author Tristan Hoare <thoare@aperio.com>
*
* @package Tables
*/
class cDateField extends cDateTimeField
{
	public $FutureOk = true;

	/**
	* {@inheritdoc}
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);

		$this->FieldType = 'Date';
		$this->FormatHint = GetConfigValue('DateFormatHint');
		$this->MaxLength = strlen ($this->FormatHint);
		$this->Vocabulary = null;
		$this->HasVocabulary = false;
		$this->AjaxDropDown = false;
		//future date checking was removed per FB15672
	}

	/**
	* {@inheritdoc}
	*/
	// cDateField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if ($Value == '[various]')
			return true;

		$OldMessage = $Message;

		if (CheckTimeFormat ($Value, $Message, false, $this->FutureOk) == false)
							return false;

		$Message = $OldMessage;
		return true;
	}
}

/**
* cTimeZoneField
* @package Tables
*/
class cTimeZoneField extends cDateTimeField
{
	public function __construct ($TableName, $ColumnName, $DisplayName, $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);

		$this->FieldType = 'Date';
		$this->MaxLength = 10;
		$this->Vocabulary = null;
		$this->HasVocabulary = false;
		$this->AjaxDropDown = false;
	}

	// cTimeZoneField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;

		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$Value = $this->GetValue($Record);

		$Text = $this->GetDisplayText($Value);

		if ($this->IsReadOnly($ReadOnly))
		{
			$Node->appendChild ($DOM->createTextNode ($Text));
			return;
		}

		$OldValue = $this->GetOldValue($OldRecord, $Value);
		$OldText = $this->GetDisplayText($OldValue);

		$Classes = $this->Class;
		if ($Value != $OldValue)
		{
			$Classes .= ' Modified';
		}

		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'text');
		$Input->setAttribute ('name', $this->ColumnName);
		//ids in html need to be: unique, and obey html id rules  http://www.w3.org/TR/html4/types.html#type-id 
		$Input->setAttribute ('id', $this->GetUniqueIdentifier());
		$Input->setAttribute ('class', $Classes);
		$Input->setAttribute ('value', $Text);
		$Input->setAttribute ('oldvalue', $OldText);
		$Input->setAttribute ('maxLength', $this->MaxLength);

		$this->AssignAttributes($Input);
		$Input->setAttribute ('modifyCheck', 1);
	}

	// Convert from integer to text
	// cTimeZoneField
	public function GetDisplayText ($Value)
	{
		if (is_numeric($Value))
		{
			if ($Value < 0)
			{
				$Fmt = 'GMT-%02d:%02d';
				$Value = -$Value;
			}
			else
			{
				$Fmt = 'GMT+%02d:%02d';
			}
			$Hour = $Value / 100;
			$Min = $Value % 100;
			$Str = sprintf($Fmt, $Hour, $Min);
			return $Str;
		}

		// Probably already text
		return $Value;
	}

	// Convert the text value into an integer
	// cTimeZoneField. Field is stored as a signed BCD in the database
	// so, remove GMT+ and leading/trailing zeros
	public function MakeValueLegal(&$Value)
	{
		if (strcasecmp(substr($Value, 0, 3), 'GMT') == 0)
		{
			list($hour, $min) = sscanf($Value, 'GMT-%d:%d');
			if ($hour)
			{
				$Value = -(($hour * 100) + $min);
			}
			else
			{
				list($hour, $min) = sscanf($Value, 'GMT+%d:%d');
				if ($hour == null)
					return false;
				$Value = ($hour * 100) + $min;
			}
			return true;
		}
		elseif (is_numeric($Value))
			return true;
		return false;
	}

	// Convert from enumeration (or entered text) to index
	// cTimeZoneField
	public function ConvertValue(&$Value)
	{
		if ($Value == '' || $Value == '[various]')
			return NULL;
		if ($this->MakeValueLegal($Value))
			return NULL;
		return "'$Value' is invalid";
	}

	// cTimeZoneField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if (($Value == '') || ($Value == '[various]'))
			return true;

		if (is_numeric($Value) == false)
		{
			$Message = "'$Value' is not a valid timezone";
			return false;
		}

		return true;
	}
}


/**
* cIdentityField
* 
* Identity fields contain the unique ID fields provided by the database.
* 
* @author Tristan Hoare <thoare@aperio.com>
* 
* @package Tables
*/
class cIdentityField extends cIntegerField
{
	/**
	* {@inheritdoc}
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		// Ids should always be fetched with every record
		// Do not combine fields from serarate records into '[various]'
		$Flags |= cField::FLAGS_CRITICAL | cField::FLAGS_NO_COMBINE;
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);

		$this->Default = 0;	// Default to a new record ID
		$this->FieldType = 'Identity';
		$this->IsIdentity = true;
		$this->Grouped = false;
		$this->FolderSearchable = false;
		$this->AjaxDropDown = false;
	}

	// XXX deprecated
	public function AppendHTML (DOMElement $Node, $Ids, $Record, $OldRecord, $Values, $ReadOnly = true, $Index = -1)
	{
		$Options = array('ReadOnly' => $ReadOnly, 'Index' => $Index);
		$this->ToHTML($Node, $Record, $OldRecord, $Options);
	}

	// cIdentityField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;
		$Index = isset($Options['Index']) ? $Options['Index'] : -1;

		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$Values = $this->GetValues($Record);

		$FirstId = true;
		$TableName = $this->TableName;

		foreach ($Values as $Id)
		{
			if (!$FirstId)
				$Node->appendChild ($DOM->createTextNode (', '));
			else
				$FirstId = false;
			if ($this->Flags & cField::FLAGS_NO_LINK)
			{
				$Node->appendChild ($DOM->createTextNode ($Id));        
			}
			else
			{
				$Link = $DOM->createElement ('A');
				$Node->appendChild ($Link);
				if ($Index >= 0)
					$Link->setAttribute ('href', "/EditRecord.php?TableName=$TableName&Ids[]=$Id&SearchIndex=$Index");
				else
					$Link->setAttribute ('href', "/EditRecord.php?TableName=$TableName&Ids[]=$Id");
				$Link->appendChild ($DOM->createTextNode ($Id));
			}
		}
	}
}

/**
* cImageIdentityField
* 
* Image Identity fields are a specialized version of the cIdentityField objects
* which pertain solely to Images.  Specifically, where Identity fields link the
* user to an Edit Record page, Image Identity fields will attempt to open up an
* Image in ImageScope/WebScope.
* 
* @author Tristan Hoare <thoare@aperio.com>
* 
* @package Tables
*/
class cImageIdentityField extends cIdentityField
{
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);
	}

	/**
	* {@inheritdoc}
	*/
	// cImageIdentityField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$Values = $this->GetValues($Record);

		$FirstId = true;

		foreach ($Values as $ImageId)
		{
			if (!$FirstId)
				$Node->appendChild ($DOM->createTextNode (', '));
			else
				$FirstId = false;
			
			$Link = $DOM->createElement ('A');
			$Node->appendChild ($Link);
			$Link->setAttribute ('href', '#');
			$Link->setAttribute ('onclick', "viewImage($ImageId)");
			$Link->appendChild ($DOM->createTextNode ($ImageId));
		}
	}
}

/**
* cParentIdentityField
* 
* The unique ID of a record's parent record.
* 
* @package Tables
*/
class cParentIdentityField extends cIdentityField
{
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);

		// Override usual identity field behavior.  We do not display multiple parent ids
		$this->Flags &= ~cField::FLAGS_NO_COMBINE;
		$this->CombineValues = true;
		$this->Default = '';
	}

	/**
	* Output HTML needed to display this field in an Edit context
	*
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Record		- The current record which could contain this field
	* @param array $OldRecord	- The record obtained from the database (or NULL)
	*/
	// cParentIdentityField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		if ($Record[$this->ColumnName] == NULL)
			return;

		$ParentIds = $this->GetValues($Record);
		$ParentId = $ParentIds[0];	// can only be one
		$ParentTableName = $Record['ParentTable'];

		$ParentTable = GetTableObj($ParentTableName);
		if ($ParentTable)
			$Label = $ParentTable->DisplayName;
		else
			$Label = $ParentTableName;

		if ($ParentId != '[various]')
		{
			$Node->appendChild ($DOM->createTextNode ("$Label: "));
			$Link = $DOM->createElement ('A');
			$Node->appendChild ($Link);
			$Link->setAttribute ('href', "/EditRecord.php?TableName=$ParentTableName&Ids[]=$ParentId");
			$Link->appendChild ($DOM->createTextNode ($ParentId));
		}
		else
		{
			$Node->appendChild ($DOM->createTextNode ("$Label: [various]"));
		}
	}
}

/**
* cGenieParentIdentityField
* 
* The unique ID of a record's parent record.
* 
* @author Tristan Hoare <thoare@aperio.com>
* 
* @package Tables
*/
class cGenieParentIdentityField extends cIdentityField
{
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);
	}

	/**
	* Output HTML needed to display this field in an Edit context
	*
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Record		- The current record which could contain this field
	* @param array $OldRecord	- The record obtained from the database (or NULL)
	*/
	// cGenieParentIdentityField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$Values = $this->GetValues($Record);

		$FirstId = true;

		foreach ($Values as $Id)
		{
			if (!$FirstId)
				$Node->appendChild ($DOM->createTextNode (', '));
			else
				$FirstId = false;

			$Link = $DOM->createElement ('A');
			$Node->appendChild ($Link);
			$Link->setAttribute ('href', '/EditRecord.php?TableName=GenieProject&Ids[]=' . $Id);
			$Link->appendChild ($DOM->createTextNode ('Genie Project ' . $Id));
		}
	}
}

/**
*
* Create field that defines a link to a display page
*
* @package Tables
*/
class cIdentityLinkField extends cField
{
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Link)
	{
		// Ensure required flags are set
		$Flags = cField::FLAGS_READONLY | cField::FLAGS_NO_COMBINE;
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);

		$this->Link = $Link;

		$this->IsIdentity = true;
	}

	// cIdentityLinkField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);
		$Id = $Ids[0];

		$Values = $this->GetValues($Record);
		$Value = $Values[0];

		$Link = $DOM->createElement ('A');
		$Node->appendChild ($Link);
		$Link->setAttribute ('href', "$this->Link?Id=$Id");
		$Link->appendChild ($DOM->createTextNode ($Value));
	}
}

/**
* Create field that defines a link to a display page
*/
class cLinkField extends cIntegerField
{
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Title, $Link)
	{
		// Ensure required flags are set
		$Flags = cField::FLAGS_READONLY | cField::FLAGS_NO_COMBINE;
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);

		$this->Title = $Title;
		$this->Link = $Link;
	}

	// cLinkField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;
		if ($this->Link)
		{
			$Anchor = $DOM->createElement ('A');
			$Node->appendChild ($Anchor);
			$Anchor->setAttribute ('href', "$this->Link");
			$Title = $this->Title;
			// if $Title is a date in standard date format, convert it to the configured date display format
			if (IsStandardFormatDate($Title))
			{
				$Title = ConvertDateToCurrentConfigFormat($Title);
			}
			$Anchor->appendChild ($DOM->createTextNode ($Title));
		}
		else
		{
			$Node->appendChild ($DOM->createTextNode ($this->Title));
		}
	}
}

/**
* cTextLinkField		Create field that defines a link to a display page from a text link
* 
* @author Patty Kraft
* 
* @package Tables
*/
class cTextLinkField extends cTextField
{
	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table to which the field belongs
	* @param string $ColumnName - Database name of the column this field represents
	* @param string $DisplayName - User defined display name shown in the UI
	* @param int $MaxLength - Maximum display width of field
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	* @param string $Default - 
	* @param string $Title - Title of anchor element
	* @param string $Link - href target for anchor element
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $MaxLength = 255, 
								$Flags = cField::FLAGS_DEFAULT, $Default = '', $Title = '', $Link = '')
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $MaxLength);

		$this->Title = $Title;
		$this->Link = $Link;
	}
	/**
	* Output HTML needed to display this field in an Display context
	*
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Record		- The current record which could contain this field
	* @param array $OldRecord	- The record obtained from the database (or NULL)
	*/
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;
		if ($this->Link)
		{
			$Anchor = $DOM->createElement ('A');
			$Node->appendChild ($Anchor);
			$Anchor->setAttribute ('href', "$this->Link");
			$Anchor->appendChild ($DOM->createTextNode ($this->Title));
		}
		else
		{
			$Node->appendChild ($DOM->createTextNode ($this->Title));
		}
	}
}

/**
*
* Create field that displays an image's thumbnail
*
* @package Tables
*/
class cThumbnailField extends cSpecialField
{
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '')
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, cField::FLAGS_DEFAULT);
		$this->FieldType = 'Image';
		$this->DisplayOnlyInList = true;
		$this->IsExportable = false;
	}

	// cThumbnailField
	public function IsVisible($Page='List')
	{
		$Height = GetThumbnailHeight($this->TableName, $this->ColumnName, $Page);
		if ($Height == 0)
			return false;

		return $this->IsVisible;
	}

	// cThumbnailField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options=NULL)
	{
		if (isset($Options['Page']))
			$Page = $Options['Page'];
		else
			$Page = 'List';

		if (isset($Record['ImageRecords']))
		{
			foreach ($Record['ImageRecords'] as $ImageRecord)
			{
				AppendThumbnailHTMLNode($Node, $ImageRecord, $this->TableName, $this->ColumnName, $Page, false, $this->ReadOnly);
			}
			return;
		}

		$ImageIds = $this->GetImageIds($Record);
		if (count($ImageIds) != 1)
		{
			$DOM = $Node->ownerDocument;
			$Node->appendChild ($DOM->createTextNode ('n/a'));
			return;
		}

		if ($this->ColumnName == 'ReportThumbnail')
		{
			$Node->setAttribute ('TableName', 'Image');
			$Node->setAttribute ('ColumnName', 'ReportImage');
			$Node->setAttribute ('AutoUpdate', 'true');
		}

		AppendThumbnailHTMLNode($Node, $Record, $this->TableName, $this->ColumnName, $Page, false, $this->ReadOnly);
	}
}


/**
*
* Create field that displays the expansion widget
*
* @package Tables
*/
class cExpansionField extends cSpecialField
{
	public function __construct ($TableName)
	{
		$this->Position = -2;
		$this->RoleAccess = 'F';
		$this->IsStatic = true;
		parent::__construct ($TableName, 'Expansion', '', cField::FLAGS_DEFAULT);
	}

	// cExpansionField
	public function ToHTML (DOMElement $Cell, $Record, $OldRecord, $Options = NULL)
	{
		if (isset($Record['ChildListName']))
		{
			$ChildName = $Record['ChildListName'];

			if (IsListExpanded($ChildName))
				$Img = '/Images/Minus.gif';
			else
				$Img = '/Images/Plus.gif';

			$DOM = $Cell->ownerDocument;

			$ExpImg = $Cell->appendChild ($DOM->createElement ('IMG'));
			//ids in html need to be: unique, and obey html id rules  http://www.w3.org/TR/html4/types.html#type-id 
			$ExpImg->setAttribute ('id', $this->GetUniqueIdentifier()  . '_' . 'Img' . $ChildName);
			$ExpImg->setAttribute ('style', 'cursor: pointer');

			$ExpImg->setAttribute ('src', $Img);

			$ExpImg->setAttribute ('onclick', "ToggleChildList(this);");
		}
		// else blank cell
	}
}


/**
*
* Create field that displays the bulk check box
*
* @package Tables
*/
class cBulkCheckField extends cSpecialField
{
	public function __construct ($TableName, $OnCheck)
	{
		$this->Position = -1;
		$this->RoleAccess = 'F';
		$this->IsStatic = true;
		parent::__construct ($TableName, 'BulkCheck', '', cField::FLAGS_DEFAULT);

		$this->OnCheck = $OnCheck;
	}

	// cBulkCheckField
	public function ToHTML (DOMElement $Cell, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Cell->ownerDocument;

		$Input = $Cell->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'checkbox');
		$Input->setAttribute ('name', 'Ids[]');
		$Input->setAttribute ('onclick', $this->OnCheck);

		$ImageIds = $this->GetImageIds($Record);
		if (count($ImageIds) == 1)
		{
			$ImageId = $ImageIds[0];
			$Input = $Cell->appendChild ($DOM->createElement ('INPUT'));
			$Input->setAttribute ('type', 'hidden');
			$Input->setAttribute ('name', 'ImageIds[]');
			$Input->setAttribute ('value', $ImageId);
		}
	}
}

/**
*
* Create field that displays the icon
*
* @package Tables
*/
class cIconField extends cSpecialField
{
	public function __construct ($TableName, $IconLocation)
	{
		parent::__construct ($TableName, 'Icon', '', cField::FLAGS_DEFAULT);
		$this->IconLocation = $IconLocation;
		$this->DisplayOnlyInList = true;
		$this->IsExportable = false;
	}

	// cIconField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		if ((count($Ids) == 1) && ($this->ReadOnly == false))
		{
			$Id = $Ids[0];
			$Link = "/EditRecord.php?TableName=$this->TableName&Ids[]=$Id";
			$Title = "Open $this->TableName #$Id";
		}
		else
		{
			$Link = NULL;
			$Title = NULL;
		}

		$IconLocation = GetRecordIcon($Record, $this->IconLocation);

		$ImageElement = $DOM->createElement ('IMG');
		$ImageElement->setAttribute ('src', $IconLocation);
		//$ImageElement->setAttribute ('border', 0);

		$ImageElement->setAttribute ('class', 'noBorder');
		$ImageElement->setAttribute ('alt', '');
		if ($Title)
			$ImageElement->setAttribute ('title', $Title);

		if ($Link)
		{
			$Anchor = $Node->appendChild($DOM->createElement ('A'));
			$Anchor->setAttribute ('href', $Link);
			$Anchor->appendChild ($ImageElement);
		}
		else
		{
			$Node->appendChild ($ImageElement);
		}
	}
}


/**
* cSpecialField
* 
* Special fields cannot recieve input and can never be modified directly by the
* user (if they can be modified at all).  All have special software behind them
* to determine their values and most have display methods as well.
* 
* @author Tristan Hoare <thoare@aperio.com>
* @package Tables
*/
class cSpecialField extends cField
{
	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table the field belongs to
	* @param string $ColumnName - Database name of the column this field represents
	* @param string $DisplayName - User defined display name shown in the UI
	* @param int $MaxLength - Maximum character length of this field
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		$this->IsDatabaseField = false;
		$this->Searchable = false;
		$this->Sortable = false;
		$this->FieldType = 'Special';
		$this->Vocabulary = null;
		$this->HasVocabulary = false;

		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);
	}

	/**
	* Validates the given value for this field
	*
	* @param string $Value - The value to test
	* @param string $Message - Output parameter to contain specific messages about the value, if null it won't be set by the function
	* @param bool $RigidCheck - Forces more restrictive checks for what may be placed in the DB (rather than all values could potentially occur)
	* @return true if the value is valid, false otherwise
	*/
	// cSpecialField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		return false;
	}
	
	/**
	* Output HTML needed to display this field in an Edit context
	*
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Record		- The current record which could contain this field
	* @param array $OldRecord	- The record obtained from the database (or NULL)
	*/
	// cSpecialField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$Value = $this->GetValue($Record);

		$Node->appendChild ($DOM->createTextNode ($Value));
	}
}

/**
* cAnalysisField
* 
* @author Tristan Hoare <thoare@aperio.com>
* 
* @package Tables
*/
class cAnalysisField extends cSpecialField
{
	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table the field belongs to
	* @param string $ColumnName - Database name of the column this field represents
	* @param string $DisplayName - User defined display name shown in the UI
	* @param int $MaxLength - Maximum character length of this field
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);

		$this->ReadOnly = true;
		$this->IsSSP = true;
		$this->IsVirtual = true;
		$this->IsDatabaseField = true;
	}

	/**
	* Validates the given value for this field
	*
	* @param string $Value		- The value to test
	* @param string $Message	- Output parameter to contain specific messages about the value, if null it won't be set by the function
	* @param bool $RigidCheck	- Forces more restrictive checks for what may be placed in the DB (rather than all values could potentially occur)
	* 
	* @return true if the value is valid, false otherwise
	*/
	// cAnalysisField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if ($Value == '' || $Value == '[various]' || $RigidCheck == false)
			return true;

		// TODO: Validate

		return true;
	}

	/**
	* Output HTML needed to display this field in an Edit context
	*
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Record		- The current record which could contain this field
	* @param array $OldRecord	- The record obtained from the database (or NULL)
	*/
	// cAnalysisField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;

		$Ids = $this->GetImageIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$ImageId = $this->GetValue($Record);
		if ($ImageId == '')
			return;
		if ($ImageId == '[various]')
		{
			$Node->appendChild ($DOM->createTextNode ('[various]'));
			return;
		}

		$AnalysisResults = ADB_GetImageAnalysisData ($ImageId);

		$Node->setAttribute ('AutoUpdate', 'true');
		$Node->setAttribute ('ReadOnly', 1);

		$Table = $Node->appendChild ($DOM->createElement ('TABLE'));
		$Table->setAttribute ('class', 'ScoreTable');
		$TBody = $Table->appendChild ($DOM->createElement ('TBODY'));

		$Headers = $TBody->appendChild ($DOM->createElement ('TR'));
		$Data = $Table->appendChild ($DOM->createElement ('TR'));

		if (isset($AnalysisResults['AlgorithmResults']))
		{
			foreach ($AnalysisResults['AlgorithmResults'] as $Result)
			{
				$Row = $TBody->appendChild ($DOM->createElement ('TR'));
				$Row->appendChild ($DOM->createElement ('TH'))->appendChild ($DOM->createTextNode ($Result->AttributeName));
				$Row->appendChild ($DOM->createElement ('TD'))->appendChild ($DOM->createTextNode ((float)$Result->AttributeValue));
			}
		}
	}
}

/**
* cSSPScoreField
* 
* @author Tristan Hoare <thoare@aperio.com>
* 
* @package Tables
* 
* -08/29/08 thoare	Better checks for invalid scores. Better checks to see whether an update should or should not occur
*/
class cSSPScoreField extends cSpecialField
{
	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table the field belongs to
	* @param string $ColumnName - Database name of the column this field represents
	* @param string $DisplayName - User defined display name shown in the UI
	* @param int $MaxLength - Maximum character length of this field
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);
		$this->IsSSP = true;
		$this->IsVirtual = true;
		$this->IsDatabaseField = true;

		$this->AddAttribute ('onchange', 'if (CheckModified(this)) this.parentNode.parentNode.parentNode.parentNode.parentNode.setAttribute ("Modified", "true");');
		$this->AddAttribute ('onfocus', 'this.Focus = true;');
		$this->AddAttribute ('onblur', 'this.Focus = false;');
	}

	/**
	* Validates the given value for this field
	*
	* @param string $Value - The value to test
	* @param string $Message - Output parameter to contain specific messages about the value, if null it won't be set by the function
	* @param bool $RigidCheck - Forces more restrictive checks for what may be placed in the DB (rather than all values could potentially occur)
	* @return true if the value is valid, false otherwise
	*/
	// cSSPScoreField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if (is_array($Value) == false)
		{
			// If the record came from the database then the score is actually the image id (just a place holder)
			if (is_numeric($Value) || ($Value == '') || ($Value == '[various]') || ($RigidCheck == false))
				return true;
			$Message = 'Bad Value';
			return false;
		}

		// If the record is from an table edit (manual override) then the field is filled with an array of ($Score->Id => override)
 
		// Note: There should be a range check here, but we would need the imageID, the range check is done in SaveRecord.php
		foreach ($Value as $Override)
		{
			if (IsFloat($Override, false) == false)
			{
				$Message = 'not floating point';	// this gets overrided in ToHTML()
				return false;
			}
		}
		return true;
	}

	/**
	* Output HTML needed to display this field in an Edit context
	*
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Record		- The current record which could contain this field
	* @param array $OldRecord	- The record obtained from the database (or NULL)
	* @param bool $ReadOnly		- Whether the field should be displayed as editable or read-only
	* 
	*/
	// cSSPScoreField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;

		$DOM = $Node->ownerDocument;

		$Ids = $this->GetImageIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		if (count($Ids) == 0)
			return;
		if (count($Ids) > 1)
		{
			$Node->appendChild ($DOM->createTextNode ('[various]'));
			return;
		}
		$ImageId = $Ids[0];

		$Scores = ADB_GetImageScores ($ImageId, true);

		$Overrides = $this->GetValues($Record);
		if (is_array($Overrides) == false)
		{
			// If the record is from an table edit (manual override) then the field is filled with an array of ($Score->Id => override)
			// If the record came from the database then the score is actually the image id (just a place holder) & thus no overrides
			$Overrides = array();
		}

		$SortedScores = array ();
		$OldScores = array ();
		foreach ($Scores as $Score)
		{
			if ($Score->DisplayOrder > 0)
			{
				$SortedScores [(int)$Score->DisplayOrder] = $Score;
				$OldScores [(int)$Score->DisplayOrder] = $Score->Value;

				if (isset ($Overrides [$Score->Id]))
					$SortedScores [(int)$Score->DisplayOrder]->Value = $Overrides [$Score->Id];
			}
		}
		ksort ($SortedScores);
		$Scores = $SortedScores;

		$Editable = ($this->IsReadOnly($ReadOnly) == false);

		// Check the scores for errors
		$Error = false;
		$Messages = array ();

		if ($Editable)
		{
			foreach ($Scores as $Score)
			{
				$Score->Error = false;

				$Value = $Score->Value;
				if ($Value != '')
				{
					if (IsFloat($Value, false) == false)
					{
						$Error = true;
						$Score->Error = true;
						$Messages [] = "$Score->DisplayName: $Value is not floating point";
						continue;
					}
					$Value = (float)$Value; // convert string to float
				}

				$OldValue = (float) $OldScores [$Score->DisplayOrder];
				$Min = $Score->ValueMin != '' ? (float)$Score->ValueMin : 'null';
				$Max = $Score->ValueMax != '' ? (float)$Score->ValueMax : 'null';
				//$Step = $Score->ValueStep != '' ? (float)$Score->ValueStep : 'null';

				if (($Value != '') && $Score->Vocabulary)
				{
					$Vocabulary = explode ('|', $Score->Vocabulary);
					// convert strings to floating values
					for ($i=0; $i < count($Vocabulary); $i++)
						if (IsFloat ($Vocabulary[$i], false))
							$Vocabulary[$i] = (float)$Vocabulary[$i];

					// Ensure existing string is still in vocabulary
					if (!in_array ($Value, $Vocabulary, true))
					{
						$Error = true;
						$Score->Error = true;
						$Messages [] = "$Score->DisplayName: $Value is no longer a valid option";
						continue;
					}
				}

				if ($Value != $OldValue)
				{
					if ($Value !== '')
					{
						if ($Min != 'null' && $Value < $Min)
						{
							$Error = true;
							$Score->Error = true;
							$Messages [] = "$Score->DisplayName: $Value is too low (Min: $Min)";
						}
						elseif ($Max != 'null' && $Value > $Max)
						{
							$Error = true;
							$Score->Error = true;
							$Messages [] = "$Score->DisplayName: $Value is too high (Max: $Max)";
						}
					}
					else
					{
						$Error = true;
						$Score->Error = true;
						$Messages [] = "$Score->DisplayName: Score cannot be removed once set";
					}
				}
			}
		}

		// Draw the scores

		$Node->setAttribute ('AutoUpdate', 'true');
		$Node->setAttribute ('ReadOnly', $Editable ? '0' : '1');

		// Note, changed score element from <table> to <div> for rel 12 
		// because flexigrids (used in PALModules) can't handle <td>'s that are tables
		$ScoreDiv = $Node->appendChild ($DOM->createElement ('div'));
		$ScoreDiv->setAttribute ('class', 'ScoreDiv');

		foreach ($Scores as $Score)
		{
			$Value = $Score->Value;
			$OldValue = $OldScores [$Score->DisplayOrder];

			$Classes = $this->Class;

			if ($Score->IsManual == '1')
			{
				$Override = true;
				$Classes .= ' Override';
			}
			else
			{
				$Override = false;
			}

			if ($Score->IsCalculated == '1')
			{
				$Calculated = true;
				$Classes .= ' Calculated';
			}
			else
			{
				$Calculated = false;
			}

			if ($Score->MacroOutputId != '')
			{
				$Linked = true;
				$Classes .= ' Linked';
			}
			else
			{
				$Linked = false;
			}

			$ScoreDiv->appendChild ($DOM->createElement ('text'))->appendChild ($DOM->createTextNode ($Score->DisplayName . ' '));

			$DataCell = $ScoreDiv->appendChild ($DOM->createElement ('text'));
			$DataCell->setAttribute ('style', 'padding: 0px;');

			if ($Editable)
			{
				if ($Score->Vocabulary)
				{
					$Vocabulary = explode ('|', $Score->Vocabulary);
					// Do not display non numeric vocabs
					for ($i=0; $i < count($Vocabulary); $i++)
						if (is_numeric ($Vocabulary[$i]) == false)
							unset($Vocabulary[$i]);

					$Input = $DOM->createElement ('SELECT');
					$Input->setAttribute ('oldvalue', $OldValue !== '' ? $OldValue : '');

					// Add all options

					if (!in_array ($Value, $Vocabulary, true))
					{
						// Include no entry or bad entry
						$Option = $DOM->createElement ('OPTION');
						$Input->appendChild ($Option);
						$Option->setAttribute ('value', '');
						$Option->appendChild ($DOM->createTextNode ($Value));
					}

					foreach ($Vocabulary as $Text)
					{
						$Option = $DOM->createElement ('OPTION');
						$Input->appendChild ($Option);
						
						$Option->setAttribute ('value', (string)$Text);
						if ($Value == $Text)
						{
							$Option->setAttribute ('selected', true);
						}

						$Option->appendChild ($DOM->createTextNode ($Text));
					}
				}
				else
				{
					$Input = $DOM->createElement ('INPUT');
					$Input->setAttribute ('value', $Value);
					$Input->setAttribute ('oldvalue', $OldValue);
				}

				if ($Score->Error)
					$Classes .= ' Invalid';
				if (($Error) && ($Value != $OldValue))
					$Classes .= ' Modified';

				$Input->setAttribute ('align', 'right');
				$Input->setAttribute ('class', $Classes);
				$Input->setAttribute ('name', "SspScore[$Score->Id]");

				$this->AssignAttributes($Input);

				$DataCell->appendChild ($Input);
			}
			else
			{
				//SSP Score read-only display alignment
				$Original = 0;
				if ($Original)
				{
					$Output = $DataCell->appendChild ($DOM->createElement ('DIV'));
					$Output->setAttribute ('style', 'width:50px');
					$Output->setAttribute ('align', 'right');
					$Output->setAttribute ('class', $Classes);
					$Output->appendChild ($DOM->createTextNode ($Value));
				}
				else
				{
					$Output = $DataCell->appendChild ($DOM->createElement ('INPUT'));
					$Output->setAttribute ('style', 'width:6em');
					$Output->setAttribute ('disabled', '1');
					//$Output->setAttribute ('style', 'border:0');
					$Output->setAttribute ('value', $Value);
					$Output->setAttribute ('class', $Classes);
				}
			}

			if ($Linked || $Calculated)
			{
				$Img = $ScoreDiv->appendChild ($DOM->createElement ('IMG'));
				$Img->setAttribute ('src', $Override ? '/Images/Link-Brokens.png' : '/Images/Links.png');
			}
		}
		
		if (!empty ($Messages))
		{
			$Node->setAttribute ('error', implode ('<br/>', $Messages));
		}
	}
}

/**
* cSSPInterpretationField
* 
* @author Tristan Hoare <thoare@aperio.com>
* 
* @package Tables
* 
* -08/29/08 thoare	Better checks for invalid scores. Better checks to see whether an update should or should not occur
*/
class cSSPInterpretationField extends cSpecialField
{
	/**
	 * Set default values for the object based on inputs
	 *
	 * @param string $TableName - Name of the table the field belongs to
	 * @param string $ColumnName - Database name of the column this field represents
	 * @param string $DisplayName - User defined display name shown in the UI
	 * @param int $MaxLength - Maximum character length of this field
	 * @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	 */
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);
		$this->IsSSP = true;
		$this->IsVirtual = true;
		$this->IsDatabaseField = true;

		$this->AddAttribute ('onfocus', 'this.Focus = true;');
		$this->AddAttribute ('onblur', 'this.Focus = false;');
	}

	/**
	* Validates the given value for this field
	*
	* @param string $Value - The value to test
	* @param string $Message - Output parameter to contain specific messages about the value, if null it won't be set by the function
	* @param bool $RigidCheck - Forces more restrictive checks for what may be placed in the DB (rather than all values could potentially occur)
	* 
	* @return true if the value is valid, false otherwise
	*/
	// cSSPInterpretationField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if ($Value == '' || $Value == '[various]' || $RigidCheck == false)
			return true;
		
		// TODO: Validate
		
		return true;
	}

	/**
	* Output HTML needed to display this field in an Edit context
	*
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Record		- The current record which could contain this field
	* @param array $OldRecord	- The record obtained from the database (or NULL)
	* @param bool $ReadOnly		- Whether the field should be displayed as editable or read-only
	*/
	// cSSPInterpretationField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;

		$DOM = $Node->ownerDocument;

		$Ids = $this->GetImageIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		if (count($Ids) == 0)
			return;
		if (count($Ids) > 1)
		{
			$Node->appendChild ($DOM->createTextNode ('[various]'));
			return;
		}
		$ImageId = $Ids[0];

		$ReadOnly = $ReadOnly || $this->ReadOnly;

		$Node->setAttribute ('AutoUpdate', 'true');
		$Node->setAttribute ('ReadOnly', $ReadOnly ? '1' : '0');

		$Result = ADB_GetImageInterpretationData ($ImageId);
		if (!$Result)
			return;

		list ($Interpretation, $Configs) = $Result;

		$OldValue = $Interpretation->InterpretationText; // OldValue is always what is currently in the DataBase
		$Override = ($Interpretation->IsManual == 1);

		$Values = $this->GetValues($Record);

		$Value = isset($Values[$Interpretation->Id]) ? $Values[$Interpretation->Id] : $OldValue; // Current value can be user-defined, but is otherwise identical to OldValue

		if ($ReadOnly)
			$Node->appendChild ($DOM->createTextNode ($OldValue));
		else
		{
			$Select = $DOM->createElement ('SELECT');
			$Node->appendChild ($Select);
			$Select->setAttribute ('name', "SspInterpretation[$Interpretation->Id]");
			$Select->setAttribute ('oldvalue', $OldValue);

			$this->AssignAttributes($Select);

			$Vocabulary = array ();
			foreach ($Configs as $Config)
				$Vocabulary [] = $Config->InterpretationText;

			$Classes = '';

			if (!in_array ($Value, $Vocabulary))
			{
				$Option = $DOM->createElement ('OPTION');
				$Select->appendChild ($Option);
				$Option->setAttribute ('value', '');
				$Option->appendChild ($DOM->createTextNode ($Value));

				if ($Value !== '')
				{
					$Messages [] = "$this->DisplayName: $Value is no longer a valid interpretation";
					$Classes .= 'Invalid ';
				}
			}

			foreach ($Vocabulary as $Vocab)
			{
				$Option = $DOM->createElement ('OPTION');
				$Select->appendChild ($Option);
				
				$Option->setAttribute ('value', $Vocab);
				if ($Value == $Vocab)
					$Option->setAttribute ('selected', true);

				$Option->appendChild ($DOM->createTextNode ($Vocab));
			}

			if ($Override)
				$Classes .= 'Override ';
			$Select->setAttribute ('class', $Classes . 'Linked ');			

			if ($Value == $OldValue)
				$Select->setAttribute ('class', $Classes . "$this->Class");
			else
				$Select->setAttribute ('class', $Classes . "$this->Class Modified");
		}

		$Img = $Node->appendChild ($DOM->createElement ('IMG'));
		$Img->setAttribute ('src', $Override ? '/Images/Link-Brokens.png' : '/Images/Links.png');

		if (!empty ($Messages))
		{
			$Node->setAttribute ('error', implode ('<br/>', $Messages));
		}
	}
}

class cSsField extends cField
{
	/**
	 * Set default values for the object based on inputs
	 *
	 * @param int $SsFieldValueId - Id for $SsFieldValue (existing data will be found).
	 * @param int $SsFieldConfigId - Id for SsFieldConfig, (optional: SsFieldValue, or [TableName & FieldName] may be passed)
	 * @param string $SsTableName - alternative for SsFieldConfigId: TableName
	 * @param string $SsFieldName - alternative for $SsFieldConfigId: FieldName
	 * @param int $MaxLength - Maximum character length of this field
	 * @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	 * @author Mark Smaga <msmaga@aperio.com> 05/30/08
	 *
	 * -06/11/08 msmaga	removed 'dropdown' & 'help'
	 */

	public $Translate 		= array();		// key=>string translation array
	public $SsFieldValueId	= ''; 			
	public $SsFieldConfigId = '';			
	public $SsActionId		= '';
	public $SsConfigId		= '';
	public $Value 		= '';

	public function __construct ($SsFieldValueId = 0, $SsFieldConfigId = 0, $TableName = 'SsField', $Flags = cField::FLAGS_DEFAULT)
	{
		// check for null input
		if (($SsFieldValueId == 0) && ($SsFieldConfigId == 0)) // no input
			return;

		// check for bad input
		if ((!is_numeric($SsFieldValueId)) || (!is_numeric($SsFieldConfigId)))
			return;

		// get SsFieldValue data if Id passed
		if ($SsFieldValueId != 0)
		{
			$SsFieldValueRecord = ADB_GetRecordData('SsFieldValue', $SsFieldValueId);
			if ($SsFieldValueRecord != false)
			{
				$this->SsFieldValueId 	= $SsFieldValueId;
				$this->SsActionId 		= $SsFieldValueRecord['SsActionId'];
				$SsFieldConfigId 		= $SsFieldValueRecord['SsFieldConfigId'];
				$this->SsFieldConfigId 	= $SsFieldConfigId;
				$this->Value			= $SsFieldValueRecord['Value'];
			}
		}
		$this->SsFieldConfigId = $SsFieldConfigId;

		// Get Field and Display names
		$FieldName 		= '';
		$DisplayName 	= '';
		$SsFieldDisplayNames = ADB_ListSsFieldConfigs();
		foreach ($SsFieldDisplayNames as $SsFieldDisplayName)
		{
			if ($SsFieldDisplayName['Id'] == $SsFieldConfigId)
			{
				$FieldName = $SsFieldDisplayName['FieldName'];
				$DisplayName = $SsFieldDisplayName['FieldDisplayName'];
				break;
			}
		}

		parent::__construct ($TableName, $FieldName, $DisplayName, $Flags);
		$this->FieldType = 'Text';

		$this->ConstructTranslationTable($FieldName);
	}

	// cSsField
	private function ConstructTranslationTable($FieldName)
	{
		/**
		 * if this is a key field, create a table that will return string $StringValue, given int (key) $IdValue
		 */

		$Translate = &$this->Translate;
		$KeyColumns = array();
		$SsFieldConfigList = ADB_GetRecordList('SsFieldConfig', 'FieldName');
		foreach ($SsFieldConfigList as $SsConfig)
		{
			if ($SsConfig['FieldName'] == $FieldName)
			{
				$KeyColumns[$SsConfig['FieldName']]['FieldName']	= $SsConfig['FieldName'];
				$KeyColumns[$SsConfig['FieldName']]['Id'] 			= $SsConfig['Id'];
				break;
			}
		}

		// TBA: Add an ignore option
		// $Translate[0] = '(ignore)';
		foreach ($KeyColumns as $Column=>$Data)
		{
			// Trap 'pointers' to other tables
			switch ($Data['FieldName'])
			{
				case 'DataGroupId' :
					$List = ADB_GetRecordList('DataGroups', 'Name', array('Id', 'Name'));
					foreach ($List as $Item)
						$Translate[$Item['Id']] = $Item['Name'];
					break;

				case 'BodySiteId' :
					$List = ADB_GetRecordList('BodySite', 'Name');
					foreach ($List as $Item)
						$Translate[$Item['Id']] = $Item['Name'];
					break;

				case 'StainId' :
					$List = ADB_GetRecordList('Stain', 'ShortName', array('Id', 'ShortName'));
					foreach ($List as $Item)
					{
						$Name = $Item['ShortName'];
						$Translate[$Item['Id']] = $Name;
					}
					break;

				case 'GenieProjectId' :
					$List = ADB_GetRecordList('GenieProject', 'Name', array('Id', 'Name'));
					foreach ($List as $Item)
						$Translate[$Item['Id']] = $Item['Name'];
					break;

				case 'ImageTypeId' :
					$List = ADB_GetRecordList('ImageType', 'Name', array('Id', 'Name'));
					foreach ($List as $Item)
						$Translate[$Item['Id']] = $Item['Name'];
					break;

				case 'CustomerId' :
					$List = ADB_GetRecordList('Customers', 'Name', array('Id', 'Name'));
					foreach ($List as $Item)
						$Translate[$Item['Id']] = $Item['Name'];
					break;

				case 'SsConfigId' :
					$List = ADB_GetRecordList('SsConfig', 'Description', array('Id', 'Description'));
					foreach ($List as $Item)
					{
						$Name = $Item['Description'];
						$Translate[$Item['Id']] = $Name;
					}
					break;

				default :
					break;
			}
		}

		if (empty ($Translate))	// vocabulary-based dropdown
		{
			if (count($this->Vocabulary) > 0)
			{
				$DropDown[''] = '';			// null value when opening dropdown
				foreach ($this->Vocabulary as $Vocabulary)
					$DropDown[$Vocabulary] = $Vocabulary;
			}
		}
	}

	/**
	 * Validates the given value for this field
	 *
	 * @param string $Value - The value to test
	 * @param string $Message - Output parameter to contain specific messages about the value, if null it won't be set by the function
	 * @param bool $RigidCheck - Forces more restrictive checks for what may be placed in the DB (rather than all values could potentially occur)
	 * @return true if the value is valid, false otherwise
	 */
	// cSsField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if ($Value == '' || $Value == '[various]' || $RigidCheck == false)
			return true;

		// see if there's a matching value in Translate
		if (count ($this->Translate) > 0)
		{
			if (isset ($this->Translate[$Value]))
				return true;
			else 
			{
				$Message = "{$this->DisplayName} '$Value' doesn't exist";
				return false;
			}
		}

		// see if there's a matching value in Vocabulary
		if (count ($this->Vocabulary) > 0)
		{
			if (isset ($this->Vocabulary[$Value]))
				return true;
			else
			{
				$Message = "{$this->DisplayName} '$Value' doesn't exist";
				return false;
			}
		}

		return true;
	}

	/**
	 * Output HTML needed to display this field in an Edit context
	 *
	 * @param string $Value - What field value should get diplayed
	 * @param string $NameInput - Name to be given to the hidden input that contains the ColumnName
	 * @param string $ValueInput - Name given to the visible input that holds the field value
	 * @param bool $ReadOnly - Whether the field should be displayed as editable or read-only
	 * @param bool $VerticalFormat - Put Input area below DisplayName (not to the right of DisplayName)
	 */
	// cSsField
	public function EditHTML ($Value, $TableColumn = "", $SsValueInput = "", $ReadOnly = true, $VerticalFormat = false)
	{
		$EncodedValue = htmlentities ($Value, ENT_QUOTES, "UTF-8");

		echo "
		<th>", $this->DisplayName, ": </th>";

		if ($VerticalFormat)
			echo '<br />';

		echo "
		<td style='white-space:nowrap;'>";

		// Javascript-required id=name collection
		echo "
		<input type='hidden' class='cSsFieldConfigTable' value='$Value' id='$TableColumn' />";

		if ($ReadOnly || $this->ReadOnly)
		{
			// check translation table
			if (count ($this->Translate) > 0)
				$EncodedValue = isset($this->Translate[$Value]) ? htmlentities($this->Translate[$Value], ENT_QUOTES, 'UTF-8') : $EncodedValue;
			
			echo $EncodedValue;
		}
		else
		{	
			//	check & handle dropdown list
			$Translate = $this->Translate;
			if (count($Translate) > 0)
			{
				echo "
			<select name='$TableColumn' class=$this->Class OldValue='$Value' onkeyup='{$this->OnKeyUp}' onchange='CheckModified(this); SlideMacros();' >";
				// insert blank space at top of drop-down stack
				echo "
			<option value=''></option>";
				foreach ($this->Translate as $DropDownValue=>$DropDownName)
				{
					$EncodedValue = htmlentities ($DropDownValue, ENT_QUOTES, 'UTF-8');
					$EncodedName = htmlentities ($DropDownName, ENT_QUOTES, 'UTF-8');

					echo "
			<option value='$EncodedValue'";

					if ($Value == $DropDownValue)
						echo ' selected="selected"';

					echo ">$EncodedName</option>";
				}
				echo "
			</select>";
			}

			else	// normal text input field
			{
				echo "
			<input type='text' maxLength='{$this->MaxLength}' name='$TableColumn' value='$EncodedValue' class='$this->Class' OldValue='$Value' onkeyup='{$this->OnKeyUp}' onchange='CheckModified(this)' />";
			}
		}

		echo "
		</td>";
//		<td id='help'>{$this->SsTableName} {$this->DisplayName} parameter for this Image Scoring selection. </td>"
	}
}

/**
*
* Create field that defines a link for an SSP configuration
*
* @package Tables
*/
class cSSPConfigIdField extends cIdentityLinkField
{
	public function __construct ($FieldName='SsConfigId')
	{
		parent::__construct ('Image', $FieldName, 'SSP ID', '/PutSlideStainDefaults.php');

		// Override usual identity field behavior.  We do not display multiple SSPConfig Ids
		$this->Flags &= ~cField::FLAGS_NO_COMBINE;
		$this->CombineValues = true;
	}

	/**
	* {@inheritdoc}
	*/
	// cSSPConfigIdField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$SsConfigId = $this->GetValue($Record);
		if ($SsConfigId == '')
			return;
		if ($SsConfigId == '[various]')
		{
			$Node->appendChild ($DOM->createTextNode ('[various]'));
			return;
		}

		$SsConfigData = ADB_GetScoreConfig($SsConfigId, NULL);
		if (isset($SsConfigData['SsConfig']['Inactive']) && ($SsConfigData['SsConfig']['Inactive'] == '0'))
			$Active = true;
		else
			$Active = false;

		$Link = $DOM->createElement ('A');
		$Node->appendChild ($Link);
		if ($Active == false)
			$Link->setAttribute ('style', 'color: #FF8888');
		$Link->setAttribute ('href', "$this->Link?Ids[]=$SsConfigId");
		$Label = $Link->appendChild ($DOM->createTextNode ($SsConfigId));
	}
}


/**
* cCommentField
* 
* @author Mark Smaga <msmaga@aperio.com>
* 
* @package Tables
*/
class cCommentField extends cMemoField
{
	public $CommentId		= '';
	public $CommentType		= '';
	public $CommentName		= '';
	public $CommentText		= '';
	public $CommentOptions	= array();	/**	[CommentId]
										*		[Name]
										*		[Text]	**/
	public $SsActionId		= '';
	public $ValidTypes		= array();

	/**
	 * Set default values for the object based on inputs
	 *
	 * @param int $CommentId - Comment.Id if editing existing data
	 * @param int $SsActionId - SsAction.Id if adding SlideSpecific comment
	 * @param string	$CommentType 	{Default = 'Slide'}
	 * @param string $TableName - Name of the table the field belongs to
	 * @param string $ColumnName - Database name of the column this field represents
	 * @param string $DisplayName - User defined display name shown in the UI
	 * @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	 */
	public function __construct ($TableName = "", $ColumnName = "", $DisplayName = "", $Flags = cField::FLAGS_DEFAULT, $CommentId = '0', $SsActionId = '0')
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);

		$this->FieldType = "Comment";

		//	load $this->ValidTypes	
		$ValidTypes = &$this->ValidTypes;
		foreach ($_SESSION['HierarchyLevels'] as $Schema)
		{
			if (in_array($Schema->TableName, array ("Case", "Specimen", "Slide", "Project", 'TMA'))) // A list of tables that get canned comments
			{
				$ValidTypes[] = $Schema->TableName;

				//	if user has access to 'Slide', add 'Slide-Specific' as a valid $Type
				if ($Schema->TableName == 'Slide')
					$ValidTypes[] = SSTYPE;			// DEFINEd in Skeleton.php
			}
		}

		//	load info from CommentId, if passed
		if ($CommentId != 0)
		{
			$CommentRecord		= ADB_GetRecordData('Comments', $CommentId);
			
			$this->CommentId 	= $CommentId;
			$this->CommentName	= $CommentRecord['Name'];
			$this->CommentText	= $CommentRecord['CommentText'];
			$this->CommentType	= $CommentRecord['Type'];
			$SsActionId			= $CommentRecord['SsActionId'];

			//	load 'drop-down' data of valid Comment options (i.e. having same Type & SsActionId)
			$TotalValidComments	= 0;
			$CommentList		= ADB_GetFilteredRecordList('Comments', 0, 0, array('Id', 'Name', 'CommentText'), array('Type', 'SsActionId'), $FilterOperators=array('=', '='), array($this->CommentType, $SsActionId), array('Comments', 'Comments'), 'Name', 'Ascending', $TotalValidComments);

			$Option = &$this->CommentOptions;
			foreach ($CommentList as $Data)
			{
				$Option[$Data['Id']]['Name']	= $Data['Name'];
				$Option[$Data['Id']]['Text']	= $Data['CommentText'];
			}
		}

		//	load info from SsActionId, if passed or determined
		if ($SsActionId != 0)
		{
			$this->SsActionId		= $SsActionId;
		}		
	}

	/**
	 * Output HTML needed to display this field in a multiple-select dropdown Edit context
	 *
	 * @param DOMElement $Node	- DOM Element to populate
	 * @param array $Record		- The current record which could contain this field
	 * @param array $OldRecord	- The record obtained from the database (or NULL)
	 * @param bool $ReadOnly	- Whether the field should be displayed as editable or read-only
	 */
	// cCommentField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$this->PrimaryKey = GetPrimaryKeyValueFromRecordArray($this->RefTableName, $this->ColumnName, $Record);
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$Value = $this->GetValue($Record);
		$Value = str_replace (array ("\r\n", "\r"), "\n", $Value);

		// Load drop-down canned Comments List
		$CommentsList[] = array('Id'=>'', 'Name'=>'', 'CommentText'=>'');	// Include a blank row
		if (empty($Ids) == false)
		{
			if ($this->TableName == 'Slide')
			{
				// Get SSP comments (if any)
				$RecordImages = ADB_GetRecordImages ($Ids[0], 'Slide');
				if (isset ($RecordImages [0]['ImageId']))
				{
					$ImageId = $RecordImages [0]['ImageId'];
					$SSPCanned = ADB_ListCannedCommentsForImage($ImageId);
					$CommentsList = array_merge($CommentsList, $SSPCanned);
				}
			}
		}
		// Get table generic comments (if any)
		$TableCanned = ADB_ListCannedComments(0, $this->TableName);
		// merge the comments, can't do an array_merge, or we can get duplicate comments
		foreach($TableCanned as $Tc)
		{
			if (in_array($Tc, $CommentsList) == false)
			{
				$CommentsList = array_merge($CommentsList, array($Tc));
			}
		}

		$DOM = $Node->ownerDocument;

		if ($ReadOnly || $this->ReadOnly)
		{
			if ($Value != '')
			{
				$Lines = preg_split ("/[\r\n]+/", $Value);

				$LastBR = null;

				foreach ($Lines as $Line)
				{
					$Node->appendChild ($DOM->createTextNode ($Line));
					$LastBR = $Node->appendChild ($DOM->createElement ('BR'));
				}

				if ($LastBR)
					$Node->removeChild ($LastBR);
			}
		}
		else
		{
			$OldValue = $this->GetOldValue($OldRecord, $Value);
			$OldValue = str_replace (array ("\r\n", "\r"), "\n", $OldValue);

			//	make a drop-down only if there are data to show
			if (count($CommentsList) > 1)
			{
				$Node->setAttribute ('class', 'CommentId');
				$Select = $Node->appendChild ($DOM->createElement ('SELECT'));			
				$Select->setAttribute ('onchange', 'var Text = this.nextSibling.nextSibling; Text.value += (Text.value ? " " : "") + this.value; addClass (Text, "Modified"); this.selectedIndex = 0;');
				
				foreach ($CommentsList as $Comment)
				{
					$Option = $Select->appendChild ($DOM->createElement ('OPTION'));
					$Option->setAttribute ('value', $Comment['CommentText']);
					$Option->appendChild ($DOM->createTextNode ($Comment['Name']));
					
					if ($Comment['CommentText'] == $Value)
						$Option->setAttribute ('selected', 'true');
				}
				$Node->appendChild ($DOM->createElement ('BR'));
			}

			// add textarea
			$Message = '';
			$Classes = $this->Class;
			if ($Value != $OldValue)
				$Classes .= ' Modified';
			if (!$this->IsLegalValue ($Value, $Message))
			{
				$Classes .= ' Invalid';
				$Node->setAttribute ('error', $Message);
			}


			$TextArea = $Node->appendChild ($DOM->createElement ('TEXTAREA'));
			//ids in html need to be: unique, and obey html id rules  http://www.w3.org/TR/html4/types.html#type-id 
			$TextArea->setAttribute ('id', $this->GetUniqueIdentifier());
			$TextArea->setAttribute ('name', $this->ColumnName);
			$TextArea->setAttribute ('class', $Classes);
			$TextArea->setAttribute ('oldvalue', $OldValue);
			$TextArea->setAttribute ('PrimaryKey', (is_array($this->PrimaryKey) ? join(",", $this->PrimaryKey) : $this->PrimaryKey)); 

			$this->AssignAttributes($TextArea);

			$TextArea->appendChild ($DOM->createTextNode ($Value));
		}
	}

	/**	editable memo-type field of data, with drop-down of predefined comments
	*	string Value = original comment string
	*	int	Readonly = display only (versus Edit)
	*/
	// cCommentField
	public function EditHTML ($Value = "", $ReadOnly = false)
	{
		$Id = $this->CommentId;
		$Options = $this->CommentOptions;

		if ($ReadOnly)	// no drop-down for simple display
			echo "
			<td class='CommentId'>$Value/td>";

		else			// editable
		{
			echo "
			<td class='CommentId'>
				<select class='EditRecord' onchange='pickComment(this);'>";	

			foreach ($Options as $OptionId=>$Data)
				echo "
					<option value='{$Data['Text']}</option>";
			echo "
				</select><br>
				<textarea OldValue='$Value' onchange='CheckModified (this);'>$Value</textarea>
			</td>";
		}
	}
}	// cCommentField

/**
* cForeignField
* 
* Foreign fields are fields which display name values from another table, but
* contain id values referencing that table.  For example, a records DataGroup
* field will show the Data Group name to the user, but contain the DataGroups
* ID in the actual table.  As such, the field contents must be constrained to
* values already contained within the DataGroups table.
* 
* @author Tristan Hoare <thoare@aperio.com>
* @package Tables
*/
class cForeignField extends cField
{
	/**
	 * Table to lookup data value, ex: MacroId would have LookupTable 'Macro'
	 */
	public $LookupTable = '';			
	/**
	 * Column to lookup data value, ex: MacroId would have LookupColumn 'MacroName'
	 */
	public $LookupColumn = 'Name';		
	/**
	 * The FK Source Table Name
	 *     EG:  Specimen   in the DownToSpecimenByParentId.UpToBodySiteByBodySiteId.Name
	 */
	public $ForeignKeyTableName = '';
	/**
	 * The FK Source Column Name
	 *    EG:  BodySiteId   in the DownToSpecimenByParentId.UpToBodySiteByBodySiteId.Name
	 */
	public $ForeignKeyColumnName = '';
	/**
	 * The path that is taken to get to the Field.
	 * This allows cViewSchemas to use the existing cForeignField concretes
	 * For example:  
	 *      If a view has  CaseDownToSpecimenByParentId then $DownToSyntax will be DownToSpecimenByParentId 
	 * @var string Empty if not a DataView
	 */
	public $DownToSyntax = '';	

	/**
	* {@inheritdoc}
	*/
	// cForeignField
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags, $LookupTable, $LookupColumn = 'Name', $DownToSyntax = '')
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);

		$this->LookupTable = $LookupTable;
		$this->LookupColumn = $LookupColumn;
		$this->DownToSyntax = $DownToSyntax;

		$this->FieldType = 'Foreign';
		$this->AjaxDropDown = true;
		$this->Vocabulary = null;
		$this->HasVocabulary = false;
		$this->ValidFilters = array(
			'-- All --' 	=> 	'All', 
			'equal to'		=>	'=',
			'not equal to' 	=>	'<>');
		$this->FieldPath = $DownToSyntax . 'UpTo' . $LookupTable . 'By' . $ColumnName . '.' . $LookupColumn;
		$this->FieldPath2 = $ColumnName;	// Also retrieve the Id so subsequent logic can use it
		$this->SortPath = $this->FieldPath;

		// Create the Database reader used for the field's select options
		$this->DBReader = new cDatabaseReader('GetFilteredRecordList', $LookupTable);
		$this->DBReader->AddColumn('Id');
		$this->DBReader->AddColumn($LookupColumn);
		$this->DBReader->SetSort($LookupColumn, 'Ascending');
		$this->DBReader->SetCache(true);
		//  For the foreign fields enable searching for lkup data 
		$this->AddAttribute ('onkeyup', "AjaxRequestMatches('$this->RefTableName','$this->RefColumnName',this, event, true)");      
		$this->AddAttribute ('autocomplete', 'off');
		//the cField ctor() sets these before they have been updated 
		//this is cleaner that moving code before the parent::__construct()
		$this->ClearAttribute('DownToSyntax');
		$this->AddAttribute('DownToSyntax', $this->FieldPath);        
	}


	// cForeignField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		//The primary key of a foreign key is still the table containing the FK
		//EX:  Specimen.BodySiteId   The FK on that input is : Specimen.Id        
		if (!empty($this->DownToSyntax))
		{
			$this->PrimaryKey = GetPrimaryKeyValueFromRecordArray($this->RefTableName, $this->DownToSyntax, $Record);
		}
		else
		{
			$PK = GetPrimaryKeyForSqlTable($this->RefTableName);
			if (array_key_exists($PK, $Record))
			{
				$this->PrimaryKey = $Record[$PK];
			}
			else
			{
				$this->PrimaryKey = NULL;
			}            
		}
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;

		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$Value = $this->GetValue($Record);

		if ($ReadOnly || $this->ReadOnly)
		{
			$Node->appendChild ($DOM->createTextNode ($Value));
			return;
		}

		$OldValue = $this->GetOldValue($OldRecord, $Value);

		$Classes = $this->Class;
		if ($Value != $OldValue)
		{
			$Classes .= ' Modified';
		}

		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'text');
		$Input->setAttribute ('name', $this->ColumnName);        
		//ids in html need to be: unique, and obey html id rules  http://www.w3.org/TR/html4/types.html#type-id 
		$Input->setAttribute ('id', $this->GetUniqueIdentifier());
		$Input->setAttribute ('class', $Classes);
		$Input->setAttribute ('value', $Value);
		$Input->setAttribute ('oldvalue', $OldValue);
		$Input->setAttribute ('maxLength', $this->MaxLength);

		$this->AssignAttributes($Input);

		$SspWarning = $this->IsSspConfigField($Value, $Record);
		if ($SspWarning)
			$Input->setAttribute ('getApproval', $SspWarning);
		$Input->setAttribute ('modifyCheck', 1);
				
		$Input->setAttribute ('PrimaryKey', (is_array($this->PrimaryKey) ? join(",", $this->PrimaryKey) : $this->PrimaryKey)); 

		$Input->setAttribute('ForeignKeyTableName', $this->ForeignKeyTableName);
		$Input->setAttribute('ForeignKeyColumnName', $this->ForeignKeyColumnName);                            

		$Input->setAttribute('TableName', $this->LookupTable); 
		$Input->setAttribute('ColumnName', $this->LookupColumn);        
	}

	// Override to return the displayed text or the id
	// cForeignField
	protected function GetValue($Record)
	{
		$Value = $this->Default;
		foreach ($Record as $RecordRow)
		{
			//If $Record is an array of records
			if (is_array($RecordRow))
			{                
				$OldPrimaryKey = GetPrimaryKeyValueFromRecordArray($this->RefTableName, $this->DownToSyntax, $RecordRow);
				if ($RecordRow[$this->FieldPath] && ($OldPrimaryKey === $this->PrimaryKey))
				{
					return $RecordRow[$this->FieldPath];                    
				}
			}
			else
			{                                
				if (isset ($Record[$this->FieldPath]) == false)
				{
					$ColumnName = $this->ColumnName;
					if (isset($Record[$ColumnName]))
						$Record[$this->FieldPath] = $this->GetDisplayText($Record[$ColumnName]);
				}                
				return parent::GetValue($Record);
			}
		}        
	}

	// Return an array of matches in the database for SearchString
	// cForeignField
	public function GetMatches($SearchString, $NumToReturn)
	{
		if ($this->HasAjaxDropDown() == false)
			return array();

		$this->DBReader->SetRecordsPerPage($NumToReturn);
		$this->DBReader->SetFilter($this->LookupTable, $this->LookupColumn, 'LIKE', $SearchString);
		$Matches = $this->DBReader->GetRecords(1);

		if (empty($Matches))
		{
			if (strncmp($SearchString, '[various]', strlen($SearchString)) == 0)
				return array('[various]');
			// else This type of field requires a match
			return NULL;
		}

		return $this->FormatMatches($Matches, $this->LookupColumn, $SearchString);
	}

	// Convert from index to enumeration
	// cForeignField
	public function GetDisplayText ($Value)
	{
		// need to check for the valid numeric data to avoid data server errors
		// since ADB_GetRecordData uses this value to compare it with ID column
		if (is_numeric($Value) && $Value > 0)
		{
			$Record = ADB_GetRecordData ($this->LookupTable, $Value);
			$DisplayValue = $Record [$this->LookupColumn];
			return $DisplayValue;
		}

		// Probably the enumeration
		return $Value;
	}

	// Convert from enumeration (or entered text) to index
	// cForeignField
	public function ConvertValue(&$Value)
	{
		if ($Value == '' || $Value == '[various]')
			return NULL;
		if ($this->MakeValueLegal($Value))
			return NULL;
		return "'$Value' is not in the list of choices";
	}

	// cForeignField
	public function IsLegalValue (&$Value, &$Message, $RigidCheck = true)
	{
		if ($Value == '' || $Value == '[various]' || $RigidCheck == false)
			return true;

		if (is_numeric($Value) == false)
		{
			$Message = "'$Value' is not in the list of choices";
			return false;
		}

		// see if there's a matching Record
		$Record = GetRecord($this->LookupTable, 'Id', $Value);
		if ($Record == NULL)
		{
			if ($this->LookupTable == 'DataGroups')
			{
				// GetRecord for DataGroups doesn't return private DataGroups (those whose ParentDataGroupId is not null)
				// Private DataGroups are returned only if a filter with Id and/or ParentDataGroupId is used.
				$Record = ADB_GetFilteredRecordList($this->LookupTable, 0, 0, array('Id'), array('Id'), array('='), array($Value), array($this->LookupTable));
				if (count($Record) != 1)
					$Record = NULL;
			}
			if ($Record == NULL)
			{
				$Message = "'$Value' is not in the list of choices";
				return false;
			}
		}
		return true;
	}

	// Convert the text value into its ID
	// cForeignField
	public function MakeValueLegal(&$Value)
	{
		// Get the ID from the database
		$Record = GetRecord($this->LookupTable, $this->LookupColumn, $Value);
		if ($Record)
		{
			// Found
			$Value = $Record->Id;
			return true;
		}
		// Can't find it or create it
		return false;
	}

	/**
	* {@inheritdoc}
	*/
	// cForeignField
	public function GetSearchInputHTML (DOMDocument $DOM, DOMElement $Node, $Value, $Value2, $Disabled, $Disabled2)
	{
		if ($this->SavedEncoded)
			$Value = xmldecode ($Value);

		// Value is an index into the table
		$Record = GetRecord($this->LookupTable, 'Id', $Value);
		if ($Record)
		{
			$TextColumn = $this->LookupColumn;
			$Text = $Record->$TextColumn;
		}
		else
			$Text = '';

		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'text');
		$Input->setAttribute ('value', $Text);
		$Input->setAttribute ('maxLength', $this->MaxLength);
		$Input->setAttribute ('name', 'FieldValue[]');
		$Input->setAttribute ('autocomplete', 'off');
		$Input->setAttribute ('class', 'searchinput');
		// perform  searching ''onkeyup'' only on fields that have lkup dropdown menu set 
		$EnableLkupSearch = $this->HasAjaxDropDown() ? 'true' : 'false';	
		$Input->setAttribute ('onkeyup', "SetSearchOperator('$this->RefTableName','$this->RefColumnName'); AjaxRequestMatches('$this->RefTableName','$this->RefColumnName',this, event, $EnableLkupSearch)");
		if ($Disabled)
			$Input->setAttribute ('disabled', 'true');

		// There is no range operator for foreign fields, but we must create a Value2 field to pass to the processing pages
		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'hidden');
		$Input->setAttribute ('name', 'FieldValue2[]');
		if ($Disabled)
			$Input->setAttribute ('disabled', 'true');
	}


	// cForeignField
	public function GetFolderHTML (DOMDocument $DOM, DOMElement $Node, $Index, $Value, $Open)
	{
		$DisplayValue = '(empty)';
		if ($Value > 0)
		{
			$Total = 0;
			$Record = ADB_GetRecordData ($this->LookupTable, $Value);

			$DisplayValue = $Record [$this->LookupColumn];
		}

		if ($this->SavedEncoded)
			$Value = xmldecode ($Value);

		if ($Open)
			$Node->setAttribute ('class', 'Icon-Open');
		else
			$Node->setAttribute ('class', 'Icon');

		$Link = $DOM->createElement ('A');
		$Node->appendChild ($Link);
		$Link->setAttribute ('onclick', 'OpenFolder (this);');
		$Link->setAttribute ('href', '#');
		$Link->setAttribute ('index', $Index);
		$Link->setAttribute ('column', $this->ColumnName);
		$Link->setAttribute ('value', $Value);

		$Img = $DOM->createElement ('IMG');
		$Link->appendChild ($Img);
		//$Img->setAttribute ('border', '0');
		if ($Open)
			$Img->setAttribute ('src', '/Images/OpenFolder.gif');
		else
			$Img->setAttribute ('src', '/Images/ClosedFolder.gif');

		$Link->appendChild ($DOM->createElement ('BR'));
		$Link->appendChild ($DOM->createTextNode ($DisplayValue));
	}
}

/**
* cCustomerField
* 
* The Customer field is a field which can only contain a name which has already
* been defined in the Customers table in the database.  It requires specialized
* checking and display methods to ensure this.
* 
* @package Tables
*/
class cCustomerField extends cForeignField
{
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $MaxLength = 0, $Flags = cField::FLAGS_DEFAULT, $DownToSyntax = '', $DownFromTable = NULL)
	{                
		parent::__construct ( isset($DownFromTable) ?  $DownFromTable : $TableName , $ColumnName, $DisplayName, $Flags, 'Customers', 'Name', $DownToSyntax);                      
		$this->ForeignKeyTableName = $DownFromTable;
		$this->ForeignKeyColumnName = $ColumnName;         
	}        
}

/**
* cDataGroupField
* 
* The DataGroup field can only be populated by a name defined in the DataGroups
* table in the database.  The back-end uses the DataGroup Id while presenting a
* user with only the DataGroup names in a drop-down.
* 
* @package Tables
*/
class cDataGroupField extends cForeignField
{
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $MaxLength = 0, $Flags = cField::FLAGS_DEFAULT, $DownToSyntax = '', $DownFromTable = NULL)
	{
		$Flags |= cField::FLAGS_CRITICAL;
		parent::__construct (isset($DownFromTable) ?  $DownFromTable : $TableName, $ColumnName, $DisplayName, $Flags, 'DataGroups', 'Name', $DownToSyntax);

		$this->Default = $_SESSION['User']['DataGroupDefaultId'];

		// Create the special Database reader used for the AJAX field expansions.
		// This reader only gets datagroup names for which the user has full control.
		$this->DBMatches = new cDatabaseReader('GetFilteredRecordList', 'DataGroups');
		$this->DBMatches->AddColumn('Name');
		$this->DBMatches->SetSort('Name', 'Ascending');
		$this->DBMatches->AddNode('SelectListColumnsOnly', 1);
		$this->DBMatches->SetCache(true);
				
		$this->ForeignKeyTableName = (isset($DownFromTable) ? $DownFromTable : $TableName);
		$this->ForeignKeyColumnName = 'DataGroupId';        
	}


	// Override to only return DataGroups for which the user has full permission.
	// cDataGroupField
	public function GetMatches($SearchString, $NumToReturn)
	{
		if ($this->HasAjaxDropDown() == false)
			return array();

		$this->DBMatches->SetRecordsPerPage($NumToReturn);
		$this->DBMatches->SetFilter('DataGroups', 'DownToAccessLevelsEffectiveByDataGroupId.AccessFlags', '=', 6);	// only full access
		$this->DBMatches->AddFilter('DataGroups', 'DownToAccessLevelsEffectiveByDataGroupId.UserId', '=', $_SESSION['UserId']); // for the current user
		$this->DBMatches->AddFilter('DataGroups', 'Name', 'LIKE', $SearchString);
		// Do not display workflow root DataGroups
		$this->DBMatches->AddFilter('DataGroups', 'Name', 'NOTLIKE', '_HCIC');
		$this->DBMatches->AddFilter('DataGroups', 'Name', 'NOTLIKE', '_HCOC');
		$Matches = $this->DBMatches->GetRecords(1);

		if (empty($Matches))
		{
			if (strncmp($SearchString, '[various]', strlen($SearchString)) == 0)
				return array('[various]');
			// else This type of field requires a match
			return NULL;
		}

		return $this->FormatMatches($Matches, 'Name', $SearchString);
	}
}


/**
* cBodySiteField
* 
* @package Tables
*/
class cBodySiteField extends cForeignField
{                  
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT, $DownToSyntax = '', $DownFromTable = NULL)
	{
		parent::__construct ( isset($DownFromTable) ?  $DownFromTable : $TableName , $ColumnName, $DisplayName, $Flags, 'BodySite', 'Name', $DownToSyntax);
		$this->ForeignKeyTableName = (isset($DownFromTable) ? $DownFromTable : $TableName);
		$this->ForeignKeyColumnName = 'BodySiteId';
	}

	// Convert the text value into its ID
	// cBodySiteField
	public function MakeValueLegal(&$Value)
	{
		if (parent::MakeValueLegal($Value) == true)
			return true;

		 // stain was not found.  For SecondSlide, we should create it automatically
		if (GetSecondSlideServerEnabled())
		{
			$BodySiteData = array();
			$BodySiteData['Name'] = trim($Value);
			$BodySiteId = ADB_PutRecordData('BodySite', $BodySiteData);

			if ($BodySiteId > 0)
			{
				// bodysite successfully created, use bodysite ID.
				$Value = $BodySiteId;
				return true;
			}
		}

		// Can't find it or create it
		return false;
	}
}

/**
* cStainField
* 
* @package Tables
*/
class cStainField extends cForeignField
{
	// cStainField
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT, $DownToSyntax = '', $DownFromTable = NULL, $SortColumn = 'DisplayOrder')
	{
		parent::__construct (isset($DownFromTable) ?  $DownFromTable : $TableName, $ColumnName, $DisplayName, $Flags, 'Stain', 'ShortName', $DownToSyntax);
		$this->SortPath = $DownToSyntax. 'UpTo' . $this->LookupTable . 'By' . $ColumnName . '.' . $SortColumn;
		$this->ForeignKeyTableName = (isset($DownFromTable) ? $DownFromTable : $TableName);
		$this->ForeignKeyColumnName = 'StainId';        
	}

	// Convert the text value into its ID
	// cStainField
	public function MakeValueLegal(&$Value)
	{
		if (parent::MakeValueLegal($Value) == true)
			return true;

		 // stain was not found.  For SecondSlide, we should create it automatically
		if (GetSecondSlideServerEnabled())
		{
			$StainData = array();
			$StainData['ShortName'] = trim($Value);
			$StainData['LongName'] = trim($Value);
			$StainId = ADB_PutRecordData('Stain', $StainData);

			if ($StainId > 0)
			{
				// stain successfully created, use Stain ID.
				$Value = $StainId;
				return true;
			}
		}

		// Can't find it or create it
		return false;
	}
}

/**
* cJobStatusField
* 
* @author Mark Smaga <msmaga@aperio.com
* @package Tables 
*/
class cJobStatusField extends cField
{
	/**
	* {@inheritdoc}
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);

		$this->Vocabulary = array('Completed', 'Cancelled', 'Submitted', 'Failed');	// Lodged here to avoid iteration through all images & removing duplicates. 

		$this->AddAttribute ('onchange', "SetSearchOperator('$this->RefTableName','$this->RefColumnName');");
	}

	/**
	* {@inheritdoc}
	*/
	// cJobStatusField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;

		$Ids = $this->GetImageIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);

		$Value = $this->GetValue($Record);

		if (count ($Ids) == 1)
		{
			$Link = $DOM->createElement ('A');
			$Node->appendChild ($Link);
			$Link->setAttribute ('href', '/AnalysisJobs.php?ImageId=' . $Ids [0]);
			$Link->appendChild ($DOM->createTextNode ($Value));
		}
		else
		{
			$Node->appendChild ($DOM->createTextNode ($Value));
		}
	}

	/**
	* {@inheritdoc}
	*/
	// cJobStatusField
	public function GetSearchInputHTML (DOMDocument $DOM, DOMElement $Node, $Value, $Value2, $Disabled, $Disabled2)
	{
		$Input = $Node->appendChild ($DOM->createElement ('SELECT'));
		$Input->setAttribute ('type', 'text');
		$Input->setAttribute ('name', 'FieldValue[]');

		$this->AssignAttributes($Input);

		if ($Disabled)
			$Input->setAttribute ('disabled', 'true');

		$Input->appendChild ($DOM->createElement ('OPTION'));

		foreach ($this->Vocabulary as $ThisValue)
		{
			$Option = $Input->appendChild ($DOM->createElement ('OPTION'));
			$Option->setAttribute ('value', $ThisValue);
			if ($Value == $ThisValue)
				$Option->setAttribute ('selected', 'true');
			$Option->appendChild ($DOM->createTextNode ($ThisValue));
		}

		$Input = $Node->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('type', 'hidden');
		$Input->setAttribute ('name', 'FieldValue2[]');
		if ($Disabled)
			$Input->setAttribute ('disabled', 'true');
	}

	/**
	* {@inheritdoc}
	*/
	// cJobStatusField
	public function GetFolderHTML (DOMDocument $DOM, DOMElement $Node, $Index, $Value, $Open)
	{
		if ($this->SavedEncoded)
			$Value = xmldecode ($Value);

		if ($Open)
			$Node->setAttribute ('class', 'Icon-Open');
		else
			$Node->setAttribute ('class', 'Icon');

		$Link = $DOM->createElement ('A');
		$Node->appendChild ($Link);
		$Link->setAttribute ('onclick', 'OpenFolder (this);');
		$Link->setAttribute ('href', '#');
		$Link->setAttribute ('index', $Index);
		$Link->setAttribute ('column', $this->ColumnName);
		$Link->setAttribute ('value', $Value);

		$Img = $DOM->createElement ('IMG');
		$Link->appendChild ($Img);
		//$Img->setAttribute ('border', '0');
		if ($Open)
			$Img->setAttribute ('src', '/Images/OpenFolder.gif');
		else
			$Img->setAttribute ('src', '/Images/ClosedFolder.gif');

		$Link->appendChild ($DOM->createElement ('BR'));

		if ($Value != '')
			$Link->appendChild ($DOM->createTextNode ($Value));
		else
			$Link->appendChild ($DOM->createTextNode ('(empty)'));
	}
}

/**
* cCasePriorityField
* 
* @package Tables
*/
class cCasePriorityField extends cForeignField
{
	// cCasePriorityField
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT, $DownToSyntax = '', $DownFromTable = NULL, $SortCol, $ViewName)
	{
		parent::__construct (!empty($DownFromTable) ?  $DownFromTable : $TableName, $ColumnName, $DisplayName, $Flags, 'CasePriority', 'Name', $DownToSyntax);
		$this->SortPath = $DownToSyntax. 'UpTo' . $this->LookupTable . 'By' . $ColumnName . '.' . $SortCol;
		$this->ForeignKeyTableName = 'Case';
		$this->ForeignKeyColumnName = 'PriorityId';
		// For Case Priority enable searching for lkup data
		 $this->ClearAttribute('onkeyup');
		$this->AddAttribute ('onkeyup', "AjaxRequestMatches('$ViewName','$this->RefColumnName',this, event,true)");
	
	}
}


/**
* cCustomField
*
* Create a dummy field that acts as a placeholder.  This field should be inherited for custom code.
*
* @package Tables
*/
class cCustomField extends cField
{
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);
		
		$this->FieldType = 'Custom';
		$this->AjaxDropDown = false;
		$this->Grouped = false;
		$this->Sortable = false;
		$this->Vocabulary = null;
		$this->HasVocabulary = false;
	}

	/**
	* Output HTML needed to display this field in an Edit context
	*
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Record		- Record from the database
	* @param array $OldRecord	- The record obtained from the database (or NULL)
	*/
	// cCustomField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;
		// Only called if no override
		$DOM = $Node->ownerDocument;
		$Node->appendChild ($DOM->createTextNode (' '));
	}
}

/**
* cTaskField
*
* Create a field to display tasks, i.e. 'Delete'
*
* @package Tables
*/
class cTaskField extends cField
{
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);

		$this->Sortable = false;
		$this->IsExportable = false;
		$this->IsDatabaseField = false;
		$this->DisplayOnlyInList = true;
	}
}


// Display 'Assign' link for assigning new parent/grandparent
class cAssignField extends cIdentityField
{
	protected $LinkText;
	
	public function __construct ($TableName, $ColumnName, $DisplayName, $NodeName, $URL, $LinkText = "Assign")
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, cField::FLAGS_DEFAULT);

		$this->LinkText = $LinkText;
		$this->NodeName = $NodeName;
		$this->URL = $URL;

		$this->Position = 1;
		$this->RoleAccess = 'F';
		$this->IsDatabaseField = false;
		$this->IsStatic = true;
		$this->Sortable = false;
		$this->SetState();
	}

	// cAssignField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$ReadOnly = isset($Options['ReadOnly']) ? $Options['ReadOnly'] : true;

		$DOM = $Node->ownerDocument;

		$Ids = $this->GetIds($Record);
		$this->SetNodeAttributes ($Node, $Ids);
		$Id = $Ids[0];

		$Link = $DOM->createElement ('A');
		$Node->appendChild ($Link);
		$Link->setAttribute ('href', '#');
		$Link->setAttribute ('onclick', "javascript:SubmitNode('$this->NodeName', '$this->URL?Id=$Id');");
		$Link->appendChild ($DOM->createTextNode ($this->LinkText));
	}
}

//
// Functions
//

function UpdateLastChangeFields($Node, $TableName, $Id, $IsLink = true)
{
	$DOM = $Node->ownerDocument;

	$AuditTableSchema = GetTableObj('vwAuditTrail');
	list($DateTime, $Rfc) = $AuditTableSchema->GetLastUpdate($TableName, $Id);
	if ($DateTime == '')
	{
		$LastChangeField = new cTextField($TableName, 'LastChange', 'No audit trail for this record', 255, cField::FLAGS_READONLY);
		return array($LastChangeField, null);
	}

	$Record = array();

	$Date = substr($DateTime, 0, strpos($DateTime, 'T'));
	$Link = '/AuditList.php?TableName=' . $TableName . '&AuditInserts&AuditUpdates&AuditDeletes'
					. '&AuditStartDate=' . $Date . ' 00:00:00' . '&AuditStopDate=' . $Date . ' 23:59:59'
					. '&Ids[]=' . $Id;
	if ($IsLink)
	{
		$LastChangeField = new cLinkField($TableName, 'LastChange', 'Last Change on', $Date, $Link);				
	}
	else 
	{
		$LastChangeField = new cTextField($TableName, 'LastChange', 'Last Change on', '','',$Date);
	}

	$Node->appendChild ($DOM->createTextNode ($LastChangeField->DisplayName . ':'));
	$Options = array('ReadOnly' => true);
	$LastChangeField->ToHTML ($Node, $Record, NULL, $Options);

	
	$RfcField = null;
	if (IsConfigured('ReasonForChange') || ($Rfc != ''))
	{
		$Record['RfcValue'] = $Rfc;
		$RfcField = new cTextField($TableName, 'RfcValue', ', Reason for Change', 255, cField::FLAGS_READONLY);
		$Node->appendChild ($DOM->createTextNode ($RfcField->DisplayName . ':'));
		$Options = array('ReadOnly' => true);
		$RfcField->ToHTML ($Node, $Record, NULL, $Options);
	}

	// Ensure the column is LastChange
	$Node->setAttribute ('ColumnName', 'LastChange');
}
/**
 * Return True, if $Field is a class that extends cForeignField
 * @param cField $Field
 * @return boolean 
 */
function IsFieldAForeignKey($Field)
{
	if ((0 === strcasecmp(get_class($Field), 'cBodySiteField')) ||
		(0 === strcasecmp(get_class($Field), 'cCustomerField')) ||
		(0 === strcasecmp(get_class($Field), 'cDataGroupField')) ||
		(0 === strcasecmp(get_class($Field), 'cStainField'))||
		(0 === strcasecmp(get_class($Field), 'cCasePriorityField')))
	{ 
		return true;
	}
	return false;
}
/**
* cActionField
* 
* @author Bob Ellis
* 
* @package Tables
*/
class cActionField extends cField
{
	/*
	*  cActionField field contains a number of icons that can be clicked on to perform an action.
	* 
	*  $this->Actions is an array of arrays that specify an icon, a javascript function to execute when clicked, and a title:
	* 		$this->Actions = array(array('icon' => '/Images/viewPatient.png', 'action' => 'buildDigitalReviewTab', 'title' => 'View Patient'));
	* 		optional array values:
	* 		'message' - message to display before performing action, currently supported for jsPAL.deleteGridRow action only
	* 		'conditionalicon' - the icon to display if the record's attributes meet a specific condition. if the condition is not true, then the 'icon' is displayed
	* 
	* 		'conditionalicon' is an array with the following elements:
	* 			'attribute' - the name of the $Record attribute to compare
	* 			'operator'	- the operator used to compare the attribute, must be '=' or '!='
	* 			'value'		- the value to compare to the attribute
	* 			'icon'		- the name of the icon to display if the results of the compare is true
	*/
	private  $Actions = array();
	/**
	* Set default values for the object based on inputs
	*
	* @param string $TableName - Name of the table the field belongs to
	* @param string $ColumnName - Database name of the column this field represents
	* @param string $DisplayName - User defined display name shown in the UI
	* @param int $Flags - Combination of cField::FLAGS_* flags to indicate type of field
	*/
	public function __construct ($TableName = '', $ColumnName = '', $DisplayName = '', $Flags = cField::FLAGS_DEFAULT)
	{
		parent::__construct ($TableName, $ColumnName, $DisplayName, $Flags);

		$this->FieldType = 'Action';
		$this->ReadOnly = true;
		$this->IsVirtual = true;
		$this->IsVisible = true;
		$this->IsDatabaseField = false;
		$this->Sortable = false;
		$this->DisplayWidth = 50;
	}

	/**
	* 
	* Set field actions
	* 
	* @param array $Actions - array of actions to set 
	*/
	public function SetActions($Actions)
	{
		$this->Actions = $Actions;
	}
	/**
	* 
	* Get field actions
	* 
	*/
	public function GetActions()
	{
		return $this->Actions;
	}
	/**
	* 
	* Remmove a field action
	* 
	* @param string $Key - Key of element to remove from Actions array
	*/
	public function RemoveAction($Key)
	{
		unset($this->Actions[$Key]);
	}
	/**
	* Output HTML needed to display this field in an Edit context
	*
	* @param DOMElement $Node	- DOM Element to populate
	* @param array $Record		- The current record which could contain this field
	* @param array $OldRecord	- The record obtained from the database (or NULL)
	*/
	// cActionField
	public function ToHTML (DOMElement $Node, $Record, $OldRecord, $Options = NULL)
	{
		$DOM = $Node->ownerDocument;
		$Span = $DOM->createElement('span');
		$Span->setAttribute('class', 'iconlist');
		foreach ($this->Actions as $Action)
		{
			$A = $DOM->createElement('a');
			$A->setAttribute('href', '#');
			$ActionClickTimerMS = GetConfigValue('ActionClickTimerMS');
			if (isset($ActionClickTimerMS) === false)
				$ActionClickTimerMS = 1000;	// default to 1000ms click timer
			$Act = $Action['action'];
			// When the user double clicks, FireFox and Chrome trigger 2 click events followed by a double click event.
			// IE triggers 1 click event followed by a double click
			// The setTimeout in the onclick handler prevents double clicks in FF and Chrome from opening the case twice due to the 2 click events
			$A->setAttribute('onclick',
				 "if(typeof _actionClickTimer!=='undefined')return false;_actionClickTimer=setTimeout('_actionClickTimer=undefined;', $ActionClickTimerMS);$Act");
			$A->setAttribute('ondblclick', 'return false;');				 
			// $Action['type'] is a verb like Delete, Edit, View, etc.
			if (isset($Action['type']))
				$A->setAttribute('class', $Action['type']);
			if (isset($Action['message']))
				$A->setAttribute('message', $Action['message']);
			$Img = $DOM->createElement('img');
			$Img->setAttribute('src', $Action['icon']);
			$Img->setAttribute('alt', '*');
			$Img->setAttribute('title', $Action['title']);
			$Img->setAttribute('class', 'iconlist');
			if (isset($Action['conditionalicon']) && 
				isset($Action['conditionalicon']['attribute']) && 
				isset($Action['conditionalicon']['operator']) &&
				isset($Action['conditionalicon']['value']) &&
				isset($Action['conditionalicon']['icon']))
			{
				if (isset($Record[$Action['conditionalicon']['attribute']]))
				{
					if ($Action['conditionalicon']['operator'] == '=')
					{
						if ($Record[$Action['conditionalicon']['attribute']] == $Action['conditionalicon']['value'])
						{
							$Img->setAttribute('src', $Action['conditionalicon']['icon']);	
							if (isset($Action['conditionalicon']['title']))
							{
								$Img->setAttribute('title', $Action['conditionalicon']['title']);
							}
						}
					}
					else if ($Action['conditionalicon']['operator'] == '!=')
					{
						if ($Record[$Action['conditionalicon']['attribute']] != $Action['conditionalicon']['value'])
						{
							$Img->setAttribute('src', $Action['conditionalicon']['icon']);	
							if (isset($Action['conditionalicon']['title']))
							{
								$Img->setAttribute('title', $Action['conditionalicon']['title']);
							}
						}
					}
				}
			}
			$A->appendChild($Img);
			$Span->appendChild($A);
		}			
		$this->DisplayWidth = $this->CalcWidth();
		$Node->appendChild($Span);
	}
	public function CalcWidth()
	{
		return max(count($this->Actions) * 38, 50); 
	}
	public function GetValue($OldRecord)
	{
		$Value = '<span class="iconlist">';
		foreach ($this->Actions as $Action)
		{
			$Value .= '<a href="#" onclick=\'' . $Action['action'] . '\'>';
			$Value .= '<img src="' . $Action['icon'] . '" title="' . $Action['title'] . '" class="iconlist">';
			$Value .= '</a>&nbsp;';
		}
		$Value .= '</span>';
		return $Value;
	}
}
?>
