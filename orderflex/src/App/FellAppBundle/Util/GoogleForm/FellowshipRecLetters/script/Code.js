//Synchronised by CLASP order-lab\orderflex\src\App\FellAppBundle\Util\GoogleForm\FellowshipApplication\script
//ScriptID=1JwVzCKlR3be-Y7lHw6BKD2X2XWspB6bOSmCKP3geCsiP31JoHea-tFkq
//0) clasp login
//1) Clone an existing project: clasp clone scriptID
//2) modify script locally
//3) save changes on Google: clasp push
//4) clasp version [description] => [version]
//5) clasp deploy [version] [description]

// Set script permission
//6) Go to https://script.google.com/home/projects/ScriptID/edit
//7) Choose Code.gs and click Run
//8) Review Permission => Allow

//Test url:
//https://script.google.com/a/macros/pathologysystems.org/s/AKfycbznfC7-ZJTs713_JvYjRsxpkJbXbYxFEPZqBpjuzqI/dev?Reference-Letter-ID=a64281915ee59c867f2a9780184604484c18a184&Identification=wcmpathdev&Applicant-First-Name=Test&Applicant-Last-Name=Test&Applicant-E-Mail=cinava@yahoo.com&Fellowship-Type=Clinical%20Informatics&Fellowship-Start-Date=07/01/2025&Fellowship-End-Date=06/30/2026&Reference-First-Name=rec1&Reference-Last-Name=rec1&Reference-Degree=&Reference-Title=&Reference-Institution=&Reference-Phone=&Reference-EMail=cinava@yahoo.com
//Head url:
//https://script.google.com/macros/s/AKfycbznfC7-ZJTs713_JvYjRsxpkJbXbYxFEPZqBpjuzqI/exec?Reference-Letter-ID=a64281915ee59c867f2a9780184604484c18a184&Identification=wcmpathdev&Applicant-First-Name=Test&Applicant-Last-Name=Test&Applicant-E-Mail=cinava@yahoo.com&Fellowship-Type=Clinical%20Informatics&Fellowship-Start-Date=07/01/2025&Fellowship-End-Date=06/30/2026&Reference-First-Name=rec1&Reference-Last-Name=rec1&Reference-Degree=&Reference-Title=&Reference-Institution=&Reference-Phone=&Reference-EMail=cinava@yahoo.com

var _colIndexNameMapArray = {};
var _uniqueId = null;

var _formCreationTimeStamp = CacheService.getPrivateCache().get('_formCreationTimeStamp');

//var _destinationFolderSSKey = "1jK4XJf_Jqn_UvjTvbgiu4jV9Kvp5G3nY"; //folder where the response spreadsheets (forms) are saved;
var _recSpreadsheetFolderId = null; //folder ID where the response spreadsheets (forms) will be saved
var _recUploadsFolderId = null; //folder ID where the recommendation letter will be uploaded

//var _templateSSKey = '1SwKJ04BFSGByTkROYuNAdKo-dEs2rhVFKf36g3DhKhE';
//var _backupSSKey = '1nmBdCIatjBOXffoMsD-lSh6exSwczdMyJgtNmQBOhBs';
var _recTemplateFileId = null;
var _recBackupTemplateFileId = null;
//var _dropbox = "RecommendationLetterUploads"; //folder name where the recommendation letter will be uploaded. Must be unique on the Google Drive!
//var _configFolderId = "0B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M";

var _adminemail = null; //'oli2002@med.cornell.edu';
var _useremail = null; //'WCMPathPrgm@med.cornell.edu';
var _exceptionAccount = null; //"olegivanov@pathologysystems.org";

var _AcceptingSubmissions = true;
var _fullValidation = true;

//Maintenance flag (uncomment for maintenance)
//var _AcceptingSubmissions = false; 
//var _fullValidation = false; //will validate only fellapp type, names, email, signature
//var _useremail = 'cinava@yahoo.com';

