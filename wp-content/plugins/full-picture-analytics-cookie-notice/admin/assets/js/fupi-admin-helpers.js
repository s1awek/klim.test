let FP = {};

(function(FP){

    'use strict';

    let d = document,
	    w = window;

    FP.v = {}; // for variables
    FP.f = {}; // for single functions
    FP.v.viewport = {};
    FP.m = {}; // for modules and extensions

    FP.updateViewportDimensions = function () {
        var e = d.documentElement,
        g = d.getElementsByTagName('body')[0],
        x = w.innerWidth || e.clientWidth || g.clientWidth,
        y = w.innerHeight || e.clientHeight || g.clientHeight;
        FP.v.viewport = {
            width: x,
            height: y
        };
    };

    FP.getUrlParamByName = function (name) {
        // var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
        // return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
        const queryString = window.location.search,
			urlParams = new URLSearchParams(queryString);
		return urlParams.get(name);
    };

    FP.getSiblings = function(e){

        // for collecting siblings
        let siblings = [];

        // if no parent, return no sibling
        if (!e.parentNode) return siblings;

        // first child of the parent node
        let sibling  = e.parentNode.firstChild;

        // collecting siblings
        while (sibling) {
            if (sibling.nodeType === 1 && sibling !== e) {
                siblings.push(sibling);
            }
            sibling = sibling.nextSibling;
        }
        return siblings;
    }

    // ASYNC GET SCRIPT
    FP.getScript = function(src, func, id) {
        var script = d.createElement('script');
        script.async = "async";
        script.src = src;
        script.id = id;
        if (func) { script.onload = func; }
        d.getElementsByTagName("head")[0].appendChild( script );
    };

    // CONVERT NODE LIST TO ARRAY
    FP.nl2Arr = nl => nl ? [].slice.call(nl) : false;

    FP.remove = function(el){
        if(el){ el.parentNode.removeChild(el); }
    };

    FP.findAll = function(e, c){
        if ( c === null ) return [];
        c = c || document;
        return FP.nl2Arr(c.querySelectorAll(e));
    };

    FP.findFirst = function(e, c){
        if ( c === null ) return null;
        c = c || document;
        return c.querySelector(e);
    };

    FP.findID = function(e, c){
        if ( c === null ) return null;
        c = c || document;
        return c.getElementById(e);
    };

    FP.findTags = function(e, c){
        if ( c === null ) return null;
        c = c || document;
        return FP.nl2Arr(c.getElementsByTagName(e));
    };

    FP.setCookie = function (name,value,days) {
        if (days > 1) {
            var date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            var expires = "; expires="+date.toGMTString();
        } else {var expires = "";}
        d.cookie = name+"="+value+expires+"; path=/";
    };

    FP.readCookie = function (name) {
        var nameEQ = name + "=";
        var ca = d.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        };
        return null;
    };

    FP.deleteCookie = function (name) {
        FP.setCookie(name,"",-1);
    };

})(FP);
