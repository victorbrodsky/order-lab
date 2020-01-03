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
 * Created with JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/9/14
 * Time: 4:58 PM
 * To change this template use File | Settings | File Templates.
 */

$(document).ready(function() {


    if( $('.select2-list-type').select2('val') == "default" ) {
        $(".select2-list-original").select2("readonly", true);
        $('.select2-list-original').select2('val',null);
    }

    $('.select2-list-type').on("change", function(e) {

        //console.log("type change listener, val="+$('.select2-list-type').select2('val'));
        if( $('.select2-list-type').select2('val') == "default" ) {
            //console.log("default");
            $(".select2-list-original").select2("readonly", true);
            $('.select2-list-original').select2('val',null);
        } else {
            $(".select2-list-original").select2("readonly", false);
        }

    });

});
