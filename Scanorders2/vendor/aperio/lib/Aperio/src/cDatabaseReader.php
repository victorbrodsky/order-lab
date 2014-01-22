<?php
include_once '/cExtendedSoapClient.php';

/**
 * Class that uses a GetFilteredRecordList() return SOAP call to fetch records from the database
 * Each cTableSchema creates one of these objects in the Load() method from the CTOR of the concrete tableschema 
 */
class cDatabaseReader
{
	public	$MethodName;
	public	$TableName;
	public	$GetRecordsClient = cSOAPClients::Image; 
    public	$TotalNumRecords = 0;
	private	$Columns = array();
	private	$PageIdx = 0;
    /**
     * Number of records per page, 0 indicates to retrieve all records
     * @var integer 
     */
	private	$RecordsPerPage = 0; 
	private	$Filters = array();	
	private	$FilterXML = array();
	private	$Nodes = array();	
	private	$Records = array();    
	private $SoapParms = array();    
    private	$ReturnType = 'Objects';
	private	$DoCache = false;
	private	$DoCacheById = false;
	private	$DoGetTotalNumRecords = false;
	private	$DoGetImages = false;
    private	$Distinct = false;
	private	$HasCachedRecords = false;
    private $HasColumnAliasSupport = true;
    // By default, GetFilteredRecordList automatically returns image table data when slide table data is requested. If there are multiple
    // image records for a given slide record, DataServer will return multiple rows for the same slide record.
    // Set $ReturnImageDataWithSlide to false to not automatically return image data with slide data 
    private $ReturnImageDataWithSlide = true;
    
    /**
     * Array of sorted fields
     * @var array cSortField 
     */    
    private $SortFields = array();        
    /**
     * These custom hardcoded adjustments change the $SortColumnNames sort order xml sent to dataserver
     * Thus, these always override the inbound sort columns and change the SOAP sent to dataserver
     * 
     * For example, if you are on the case details->specimens->slides list and send in Stain in the $SortColumnNames
     *              then this will translate 'Stain' to UpToStainByStainId.DisplayOrder which will sort the slides by Stain.DisplayOrder
     * @var array 
     */
    private static  $ForeignKeyAdjustments = array(
            'BodySiteId'    => 'UpToBodySiteByBodySiteId.Name',
            'DataGroupId'   => 'UpToDataGroupsByDataGroupId.Name',
            'StainId'       => 'UpToStainByStainId.ShortName',
            'Stain'         => 'UpToStainByStainId.DisplayOrder',
            'CreatedByUserId' => 'UpToUsersByCreatedByUserId.FullName', 
            'CustomerId' => 'UpToCustomersByCustomerId.Name'
        );      
	/**
     * CTOR
     * @param string $MethodName  The DataServer method to call
     * @param string $TableName   The SQL table, also $_SESSION['Tables'][$TableName]
     */
	public function __construct ($MethodName=NULL, $TableName=NULL)
	{		
		if ($MethodName)
        {
			if ($MethodName == 'GetRecordData')
			{
				// It appears that this method is a subset of GetFilteredRecordList
				$MethodName = 'GetFilteredRecordList';
			}
			$this->MethodName = $MethodName;
        }
		else
        {
			$this->MethodName = 'GetFilteredRecordList';
        }
		$this->TableName = $TableName;
	}
    /**
     * You can set the returned records to be of type object or array
     * XXX All clients should be modified to expect objects only since they are more efficient.
     * @param type $Type 
     */
	public function SetReturnType($Type)
	{
		$this->ReturnType = $Type;
	}
	/**
     * Turn caching on/off
     * XXX - I question this is working, most subsequent calls seem to clear this->HasCachedRecords thereby defeating the cache
     * @param bool $OnOff 
     */
    public function SetCache($OnOff)
	{
		$this->DoCache = $OnOff;
	}
    /**
     * Cache records indexed by their ID.
     * This is useful for GetRecordById()
     * @param type $OnOff 
     */
	public function SetCacheById($OnOff)
	{
		$this->DoCacheById = $OnOff;
	}