function doGet(request) {   

  _AcceptingSubmissions = getConfigParameters("letterAcceptingSubmission");

  // _adminemail = getConfigParameters("adminEmail");
  // _useremail = getConfigParameters("fellappAdminEmail");
  // _exceptionAccount = getConfigParameters("letterExceptionAccount");
  //
  // _recSpreadsheetFolderId = getConfigParameters("recSpreadsheetFolderId");
  // _recUploadsFolderId = getConfigParameters("recUploadsFolderId");
  //
  // _recTemplateFileId = getConfigParameters("recTemplateFileId");
  // _recBackupTemplateFileId = getConfigParameters("recBackupTemplateFileId");
  //Logger.log('doGet: _recSpreadsheetFolderId='+_recSpreadsheetFolderId);

  initConfig();

  if( checkConfig() == false ) {
    _AcceptingSubmissions = false;
  }

  //PropertiesService.getScriptProperties().setProperty('_jstest', 'jstest!!!');

  //PropertiesService.getScriptProperties().setProperty('_formCreationTimeStamp', getCurrentTimestamp());
  CacheService.getPrivateCache().put('_formCreationTimeStamp', getCurrentTimestamp(),10800); //expirationInSeconds 10800 sec => 3 hours
    
  var curUser = Session.getActiveUser().getEmail();
  //Logger.log('curUser='+curUser);
    
  if( !_AcceptingSubmissions ) {
    if( curUser == _exceptionAccount ) {
        _AcceptingSubmissions = true;
    }  
  } 
      
  if( _AcceptingSubmissions ) {    
     var template = HtmlService.createTemplateFromFile('Form.html');
     //var template = HtmlService.createTemplate('<b>The time is &lt;?= new Date() ?&gt;</b>');
  } else {
     var template = HtmlService.createTemplateFromFile('Maintenance.html');      
  }    
  
  //get request's parameters
  if(typeof request !== 'undefined') {
    //var urlParameters = ContentService.createTextOutput(JSON.stringify(request.parameter));
    //Logger.log('request.parameter:');
    //Logger.log(request.parameter);
    //Logger.log('urlParameters='+urlParameters);
    //Logger.log(urlParameters);
    
    _ReferenceLeterId = request.parameter['Reference-Letter-ID'];
    //_ReferenceLeterId = request.parameter['id'];
    //Logger.log('_ReferenceLeterId='+_ReferenceLeterId);
    
    if( typeof _ReferenceLeterId === 'undefined' ) {
      template = HtmlService.createTemplateFromFile('Error.html'); 
    }       
     
    template.dataFromServerTemplate = { 
      //Reference fields (13)
      ReferenceFirstName: request.parameter['Reference-First-Name'],
      ReferenceLastName: request.parameter['Reference-Last-Name'], 
      ReferenceDegree: request.parameter['Reference-Degree'],     //Reference-Degree
      ReferenceTitle: request.parameter['Reference-Title'],      //Reference-Title
      ReferenceInstitution: request.parameter['Reference-Institution'],  //Reference-Institution
      ReferencePhone: request.parameter['Reference-Phone'],  //Reference-Phone 
      ReferenceEMail: request.parameter['Reference-EMail'],  //Reference-EMail
      ReferenceStreet1: request.parameter['Reference-Street1'],  //Reference-Street1
      ReferenceStreet2: request.parameter['Reference-Street2'],  //Reference-Street2
      ReferenceCity: request.parameter['Reference-City'],  //Reference-City
      ReferenceState: request.parameter['Reference-State'],  //Reference-State
      ReferenceZip: request.parameter['Reference-Zip'],  //Reference-Zip
      ReferenceCountry: request.parameter['Reference-Country'],  //Reference-Country
      //Applicant fields (7)
      ReferenceLeterId: _ReferenceLeterId, 
      InstituteIdentification: request.parameter['Identification'],
      ApplicantFirstName: request.parameter['Applicant-First-Name'], 
      ApplicantLastName: request.parameter['Applicant-Last-Name'], 
      ApplicantEMail: request.parameter['Applicant-E-Mail'], 
      FellowshipType: request.parameter['Fellowship-Type'], 
      FellowshipStartDate: request.parameter['Fellowship-Start-Date'],  
      FellowshipEndDate: request.parameter['Fellowship-End-Date'],
      letterError: getConfigParameters("letterError")
    };
  } else {
    Logger.log("request is NULL");
    template.dataFromServerTemplate = {
      letterError: getConfigParameters("letterError")
    };
  } //if typeof request !== 'undefined'
  
  //template.action = ScriptApp.getService().getUrl();  
  //Logger.log('url='+ScriptApp.getService().getUrl());
  
  //return template.evaluate().setSandboxMode(HtmlService.SandboxMode.IFRAME);
  //return template.evaluate().setSandboxMode(HtmlService.SandboxMode.IFRAME).setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
  return template.evaluate().setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
}

