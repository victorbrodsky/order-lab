var _colIndexNameMapArray = {};
var _uniqueId = null;

var _formCreationTimeStamp = CacheService.getPrivateCache().get('_formCreationTimeStamp');

var _dropbox = "FellowshipRecommendationLetters"; 

var _adminemail = 'oli2002@med.cornell.edu';
var _useremail = 'eah2006@med.cornell.edu';

var _AcceptingSubmissions = true;


//Maintenance flag (uncomment for maintenance)
var _AcceptingSubmissions = false; 
//var _fullValidation = false; //will validate only fellapp type, names, email, signature
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
_ReferenceFirstName = null; //Reference-First-Name
_ReferenceLastName = null;  //Reference-Last-Name
_ReferenceDegree = null;    //Reference-Degree
_ReferenceTitle = null;     //Reference-Title
_ReferenceInstitution = null; //Reference-Institution
_ReferencePhone = null; //Reference-Phone 
_ReferenceEMail = null; //Reference-EMail
_ReferenceStreet1 = null; //Reference-Street1
_ReferenceStreet2 = null; //Reference-Street2
_ReferenceCity = null; //Reference-City
_ReferenceState = null; //Reference-State
_ReferenceZip = null; //Reference-Zip
_ReferenceCountry = null; //Reference-Country
//Applicant fields (7)
_ReferenceLeterId = null; //Reference-Letter-ID
_ApplicantFirstName = null; //Applicant-First-Name
_ApplicantLastName = null; //Applicant-Last-Name
_ApplicantEMail = null; //Applicant-EMail
_FellowshipType = null; //Fellowship-Type
_FellowshipStartDate = null; //Fellowship-Start-Date
_FellowshipEndDate = null; //Fellowship-End-Date


function doGet(request) {   


  PropertiesService.getScriptProperties().setProperty('_jstest', 'jstest!!!');

  //PropertiesService.getScriptProperties().setProperty('_formCreationTimeStamp', getCurrentTimestamp());
  CacheService.getPrivateCache().put('_formCreationTimeStamp', getCurrentTimestamp(),10800); //expirationInSeconds 10800 sec => 3 hours
    
  var curUser = Session.getActiveUser().getEmail();
  Logger.log('curUser='+curUser);
    
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
    Logger.log('request.parameter:');
    Logger.log(request.parameter);
    //Logger.log('urlParameters='+urlParameters);
    //Logger.log(urlParameters);
    
    _ReferenceLeterId = request.parameter['Reference-Letter-ID'];
    Logger.log('_ReferenceLeterId='+_ReferenceLeterId);
    
    if( typeof _ReferenceLeterId === 'undefined' ) {
      var template = HtmlService.createTemplateFromFile('Error.html'); 
    }
    
    _ApplicantFirstName = request.parameter['Applicant-First-Name'];   
    _ApplicantLastName = request.parameter['Applicant-Last-Name'];       
    _ApplicantEMail = request.parameter['Applicant-E-Mail'];
    _FellowshipType = request.parameter['Fellowship-Type'];
    _FellowshipStartDate = request.parameter['Fellowship-Start-Date'];
    
    
    
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
      ApplicantFirstName: _ApplicantFirstName, 
      ApplicantLastName: _ApplicantLastName, 
      ApplicantEMail: _ApplicantEMail, 
      FellowshipType: _FellowshipType, 
      FellowshipStartDate: _FellowshipStartDate,  
      FellowshipEndDate: request.parameter['Fellowship-End-Date'],  
    };
  }
  
  //template.action = ScriptApp.getService().getUrl();  
  Logger.log('url='+ScriptApp.getService().getUrl());
  
  return template.evaluate().setSandboxMode(HtmlService.SandboxMode.IFRAME);
}