	public function SetGetTotalNumRecords($OnOff)
	{
		$this->DoGetTotalNumRecords = $OnOff;
	}

	public function GetImages($TrueFalse)
	{
		$this->DoGetImages = $TrueFalse;
	}
	
	public function SetReturnImageDataWithSlide($TrueFalse)
	{
		$this->ReturnImageDataWithSlide = $TrueFalse;
	}
	public function GetReturnImageDataWithSlide()
	{
		return $this->ReturnImageDataWithSlide;
	}
	/**
     * Adds a column that will be part of the SELECT list.
     * @param type $ColumnName 
     */
    public function AddColumn($ColumnName)
	{
		if (false == in_array($ColumnName, $this->Columns))         
		{
            //If the column is part of the custom FK columns then only allow one of them
            //into the select list
            //  For example:   If   AddColumn('BodySiteId')  and AddColumn('UpToBodySiteByBodySiteId.Name') were both called
            //                 only add one to the select list
            if (array_key_exists($ColumnName, self::$ForeignKeyAdjustments))                    
            {
                if (false == in_array(self::$ForeignKeyAdjustments[$ColumnName], $this->Columns))
                {
                    $this->Columns[] = $ColumnName;
                    $this->HasCachedRecords = false;                    
                }                
            }             
            else
            {
                $this->Columns[] = $ColumnName;
                $this->HasCachedRecords = false;
            }
		}
	}
	/**
     * Setup filters using the cSearcher
     * @param cSearcher $Searcher 
     */
    public function SetFilters(cSearcher $Searcher)
	{
		$this->ClearFilters();
		for ($Filter = $Searcher->GetFirstFilter(); $Filter != NULL; $Filter = $Searcher->GetNextFilter())
		{
			$this->_addFilter($Filter);
		}
	}

	public function SetFilter($TableName, $ColumnName, $Operator, $Value)
	{
		$this->ClearFilters();
		$this->AddFilter($TableName, $ColumnName, $Operator, $Value);
	}

	public function AddFilter($TableName, $ColumnName, $Operator, $Value)
	{
		$this->Filters[] = new cFilter($TableName, $ColumnName, $Operator, $Value);

		$Filter = new cSearchFilter($TableName, $ColumnName, $Operator, $Value);
		$this->_addFilter($Filter);
	}

	private function _addFilter(cSearchFilter $Filter)
	{
		$Filter->ToXML($this->FilterXML);

		if ($Filter->TableName)
		{
			// Use distinct if filter depends on another table
			if (($this->TableName != $Filter->TableName) && !($this->TableName == 'Slide' && $Filter->TableName == 'Image'))
            {
				$this->Distinct = true;
            }
		}

		$this->HasCachedRecords = false;
	}

	public function ClearFilters()
	{
		$this->Filters = array();
		$this->FilterXML = array();
		$this->HasCachedRecords = false;
	}

	public function ClearCache()
	{
		$this->Records = array();
		$this->TotalNumRecords = 0;
		$this->HasCachedRecords = false;
	}
	/**
     * Add a sort to the cDatabaseReader object
     * The parameters are always promoted to an array of sorts so they can be used in multicolumn sorting
     * When the parameters are arrays the counts of $SortField and $SortOrder must match     
     * @param array/string $SortColumnNames The sortable column names
     * @param array/string $SortOrders The sortable column orders, Ascending or Descending
     */
    public function SetSort($SortColumnNames, $SortOrders)
	{		                
        $this->SortFields = array();
        
        if (is_array($SortColumnNames) && is_array($SortOrders))
        {
            if (count($SortColumnNames) === count($SortOrders))
            { 
                $sortCount = count($SortOrders);
                for ($i = 0; $i < $sortCount; $i++) 
                {
                    $sortFieldName = $SortColumnNames[$i];
                    $sortOrderName = $SortOrders[$i];
                    if (array_key_exists($sortFieldName, self::$ForeignKeyAdjustments)  )
                    {
                        $sortFieldName  =  self::$ForeignKeyAdjustments[$sortFieldName];                         
                    }                     
                    $this->SortFields[] = new cSortField($sortFieldName , $sortOrderName);
                }
            }
        }
        else if (is_string ($SortColumnNames) && is_string ($SortOrders))
        {
            $sortFieldName = $SortColumnNames;  
            $sortOrderName = $SortOrders;  
            if (array_key_exists($SortColumnNames, self::$ForeignKeyAdjustments)  )
            {
                $sortFieldName  =  self::$ForeignKeyAdjustments[$SortColumnNames]; ;                        
            }
            $this->SortFields[] = new cSortField($sortFieldName , $sortOrderName);
		}
		$this->HasCachedRecords = false;
	}

