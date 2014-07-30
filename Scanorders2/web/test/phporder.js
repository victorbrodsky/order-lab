var casper = require('casper').create()
//var colorizer = require('colorizer').create('Colorizer');
var x = require('casper').selectXPath;

// casper.userAgent('Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)'); 
 
var userNames = ['testsubmitter','testextsubmitter','testprocessor','testadministrator'];
var passWords = ['testsubmitter1','testextsubmitter1','testprocessor1','testadministrator1'];
var url = 'http://collage.med.cornell.edu/order/scan/login';
var tracker = {Success: [], Fail: []};
 
function login(username, password) {
	casper.then(function () {
		this.sendKeys('#username', username);
		this.sendKeys('#password', password);
		this.click('body > div > form > button');
	});
	casper.waitFor(function check() {
	    return this.evaluate(function() {
	        return document.getElementById('nav-bar-myscanorders');
	    });
	}, function then() {    // step to execute when check() is ok
			this.click('#nav-bar-user > ul > li:nth-child(2) > a'); 
			tracker.Success.push(username);
			//this.echo(this.fetchText('#nav-bar-user > ul > li:nth-child(1) > a') + " you logged in.");
			this.capture('Success_'+username+'.png');		
	}, function timeout() { // step to execute if check has failed
		tracker.Fail.push(username);
		//this.echo("Warning: " + username + " could not be logged in.", "WARNING");
		this.capture('Fail_'+username+'.png');
	});    
};

casper.start(); 
casper.viewport(1024, 768);
 
userNames.forEach(function(username, index){
    casper.thenOpen(url); // open the start page
    login(username, passWords[index]); // schedule the steps
});

casper.then(function () {
			//this.echo("Success: " + tracker.Success.length, "INFO");
			//this.echo("Fail: " + tracker.Fail.length, "WARNING");
			//this.echo(JSON.stringify(tracker));
			this.echo("Success: " + tracker.Success.length + " " + JSON.stringify(tracker.Success) + " Fail: " + tracker.Fail.length + " " + JSON.stringify(tracker.Fail));
		});
		
casper.run(); // begin the execution