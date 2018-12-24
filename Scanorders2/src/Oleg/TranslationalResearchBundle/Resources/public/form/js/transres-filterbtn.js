
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

$(document).ready(function() {

    //console.log('transres-filterbtn.js');
    changeFilterColorButton();

});

function changeFilterColorButton() {
    var filterBtn = $('#filter-btn');
    var filterForm = filterBtn.closest('.form-search');

    //datepicker
    filterForm.find('.datepicker-only-year').on('change',function() {
        chnageBtnColorGreen(filterBtn);
    });
    filterForm.find('.datepicker').on('change',function() {
        chnageBtnColorGreen(filterBtn);
    });

    //combobox
    filterForm.find('.combobox').on('change',function() {
        chnageBtnColorGreen(filterBtn);
    });

    //select
    filterForm.find('select').on('change',function() {
        chnageBtnColorGreen(filterBtn);
    });

    //text
    filterForm.find('text').on('input',function() {
        chnageBtnColorGreen(filterBtn);
    });

    //input
    filterForm.find('input').on('input',function() {
        chnageBtnColorGreen(filterBtn);
    });

    function chnageBtnColorGreen(filterBtn) {
        filterBtn.removeClass('btn-default');
        filterBtn.addClass('btn-success');
    }

}