	public function AddNode($Tag, $Value)
	{
		$this->Nodes[$Tag] = $Value;
	}

	public function ClearNodes($Tag, $Value)
	{
		$this->Nodes = array();
	}

	public function SetRecordsPerPage($RecordsPerPage=NULL)
	{
		if ($RecordsPerPage === NULL)
        {
			$this->RecordsPerPage = GetRecordsPerPage();
        }
		else
        {
			$this->RecordsPerPage = $RecordsPerPage;
        }
		$this->HasCachedRecords = false;
	}
	
    /**
     * Return the requested record by primary key
     * @param int $Id
     * @return array
     */
	public function GetRecordById($Id)
	{
		if ($this->HasCachedRecords && $this->DoCacheById)
		{
			if (isset($this->Records[$Id]))
            {
				return $this->Records[$Id];
            }
		}

		$this->ClearFilters();

		$TableSchema = GetTableObj($this->TableName);
		$IdField = $TableSchema->GetIdFieldName();
		$this->AddFilter($this->TableName, $IdField, '=', $Id);

		$Records = $this->GetRecords(0);
		if (($Records != NULL) && (empty($Records) == false))
		{
			// Note: If tableName == Slide, and the Slide has multiple images, then there will be a list of records
			return array_first_element($Records);
		}
		return NULL;
	}    
    /**
     * Get the type of SOAP client
     * @return cSOAPClients enumeration
     */
    private function GetSoapClient()
    {
		switch ($this->GetRecordsClient)
        {
            case cSOAPClients::Security;
                return GetSOAPSecurityClient();                
            case cSOAPClients::Image:
                return GetSOAPImageClient();
            case cSOAPClients::SpectrumHealthcare:
                return GetSOAPShcClient();
            case cSOAPClients::Pal:
                return GetSOAPPalClient();
            default:
                return GetSOAPImageClient();                
        }
    }    
    /**
     * Add the SELECT columns names to the SOAP
     * @param SoapVar $ParamsArray
     * @param type $AliasToColumnMap 
     */
    private function AddSelectColumns(&$ParamsArray, &$AliasToColumnMap)
    {  
		// Selection Fields
		$NumColumns = count($this->Columns);
		if ($NumColumns > 0)
		{
			$aliasIndex = 0;
			$SelectColumnXML = '';
			foreach ($this->Columns as $Column)
		    {
    			// alias all columns whose length is greater than ALIASCOLUMNLENGTH
    			if (strlen($Column) > ALIASCOLUMNLENGTH)
    			{
        			$alias = 'A' . $aliasIndex;
		        	$aliasIndex++;
				}
		        else
                {
        			$alias = $Column;
                }
		        $AliasToColumnMap[$alias] = $Column;   
                
                if ($this->HasColumnAliasSupport)
                {
                    $SelectColumnXML .= "<Column><Name>" . $Column . '</Name><Alias>' . $alias . '</Alias></Column>';
                }
                else
                {
                    $SelectColumnXML .= "[$Column]";
                }
		    }

			// If the sortby field somehow didn't wind up the list of columns stick it in there
            foreach ($this->SortFields as $sf)
            {
                $sortFieldName = $sf->GetSortField();
                if (!empty($sortFieldName))
                {
                    if ((!in_array($sortFieldName, $this->Columns)))
                    {
                        // alias all columns whose length is greater than ALIASCOLUMNLENGTH
                        if (strlen($sortFieldName) > ALIASCOLUMNLENGTH)
                        {
                            $alias = 'A' . $aliasIndex;
                            $aliasIndex++;
                        }
                        else
                        {
                                $alias = $sortFieldName;
                        }
                        if ($this->HasColumnAliasSupport)
                        {                        
                            $SelectColumnXML .= "<Column><Name>" . $sortFieldName . '</Name><Alias>' . $alias . '</Alias></Column>';;
                        }
                        else
                        {
                            $SelectColumnXML .= "[$sortFieldName]";
                        }                                                
                        $NumColumns++;
                    }
                    //if the sort field is a custom adjusted column then place the translated name into the select
                    if (array_key_exists($sortFieldName,  self::$ForeignKeyAdjustments)  )
                    {
                        // alias all columns whose length is greater than ALIASCOLUMNLENGTH
                        if (strlen($sortFieldName) > ALIASCOLUMNLENGTH)
                        {
                            $alias = 'A' . $aliasIndex;
                            $aliasIndex++;
                        }
                        else
                        {
                                $alias = $sortFieldName;
                        }      
                        if ($this->HasColumnAliasSupport)
                        {                        
                            $SelectColumnXML .= "<Column><Name>" . $sortFieldName . '</Name><Alias>' . $alias . '</Alias></Column>';;
                        } 
                        else
                        {
                            $SelectColumnXML .= "[$sortFieldName]";
                        }                        
                        $NumColumns++;
                    }
                }
            }

			// If there's only one column, we can always (and should always) use DISTINCT
			$Distinct = $this->Distinct | ($NumColumns == 1);

			$SelectColumnsXML = "<ColumnList" . ($Distinct ? " Distinct='true'" : "") . ">$SelectColumnXML</ColumnList>";
			$ParamsArray[] = new SoapVar($SelectColumnsXML, 147);
		}
    }    
    /**
     * Add the SortBy and SortOrder Element to the SOAP call
     * @param array $ParamsArray 
     */
    private function AddSortColumns(&$ParamsArray)
    {		
		if ($this->SortFields)
		{            
            $sortCount = count($this->SortFields);
            for ($i = 0; $i < $sortCount; $i++) 
            {
                $sortField = $this->SortFields[$i];
                $sortFieldName = $sortField->GetSortField();                
                $sortOrderName = $sortField->GetSortOrder();
                
                if (!empty($sortFieldName) && !empty($sortOrderName))
                {
                    $SortByXML =  "<Sort By=\"$sortFieldName\" Order=\"$sortOrderName\"/>";
                    $ParamsArray[] = new SoapVar($SortByXML, 147);
                }
            }    
        }
    }
    /**
     * Add Pagination to the SOAP call
     * @param SoapParam $ParamsArray
     * @param type $Page The db page number
     */
    private function AddPagination(&$ParamsArray, $Page)
    {        
		if (($this->DoGetTotalNumRecords == false) && ($Page <= 1))
		{
			// Since client is requesting the first page (or all pages), the DataServer is more efficient by not processing pagination.
			// Note: although this returns totalCount below, the returned value will not be more than MaxCount
			$ParamsArray[] = new SoapParam ($this->RecordsPerPage, 'MaxCount');
		}
		else
		{
			// Pagination
			$ParamsArray[] = new SoapParam ($Page, 'PageIndex');
			if ($this->RecordsPerPage > 0)
            {
				$ParamsArray[] = new SoapParam($this->RecordsPerPage, 'RecordsPerPage');
            }
		}                
    }
    /**
     * Add the WHERE clause to the SOAP
     * @param SoapParam $ParamsArray 
     */
    private function AddWhereClause(&$ParamsArray)
    {		
		foreach ($this->FilterXML as $FilterXML)
		{
			$ParamsArray[] = $FilterXML;
		}                
    }
    /**
     * Add Token_TODEL renewal to SOAP
     * IF $DoNotRenewToken is true then the dataserver will not renew the token
     * @param SoapParam $ParamsArray
     * @param bool $DoNotRenewToken 
     */
    private function AddTokenRenewal(&$ParamsArray, $DoNotRenewToken )
    {
		if ($DoNotRenewToken)
		{
			$ParamsArray[] = new SoapParam('1', 'DoNotRenewToken');
		}        
    }

