<?
/**
* This contains a list class and several supporting classes.
*
* Items needed in calling page for proper operation:
* 	Must link Spectrum.js & List.js
* 	If using editable foreign fields: must link DropDown.css
*	If using pagination: must call GlobalList.OnLoad() in BODY onload="...."
* 
* @package List
*
*/

include_once '/cTable.php';
include_once '/cSearcher.php';
include_once '/PALModules/HealthCare/ColumnWidths.php';

/**
* Object to manage generic list objects such as the Digital Slide List
* 
* @package List
*/
class cList
{
	public		$Name					= '';
	public		$Mode					= 'Listing';
	public		$TableName				= '';
	public		$TableSchema			= NULL;

	public		$Columns				= array ();
	private		$BulkTasks 				= array ();		// array of cBulkTasks which will appear above the list
	private		$BulkTasksChild			= array ();		// array of cBulkTasks for the children
	public		$Rows 					= array ();		// array of cRows which contain all the table data
	public		$IdFields				= array ();		// array of Id names => Id default values
	public		$Records				= array ();		// array of row data from the database

	protected 	$BulkCommandsAllowed 	= true;			// Can we ever allow bulk commands with this list
	protected 	$BulkCommandsEnabled	= true;			// Determines whether we show the commands atop the list
	protected 	$BulkSelectAllowed 		= false;		// Can we ever allow bulk selects with this list
	protected 	$BulkSelectEnabled 		= false;		// determines whether we show a checkbox on each row

	public	 	$FloatingHeadersEnabled;				// Determines whether we show floating headers for each group
	protected 	$AllowEdits				= true;
	protected 	$EditsEnabled;
	protected 	$ShowSaveButtons		= false;
	protected	$EditURL				= '';			// URL to send data to when saving
    protected   $ShowColumnHeaders      = true;

	private		$DOM					= NULL;
	private		$DBReader				= NULL;
	private		$Searcher				= NULL;

	public		$Page					= 1;			// current page view of the list
	public 		$RecordsPerPage			= 0;			// number of records shown on a full page (0 means no limit)
	public		$TotalCount				= 0;			// total records in the database 
	public		$DisplayPaginator		= true;			// display pagination controls 

	public		$EnableCountChange;						// whether to allow the user to change the visible records per page
	public		$EnableSorting;
    public      $EnableChildExpansion   = true;         // weather to allow the expansion of a record with the glyph icon
	public		$EnableEdits;
	private     $EnableJavascriptCList  = true;         // when true, outputs a cList object with the PHP cList to allow records to be saved with the operations queue 
	public		$AddBlankRow			= false;
	private		$DoExpand				= true;
    /**
     * The fields that the list is currently being sorted by
     * @var array cSortField 
     */
    private     $CurrentSortFields      = array();

	public		$TaskParameters			= array ();		// Extra parameters used in the bulk tasks
	private		$SaveParameters			= array();		// Extra parameters passed when saving a list

	public		$PluralRecordsLabel		= 'Records';	// plural noun to refer to the type of records being displayed
	public		$RefreshURL				= '/GetFilteredRecordList.php';
	private		$AutoSelectCommonField	= '';			// column key of field used to determine sticky row selection.
    public      $AllowAutoView          = false;        // whether to show an AutoView text box for automatically viewing selected images.  Used with AutoSelectCommonField

	private		$RowClass				= 'DataRow';

	public		$StretchWidth			= true;			// stretch width on resize
	private		$IsPermanent			= false;

	private		$ListStateObj			= null;

    public      $PALModePostfix         = '';           // In PAL mode the filters for the current table are appended to the table name to make a unique identifier for operations.js
    // By default, GetFilteredRecordList automatically returns image table data when slide table data is requested. If there are multiple
    // image records for a given slide record, DataServer will return multiple rows for the same slide record.
    // Set $ReturnImageDataWithSlide to false to not automatically return image data with slide data 
    private		$ReturnImageDataWithSlide = true;

	// cList
	public function __construct ($Name = NULL, &$TableSchema = NULL)
	{
		if ($Name != NULL)
		{
			$this->SetName($Name);
		}
		else
		{
			// Creating a transient object
			$this->SetName('Temp', false);
		}

		if ($TableSchema)
		{
			$this->TableSchema = $TableSchema;
			$this->TableName = $TableSchema->TableName;
			$this->PluralRecordsLabel = $TableSchema->PluralDisplayName;
		}

		$ListStateObj = GetListStateObj($this->Name, $this->Name);
		$this->SetState($ListStateObj);
	}

	public function _destruct()
	{
		// Ensure our state object is up to date
		$ListStateObj = GetListStateObj($this->Name);
		$ListStateObj->UpdateFromList($this);
	}

	public function SetState(cListState $ListStateObj)
	{
		$this->SetMode($ListStateObj->Mode);
		$this->Page = $ListStateObj->PageNum;
		$this->RecordsPerPage = $ListStateObj->RecordsPerPage;
		$this->SetExpansion($ListStateObj->IsExpanded);
		$this->Searcher = $ListStateObj->Searcher;
		$this->ListStateObj = $ListStateObj;
	}

	// cList
	public function SetName($Name, $DoRegister=true)
	{
		$this->Name = $Name;
		if ($DoRegister)
		{
			$_SESSION['Lists'][$Name] = $this;
		}
	}

	// cList
	public function GetName()
	{
		return $this->Name;
	}

	// Flag whether list is permanent or transient/temporary
	// cList
	public function SetPermanent($Flag=true)
	{
		$this->IsPermanent = $Flag;
	}

	// cList
	public function IsPermanent()
	{
		return $this->IsPermanent;
	}

	// Copy pertinent members from passed cList to this one
	// cList
	public function Copy(&$src)
	{
		$Members = get_class_vars('cList');
		foreach ($Members as $Key => $Value)
		{
			if (($Key != 'Name') && ($Key != 'ListStateObj'))
				DeepCopy2($this->$Key, $src->$Key);
		}
		$this->SetPermanent(false);	// this is a copy

		if ($src->BulkSelectEnabled)
		{
			// This is needed to create the correct cBulkTask checkbox command
			$this->EnableBulkSelect(true);
		}
	}

	// cList
	public function SetMode($Mode)
	{
		$this->Mode = $Mode;

		if ($Mode == 'Listing')
		{
			//$this->FloatingHeadersEnabled = true;
			foreach ($this->Rows as &$Row)
				$Row->ChildListing = NULL;
			$this->EnableSorting(true);
			$this->EnableEdits(false, false);
		}
		else // if (($Mode == 'ChildListing') || ($Mode == 'GrandChildListing'))
		{
			//$this->FloatingHeadersEnabled = false;
			$this->EnableEdits(true, false);
			// Slides list by Stain Display Order when in details (Grand/ChildList) mode
		}	
		
		// Reset state of object (revert any temporary settings)
		$this->EnableCountChange = true;	// allow children list to control number of records
		$this->EnableChildExpansion(false);
		$this->EnableBulkCommands($this->BulkCommandsAllowed);
		$this->EnableBulkSelect($this->BulkSelectAllowed);
	}

	// cList
	private function EnableChildExpansion($TrueFalse)
	{
		if ($TrueFalse == true) 
		{
			if (isset($this->Columns['Expansion']) == false)
			{
				$this->AddField(new cExpansionField($this->TableName));
				$this->OrderColumns();	// Ensure expansion field is first
			}
		}
		else
		{
			$this->DisableField('Expansion');
		}
	}

	// cList
	public function SetExpansion($Flag)
	{
		$this->DoExpand = $Flag;	// default
	}

	// cList
	public function IsExpanded()
	{
		return $this->DoExpand;
	}

	// cList
	public function AssignFromTable($TableName)
	{
		$TableSchema = GetTableObj($TableName);
		if ($TableSchema == NULL)
			return;

		$this->TableName = $TableName;
		$this->TableSchema = $TableSchema;

		$this->PluralRecordsLabel = $TableSchema->PluralDisplayName;

		foreach ($TableSchema->Fields as $Field)
			$this->AddField($Field);

		foreach ($TableSchema->IdFields as $Id)
			$this->AddIdField ($Id->ColumnName . 's');

		if ($TableSchema->InlineTasksEnabled)
			$this->AddNewColumnHeader ('Tasks', 'Tasks');

		if (($this->DBReader == NULL) && $TableSchema->DBReader)
		{
			// Not a view, get reader from table
			$this->DBReader = DeepCopy($TableSchema->DBReader);
			$this->DBReader->SetGetTotalNumRecords(true);
			$this->DBReader->SetReturnImageDataWithSlide($this->ReturnImageDataWithSlide);
		}

		// make sure current saved or default sorting order applied when list is loaded for the first time
		$SavedSort; $SortField; $SortOrder;
		// read cuurent sort settings
		$SavedSort = $TableSchema->GetSavedSort();
		if (is_array($SavedSort))
		{
			if (count($SavedSort) == 2)
			{
				if(count($SavedSort[0]) && count($SavedSort[1]))
				{        
					$SortField = $SavedSort[0]; 
					$SortOrder = $SavedSort[1];  
					$this->SetSort($SortField, $SortOrder);
					return; 
				}				
			}
		}
		
		// if not found any save sort apply defaults sort settings    
		$TableSchema->GetDefaultSort($SortField, $SortOrder);  		
		$this->SetSort($SortField, $SortOrder);
	}

	// cList
	public function SetDOM($DOM)
	{
		$this->DOM = $DOM;
	}

	// cList
	public function GetDOM()
	{
		if ($this->DOM == NULL)
			$this->SetDOM(new DOMDocument());
		return $this->DOM;
	}

	// cList
	public function ClearBulkTasks()
	{
		$this->BulkTasks = array();
		$this->BulkTasksChild = array();
	}
	// cList
	public function SetReturnImageDataWithSlide($TrueFalse)
	{
		$this->ReturnImageDataWithSlide = $TrueFalse;
		if ($this->DBReader)
		{
			$this->DBReader->SetReturnImageDataWithSlide($TrueFalse);
		}
	}

	/**
	 * Add a bulk task link object to the BulkTasks array
	 *
	 * @param string $Text - Text to display in the link
	 * @param string $FormAction - 
	 * @param string $Ids - Name of the id field this task should act on
	 * @param string $MinAccessLevelRequired - "None", "Read", or "Full".  Minimum access a user must have to enable the link
	 * @param string $ConfirmMessage - Optional JavaScript confirmation message to display before performing the task
	 * @param string $DropDownTexts - array of strings that will appear in a dropdown list when the bulk task is clicked
	 * @param string $DropDownActions - array of actions.  Can be a form action or can be javascript.
     * @param string $DropDownValues - array of values that corresponds to the string array DropDownTexts
     * @param string $DropDownName - if there are dropdown values, then this dictates the name of the form SELECT element.
     * @param boolean $SelectOne -
	 * @return cBulkTask - Bulk task object created
	 */
	// cList
	public function AddNewBulkTask ($Text, $FormAction = '', $Ids = '', $MinAccessLevelRequired = 'None', $ConfirmMessage = '', $DropDownTexts = array(), $DropDownActions = array(), $DropDownValues = array(), $DropDownName="", $SelectOne = false)
	{
		$NewBulkTask = new cBulkTask();

		$NewBulkTask->SelectOne					= $SelectOne;
		$NewBulkTask->Text						= $Text;
		$NewBulkTask->Ids						= $Ids;
		$NewBulkTask->MinAccessLevelRequired	= $MinAccessLevelRequired;
		$NewBulkTask->FormAction				= $FormAction;
		$NewBulkTask->ConfirmMessage			= $ConfirmMessage;
        $NewBulkTask->DropDownName              = $DropDownName;

		// add bulk task options
		for($i = 0; $i < count($DropDownTexts); $i++)
		{
			$Option = new cBulkTaskOption();
			$Option->OptionText 	 = $DropDownTexts[$i];
			$Option->OptionAction 	 = $DropDownActions[$i];
            $Option->OptionValue     = $DropDownValues[$i];
			$NewBulkTask->BulkTaskOptions[] = $Option;
		}

		if ($this->Mode == 'Listing')
			$this->BulkTasks[] = $NewBulkTask;
		else
			$this->BulkTasksChild[] = $NewBulkTask;

		return $NewBulkTask;
	}


