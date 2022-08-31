/**
 * This work is licensed under the Creative Commons Attribution-Share Alike 3.0
 * United States License. To view a copy of this license,
 * visit http://creativecommons.org/licenses/by-sa/3.0/us/ or send a letter
 * to Creative Commons, 171 Second Street, Suite 300, San Francisco, California, 94105, USA.
 *
 * Modified by: Jill Elaine
 * Email: jillelaine01@gmail.com
 *
 * Configurable idle (no activity) timer and logout redirect for jQuery.
 * Works across multiple windows, tabs and iframes from the same domain.
 *
 * Dependencies: JQuery v1.7+, JQuery UI, store.js from https://github.com/marcuswestin/store.js - v1.3.4+
 *
 * Commented and console logged for debugging with Firefox & Firebug or similar
 * version 1.0.10
 **/

/*global jQuery: false, document: false, store: false, clearInterval: false, setInterval: false, setTimeout: false, clearTimeout: false, window: false, alert: false, console: false*/
/*jslint indent: 2, sloppy: true, plusplus: true*/

(function ($) {

  $.fn.idleTimeout = function (userRuntimeConfig) {

    console.log('start of jquery-idleTimeout plugin');

    //##############################
    //## Public Configuration Variables
    //##############################
    var defaultConfig = {
      redirectUrl: '/logout',      // redirect to this url on logout. Set to "redirectUrl: false" to disable redirect

      // idle settings
      idleTimeLimit: 30,           // 30 seconds for testing. 'No activity' time limit in seconds. 1200 = 20 Minutes
      idleCheckHeartbeat: 2,       // Frequency to check for idle timeouts in seconds

      // optional custom callback to perform before logout
      customCallback: false,       // set to false for no customCallback
      // customCallback:    function () {    // define optional custom js function
          // perform custom action before logout
      // },

      // configure which activity events to detect
      // http://www.quirksmode.org/dom/events/
      // https://developer.mozilla.org/en-US/docs/Web/Reference/Events
      activityEvents: 'click keypress scroll wheel mousewheel', // separate each event with a space

      // warning dialog box configuration
      enableDialog: true,           // set to false for logout without warning dialog
      dialogDisplayLimit: 20,       // 20 seconds for testing. Time to display the warning dialog before logout (and optional callback) in seconds. 180 = 3 Minutes
      dialogTitle: 'Session Expiration Warning', // also displays on browser title bar
      dialogText: 'Because you have been inactive, your session is about to expire.',
      dialogTimeRemaining: 'Time remaining',
      dialogStayLoggedInButton: 'Stay Logged In',
      dialogLogOutNowButton: 'Log Out Now',

      // error message if https://github.com/marcuswestin/store.js not enabled
      errorAlertMessage: 'Please disable "Private Mode", or upgrade to a modern browser. Or perhaps a dependent file missing. Please see: https://github.com/marcuswestin/store.js',

      // server-side session keep-alive timer
      sessionKeepAliveTimer: 600,   // ping the server at this interval in seconds. 600 = 10 Minutes. Set to false to disable pings
      sessionKeepAliveUrl: window.location.href // set URL to ping - does not apply if sessionKeepAliveTimer: false
    },

    //##############################
    //## Private Variables
    //##############################
      currentConfig = $.extend(defaultConfig, userRuntimeConfig), // merge default and user runtime configuration
      origTitle = document.title, // save original browser title
      storeConfiguration, // public function support, store private configuration variables
      activityDetector,
      startKeepSessionAlive, stopKeepSessionAlive, keepSession, keepAlivePing, // session keep alive
      idleTimer, remainingTimer, checkIdleTimeout, checkIdleTimeoutLoop, startIdleTimer, stopIdleTimer, // idle timer
      openWarningDialog, dialogTimer, checkDialogTimeout, startDialogTimer, stopDialogTimer, isDialogOpen, destroyWarningDialog, countdownDisplay, // warning dialog
      logoutUser,
      checkForIframes, includeIframes, attachEventIframe; // iframes

    //##############################
    //## Public Functions
    //##############################
    // trigger a manual user logout
    // use this code snippet on your site's Logout button: $.fn.idleTimeout().logout();
    this.logout = function () {
      console.log('start logout');
      store.set('idleTimerLoggedOut', true);
    };

    // trigger a recheck for iframes
    // use this code snippet after an iframe is inserted into the document: $.fn.idleTimeout().iframeRecheck()
    this.iframeRecheck = function () {
      console.log('start iframeRecheck');
      checkForIframes();
    };

    //##############################
    //## Private Functions
    //##############################
    //----------- PUBLIC FUNCTION SUPPORT --------------//
    // WORK AROUND - save currentConfig.activityEvents value to 'store.js' variable for use in function: attachEventIframe
    storeConfiguration = function () {
      console.log('store configuration - currentConfig.activityEvents ' + currentConfig.activityEvents + '.');
      store.set('activityEvents', currentConfig.activityEvents);
    };

    //----------- KEEP SESSION ALIVE FUNCTIONS --------------//
    startKeepSessionAlive = function () {
      console.log('start startKeepSessionAlive');

      keepSession = function () {
        console.log('startKeepSessionAlive - send ping to sessionKeepAliveUrl');
        $.get(currentConfig.sessionKeepAliveUrl);
        startKeepSessionAlive();
      };

      keepAlivePing = setTimeout(keepSession, (currentConfig.sessionKeepAliveTimer * 1000));
    };

    stopKeepSessionAlive = function () {
      console.log('stop keep session alive function');
      clearTimeout(keepAlivePing);
    };

    //----------- ACTIVITY DETECTION FUNCTION --------------//
    activityDetector = function () {

      $('body').on(currentConfig.activityEvents, function () {

        if (!currentConfig.enableDialog || (currentConfig.enableDialog && isDialogOpen() !== true)) {
          console.log('activity detected');
          startIdleTimer();
        } else {
          console.log('warning dialog open. activity ignored');
        }
      });
    };

    //----------- IDLE TIMER FUNCTIONS --------------//
    checkIdleTimeout = function () {

      var timeIdleTimeout = (store.get('idleTimerLastActivity') + (currentConfig.idleTimeLimit * 1000));

      if ($.now() > timeIdleTimeout) {
        console.log('inactivity has exceeded the idleTimeLimit');

        if (!currentConfig.enableDialog) { // warning dialog is disabled
          console.log('warning dialog disabled - log out user without warning');
          logoutUser(); // immediately log out user when user is idle for idleTimeLimit
        } else if (currentConfig.enableDialog && isDialogOpen() !== true) {
          console.log('warning dialog is not open & will be opened');
          openWarningDialog();
          startDialogTimer(); // start timing the warning dialog
        }
      } else if (store.get('idleTimerLoggedOut') === true) { //a 'manual' user logout?
        console.log('user has manually logged out? Log out all windows & tabs now.');
        logoutUser();
      } else {
        console.log('inactivity has not yet exceeded the idleTimeLimit');

        if (currentConfig.enableDialog && isDialogOpen() === true) {
          console.log('warning dialog is open & will be closed');
          destroyWarningDialog();
          stopDialogTimer();
        }
      }
    };

    startIdleTimer = function () {
      console.log('start startIdleTimer');
      stopIdleTimer();
      store.set('idleTimerLastActivity', $.now());
      checkIdleTimeoutLoop();
    };

    // continually check if user inactivity has exceeded the idleTimeLimit
    checkIdleTimeoutLoop = function () {
      checkIdleTimeout();
      idleTimer = setTimeout(checkIdleTimeoutLoop, (currentConfig.idleCheckHeartbeat * 1000));
    };

    stopIdleTimer = function () {
      console.log('start stopIdleTimer');
      clearTimeout(idleTimer);
    };

    //----------- WARNING DIALOG FUNCTIONS --------------//
    openWarningDialog = function () {
      console.log('start openWarningDialog');

      var dialogContent = "<div id='idletimer_warning_dialog'><p>" + currentConfig.dialogText + "</p><p style='display:inline'>" + currentConfig.dialogTimeRemaining + ": <div style='display:inline' id='countdownDisplay'></div></p></div>";

      $(dialogContent).dialog({
        buttons: [{
          text: currentConfig.dialogStayLoggedInButton,
          click: function () {
            console.log('Stay Logged In button clicked');
            destroyWarningDialog();
            stopDialogTimer();
            startIdleTimer();
          }
        },
          {
            text: currentConfig.dialogLogOutNowButton,
            click: function () {
              console.log('Log Out Now button clicked');
              logoutUser();
            }
          }
          ],
        closeOnEscape: false,
        modal: true,
        title: currentConfig.dialogTitle,
        open: function () {
          //hide the dialog's upper right corner "x" close button
          $(this).closest('.ui-dialog').find('.ui-dialog-titlebar-close').hide();
        }
      });

      // start the countdown display
      countdownDisplay();

      // change title bar to warning message
      document.title = currentConfig.dialogTitle;

      // if keep-alive is enabled, stop the session keep-alive ping
      if (currentConfig.sessionKeepAliveTimer) {
        stopKeepSessionAlive();
      }
    };

    checkDialogTimeout = function () {
      var timeDialogTimeout = (store.get('idleTimerLastActivity') + (currentConfig.idleTimeLimit * 1000) + (currentConfig.dialogDisplayLimit * 1000));

      if (($.now() > timeDialogTimeout) || (store.get('idleTimerLoggedOut') === true)) {
        console.log('warning dialog is open and user has remained inactive for the dialogDisplayLimit. Time to log out user.');
        logoutUser();
      } else {
        console.log('dialog not yet timed out');
      }
    };

    startDialogTimer = function () {
      console.log('start startDialogTimer');
      dialogTimer = setInterval(checkDialogTimeout, (currentConfig.idleCheckHeartbeat * 1000));
    };

    stopDialogTimer = function () {
      clearInterval(dialogTimer);
      clearInterval(remainingTimer);
    };

    isDialogOpen = function () {
      var dialogOpen = $("#idletimer_warning_dialog").is(":visible");

      if (dialogOpen === true) {
        return true;
      }
      return false;
    };

    destroyWarningDialog = function () {
      console.log('start destroyWarningDialog');
      $("#idletimer_warning_dialog").dialog('destroy').remove();
      document.title = origTitle;

      // if keep-alive is enabled, restart the session keep-alive ping 
      if (currentConfig.sessionKeepAliveTimer) {
        startKeepSessionAlive();
      }
    };

    // display remaining time on warning dialog
    countdownDisplay = function () {
      var dialogDisplaySeconds = currentConfig.dialogDisplayLimit, mins, secs;

      remainingTimer = setInterval(function () {
        mins = Math.floor(dialogDisplaySeconds / 60); // minutes
        if (mins < 10) { mins = '0' + mins; }
        secs = dialogDisplaySeconds - (mins * 60); // seconds
        if (secs < 10) { secs = '0' + secs; }
        $('#countdownDisplay').html(mins + ':' + secs);
        dialogDisplaySeconds -= 1;
      }, 1000);
    };

    //----------- LOGOUT USER FUNCTION --------------//
    logoutUser = function () {
      console.log('start logoutUser');
      store.set('idleTimerLoggedOut', true);

      if (currentConfig.sessionKeepAliveTimer) {
        stopKeepSessionAlive();
      }

      if (currentConfig.customCallback) {
        console.log('logout function custom callback');
        currentConfig.customCallback();
      }

      if (currentConfig.redirectUrl) {
        console.log('logout function redirect to URL');
        window.location.href = currentConfig.redirectUrl;
      }
    };

    //----------- IFRAME FUNCTIONS --------------//
    // triggered when a dialog is opened, recheck for iframes
    $("body").on("dialogopen", function () {
      console.log('start body dialogopen');

      if (currentConfig.enableDialog && isDialogOpen() !== true) {
        console.log('a dialog (not the idleTimeout warning dialog) opened. Recheck for iframes.');
        checkForIframes();
      } else {
        console.log('warning dialog opened. No recheck for iframes.');
      }
    });

    // document must be in readyState 'complete' before checking for iframes - $(document).ready() is not good enough!
    checkForIframes = function () {
      console.log('start checkForIframes');

      var docReadyCheck, isDocReady;

      docReadyCheck = function () {
        if (document.readyState === "complete") {
          console.log('check for iframes, now that the document is complete');
          clearInterval(isDocReady);
          includeIframes();
        }
      };

      isDocReady = setInterval(docReadyCheck, 1000); // check once a second to see if document is complete
    };

    // find and include iframes
    includeIframes = function (elementContents) {
      console.log('start includeIframes');

      if (!elementContents) {
        console.log('elementContents not defined. Define as $(document)');
        elementContents = $(document);
      }

      var iframeCount = 0;

      elementContents.find('iframe,frame').each(function () {
        console.log('start of find iframes');

        if ($(this).hasClass('jit-inspected') === false) {

          console.log('first time inpection of iframe - no jit-inspected class');

          try {

            includeIframes($(this).contents()); // recursive call to include nested iframes

            // attach event code for most modern browsers
            $(this).on('load', attachEventIframe($(this))); // Browser NOT IE < 11

            // attach event code for older Internet Explorer browsers
            console.log('iframeCount: ' + iframeCount + '.');
            var domElement = $(this)[iframeCount]; // convert jquery object to dom element

            if (domElement.attachEvent) { // Older IE Browser < 11
              console.log('attach event to iframe. Browser IE < 11');
              domElement.attachEvent('onload', attachEventIframe($(this)));
            }

            iframeCount++;

          } catch (err) {
            /* Unfortunately, if the 'includeIframes' function is manually triggered multiple times in quick succession,
             * this 'try/catch' block may not have time to complete,
             * and so it might error out, 
             * and iframes that are NOT cross-site may be set to "cross-site"
             * and activity may or may not bubble to parent page.
             * Fortunately, this is a rare occurrence!
             */
            console.log('found cross-site iframe - add classes "jit-inspected" & "cross-site"');
            $(this).addClass('jit-inspected cross-site');
          }

        } else {
          console.log('iframe already has class jit-inspected');
        }

      });

    };

    // attach events to each iframe
    attachEventIframe = function (iframeItem) {
      console.log('start attachEventIframe');

      // retrieve stored value as currentConfig will not include private userRuntimeConfig
      // when this function is called by public function, iframeRecheck
      console.log('currentConfig.activityEvents: ' + currentConfig.activityEvents + '.');
      var iframeContents = iframeItem.contents(), storeActivityEvents = store.get('activityEvents');

      try {

        iframeContents.on(storeActivityEvents, function (event) {
          console.log('bubbling iframe activity event to body of page event: ' + event.type + '.');
          $('body').trigger(event);
        });

        iframeItem.addClass('jit-inspected'); // add "jit-inspected" class, so we don't need to check this iframe again

      } catch (err) {
        console.log('problem with attachment of activity events to this iframe');
      }

    };

    //###############################
    // Build & Return the instance of the item as a plugin
    // This is your construct.
    //###############################
    return this.each(function () {

      if (store.enabled) {

        store.set('idleTimerLastActivity', $.now());
        store.set('idleTimerLoggedOut', false);

        storeConfiguration();

        activityDetector();

        if (currentConfig.sessionKeepAliveTimer) {
          startKeepSessionAlive();
        }

        startIdleTimer();

        checkForIframes();

      } else {
        console.log('store.js not enabled, or browser "private mode"?');
        alert(currentConfig.errorAlertMessage);
      }

    });
  };
}(jQuery));