    /**
     * Add the FROM clause to the SOAP
     * @param SoapParam $ParamsArray 
     */    
    private function AddFromSQLTable(&$ParamsArray)
    {
		if ($this->TableName)
		{
			$ParamsArray[] = new SoapParam($this->TableName, 'TableName');
		}                
    }    
    /**
     * Add Custom Soap Parameters
     * @param SoapParam $ParamsArray 
     */
    private function AddExtraSoapParameters(&$ParamsArray)
    {		
		foreach ($this->Nodes as $Tag => $Value)
		{
			$ParamsArray[] = new SoapParam($Value, $Tag);
		}
    }
    /**
     * Add ReturnImageDataWithSlide Parameter (used for slide table requests only)
     * @param SoapParam $ParamsArray 
     */
    private function AddReturnImageDataWithSlide(&$ParamsArray)
    {
		if ($this->TableName == 'Slide')
        {
			$ParamsArray[] = new SoapParam($this->ReturnImageDataWithSlide ? '1' : '0', 'ReturnImageDataWithSlide');
        }
    }
    /**
     * Add Generic key/value soap parameters.  Used for custom DS Methods
     * @param SoapParam $ParamsArray 
     */
    private function AddGenericSoapParameters(&$ParamsArray)
    {		
        foreach ($this->SoapParms as $key => $value )
        {
			$split = explode('*', $value);
            //the generic soap paramaters are delimited by the star character
            // Only allow single parameters per array element
            if (2 === count($split))
            {
                $ParamsArray[] = new SoapParam($split[1], $split[0]);
            }
        }
	}
	/**
	 * Issue a GetFilteredRecord List SOAP Call
	 * @param int $Page The DB page number
	 * @param bool $DoNotRenewToken If true, do not renew token
	 * @param array $ParamsArray An array containing all the SOAP parameters
	 * @param array $AliasToColumnMap Hash to map a key to a databasecolumn
	 */
	private function SetGetFilteredRecordListSoapParamaters($Page, $DoNotRenewToken, &$ParamsArray, &$AliasToColumnMap=NULL)
	{
		$this->AddFromSQLTable($ParamsArray);
		$this->AddTokenRenewal($ParamsArray, $DoNotRenewToken);
		$this->AddPagination($ParamsArray, $Page);
		$this->AddExtraSoapParameters($ParamsArray);
		$this->AddWhereClause($ParamsArray);
		$this->AddSelectColumns($ParamsArray, $AliasToColumnMap);
		$this->AddSortColumns($ParamsArray);
		$this->AddReturnImageDataWithSlide($ParamsArray);
	}
    /**
     * Issue a GetRecordData  SOAP Call.  
	 **/
	/***
	private function SetGetRecordDataSoapParamaters($Page, $DoNotRenewToken, &$ParamsArray, &$AliasToColumnMap=NULL)
	{
		$this->AddFromSQLTable($ParamsArray);
		$this->AddTokenRenewal($ParamsArray, $DoNotRenewToken);
		$this->AddPagination($ParamsArray, $Page);
		$this->AddExtraSoapParameters($ParamsArray);
		// GetRecordData requires an ID
		$Id = null;
		foreach ($this->Filters as $Filter)
		{
			if ($Filter->ColumnName == 'Id')
			{
				$Id = $Filter->Value;
				$ParamsArray[] = new SoapParam($Id, 'Id');
				break;
			}
		}
	}
	*****/
    /**
     * Issue a GetFilteredRecordList Type Generic SOAP Call.  
	 * Only adds SELECT columns and Generic SOAP elements.
     * @param int $Page The DB page number
     * @param bool $DoNotRenewToken If true, do not renew token
     * @param array $ParamsArray An array containing all the SOAP parameters
     * @param array $AliasToColumnMap Hash to map a key to a databasecolumn
     */
    private function SetFilteredRecordListGenericSoapParamaters($Page, $DoNotRenewToken, &$ParamsArray, &$AliasToColumnMap=NULL)
	{
        $this->AddTokenRenewal($ParamsArray, $DoNotRenewToken);        
        $this->AddSelectColumns($ParamsArray, $AliasToColumnMap);
        $this->AddGenericSoapParameters($ParamsArray);
	}
	/**
     * Return the requested (filtered & sorted) records from the database
     * The DataServer Client and Method have to support a GetFilteredRecordList return object
     * @param int $Page (0 will get all records (no paging))
     * @param bool $DoNotRenewToken
     * @return object The response from the SOAP call as an object
     */
    public function GetRecords($Page=0, $DoNotRenewToken = false)
	{
		// Clear any previous error
		ClearDBErrors();

		if (($this->HasCachedRecords) && ($Page == $this->PageIdx))
        {
			return $this->Records;
        }

		$this->PageIdx = $Page;

        $client = $this->GetSoapClient();
		$ParamsArray = GetAuthVars();
        $AliasToColumnMap = array();

        switch ($this->MethodName)
        {
            case 'GetFilteredRecordList':
                $this->SetGetFilteredRecordListSoapParamaters($Page, $DoNotRenewToken, $ParamsArray, $AliasToColumnMap);
                break;
			/***
            case 'GetRecordData':
                $this->SetGetRecordDataSoapParamaters($Page, $DoNotRenewToken, $ParamsArray, $AliasToColumnMap);
                break;
			*****/
			case 'GetPriorCases':                
                $this->SetFilteredRecordListGenericSoapParamaters($Page, $DoNotRenewToken, $ParamsArray, $AliasToColumnMap);
                break;
            default:  
                //use GetFilteredRecordList 
                $this->SetGetFilteredRecordListSoapParamaters($Page, $DoNotRenewToken, $ParamsArray, $AliasToColumnMap);
                break;
		}
		// Create the SOAP XML and send to dataserver
		$response = $client->__soapCall($this->MethodName, $ParamsArray, array('encoding'=>'UTF-8'));
		return $this->ProcessSOAPResponse($response, $AliasToColumnMap);
	}
	/**
	 * 
	 * @param array $response The response from the DataServer SOAP call
	 * @param array $AliasToColumnMap Hash of Aliased SQL columns their real sql column name
	 * @return object
	 */
	private function ProcessSOAPResponse($response, &$AliasToColumnMap)
	{
		if (is_array($response) == false)
		{
			return ReportDataServerError($response);
		}

		if ($this->DoGetTotalNumRecords)
		{
			$this->TotalNumRecords = $response['TotalRecordCount'];
		}

		if ($this->MethodName == 'GetRecordData')
		{
			$Records = array($response['DataRow']);
		}
		else
		{
			if (is_string($response['GenericDataSet']))
			{
				// Soap's way of saying no records
				return array();
			}
			if (is_array($response['GenericDataSet']->DataRow))
			{
				$Records = &$response['GenericDataSet']->DataRow;
			}
			else
			{
				// Only one record, convert to an array for common processing
				$Records = array($response['GenericDataSet']->DataRow);
			}
		}

		if (empty($AliasToColumnMap) == false)
		{
			$DataRows = array();
			foreach($Records as $DataRow)
			{
				$RowFields = array();
				foreach($DataRow as $key => $value) 
				{
					if (array_key_exists($key, $AliasToColumnMap))
					{
						$RowFields[$AliasToColumnMap[$key]] = $value;
					}
					else
					{
						$RowFields[$key] = $value;
					}
				}					
				$DataRows[] = arraytoobject($RowFields);
			}
			$Records = $DataRows;	
		}

		if ($this->ReturnType == 'Objects')
		{
			if ($this->DoCacheById && isset($Records[0]->Id))
			{
				// Index the records by ID for easy lookup
				foreach ($Records as $Record)
				{
					$this->Records[$Record->Id] = $Record;
				}
			}
			else
			{
				$this->Records = $Records;
			}
		}
		else
		{
			// Convert array or row objects into an array of row arrays indexed by field name
			// The data row comes back as an object, but it's more useful if we turn it into an associative array
			// XXX this is for compatibility (at least with cTable::FormatFilteredRecordList())
			$this->Records = array();
			if ($this->DoCacheById && isset($Records[0]->Id))
			{
				// Index the records by ID for easy lookup
				foreach ($Records as $Record)
					$this->Records[$Record->Id] = ObjectToArray($Record);
			}
			else
			{
				foreach ($Records as $Record)
					$this->Records[] = ObjectToArray($Record);
			}
		}

		if ($this->DoCache || $this->DoCache)
        {
			$this->HasCachedRecords = true;
        }

		if ($this->DoGetImages)
		{
			$CurrId = -1; 		// current record id
			$ImageIndex = 0; 	// $ImageRecords array index

			// loop through each record, and assign a single image record to each
			foreach ($this->Records as &$Record)
			{
				if ($Record['Id'] == $CurrId)
				{
					// this is the same record as the previous iteration through the loop,
					// already have the image records for it
					++$ImageIndex;
				}
				else
				{
					// this is a different record, read the image records for this record
					$CurrId = $Record['Id'];
					$ImageIndex = 0;
					$ImageRecords = ADB_GetRecordImages ($Record['Id'], $this->TableName);
				}
				if (isset($ImageRecords[$ImageIndex]))
                {
					$Record['ImageRecords'] = array($ImageRecords[$ImageIndex]);
                }
			}
		}
		return $this->Records;
    }