function initConfig() {
  //_AcceptingSubmissions = getConfigParameters("letterAcceptingSubmission");
  if( _adminemail == null ) {
    _adminemail = getConfigParameters("adminEmail");
  }
  if( _useremail == null ) {
    _useremail = getConfigParameters("fellappAdminEmail");
  }
  if( _exceptionAccount == null ) {
    _exceptionAccount = getConfigParameters("letterExceptionAccount");
  }
  if( _recSpreadsheetFolderId == null ) {
    _recSpreadsheetFolderId = getConfigParameters("recSpreadsheetFolderId");
  }
  if( _recUploadsFolderId == null ) {
    _recUploadsFolderId = getConfigParameters("recUploadsFolderId");
  }
  if( _recTemplateFileId == null ) {
    _recTemplateFileId = getConfigParameters("recTemplateFileId");
  }
  if( _recBackupTemplateFileId == null ) {
    _recBackupTemplateFileId = getConfigParameters("recBackupTemplateFileId");
  }
  //Logger.log('initConfig: _recSpreadsheetFolderId='+_recSpreadsheetFolderId);
}

function checkConfig() {
  if( _adminemail == null ) {
    return false;
  }
  if( _useremail == null ) {
    return false;
  }
  if( _exceptionAccount == null ) {
    return false;
  }
  if( _recSpreadsheetFolderId == null ) {
    return false;
  }
  if( _recUploadsFolderId == null ) {
    return false;
  }
  if( _recTemplateFileId == null ) {
    return false;
  }
  if( _recBackupTemplateFileId == null ) {
    return false;
  }
  return true;
}

function uploadFilesLetter(form) {  
  //Logger.log('uploadFilesLetter...');
  
  //init global variables here from JSON config
  //doGet does not set the script global variables
  initConfig();
  
  //2 submit letter
  var blob = form.recommendationLetter;
  blob = setNewBlobName(form,blob,"RecommendationLetter");
  var fileUrl = uploadFile(form,blob);
  form.uploadedLetterUrl = fileUrl;
  
  //1) submit spreadsheet form
  processForm(form);
  
  return fileUrl  
}
function uploadFile(form,blob) {
    
  //Logger.log('blob='+blob);
  //validateFormBeforeUpload(form);  
    
  try {
          
    //var folder, folders = DriveApp.getFoldersByName(_dropbox);
    var recUploadsFolder = DriveApp.getFolderById(_recUploadsFolderId);
    if( !recUploadsFolder ) {
      MailApp.sendEmail(
          _useremail+","+_adminemail,
          "Google Drive failed to find the Recommendation Letter Upload folder by id="+_recUploadsFolderId,
          "Google Drive failed to find the Recommendation Letter Upload folder by id="+_recUploadsFolderId
      );
    }

    // if (folders.hasNext()) {
    //   folder = folders.next();
    // } else {
    //   folder = DriveApp.createFolder(_dropbox);
    // }

    //var lastname = document.getElementById('textbox_id').value
    //console.log('lastname='+lastname);
            
    //var oldBlobName = blob.getName();
    //Logger.log('oldBlobName='+oldBlobName);   
    //Logger.log('upload _formCreationTimeStamp='+_formCreationTimeStamp);
    //var uniqueId = createUniqueId(form);
    //Logger.log('uniqueId='+uniqueId);    
    //blob.setName(uniqueId+"_"+oldBlobName);
            
    //var blob = form.name;    
    var file = recUploadsFolder.createFile(blob);
                      
    file.setDescription("Uploaded by " + form.firstName + " " + form.lastName);
        
               
    return file.getUrl();
    
  } catch (error) {
    Logger.log('error='+error.toString());   
    return error.toString();
  }
  
}

//RecLetterHash_TimeStamp_123456789.doc
//Rename filename on the internal server to: institution-scryptHash-timestamp-originalName.ext 
function setNewBlobName(formObject,blob,fileType) {
    var oldBlobName = blob.getName();
    
    //limit oldBlobName by 6 chars
    var ext = oldBlobName.split('.').pop();
    var oldBlobNameFilename = oldBlobName.replace(ext,"");
    oldBlobNameFilename = oldBlobNameFilename.substring(0,6);
    oldBlobName = oldBlobNameFilename + "." + ext;    
    oldBlobName = oldBlobName.replace("_", "-");
    //Logger.log('oldBlobName='+oldBlobName);
    
    var uniqueId = createUniqueId(formObject);
    var newBlobName = uniqueId+"_"+oldBlobName;
    //Logger.log('newBlobName='+newBlobName);
    
    blob.setName(newBlobName);
    return blob;
}

