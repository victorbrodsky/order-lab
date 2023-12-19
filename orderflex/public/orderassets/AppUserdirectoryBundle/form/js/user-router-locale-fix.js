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

//Routing.generate and _locale fix, proposed in:
//https://stackoverflow.com/questions/25842418/symfony-fos-js-routing-and-problems-with-locale/35223108#35223108

//var mylocale= "{{ app.request.attributes.get('_locale') }}";
//console.log('mylocale='+mylocale);


$(function () {
    //var REQUEST_LOCALE2 = '{{ app.request.locale }}';
    //console.log('REQUEST_LOCALE2='+REQUEST_LOCALE2);
    console.log('function _REQUEST_LOCALE='+_REQUEST_LOCALE);
    // change name of initial method
    Routing.generateImpl = Routing.generate;
    // override generate fonction by adding a default _locale from request locale
    Routing.generate = function (url, params) {
        var paramsExt = {};
        if (params) {
            paramsExt = params;
        }
        if (!paramsExt._locale){
            //paramsExt._locale = '{{ app.request.locale }}';
            paramsExt._locale = _REQUEST_LOCALE;
        }
        return Routing.generateImpl(url, paramsExt);
    }
})

