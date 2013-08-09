<?

/*************************************************************************************
* FILE: cCSVWriter.php
* AUTHOR: Steven Hashagen
* DESCRIPTION:	This class encapsulates the logic for organizing and writing out
*  data in a CSV file.  When the CSV file is opened in a spreadsheet app like Excel,
*	all of the columns should align properly, regardless of what order they get set
*  in this class.  For each record/row, name-value pairs can be set in
*	any order and they will be still be output in an organized fashion.  Rows that
*	are missing a particular name/value will get a placeholder for that pair.
*
* 080724 	msmaga	updated CSV to import UTF directly into IE; added transposedCSV option
* 080805	vunger	Added file write ability.  Reverted to comma delimiter.
* 080812 	msmaga	text translation for hierarchy key values
* 080922	vunger	Added NextRow() to avoid PHP memory issues.
* 					Added SetColumnName(), WriteHeader() for file output header.
* 081209	vunger	Added GetCsvOption(), SetCsvOption()
**************************************************************************************/

include_once '/DownloadFile.php';

class cCsvWriter
{
	public $Rows = array();
	public $ColumnNames = array();
	public $ColumnDisplayNames = array();
	public $FileName = '';
	public $FileHandle = NULL;
	public $Delimiter;				// Field Delimiter
	public $EscapeChar = '"';		// Escape Character (currently applies to double-quotes within a field)
	private $TempFileCreated = false;
	private	$Transpose = false;
	public	$DisplayEmptyFields = false;
	private	$CurrentRow;
	
	// these hold translations of foreign key data:
	protected $TranslateTable = array ();

	public function __construct($fName = '')
	{
		if ($fName != '')
		{
			$this->FileName = $fName;
			$this->FileHandle = fopen($fName, 'w');
			if ($this->FileHandle == FALSE)
				throw new Exception("Cannot open file '$fName'");
		}

		$Delimiter = GetCsvOption('Delimiter');
		if ($Delimiter == 'Tab')
			$this->Delimiter = "\t";  // required for excel to translate UTF-8 (note: double quotes needed for escaping)
		else
			$this->Delimiter = ',';

		// build Translate tables
		$this->BuildTranslateTable ('DataGroupId', 'DataGroups', 'Name');
		$this->BuildTranslateTable ('BodySiteId', 'BodySite', 'Name');
		$this->BuildTranslateTable ('StainId', 'Stain', 'ShortName');
		$this->BuildTranslateTable ('GenieProjectId', 'GenieProject', 'Name');
		$this->BuildTranslateTable ('ImageTypeId', 'ImageType', 'Name');
		$this->BuildTranslateTable ('CustomerId', 'Customers', 'Name');
		$this->BuildTranslateTable ('SsInterpretationId', 'SsInterpretation', 'InterpretationText');
/*		
		// escape double-quotes in Translate Tables DisplayNames
		$EscapeChar = $this->EscapeChar;
		foreach ($this->TranslateTable as $Table)
		{
			foreach ($Table as $Id=>$Name)
			{
				$EscapedName = '';
				for ($i=0; $i < strlen($Name); $i++)
				{
					if ($Name[$i] == '"')
						$EscapedName .= $EscapeChar;
					$EscapedName .= $Name[$i];
				}
			}
		}
*/
		// Create an initial row
		$this->CurrentRow = 0;;
		$this->Rows[0] = array();

		$maxMem = ini_get('memory_limit');
		if ($maxMem == '16M')
		{
			// Ensure at least 32M
			SetMemory('32M');
		}
		// else assume caller has increased the limit.
	}

	public function IsTempFileCreated()
	{
		return $this->TempFileCreated;
	}
	// build the translation arrays:  $this->TableArray[$IdName][$Id] = $Translation
	private function BuildTranslateTable ($IdName, $TableName, $DisplayName)
	{
		$List = ADB_GetRecordList($TableName, $DisplayName, array('Id', $DisplayName));
			foreach ($List as $Item)
				$this->TranslateTable[$IdName][$Item['Id']] = $Item[$DisplayName];
	}

	public function __destruct()
	{
		if ($this->FileHandle)
			fclose($this->FileHandle);
		if ($this->TempFileCreated)
			unlink($this->FileName);
	}

	function CreateTempFile()
	{
		if ($this->Transpose)
			trigger_error('CSVWriter Error: Cannot transpose a CSV in file mode', E_USER_ERROR);
		$this->FileName = tempnam('', 'csv');
		$this->FileHandle = fopen($this->FileName, 'w');
		if ($this->FileHandle == FALSE)
			throw new Exception("Cannot open file '$this->FileName'");
		$this->TempFileCreated = true;
	}

	function TransposeCSV($flag = true)
	{
		if (($this->FileHandle) && ($flag == true))
			trigger_error('CSVWriter Error: Cannot transpose a CSV in file mode', E_USER_ERROR);

		$this->Transpose = $flag;
	}