    /**
     * Set custom DataServer SOAP client, and method
     * @param string $DataServerClient
     * @param string $DataServerMethod
     * @param array $SoapParmsArray Star delimited set of elements to add to the SOAP call.  
     *                              For example:  $SoapParmsArray = array('CaseId*1234', 'PalModuleName*AllRecords')
     *                                            will generate SOAP Elements   <CaseId>1234</CaseId>   and  <PalModuleName>AllRecords</PalModuleName>
     *                                            and will be sent to dataserver along with the SELECT columns
     */
    public function SetSoapParameters($DataServerClient, $DataServerMethod,  $SoapParmsArray)
    {                
        $this->SoapParms = $SoapParmsArray;        
        $this->MethodName = $DataServerMethod;        
        $this->GetRecordsClient = $DataServerClient;        
    }    
    
	public function Copy(&$src)
	{
		$Members = get_class_vars('cDatabaseReader');
		foreach ($Members as $Key => $Value)
		{
			if (isset($src->$Key))
			{
				DeepCopy2($this->$Key, $src->$Key);
			}
		}
	}    
    /**
     * Get the current cList Sort Fields as an array of strings.  
     * @return array Each entry is a string which is the key in the $this->Columns[]     
     */
    public function GetSortFields()
    {        
        $localSortFieldArray = array();
        if ($this->SortFields)
        {
            foreach ($this->SortFields as $sf)
            {
                $localSortFieldArray[] = $sf->GetSortField();                
            }            
        }
        return $localSortFieldArray;
    }    
    /**
     * Get the current cList Sort Orders as an array of strings.  
     * @return array [Ascending or Descending]     
     */
    public function GetSortOrders()
    {
        $localSortOrderArray = array();
        if ($this->SortFields)
        {
            foreach ($this->SortFields as $sf)
            {
                $localSortOrderArray[] = $sf->GetSortOrder();                
            }            
        }
        return $localSortOrderArray;              
    }   
    /**
     * Get the custom column name adjustments for the foreign fields
     */
    public static function GetForeignKeyAdjustments()
    {
        return self::$ForeignKeyAdjustments;        
    }
}
?>
