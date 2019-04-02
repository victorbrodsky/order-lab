//https://sites.google.com/a/pathologysystems.org/wcmc/fellowship-application
//http://wcmc.pathologysystems.org/fellowship-application

var _templateSSKey = '1ITacytsUV2yChbfOSVjuBoW4aObSr_xBfpt6m_vab48';
var _backupSSKey = '19KlO1oCC88M436JzCa89xGO08MJ1txQNgLeJI0BpNGo';
//Destination forlder: ID of the folder where a newly created copy of template spreadsheet will be placed (Spreadsheets).
var _destinationFolder = '163MoMsFodHxPj98dM-C_cLTVU5bkGHz1'; //'0B2FwyaXvFk1ecEJVeWc3VW1wS2c';
//Unique folder name where uploaded files will be placed.
var _dropbox = "FellowshipApplicantUploads"; 
var _configFolderId = "0B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M";

var _colIndexNameMapArray = {};
var _uniqueId = null;

var _formCreationTimeStamp = CacheService.getPrivateCache().get('_formCreationTimeStamp');

var _adminemail = 'oli2002@med.cornell.edu';
var _useremail = 'eah2006@med.cornell.edu';
//var _useremail = 'cinava@yahoo.com';

var _AcceptingSubmissions = true;
var _fullValidation = true;

//Maintenance flag (uncomment for maintenance)
var _AcceptingSubmissions = false; 
//var _fullValidation = false; //will validate only fellapp type, names, email, signature
var _useremail = 'cinava@yahoo.com';
//EOF Maintenance flag

//var _fileUrl;

//default fellowship types
var _Status = false;

var _FellowshipTypes_Orig = [
      {
        "id": "s1",
        "text": "s1"
      },
      {
        "id": "s2",
        "text": "s2"
      }
    ];
 var _FellowshipTypes = [];   


function doGet(request) {   

  //Logger.log("reading config data");
  _AcceptingSubmissions = getConfigParameters("acceptingSubmissions");
  //var status = getConfigParameter(configFile);
  //Logger.log('status='+configFile);
  Logger.log("_AcceptingSubmissions="+_AcceptingSubmissions);
  //Logger.log("_FellowshipTypes:");
  //Logger.log(_FellowshipTypes);
  //dssrrs.gsgs;

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
  } else {
     var template = HtmlService.createTemplateFromFile('Maintanance.html');      
  }    
  
  //template.action = ScriptApp.getService().getUrl();  
  //Logger.log('url='+ScriptApp.getService().getUrl());
  
  return template.evaluate().setSandboxMode(HtmlService.SandboxMode.IFRAME);
}

function doGet_out(request) {  
  var template = HtmlService.createTemplateFromFile('Form.html');   
  template.action = ScriptApp.getService().getUrl();  
  //Logger.log('url='+ScriptApp.getService().getUrl());
  
  var result = template.evaluate().getContent(); 
  //Logger.log('result='+result);
   
  var content = request.parameters.prefix + '(' +JSON.stringify(result) + ')';   
  //Logger.log('content='+content);  
  
  return ContentService.createTextOutput(content).setMimeType(ContentService.MimeType.JSON);
}


function include(filename) {
  return HtmlService.createHtmlOutputFromFile(filename).getContent();
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
          
        }   
    }    
      
  }
    
  
  //Logger.log('lastRow='+lastRow);
  //var targetRange = sheet.getRange(lastRow+1, 1, 1, 4).setValues( [[timestamp,lastName,firstName,uploadedPhotoUrl]] );
      
  var email = Trim(formObject.email);
  //Logger.log('email='+email);
  //formSendConfirmationEmail(email,uniqueId);  
  
  //create blob of attachments
  var blobArr = createUploadedFilesArr(formObject);
  
  //Logger.log('before htmlToPDFandEmail');
  htmlToPDFandEmail(htmlData,blobArr,email,uniqueId);
  
  Logger.log('return uniqueId='+uniqueId);
  return uniqueId;
  
  // Fill in response template
  //var template = HtmlService.createTemplateFromFile('Thanks.html');
  //var name = template.name = theForm.name;
  //var department = template.department = theForm.department;
  //var message = template.message = theForm.message;
  //template.email = formObject.email;     
  //var fileUrl = template.fileUrl = doc.getUrl();
  
  // Return HTML text for display in page.
  //return template.evaluate().getContent();
  
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


