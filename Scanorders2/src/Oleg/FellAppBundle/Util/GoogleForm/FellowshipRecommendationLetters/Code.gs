var _colIndexNameMapArray = {};
var _uniqueId = null;

var _formCreationTimeStamp = CacheService.getPrivateCache().get('_formCreationTimeStamp');

var _destinationFolderSSKey = "1jK4XJf_Jqn_UvjTvbgiu4jV9Kvp5G3nY"; //folder where the response spreadsheets (forms) are saved;
var _templateSSKey = '1SwKJ04BFSGByTkROYuNAdKo-dEs2rhVFKf36g3DhKhE';
var _backupSSKey = '1nmBdCIatjBOXffoMsD-lSh6exSwczdMyJgtNmQBOhBs';

var _dropbox = "RecommendationLetterUploads"; //folder name where the recommendation letter will be uploaded

var _adminemail = 'oli2002@med.cornell.edu';
var _useremail = 'eah2006@med.cornell.edu';

var _AcceptingSubmissions = true;


//Maintenance flag (uncomment for maintenance)
//var _AcceptingSubmissions = false; 
//var _fullValidation = false; //will validate only fellapp type, names, email, signature
var _fullValidation = true;
var _useremail = 'cinava@yahoo.com';

//Request parameters
//http://wcmc.pathologysystems.org/fellowship-application-reference-letter-upload?
//Reference-Letter-ID=6ebd9 (hash)
//&Applicant-First-Name=John
//&Applicant-Last-Name=Smith
//&Applicant-E-Mail=john@smith.com
//&Fellowship-Type=Cytopathology
//&Fellowship-Start-Date=07-01-2018
//Reference fields (13)
//_ReferenceFirstName = null; //Reference-First-Name
//_ReferenceLastName = null;  //Reference-Last-Name
//_ReferenceDegree = null;    //Reference-Degree
//_ReferenceTitle = null;     //Reference-Title
//_ReferenceInstitution = null; //Reference-Institution
//_ReferencePhone = null; //Reference-Phone 
//_ReferenceEMail = null; //Reference-EMail
//_ReferenceStreet1 = null; //Reference-Street1
//_ReferenceStreet2 = null; //Reference-Street2
//_ReferenceCity = null; //Reference-City
//_ReferenceState = null; //Reference-State
//_ReferenceZip = null; //Reference-Zip
//_ReferenceCountry = null; //Reference-Country
//Applicant fields (7)
//_ReferenceLeterId = null; //Reference-Letter-ID
//_ApplicantFirstName = null; //Applicant-First-Name
//_ApplicantLastName = null; //Applicant-Last-Name
//_ApplicantEMail = null; //Applicant-EMail
//_FellowshipType = null; //Fellowship-Type
//_FellowshipStartDate = null; //Fellowship-Start-Date
//_FellowshipEndDate = null; //Fellowship-End-Date


function doGet(request) {   


  PropertiesService.getScriptProperties().setProperty('_jstest', 'jstest!!!');

  //PropertiesService.getScriptProperties().setProperty('_formCreationTimeStamp', getCurrentTimestamp());
  CacheService.getPrivateCache().put('_formCreationTimeStamp', getCurrentTimestamp(),10800); //expirationInSeconds 10800 sec => 3 hours
    
  var curUser = Session.getActiveUser().getEmail();
  //Logger.log('curUser='+curUser);
    
  if( !_AcceptingSubmissions ) {
    if( curUser == "olegivanov@pathologysystems.org" ) {
        _AcceptingSubmissions = true;
    }  
  } 
      
  if( _AcceptingSubmissions ) {    
     var template = HtmlService.createTemplateFromFile('Form.html');
     //var template = HtmlService.createTemplate('<b>The time is &lt;?= new Date() ?&gt;</b>');
  } else {
     var template = HtmlService.createTemplateFromFile('Maintanance.html');      
  }    
  
  //get request's parameters
  if(typeof request !== 'undefined') {
    //var urlParameters = ContentService.createTextOutput(JSON.stringify(request.parameter));
    //Logger.log('request.parameter:');
    //Logger.log(request.parameter);
    //Logger.log('urlParameters='+urlParameters);
    //Logger.log(urlParameters);
    
    _ReferenceLeterId = request.parameter['Reference-Letter-ID'];
    //Logger.log('_ReferenceLeterId='+_ReferenceLeterId);
    
    if( typeof _ReferenceLeterId === 'undefined' ) {
      var template = HtmlService.createTemplateFromFile('Error.html'); 
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
      ApplicantFirstName: request.parameter['Applicant-First-Name'], 
      ApplicantLastName: request.parameter['Applicant-Last-Name'], 
      ApplicantEMail: request.parameter['Applicant-E-Mail'], 
      FellowshipType: request.parameter['Fellowship-Type'], 
      FellowshipStartDate: request.parameter['Fellowship-Start-Date'],  
      FellowshipEndDate: request.parameter['Fellowship-End-Date'] 
    };
  } else {
    template.dataFromServerTemplate = {};
  } //if typeof request !== 'undefined'
  
  //template.action = ScriptApp.getService().getUrl();  
  //Logger.log('url='+ScriptApp.getService().getUrl());
  
  return template.evaluate().setSandboxMode(HtmlService.SandboxMode.IFRAME);
}