//RecLetterHash_TimeStamp
//instituteIdentification-RecLetterHash_TimeStamp
function createUniqueId(formObject) {

  //Logger.log('start createUniqueId');

  if( _uniqueId ) {
     return _uniqueId;
  }

  //Logger.log(formObject);
  //validateFormBeforeUpload(formObject);
  var recommendationLetterID = Trim(formObject.recommendationLetterID);
  
  var instituteIdentification = Trim(formObject.instituteIdentification);
  
  if( !_formCreationTimeStamp || _formCreationTimeStamp == null || _formCreationTimeStamp == "" ) {
     //Logger.log('_formCreationTimeStamp is invalid, _formCreationTimeStamp='+_formCreationTimeStamp);
     _formCreationTimeStamp = getCurrentTimestamp();
     CacheService.getPrivateCache().put('_formCreationTimeStamp', _formCreationTimeStamp,21600); //expirationInSeconds 21600 sec=>6 hours
  }
  var timestamp = _formCreationTimeStamp;  
  timestamp = timestamp.replace(" ", "-");
  timestamp = timestamp.replace(":", "-");
  
  var uniqueId = instituteIdentification+"_"+recommendationLetterID+"_"+timestamp;
  if( uniqueId == null || uniqueId == "" ) {
     Logger.log('uniqueId is invalid, uniqueId='+uniqueId);
  }
  uniqueId = uniqueId.replace(" ", "-");
  uniqueId = uniqueId.replace(":", "-");
  uniqueId = uniqueId.replace("@", "-");   //@ cause the query sq problem by Google Sheet API
  uniqueId = uniqueId.replace(".", "-");
  
  _uniqueId = uniqueId;
  
  //Logger.log(uniqueId);
  return uniqueId;
}


function getCurrentTimestamp() {
  var timezone = "GMT-4";
  var timestamp_format = "yyyy-MM-dd HH:mm:ss";
  var date = Utilities.formatDate(new Date(), timezone, timestamp_format);
  return date;
}

function include(filename) {
  return HtmlService.createHtmlOutputFromFile(filename).getContent();
}

function Trim(string) {
  if( !string || string == null || string == "" ) {
    Logger.log("string to trim is invalid, string="+string);
    return "";
  }
  return string.replace(/\s/g, ""); 
}

//constract mapping array: arr['fieldName'] = column;
function getColIndexNameMapArray(sheet) {
  var row = 1; //header row
  var maxColumn = sheet.getLastColumn();  
  for( var col = 1; col <= maxColumn; col++ ) { 
     var range = sheet.getRange(row, col);
     var values = range.getValues();
     var value = values[0][0];    
    _colIndexNameMapArray[value] = col;     
  }    
  return _colIndexNameMapArray;
}

function getColIndexByName(name) {   
  var col = _colIndexNameMapArray[name]; 
  if( col == undefined ) {   
    col = -1;
  }  
  return col;
}