	function NextRow()
	{
		if (empty($this->Rows[$this->CurrentRow]))
			return;

		if ($this->FileHandle)
		{
			$Row = '';
			$this->GetRow($Row, $this->Rows[0]);
			fwrite($this->FileHandle, $Row, strlen($Row));
			// Clear the row
			$this->Rows[0] = array();
		}
		else
		{
			// Memory csv, simply add another row
			$this->CurrentRow++;
			$this->Rows[$this->CurrentRow] = array();
		}
	}

	function RowCount()
	{
		return count($this->Rows);
	}
    function GetSize()
    {
        if ($this->FileHandle)
        {
            $Headers = '';
            $this->GetHeader($Headers);
            return strlen($Headers) + FileSize($this->FileName);
        }
        else
        {
            return strlen($this->Transpose ? $this->TransposedCSV() : $this->GetCSV());
        }
    }
	// Set the cell of the current row
	function SetCell($Name, $Value, $DisplayName='')
	{
		$this->SetValue($this->CurrentRow, $Name, $Value, $DisplayName);
	}

	// Set the cell of the named row
	function SetValue($RowIndex, $ColName, $Value, $DisplayName='' )
	{
		if (($RowIndex < 0) || ($RowIndex >= count($this->Rows)))
			trigger_error('CSVWriter Error: RowIndex out of bounds', E_USER_ERROR);

		if (($Value == '') && ($this->DisplayEmptyFields == false))
			return;

			$EscapeChar = $this->EscapeChar;

			if (!in_array($ColName, $this->ColumnNames))
			{
				// Name doesn't exist in the ColumnNames array, add it.
				$EscapedDisplayName = '';
				for ($i=0; $i < strlen($DisplayName); $i++)
				{
					// escape double-quotes in Translate Tables DisplayNames
					if ($DisplayName[$i] == '"')
						$EscapedDisplayName .= $EscapeChar;
					$EscapedDisplayName .= $DisplayName[$i];
				}

				$this->SetColumnName($ColName, $EscapedDisplayName);
			}

			// escape double-quotes in Value":
			$EscapedValue = '';
			for ($i=0; $i < strlen($Value); $i++)
			{
				if ($Value[$i] == '"')
					$EscapedValue .= $EscapeChar;
				$EscapedValue .= $Value[$i];
			}						

			// now add the name/value pair to the specified row, checking for key translations		
			$this->Rows[$RowIndex][$ColName] = $this->Translate($ColName, $EscapedValue);		
	}
	
	private function TransposedCSV()
	{
		$Delimiter = $this->Delimiter;
		$NumRows = $this->_getNumberOfValidRows();
		$NumCols = count($this->ColumnNames);

		// first column is the field names
		$CSV = "\"Field Name\"" . $Delimiter;
		// Fill remainder of header with row numbers
		for ($row = 0; $row < $NumRows; $row++)
			$CSV .= "\"$row\"" . $Delimiter;
		$CSV .= "\r\n";

		// cycle through the rows
		for ($col = 0; $col < $NumCols; $col++)
		{
			$ColumnName = $this->ColumnNames[$col];

        	// first column of each row is the display name
			$DisplayName = $this->ColumnDisplayNames[$ColumnName];                          
			$CSV .= "\"$DisplayName\"" . $Delimiter; 

			for ($row = 0; $row <= $NumRows; $row++)     
			{
				$Value = isset ($this->Rows[$row][$ColumnName]) ? $this->Rows[$row][$ColumnName] : '';
				$CSV .= "\"$Value\"" . $Delimiter;
			}
			$CSV .= "\r\n";
		}                                                    

        return $CSV;	
	}
	
    function GetCSV()
    {
        $CSV = '';

		$this->GetHeader($CSV);

        // add a new line for every row
		$NumRows = $this->_getNumberOfValidRows();
		for ($row = 0; $row < $NumRows; $row++)
        {
			$this->GetRow($CSV, $this->Rows[$row]);
        }

        return $CSV;	
    }

	function GetHeader(&$CSV)
	{
		$Delimiter = $this->Delimiter;

        // first line is the column names
        foreach($this->ColumnNames as $ColumnName)
        {
			$DisplayName = $this->ColumnDisplayNames[$ColumnName];                  
            $CSV .= "\"$DisplayName\"" . $Delimiter;
        }
        $CSV .= "\r\n";
	}

	private function GetRow(&$CSV, $Row)
	{
		$Delimiter = $this->Delimiter;

		// look for each column in this row
		foreach($this->ColumnNames as $ColumnName)
		{
			$Value = isset ($Row[$ColumnName]) ? $Row[$ColumnName] : '';
			$CSV .= "\"$Value\"" . $Delimiter;
		}

		$CSV .= "\r\n";
	}
	