function uploadFilesLetter(form) {  
  Logger.log('uploadFilesLetter...');
  
  
  
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
          
    var folder, folders = DriveApp.getFoldersByName(_dropbox);
    
    if (folders.hasNext()) {
      folder = folders.next();
    } else {
      folder = DriveApp.createFolder(_dropbox);
    }
       

    //TODO: check file size   
    
    //var lastname = document.getElementById('textbox_id').value
    //console.log('lastname='+lastname);
            
    //var oldBlobName = blob.getName();
    //Logger.log('oldBlobName='+oldBlobName);   
    //Logger.log('upload _formCreationTimeStamp='+_formCreationTimeStamp);
    //var uniqueId = createUniqueId(form);
    //Logger.log('uniqueId='+uniqueId);    
    //blob.setName(uniqueId+"_"+oldBlobName);
            
    //var blob = form.name;    
    var file = folder.createFile(blob); 
                      
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
function createUniqueId(formObject) {

  if( _uniqueId ) {
     return _uniqueId;
  }

  //Logger.log(formObject);
  //validateFormBeforeUpload(formObject);
  var recommendationLetterID = Trim(formObject.recommendationLetterID);
  
  if( !_formCreationTimeStamp || _formCreationTimeStamp == null || _formCreationTimeStamp == "" ) {
     Logger.log('_formCreationTimeStamp is invalid, _formCreationTimeStamp='+_formCreationTimeStamp);
     _formCreationTimeStamp = getCurrentTimestamp();
     CacheService.getPrivateCache().put('_formCreationTimeStamp', _formCreationTimeStamp,21600); //expirationInSeconds 21600 sec=>6 hours
  }
  var timestamp = _formCreationTimeStamp;  
  timestamp = timestamp.replace(" ", "-");
  timestamp = timestamp.replace(":", "-");
  
  var uniqueId = recommendationLetterID+"_"+timestamp;
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
  Logger.log("start processForm");
  validateFormFields(formObject);   
  
  //set Unique ID based on email_lastname_firstname_timestamp
  //var uniqueId = email+lastName+"_"+firstName+"_"+"_"+timestamp;
  var uniqueId = createUniqueId(formObject);
    
  var sheet = getSheetFromSingleTruthSource(uniqueId);
  
  //create mapping array with header=index
  _colIndexNameMapArray = getColIndexNameMapArray(sheet);
  
  var lastRow = sheet.getLastRow();
  var maxColumn = sheet.getLastColumn();
  Logger.log("maxColumn="+maxColumn);
  
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
        Logger.log("fieldName="+fieldName+", col="+col);  
        
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
  Logger.log('email='+email);
  //formSendConfirmationEmail(email,uniqueId);  
  
  //create blob of attachments
  var blobArr = createUploadedFilesArr(formObject);
  
  //Logger.log('before htmlToPDFandEmail');
  htmlToPDFandEmail(htmlData,blobArr,email,uniqueId);
  
  Logger.log('return uniqueId='+uniqueId);
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
//var _destinationFolderSSKey - folder where the response spreadsheets (forms) are saved;
//var _templateSSKey = '1ITacytsUV2yChbfOSVjuBoW4aObSr_xBfpt6m_vab48';
//var _backupSSKey = '19KlO1oCC88M436JzCa89xGO08MJ1txQNgLeJI0BpNGo';
function getSheetFromSingleTruthSource(uniqueId) {

    var sheet = null;

    //var templateSheet = SpreadsheetApp.openById(_templateSSKey).getActiveSheet(); 
    var destinationFolder = DriveApp.getFolderById(_destinationFolderSSKey); 
    
    try {
      //_templateSSKey= "testing!!!";
      //1) make a copy from template
      var copyFile = DriveApp.getFileById(_templateSSKey).makeCopy(uniqueId, destinationFolder);
      //Logger.log('copy speadsheet='+copyFile.getId());
      sheet = SpreadsheetApp.openById(copyFile.getId()).getActiveSheet(); 
      //sheet = copyFile.getActiveSheet(); 
    
      
    } catch(e) {
    
      Logger.log('copy error catch='+e.message);
    
      //2) get backup
      sheet = SpreadsheetApp.openById(_backupSSKey).getActiveSheet(); 
      Logger.log('backup sheet='+_backupSSKey);
      
      //_useremail,_adminemail
      MailApp.sendEmail(
        _useremail+","+_adminemail, 
        "Google Drive failed to make a new copy from template", 
        "Google Drive failed to make a new copy from template for applicant=" + uniqueId + 
        ". Error=" + e.message +
        ". The application has been wtitten to a backup sheet with ID=" + _backupSSKey
      );
      
    }

    return sheet;
}

function createUploadedFilesArr(formObject) {
  
  var blobArr = [];
    
  var uniqueId = createUniqueId(formObject);    
   
  var blob = formObject.recommendationLetter;
  if( blob ) {
     blob = setNewBlobName(formObject,blob,"RecommendationLetter");
     blobArr.push(blob);  
  }    
   
  return blobArr;
}

function htmlToPDFandEmail(htmlData,blobArr,email,uniqueId) {
  
  Logger.log('htmlToPDFandEmail');      
  //Logger.log(htmlData);
    
  //////////// 1) send email to applicant ////////////
  Logger.log('before sending confirmation email to reference');  
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
  Logger.log('before sending confirmation email to admin');  
  textHtml = "<p>The fellowship recommendation letter is submitted with unique ID " + uniqueId + ".</p>";
  MailApp.sendEmail(
    _useremail, 
    "[Fellowship Site] Fellowship Application Notification (" + uniqueId + ")", 
    "The fellowship application is submitted with unique ID " + uniqueId, 
    {htmlBody: textHtml, attachments: blobArr, bcc: _adminemail });
  //////////// EOF 2) send email to admin ////////////
  
  Logger.log('htmlToPDFandEmail finished');
}

function sortByKey(array, key) {
    return array.sort(function(a, b) {
        var x = a[key]; var y = b[key];
        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
    });
}