//use first row in spreadsheet to hold names of the form (must be exact as in the form's field name)
//use second row in spreadsheet to hold field labels (we need them to print report)
function processForm(formObject) {
  //throw new Error("start processForm");
  //Logger.log("start processForm");
  validateFormFields(formObject);
  //Logger.log("processForm: after validateFormFields");
  
  //set Unique ID based on email_lastname_firstname_timestamp
  //var uniqueId = email+lastName+"_"+firstName+"_"+"_"+timestamp;
  var uniqueId = createUniqueId(formObject);
  //Logger.log("processForm: after createUniqueId, uniqueId="+uniqueId);
    
  var sheet = getSheetFromSingleTruthSource(uniqueId);
  //Logger.log("processForm: after getSheetFromSingleTruthSource");
  
  //create mapping array with header=index
  _colIndexNameMapArray = getColIndexNameMapArray(sheet);
  
  var lastRow = sheet.getLastRow();
  var maxColumn = sheet.getLastColumn();
  //Logger.log("maxColumn="+maxColumn);
  
  var timestamp = _formCreationTimeStamp;
  
  //set uniqueId field: column 1
  var uniqueIdCell = sheet.getRange(lastRow+1,1);
  uniqueIdCell.setValue(uniqueId);
  
  //set timestamp field: column 2
  var timestampCell = sheet.getRange(lastRow+1,2);
  timestampCell.setValue(timestamp);
  
  var attachments = [];
  var htmlData = [];
  
  var reportHeader = "<h>"+"Fellowship Recommendation Letter Submission"+"</h>";
  reportHeader = reportHeader + "<br><p>Submission Date: " + timestamp + "</p>";
  reportHeader = reportHeader + "<p>Unique ID: " + uniqueId + "</p><br>";
  htmlData.push({"key":0, "value":reportHeader});
  
  for( var fieldName in formObject ) {  
  
    //checkNotExistingFieldsSpreadsheet(sheet,fieldName,1,maxColumn);
  
    //Logger.log('fieldName ='+fieldName);
    var value = formObject[fieldName];
    //Logger.log("value="+value);
    //Logger.log('fieldName ='+fieldName+":"+value);
    
    if( value != "" ) {    
        var rowHeader = 1;  //use first row in spreadsheet to hold names of the form (must be exact as in the form's field name)
        var col = getColIndexByName(fieldName);
        //Logger.log("fieldName="+fieldName+", col="+col);
        
        if( col > 0 ) {
          var cell = sheet.getRange(lastRow+1,col);
                 
          //Logger.log("set value="+value); 
          cell.setValue(value);
                         
          //create html report         
          var colTitleCell = sheet.getRange(2,col);
          var colTitle = colTitleCell.getValue().toString();
          //Logger.log("colTitle="+colTitle);           
          htmlData.push({"key":col, "value":"<p>" + colTitle + ": " + value + "</p>"});
          
        } //if  
    } //if    
      
  } //for
  
  //Logger.log('lastRow='+lastRow);
  //var targetRange = sheet.getRange(lastRow+1, 1, 1, 4).setValues( [[timestamp,lastName,firstName,uploadedPhotoUrl]] );
      
  var email = Trim(formObject.email);
  //Logger.log('email='+email);
  //formSendConfirmationEmail(email,uniqueId);  
  
  //create blob of attachments
  var blobArr = createUploadedFilesArr(formObject);
  
  //Logger.log('before htmlToPDFandEmail');
  htmlToPDFandEmail(htmlData,blobArr,email,uniqueId);
  
  //Logger.log('return uniqueId='+uniqueId);
  return uniqueId;
  
}

//Validate fields
function validateFormFields(formObject) {

  if( Trim(formObject.email) == "" ) {         
     throw new Error("Empty Email field");
  }

  if( Trim(formObject.fellowshipType) == "" ) {         
     throw new Error("Empty Fellowship Type field");
  }
  
  if( Trim(formObject.recommendationLetterID) == "" ) {
     //Logger.log("empty lastName="+lastName);     
     throw new Error("Empty Recommendation Letter ID field");
  }
  
  if( _fullValidation ) {
    if( Trim(formObject.uploadedLetterUrl) == "" ) {         
       throw new Error('Please click the "Choose file" button to select the recommendation letter (preferably in PDF format) and then make sure to click the "Press here to upload" button to complete the upload.');
    }
  }
}

//1) make a copy of the sheet from template
//2) if fails get a backup sheet
//Required: 
//var _recSpreadsheetFolderId - folder where the response spreadsheets (forms) are saved;
//var _recTemplateFileId = '1ITacytsUV2yChbfOSVjuBoW4aObSr_xBfpt6m_vab48';
//var _recBackupTemplateFileId = '19KlO1oCC88M436JzCa89xGO08MJ1txQNgLeJI0BpNGo';
function getSheetFromSingleTruthSource(uniqueId) {

    //doGet has _recSpreadsheetFolderId, but here _recSpreadsheetFolderId is null
    //Logger.log('start getSheetFromSingleTruthSource, uniqueId='+uniqueId+"; _recSpreadsheetFolderId="+_recSpreadsheetFolderId);

    var sheet = null;

    try {
      //var templateSheet = SpreadsheetApp.openById(_recTemplateFileId).getActiveSheet();
      var recSpreadsheetFolder = DriveApp.getFolderById(_recSpreadsheetFolderId);
    } catch(e) {
        MailApp.sendEmail(
            _useremail + "," + _adminemail,
            //_useremail, //testing
            "Google Drive failed to find the Spreadsheet folder",
            "Google Drive failed to find the Spreadsheet folder by id=" + _recSpreadsheetFolderId
            + ". Error=" + e.message
        );
    }
    
    try {
      //_recTemplateFileId= "testing!!!";
      //1) make a copy from template
      var copyFile = DriveApp.getFileById(_recTemplateFileId).makeCopy(uniqueId, recSpreadsheetFolder);
      //Logger.log('copy speadsheet='+copyFile.getId());
      sheet = SpreadsheetApp.openById(copyFile.getId()).getActiveSheet(); 
      //sheet = copyFile.getActiveSheet(); 
    
      
    } catch(e) {
    
      //Logger.log('copy error catch='+e.message);
    
      //2) get backup
      sheet = SpreadsheetApp.openById(_recBackupTemplateFileId).getActiveSheet();
      //Logger.log('backup sheet='+_recBackupTemplateFileId);
      
      //_useremail,_adminemail
      MailApp.sendEmail(
        _useremail+","+_adminemail, 
        "Google Drive failed to make a new copy from template", 
        "Google Drive failed to make a new copy from template for applicant=" + uniqueId + 
        ". Error=" + e.message +
        ". The application has been wtitten to a backup sheet with ID=" + _recBackupTemplateFileId
      );
      
    }

    return sheet;
}