function createUploadedFilesArr(formObject) {
  
  var blobArr = [];
    
  var uniqueId = createUniqueId(formObject);    
  
  var blob = formObject.applicantPhoto;
  if( blob ) {     
     blob = setNewBlobName(formObject,blob,"Photo");
     blobArr.push(blob);  
  }
  
  var blob = formObject.curriculumVitae;
  if( blob ) {
     blob = setNewBlobName(formObject,blob,"CV");
     blobArr.push(blob);  
  }
  
  var blob = formObject.coverLetter;
  if( blob ) {
     blob = setNewBlobName(formObject,blob,"CoverLetter");
     blobArr.push(blob);  
  }
  
  var blob = formObject.reprimandExplanation;
  if( blob ) {
     blob = setNewBlobName(formObject,blob,"ReprimandExplanation");
     blobArr.push(blob);  
  }
  
  var blob = formObject.legalExplanation;
  if( blob ) {
     blob = setNewBlobName(formObject,blob,"LegalExplanation");
     blobArr.push(blob);  
  }
  
  var blob = formObject.USMLEScores;
  if( blob ) {
     blob = setNewBlobName(formObject,blob,"USMLEScores");
     blobArr.push(blob);  
  }
   
  return blobArr;
}

function formSendConfirmationEmail_TODEL(email,uniqueId) {  
  if( email != "" ) {
    MailApp.sendEmail(email,
                   "Fellowship Application Confirmation",
                   "Thank you for submitting the fellowship application. Your unique ID is " + uniqueId);
  }  
};


function htmlToPDFandEmail(htmlData,blobArr,email,uniqueId) {
  
  Logger.log('htmlToPDFandEmail');      
  //Logger.log(htmlData);
    
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
  
  //var pdfFile = DriveApp.createFile(pdf);
  //pdfFile.setName(filename);
  
  var textHtml = "<p>Thank you for submitting the fellowship application.</p> <p>Your unique ID is " + uniqueId + ".</p>";
  
  Logger.log('before sending confirmation email to applicant');
  //send email to applicant
  MailApp.sendEmail(
    email, 
    "Fellowship Application Confirmation", 
    "Thank you for submitting the fellowship application. Your unique ID is " + uniqueId, 
    {htmlBody: textHtml, attachments: blobArr });
  
  Logger.log('before sending confirmation email to user');  
  //send email to admin
  textHtml = "<p>The fellowship application is submitted with unique ID " + uniqueId + ".</p>";
  MailApp.sendEmail(
    _useremail, 
    "[Fellowship Site] Fellowship Application Notification (" + uniqueId + ")", 
    "The fellowship application is submitted with unique ID " + uniqueId, 
    {htmlBody: textHtml, attachments: blobArr, bcc: _adminemail });
  
  //pdfFile.setTrashed(true);
  
  Logger.log('htmlToPDFandEmail finished');
}

function sortByKey(array, key) {
    return array.sort(function(a, b) {
        var x = a[key]; var y = b[key];
        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
    });
}


function getJsData() {
  
  var fellowshipTypes = getConfigParameters("fellowshipTypes");
  
  if( !fellowshipTypes ) {
    return _FellowshipTypes;
  }
  
    return fellowshipTypes;
}

