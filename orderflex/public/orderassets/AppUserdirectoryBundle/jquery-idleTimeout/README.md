# jquery-idleTimeout

November 2021: Please note, I am no longer able to maintain this code. When I wrote the code, I used clear naming conventions and numerous comments to make it easy for others to follow and modify the code. The code, as it is at this point in time, is untested on the most recent browsers, and needs to have some bugs fixed. 

# IF YOU WANT TO TAKE OVER OWNERSHIP OF THIS REPOSITORY:
Please read https://docs.github.com/en/repositories/creating-and-managing-repositories/transferring-a-repository
and let's do it!

Highly configurable idle (no activity) timer and logout redirect for jQuery.

**Functions across multiple browser windows, tabs and, optionally, iframes (single or nested) as long as the iframes meet the '[Same-Origin Policy](http://en.wikipedia.org/wiki/Same-origin_policy)'.**

Listed on [NPM](https://www.npmjs.com/package/jquery-idletimeout) and [JQuery's Plugin site](http://plugins.jquery.com/idleTimeout/).

Requires [Marcus Westin's store.js](https://github.com/marcuswestin/store.js) which uses localStorage, globalStorage and userData behavior to 'communicate' across multiple browser windows & tabs without cookies or flash.

**[Basic Demo Page](http://jillelaine.github.io/jquery-idleTimeout/) and [Iframes Demo Page](http://jillelaine.github.io/jquery-idleTimeout/iframe-demo.html)**

**[Informative Wiki Pages!](https://github.com/JillElaine/jquery-idleTimeout/wiki)**

#### Communication Across Multiple Browser Windows, Tabs and Iframes

* Functions across multiple instances of a browser window and across multiple browser tabs within the same domain
* Use **jquery-idleTimeout-iframes.min.js** if detection of activity within 'same domain' iframes is desired

#### Required Dependencies

* JQuery core - version 1.7 or newer
* JQuery UI - version 1.9 or newer
* store.js - [https://github.com/marcuswestin/store.js](https://github.com/marcuswestin/store.js) - version 1.3.4 or newer

#### Functionality

* If a window or tab is logged out, all other windows and tabs will log out too
* If **warning dialog** pops up on a window or tab, a **warning dialog** appears on all other windows and tabs too
* If **'Stay Logged In'** button on **warning dialog** is clicked, warning dialogs on all other windows and tabs will be dismissed too
* If **'Log Out Now'** button on **warning dialog** is clicked, all other windows and tabs will log out too
* Public function for your site's **Logout** button so that all open windows and tabs will logout too
* Keep-alive pings server every 10 minutes (default) to prevent server-side session timeout
* All user message text may be modified to your desired language
* Use the optional configuration variable `customCallback` to add function(s) which execute just before user logout

**If the warning dialog box is enabled:**
* After the `idleTimeLimit` amount of user inactivity, the warning dialog box with 2 buttons appear. Default button may be activated with mouse click or press of Enter key.
* Warning dialog includes countdown 'Time remaining' display.
* Browser window and tab title bar(s) display warning message when warning dialog appears. Original browser title restored to all windows and tabs if warning dialog is dismissed.
* Warning dialog will display for the configured `dialogDisplayLimit` amount of time. If user remains idle, plugin will redirect to configured `redirectUrl`.

![Warning Dialog](https://raw.github.com/JillElaine/jquery-idleTimeout/master/warning_dialog.png)

**If the warning dialog box is disabled:**
* After the configured `idleTimeLimit` amount of user inactivity, idleTimeout script will redirect to configured `redirectUrl`.
* No warning dialog box will appear and browser window and tab title bar(s) do not display a warning.

#### How to Use

* Load the required jQuery dependencies on your website: jQuery core and jQuery UI
* Download [store.min.js](https://github.com/marcuswestin/store.js) and the appropriate minified jquery-idleTimeout script: jquery-idleTimeout.min.js or jquery-idleTimeout-iframes.min.js
* Upload these .js files and make them available to your website
* Call the jquery-idleTimeout script in a 'document ready', **`$(document).ready(function ()...`**, function somewhere on your site
* Configure the `redirectUrl` variable within the 'document ready' function to redirect to your site's logout page
* Use the public logout function on your site's 'Logout' button: **`$.fn.idleTimeout().logout()`**

For lots more information, please read the [Wiki](https://github.com/JillElaine/jquery-idleTimeout/wiki)

Detailed information on all the **[Public Configuration Variables](https://github.com/JillElaine/jquery-idleTimeout/wiki/Public-Configuration-Variables)**.

**[Example Usage Page](https://github.com/JillElaine/jquery-idleTimeout/blob/master/example.html)**

**Example Basic Document Ready Function**

```Javascript
  $(document).ready(function () {
    $(document).idleTimeout({
      redirectUrl:  '/logout' // redirect to this url. Set this value to YOUR site's logout page.
    });
  });
```

**Example Site Logout Button**

If user voluntarily logs out of your site with your 'Logout' button (instead of timing out), you can force all 'same domain' windows and tabs to log out too! Attach this small snippet of code, **`$.fn.idleTimeout().logout();`**, to the 'onclick' function of your site's 'Logout' button. See example below.

```
<input value="Logout" onclick="$.fn.idleTimeout().logout();" type="button" title="Logout ALL Windows & Tabs" />
```

#### Iframe Information

If you require activity detection within iframes, use the **jquery-idleTimeout-iframe.min.js** script. 

Please read the [Iframe Wiki Page](https://github.com/JillElaine/jquery-idleTimeout/wiki/Iframes---Information-&-Troubleshooting).

#### Troubleshooting

Please read the [Troubleshooting Wiki Page](https://github.com/JillElaine/jquery-idleTimeout/wiki/General-Troubleshooting)

##### Possible 'mousemove' bug with Chrome browser (on Windows?)

User g4g4r1n reports 'mousemove' event sometimes fires when mouse is not moving on Chrome browser and offers a [possible solution](https://github.com/JillElaine/jquery-idleTimeout/issues/13).

##### Your suggestions and bug reports are appreciated

Use **jquery-idleTimeout-for-testing.js** with Firefox with Firebug add-on or similar for debugging. Your feedback helps to improve this plugin!