function createUploadedFilesArr(formObject) {
  
  var blobArr = [];
    
  //var uniqueId = createUniqueId(formObject);
   
  var blob = formObject.recommendationLetter;
  if( blob ) {
     blob = setNewBlobName(formObject,blob,"RecommendationLetter");
     blobArr.push(blob);  
  }    
   
  return blobArr;
}

function htmlToPDFandEmail(htmlData,blobArr,email,uniqueId) {
  
  //Logger.log('htmlToPDFandEmail');
  //Logger.log(htmlData);
    
  //////////// 1) send email to applicant ////////////
  //Logger.log('before sending confirmation email to reference');
  var textHtml = "<p>Thank you for submitting the fellowship recommendation letter.</p>";
  MailApp.sendEmail(
    email, 
    "Fellowship Recommendation Letter Confirmation", 
    "Thank you for submitting the fellowship recommendation letter.", 
    {htmlBody: textHtml, attachments: blobArr });  
  //////////// EOF 1) send email to applicant ////////////  
   
  //////////// Construct form in HTML ////////////
  htmlData = sortByKey(htmlData,"key"); 
  
  var html = "";
  
  for( var key in htmlData ) {
    html = html + htmlData[key].value;    
  }   
  //Logger.log(htmlData);
      
  var filename = uniqueId+".pdf";
  
  var blob = Utilities.newBlob(html, "text/html", filename);
  var pdf = blob.getAs("application/pdf");
  
  blobArr.push(pdf);
  //////////// EOF Construct form in HTML ////////////
  
  
  //////////// 2) send email to admin ////////////
  //Logger.log('before sending confirmation email to admin');
  textHtml = "<p>The fellowship recommendation letter is submitted with unique ID " + uniqueId + ".</p>";
  MailApp.sendEmail(
    _useremail, 
    "[Fellowship Site] Fellowship Application Notification (" + uniqueId + ")", 
    "The fellowship application is submitted with unique ID " + uniqueId, 
    {htmlBody: textHtml, attachments: blobArr, bcc: _adminemail });
  //////////// EOF 2) send email to admin ////////////
  
  //Logger.log('htmlToPDFandEmail finished');
}

function sortByKey(array, key) {
    return array.sort(function(a, b) {
        var x = a[key]; var y = b[key];
        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
    });
}

function getConfigFileObject() {
  //Use the unique config file name "config-fellapp.json" in GAS and in PHP
  const files = DriveApp.getFilesByName('config-fellapp.json');

  if( files.hasNext() ) {
    const  file = files.next();
    const configFile = file.getAs('application/json');
    const configObject = JSON.parse(configFile.getDataAsString());
    return configObject;
  }

  return null;
}
function getConfigParameters(parameterKey) {
  var configObject = getConfigFileObject();

  if( !configObject ) {
    return null;
  }

  var parameter = configObject[parameterKey];

  if( !parameter ) {
    if( _adminemail == null ) {
      _adminemail = getConfigParameters("adminEmail");
    }
    if( _useremail == null ) {
      _useremail = getConfigParameters("fellappAdminEmail");
    }
    MailApp.sendEmail(
        _useremail+","+_adminemail,
        "Config file does not have parameter "+parameterKey,
        "Config file does not have parameter "+parameterKey
    );
  }

  return parameter;
}