function uploadFilesLetter(form) {  
  Logger.log('uploadFilesLetter...');
  var blob = form.recommendationLetter;
  blob = setNewBlobName(form,blob,"recommendationLetter");
  return uploadFile(form,blob);  
}
function uploadFile(form,blob) {
    
  Logger.log('blob='+blob);
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

function setNewBlobName(formObject,blob,fileType) {
    var oldBlobName = blob.getName();
    var uniqueId = createUniqueId(formObject);
    Logger.log('oldBlobName='+oldBlobName);
    blob.setName(uniqueId+"-"+fileType+"-"+oldBlobName);
    return blob;
}

function createUniqueId(formObject) {

  if( _uniqueId ) {
     return _uniqueId;
  }

  //Logger.log(formObject);
  //validateFormBeforeUpload(formObject);
  var lastName = Trim(formObject.lastName);
  var firstName = Trim(formObject.firstName);
  var email = Trim(formObject.email);
  
  if( !_formCreationTimeStamp || _formCreationTimeStamp == null || _formCreationTimeStamp == "" ) {
     Logger.log('_formCreationTimeStamp is invalid, _formCreationTimeStamp='+_formCreationTimeStamp);
     _formCreationTimeStamp = getCurrentTimestamp();
     CacheService.getPrivateCache().put('_formCreationTimeStamp', _formCreationTimeStamp,21600); //expirationInSeconds 21600 sec=>6 hours
  }
  var timestamp = _formCreationTimeStamp;  
  timestamp = timestamp.replace(" ", "_");
  timestamp = timestamp.replace(":", "_");
  
  var uniqueId = email+"_"+lastName+"_"+firstName+"_"+timestamp;
  if( uniqueId == null || uniqueId == "" ) {
     Logger.log('uniqueId is invalid, uniqueId='+uniqueId);
  }
  uniqueId = uniqueId.replace(" ", "_");
  uniqueId = uniqueId.replace(":", "_");
  uniqueId = uniqueId.replace("@", "_");   //@ cause the query sq problem by Google Sheet API
  uniqueId = uniqueId.replace(".", "_");
  
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


//use first row in spreadsheet to hold names of the form (must be exact as in the form's field name)
//use second row in spreadsheet to hold field labels (we need them to print report)
function processForm(formObject) {
  
  //var lastName = Trim(formObject.lastName);
  //var firstName = Trim(formObject.firstName);
  //var email = Trim(formObject.email);  
  //Logger.log("start processForm");
  
  //var middleName = Trim(formObject.middleName);
  //Logger.log("middleName="+middleName);
  
  validateFormFields(formObject);   
  
  //set Unique ID based on email_lastname_firstname_timestamp
  //var uniqueId = email+lastName+"_"+firstName+"_"+"_"+timestamp;
  var uniqueId = createUniqueId(formObject);
    
  var sheet = getSheetFromSingleTruthSource(uniqueId);
  
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
  
  var reportHeader = "<h>"+"Fellowship Application"+"</h>";
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
          
          //replace other fellowship type
          if( fieldName == "fellowshipType" && value == "Other" ) {
              value = formObject.otherFellowshipType;
          }
          
          //Logger.log("set value="+value); 
          cell.setValue(value);
          
          //create array of attachment by 'uploaded' string in fieldname'uploadedCVUrl' 'uploadedLegalExplanationUrl' ...
          //var fieldNameStr = fieldName+"";
          //Logger.log("fieldNameStr="+fieldNameStr); 
          //var stringIndex = fieldNameStr.indexOf("uploaded");         
          //Logger.log("stringIndex="+stringIndex+" "+fieldNameStr); 
          //if( stringIndex > -1 ) {
          //   Logger.log("add attachment="+fieldName); 
          //   var fileBlob = formObject.fieldName;
          //   attachments.push(fileBlob);
          //}
          
          //create html report         
          var colTitleCell = sheet.getRange(2,col);
          var colTitle = colTitleCell.getValue().toString();
          //Logger.log("colTitle="+colTitle);           
          htmlData.push({"key":col, "value":"<p>" + colTitle + ": " + value + "</p>"});
          
        } //if  
    } //if    
      
  } //for
}

//1) make a copy of the sheet from template
//2) if fails get a backup sheet
function getSheetFromSingleTruthSource(uniqueId) {

    var sheet = null;

    //var templateSheet = SpreadsheetApp.openById(_templateSSKey).getActiveSheet(); 
    var destinationFolder = DriveApp.getFolderById(_destinationFolder); 
    
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