	/**
	 * Add an id field to the IdFields array.
	 *
	 * @param string $IdName - Name to apply to this id.  Must be unique
	 * @param string $Default - Default id value
	 */
	// cList
	public function AddIdField ($IdName = 'Ids', $Default = -1)
	{
		if (isset ($this->IdFields[$IdName]))
		{
			trigger_error ('Id name must be unique');
		}

		$this->IdFields[$IdName] = $Default;
	}

	// cList
	public function AddField($Field)
	{
		if ($Field->IsVisible('List') == false)
			return;

		$FieldName = $Field->ColumnName;

		if (isset($this->Columns[$FieldName]) == false)
		{
			$this->Columns[$FieldName] = new cColumn(NULL, $Field);
		}
		else
		{
			$this->Columns[$FieldName]->Field = $Field;
		}

		if ($Field->IsNeeded && $this->DBReader)
		{
			$this->DBReader->AddColumn($FieldName);
		}
	}

	// cList
	private function DisableField($FieldName)
	{
		unset($this->Columns[$FieldName]);
	}

	/**
	 * @param string $ColumnName - Key used internally to refer to this column.  Must be unique
	 * @param string $DisplayName - Text to display in the column header
	 * @param bool $CanSort - Whether this field can be sorted on
	 * @param bool $Grouped - No longer used
	 * @return cColumnHeader - The column header created
	 */
	// cList
	public function AddNewColumnHeader ($ColumnName, $DisplayName = '', $CanSort = false, $Grouped = false, $Width = NULL)
	{
		$Sorted = $this->GetSortOrderForColumn($ColumnName);

		$NewColumnHeader = new cColumnHeader ($ColumnName, $DisplayName, $CanSort, $Sorted, $Width);

		if (isset($this->Columns[$ColumnName]) == false)
		{
			$Column = new cColumn($NewColumnHeader, NULL);
			$Column->Position = count($this->Columns);
			$this->Columns[$ColumnName] = $Column;
		}

		return $NewColumnHeader;
	}

	// cList
	public function RemoveColumn($ColumnName)
	{
		unset ($this->Columns[$ColumnName]);
	}

	// cList
	public function AllowBulkCommands($TrueFalse)
	{
		$this->BulkCommandsAllowed = $TrueFalse;
		$this->EnableBulkCommands($TrueFalse);
		if ($TrueFalse == false)
		{
			// No commands means no bulk selections
			$this->AllowBulkSelect(false);
		}
	}

	// cList
	public function EnableBulkCommands($TrueFalse)
	{
		if ($TrueFalse == true)
		{
			if ($this->BulkCommandsAllowed)
				$this->BulkCommandsEnabled = true;
		}
		else
		{
			$this->BulkCommandsEnabled = false;
			// Also disable any select boxes
			$this->EnableBulkSelect(false);
		}
	}

	// cList
	public function AllowBulkSelect($TrueFalse)
	{
		$this->BulkSelectAllowed = $TrueFalse;
		if ($TrueFalse == true)
		{
			$this->AllowBulkCommands(true);
			$this->EnableBulkSelect(true);
		}
		else
		{
			$this->EnableBulkSelect(false);
		}
	}

	// cList
	public function EnableBulkSelect($TrueFalse)
	{
		if ($TrueFalse == true)
		{
			if ($this->BulkSelectAllowed)
			{
				$this->BulkSelectEnabled = true;

				$OnClick = 'OnCheck (this);';
				$this->AddField(new cBulkCheckField($this->TableName, $OnClick));
			}
		}
		else
		{
			$this->BulkSelectEnabled = false;
			$this->DisableField('BulkCheck');
		}
	}

	// cList
	public function AllowEdits($TrueFalse)
	{
		$this->AllowEdits = $TrueFalse;
		if ($TrueFalse == false)
			$this->EditsEnabled = false;
	}

	// cList
	public function EnableEdits($TrueFalse, $ShowSaveButton = false, $EditURL = '/Record_Save.php')
	{
		if ($this->AllowEdits)
		{
			$this->EditsEnabled = $TrueFalse;
			$this->ShowSaveButtons = $ShowSaveButton;
			$this->EditURL = $EditURL;
		}
	}

	// cList
	public function EnablePaginator($TrueFalse)
	{
		$this->DisplayPaginator = $TrueFalse;
	}
        
	// cList
	public function EnableHeaderColumns($TrueFalse)
	{
		$this->ShowColumnHeaders = $TrueFalse;
	}        

	// cList
	public function IsEditingEnabled()
	{
		return $this->EditsEnabled;
	}

	// cList
	public function AddBlankRow($TrueFalse)
	{
		$this->AddBlankRow = $TrueFalse;
		// Do not allow pagination - this will take code changes
		$this->SetRecordsPerPage(0);
	}

	// cList
	public function SetSaveParameter($Key, $Value)
	{
		$this->SaveParameters[$Key] = "$Key=$Value";
	}

	/**
	 * Allow for client to change record retieval method
	 */
	// cList
	public function SetDatabaseReader ($DBReader)
	{
		$this->DBReader = $DBReader;
		$this->DBReader->SetReturnType('Arrays');
		$this->DBReader->SetGetTotalNumRecords(true);
		$this->DBReader->SetReturnImageDataWithSlide($this->ReturnImageDataWithSlide);
        //set inbound databasereader sorts to match the current list sort
		$this->SetSort ($DBReader->GetSortFields(), $DBReader->GetSortOrders());
	}

	public function GetDatabaseReader()
	{
		return $this->DBReader;
	}

	public function GetSearcher()
	{
		return $this->Searcher;
	}

	public function SetAutoSelectCommonField($FieldName)
	{
		$this->AutoSelectCommonField = $FieldName;
	}


	/**
     * Add a sort to the cList object
     * The parameters are always promoted to an array of sorts so they can be used in multicolumn sorting
     * When the parameters are arrays the counts of $SortField and $SortOrder must match     
	 *
	 * @param array/string $SortColumnNames - Names of the columns to sort on
	 * @param array/string $SortOrders - Ascending or Descending
	 */
	// cList
	public function SetSort ($SortColumnNames, $SortOrders)
	{                
        //clear out the current list sort settings
        $this->CurrentSortFields = array();
        
        //local variables used to set the $this->cDatabase Readers Sort 
        $localSortFieldsArray = array();
        $localSortOrderArray = array();
        
        if (is_array($SortColumnNames) && is_array($SortOrders))
        {
            if (count($SortColumnNames) === count($SortOrders))
            { 
                $sortCount = count($SortOrders);
                for ($i = 0; $i < $sortCount; $i++) 
                {
                    $sortFieldName = $SortColumnNames[$i];
                    $sortOrderName = $SortOrders[$i];                    
                    $localSortFieldsArray[] = $sortFieldName;
                    $localSortOrderArray[]  = $sortOrderName;                    
                    $this->CurrentSortFields[] = new cSortField($sortFieldName , $sortOrderName);
                }
            }
        }
        else if (is_string ($SortColumnNames) && is_string ($SortOrders))
        {
            $sortFieldName = $SortColumnNames;  
            $sortOrderName = $SortOrders;              
            $localSortFieldsArray[] = $sortFieldName;
            $localSortOrderArray[]  = $sortOrderName;
            $this->CurrentSortFields[] = new cSortField($sortFieldName , $sortOrderName);
        }  
       
        //loop over the headers two times
        //clear all the 'little arrows' on the first pass
        //set the header 'little arrows' on the second pass to match the inbound sort        
        $columnsCount = count($this->Columns);
        foreach ($this->Columns as $columnKey => $columnValue )
        {
            $Header = $this->GetColumnHeader ($columnKey);
            $Header->Sorted = '';            
        }
        
        //change the headers for the cList to match their search settings
        //the header is the  <th> that has the little up/down arrow        
        $sortCount = count($localSortFieldsArray);
        for ($i = 0; $i < $sortCount; $i++) 
        {        
            $localSortFieldName = $localSortFieldsArray[$i];
            $localSortOrderName = $localSortOrderArray[$i];
            // Set new sorted header
            $Header = $this->GetColumnHeader ($localSortFieldName);
            
            if ($Header == null)
            {
            	/* If the header is not found, then we are dealing with a foreign key column. */
				$Header = $this->MapSortColumnToHeader ($localSortFieldName);
            } 

            if ($Header)
            {
                // Set new header
                $Header->Sorted = $localSortOrderName;
            }                    
        }
        
        //set the databasereader's sort to match the list's sort
        if ($this->DBReader)
        {
            $this->DBReader->SetSort($localSortFieldsArray, $localSortOrderArray);
        }         
        if ($this->TableSchema)
        {
            //the list object has a pointer to the lists current "Table Schema"
            //for example, if you are on the case list page and change the sort order
            //the sort update change needs to be propogated down to the $_SESSION["Case"] object's searcher
            //
            //then, when ShowHierarchy->ShowExpandedTable->GetViewLinks->GetNeighbors is called to display the next/previous links
            //      the sort settings are persisted through to the parent table
            $Searcher = $this->TableSchema->GetSearcher();
            if ($Searcher)
            {
                $Searcher->SetSort($localSortFieldsArray, $localSortOrderArray);
            }
        }
	}

    /**
	 * Multicolsort stores foreign key columns to be sorted in UpTo/DownTo syntax
	 * while the HTML only contains the key.  For those foreign key columns, need to lookup
	 * the UpTo/DownTo fieldpath in the Columns array.
	 */
	// cList
	public function MapSortColumnToHeader ($Key)
	{  
		foreach ($this->Columns as $Column)
		{
			if ($Key == $Column->Field->FieldPath)
			{
				return($Column->Header);
			}				
		}
		/* if we got to this point, then the stored sort column is no longer in the List. */
		return null;
	}
	
	/**
	 * Checks if a column header key exists already
	 *
	 * @param string $Key - Key string to look for
	 * @return cColumnHeader - Column header object requested, or null if no such column exists
	 */
	// cList
	public function GetColumnHeader ($Key)
	{
		if (isset ($this->Columns[$Key]))
			return $this->Columns[$Key]->Header;
		return null;
	}

	/**
	 * Enable/Disable sorting for all columns
	 *
	 * @param bool $OnOff - TRUE to allow sorting, FALSE to not
	 */
	// cList
	public function SetColumnSorting($OnOff)
	{
		foreach ($this->Columns as $Column)
			$Column->Header->CanSort = $OnOff;
	}

	// cList
	public function EnableSorting($OnOff)
	{
		foreach ($this->Columns as $Column)
		{
			if ($Column->Header->CanSort)
				$Column->Header->AllowSort = $OnOff;
		}
	}


	// cList
	public function Clean()
	{
		$this->ClearRows();
		$this->Records = array();
		$this->DOM = NULL;
	}

	// cList
	public function ClearRows()
	{
		$this->Rows = array ();
	}


	/**
	 * Add a new row to the Rows array
	 *
	 * @param mixed $RecordIds - Id(s) for the row.  Used to create the checkbox column.  Either int, or array of string=>int where string is the id field name
	 * @param string $AccessLevel - "None", "Read", or "Full"
	 * @return cRow - Row object created
	 */
	// cList
	public function AddNewRow ($RecordIds, $AccessLevel='Read')
	{
		$NewRow = new cRow($this);
		$NewRow->SetAttribute('Ids', $RecordIds);
		$NewRow->SetAttribute('AccessLevel', $AccessLevel);
		$NewRow->SetAttribute('DataGroupAccessLevel', $AccessLevel);
		$NewRow->SetAttribute('class', $this->RowClass);
		$this->Rows[] = $NewRow;

		// Alternate row background colors
		if ($this->RowClass == 'DataRow')
			$this->RowClass = 'DataRow Alt';
		else
			$this->RowClass = 'DataRow';

		return $NewRow;
	}


	// cList
	public function SetPage ($Page)
	{
		$this->Page = $Page;
	}

