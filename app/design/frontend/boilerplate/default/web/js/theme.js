/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/ie-class-fixer',
    'domReady!'
], function ($, keyboardHandler) {
    'use strict';

    if ($('body').hasClass('checkout-cart-index')) {
        if ($('#co-shipping-method-form .fieldset.rates').length > 0 && $('#co-shipping-method-form .fieldset.rates :checked').length === 0) {
            $('#block-shipping').on('collapsiblecreate', function () {
                $('#block-shipping').collapsible('forceActivate');
            });
        }
    }

    $('.cart-summary').mage('sticky', {
        container: '#maincontent'
    });

    $('.panel.header > .header.links').clone().appendTo('#store\\.links');

    keyboardHandler.apply();
    $('.dropdown-toggle').on("click", function(){
        if($(this).hasClass("open-drop")){
            $(this).removeClass('open-drop');
            $('.dropdown-menu').hide();
        } else {
            $(this).addClass('open-drop');
            $(this).next('.dropdown-menu').show();
        }
    });
    if(window.innerWidth <= 768){
        $('nav.navigation').on("click", "a", function(){
            if($(this).hasClass("ui-state-active")){
                window.location.href = $(this).attr("href");
            } else if($(this).parent().hasClass("parent")) {
                $(this).parent().find('.submenu').show();
                $(this).addClass('ui-state-active');
            }
        });
        $('.filter-content').find('dd.filter-options-content').slideUp();
        $('.filter-content').on("click", 'dt', function(){
            if($(this).hasClass('open-filter')){
                $(this).removeClass('open-filter').next('dd').stop().slideUp(300);
            } else {
                $(this).parents('.filter-content').find('.open-filter').removeClass('open-filter').next('dd').stop().slideUp(300)
                $(this).addClass('open-filter').next('dd').stop().slideDown(300);
            }
        });
    } else {
        $('.filter-content').find("dd.filter-options-content").slideDown();
    }

});