function onFormSuccess_TODEL() {
  //Logger.log('on Form Success');
  
  var app = UiApp.getActiveApplication();
  
  app.close();
  
  //Logger.log(e);
  //var template = HtmlService.createTemplateFromFile('Thanks.html');  
  //var lastName = Trim(formObject.lastName);
  //template.name = lastName; 
  //return template.evaluate();
}

function checkNotExistingFieldsSpreadsheet(sheet,fieldName,rowHeader,maxColumn) {
    var col = getColIndexByName(fieldName);
    if( col < 0 ) {
       Logger.log("not existing fieldName="+fieldName+", col="+col);
    }
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



//Validate fields
function validateFormFields(formObject) {


  if( Trim(formObject.fellowshipType) == "" ) {         
     throw new Error("Empty Fellowship Type field");
  }
  
  if( Trim(formObject.lastName) == "" ) {
     //Logger.log("empty lastName="+lastName);     
     throw new Error("Empty Last Name field");
  }
  
  if( Trim(formObject.firstName) == "" ) {         
     throw new Error("Empty First Name field");
  }
  
  if( Trim(formObject.email) == "" ) {         
     throw new Error("Empty E-mail field");
  }   
  
  //validate email for @ and .
  validateEmailFormat(formObject.email,"Applicant");
  
  if( _fullValidation ) {
      validateUsmleComlex(formObject);
  }
  
  //Recommendations
  if( _fullValidation ) { //don't validate references
    
    if( Trim(formObject.recommendation1FirstName) == "" || Trim(formObject.recommendation1LastName) == "" ) {         
       throw new Error("Reference #1 First or Last Name are empty");
    }
    validateEmailFormat(formObject.recommendation1Email,"Reference #1");
    
    if( Trim(formObject.recommendation2FirstName) == "" || Trim(formObject.recommendation2LastName) == "" ) {         
       throw new Error("Reference #2 First or Last Name are empty");
    }
    validateEmailFormat(formObject.recommendation2Email,"Reference #2");
    
    if( Trim(formObject.recommendation3FirstName) == "" || Trim(formObject.recommendation3LastName) == "" ) {         
       throw new Error("Reference #3 First or Last Name are empty");
    }
    validateEmailFormat(formObject.recommendation3Email,"Reference #3");
    
    //validateOptionalReference(formObject);
  }
  
  if( _fullValidation ) {
    if( Trim(formObject.uploadedPhotoUrl) == "" ) {         
       throw new Error("Photo is not uploaded");
    }
    
    if( Trim(formObject.uploadedCVUrl) == "" ) {         
       throw new Error("CV is not uploaded");
    }
    
    if( Trim(formObject.uploadedCoverLetterUrl) == "" ) {         
       throw new Error("Cover Letter is not uploaded");
    }
    
    if( Trim(formObject.uploadedUSMLEScoresUrl) == "" ) {         
       throw new Error("USMLE Scores are not uploaded");
    }
  }
  
  if( Trim(formObject.signatureName) == "" ) {         
    throw new Error("Please sign the form");
  }
  
  if( Trim(formObject.signatureDate) == "" ) {         
    throw new Error("Signature date field is empty");
  }
  
}

//Function to validate email
function validateEmailFormat(email,text) {
  email = Trim(email);
  if( email == "" ) {
    throw new Error("Please enter an email address for " + text);
  }
  
  var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
  if( emailPattern.test(email) == false ) {
    throw new Error("E-mail format for " + text + " is invalid; Email:" + email);
  }     
}
function validateOptionalReference(formObject) {
  if( formObject.recommendation4FirstName != "" || formObject.recommendation4LastName != "" ) {
    validateEmailFormat(formObject.recommendation4Email,"Reference 4");
  }
}

// USMLEStep1Score or COMLEXLevel1Score should be filled out
function validateUsmleComlex(formObject) {
  
  //allow only digits
  checkIfValueDigit(formObject.USMLEStep1Score);
  checkIfValueDigit(formObject.USMLEStep2CKScore);
  checkIfValueDigit(formObject.USMLEStep2CSScore);
  checkIfValueDigit(formObject.USMLEStep3Score);
  checkIfValueDigit(formObject.COMLEXLevel1Score);
  checkIfValueDigit(formObject.COMLEXLevel2Score);
  checkIfValueDigit(formObject.COMLEXLevel3Score);
  
  var USMLEStep1 = false;
  var COMLEXLevel1 = false;
  
  if( formObject.USMLEStep1DatePassed || formObject.USMLEStep1Score ) {     
    USMLEStep1 = true;
  }
  
  if( formObject.COMLEXLevel1Score || formObject.COMLEXLevel1DatePassed ) {
     COMLEXLevel1 = true;
  }
  
 
  if( !USMLEStep1 && !COMLEXLevel1 ) {
     throw new Error("Please enter either USMLE Step 1 Score and Date passed or COMLEX Level 1 Score and Date passed above");
  }
  
  if( USMLEStep1 ) {  
    if( Trim(formObject.USMLEStep1DatePassed) == "" ) {         
      throw new Error("Empty USMLE Step 1 Passed Date");
    } 
    
    if( Trim(formObject.USMLEStep1Score) == "" ) {         
      throw new Error("Empty USMLE Step 1 Score");
    } 
  }
  
  if( COMLEXLevel1 ) {  
    if( Trim(formObject.COMLEXLevel1DatePassed) == "" ) {         
      throw new Error("Empty COMLEX Level 1 Passed Date");
    } 
    
    if( Trim(formObject.COMLEXLevel1Score) == "" ) {         
      throw new Error("Empty COMLEX Level 1 Score");
    } 
  }
   
  
  //throw new Error("validateUsmleComlex passed");
}

function checkIfValueDigit( value ) {
  if( value ) {
    if( value.match(/^[0-9]+$/) ) { 
      //throw new Error("Score is a number !!! value="+value);
    } else {
      throw new Error("Score can contain only digits. Invalid score provided: " + value);
    }
  }
}


//Uploads
function uploadFilesPhoto(form) {  
  var blob = form.applicantPhoto; 
  blob = setNewBlobName(form,blob,"Photo");
  return uploadFile(form,blob);  
}

function uploadFilesCV(form) {  
  var blob = form.curriculumVitae;
  blob = setNewBlobName(form,blob,"CV");
  return uploadFile(form,blob);  
}

function uploadFilesCoverLetter(form) {  
  var blob = form.coverLetter;
  blob = setNewBlobName(form,blob,"CoverLetter");
  return uploadFile(form,blob);  
}

function uploadFilesReprimandExplanation(form) {  
  var blob = form.reprimandExplanation;
  blob = setNewBlobName(form,blob,"ReprimandExplanation");
  return uploadFile(form,blob);  
}

function uploadFilesLegalExplanation(form) {  
  var blob = form.legalExplanation;
  blob = setNewBlobName(form,blob,"LegalExplanation");
  return uploadFile(form,blob);  
}

function uploadFilesUSMLEScores(form) {  
  var blob = form.USMLEScores;
  blob = setNewBlobName(form,blob,"USMLEScores");
  return uploadFile(form,blob);  
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

function setNewBlobName(formObject,blob,fileType) {
    var oldBlobName = blob.getName();
    var uniqueId = createUniqueId(formObject);
    //Logger.log('oldBlobName='+oldBlobName);
    blob.setName(uniqueId+"-"+fileType+"-"+oldBlobName);
    return blob;
}

//function validateFormBeforeUpload(form) {
//    // check for last name, first name and email before uploading file
//    var lastName = Trim(form.lastName);
//    var firstName = Trim(form.firstName);
//    var email = Trim(form.email);
//    Logger.log("lastName="+lastName);  
//    
//    
//    if( lastName == "" ) {
//       Logger.log("empty lastName="+lastName);     
//       throw new Error("Please fill in your Last Name before uploading");
//    }
//  
//    if( firstName == "" ) {         
//       throw new Error("Please fill in your First Name before uploading");
//    }
//    
//    if( email == "" ) {         
//       throw new Error("Please fill in your E-mail before uploading");
//    } 
//}






//Util functions

function getCurrentTimestamp() {
  var timezone = "GMT-4";
  var timestamp_format = "yyyy-MM-dd HH:mm:ss";
  var date = Utilities.formatDate(new Date(), timezone, timestamp_format);
  return date;
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
     
     //Logger.log(value.toString() + "?=" + name);          
    
  }  
  //Logger.log("_colIndexNameMapArray:");   
  //Logger.log(_colIndexNameMapArray);  
  
  //testing
  //var lookVar = 5;
  //var col = _colIndexNameMapArray[lookVar];
  //Logger.log("col="+col);
  
  return _colIndexNameMapArray;
}

function getColIndexByName(name) {
  
  //Logger.log("getColIndexByName: name="+name);
  //name = "test name";
  var col = _colIndexNameMapArray[name];
  //Logger.log("col="+col);
  
  if( col == undefined ) {
    //Logger.log("name="+name+" not found in Spreadsheet!!!");
    col = -1;
  }
  
  return col;
}


function Trim(string) {
  if( !string || string == null || string == "" ) {
    Logger.log("string to trim is invalid, string="+string);
    return "";
  }
  return string.replace(/\s/g, ""); 
}

function getConfigParameters(parameterKey) {
  //var sheetname = "test";
  //var aUrl = "http://pipes.yahoo.com/pipes/pipe.run?_id=286bbb1d8d30f65b54173b3b752fa4d9&_render=json";
  //var aUrl = "https://drive.google.com/drive/u/0/folders/0B2FwyaXvFk1efmlPOEl6WWItcnBveVlDWWh6RTJxYzYyMlY2MjRSalRvUjdjdzMycmo5U3M";
  
  //Get a reference to the folder    
  fldr = DriveApp.getFolderById(_configFolderId);

  //Get all files by that name. Put return into a variable
  allFilesInFolder = fldr.getFilesByName("config.json");
  Logger.log('allFilesInFolder: ' + allFilesInFolder);
  
  if (allFilesInFolder.hasNext() === false) {
    //If no file is found, the user gave a non-existent file name
    return false;
  };
  
  var configFile = null;
  //cntFiles = 0;
  //Even if it's only one file, must iterate a while loop in order to access the file.
  //Google drive will allow multiple files of the same name.
  while (allFilesInFolder.hasNext()) {
    //thisFile = allFilesInFolder.next();
    //cntFiles = cntFiles + 1;
    //Logger.log('File Count: ' + cntFiles);

    //docContent = thisFile.getAs('application/json');
    //Logger.log('docContent : ' + docContent );
    
    
    // define a File object variable and set the Media Tyep
    var file = allFilesInFolder.next();
    configFile = file.getAs('application/json')
    
    // log the contents of the file
    Logger.log("configFile:");
    Logger.log(configFile.getDataAsString());
    
    //return configFile;
  };
  
  //return NULL;
  
  var configObject = JSON.parse(configFile.getDataAsString());
  
  var parameter = configObject[parameterKey];
  Logger.log("parameter:");
  Logger.log(parameter);
  
  return parameter;
  
  _Status = configObject.status;
  _FellowshipTypes = configObject.fellowshiptypes;
  fellowshiptypeId = _FellowshipTypes[0].id;
  fellowshiptypeName = _FellowshipTypes[0].text;
  Logger.log("_Status="+_Status);
  Logger.log("fellowshiptypeId="+fellowshiptypeId);
  Logger.log("fellowshiptypeName="+fellowshiptypeName); 
  //Logger.log("fellowshipTypes:");
  //Logger.log(fellowshipTypes);
  
  //_FellowshipTypes = fellowshipTypes;
  
  
  
  return configFile;
}