	// cList
	public function GetPage ()
	{
		return $this->Page;
	}

	// cList
	public function SetRecordsPerPage ($RecordsPerPage)
	{
		$this->RecordsPerPage = $RecordsPerPage;
		if ($this->DBReader)
			$this->DBReader->SetRecordsPerPage($RecordsPerPage);
	}

	// cList
	public function GetRecordsPerPage()
	{
		return $this->RecordsPerPage;
	}


	// cList
	public function EnablePageCountChange ($Flag)
	{
		$this->EnableCountChange = $Flag;
	}

	// Update list from parameters (usually _REQUEST)
	// cList
	public function Update($Parms)
	{
		if (isset ($Parms['Page']))
			$this->Page = $Parms['Page'];
		if (isset ($Parms['RecordsPerPage']))
			$this->RecordsPerPage = $Parms['RecordsPerPage'];
		if (isset ($Parms['SortField']) && isset($Parms['SortOrder']))
			$this->SetSort($Parms['SortField'], $Parms['SortOrder']);
		if (isset ($Parms['AllowEditFields']))
			$this->AllowEdits(true);                
		if (isset ($Parms['EnableColumnHeaders']))
			$this->ShowColumnHeaders ($Parms['ShowColumnHeaders']);

		$this->ListStateObj->Update($Parms);
	}

	// cList
	public function PrintHTML ()
	{
		$DOM = $this->GetDOM();

		$OuterNode = $DOM->createElement ('DIV');

		$this->ToHTML ($OuterNode);

		OutputHTML($OuterNode);
	}