	public function Translate($Name='', $Value)
	{
		if ($Value == '')	// no data = nothing to do
			return $Value;
			
		// return 'friendly' data if $ColumnName is actually a key to data in another table
		$Found= preg_match("/.*: (.*$)/", $Name, $Names);   		// extract TableName
		if (isset($Names[1]))
			$Found= preg_match("/(.*) \(Slide.*/", $Names[1], $Prefix); // remove suffix:  TMA appends "(Slide # - Stain)" to ColumnNames...
		$ColumnName = isset($Prefix[1])? $Prefix[1] :
					(isset($Names[1]) ? $Names[1] : '');
					
		if (isset ($this->TranslateTable[$ColumnName][$Value]))
			return $this->TranslateTable[$ColumnName][$Value];
		else
			return $Value;
	}


	function HttpOutput($DownloadFname = 'SpectrumData.csv')
	{
        // Write csvObject to csv reader (excel, for example) via http 
		if ($this->FileHandle)
		{
			if ($this->TempFileCreated)
			{
				// The headers are maintained in memory
				$Headers = '';
				$this->GetHeader($Headers);
				$Size = strlen($Headers) + FileSize($this->FileName);
				$DownLoader = new cDownLoader($DownloadFname, $Size, 'csv');
				$DownLoader->WriteData($Headers);
				$DownLoader->WriteFile($this->FileName);
			}
			else
			{
				// Client should have already done a WriteHeader()
				$Size = FileSize($this->FileName);
			    $DownLoader = new cDownLoader($DownloadFname, $Size, 'csv');
				$DownLoader->WriteFile($this->FileName);
			}

			return;
		}

		if ($this->Transpose)
			$csvData = $this->TransposedCSV();
		else
    		$csvData = $this->GetCSV();   
			
        $stringLocation = 0;
        $bytesSent = 0;
        $csvDataLength = mb_strlen($csvData);                                                  
        $packetSize = 8192;                         
                                                                    
    	header('Expires: 0'); 
        header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');                      
        header('Pragma: private');
		header('Cache-Control: private');
        header('Content-Type: application/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=$DownloadFname"); 
        header('Content-Transfer-Encoding: none');
        header('Content-Length: '.$csvDataLength);

        // send data in packets if length > $packetSize
        for ($i=0; $i<$csvDataLength; $i+=$packetSize)
     	{
     		$packet = substr($csvData, $i, $packetSize);
            $thisPacketSize = strlen($packet);
			$bytesSent = $stringLocation + $thisPacketSize -1;  // string end pointer: allow for string starting at 0
			if ($csvDataLength > $packetSize)
				header("Content-Range: bytes $stringLocation.-.$bytesSent./.$csvDataLength");
            $stringLocation = $bytesSent +1;                    // string start pointer: start with next byte
            echo $packet;
        }
    }
	// Set the CSV's column names
	function SetColumnName($Name, $DisplayName=NULL)
	{
		$this->ColumnNames[] = $Name;
		// add display name to ColumnDisplayNames array.  Use $Name if none given
		$this->ColumnDisplayNames[$Name] = ($DisplayName) ? $DisplayName : $Name;
	}

	// Set the CSV's display column names
	function SetColumnDisplayName($Name, $DisplayName)
	{
		$this->ColumnDisplayNames[$Name] = $DisplayName;
	}
	function WriteHeader()
	{
		if ($this->FileHandle)
		{
			$Row = '';
			$this->GetHeader($Row);
			fwrite($this->FileHandle, $Row, strlen($Row));
		}
	}                                  

	private function _getNumberOfValidRows()
	{
		for ($row = count($this->Rows) - 1; $row >= 0; $row--)
		{
			if (empty($this->Rows[$row]) == false)
				break;
		}
		return ($row + 1);
	}

}  // cCSVWriter


function GetCsvOption($OptionName)
{
	if (isset($_SESSION['CsvOptions']) == false)
		$_SESSION['CsvOptions'] = ADB_GetUserConfig('CsvOptions');

	if (isset($_SESSION['CsvOptions'][$OptionName]))
		return $_SESSION['CsvOptions'][$OptionName];

	// Defaults

	if ($OptionName == 'Delimiter')
		return 'Comma';

	return NULL;
}

function SetCsvOption($OptionName, $Value)
{
	$CurrentValue = GetCsvOption($OptionName);
	if ($Value == $CurrentValue)
	{
		// No change in value
		return $Value;
	}

	$_SESSION['CsvOptions'][$OptionName] = $Value;
	ADB_SetUserConfig('CsvOptions', $_SESSION['CsvOptions']);
	if (isset($_SESSION['CsvOptions']['Id']) == false)
	{
		// This was a new record, we need the ID for overwrites
		$_SESSION['CsvOptions'] = ADB_GetUserConfig('CsvOptions');
	}

	return $Value;
}
