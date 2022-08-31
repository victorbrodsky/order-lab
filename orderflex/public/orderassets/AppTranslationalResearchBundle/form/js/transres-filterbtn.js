
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

    toggleBtnListener();

});

function changeFilterColorButton() {
    var filterBtn = $('#filter-btn');
    var filterForm = filterBtn.closest('.form-search');

    //datepicker
    filterForm.find('.datepicker-only-year').on('change',function() {
        changeBtnColorGreen(filterBtn);
    });
    filterForm.find('.datepicker').on('change',function() {
        changeBtnColorGreen(filterBtn);
    });

    //combobox
    filterForm.find('.combobox').on('change',function() {
        changeBtnColorGreen(filterBtn);
    });

    //select
    filterForm.find('select').on('change',function() {
        changeBtnColorGreen(filterBtn);
    });

    //text
    filterForm.find('text').on('input',function() {
        changeBtnColorGreen(filterBtn);
    });

    //input
    filterForm.find('input').on('input',function() {
        changeBtnColorGreen(filterBtn);
    });

    function changeBtnColorGreen(filterBtn) {
        filterBtn.removeClass('btn-default');
        filterBtn.addClass('btn-success');
    }

}

function toggleBtnListener() {
    //$('.toggle-btn-state').click(function(e) {
        //$(this).toggleClass('toggle-btn-state-active');
        // if( $(this).hasClass('toggle-btn-state-active') ) {
        //     $(this).removeClass('toggle-btn-state-active').addClass('toggle-btn-state-inactive');
        // }
        // if( $(this).hasClass('toggle-btn-state-inactive') ) {
        //     $(this).removeClass('toggle-btn-state-inactive').addClass('toggle-btn-state-active');
        // }
        //
        // e.preventDefault();
    //});

    //Default: if #transres-AdvancedSearch has "collapse in" => grey
    if( $("#transres-AdvancedSearch").hasClass("in") ) {
        // $('.toggle-btn-state').toggleClass('toggle-btn-state-active');
        $('.toggle-btn-state').toggleClass('active');
    }
}