	// cList - print jQuery grid
	// $DivId is the id for the containing div	
	// $UniqueGridId is a unique id for the grid
	// $loadData controls whether data is to loaded immediately
	// $Subgrids is an array of table (view) names for nested subgrids
    // $SelectRowJSFunction is the name of a JS function to call when a row is selected in the grid
    // $PrimaryKeyColumn  is the DownTo Syntax to used in setting the jqgrid <tr id>
    // $GridCaption is the optional caption to display at the top of the grid. If not specified the view's base table name is displayed as the grid caption
    // $MultiSelect enables multiselect on the grid
    // $SelectAllJSFunction is the name of a JS function to call when all rows are selected in the grid, if $MultiSelect is true
	public function DisplayJqGrid($DivId, $UniqueGridId = '', $loadData = true, $Subgrids = array(), $Class = '', $SelectRowJSFunction = '', $PrimaryKeyColumn = 'Id',
	 	$GridCaption = '', $MultiSelect = '', $SelectAllJSFunction = '', $GridCompleteJSFunction = '')
	{        
        
        $RecordsPerPage = GetRecordsPerPage();
        
		//force unique names
        if ($UniqueGridId == '')
		{
			$UniqueGridId = uniqid($this->Name);
		}

        //TODO: JQGRID_MULTICOLUMN_SUPPORT: When JQGrid supports multicolumn sort the following lines may need to be modified        
        $SortField = join(",",$this->GetSortFields());
        $SortOrder = join(",",$this->GetSortOrders());
        
        
        
		// Override the sortfield/sortorder for this grid which is displayed in the Patients tab of the TB workflow.
		if ($this->Name == const_tumorBoardSubGrid2){
			$SortField = 'DownToDataGroupsCaseByCaseId.DisplayOrder';
			$SortOrder = 'asc';			
		}

        //append the search parameters as custom attributes to the jqgrid table
        //... can get what search parameters were used to get the grid data client side
        $GridSearchParameters = NULL;
        if ($this->Searcher)
        {            
            for ($Filter = $this->Searcher->GetFirstFilter(); $Filter != NULL; $Filter = $this->Searcher->GetNextFilter())
            {
                $GridSearchParameters[] = $Filter;
            }            
        }
		echo '<div id="' . $DivId . '">';
		echo '<table id="' . $UniqueGridId . '" class="tablegrid" tablename="' . $this->DBReader->TableName . '"></table>';
		echo '<div id="' . $UniqueGridId .'pager"></div>';
		echo '<script type="text/javascript">';
		echo '$(document).ready(function() {';
        
        $SubGridDebugInfo = array();
        foreach ($Subgrids as $sg)
        {
            $DebugListObj = GetListObj($sg);		
            $SubGridDebugInfo[] = array( 'ViewName'            => $DebugListObj->TableSchema->Name,
                                         'SortFields'          => join(",",$DebugListObj->GetSortFields()),                                         
                                         'SortOrders'          => join(",",$DebugListObj->GetSortOrders()),   
                                         );
        }        
        //setup usefull debugging parameters to the grid
        $DebugingArray = array_merge(                                       
                                   array('ViewName' => $this->TableSchema->Name,
                                         'SortFields'          => $SortField,                                         
                                         'SortOrders'          => $SortOrder),
                                   array('SubGrids' => $SubGridDebugInfo));
        echo '$("#' . $UniqueGridId . '").data(' . json_encode($DebugingArray) . ');';
        
          if (isset($GridSearchParameters))
          {
              //attach all of the search parameters to the grid using jquery.data()
             echo '$("#' . $UniqueGridId . '").data(' . json_encode($GridSearchParameters). ');';
          } 
		  if ($this->Name == const_tumorBoardSubGrid2) 
		  {
			  $OnDrop = "function (table, row)" .
				      "{" .
					  "    var rowsPerPage = $('#GRID_ID').getGridParam('rowNum'," .
					  "        page = $('#GRID_ID').getGridParam('page')," .
					  "        rowOnPage = $(row).index())," .
					  "        overallPos = ((page - 1) * rowsPerPage) + (rowOnPage)," .
					  "        caseId = $(row).attr('id');" .
										  
					  "    $.post('tumorBoardReorderCases.php', { caseId: caseId, position: overallPos }, function () { $('#GRID_ID').jqGrid().trigger('reloadGrid'); });" .
					  "}";

			  echo "$('#" . $UniqueGridId . "').tableDnD({" .
				(strlen($OnDrop) == 0 ? "" : "onDrop: " . str_replace("GRID_ID", $UniqueGridId, $OnDrop)) .
				"});";
		  }
			if (is_string($loadData))		  
				$loadData = strcasecmp($loadData, 'true') === 0 || strcmp($loadData, '1') === 0;
			$GridSortOrder = $SortOrder;
			if ($GridSortOrder === 'Ascending')
				$GridSortOrder = 'Asc';
			else if ($GridSortOrder === 'Descending')
				$GridSortOrder = 'Desc';
			echo '$("#' . $UniqueGridId . '").jqGrid({
			sortable: false,  // prevent columns from being draggable
			caption: "' . $GridCaption . '",
			url:"/PALModules/HealthCare/GetJQGridData.php?q=1&AJAX=true&PALMode=1&Table=' . $this->TableName . '&GridRowPrimaryKey=' . $PrimaryKeyColumn. '",';
			echo $this->GetColModel();

            //TODO JQGRID_MULTICOLUMN_SUPPORT: When JQGrid supports multicolumn sort the following line may need to be modified        
			echo ',
			pager: "#' . $UniqueGridId . 'pager",
			rowNum: ' . $RecordsPerPage . ',
			sortname: "' . $SortField . '",
			sortorder: "' . $GridSortOrder . '",
			viewrecords: true,
			gridview: true,
			height: "100%",
			autowidth: true,
			width: 950,
			shrinkToFit: false,
			forceFit: false,
			rowList:[5,10,15,20,30, ' . (in_array($RecordsPerPage, array(5,10,15,20,30)) ? '' : $RecordsPerPage)   . '],
			datatype: "' . ($loadData ? "json" : "local") . '",
			loadComplete: jqGridLoadComplete,
			gridComplete: function () {
			';
			if ($Class != '')
				echo '$(this).parent().addClass("' . $Class . '");';
			
			echo $GridCompleteJSFunction;

			if ($this->Name == const_tumorBoardSubGrid2)
			{
				echo '$("#' . $UniqueGridId . '").tableDnDUpdate();';
			}
			echo '}';
			
		    if ($SelectRowJSFunction != '')
			{
			    echo ',
			    onSelectRow: ' . $SelectRowJSFunction;
		    }
			if ($SelectAllJSFunction != '')
		    {
			    echo ',
			    onSelectAll: ' . $SelectAllJSFunction;
		    }
			if ($MultiSelect == '1')
		    {
			    echo ',
			    multiselect: true,
				multiselectWidth: 25
				';
			}
		    if (count($Subgrids))
		    {
			    echo ',';			
		    	// get first table name from $Subgrids, and remove it from the $Subgrid array
		    	$SubgridTbl = array_shift($Subgrids);
		    	// display the subgrid(s)
		    	$this->PrintJQSubGrid($SubgridTbl, $this->DBReader->TableName, $Subgrids);
			}
		
		echo '});
			});
			</script>
			</div>';
	}

	// cList
	// display one or more subgrids
	// $Table is the table name for the subgrid
	// $ParentTable is the parent table
	// $Subgrids is an array of table/view names for nested subgrids
	public function PrintJQSubGrid($Table, $ParentTable, $Subgrids = array())
	{
        $RecordsPerPage = GetRecordsPerPage();
        
       //TODO: NEED to clean up  ListTumorBoard_Datagroups_Grid_DataGroups 
       //      CANNOT leave a one liner hanging around in a js subgrid filter   
       //      with a substring compare of the DataView.Name no less!!    
		$ListObj = GetListObj($Table);		
        
        //TODO JQGRID_MULTICOLUMN_SUPPORT: When JQGrid supports multicolumn sort the following line may need to be modified                
        $SortField = join(",",$ListObj->GetSortFields());
        $SortOrder = join(",",$ListObj->GetSortOrders());
        
		echo 'subGrid: true,';
		echo 'subGridOptions: {openicon: "ui-icon-arrowreturnthick-1-e"},';
		echo 'subGridRowExpanded: function(subgrid_id, row_id) {  ';        
			// if the expanding row is in the main grid and not a subgrid, collapse all the other rows in the main grid
			echo '                              
                var expandRowId = row_id;
                
                if (!$(this).hasClass("subgrid"))
                {
                    var ids=$(this).getDataIDs();

                    for(var i=0;i<ids.length;i++)
                    {
                        if(ids[i]!=row_id)
                        {
                            $(this).jqGrid("collapseSubGridRow", ids[i]);
                        }
                    }
                }
                ';		
			echo 'var subgrid_table_id, pager_id;';
			echo 'subgrid_table_id = subgrid_id+"_t";';
			echo 'pager_id = "p_"+subgrid_table_id;';
			echo 'jQuery("#"+subgrid_id).html("<table id=\'"+subgrid_table_id+"\' class=\'subgrid tablegrid\' tablename=\'' . $ListObj->DBReader->TableName . '\'></table><div id=\'" + pager_id + "\'></div>");';			
            echo 'jQuery("#"+subgrid_table_id).jqGrid({ ';
				echo 'caption: "' . $ListObj->DBReader->TableName . '",
				url: "/PALModules/HealthCare/GetJQSubGridData.php?q=2&AJAX=true&PALMode=1&id=" + expandRowId + "&Table=' . $Table . '&ParentTable=' . $ParentTable . '",
				datatype: "json",';
					// get subgrid colModel (without any cActionFields)

				if (isTumorBoardFolderSubGrid($ListObj->Name)){
					tumorBoardAddActionsToFolderSubGrid($ListObj);
					echo $ListObj->GetColModel(true);
					echo ',caption: ""';
				} elseif (isTumorBoardCaseSubGrid($ListObj->Name)){
					tumorBoardAddActionsToCaseSubGrid($ListObj);
					echo $ListObj->GetColModel(true);
					echo ',caption: ""';					
				} else
					echo $ListObj->GetColModel(false);
                //TODO JQGRID_MULTICOLUMN_SUPPORT: When JQGrid supports multicolumn sort the following line may need to be modified        
				echo ',
 				sortable: true,
 				rowNum: ' . $RecordsPerPage . ',
				pager: pager_id,
			    sortname: "' . $SortField . '",
			    sortorder: "' . $SortOrder . '",
				height: "100%",
				autowidth: false,
				width: 900,
			    shrinkToFit: false,
				rowList:[5,10,15,20,30,' . (in_array($RecordsPerPage, array(5,10,15,20,30)) ? '' : $RecordsPerPage) . '],
				loadComplete: jqGridLoadComplete,
				multiselect:false'. (count($Subgrids) > 0 ? ',' : '');
				if (count($Subgrids))
				{
		    		// get first table name from $Subgrids, and remove it from the $Subgrid array
		    		$SubgridTbl = array_shift($Subgrids);
		    		// display the next subgrid
				   	$this->PrintJQSubGrid($SubgridTbl, $ListObj->DBReader->TableName, $Subgrids);
				}
				echo '});';
			echo '}';
		
	}
	// clist - print a jqGrid colNames and colModel for this cList object
	public function GetColModel($getActionField = true)
	{
  		global $ColumnWidths;
		// print column names as colNames:[name, name, ...]
		$ColModel = 'colNames:[';
		$Columns = $this->ColumnsSortedByPosition();
		$i = 0;
		foreach ($Columns as $Column)
		{
			if ($getActionField === false && get_class($Column->Field) === 'cActionField')
				continue;
		    if ($i++ > 0)
		    	$ColModel .= ',';
			$ColModel .=  "'" . $Column->Field->DisplayName ."'";
		}
		$ColModel .= '],';
		// print colModel as colModel:[{column info},{column info}...]
		$ColModel .= 'colModel: [';
		$i = 0;
		foreach ($Columns as $Column)
		{
			if ($getActionField === false && get_class($Column->Field) === 'cActionField')
				continue;
			if ($i++ > 0)
				$ColModel .= ',';
			$Field = $Column->Field;
			$Sortable = $Field->Sortable ? 'true' : 'false';
			$ColModel .= '{name: "' . $Field->DisplayName . '", index: "' . $Field->SortPath. '", sortable:' . $Sortable;
			if ($Field->FieldType == 'Memo' || ($Field->FieldType == 'Text' && $Field->MaxLength == -1))
				$ColModel .= ', classes:"field"';
			if (get_class($Field) == 'cActionField')
				$ColModel .= ',align:"center"';
			// all fields should initially sort descending, except for case priority which sorts ascending by default
			if ($Field->ColumnName !== 'PriorityId')
				$ColModel .= ',firstsortorder:"desc"';
			else
				$ColModel .= ',firstsortorder:"asc"';
			if ($Field->DisplayWidth != null)
			{
				$ColModel .= ',width:' . $Field->DisplayWidth;
			}
			else
			{
				$key = $Field->TableName . ':' . $Field->ColumnName;
				if (isset($ColumnWidths[$key]))
				{
					$ColModel .= ',width:' . $ColumnWidths[$key] ;
				}
				else
				{
					if ($Field->FieldType == 'Memo')
						$ColModel .= ',width:120';
					else if ($Field->FieldType == 'Special')
						$ColModel .= ',width:150';
					else
						$ColModel .= ',width:110';
				}
			}
			$ColModel .= '}';				
		}
		$ColModel .= ']';
		return $ColModel;
	}

	// cList
	public function SetSearcher($Searcher)
	{
		$this->Searcher = $Searcher;
	}
	// cList
	public function ColumnsSortedByPosition()
	{
		$Columns = $this->Columns;
		uasort($Columns, 'SortByPosition');
		return $Columns;
	}

	// cList
	public function GetColModelColumns()
	{
		$Response = '';
		foreach ($this->Columns as $Column)
		{
			$Response->colNames[] = $Column->Field->DisplayName;
		}
		return json_encode($Response);
	}

	// cList
	public function DisplayList($Div, $Page, cSearcher $Searcher = NULL, $loadData = true)
	{
		if ($loadData)
			$this->ImportRecords($Page, $Searcher);
		else
		{
			if ($Searcher)
				$this->Searcher = $Searcher;
		}

		CreatePopups($Div);

		$this->ToHTML($Div);
	}


	/**
	 * Import table data for later display.
	 * This method should be called if a DBReader was designated.
	 * @param int $Page		Page number to start collecting records from
	 */
	// cList
	public function ImportRecords ($Page, $Searcher = NULL)
	{
		if ($Searcher)
		{
			// Retain the new search criteria
			$this->Searcher = $Searcher;
		}
		else
		{
			// Use the old search criteria (if any)
			$Searcher = $this->Searcher;
		}

		$this->ClearRows();

		if ($Searcher)
        {
			$this->DBReader->SetFilters($Searcher);
        }
		if ($this->CurrentSortFields)
        {            
			$this->DBReader->SetSort($this->GetSortFields(), $this->GetSortOrders());
        }
		$this->DBReader->SetGetTotalNumRecords(true);

		if ($this->IsExpanded())
		{
			$RecordsPerPage = $this->RecordsPerPage;
			$this->DBReader->SetRecordsPerPage($RecordsPerPage);
			$Records = $this->DBReader->GetRecords($Page);
			$TotalCount = $this->DBReader->TotalNumRecords;

			if ($TotalCount <= ($RecordsPerPage * ($Page - 1)))
			{
				// There are (now) less records than the requested page supports, lower the page number
				if ($TotalCount > 0)
					$Page = ceil ($TotalCount / $RecordsPerPage);
				else
					$Page = 1;	// Note: Page is returned and should never be zero
				$Records = $this->DBReader->GetRecords($Page);
			}
		}
		else
		{
			$Records = array();
			$TotalCount = 0;
		}

		$this->LoadRecords($Records, $Page, $TotalCount);

		return $Records;
	}

	// cList
	public function LoadRecords($Records, $Page, $TotalCount)
	{
		$this->Records = $Records;
		$this->Page = $Page;
		$this->TotalCount = $TotalCount;
	}

	// cList
	public function SetTotalCount($Count)
	{
		$this->TotalCount = $Count;
	}

	// cList
	public function BuildRows()
	{
		if ($this->TableSchema == NULL)
		{
			// With no tableschema the caller must build his own rows
			// XXX - is this true?
			return;
		}

		$this->OrderColumns();

		//$DOM = new DOMDocument();
		//$RowAtts = $this->TableSchema->FormatFilteredRecordList ($DOM, $this, $this->Records);
		// Need ListObj for basetablename
		$RowAtts = $this->TableSchema->FormatFilteredRecordList (NULL, $this, $this->Records);	
		// Most RowAtts have an Id, and possibly ImageIds

		$this->ClearRows();

		$NumRecords = count($this->Records);
		$RowClass = 'DataRow';
		for ($idx = 0; ; $idx++)
		{
			$NewRow = new cRow ($this);

			if ($idx >= $NumRecords)
			{
				if (($this->AddBlankRow == false) || ($idx > $NumRecords))
					break;

				$NewRow->SetBlankRow(true);
				$NewRecord = $this->TableSchema->CreateDefaultRecord();
				$this->Records[] = $NewRecord;
				if (isset($NewRecord['Id']))
					$RowAtts[$idx] = array('Ids' => $NewRecord['Id']);
				else
					$RowAtts[$idx] = array('Ids' => 0);
			}

			$Record = &$this->Records[$idx];

			if (isset($RowAtts[$idx]))
				$RowAtt = $RowAtts[$idx];

			if (isset($Record['AccessFlags']))
				$AccessLevel = $Record['AccessFlags'];
			else
				$AccessLevel = 'Full';

			$DataGroupAccessLevel = $AccessLevel;
			$EffectiveAccessLevel = $AccessLevel;
			
			// Private datagroups are those data groups whose ParentDataGroupId is not null.
			// These datagroups are used for case copies in workflows like Outgoing Consult, etc.
			// Private datagroups are not returned by the DataServer ListDataGroups method,
			// so they will not be present in $_SESSION['User']['DataGroups']
			$PrivateDataGroup = false;
			if (isset($Record['UpToDataGroupsByDataGroupId.Name']))
			{
				// assume it is a private datagroup
				$PrivateDataGroup = true;
				// users have Full access to their private datagroups
				$DataGroupAccessLevel = 'Full';
				$DataGroupName = $Record['UpToDataGroupsByDataGroupId.Name'];
				foreach ($_SESSION['User']['DataGroups'] as $DataGroup)
				{
					if ($DataGroup->DataGroupName == $DataGroupName)
					{
						// found the datagroup, therefore it is not a private datagroup
						$PrivateDataGroup = false;
						// get the datagroup access level. it is possible for a user to have Full access to a row,
						// and have Read access to the datagroup for the row. This happens when a user is granted
						// direct access to an entity that is in a Readonly datagroup.
						$DataGroupAccessLevel = $DataGroup->AccessFlags;
						break;
					}
				}
			}

			// if this is a private datagroup, need to set the EffectiveAccessLevel to 'Read'
			// This is necessary so that bulk links that require full control will not enabled. If they were
			// enabled, then attempts to Add, Move etc. would result in an error stating that the Datagroup
			// is not in the list of choices. This is because DataServer does not return private datagroups
			// in ListDataGroups. If DataServer is modified to return private datagroups (as it was in the AMN project),
			// then the EffectiveAccessLevel logic can be removed from here and from list.js.
			if ($PrivateDataGroup)
				$EffectiveAccessLevel = 'Read';
			foreach ($RowAtt as $Key => $Value)
				$NewRow->SetAttribute($Key, $Value);
			$NewRow->SetAttribute('class', $RowClass);
			$NewRow->SetAttribute('AccessLevel', $AccessLevel);
			$NewRow->SetAttribute('EffectiveAccessLevel', $EffectiveAccessLevel);
			$NewRow->SetAttribute('DataGroupAccessLevel', $DataGroupAccessLevel);
			$NewRow->SetRecord($Record);
			$this->Rows[] = $NewRow;

			// Alternate row background colors
			if ($RowClass == 'DataRow')
				$RowClass = 'DataRow Alt';
			else
				$RowClass = 'DataRow';


			if ($this->Mode != 'Listing')
			{
				$ChildTable = $this->TableSchema->GetChildTable();
				if ($ChildTable)
				{
					$this->EnableChildExpansion($this->EnableChildExpansion);

					// Create grandchild listing
					$ChildTableName = $ChildTable->TableName;
					$Id = $Record['Id'];
					$ChildName = 'List' . $ChildTableName . $Id;

					$Record['ChildListName'] = $ChildName;	// For cExpansionField

					// And add a new row (for all children/grandchildren)
					$ChildRow = new cRow($this);
					$ChildRow->SetAttribute('ChildListName', $ChildName);

					$ChildState = GetListStateObj($ChildName, $ChildTableName);
					$ChildState->Mode = 'Details';
					if (count($this->Records) == 1)
					{
						// Display the slides if there is only one specimen (legacy behavior)
						$ChildState->IsExpanded = true;
					}
					else
						$ChildState->IsExpanded = false;
					if ($ChildState->IsExpanded)
					{
						$ChildRow->SetAttribute('ChildExpanded', 'true');
					}

					$Searcher = new cSearcher($this->TableName);
					$Searcher->AddFilter($this->TableName, 'Id', '=', $Id);
					$ChildState->Searcher = $Searcher;

					$this->Rows[] = $ChildRow;
				}
			}
		}

		return $this->Rows;
	}


	// cList
	public static function PosCmp(cColumn $a, cColumn $b)
	{
		return $a->Position - $b->Position;
	}
	public function OrderColumns()
	{
		usort ($this->Columns, 'cList::PosCmp');

		// Convert number indexes to name indexes and remove nonvisible fields
		$NumCols = count($this->Columns);
		for ($i = 0; $i < $NumCols; $i++)
		{
			$Column = $this->Columns[$i];
			if ($Column->Field && ($Column->Field->IsVisible()))
				$this->Columns[$Column->Field->ColumnName] = $Column;
			unset($this->Columns[$i]);
		}
	}

	// cList
	public function NumRecords()
	{
		return count($Records);
	}


	/**
	* Populate the given DOMElement with the list data
	* 
	* @param DOMElement $OuterNode	DOMElement within $DOM to populate
	*/
	// cList
	public function ToHTML ($OuterNode)
	{
		global $PALMode;
		SetCurrentNode($this->Name);

		if (empty($this->Rows))
			$this->BuildRows();

		$DOM = &$OuterNode->ownerDocument;
		$this->SetDOM($DOM);

		$RootNode = $OuterNode->appendChild ($this->CreateRootNode($DOM));

		if ($this->AllowEdits)
			$RootNode->setAttribute('ReadOnly', '0');
		else
			$RootNode->setAttribute('ReadOnly', '1');


		if ($this->BulkCommandsEnabled)
			$this->AppendBulkForm ($RootNode);

		// Display the auto view check box if AutoView is enabled
		// and if the AutoSelectCommon field is present
		if ($this->AllowAutoView && $this->GetColumnHeader($this->AutoSelectCommonField) != null)
		{
			$AutoViewDiv = $DOM->createElement('DIV');
			$RootNode->appendChild($AutoViewDiv);
			$AutoViewDiv->setAttribute('class', 'AutoView');

			$CheckBox = $DOM->createElement('INPUT');
			$AutoViewDiv->appendChild ($CheckBox);
			$CheckBox->setAttribute('TYPE', 'checkbox');
			if (isset($_COOKIE['AutoViewSelectedImages']) && $_COOKIE['AutoViewSelectedImages'] == 1)
				$CheckBox->setAttribute('checked', 'true');

            //$CheckBox->setAttribute('onclick', 'List' . $this->TableName . '.AutoViewSelectedImages(this);');
			$CheckBox->setAttribute('name', 'AutoViewNode');
            $CheckBox->setAttribute('message', '');
            $CheckBox->setAttribute('idfields', 'ImageIds'); 
            $CheckBox->setAttribute('formaction', 'javascript: this.BulkViewWithImageScopeOrBrowser();');
            $CheckBox->setAttribute('onclick', 'javascript: ListSpot.AutoViewSelectedImages();');

			$CheckText = $DOM->createTextNode('Auto View Selected ' . $this->PluralRecordsLabel);
			$AutoViewDiv->appendChild ($CheckText);
		}


		if (($this->BulkCommandsEnabled || ($this->RecordsPerPage > 0)) && $this->DisplayPaginator)
		{
			//
			// The Header row includes the Bulk Tasks and the upper Paginator
			//

			$ListHeader = $RootNode->appendChild ($DOM->createElement ('TABLE'));
			$ListHeader->setAttribute ('class', 'ListMargin ListHeader');

			$ListHeaderRow = $ListHeader->appendChild ($DOM->createElement ('TBODY'))
							->appendChild ($DOM->createElement ('TR'));
			$ListHeaderRow->setAttribute ('id', 'ListHeaderRow');
			$ListHeaderRow->appendChild ($this->GetBulkTasksHTML ($DOM));
			$ListHeaderRow->appendChild ($this->GetPaginatorHTML ($DOM));


			//
			// The selection row for selecting previously-selected records on other pages
			//

			$SelectTable = $RootNode->appendChild ($DOM->createElement ('TABLE'));
			$SelectTable->setAttribute ('class', 'ListMargin ListTable');
			$SelectTbody = $SelectTable->appendChild ($DOM->createElement ('TBODY'));
			$SelectTbody->setAttribute ('class', 'SelectClass');

			$HiddenSelectRow = $SelectTbody->appendChild ($DOM->createElement ('TR'));
			$HiddenSelectRow->setAttribute ('class', 'HiddenSelectRow');
			$HiddenSelectRow->setAttribute ('style', 'display: none;');

			$HiddenSelectCell = $HiddenSelectRow->appendChild ($DOM->createElement ('TD'));

			$HiddenSelectInput = $DOM->createElement ('INPUT');
			$HiddenSelectCell->appendChild ($HiddenSelectInput);
			$HiddenSelectInput->setAttribute ('class', 'HiddenSelectCheckBox');
			$HiddenSelectInput->setAttribute ('type', 'checkbox');
			$HiddenSelectInput->setAttribute ('onclick', 'OnHidden(this);');

			$HiddenSelectCount = $DOM->createElement ('B');
			$HiddenSelectCell->appendChild ($HiddenSelectCount);
			$HiddenSelectCount->setAttribute ('class', 'HiddenSelectCount');
			$HiddenSelectCount->setAttribute ('style', 'padding-left: 10px; padding-right: 3px;');
			$HiddenSelectCount->appendChild ($DOM->createTextNode (' '));

			$HiddenSelectCell->appendChild ($DOM->createTextNode ('items selected on other pages'));
		}

		//
		// The List Table Div is the block that controls list scrolling within the page
		//
		$ListTableDiv = $RootNode->appendChild ($DOM->createElement ('DIV'));
		$ListTableDiv->setAttribute ('class', 'ListTableDiv');
		/*if ($this->FloatingHeadersEnabled)
		{
			// Box the table, create scrollbars and floating header
			$ListTableDiv->setAttribute ('style', 'overflow:auto');
			$ListTableDiv->setAttribute ('onscroll', $this->Name . '.AlignHeaders ();');
		}*/

		// Now for the actual List Table
		$ListTable = $ListTableDiv->appendChild ($DOM->createElement ('TABLE'));
		$ListTable->setAttribute ('class', 'ListTable');
        $ListTable->setAttribute ('ClassListType', get_class($this->TableSchema));        
        
        //by default $this->PALModePostfix is an empty string
        //when the list is operating in "PAL" mode the filters are used to generate a unique id for the list
        $cListName = trim($this->Name . $this->PALModePostfix);
        $ListTable->setAttribute ('name', $cListName );

		//if the current table is a cViewSchema then set the tableschema to the basetable name of the view
        if(is_object($this->TableSchema))
        {
            if (0 === strcasecmp('cViewSchema', get_class($this->TableSchema)))
            {
                $ListTable->setAttribute ('id',  'cViewSchema_'  . $cListName  );
            }            
        }
                
		$Header = $DOM->createElement ('thead');
		$ListTable->appendChild($Header);
		$ListTableBody = $ListTable->appendChild ($DOM->createElement ('TBODY'));
		$this->AccessLevel = 'Default';

		if (count($this->Rows) > 0)
		{
			//
			// Display header(s) & all rows
			//

			// Draw header
			$this->GetHeaderRowHTML ($Header, 0, count($this->Rows));

			foreach ($this->Rows as $Row)
			{
				// Draw next row
				$Row->AddRowHTML ($this, $ListTableBody, $this->Columns);
				$RowAccessLevel = $Row->GetAttribute('AccessLevel');
				if ($RowAccessLevel)
					$this->AccessLevel = GetHighestAccessLevel($this->AccessLevel, $RowAccessLevel);
			}
		}
		else if (strpos($this->Name, 'Template') == false)
		{
			$NoRecords = $ListTableBody->appendChild ($DOM->createElement ('TR'))->appendChild ($DOM->createElement ('TH'));
			$NoRecords->setAttribute ('class', 'NoListTable');
			$NoRecords->appendChild ($DOM->createTextNode (' (No data to display) '));
		}


		//
		// Footer
		//

		if (($this->EditsEnabled) && ($this->AccessLevel == 'Full'))
			$EnableChanges = true;
		else
			$EnableChanges = false;
		if ($this->EnableCountChange)
			$DisplayRecordsPerPage = true;
		else
			$DisplayRecordsPerPage = false;

		$ListFooter = $RootNode->appendChild ($DOM->createElement ('TABLE'));
		$ListFooter->setAttribute ('class', 'ListMargin ListFooter');
		$ListFooterRow = $ListFooter->appendChild ($DOM->createElement ('TBODY'))
								->appendChild ($DOM->createElement ('TR'));
		$ListFooterRow->appendChild ($this->GetErrorBox());

		if ($this->ShowSaveButtons)
		{
			$Cell = $ListFooterRow->appendChild($DOM->createElement('TD'));
			AddButton($Cell, 'Save', $this->Name, 'SaveAll(); return false;', true);
			AddButton($Cell, 'Reset', $this->Name, 'ResetAll(); return false;', true);
		}

		if ($DisplayRecordsPerPage)
			$ListFooterRow->appendChild ($this->GetRecordCounterHTML ($DOM));

		//
		// Create javascript object for this list
		//

		$Script = $RootNode->appendChild ($DOM->createElement ('SCRIPT'));
		$Script->setAttribute ('type', 'text/javascript');
        
		if (isset($PALMode))
        {
			$cListName = trim($this->Name . $this->PALModePostfix);			
			if (true === $this->EnableJavascriptCList)
			{ 
				$Script->appendChild ($DOM->createTextNode ("var $this->Name = new cList ('$cListName', true);"));
			}
        }
		else
        {			
			if (true === $this->EnableJavascriptCList)
			{ 
				$Script->appendChild ($DOM->createTextNode ("var $this->Name = new cList ('$this->Name', false);"));
			}						
        }
		if ($this->AddBlankRow)
			$Script->appendChild ($DOM->createTextNode ("SetRefreshAfterSave('true')"));

		$this->Clean();	// free memory
	}


	/**
	 * Format the list as JSON and send to the browser
	 */
	// cList
	public function SendJSON ()
	{
		$DOM = $this->GetDOM();

		$Response = array
		(
			'ListName' => $this->Name,
			'TotalCount' => $this->TotalCount,
			'Page' => $this->Page,
			'RecordsPerPage' => $this->RecordsPerPage,
			'Headers' => array(),
			'Rows' => array (),
		);

		if (empty($this->Rows))
			$this->BuildRows();

		foreach ($this->Columns as $Name => $Column)
		{
			$Header = $Column->Header;
			$Cell = $Header->GetFieldHeaderHTML($DOM, $this->Name, 0, $this->RecordsPerPage);
			//$Field = $DOM->saveXML($Cell);
			//$Response['Headers'][$Name]['Text'] = $Field;
			$Field = ExtractCell($Cell);
			$Response['Headers'][$Name]['Contents'] = $Field;
		}

		foreach ($this->Rows as $Row)
		{
			$Row->AddRowJSON ($Response['Rows']);
		}

		$this->Clean();	// free memory

		AjaxReply($Response);
	}


	/**
	 * Deprecated - either use SendJson() or collapse the code
	 */
	// cList
	public function GetJSON ()
	{
		global $PALMode;
		$DOM = $this->GetDOM();

		$Data = array
		(
			'TotalCount' => $this->TotalCount,
			'Page' => $this->Page,
			'RecordsPerPage' => $this->RecordsPerPage,
			'Headers' => array(),
			'Rows' => array (),

		);
		if (isset($PALMode))                    
			$Data['PALMode'] = 'true';
				
		if (empty($this->Rows))
			$this->BuildRows();

		foreach ($this->Columns as $Name => $Column)
		{
			$Header = $Column->Header;
			$Cell = $Header->GetFieldHeaderHTML($DOM, $this->Name, 0, $this->RecordsPerPage);
			//$Field = $DOM->saveXML($Cell);
			//$Data['Headers'][$Name]['Text'] = $Field;
			$Field = ExtractCell($Cell);
			$Data['Headers'][$Name]['Contents'] = $Field;
			if ($Cell->getAttribute('locked') == 'true')
			{
				$Data['Headers'][$Name]['locked'] = 'true';
			}
			if ($Cell->getAttribute('align') != '')
			{
				$Data['Headers'][$Name]['align'] = $Cell->getAttribute('align');
			}
			if ($Cell->getAttribute('width') != '')
			{
				$Data['Headers'][$Name]['width'] = $Cell->getAttribute('width');
			}
		}

		foreach ($this->Rows as $Row)
		{
			$Row->AddRowJSON ($Data['Rows']);
		}

		$this->Clean();	// free memory
		return json_encode ($Data);
	}

	// cList
	private function CreateRootNode($DOM)
	{
		global $PALMode;
		$RootNode = $DOM->createElement ('DIV');

		//default:   $this->PALModePostfix is empty 
        $cListName = $this->Name . $this->PALModePostfix;
		$RootNode->setAttribute ('id', $cListName);
		$RootNode->setAttribute ('name', $cListName);
        
		if ($this->Mode == 'GrandChildListing')
			$RootNode->setAttribute ('class', 'GrandChildTable');
		if ($this->IsExpanded())
		{
			$RootNode->setAttribute ('HasExpanded', '1');
		}
		else
		{
			$RootNode->setAttribute ('HasExpanded', '0');
			$RootNode->setAttribute ('style', 'display:none');
		}
		$RootNode->setAttribute ('action', '#');
		$RootNode->setAttribute ('method', 'post');
		if (count($this->IdFields) == 2)
			$RootNode->setAttribute ('HasImages', 'true');
		else
			$RootNode->setAttribute ('HasImages', 'false');
		$RootNode->setAttribute ('PluralRecordsLabel', $this->PluralRecordsLabel);
		$RootNode->setAttribute ('Page', $this->Page);
		$RootNode->setAttribute ('RecordsPerPage', $this->RecordsPerPage);
		$RootNode->setAttribute ('EnableCountChange', $this->EnableCountChange);
		$RootNode->setAttribute ('AllowBulkCommands', $this->BulkCommandsEnabled ? 'true' : 'false');
		$RootNode->setAttribute ('AllowBulkSelect', $this->BulkSelectEnabled ? 'true' : 'false');
		$RootNode->setAttribute ('TotalCount', $this->TotalCount);
		$RootNode->setAttribute ('SortField', join(",", $this->GetSortFields()));
		$RootNode->setAttribute ('SortOrder', join(",", $this->GetSortOrders()));
		if (isset($PALMode))
			$RootNode->setAttribute ('RefreshURL', $this->RefreshURL . "?PALMode=true");
		else
			$RootNode->setAttribute ('RefreshURL', $this->RefreshURL);

        $RootNode->setAttribute ('TableName', $this->TableName);
                
		//if the current table is a cViewSchema then set the tableschema to the basetable name of the view
        if(is_object($this->TableSchema))
        {
        	if (0 === strcasecmp('cViewSchema', get_class($this->TableSchema)))
            {
                $RootNode->setAttribute ('TableName', $this->TableSchema->BaseTableName);
            }            
        }        

		$RootNode->setAttribute ('Columns', implode (',', array_keys ($this->Columns)));

		$RootNode->setAttribute ('TaskParameters', implode (',', $this->TaskParameters));
		//$RootNode->setAttribute ('FloatingHeadersEnabled', $this->FloatingHeadersEnabled ? 'true' : 'false');
		$RootNode->setAttribute ('StretchWidth', $this->StretchWidth ? 'true' : 'false');
		$RootNode->setAttribute ('AllowEditFields', $this->EditsEnabled ? 'true' : 'false');
		$RootNode->setAttribute ('EditURL', $this->EditURL);

		if (empty($this->SaveParameters) == false)
		{
			$Parms = implode('&', $this->SaveParameters);
			$RootNode->setAttribute('SaveParms', $Parms);
		}

		return $RootNode;
	}

	// The Bulk Form is used when performing php bulk command operations.
	// It will be populated with Ids when the user checks the bulk action box.
	// cList
	private function AppendBulkForm($Node)
	{
		$DOM = $Node->ownerDocument;
		$BulkForm = $DOM->createElement ('FORM');
		$BulkForm->setAttribute ('class', 'BulkForm');
		$BulkForm->setAttribute ('method', 'POST');
		$BulkForm->setAttribute ('action', '/BulkAction.php');

		$BulkForm->appendChild ($DOM->createTextNode (' '));

		return $Node->appendChild ($BulkForm);
	}

	/**
	 * Output the HTML representation of the bulk tasks
	 *
	 */
	// cList
	private function GetBulkTasksHTML (DOMDocument $DOM)
	{
		$Cell = $DOM->createElement ('TD');
		$Cell->setAttribute ('class', 'BulkTasks');

		if ($this->Mode == 'Listing')
			$BulkTasks = $this->BulkTasks;
		else
			$BulkTasks = $this->BulkTasksChild;

		if (($this->BulkCommandsEnabled == false) || (count($BulkTasks) == 0))
		{
			$Cell->setAttribute('style', 'display:none');
			return $Cell;
		}

		// if (($this->BulkSelectEnabled) && (count($this->Rows) > 0))
		if ($this->BulkSelectEnabled)	// Bulk commands needed for template cList (no rows)
			$BulkSelectEnabled = true;
		else
			$BulkSelectEnabled = false;

		$separator = false;

		foreach ($BulkTasks as $BulkTask)
		{
			$Link = $DOM->createElement ('A');
			$Link->setAttribute ('name', 'BulkTask[]');
			if ($BulkTask->MinAccessLevelRequired == 'None')
			{
				// This command is always present and active, eg. 'Add'
				$Link->setAttribute ('class', 'BulkTaskLink');
			}
			else
			{
				if ($BulkSelectEnabled == false)
					continue;
				$Link->setAttribute ('class', 'BulkTaskLink Disabled');
			}
			$Link->setAttribute ('MinAccessLevelRequired', $BulkTask->MinAccessLevelRequired);
			$Link->setAttribute ('href', '#');
			$Link->setAttribute ('onclick', 'ListAction(this); return false;');
			$Link->setAttribute ('IdFields', $BulkTask->Ids);
			$Link->setAttribute ('SelectOne', $BulkTask->SelectOne ? 'true' : 'false');
			$Link->setAttribute ('FormAction', $BulkTask->FormAction);
			$Link->setAttribute ('Message', $BulkTask->ConfirmMessage);
			$Link->appendChild ($DOM->createTextNode ($BulkTask->Text));

			// Place the seperator between links
			if ($separator)
				$Cell->appendChild ($DOM->createTextNode (' | '));

			$Cell->appendChild($Link);

			// add dropdown if dropdown options exist
			if (count($BulkTask->BulkTaskOptions) > 0)
			{
				// create a div tag to show/hide the select tag
				$Cell->appendChild ($DOM->createTextNode (' '));

				$DivTag = $Cell->appendChild ($DOM->createElement ('Div'));
				$DivTag->setAttribute ('style', 'display:none; margin:0; padding:0');

				// create the select tag
				$SelectTag = $DivTag->appendChild ($DOM->createElement ('SELECT'));
				$SelectTag->setAttribute ('Message', $BulkTask->ConfirmMessage);
				$SelectTag->setAttribute ('onChange', "ListActionOption(this); return false;");
				$SelectTag->setAttribute ('Name', $BulkTask->DropDownName);

				// add the options
				$OptionTag = $SelectTag->appendChild ($DOM->createElement ('OPTION'));
				$OptionTag->appendChild ($DOM->createTextNode ('--Select--'));
				foreach($BulkTask->BulkTaskOptions as $BulkTaskOption)
				{
					$OptionTag = $SelectTag->appendChild ($DOM->createElement ('OPTION'));
					$OptionTag->setAttribute ('value', $BulkTaskOption->OptionValue);
					$OptionTag->setAttribute ('action', $BulkTaskOption->OptionAction);
					$OptionTag->appendChild ($DOM->createTextNode ($BulkTaskOption->OptionText));
				}
			}
			$separator = true;
		}

		return $Cell;
	}

	/**
	 * Output the HTML representation of the list paginator
	 */
	// cList
	private function GetPaginatorHTML (DOMDocument $DOM)
	{
		$Cell = $DOM->createElement ('TD');

		if ($this->RecordsPerPage == 0)
		{
			// No pagination, return empty cell
			return $Cell;
		}

		$MaxPages = ($this->TotalCount >= 0 && $this->RecordsPerPage > 0) ?
					ceil ($this->TotalCount / $this->RecordsPerPage) :
					0;

		// For some reason the automatic sizing of the select field does not work here.
		// Estimate it ourselves.  Allow 8 (pixels 
		// for each char and 30 for the chevron & borders.
		$pageSelectWidth = ((1 + log10($MaxPages)) * 8) + 30;
		$widthStr = "width: $pageSelectWidth" . 'px';

		$Cell->setAttribute ('class', 'Paginator');
		$Cell->appendChild ($DOM->createTextNode ('Page '));

		if ($MaxPages <= 100)
		{
			$Paginator = $Cell->appendChild ($DOM->createElement ('SELECT'));

			if ($MaxPages >= 1)
			{
				for ($PageSelect = 1; $PageSelect <= $MaxPages; $PageSelect++)
				{
					$Option = $Paginator->appendChild ($DOM->createElement ('OPTION'));
					$Option->setAttribute ('value', $PageSelect);
					if ($this->Page == $PageSelect)
						$Option->setAttribute ('selected', 'selected');
					$Option->appendChild ($DOM->createTextNode ($PageSelect));
				}
			}
			else
			{
					$Option = $Paginator->appendChild ($DOM->createElement ('OPTION'));
					$Option->setAttribute ('value', 1);
					$Option->setAttribute ('selected', 'selected');
					$Option->appendChild ($DOM->createTextNode ('1'));
			}
		}
		else
		{
			// Dropdown is too large, draw a text box
			$Paginator = $Cell->appendChild ($DOM->createElement ('INPUT'));
			$Paginator->setAttribute ('type', 'text');
			$Paginator->setAttribute ('maxLength', strlen ($MaxPages));
			$Paginator->setAttribute ('onkeypress', 'return CheckForInteger(event)');
			$Paginator->setAttribute ('disabled', 'true');
		}
		$Paginator->setAttribute ('name', 'PageNumber');
		$Paginator->setAttribute ('style', $widthStr);
		$Paginator->setAttribute ('onchange', 'GoToPage(this);');

		$Cell->appendChild ($DOM->createTextNode (" of $MaxPages ($this->TotalCount $this->PluralRecordsLabel) ("));

		$Previous = $Cell->appendChild ($DOM->createElement ('A'));
		$Previous->setAttribute ('href', '#');
		$Previous->setAttribute ('FormAction', "javascript: this.GoToPage('-')");
		$Previous->setAttribute ('onclick', 'ListAction (this); return false;');
		$Previous->setAttribute ('class', 'Disabled');
		$Previous->appendChild ($DOM->createTextNode ('Previous'));
		$Previous->setAttribute ('name', 'Previous');

		$Cell->appendChild ($DOM->createTextNode (' | '));

		$Next = $Cell->appendChild ($DOM->createElement ('A'));
		$Next->setAttribute ('href', '#');
		$Next->setAttribute ('FormAction', "javascript: this.GoToPage('+')");
		$Next->setAttribute ('onclick', 'ListAction (this); return false;');
		$Next->setAttribute ('class', 'Disabled');
		$Next->setAttribute ('name', 'Next');
		$Next->appendChild ($DOM->createTextNode ('Next'));

		$Cell->appendChild ($DOM->createTextNode (')'));

		return $Cell;
	}

	// cList
	private function GetErrorBox ()
	{
		$DOM = $this->GetDOM();
		$Cell = $DOM->createElement ('TD');
		$Cell->setAttribute ('class', 'ErrorBox');
		$Cell->appendChild ($DOM->createTextNode (' '));
		return $Cell;
	}


	/**
	 * Output the HTML representation of the list record counter
	 */
	// cList
	private function GetRecordCounterHTML (DOMDocument $DOM)
	{
		$Cell = $DOM->createElement ('TD');
		$Cell->setAttribute ('class', 'RecordCounter');

		if (!$this->EnableCountChange)
			$Cell->setAttribute ('style', 'display: none;');

		$Cell->appendChild ($DOM->createTextNode ('Display '));

		$Input = $Cell->appendChild ($DOM->createElement ('INPUT'));
		$Input->setAttribute ('name', 'RecordsPerPage');
		$Input->setAttribute ('type', 'text');
		$Input->setAttribute ('maxlength', 3);
		// Limit the records per page to 100
		$Input->setAttribute ('onchange', "SetRecordsPerPage(this);");
		$Input->setAttribute ('onkeypress', "CheckKeyCode(this,event);");
		$Input->setAttribute ('value', $this->RecordsPerPage);

		$Cell->appendChild ($DOM->createTextNode (' Records Per Page'));

		return $Cell;
	}
	
	/**
	 * Output the HTML representation of an anchor header above a list grouping
	 *
	 * @param string $HeaderClass - Name of class for display
	 * @param int $FirstIndex - Which row index the grouping begins with
	 * @param int $LastIndex - Index of the last row in the list grouping
	 */
	// cList
	protected function GetHeaderRowHTML ($TableBody, $FirstIndex = 0, $LastIndex = 0)
	{
    	if (true === $this->ShowColumnHeaders )
        {
			$DOM = $TableBody->ownerDocument;
			$Row = $DOM->createElement ('TR');
			$Row->setAttribute ('class', 'HeaderRow');

			// Add the column headers
			foreach ($this->Columns as $Column)
			{
				$Row->appendChild ($Column->Header->GetFieldHeaderHTML ($DOM, $this->Name, $FirstIndex, $LastIndex));
			}

			$TableBody->appendChild($Row);
        }
	}

	// cList
	public function AutoSelectCheck($Record, $Cell)
	{
		if ($this->AutoSelectCommonField)
		{
			$AutoSelectCommonField = $this->AutoSelectCommonField;
			if (isset($Record[$AutoSelectCommonField])  && ($Record[$AutoSelectCommonField] != ''))
			{
				// AutoSelectCookieName will be set as an attribute on this checkbox
				// so that when it is checked/unchecked the appropriate cookie can be set/unset.
				$AutoSelectCookieName = $this->Name . 'AutoSelect' . $Record[$AutoSelectCommonField];
				//$FormattedRow['BulkCheck']->firstChild->setAttribute ('AutoSelectCookieName', $AutoSelectCookieName);
				$Cell->firstChild->setAttribute ('AutoSelectCookieName', $AutoSelectCookieName);
				// cookie determines whether to auto select this row
				if (isset($_COOKIE[$AutoSelectCookieName]) && $_COOKIE[$AutoSelectCookieName] == '1')
					$Cell->firstChild->setAttribute ('checked', 'true');  
			}
		}
	}
    /**
     * Get the current cList Sort Fields as an array of strings.  
     * Use in conjunction with GetSortFields() to set the cDataBaseReader() sorting for the list
     * @return array Each entry is a string which is the key in the $this->Columns[]     
     */
    public function GetSortFields()
    {        
        $localSortFieldArray = array();
        if ($this->CurrentSortFields)
        {
            foreach ($this->CurrentSortFields as $sf)
            {
                $localSortFieldArray[] = $sf->GetSortField();                
            }            
        }
        return $localSortFieldArray;
    }    
    /**
     * Get the current cList Sort Orders as an array of strings.  
     * Use in conjunction with GetSortFields() to set the cDataBaseReader() sorting for the list
     * @return array [Ascending or Descending]     
     */
    public function GetSortOrders()
    {
        $localSortOrderArray = array();
        if ($this->CurrentSortFields)
        {
            foreach ($this->CurrentSortFields as $sf)
            {
                $localSortOrderArray[] = $sf->GetSortOrder();                
            }            
        }
        return $localSortOrderArray;              
    }
    /**
     * Get the current cList Sort Order for a column
     * Default to empty
     * @return string [Ascending or Descending]     
     */
    private function GetSortOrderForColumn($ColumnName)
    {        
        $localSortOrderArray = $this->GetSortOrders();
        $localSortFieldArray = $this->GetSortFields();
        
        if ($this->CurrentSortFields)
        {
            // Set the sort parameters
            $sortCount = count($localSortOrderArray);
            for ($i = 0; $i < $sortCount; $i++) 
            {
                $sortFieldName = $localSortFieldArray[$i];
                $sortOrderName = $localSortOrderArray[$i];   
                
                if ($ColumnName === $sortFieldName)
                {
                    return $sortOrderName;
                }       
            }
        }
        return '';               
    }   
	/**
	 * If true, output a javascript cList with the PHP in the ToHTML().  
	 * Allows the cList to be 'updateable, sortable, savable, etc. with the operations queue'
	 * @param bool $enable 
	 */
	public function SetEnableJavascriptCList($enable)
	{
		$this->EnableJavascriptCList = $enable;	
	}
}


