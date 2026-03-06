function fupi_top_helpers(){

    'use strict';

    var d = document;

    // VARS

    FP.v = {};
    fp.track_queue = [];
    fpdata.activity = { total : 0, last : 0, current : 0 };
    fpdata.refreshed = false;
    
    if ( document.referrer == document.location.origin + document.location.pathname ) {
        fpdata.refreshed = true;
    } else {
        if ( performance.navigation ? performance.navigation.type === performance.navigation.TYPE_RELOAD : performance.getEntriesByType('navigation')[0].type === 'reload' ) fpdata.refreshed = true; // deprecated method first, the new one later
    }

    // HELPERS

    function getFormattedDateTime(){
        const d = new Date();
        
        const year = d.getFullYear();
        const month = d.getMonth() + 1;
        const day = d.getDate();
        const hours = d.getHours() * 100;
        const minutes = d.getMinutes();
        
        return {
            year: year,
            month: month,
            day: day,
            time: hours + minutes
        };
    }

    fpdata.date = getFormattedDateTime();

    function check_if_can_save_wpfp_cookies( first_check = false ) {

        // save previous state
        let prev_check = !! fp.vars.can_save_wpfp_cookies; // "!!" changes undefined into false
        
        // get current state
        fp.vars.can_save_wpfp_cookies = ! fp.notice.enabled || fp.notice.mode == 'notify' || fp.notice.mode == 'hide' || ( fp.notice.mode == 'optout' && ! fpdata.cookies ) || ( fp.notice.mode == 'optin' && fpdata.cookies && fpdata.cookies.stats );

        // check if the state has changed
        fp.vars.consents_changed = first_check ? false : prev_check != fp.vars.can_save_wpfp_cookies;        
    }

    function shouldCustomScriptLoadHere( geo ){

        let geo_method = geo[0],
            geo_countries = geo[1];

        if ( fp.main.geo && geo_method && geo_countries ) { // we check fp.geo to prevent situations when user disabled geo but didn't save new tools settings
        
            if ( fp.ready ) {
                return geo_method == 'incl' ? geo_countries.includes( fpdata.country ) : ! geo_countries.includes( fpdata.country );
            }
            return false;
        }

        return true;
    }

    function shouldScriptLoadHere( id ){

        if ( fp.main.geo && fp[id].limit_country ) { // we check fp.main.geo to prevent situations when user disabled geo but didn't save new tools settings
            
            if ( fp.ready ) {

                if ( fp[id].limit_country.method == 'excl' ) {
                    return ! fp[id].limit_country.countries.includes( fpdata.country );
                } else {
                    return fp[id].limit_country.countries.includes( fpdata.country );
                }
            }

            return false;
        }

        return true;
    };

    function get_session_data_from_cookies(){

        let session_cookie = FP.readCookie('fp_current_session');
        if ( session_cookie ) fpdata.current_session = JSON.parse(session_cookie);

        
    }

    //
    // FP - start
    //

    FP.setCookie = (name, value, days = 0, mins = 0, path = "/") => {
		var expires = "";
		if ( days > 0 || mins > 0 ) {
			var date = new Date();
			if (days > 0) {
				date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
			} else {
				date.setTime(date.getTime() + mins * 60 * 1000);
			}
			expires = "; expires=" + date.toGMTString();
		};
		d.cookie = name + "=" + value + expires + "; path=" + path + "; sameSite=strict";
	};

    FP.readCookie = name => {
		var nameEQ = name + "=";
		var ca = d.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') {c = c.substring(1, c.length);}
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
		};
		return null;
	};

    FP.deleteCookie = name => { FP.setCookie(name, "", -1); };

    FP.getUrlParamByName = ( name, url = false ) => {
		if ( ! url ) {
            url = window.location.search;
        } else {
            url = url.split('?')[1];
			if ( ! url ) return null;
        };
		
		const queryString = url,
			urlParams = new URLSearchParams(queryString);
		return urlParams.get(name);
	};

    FP.nl2Arr = nl => nl ? [].slice.call(nl) : false;

    FP.findID = (e, c) => {
        if ( c === null ) return null;
        if ( !e ) return false;
        c = c || document;
        return c.getElementById(e);
    };

    FP.findFirst = (e, c) => {
        if ( c === null ) return null;
        if ( !e ) return false;
        c = c || document;
        return c.querySelector(e);
    };

    FP.findAll = (e, c) => {
		if ( c === null ) return [];
		if ( ! e ) return false;
		c = c || document;
		return FP.nl2Arr(c.querySelectorAll(e));
	};

    FP.getScript = ( url, cb, attrs, tag = 'script', fallback = false ) => {

		attrs = attrs || false;
		var new_tag = "";

		if ( tag == "img" ) {
			new_tag = d.createElement('img');
			new_tag.src = url;
		} else if ( tag == "link" ) {
			new_tag = d.createElement('link');
			new_tag.href = url;
		} else {
			new_tag = d.createElement('script')
			new_tag.src = url;
			new_tag.type = 'application/javascript';
		}

        if ( cb ) new_tag.onload = cb;

		if ( fallback ) new_tag.onerror = ()=>{ FP.getScript( fallback, cb, attrs, tag ); };

		if ( attrs ) {
            // check if attrs holds a reference to and HTML tag
            if ( attrs instanceof Element ) {
                let old_tag = attrs;
                // copy from the original tag (with exceptions)
                Array.from( old_tag.attributes ).forEach( attr => {
                    if ( attr.name !== 'type' && attr.name !== 'data-src' && ! attr.name.includes('fupi') ) {
                        new_tag.setAttribute( attr.name, attr.value );
                    }
                });
            // or add provided values
            } else {
                for (var key in attrs) {
                    if ( key !== "/" ) new_tag.setAttribute(key, attrs[key]);
                }
            }
		}

		d.getElementsByTagName("head")[0].appendChild(new_tag);
	};

    FP.createTag = FP.getScript;

    function loadInlineScript(temp_script) {
		
		let scr_id = temp_script.dataset?.fupi_id,
            orig_id = scr_id.replace('fp_',''),
            is_cscr = temp_script.dataset?.module == 'cscr';
        
        if ( ! scr_id ) return;

		let new_script = document.createElement('script');

		new_script.innerHTML = temp_script.innerHTML;
		temp_script.parentNode.replaceChild(new_script, temp_script);

		if ( fp.main.debug ) {
            if ( is_cscr && fp.cscr[orig_id] ) {
                console.log( "[FP] Custom script loaded: " + fp.cscr[orig_id] );
            } else {
                console.log( "[FP] Inline script loaded");
            }
        };

		fp.loaded.push( orig_id );
	};

    FP.sendEvt = (evt_name, details_a) => {
        var details = details_a ? { 'detail' : details_a } : {},
            fp_event = new CustomEvent( evt_name, details );
        document.dispatchEvent(fp_event);
    };

    FP.getRandomStr = ()=>{
		return ( Math.random() + 1 ).toString(36).substring(2);
	};

    FP.updateConsents = () => {

		if ( fp.vars.use_other_cmp ) return;
		
		if ( ! fp.main.track_current_user ) {

			if ( fp.main.debug ) console.log("[FP] Current user cannot be tracked");

		// if the user made a choice in the past
		} else if ( fpdata.cookies ){

			set_consents_in_fpdata( fpdata?.cookies?.stats, fpdata?.cookies?.personalisation, fpdata?.cookies?.marketing );

		// if no choice was made in the past
		} else {
			
			// deny all if consent banner is in optin mode
			if ( fp.notice.enabled && fp.notice.mode == "optin" ) {
				
				set_consents_in_fpdata();
			
			// agree to all if consent banner is disabled or we are in optout or notification mode
			} else {
				set_consents_in_fpdata(true, true, true);
			}
		}

		if ( ! fp.main.is_customizer ) {

			// set MS Ads consent
			
			if ( fp.main.track_current_user && fpdata.cookies ){
				if ( fpdata.cookies.stats && fpdata.cookies.marketing ) {
					window.uetq.push( "consent", "update", {
						"ad_storage": "granted"
					});
				}
			} else {
				if ( ! ( fp.notice.enabled && fp.notice.mode == "optin" ) ) {
					window.uetq.push( "consent", "update", {
						"ad_storage": "granted"
					});
				}
			}
			
			// Set GTAG consents

			["gtag", "fupi_gtm_gtag"].forEach( tag_name => {

				if ( tag_name == "fupi_gtm_gtag" && ! window.fupi_gtm_gtag ) return;

				// update if the user made a choice in the past
				if ( fp.main.track_current_user && fpdata.cookies ){

					set_gtag_consents( tag_name, "update", fpdata?.cookies?.stats, fpdata?.cookies?.personalisation, fpdata?.cookies?.marketing );
					if ( fp.main.debug ) console.log("[FP] Google consents set to user choices");
				
				// if no choice was made in the past
				} else {
					
					// agree to all if consent banner is disabled or is in optout or notification mode
					if ( ! ( fp.notice.enabled && fp.notice.mode == "optin" ) ) {

						set_gtag_consents( tag_name, "update", true, true, true );
						if ( fp.main.debug ) console.log("[FP] All Google consents granted");

					};
				}
			} );
		}
	}

	FP.postToServer = ( event_data_a, cb = false ) => {

		if ( fpdata.is_robot ) return;
		if ( fp.main.debug ) console.log( "[FP] Posting to server", event_data_a );

		let fetch_url = fp.main.server_method == "rest" ? "/index.php?rest_route=/fupi/v1/sender" : "/wp-admin/admin-ajax.php?action=fupi_ajax";

		if ( fp.main.debug || event_data_a[0][0] == 'cdb') {
		
			fetch( fetch_url, {
				method: "POST",
				body: JSON.stringify( event_data_a ),
				credentials: 'same-origin',
				headers: {
					"Content-type": "application/json; charset=UTF-8",
					// "X-WP-Nonce": fp_nonce
				}
			})
			.then((response) => response.json())
			.then((json) => {
				if ( cb ) { 
					cb(json);
				} else {
					console.log( "[FP] Server response", json);
				}
			});

		} else {

			fetch( fetch_url, {
				method: "POST",
				credentials: 'same-origin',
				body: JSON.stringify( event_data_a ),
				headers: {
					"Content-type": "application/json; charset=UTF-8",
					// "X-WP-Nonce": fp_nonce
				}
			});
		}
	};

    // fires function every X ms with an option to fire them ADDITIONALLY on start ("leading") and after events ("trailing")
    FP.throttle = function (func, wait, options) {
        var context, args, result;
        var timeout = null;
        var previous = 0;
        if (!options) options = {};

        var later = function later() {
            previous = options.leading === false ? 0 : Date.now();
            timeout = null;
            result = func.apply(context, args);
            if (!timeout) context = args = null;
        };

        return function () {
            var now = Date.now();
            if (!previous && options.leading === false) previous = now;
            var remaining = wait - (now - previous);
            context = this;
            args = arguments;

            if (remaining <= 0 || remaining > wait) {
                if (timeout) {
                    clearTimeout(timeout);
                    timeout = null;
                }

                previous = now;
                result = func.apply(context, args);
                if (!timeout) context = args = null;
            } else if (!timeout && options.trailing !== false) {
                timeout = setTimeout(later, remaining);
            }

            return result;
        };
    };

    FP.remove = el => { if (el) el.parentNode.removeChild(el); };

    FP.addAction = function( action_name, fn ){

        let arr = Array.isArray(action_name) ? action_name : [action_name]; // make sure that trigger is in an array

        arr.forEach(function ( name , i) { 
            if ( ! fp.actions[name] ) fp.actions[name] = [];
            fp.actions[name].push( fn );
        });
    }

    FP.doActions = ( action_name, args_o = null, cb ) => {
        if ( fp.actions[action_name] ) fp.actions[action_name].forEach( fn => {
            let return_val = fn( args_o );
            if ( typeof return_val !== 'undefined' ) args_o = return_val;
        } );
        if ( cb ) cb( args_o );
    }

    FP.hasActions = action_name => {
        return fp.actions[action_name] && fp.actions[action_name].length > 0;
    };

    FP.isScriptAllowedToLoad = function( id, force_load, permissions_a, geo_a ){
    
        // STOP if we don't have all the basic data yet
        if ( ! fp.ready ) return false;

        // STOP if the script has already loaded
        if ( fp.loaded.includes(id) ) return false;

        // START if the load is forced
        if ( force_load ) return true;

        // STOP if the tab is not in focus AND we are NOT on WooCommerce checkout page
        if ( fpdata.page_type !== 'Woo Order Received' ) {
            if ( document.hidden ) return false;
        }

        // STOP tracking an excluded visitor (MUST be after checking FORCE LOAD)
        if ( ! fp.main.track_current_user ) return false;

        // STOP if the script is set NOT to load in the current location
        if ( geo_a && ! shouldCustomScriptLoadHere( geo_a ) ) return false;

        // STOP if any permissions need to be given in this location...
        if ( fp.notice.enabled && fp.notice.mode == 'optin' && ! fpdata.cookies && permissions_a.length > 0 ) return false;

        // STOP if required permissions are not given
        if ( fpdata.cookies && permissions_a.length > 0 ) {
            if ( ! permissions_a.every( permission => fpdata.cookies[permission] ) ) return false;
        }

        return true;
    };

    FP.isAllowedToLoad = function( module_id, permissions_a, required_a, nr, tool_does_not_use_cookies = false ) {

        var id = nr ? module_id + nr : module_id;

        // STOP if we don't have all the basic data yet
        if ( ! fp.ready ) return false;

        // STOP if we have no settings for this tool
        if ( ! fp[id] ) return false;

        // STOP if the script has already loaded
        if ( fp.loaded.includes(id) ) return false;

        // STOP if the required data is missing
        if ( required_a.some( req => ! fp[id][req] ) ) return false;

        // START if the load is forced
        if ( fp[id].force_load ) return true;

        // STOP if the tab is not in focus AND we are NOT on WooCommerce checkout page
        if ( fpdata.page_type !== 'Woo Order Received' ) {
            if ( document.hidden ) return false;
        }

        // STOP tracking an excluded visitor (MUST be after checking FORCE LOAD)
        if ( ! fp.main.track_current_user ) return false;

        // STOP if the script is set NOT to load in the current location
        if ( ! shouldScriptLoadHere( id ) ) return false;

        // START 
        // if the tool doesn't use cookies
        if ( tool_does_not_use_cookies ) return true;

        // STOP if we wait for consent but some permissions need to be given
        if ( fp.notice.enabled && fp.notice.mode == 'optin' && ! fpdata.cookies && permissions_a.length > 0) return false;

        // STOP if user chose their cookie preferences but some required permissions are missing
        if ( fp.notice.enabled && fpdata.cookies && permissions_a.length > 0 ) {
            if ( ! permissions_a.every( permission => fpdata.cookies[permission] ) ) return false;
        }

        return true;
    };

    FP.startActivityTimer = function () {

        fpdata.activity.last = 0;

        if (!FP.v.activityTimerRunning) {
            FP.v.activityTimerRunning = true;
            FP.v.activityTimer = setInterval(function () {

                fpdata.activity.total++;
                fpdata.activity.current++;
                fpdata.activity.last++;

                FP.doActions( 'active_time_tick' );

                if ( fpdata.activity.last >= 15 ) FP.stopActivityTimer(); // stop timer after 15 secs from last activity
            }, 1000);
        }
    };

    FP.stopActivityTimer = function () {
        if (FP.v.activityTimerRunning) {
            FP.v.activityTimerRunning = false;
            clearInterval(FP.v.activityTimer);
        }
    };

    FP.encode = function (string) {
        return window.btoa(string).split('=').join('')
    }

    FP.detectAddedElement = callback => {

		const observer = new MutationObserver( mutations => {
		  	mutations.forEach( mutation => {
				if ( mutation.type === 'childList' ) {
			  		callback( mutation.addedNodes );
				}
		  	} );
		} );
	  
		observer.observe( document.body, {
			childList: true,
			subtree: true
		} );
	}

    FP.initCondTracking = ( events_settings, cb ) => {
		
		if ( ! ( events_settings && fp.atrig ) ) return;
		if ( ! fp.atrig.actions ) fp.atrig.actions = {};

		events_settings.forEach( e => {
			
			if ( ! fp.atrig.actions[e.atrig_id] ) fp.atrig.actions[e.atrig_id] = [];
			
			fp.atrig.actions[e.atrig_id].push( {
				'func' : cb.bind( this, e ),
				'repeat' : e.repeat
			} );
		});
	}

    FP.updateSessionData = () => {
        
        check_if_can_save_wpfp_cookies();

        if ( fp.vars.consents_changed ) {
            
            // if we can no longer use session cookies
            if ( ! fp.vars.can_save_wpfp_cookies ) {
                
                // delete everything
                FP.deleteCookie( 'fp_current_session' );
                delete fpdata.current_session;
                
            
            // if we can now use session cookies
            } else {

                // get latest sesion data (in case user agreed to cookies on a different tab)
                get_session_data_from_cookies();

                // set new data if it wasn't set before (when a user has just chosen sth in the banner)
                ! fpdata.current_session ? create_new_single_session_data() : update_single_session_data();
                
            };

        } else {
            // get latest sesion data (in case user visited a different tab before opening this one)
            get_session_data_from_cookies();
        }
    }
    
    FP.isClickTarget = function( selector ){
		return ( ! fpdata.clicked.link && ! fpdata.clicked.middle && fpdata.clicked.element.matches( selector ) ) || ( fpdata.clicked.link && fpdata.clicked.link.element.matches( selector ) );
	}

    

    //
    //
    // FP - END
    //
    //

    // BROKEN LINK TRACKING + REDIRECT TO A CUSTOM 404 PAGE
	if ( fp.track.track404 && fpdata.page_type == "404" && ! FP.getUrlParamByName("broken_link_location") ){
		const location = fp.track.redirect404_url ? new URL( fp.track.redirect404_url ) : window.location;
		window.location = location + ( location.search ? "&" : "?" ) + "broken_link_location=" + ( document.referrer || "direct_traffic_or_unknown" ) + "&broken_link=" + window.location;
	}
	
    // Random number (sometimes gets handy)
	fp.random = FP.getRandomStr(7);

    // CHECK FOR BOT TRAFFIC
	// -- modified version of https://stackoverflow.com/a/65563155/7702522

	fpdata.is_robot = (() => {
		
		// SMALL list
		if ( fp.main.bot_list == "basic" ) {
			
			const robots = new RegExp([/bot/,/spider/,/crawl/,/APIs-Google/,/AdsBot/,/Googlebot/,/mediapartners/,/Google Favicon/,/FeedFetcher/,/Google-Read-Aloud/,/googleweblight/,/bingbot/,/yandex/,/baidu/,/duckduck/,/Yahoo Link Preview/,/ia_archiver/,/facebookexternalhit/,/pinterest\.combot/,/redditbot/,/slackbot/,/Twitterbot/,/WhatsApp/,/S[eE][mM]rushBot/].map((r) => r.source).join("|"),"i");

			return robots.test(navigator.userAgent);

		// BIG list
		} else if ( fp.main.bot_list == "big" ) {

			const robots = new RegExp([
				/Googlebot/, /AdsBot/, /Feedfetcher-Google/, /Mediapartners-Google/, /Mediapartners/, /APIs-Google/, 
				/Google-InspectionTool/, /Storebot-Google/, /GoogleOther/, /bingbot/, /Slurp/, /wget/, /LinkedInBot/, 
				/Python-urllib/, /python-requests/, /aiohttp/, /httpx/, /libwww-perl/, /httpunit/, /Nutch/, 
				/Go-http-client/, /phpcrawl/, /msnbot/, /jyxobot/, /FAST-WebCrawler/, /FAST Enterprise Crawler/, 
				/BIGLOTRON/, /Teoma/, /convera/, /seekbot/, /Gigabot/, /Gigablast/, /exabot/, /ia_archiver/, 
				/GingerCrawler/, /webmon/, /HTTrack/, /grub\.org/, /UsineNouvelleCrawler/, /antibot/, 
				/netresearchserver/, /speedy/, /fluffy/, /findlink/, /msrbot/, /panscient/, /yacybot/, /AISearchBot/, 
				/ips-agent/, /tagoobot/, /MJ12bot/, /woriobot/, /yanga/, /buzzbot/, /mlbot/, /yandex\.combots/, 
				/purebot/, /Linguee Bot/, /CyberPatrol/, /voilabot/, /Baiduspider/, /citeseerxbot/, /spbot/, 
				/twengabot/, /postrank/, /Turnitin/, /scribdbot/, /page2rss/, /sitebot/, /linkdex/, /Adidxbot/, 
				/ezooms/, /dotbot/, /Mail\.RU_Bot/, /discobot/, /heritrix/, /findthatfile/, /europarchive\.org/, 
				/NerdByNature\.Bot/, /sistrix crawler/, /Ahrefs/, /fuelbot/, /CrunchBot/, /IndeedBot/, 
				/mappydata/, /woobot/, /ZoominfoBot/, /PrivacyAwareBot/, /Multiviewbot/, /SWIMGBot/, /Grobbot/, 
				/eright/, /Apercite/, /semanticbot/, /Aboundex/, /domaincrawler/, /wbsearchbot/, /summify/, /CCBot/, 
				/edisterbot/, /SeznamBot/, /ec2linkfinder/, /gslfbot/, /aiHitBot/, /intelium_bot/, 
				/facebookexternalhit/, /Yeti/, /RetrevoPageAnalyzer/, /lb-spider/, /Sogou/, /lssbot/, /careerbot/, 
				/wotbox/, /wocbot/, /ichiro/, /DuckDuckBot/, /lssrocketcrawler/, /drupact/, /webcompanycrawler/, 
				/acoonbot/, /openindexspider/, /gnam gnam spider/, /web-archive-net\.com\.bot/, /backlinkcrawler/, 
				/coccoc/, /integromedb/, /content crawler spider/, /toplistbot/, /it2media-domain-crawler/, 
				/ip-web-crawler\.com/, /siteexplorer\.info/, /elisabot/, /proximic/, /changedetection/, /arabot/, 
				/WeSEE:Search/, /niki-bot/, /CrystalSemanticsBot/, /rogerbot/, /360Spider/, /psbot/, 
				/InterfaxScanBot/, /CC Metadata Scaper/, /g00g1e\.net/, /GrapeshotCrawler/, /urlappendbot/, 
				/brainobot/, /fr-crawler/, /binlar/, /SimpleCrawler/, /Twitterbot/, /cXensebot/, /smtbot/, 
				/bnf\.fr_bot/, /A6-Indexer/, /ADmantX/, /Facebot/, /OrangeBot/, /memorybot/, /AdvBot/, 
				/MegaIndex/, /SemanticScholarBot/, /ltx71/, /nerdybot/, /xovibot/, /BUbiNG/, /Qwantify/, 
				/archive\.org_bot/, /Applebot/, /TweetmemeBot/, /crawler4j/, /findxbot/, /SemrushBot/, 
				/yoozBot/, /lipperhey/, /Y!J/, /Domain Re-Animator Bot/, /AddThis/, /Screaming Frog SEO Spider/, 
				/MetaURI/, /Scrapy/, /Livelapbot/, /OpenHoseBot/, /CapsuleChecker/, /collection@infegy\.com/, 
				/IstellaBot/, /DeuSu/, /betaBot/, /Cliqzbot/, /MojeekBot/, /netEstate NE Crawler/, 
				/SafeSearch microdata crawler/, /Gluten Free Crawler/, /Sonic/, /Sysomos/, /Trove/, /deadlinkchecker/, 
				/Slack-ImgProxy/, /Embedly/, /RankActiveLinkBot/, /iskanie/, /SafeDNSBot/, /SkypeUriPreview/, 
				/Veoozbot/, /Slackbot/, /redditbot/, /datagnionbot/, /Google-Adwords-Instant/, /adbeat_bot/, 
				/WhatsApp/, /contxbot/, /pinterest\.combot/, /electricmonk/, /GarlikCrawler/, /BingPreview/, 
				/vebidoobot/, /FemtosearchBot/, /Yahoo Link Preview/, /MetaJobBot/, /DomainStatsBot/, /mindUpBot/, 
				/Daum/, /Jugendschutzprogramm-Crawler/, /Xenu Link Sleuth/, /Pcore-HTTP/, /moatbot/, /KosmioBot/, 
				/pingdom/, /AppInsights/, /PhantomJS/, /Gowikibot/, /PiplBot/, /Discordbot/, /TelegramBot/, 
				/Jetslide/, /newsharecounts/, /James BOT/, /Barkrowler/, /TinEye/, /SocialRankIOBot/, 
				/trendictionbot/, /Ocarinabot/, /epicbot/, /Primalbot/, /DuckDuckGo-Favicons-Bot/, /GnowitNewsbot/, 
				/Leikibot/, /LinkArchiver/, /YaK/, /PaperLiBot/, /Digg Deeper/, /dcrawl/, /Snacktory/, /AndersPinkBot/, 
				/Fyrebot/, /EveryoneSocialBot/, /Mediatoolkitbot/, /Luminator-robots/, /ExtLinksBot/, /SurveyBot/, 
				/NING/, /okhttp/, /Nuzzel/, /omgili/, /PocketParser/, /YisouSpider/, /um-LN/, /ToutiaoSpider/, 
				/MuckRack/, /Jamie's Spider/, /AHC/, /NetcraftSurveyAgent/, /Laserlikebot/, /^Apache-HttpClient/, 
				/AppEngine-Google/, /Jetty/, /Upflow/, /Thinklab/, /Traackr\.com/, /Twurly/, /Mastodon/, /http_get/, 
				/DnyzBot/, /botify/, /007ac9 Crawler/, /BehloolBot/, /BrandVerity/, /check_http/, /BDCbot/, 
				/ZumBot/, /EZID/, /ICC-Crawler/, /ArchiveBot/, /^LCC /, /filterdb\.iss\.netcrawler/, /BLP_bbot/, 
				/BomboraBot/, /Buck/, /Companybook-Crawler/, /Genieo/, /magpie-crawler/, /MeltwaterNews/,
				/Moreover/,/newspaper/,/ScoutJet/,/sentry/,/StorygizeBot/,/UptimeRobot/,/OutclicksBot/,
				/seoscanners/,/Hatena/,/Google Web Preview/,/MauiBot/,/AlphaBot/,/SBL-BOT/,/IAS crawler/,
				/adscanner/,/Netvibes/,/acapbot/,/Baidu-YunGuanCe/,/bitlybot/,/blogmuraBot/,/Bot\.AraTurka\.com/,
				/bot-pge\.chlooe\.com/,/BoxcarBot/,/BTWebClient/,/ContextAd Bot/,/Digincore bot/,/Disqus/,/Feedly/,
				/Fetch/,/Fever/,/Flamingo_SearchEngine/,/FlipboardProxy/,/g2reader-bot/,/G2 Web Services/,/imrbot/,
				/K7MLWCBot/,/Kemvibot/,/Landau-Media-Spider/,/linkapediabot/,/vkShare/,/Siteimprove\.com/,/BLEXBot/,
				/DareBoost/,/ZuperlistBot/,/Miniflux/,/Feedspot/,/Diffbot/,/SEOkicks/,/tracemyfile/,/Nimbostratus-Bot/,
				/zgrab/,/PR-CY\.RU/,/AdsTxtCrawler/,/Datafeedwatch/,/Zabbix/,/TangibleeBot/,/google-xrawler/,/axios/,
				/Amazon CloudFront/,/Pulsepoint/,/CloudFlare-AlwaysOnline/,/Google-Structured-Data-Testing-Tool/,
				/WordupInfoSearch/,/WebDataStats/,/HttpUrlConnection/,/Seekport Crawler/,/ZoomBot/,/VelenPublicWebCrawler/,
				/MoodleBot/,/jpg-newsbot/,/outbrain/,/W3C_Validator/,/Validator\.nu/,/W3C-checklink/,/W3C-mobileOK/,
				/W3C_I18n-Checker/,/FeedValidator/,/W3C_CSS_Validator/,/W3C_Unicorn/,/Google-PhysicalWeb/,/Blackboard/,
				/ICBot/,/BazQux/,/Twingly/,/Rivva/,/Experibot/,/awesomecrawler/,/Dataprovider\.com/,/GroupHigh/,
				/theoldreader\.com/,/AnyEvent/,/Uptimebot\.org/,/Nmap Scripting Engine/,/2ip\.ru/,/Clickagy/,
				/Caliperbot/,/MBCrawler/,/online-webceo-bot/,/B2B Bot/,/AddSearchBot/,/Google Favicon/,/HubSpot/,
				/Chrome-Lighthouse/,/HeadlessChrome/,/CheckMarkNetwork/,/www\.uptime\.com/,/Streamline3Bot/,/serpstatbot/,
				/MixnodeCache/,/^curl/,/SimpleScraper/,/RSSingBot/,/Jooblebot/,/fedoraplanet/,/Friendica/,/NextCloud/,
				/Tiny Tiny RSS/,/RegionStuttgartBot/,/Bytespider/,/Datanyze/,/Google-Site-Verification/,/TrendsmapResolver/,
				/tweetedtimes/,/NTENTbot/,/Gwene/,/SimplePie/,/SearchAtlas/,/Superfeedr/,/feedbot/,/UT-Dorkbot/,/Amazonbot/,
				/SerendeputyBot/,/Eyeotabot/,/officestorebot/,/Neticle Crawler/,/SurdotlyBot/,/LinkisBot/,/AwarioSmartBot/,
				/AwarioRssBot/,/RyteBot/,/FreeWebMonitoring SiteChecker/,/AspiegelBot/,/NAVER Blog Rssbot/,/zenback bot/,
				/SentiBot/,/Domains Project/,/Pandalytics/,/VKRobot/,/bidswitchbot/,/tigerbot/,/NIXStatsbot/,/Atom Feed Robot/,
				/curebot/,/PagePeeker/,/Vigil/,/rssbot/,/startmebot/,/JobboerseBot/,/seewithkids/,/NINJA bot/,/Cutbot/,
				/BublupBot/,/BrandONbot/,/RidderBot/,/Taboolabot/,/Dubbotbot/,/FindITAnswersbot/,/infoobot/,/Refindbot/,
				/BlogTraffic\d\.\d+ Feed-Fetcher/,/SeobilityBot/,/Cincraw/,/Dragonbot/,/VoluumDSP-content-bot/,/FreshRSS/,
				/BitBot/,/^PHP-Curl-Class/,/Google-Certificates-Bridge/,/centurybot/,/Viber/,/e\.ventures Investment Crawler/,
				/evc-batch/,/PetalBot/,/virustotal/,/(^| )PTST/,/minicrawler/,/Cookiebot/,/trovitBot/,/seostar\.co/,/IonCrawl/,
				/Uptime-Kuma/,/SeekportBot/,/FreshpingBot/,/Feedbin/,/CriteoBot/,/Snap URL Preview Service/,/Better Uptime Bot/,
				/RuxitSynthetic/,/Google-Read-Aloud/,/ValveSteam/,/OdklBot/,/GPTBot/,/ChatGPT-User/,/YandexRenderResourcesBot/,
				/LightspeedSystemsCrawler/,/ev-crawler/,/BitSightBot/,/woorankreview/,/Google-Safety/,/AwarioBot/,/DataForSeoBot/,
				/Linespider/,/WellKnownBot/,/A Patent Crawler/,/StractBot/,/search\.marginalia\.nu/,/YouBot/,/Nicecrawler/,/Neevabot/,
				/BrightEdge Crawler/,/SiteCheckerBotCrawler/,/TombaPublicWebCrawler/,/CrawlyProjectCrawler/,/KomodiaBot/,/KStandBot/,
				/CISPA Webcrawler/,/MTRobot/,/hyscore\.io/,/AlexandriaOrgBot/,/2ip bot/,/Yellowbrandprotectionbot/,/SEOlizer/,
				/vuhuvBot/,/INETDEX-BOT/,/Synapse/,/t3versionsBot/,/deepnoc/,/Cocolyzebot/,/hypestat/,/ReverseEngineeringBot/,
				/sempi\.tech/,/Iframely/,/MetaInspector/,/node-fetch/,/lkxscan/,/python-opengraph/,/OpenGraphCheck/,
				/developers\.google\.com\+websnippet/,/SenutoBot/,/MaCoCu/,/NewsBlur/,/inoreader/,/NetSystemsResearch/,/PageThing/,
				/WordPress/,/PhxBot/,/ImagesiftBot/,/Expanse/,/InternetMeasurement/,/^BW/,/GeedoBot/,/Audisto Crawler/,
				/PerplexityBot/,/claudebot/,/Monsidobot/,/GroupMeBot/].map((r) => r.source).join("|"),"i");

				return robots.test(navigator.userAgent);

		} else {
			return false;
		};
	})();

    //
    // CONSENTS
    //

	function set_consents_in_fpdata( stats = false, pers = false, market = false ){
		fpdata.consents = {
			'can_track_stats' : stats,
			'can_track_pers' : pers,
			'can_track_market' : market,
		};
	}

	function set_gtag_consents( tag_name, type, stats = false, pers = false, market = false ){
		window[tag_name]("consent", type, {
			"ad_storage": market ? "granted" : "denied",
			"ad_user_data" : market ? "granted" : "denied",
			"ad_personalization" : market ? "granted" : "denied",
			"analytics_storage": stats ? "granted" : "denied",
			"personalization_storage": pers ? "granted" : "denied",
			"functionality_storage": pers || stats || market ? "granted" : "denied",
			"security_storage": "granted",
		});
	}

    // GET VARS

	let magic_keyw = FP.getUrlParamByName( fp.main.magic_keyword ),
		ga4_debug = FP.getUrlParamByName("ga4_debug"),
		cookies = FP.readCookie("fp_cookie"),
		track_me = FP.readCookie("fp_track_me");

	// SET BASIC VARS

	fpdata.cookies = cookies ? JSON.parse(cookies) : false;
	if ( track_me === "1" ) fp.main.track_current_user = true;

	set_consents_in_fpdata();

	// SET INITIAL GTAG, GTM AND MS ADS STUFF

	// Set GTAG dataLayer with denied consents
	window.dataLayer = window.dataLayer || [];
	window.gtag = function(){window.dataLayer.push(arguments);}
	set_gtag_consents("gtag", "default");

	// Set Gtag url_passthrough
	if ( fp?.gtag?.url_passthrough && fp.notice.enabled && ( fp.notice.mode == "optin" || fp.notice.mode == "optout" ) ) {
		window.gtag("set", "url_passthrough", true);
	};

	// MS Ads datalayer with denied consents
	window.uetq = window.uetq || [];
	window.uetq.push( "consent", "default", {
		"ad_storage": "denied"
	});

    // Set a separate dataLayer for the GTM (if enabled by the user) with denied consents

	if ( fp.gtm ) {
		fp.gtm.datalayer = ! fp.gtm.datalayer || fp.gtm.datalayer == "default" ? "dataLayer" : "fupi_dataLayer";
		if ( fp.gtm.datalayer == "fupi_dataLayer" ){
			window[fp.gtm.datalayer] = window[fp.gtm.datalayer] || [];
			window.fupi_gtm_gtag = function(){window[fp.gtm.datalayer].push(arguments);} // gtag used for consents
			set_gtag_consents("fupi_gtm_gtag", "default");
		}
	};

	// UPDATE COOKIE DATA - fupi_cookies and fpdata.cookies

	if ( magic_keyw && magic_keyw == "off" ){

		fp.main.track_current_user = false;
		
		fpdata.cookies = { "stats" : false, "personalisation" : false, "marketing" : false, "disabled" : true };
		FP.setCookie( "fp_cookie", JSON.stringify(fpdata.cookies), 7300 );

	} else if ( ga4_debug && typeof fpdata.cookies == "object" ){
			
		fpdata.cookies.ga4_debug = ga4_debug == "on" ? "on" : "off";
		FP.setCookie( "fp_ga4_debug", JSON.stringify(fpdata.cookies), 7300 );

	} else if ( FP.getUrlParamByName("reset_cookies") || ( magic_keyw && ( magic_keyw == "reset" || magic_keyw == "on" ) ) ){

		FP.deleteCookie("fp_cookie");
		fpdata.cookies = false;

	// check if should ask for consent again
	} else if ( fpdata.cookies && ! fpdata.cookies.disabled && fp.notice && ! fp.notice.dont_ask_again) {

        let pp_pub_changed = fp.notice.priv_policy_update && ( ! fpdata.cookies.pp_pub || fpdata.cookies.pp_pub != fp.notice.priv_policy_update ),
            tools_changed = fp.tools && ( ! fpdata.cookies.tools || ! fp.tools.every( id => fpdata.cookies.tools.includes(id) ) );

        if ( pp_pub_changed || tools_changed ) {
            FP.deleteCookie("fp_cookie");
            fpdata.cookies = false;
        } else {
            if ( fpdata.cookies.disabled ) fp.main.track_current_user = false;
        }
	}

    // CONSENT BANNER - apply page blur and lock scroll

	if ( fp.main.track_current_user && ! fp.main.is_customizer && fp.notice.enabled && ! fpdata.cookies && fp.notice.display_notice ) {
		
		// BLUR BACKGROUND
		if ( fp.notice.blur_page ) document.getElementsByTagName( "html" )[0].classList.add("fupi_blur");
		
		// LOCK PAGESCROLL
		if ( fp.notice.scroll_lock ) document.getElementsByTagName( "html" )[0].classList.add("fupi_scroll_lock");
	}

    let uses_geo = false;

    

    if ( ! uses_geo ) {
		fpdata.country = 'unknown';
		FP.updateConsents();
		fp.ready = true;
	}

    //
    // CONSENTS - END
    //

    // Check if WPFP can use cookies

    check_if_can_save_wpfp_cookies( true );

    // Get session data from cookies

    get_session_data_from_cookies();

    

    function create_new_single_session_data( updated_from_free = false ){
        
        fpdata.current_session = {};

        

        FP.setCookie( 'fp_current_session', JSON.stringify(fpdata.current_session), 0, 30 ); // 30 mins
    }

    function update_single_session_data(){
        if ( ! fpdata.current_session ) return;
        
        FP.setCookie( 'fp_current_session', JSON.stringify(fpdata.current_session), 0, 30 ); // 30 mins
    }

    

    // Set or update session data when the page loads
    function set_session_data(){
        
        if ( fp.vars.can_save_wpfp_cookies ) {
            
            fpdata.new_session = ! fpdata.current_session;
            
            ! fpdata.current_session ? create_new_single_session_data() : update_single_session_data();
            

        } else {
            fpdata.new_session = ! fpdata.refreshed && ! window.sessionStorage['fp_old_session'] && ( window.history.length == 1 || ( window.history.length == 2 && ! document.referrer ) );
        }

        fpdata.new_tab = ! fpdata.new_session && ! window.sessionStorage['fp_old_session'];
        window.sessionStorage['fp_old_session'] = true; // removed on a new tab
    };

    set_session_data();

    // CREATE, UPDATE OR REMOVE SESSION DATA
    // triggered after users change cookie preferences or when a page gains focus
    
    // the difference between this Fn and isAnchorLinkToCurrentPage is that this one doesn't check if the link actually works
    function isLinkToCurrentPage (link) {
        let target_url_obj = new URL( link.href );
        return target_url_obj.origin + target_url_obj.pathname == location.origin + location.pathname;
    }
	
	// checks if the link is an anchor to an EXISTING element on the same page
	function isAnchorLinkToCurrentPage (link){
        
        let url = new URL( link.href ),
            el = false;
        
        try {
            el = FP.findFirst( url.hash );
        } catch (e) {
            el = false;
        }
        
        return !! el;
    };

	function isOutboundLink(el){
        return el.href.indexOf(location.host) == -1 && el.href.indexOf('mailto:') != 0 && el.href.indexOf('tel:') != 0 && el.href.indexOf('http') == 0;
    };

	function trackClicks(e, is_middle_click){

		let redirect_stopped = false;

		if ( e.target.classList.contains('fupi_click_stopped') ) return;
		
		// PREVENT MULTI CLICKS
	    // Do not run this function if a visitor clicks the element multiple times in a short time-span
	    var now = Date.now();
	    if ( ! ( ! FP.v.lastClick || now - FP.v.lastClick > fp.track.dblclck_time ) ) return;
	    FP.v.lastClick = now;
		
		// PREVENT REDIRECTS

		// prevent default if a visitor clicked a link that is to be opened on the current tab
		var link_el = e.target.closest('a[href]');

		if ( link_el && ! is_middle_click && link_el.target != '_blank' && fp.track.link_click_delay  ){
			e.preventDefault(); // prevents redirects
			e.stopPropagation(); // prevents other event listeners from firing
			e.target.classList.add('fupi_click_stopped');
			e.target.style.pointerEvents = 'none';
			redirect_stopped = true;
			if ( fp.main.debug ) console.log('[FP] link-click stopped');
		};

		// GET CLICK DATA

		fpdata.clicked = {
			'element' : e.target,
			'middle' : is_middle_click,
		};

		if ( link_el && link_el.href.length > 1 ) {

			fpdata.clicked.link 				= {};
			fpdata.clicked.link.element			= link_el;
			fpdata.clicked.link.href			= link_el.href;
			fpdata.clicked.link.is_to_current_page = isLinkToCurrentPage(link_el);
			fpdata.clicked.link.target_blank 	= link_el.target == '_blank';

			fpdata.clicked.link.is_outbound 	= isOutboundLink(link_el);
			fpdata.clicked.link.is_anchor 		= isAnchorLinkToCurrentPage(link_el);

            if ( link_el.href.includes('mailto:') ){
                fpdata.clicked.link.is_email = true;
                fpdata.clicked.link.safe_email = link_el.href.replace('mailto:', '').split('@')[0];
            }

            if ( link_el.href.includes('tel:') ){
                fpdata.clicked.link.is_tel = true;
                fpdata.clicked.link.safe_tel = link_el.href.replace('tel:','').slice(-5);
            }
		}

		// RUN QUEUED FNs

		FP.doActions( 
			'click', 
			false, // no slug
			() => { // cb after all functions are run
				if ( fpdata.clicked.element.classList.contains('fupi_click_stopped') && typeof fpdata.clicked.element.click == 'function' ) {
					fpdata.clicked.element.click();
					fpdata.clicked.element.classList.remove('fupi_click_stopped');
					e.target.style.pointerEvents = '';
					if ( fp.main.debug ) console.log('[FP] Initiated click event after all FP functions');
				}
			}
		);
    };

    // Add blocked scripts to HTML
    function addScriptsToDOM() {

        // find scripts which are not loaded
        let plain_txt_scripts = FP.findAll('script[data-fupi_class*="fupi_blocked_script"]');

        plain_txt_scripts.forEach( plain_el => {
            
            let dta = plain_el.dataset,
                tag_type = dta.fupi_type,
                scr_id = dta.fupi_id,
                url = dta.fupi_url ?? false,
                force_load = dta.fupi_force == '1',
                permissions_a = dta.fupi_permiss ? dta.fupi_permiss.split(' ') : [],
                geo_a = dta.fupi_geo ? dta.fupi_geo.split(',') : [];
            
            if ( FP.isScriptAllowedToLoad( scr_id, force_load, permissions_a, geo_a ) ) {
                if ( tag_type == 'inline' ){
                    loadInlineScript( plain_el ); // replaces old tag
                } else {
                    if ( url ){
                        FP.createTag(
                            url, 
                            ()=>{ FP.loaded( scr_id, scr_id ) }, 
                            plain_el,
                            tag_type
                        );
                        FP.remove(plain_el);
                    } else {
                        if (fp.main.debug) console.error('[FP] Couldn\'t load script with ID ' + scr_id +  '. Missing src or href values.')
                    }
                }
            }
                
        });
	}

    // FIRE TAGS WHEN THE DOCUMENT GETS AND LOSES FOCUS

    function doPageShowActions(){

        if ( ! fp.vars.page_show_tracked ){

            fp.vars.page_show_tracked = true;
            fp.vars.page_hide_tracked = false;

            fpdata.doc_in_focus = true;
            
            FP.startActivityTimer();
            
            var cookies = FP.readCookie('fp_cookie');

            if ( cookies ) {
                if ( fpdata.cookies && cookies == JSON.stringify( fpdata.cookies ) ) return;
                cookies = JSON.parse(cookies);
                fpdata.cookies = cookies;
                FP.updateConsents();
            } else {
                fpdata.cookies = false;
            }
            
            FP.updateSessionData();

            FP.sendEvt( 'fp_load_scripts' );

            if ( fp.actions.page_in_focus ) FP.doActions('page_in_focus');
        }
    }

    function doPageHideActions(){
        if ( ! fp.vars.page_hide_tracked ) {
            fp.vars.page_hide_tracked = true;
            fp.vars.page_show_tracked = false;

            fpdata.doc_in_focus = false;
            FP.stopActivityTimer();
            if ( fp.actions.page_in_blur ) FP.doActions('page_in_blur');
            fpdata.activity.current = 0;
        }
    }

    // ON DYNAMIC URL CHANGES
    // URL is set every second with setInterval() and not "active_time_tick", to make the check even when the page is not in focus

	let currentUrl = location.href;
	  
	setInterval( () => { 

        fpdata.date = getFormattedDateTime();

		if ( location.href !== currentUrl ) {
			currentUrl = location.href;
			FP.doActions( 'url_change' );
		}
	}, 1000);

    // pagehide/show fires on some events but is cross-browser
    // !! it fires BEFORE visibilitychanges
    // !! when the pageshow event fires the document obj doesn't have updated cookies or localStarage yet, thus we don't use it for "show"
    window.addEventListener( "pagehide", e => { doPageHideActions() }, false );
    // window.addEventListener( "pageshow", e => { doPageShowActions('pageshow') }, false ); visibilitychange is universal

    // beforeunload doesn't work with prev/next page buttons and has problems on mobiles
    window.addEventListener( "beforeunload", e => { doPageHideActions() }, false );

    // visibilitychanges fires on all events but has problems on mobiles and in safari
    // !! fires AFTER pagehide/show
    // !! when the visibilitychange event fires for "show" the document obj already has updated cookies and localStarage
    document.addEventListener( 'visibilitychange', function () {
        document.hidden ? doPageHideActions() :  doPageShowActions('visibilitychange');
        //if ( fp.page_visibility ) FP.doActions( 'page_visibility' );
    }, false);

    document.documentElement.addEventListener('mouseleave', (e) => {
        // Don't fire if the page is losing focus (not a real mouse leave)
        if (document.hidden) return;
        
        // Check if mouse actually left viewport boundaries
        if (e.clientY <= 0 || e.clientX <= 0 || 
            e.clientX >= window.innerWidth || e.clientY >= window.innerHeight) {
            FP.doActions('mouse_leave_window');
        }
    }, false );


    // load custom/extra scripts to DOM
    document.addEventListener( 'fp_load_scripts', addScriptsToDOM );

    function formSubmitEvents( {target} ){
        fpdata.submitted_form = { 'element' : target };
		FP.doActions( 'form_submit' );
    }

    // ON FIELD BLUR
    document.addEventListener('blur', function(e) {
        // Check if the blurred element is a form field
        if ( e.target.nodeType === Node.ELEMENT_NODE && e.target.matches('input, textarea, select') ) {
            fpdata.last_form_field = e.target;
            FP.doActions( 'form_field_blur' );
        }
    }, true);

    // ON DOM LOADED

    function do_when_DOM_loaded(){
        
        addScriptsToDOM();

        // init actions waiting for DOM loaded
        setTimeout ( ()=>{ FP.doActions( ['dom_loaded'] ) }, 250 ); // makes sure that footer JS files loaded and hooked to dom_loaded event

        // start listening to form submits
        setTimeout( ()=>{ document.addEventListener('submit', formSubmitEvents ) }, fp.track.formsubm_trackdelay ? fp.track.formsubm_trackdelay * 1000 : 1000 );
        
        // start listening to DOM modifications
		if ( fp.track.use_mutation_observer ) FP.detectAddedElement( addedNodes => {
            let els = FP.nl2Arr( addedNodes );
            if ( els.length > 0 ) FP.doActions( ['dom_modified'], els );
        } );
    }

    if ( document.readyState === "complete" ) {
        do_when_DOM_loaded();
    } else {
        document.addEventListener('DOMContentLoaded', do_when_DOM_loaded );
    }

    // ON MOUSE MOVE
    document.addEventListener( 'mousemove', FP.throttle( FP.startActivityTimer, 500 ) ); // This starts the user's activity timer

    if ( ! document.hidden) {
        FP.startActivityTimer();
        fpdata.doc_in_focus = true;
        fp.vars.page_show_tracked = true;
        fp.vars.page_hide_tracked = false;
    };

    // ON LEFT CLICK
	window.addEventListener( 'click', function (e) {
		if ( fp.actions.click ) trackClicks(e, false);
	}, true);

	// ON MIDDLE CLICK
	window.addEventListener( 'mouseup', function(e) {
		if ( fp.actions.click && e.button == 1) trackClicks(e, true);
	});

    FP.loaded('head_helpers');

};

FP.load('head_helpers', 'fupi_top_helpers', ['head_js'] );
