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

/**
 * Created by oli2002 on 9/3/14.
 */

//import { setCicleShow } from '/public/orderassets/AppUserdirectoryBundle/form/js/user-common.js';

//import '/public/orderassets/AppUserdirectoryBundle/form/js/user-common.js';
//Window.prototype.setCicleShow = setCicleShow;

// import {
//     setCicleShow,
//     getSitename
// }from '/public/orderassets/AppUserdirectoryBundle/form/js/user-common.js';

//Window.prototype.setCicleShow = setCicleShow;
//Window.prototype.getSitename  = getSitename;
//Window.prototype.fieldInputMask = fieldInputMask;

//TODO: correct, new way to replace functions by class with functions

var _cycleShow = false;
var _sitename = "";
var asyncflag = true;
var combobox_width = '100%'; //'element'

var urlBase = $("#baseurl").val();
var cycle = $("#formcycle").val();
var user_name = $("#user_name").val();
var user_id = $("#user_id").val();
var _authuser_id = $("#authuser_id").val();

if( !user_id ) {
    user_id = 'undefined';
}

export class UserCommon {

    setCicleShow() {
        //var cycle = "";
        console.log("setCicleShow: cycle="+cycle);
        //var _cycleShow = false;
        //console.log("setCicleShow: cycle.indexOf="+cycle.indexOf("show"));
        if( cycle && (cycle.indexOf("show") != -1 || cycle.indexOf("review") != -1) ) {
            _cycleShow = true;
            //console.log("setCicleShow: true");
        } else {
            _cycleShow = false;
            //console.log("setCicleShow: false");
        }
        return _cycleShow;
    }

}
//
// //const UserCommon = new UserCommon();
// console.log("cycle="+UserCommon.setCicleShow());

//Test
// class Car {
//     constructor(name, year) {
//         this.name = name;
//         this.year = year;
//     }
//
//     setCicleShow() {
//         var cycle = "";
//         console.log("setCicleShow: cycle="+cycle);
//         var _cycleShow = false;
//         //console.log("setCicleShow: cycle.indexOf="+cycle.indexOf("show"));
//         if( cycle && (cycle.indexOf("show") != -1 || cycle.indexOf("review") != -1) ) {
//             _cycleShow = true;
//             //console.log("setCicleShow: true");
//         } else {
//             _cycleShow = false;
//             //console.log("setCicleShow: false");
//         }
//         return _cycleShow;
//     }
// }
// const myCar = new Car("Ford", 2014);
// console.log(myCar.name + " " + myCar.year);
// console.log("cycle====="+myCar.setCicleShow());