/**
* @package List
*/
class cBulkTask
{
	public $SelectOne				= false;	// Whether to skip the check to save data when activating the task.
	public $Text 					= '';		// String to display
	public $Ids						= '';		// Which Id this task should be linked to
	public $FormAction				= '';		// Target to submit bulk items (example "BulkAction.php")
	public $ConfirmMessage			= '';		// Text to be displayed before performing or cancelling action
	public $MinAccessLevelRequired	= 'None';	// Enables/disables link based on min access level of 
	public $BulkTaskOptions			= array();	// array of bulktaskoption objects which will appear in a dropdown list.
	public $DropDownName			= array();  // name of select element (dropdown) that gets created for bulk task options
												// selected rows "Read", "Full" ("None" means no selection required)
}


/**
* @package List
*/
class cBulkTaskOption
{
	public $OptionText = '';					// text displayed in dropdown list
	public $OptionAction = '';					// action associated with dropdown option. will be assigned to Form.action when chosen or if it's javascript it'll be executed
	public $OptionValue = '';				   // passed as form value
}


class cColumn
{
	public	$Header;
	public	$Field;
	public	$Visible = true;
	public	$Position = 0;

	public function __construct ($Header, $Field)
	{
		if ($Header)
			$this->Header = $Header;
		else
			$this->Header = new cColumnHeader ($Field->ColumnName, $Field->DisplayName, $Field->Sortable, '', $Field->DisplayWidth);

		$this->Field = $Field;
		if ($Field)
		{
			$IsVisible = $Field->IsVisible();
			$this->Position = $Field->Position;
		}
		else
		{
			// Position set by caller
		}
	}
}


