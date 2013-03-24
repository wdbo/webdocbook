/* Scripts for demo */

// Avoid `console` errors in browsers that lack a console.
// https://github.com/h5bp/html5-boilerplate
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

function initTables()
{
    $('table').addClass('table table-striped table-hover');
}

function initHighlighted( sel )
{
    $(sel).highlight({indent:'tabs', code_lang: 'data-language'});
}

function initTablesorter( sel, opts )
{
    $(sel).find('th').each(function(i,v){
        $(this).attr('title', 'Sort entries by this column value');
    });
    $(sel).addClass('tablesorter').tablesorter(opts || null);
}

function initInpageNavigation()
{
    $('section h1, section h2,section h3,section h4,section h5,section h6').each(function(i,el){
        var _id = $(this).attr('id');
        if (!_id) {
            _id = uniqid();
            $(this).attr('id', _id);
        }
        var inpage_menu = $('ul#inpage_menu'),
            _li = $('<li>'),
            _a = $('<a>', {'href':'#'+_id}).html( $(this).html() );
        inpage_menu.append( _li.append(_a) );
        $('[data-spy="scroll"]').each(function(){ $(this).scrollspy('refresh') });
    });
}
