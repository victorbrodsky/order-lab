
/*
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

function isIE () {
    var myNav = navigator.userAgent.toLowerCase();
    return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
}

window.onerror = function( msg, url, linenumber ){

    if( isIE() ) {
        //
    } else {

        var newline = "\n";

        var siteEmail = $("#siteEmail").val();
        if( !siteEmail && siteEmail.length == 0 ) {
            siteEmail = "system admin email";
        }

        alert(  'Internal system error. Please reload the page by clicking "OK" button. ' + newline +
                'Please e-mail us at '+siteEmail+' if the problem persists. ' + newline +
                'Error message: ' + msg + newline +
                ' URL: ' + url + newline +
                ' Line Number: ' + linenumber
        );

        //location.reload();
    }

}