/**
* @author Steven Hashagen <shashagen@aperio.com>
* @author Tristan Hoare <thoare@aperio.com>
* 
* @package List
*/
class cColumnHeader
{
	public $Key;						// unique Id string for this Column
	public $Text;						// string to display
	public $CanSort; 					// true if the field can be sorted on
	public $AllowSort;
	public $Sorted;						// "Ascending", "Descending", or ""
	public $Width;
	public $Locked;						// if true, column is locked when displayed in a grid
	public $Align;						// column alignment in grid

	/**
	 * Constructor - Sets basic properties of the column header
	 *
	 * @param string $Key - Internal name for the column
	 * @param string $Text - Display name for the column
	 * @param bool $CanSort - Whether the column can be sorted on
	 * @param string $Sorted - Whether the column is sorted, and which direction
	 * 
	 * @return cColumnHeader - The newly created cColumnHeader
	 */
	// cColumnHeader
	public function __construct ($Key, $Text, $CanSort = false, $Sorted = '', $Width = NULL)
	{
		$this->Key 		= $Key;
		$this->Text 	= $Text;
		$this->CanSort 	= $CanSort;
		$this->AllowSort = $CanSort;
		$this->Sorted 	= $Sorted;
		$this->Width	= $Width;
		$this->Locked 	= false;
		$this->Align	= '';
	}

