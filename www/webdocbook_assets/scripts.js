/**
 * This file is part of the WebDocBook package.
 *
 * Copyleft (ↄ) 2008-2015 Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * The source code of this package is available online at
 * <http://github.com/wdbo/webdocbook>.
 */

// Avoid `console` errors in browsers that lack a console.
// http://github.com/h5bp/html5-boilerplate
(function() {
    var method;
    var noop = function () {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());

/**
 * Inspired by <http://www.creativejuiz.fr/blog/tutoriels/mise-jour-jquery-effet-smoothscroll-au-chargement-page>
 *
 * Usage: $('a').juizScrollTo('slow');
 */
(function($) {
    $.fn.juizScrollTo = function( speed ) {
        if ( !speed ) {
            var speed = 'slow';
        }

        return this.each( function() {
            $(this).on('click', function() {
                var goscroll = false;
                var the_hash = $(this).attr("href");
                var regex = new RegExp("(.*)\#(.*)","gi");
                var the_element = '';

                if (the_hash.match("\#(.+)")) {
                    the_hash = the_hash.replace(regex,"$2");
//                    the_hash = escapeSelector(the_hash);
                    if ($("#"+the_hash).length > 0) {
                        the_element = "#" + the_hash;
                        goscroll = true;
                    }  else if ($("a[name=" + the_hash + "]").length > 0) {
                        the_element = "a[name=" + the_hash + "]";
                        goscroll = true;
                    }
                    if ( goscroll ) {
                        $('html,body').animate( {
                            scrollTop: $(the_element).offset().top - 60
                        }, speed, function() {
                            tab_n_focus(the_hash)
                            write_hash(the_hash);
                        });
                        return false;
                    }
                }
            });
        });

        function write_hash( the_hash ) {
            document.location.hash =  "r:" + the_hash;
        }

        function tab_n_focus( the_hash ) {
            $(the_hash).attr('tabindex','0').focus().removeAttr('tabindex');
        }
    };

/*
// this was braking a hash on-load
    function trigger_click_for_slide() {
        var the_hash = document.location.hash;
        if (the_hash) {
            $('a[href~="'+the_hash+'"]').trigger('click');
        }
    }
    trigger_click_for_slide();
*/

})(jQuery);

// ---------------------------
// Utilities
// ---------------------------

function uniqid()
{
    return String(Math.floor(Math.random()*1000000))+String(Math.floor(Math.random()*1000000))+String(Math.floor(Math.random()*1000000));
}

function getUrlFilename( _url )
{
    var url = _url || document.location.href,
        filename, qm = url.lastIndexOf('?');
    if (qm!==-1) { filename = url.substr(0,qm); }
    else { filename = url; }
    return filename.substring(filename.lastIndexOf('/')+1);
}

function getUrlHash( _url )
{
    var url = _url || document.location.href,
        hash = '',
        sm = url.lastIndexOf('#');
    if (sm!==-1) { hash = url.substr(sm+1); }
    return hash;    
}

/*
var te = "abcdef!abcdef\abcdef\"abcdef#abcdef$abcdef%abcdef&abcdef'abcdef(abcdef)abcdef*abcdef+abcdef,abcdef.abcdef/abcdef:abcdef;abcdef<abcdef=abcdef>abcdef?abcdef@abcdef[abcdef\abcdef]abcdef^abcdef`abcdef{abcdef|abcdef}abcdef~abcdef";
console.debug(te);
var escapedTe = escapeSelector(te);
console.debug(escapedTe);
*/
function escapeSelector(str)
{
    var toEscape = "!\"#$%&'()\*+,.\/:;<=>?@[\\]^`{|}~";
    var reg = new RegExp('['+toEscape+']|gw|kw', 'g')
    var escapedStr = str.replace(reg, function(s){ return '\\\\'+s; });
    return escapedStr;
}
 
function updateClass(_el, _class, _class_toRemove)
{
    if (_class_toRemove!==undefined && _class_toRemove!==null) {
        $(_el).removeClass(_class_toRemove);
    }
    if (_class!==undefined && _class!==null) {
        $(_el).addClass(_class);
    }
}

// ---------------------------
// Elements creations
// ---------------------------

function getNewLi( str )
{
    return $('<li />').html(str);
}

function getNewA( href, str )
{
    return $('<a />', {'href':href}).html(str);
}

function getNewDt( str )
{
    return $('<dt />').html(str);
}

function getNewDd( str )
{
    return $('<dd />').html(str);
}

function getNewInfoItem( str, title, href )
{
    var strong = $('<strong />').html( href!==undefined ? getNewA(href, str) : str );
    return getNewLi( title!==undefined ? title+' : ' : '' ).append( strong );
}

function getNewDefinitionItem( str, title, href )
{
    var ghost = $('<dl />');
    ghost.append( getNewDt( title!==undefined ? title : '' ) );
    ghost.append( getNewDd( href!==undefined ? getNewA(href, str) : str ) );
    return ghost.html();
}

// ---------------------------
// Page tools
// ---------------------------

function initNavbar()
{
    var elt_navbar = $('#header-navigation'),
        elt_container = $('#wrapper .container:first'),
        gap = 40;
    $(elt_container).css('margin-top', ($(elt_navbar).innerHeight()-gap)+'px');
    $( window ).resize(function() {
        $(elt_container).css('margin-top', $(elt_navbar).innerHeight()+'px');
    });
}

function initHandler( _name )
{
    var elt_handler = $('#'+_name+'_handler'),
        elt_block = $('#'+_name);
    elt_block.hide();
    elt_handler.click(function(){ 
        var tltp = elt_handler.accesskey ? elt_handler.accesskey('getTooltip') : false;
        if (tltp && elt_block.is(':visible')) { tltp.hide(); }
        elt_block.toggle('slow');
        elt_handler.toggleClass('down');
    });
}

function initCollapseHandler( _name )
{
    var elt_handler = $('#'+_name+'_handler'),
        elt_collapse = $('#'+_name+'_collapse'),
        elt_block = $('#'+_name);
    elt_collapse.collapse();
    elt_handler.click(function(){ 
        elt_collapse.collapse('toggle');
        return false;
    });
}

function activateMenuItem()
{
    var page = getUrlFilename( window.location.href );
    $('nav li').each(function(i,o){
        var atag = $(o).find('a').first();
        if (atag && atag.attr('href')===page) { atag.closest('li').addClass('active'); }
    });
}

// soft scroll on arrival
function getToHash()
{
    var _hash = window.location.hash;
    if (_hash!==undefined) {
        var hash = $('#'+_hash.replace('#', ''));
        if (hash.length) {
            var poz = hash.position();
            $("html:not(:animated),body:not(:animated)").animate({ scrollTop: poz.top });
        }
    }
}

// 
function updateBacklinks()
{
    $('#short_menu').html( $('#navigation_menu').html() );
}
function initBacklinks()
{
    $('#short_navigation').hide();
    $('#short_menu').hide();
    $('#short_menu_handler').bind('mouseover', function(){
        var short_menu = $('#short_menu'),
            short_menu_ln = $('#short_menu').html().length;
        updateBacklinks();
        $('#short_menu').fadeIn('slow', function(){
            $('#short_navigation').bind('mouseleave', function(){ $('#short_menu').fadeOut('slow'); });
        });
    });
    $(window).scroll(function(){
        var nav = $('nav'),
            nav_poz = nav.position();
        if ((nav_poz.top+$('nav').height()) < $(window).scrollTop()) {
            $('#short_navigation').fadeIn('slow');
        } else {
            $('#short_navigation').fadeOut('slow');
        }
    });
}

function addCSSValidatorLink( css_filename )
{
    var url = window.location.href,
        cssfile = url.replace(/(.*)\/.*(\.(html|php)$)/i, '$1/'+css_filename);
    $('#footer a#css_validation').attr('href', 'http://jigsaw.w3.org/css-validator/validator?uri='+encodeURIComponent(cssfile));
}

function addHTMLValidatorLink( url )
{
    if (url===undefined || url===null) { var url = window.location.href; }
    $('#footer a#html_validation').attr('href', 'http://html5.validator.nu/?showimagereport=yes&showsource=yes&doc='+encodeURIComponent(url));
}

// TABLES

function initHighlighted( sel )
{
    $(sel).highlight({indent:'tabs', code_lang: 'data-language'});
}

function initTablesorter( sel, opts )
{
    $(sel).find('th').each(function(i,v){
        $(this).attr('title', JS_STRS.tablesorter_th);
    });
    $(sel).addClass('tablesorter').tablesorter(opts || null);
}

// PAGE TOOLS

// build dropdown page menu in header
function initInpageNavigation()
{
    var base_obj = 'section :not(#page_menu)',
        h_sel = base_obj+' h2,'+base_obj+' h3,'+base_obj+' h4,'+base_obj+' h5,'+base_obj+' h6',
        inpage_menu = $('ul#inpage_menu'),
        inpage_menu_toggler = $('#inpage_menu_toggler');
    if ($(base_obj+' h1').length>1) {
        h_sel = base_obj+' h1,'+h_sel;
    }
    var elements = $(h_sel);
    if (elements.length==0) {
        inpage_menu_toggler.hide();
        return;
    }
    elements.each(function(i,el){
        var _id = $(this).attr('id');
        if (!_id) {
            _id = uniqid();
            $(this).attr('id', _id);
        }
        var a_ctt = $(this).text(),
            _li = $('<li>'),
            _a = $('<a>', {'href':'#'+_id});
        switch ($(this)[0].tagName) {
            case 'H1': a_ctt = a_ctt; break;
            case 'H2': a_ctt = '&nbsp;&nbsp;# '+a_ctt; break;
            case 'H3': a_ctt = '&nbsp;&nbsp;&nbsp;&nbsp;## '+a_ctt; break;
            case 'H4': a_ctt = '&nbsp;&nbsp;&nbsp;&nbsp;### '+a_ctt; break;
            case 'H5': a_ctt = '&nbsp;&nbsp;&nbsp;&nbsp;###- '+a_ctt; break;
            case 'H6': a_ctt = '&nbsp;&nbsp;&nbsp;&nbsp;###-- '+a_ctt; break;
        }
        _a.html( a_ctt );
        inpage_menu.append( _li.append(_a) );
        $('[data-spy="scroll"]').each(function(){
            var $spy = $(this).scrollspy('refresh');
        });
    });
}

// switch size of search field
function initSearchField()
{
    var _sel = $('.searchField'),
        _origin = $(_sel).width();
    _sel.bind('focus', function(){ $(this).stop().animate({width: '100%'}); })
        .bind('blur', function(){ $(this).stop().animate({width: /*_origin*/'75px'}); });
}

// soft scrolling
function initScrollTo()
{
    $.browser = {};
    $.browser.chrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    $.browser.safari = /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor);
    $.browser.msie = /MSIE/.test(navigator.userAgent);
    $.browser.mozilla = /Firefox/.test(navigator.userAgent);
    $('a[href^="#"]').not(".noscrollto").each(function(i,el){
        $(this).juizScrollTo('slow');
    });
}

// MODAL BOX

// ajax loader must return a json with: body,footer,title
function messagebox(url, title)
{
    var msgb = $('#messagebox');
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json'
    }).done(function(data, textStatus, jqXHR) {
        if (data.body) {
            msgb.find('.modal-body').html(data.body);
        }
        if (data.footer) {
            msgb.find('.modal-footer').html(data.footer);
        }
        if (data.title) {
            msgb.find('.modal-header h4').html(data.title);
        }
        msgb.modal('show');
    });

}

// TRANSLATE SELECTOR SWITCHER
function initTranslator()
{
    $('#language-selector').hide();
    $('#language-info').show();
    return false;
}
function showTranslator()
{
    $('#language-info').hide();
    $('#language-selector').show();

    $('body').on("click", function(event) {
        if (
            event.target.id !== 'language-selector' &&
            event.target.id !== 'language-info' &&
            $(event.target).parents().index($('#language-selector')) == -1 &&
            $(event.target).parents().index($('#language-info')) == -1
        ){
            initTranslator();
            $(this).off(event);
        }
    });

    return false;
}
