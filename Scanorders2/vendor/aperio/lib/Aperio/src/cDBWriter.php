<?
/**
* Perform error checking & write a record(s) to the database
* 
* @author Vince Unger <vunger@aperio.com>
*
*/


include_once '/Skeleton.php';
include_once '/cTable.php';
include_once '/DatabaseRoutines.php';


class cDBWriter
{
	function __construct($TableName, $DatabaseTableName=NULL)
	{
		$this->TableName = $TableName;
		$this->TableSchema = GetTableObj($TableName);
		if ($DatabaseTableName)
		{
			// The local table definition is different than the database's (usually different field constrictions)
			$this->DatabaseTableName= $DatabaseTableName;
		}
		else
		{
			$this->DatabaseTableName= $TableName;
		}
		$this->Method = 'PutRecordData';
	}

	public function Clear()
	{
		$this->ClearErrors();
		$this->NewValues = array();
	}

	public function SetAsNewRecord()
	{
		$this->Ids = array(0 => 0);
	}

	public function SetIds($Ids)
	{
		$this->Ids = $Ids;
	}

	public function GetIds()
	{
		return $this->Ids;
	}

	public function SetMethod($Method)
	{
		$this->Method = $Method;
	}

	// Return any records yet to be modified
	public function GetNewValues()
	{
		return $this->NewValues;
	}

	public function GetNewValue($Name)
	{
		if (isset($this->NewValues[$Name]))
			return $this->NewValues[$Name];
		return null;
	}

	public function GetTableSchema()
	{
		return $this->TableSchema;
	}

	public function LoadRecord($Record)
	{
		$this->NewValues = $Record;
	}

	// Preparatory step used for error checking
	public function Update($FieldUpdates)
	{
		$this->ClearErrors();

		// Special Processing
		if (isset($FieldUpdates['ParentId']))
		{
			if ($this->TableSchema->ParentTableName == 'GenieProject')
				$FieldUpdates['GenieProjectId'] = $FieldUpdates['ParentId'];
			if (isset($FieldUpdates['ParentTable']) == false)
				$FieldUpdates['ParentTable'] = $this->TableSchema->ParentTableName;
		}


		//
		// Field error check
		//

		$NewValues = array();
		foreach ($this->TableSchema->Fields as $Field)
		{
			// Note: FieldUpdates is probably $_REQUEST, which has many non-field parameters
			$FieldName = $Field->ColumnName;
			if (isset($FieldUpdates[$FieldName]) == false)
				continue;
			$this->UpdateField($FieldName, $FieldUpdates[$FieldName]);
		}
		if (empty($this->FieldErrors) == false)
			return;

		/***
		 * Now done in cRecord
		// Append generic extra fields.
		if (isset ($FieldUpdates['SigUserName']))
			$this->NewValues['SigUserName'] = $FieldUpdates['SigUserName'];
		if (isset ($FieldUpdates['SigPassword']))
			$this->NewValues['SigPassword'] = $FieldUpdates['SigPassword'];
		if (isset ($FieldUpdates['RfcValue']))
			$this->NewValues['RfcValue'] = $FieldUpdates['RfcValue'];
		if (isset ($FieldUpdates['RfcComment']))
			$this->NewValues['RfcComment'] = $FieldUpdates['RfcComment'];
		***************/
	}

	public function UpdateField($FieldName, $NewValue)
	{
		if ($NewValue == '[various]')
			return;

		$Field = $this->TableSchema->GetField($FieldName);

		if ($this->TableName != $Field->TableName)
			return;

		// Convert the field to transfer to the database, e.g. Foreign Field enumeration to its id
		//$ErrorMessage = $Field->ConvertValue($NewValue);
		//if ($ErrorMessage)
		$ErrorMessage;
		if ($Field->IsLegalValue($NewValue, $ErrorMessage) == false)
			$this->FieldErrors[$FieldName] = $Field->DisplayName . ': ' . $ErrorMessage;

		if ($Field->IsVirtual == false)
			$this->SetField($FieldName, $NewValue);
	}

	// Add/change database field
	public function SetField($FieldName, $NewValue)
	{
		$this->NewValues[$FieldName] = $NewValue;
	}

	// ret:	Id of last record changed/added or errorcode
	public function SaveRecords($DoCommit=true)
	{
		for ($i = 0; $i < count($this->Ids); $i++)
		{
			$Id = $this->Ids[$i];

			// Only save changed fields
			if (empty($this->NewValues))
				continue;
			$Record = $this->NewValues;

			foreach ($Record as $key => $value)
			{
                if (array_key_exists($key, $this->TableSchema->Fields)) 
                {
                    // convert date to format that dataserver expects - yyyy/mm/ddThh:mm:ss
                    if ($this->TableSchema->Fields[$key]->FieldType == 'Date')
                    {
                    	$Record[$key] =  ConvertDateToStandardFormat($value);
                    }
                    elseif ($this->TableSchema->Fields[$key]->FieldType == 'DateTime')
                    {
                        $Record[$key] =  ConvertDateTimeToStandardFormat($value);
                    }
                }
                
			}
			$Id = ADB_PutRecord($this->Method, $this->DatabaseTableName, $Record, $Id, $DoCommit);
			if ($Id < 0)
			{
				$this->ErrorCode = $Id;	// Negative Id is actually an error code
				$this->ErrorMessage = $_SESSION['DataServerError'];
				return $this->ErrorCode;
			}
			if ($this->Ids[$i] == 0)
			{
				// Keep new Ids
				$this->Ids[$i] = $Id;
			}
		}

		// XXX this should probably just return an error status, client can later retrieve Ids
		return $Id;	// last Id
	}

	public function Commit()
	{
		return $this->SaveRecords(true);
	}

	public function ClearErrors()
	{
		$this->FieldErrors = array();
		$this->ErrorCode = 0;
		$this->ErrorMessage = '';
	}

	public function HasError()
	{
		if ($this->ErrorMessage != '')
			return true;
		if (empty($this->FieldErrors))
			return false;
		return true;
	}

	public function GetErrorCode()
	{
			return $this->ErrorCode;
	}

	public function GetErrorMessage()
	{
			return $this->ErrorMessage;
	}

	public function GetFieldErrors()
	{
		return $this->FieldErrors;
	}

	public function GetFieldError($FieldName)
	{
		if (isset($this->FieldErrors[$FieldName]))
			return $this->FieldErrors[$FieldName];
		return '';
	}

	private	$TableName;
	private	$DatabaseTableName;
	private	$TableSchema;
	private	$Ids;
	private	$NewValues	= array();	// array of fields
	private	$ErrorCode = 0;
	private	$ErrorMessage = '';
	private	$FieldErrors = array();
}

?>