	/**
	 * Prints out the <th> tag containing the column header including the sorting link and arrow (if necessary)
	 *
	 */
	// cColumnHeader
	public function GetFieldHeaderHTML (DOMDocument $DOM, $Name, $FirstIndex, $LastIndex)
	{
		$Cell = $DOM->createElement ('TH');
		if ($this->Locked)
			$Cell->setAttribute('locked', 'true');
		if ($this->Align != '')
			$Cell->setAttribute('align', $this->Align);
	
		// add the checkbox column if enabled
		if ($this->Key == 'BulkCheck')
		{
			$Input = $Cell->appendChild ($DOM->createElement ('INPUT'));
			$Input->setAttribute ('type', 'checkbox');
			$Input->setAttribute ('id', 'BulkSelectBox');
			$Input->setAttribute ('name', 'BulkSelectBox');
			$Input->setAttribute ('title', 'Select All/Unselect All');
			$Input->setAttribute ('onclick', 'BulkCheck(this);');
			$Input->setAttribute ('FirstIndex', $FirstIndex);
			$Input->setAttribute ('LastIndex', $LastIndex);
			return $Cell;
		}

		if ($this->Width)
		{
			$Cell->setAttribute('style', "width:$this->Width");
		}

		if ($this->AllowSort)
		{
			$NewSort = 'Descending';
			if ($this->Sorted == 'Descending')
				$NewSort = 'Ascending';

			$Link = $Cell->appendChild ($DOM->createElement ('A'));
			// removed $Link->setAttribute ('href', '#'); it messes up flexigrid column dragging
			$Link->setAttribute("style", "cursor:pointer;");
			$Link->setAttribute ('onclick', 'ColumnSort(event, this); return false;');
			$Link->setAttribute ('Key', $this->Key);
			$Link->setAttribute ('NewOrder', $NewSort);
            $Link->setAttribute('Message',"WARNING:  You are about to modify your saved Multi Sort.\n\n Are you sure you want to update the current sort order[s]?");

			$Link->appendChild ($DOM->createTextNode ($this->Text));

			// Draw the column header with an arrow if this is the sort column
			switch ($this->Sorted)
			{
			case 'Ascending':
				$Cell->appendChild ($DOM->createTextNode (html_entity_decode(" &#8593;", ENT_NOQUOTES, 'UTF-8')));
				break;
			case 'Descending':
				$Cell->appendChild ($DOM->createTextNode (html_entity_decode(" &#8595;", ENT_NOQUOTES, 'UTF-8') ));
				break;
			default:
				break;
			}
		}
		else
		{
			$Cell->appendChild ($DOM->createTextNode ($this->Text));
		}

		return $Cell;
	}

	/**
	 * @param unknown_type $DOM
	 */
	// cColumnHeader
	public function GetColumnHeaderXML ($DOM)
	{
		$HeaderNode = $DOM->createElement ('Header');

		$HeaderNode->setAttribute ('Key', $this->Key);
		$HeaderNode->setAttribute ('Text', $this->Text);
		$HeaderNode->setAttribute ('CanSort', $this->AllowSort);
		$HeaderNode->setAttribute ('Sorted', $this->Sorted);

		return $HeaderNode;
	}
}

/**
* @package List
*/
class cRow
{
	public	$Parent;
	public	$RowAttributes	= array();
	public	$FieldAttributes= array();
	public	$ChildListing	= NULL;
	private	$Record = NULL;
	private	$IsBlankRow = false;

	// cRow
	public function __construct ($Parent)
	{
		$this->Parent = $Parent;
		$this->SetAttribute('AccessLevel', 'Read');	// default
		$this->SetAttribute('DataGroupAccessLevel', 'Read');
	}

	// cRow
	public function SetAttribute ($Key, $Value)
	{
		if (is_array($Value))
			$Value = implode(',', $Value);
		$this->RowAttributes[$Key] = $Value;
	}

	// cRow
	public function GetAttribute ($Key)
	{
		if (isset($this->RowAttributes[$Key]))
			return $this->RowAttributes[$Key];
		return NULL;
	}

	public function SetRecord(&$Record)
	{
		$this->Record = &$Record;
	}

	public function SetBlankRow($Flag)
	{
		$this->IsBlankRow = $Flag;
	}

	/**
	 * Set the data value for the specified Column header key
	 *
	 * @param string $Key - Column key
	 * @param string $Value - Contents of the table cell
	 */
	// cRow
	public function SetDataValue ($FieldName, $Value)
	{
		$this->Record[$FieldName] = $Value;
	}

	/**
	 * Get the data value for the specified Column header key
	 *
	 * @param string $Key - Column key
	 * @return string - Contents of the table cell
	 */
	// cRow
	public function GetDataValue ($Key)
	{
		if (isset($this->Record[$Key]))
			return $this->Record[$Key];
		else
			return '';
	}

	// cRow
	public function SetFieldAttribute($FieldName, $Attribute, $Value)
	{
		$this->FieldAttributes[$FieldName][$Attribute] = $Value;
	}


	/**
	 * Append HTML code for the row
	 */
	// cRow
	public function AddRowHTML ($ParentList, DOMElement $ListTableBody, $Columns)
	{
		$DOM = $this->Parent->GetDOM();

		$Row = $ListTableBody->appendChild ($DOM->createElement ('TR'));

		// Set row's attributes
		foreach ($this->RowAttributes as $AttName => $Attribute)
			$Row->setAttribute ($AttName, $Attribute);

		if (isset($this->RowAttributes['ChildListName']))
		{
			$Cell = $Row->appendChild ($DOM->createElement ('TD')); // indent one cell
			$Cell = $Row->appendChild ($DOM->createElement ('TD'));
			$Cell->SetAttribute('align', 'left');
			$Cell->SetAttribute('colspan', '100');
			$Cell->SetAttribute('class', 'ChildCell');
			SetCurrentNode($ParentList->Name);	// reset node
			return;
		}

		// Append fields to the row

		$Columns = $this->Parent->Columns;
		if ($this->Parent->IsEditingEnabled() == false)
			$ReadOnly = true;
		else
			$ReadOnly = ($this->GetAttribute('AccessLevel') != 'Full');
		foreach ($Columns as $Column)
		{
			$FieldName = $Column->Header->Key;

			$Cell = $this->CreateCell($DOM, $Column, $ReadOnly);
			if ($Cell == NULL)
				continue;

			$Row->appendChild($Cell);

			if ($FieldName == 'BulkCheck')
				$this->Parent->AutoSelectCheck($this->Record, $Cell);

			// Set any field attributes
			if (isset ($this->FieldAttributes[$FieldName]))
			{
				foreach ($this->FieldAttributes[$FieldName] as $Name => $Value)
				{
					$Cell->setAttribute ($Name, $Value);
				}
			}
                        
            //if the table object is actually a view from DataView sql table then 
            //set the tablename of the cell to match the source table
            if (isset($Column->Field->RefTableName))
            {                                                
                $Cell->setAttribute ('TableName',    $Column->Field->RefTableName );

            }
            if (isset($Column->Field->RefColumnName))
            {                            
                $Cell->setAttribute ('ColumnName',    $Column->Field->RefColumnName );
            }            
		}
	}


	/**
	 * Output JSON code for the row
	 */
	// cRow
	public function AddRowJSON (&$Rows)
	{
		$DOM = $this->Parent->GetDOM();

		$Row = array
		(
			'Cells' => array (),
			'Attributes' => array (),
			'Class' => '',
		);

		foreach ($this->RowAttributes as $Name => $Value)
			$Row['Attributes'][$Name] = $Value;

		if (isset($this->RowAttributes['ChildListName']))
		{
			$Columns = $this->Parent->Columns;
			$Column = current($Columns);
			$FieldName = $Column->Header->Key;
			$Row['Cells'][$FieldName]['Contents'] = '';
			$Column = next($Columns);
			$FieldName = $Column->Header->Key;
			$Row['Cells'][$FieldName]['Contents'] = '';
			$Row['Cells'][$FieldName]['Attributes'] = array ();
			$Row['Cells'][$FieldName]['Attributes']['align'] = 'left';
			$Row['Cells'][$FieldName]['Attributes']['colSpan'] = '100';	// note 'colSpan', case sensitive
			$Row['Cells'][$FieldName]['Class'] = 'ChildCell';
			$Rows[] = $Row;
			return;
		}

		$Columns = $this->Parent->Columns;
		if ($this->Parent->IsEditingEnabled() == false)
			$ReadOnly = true;
		else
			$ReadOnly = ($this->GetAttribute('AccessLevel') != 'Full');
		foreach ($Columns as $Column)
		{
			$FieldName = $Column->Header->Key;

			$Cell = $this->CreateCell($DOM, $Column, $ReadOnly);
			if ($Cell == NULL)
				continue;

			$Atts = $Cell->attributes;
			$Row['Cells'][$FieldName]['Attributes'] = array ();
			for ($i = 0; $i < $Atts->length; $i++)
			{
				$Att = $Atts->item($i);
				$Row['Cells'][$FieldName]['Attributes'][$Att->name] = $Att->value;
			}

			// Extract field from the cell (TD)
			$Contents = ExtractCell($Cell);
			$Row['Cells'][$FieldName]['Contents'] = $Contents;
		}

		$Rows[] = $Row;
	}

	// cRow
	private function CreateCell($DOM, $Column, $ReadOnly)
	{
		$FieldName = $Column->Header->Key;
        
		if (isset($this->Record[$FieldName]) && is_object($this->Record[$FieldName]))
		{
			// Client already created the cell
			if ($this->Record[$FieldName]->ownerDocument === $DOM)
			{
				// field is from same DOM, return it
				return $this->Record[$FieldName];
			}
			else
			{
				// field is from a different DOM, import it
				return $DOM->importNode($this->Record[$FieldName], true);
			}
		}

		$Cell = $DOM->createElement('TD');

		if (($FieldName == 'BulkCheck') && $this->IsBlankRow)
		{
			return $Cell;
		}

		$Field = $Column->Field;
		if ($Field)
		{
			// if memo field or unlimited length text field, set field class so text will
			// be limited to 75 characters max
			if($Field->FieldType == 'Memo' || ($Field->FieldType == 'Text' && $Field->MaxLength == -1))
			{
				$Cell->setAttribute('class','field');
			}                                    
            //custom html table cell emitter for cViewSchema classes
			//adds: tablename, columnname, and primarykey to all cells in a html table
            if (0 === strcasecmp('cViewSchema', get_class($this->Parent->TableSchema)))
            { 
                // if the list object is a cViewSchema then put all of the critical columns
                // set in cViewSchema to a custom attributes on the  row 
                foreach ($this->Parent->TableSchema->Fields as $key => $ViewField)
                {
                    if (array_key_exists('IsCritical', $ViewField))
                    {
                        if (array_key_exists($key, $this->Record))
                        {               
                            if (0 !== strcasecmp($key, 'Id'))
                            {                                                           
                                $Cell->setAttribute($key, $this->Record[$key]);  
                            }
                        }
                    }
                }
                $Cell->setAttribute('DataViewName', $this->Parent->TableSchema->Name);                
            }
			$Options = array('ReadOnly' => $ReadOnly);
			$Field->ToHTML ($Cell, $this->Record, NULL, $Options);
		}
		else if (isset($this->Record[$FieldName]) && $this->Record[$FieldName] != '')
		{
			$Content = $DOM->createDocumentFragment ();
			$Content->appendXML ($this->Record[$FieldName]);
			$Cell->appendChild ($Content);
		}                
		return $Cell;
	}
}


// Object to save state of all temporary cLists.
// Since cLists are memory intensive, this allows them to be deleted and later reconstituted.
class cListState
{
	private	$Name;
	public	$ListObjName;
	public	$PageNum;
	public	$RecordsPerPage;
	public	$Mode;
	public	$IsExpanded;
	public	$Searcher;

	// cListState
	public function __construct ($Name, $ListObjName)
	{
		$this->Name = $Name;
		$this->ListObjName = $ListObjName;

		// Establish defaults
		$this->SetMode = 'Listing';	// default mode
		$this->IsExpanded = true;
		$this->PageNum = 1;
		$this->RecordsPerPage = GetRecordsPerPage();
		$this->Searcher = null;
	}

	// Update list from parameters (usually _REQUEST)
	// cListState
	public function Update($Parms)
	{
		if (isset ($Parms['Page']))
			$this->PageNum = $Parms['Page'];
		if (isset ($Parms['RecordsPerPage']))
			$this->RecordsPerPage = $Parms['RecordsPerPage'];
	}

	// cListState
	public function UpdateFromList (cList &$ListObj)
	{
		$this->Name = $ListObj->GetName();
		$this->SetExpansion($ListObj->IsExpanded());
		$this->Page = $ListObj->GetPageNum();
		$this->RecordsPerPage = $ListObj->GetRecordsPerPage();
		$this->Searcher = $ListObj->GetSearcher();
	}
}


/**************************************************************************************************
//  Functions
***************************************************************************************************/

function GetListObj($Name, $DoCreate=true) 
{
	$Array = $_SESSION['Lists'];
		
	if (isset ($Array[$Name]))
		return $Array[$Name];

	if ($DoCreate)
	{
		$ListStateObj = GetListStateObj($Name, null);
		if ($ListStateObj)
		{
			if ($Name != $ListStateObj->ListObjName)
			{
				$TemplateObj = GetListObj($ListStateObj->ListObjName);
				if ($TemplateObj)
				{
					$ListObj = CopyListObj($TemplateObj, $Name);
					$ListObj->SetState($ListStateObj);
					return $ListObj;
				}
			}
		}
	}

	if (strncmp($Name, 'List', 4) != 0)
	{
		// Client probably passed a tablename; convert to a list name & see if that exists
		$ListName = 'List' . $Name;
		if (isset ($Array[$ListName]))
			return $Array[$ListName];

		// List does not already exist, create it if possible
		if ($DoCreate)
		{
			$Table = GetTableObj($Name);
			if ($Table)
			{
				$ListObj = $Table->BuildListObj();
				return $ListObj;
			}
		}
	}

	return NULL;
}

// Return a copy of the requested list object
function CopyListObj($SrcListObj, $NewName)
{
	$CopiedListObj = new cList($NewName);
	$CopiedListObj->Copy($SrcListObj);
	return $CopiedListObj;
}


// Delete any cList pertaining to this table.
// This is done so  when tables are rebuilt (display changes) then all lists will reflect this change.
function DeleteListObjs($TableName)
{
	if (isset($_SESSION['Lists']) == false)
		return;
	$Array = $_SESSION['Lists'];

	foreach ($Array as $Name => $ListObj)
	{
		if ($ListObj->TableName == $TableName)
			unset($Array[$Name]);
	}
}

// Delete any temporary cLists
// This is done merely to keep memory usage under control
// Note: This should only be during a new page load, because AJAX called functions rely on existing cLists
function ClearListObjs()
{
	if (isset($_SESSION['Lists']) == false)
		return;
	$Array = $_SESSION['Lists'];
	if (isset($Array) == false)
			return;

	foreach ($Array as $Name => $ListObj)
	{
		if ($ListObj->IsPermanent() == false)
		{
            unset($_SESSION['Lists'][$Name]);
		}
	}
}

function GetListStateObj($Name, $ListObjName=null)
{
	if (isset ($_SESSION['ListStates'][$Name]) == false)
	{
		if ($ListObjName == null)
			return null;
		// Create a new state for this list
		$_SESSION['ListStates'][$Name] = new cListState($Name, $ListObjName);
	}
	return $_SESSION['ListStates'][$Name];
}

function IsListExpanded($ListName)
{
	$ListObj = GetListObj($ListName, false);
	if ($ListObj)
		return $ListObj->IsExpanded();
	$ListStateObj = GetListStateObj($ListName);
	if ($ListStateObj)
		return $ListStateObj->IsExpanded;
	return false;
}
	
function SortByPosition($a, $b)
{
	$Pos1 = intval($a->Position);
	$Pos2 = intval($b->Position);
	if ($Pos1 == $Pos2) {
	    return 0;
	}
	return ($Pos1 < $Pos2 ? -1 : 1);
}

/*
 * This function was put into cList.php instead of cTumorBoardWorkflowbuilder.php because
 * the include dependencies in the later file caused issues when referencing it from cList.php
 */
const const_tumorBoardMainGrid		   = 'ListTumorBoard_Datagroups_Grid_DataGroups';
const const_tumorBoardSubGrid1         = 'ListTumorBoard_DatagroupsSubgrid1_Grid_DataGroups';
const const_tumorBoardSubGrid2         = 'ListTumorBoard_DatagroupsSubgrid2_Grid_CaseToDataGroups';

function isTumorBoardFolderSubGrid($ViewName)
{
	if ($ViewName == const_tumorBoardSubGrid1){
		return true;
	}
	else {
		return false;
	}

}
function isTumorBoardCaseSubGrid($ViewName)
{
	if ($ViewName == const_tumorBoardSubGrid2){
		return true;
	}
	else {
		return false;
	}

}
?>
