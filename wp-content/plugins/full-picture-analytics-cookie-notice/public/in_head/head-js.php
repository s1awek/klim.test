<?php

// BROKEN LINK TRACKING + REDIRECT TO A CUSTOM 404 PAGE
$output .= 'fpdata.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
fp.notice.vars = {};

(function (FP) {

	\'use strict\';

	var d = document,
		w = window;

	FP.getUrlParamByName = name => {
		// var match = RegExp(\'[?&]\' + name + \'=([^&]*)\').exec(window.location.search);
		// return match && decodeURIComponent(match[1].replace(/\\+/g, \' \'));
		const queryString = window.location.search,
			urlParams = new URLSearchParams(queryString);
		return urlParams.get(name);
	};

	// BROKEN LINK TRACKING + REDIRECT TO A CUSTOM 404 PAGE
	if( fp.track.track404 && fpdata.page_type == "404" && ! FP.getUrlParamByName("broken_link_location") ){
		const location = fp.track.redirect404_url ? new URL( fp.track.redirect404_url ) : window.location;
		window.location = location + ( location.search ? "&" : "?" ) + "broken_link_location=" + ( document.referrer || "direct_traffic_or_unknown" ) + "&broken_link=" + window.location;
	}

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
		var ca = d.cookie.split(\';\');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == \' \') {c = c.substring(1, c.length);}
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
		};
		return null;
	};

	FP.nl2Arr = nl => nl ? [].slice.call(nl) : false;

	FP.findID = (e, c) => {
        if ( c === null ) return null;
        if ( !e ) return false;
        c = c || document;
        return c.getElementById(e);
    };

	FP.findAll = (e, c) => {
		if ( c === null ) return [];
		if ( ! e ) return false;
		c = c || document;
		return FP.nl2Arr(c.querySelectorAll(e));
	};
	
	FP.loadScript = scr_id => {
		
		let temp_script = FP.findID( scr_id + \'_temp\' ),
			new_script = document.createElement(\'script\');

		new_script.innerHTML = temp_script.innerHTML;
		temp_script.parentNode.insertBefore(new_script, temp_script.nextSibling);
		temp_script.remove();

		fp.loaded.push( scr_id );
	};

	FP.getRandomStr = ()=>{
		return ( Math.random() + 1 ).toString(36).substring(2);
	};

	FP.deleteCookie = name => { FP.setCookie(name, "", -1); };

    FP.getInner = function (vals, splitter = ".") {
        
        let args = Array.isArray(vals) ? vals : vals.split(splitter).map( arg => arg.trim() ),
            obj = window[args.shift()];

        for (var i = 0; i < args.length; i++) {
            var prop = args[i];
            if (! obj || ! obj.hasOwnProperty(prop)) return false;
            obj = obj[prop];
        };

        return obj;
    };

	FP.runFn = ( fn_name, args_arr ) => {
			
		let fn = FP.getInner( fn_name, "." );
		
		if ( fn ) {
			args_arr ? fn( ...args_arr ) : fn();
		} else {
			fp.waitlist[fn_name] = typeof args_arr === \'undefined\' ? [] : args_arr;
		};
	};

	FP.enqueueFn = ( fn_name ) => {
		let fn = FP.getInner( fn_name, "." );
		if ( fp.waitlist[fn_name] ) fn( ...fp.waitlist[fn_name] );
	}

	FP.getScript = ( url, cb, attrs, type = \'script\', fallback = false ) => {

		attrs = attrs || false;
		var s = "";

		if ( type == "img" ) {
			s = d.createElement(\'img\');
			s.src = url;
		} else if ( type == "link" ) {
			s = d.createElement(\'link\');
			s.href = url;
		} else {
			s = d.createElement(\'script\')
			s.src = url;
			s.type = \'application/javascript\';
		}

		s.onerror = ()=>{ 
			if ( fallback ) FP.getScript( fallback, cb, attrs, type );
		};

		if (attrs) {
			for (var key in attrs) {
				if ( key !== "/" ) s.setAttribute(key, attrs[key]);
			}
		}

		d.getElementsByTagName("head")[0].appendChild(s);
		if (cb) s.onload = cb;
	};

	FP.sendEvt = (evt_name, details_a) => {
        var details = details_a ? { \'detail\' : details_a } : {},
            fp_event = new CustomEvent( evt_name, details );
        document.dispatchEvent(fp_event);
    };

	FP.prepareProduct = ( type, id, data ) => {
		
		if ( type !== "cart_item" ) fpdata.woo.products[id] = fpdata.woo.products[id] ? { ...fpdata.woo.products[id], ...data } : data;
		
		if ( type == \'single\' || type == \'teaser\' ) {

			// prepare products after all load
			if ( fp.vars.wooImpressTimeout ) clearTimeout( fp.vars.wooImpressTimeout );
			fp.vars.wooImpressTimeout = setTimeout( () => FP.runFn( "FP.fns.prepare_teaser_and_single" ), 200 );
		}
    };

	// Change the value of track_current_user to true if there is a cookie "fp_track_me"
	let track_me = FP.readCookie(\'fp_track_me\');
	if ( track_me ) {
		fp.main.track_current_user = true;
	};

	// CHECK FOR BOT TRAFFIC
	// -- modified version of https://stackoverflow.com/a/65563155/7702522
	
	// BOT CHECK (instant)
	fpdata.is_robot = (() => {
		
		// SMALL list
		if ( fp.main.bot_list == "basic" ) {
			
			const robots = new RegExp([/bot/,/spider/,/crawl/,/APIs-Google/,/AdsBot/,/Googlebot/,/mediapartners/,/Google Favicon/,/FeedFetcher/,/Google-Read-Aloud/,/googleweblight/,/bingbot/,/yandex/,/baidu/,/duckduck/,/Yahoo Link Preview/,/ia_archiver/,/facebookexternalhit/,/pinterest\\.combot/,/redditbot/,/slackbot/,/Twitterbot/,/WhatsApp/,/S[eE][mM]rushBot/].map((r) => r.source).join("|"),"i");

			return robots.test(navigator.userAgent);

		// BIG list
		} else if ( fp.main.bot_list == "big" ) {

			const robots = new RegExp([
				/Googlebot/, /AdsBot/, /Feedfetcher-Google/, /Mediapartners-Google/, /Mediapartners/, /APIs-Google/, 
				/Google-InspectionTool/, /Storebot-Google/, /GoogleOther/, /bingbot/, /Slurp/, /wget/, /LinkedInBot/, 
				/Python-urllib/, /python-requests/, /aiohttp/, /httpx/, /libwww-perl/, /httpunit/, /Nutch/, 
				/Go-http-client/, /phpcrawl/, /msnbot/, /jyxobot/, /FAST-WebCrawler/, /FAST Enterprise Crawler/, 
				/BIGLOTRON/, /Teoma/, /convera/, /seekbot/, /Gigabot/, /Gigablast/, /exabot/, /ia_archiver/, 
				/GingerCrawler/, /webmon/, /HTTrack/, /grub\\.org/, /UsineNouvelleCrawler/, /antibot/, 
				/netresearchserver/, /speedy/, /fluffy/, /findlink/, /msrbot/, /panscient/, /yacybot/, /AISearchBot/, 
				/ips-agent/, /tagoobot/, /MJ12bot/, /woriobot/, /yanga/, /buzzbot/, /mlbot/, /yandex\\.combots/, 
				/purebot/, /Linguee Bot/, /CyberPatrol/, /voilabot/, /Baiduspider/, /citeseerxbot/, /spbot/, 
				/twengabot/, /postrank/, /Turnitin/, /scribdbot/, /page2rss/, /sitebot/, /linkdex/, /Adidxbot/, 
				/ezooms/, /dotbot/, /Mail\\.RU_Bot/, /discobot/, /heritrix/, /findthatfile/, /europarchive\\.org/, 
				/NerdByNature\\.Bot/, /sistrix crawler/, /Ahrefs/, /fuelbot/, /CrunchBot/, /IndeedBot/, 
				/mappydata/, /woobot/, /ZoominfoBot/, /PrivacyAwareBot/, /Multiviewbot/, /SWIMGBot/, /Grobbot/, 
				/eright/, /Apercite/, /semanticbot/, /Aboundex/, /domaincrawler/, /wbsearchbot/, /summify/, /CCBot/, 
				/edisterbot/, /SeznamBot/, /ec2linkfinder/, /gslfbot/, /aiHitBot/, /intelium_bot/, 
				/facebookexternalhit/, /Yeti/, /RetrevoPageAnalyzer/, /lb-spider/, /Sogou/, /lssbot/, /careerbot/, 
				/wotbox/, /wocbot/, /ichiro/, /DuckDuckBot/, /lssrocketcrawler/, /drupact/, /webcompanycrawler/, 
				/acoonbot/, /openindexspider/, /gnam gnam spider/, /web-archive-net\\.com\\.bot/, /backlinkcrawler/, 
				/coccoc/, /integromedb/, /content crawler spider/, /toplistbot/, /it2media-domain-crawler/, 
				/ip-web-crawler\\.com/, /siteexplorer\\.info/, /elisabot/, /proximic/, /changedetection/, /arabot/, 
				/WeSEE:Search/, /niki-bot/, /CrystalSemanticsBot/, /rogerbot/, /360Spider/, /psbot/, 
				/InterfaxScanBot/, /CC Metadata Scaper/, /g00g1e\\.net/, /GrapeshotCrawler/, /urlappendbot/, 
				/brainobot/, /fr-crawler/, /binlar/, /SimpleCrawler/, /Twitterbot/, /cXensebot/, /smtbot/, 
				/bnf\\.fr_bot/, /A6-Indexer/, /ADmantX/, /Facebot/, /OrangeBot/, /memorybot/, /AdvBot/, 
				/MegaIndex/, /SemanticScholarBot/, /ltx71/, /nerdybot/, /xovibot/, /BUbiNG/, /Qwantify/, 
				/archive\\.org_bot/, /Applebot/, /TweetmemeBot/, /crawler4j/, /findxbot/, /SemrushBot/, 
				/yoozBot/, /lipperhey/, /Y!J/, /Domain Re-Animator Bot/, /AddThis/, /Screaming Frog SEO Spider/, 
				/MetaURI/, /Scrapy/, /Livelapbot/, /OpenHoseBot/, /CapsuleChecker/, /collection@infegy\\.com/, 
				/IstellaBot/, /DeuSu/, /betaBot/, /Cliqzbot/, /MojeekBot/, /netEstate NE Crawler/, 
				/SafeSearch microdata crawler/, /Gluten Free Crawler/, /Sonic/, /Sysomos/, /Trove/, /deadlinkchecker/, 
				/Slack-ImgProxy/, /Embedly/, /RankActiveLinkBot/, /iskanie/, /SafeDNSBot/, /SkypeUriPreview/, 
				/Veoozbot/, /Slackbot/, /redditbot/, /datagnionbot/, /Google-Adwords-Instant/, /adbeat_bot/, 
				/WhatsApp/, /contxbot/, /pinterest\\.combot/, /electricmonk/, /GarlikCrawler/, /BingPreview/, 
				/vebidoobot/, /FemtosearchBot/, /Yahoo Link Preview/, /MetaJobBot/, /DomainStatsBot/, /mindUpBot/, 
				/Daum/, /Jugendschutzprogramm-Crawler/, /Xenu Link Sleuth/, /Pcore-HTTP/, /moatbot/, /KosmioBot/, 
				/pingdom/, /AppInsights/, /PhantomJS/, /Gowikibot/, /PiplBot/, /Discordbot/, /TelegramBot/, 
				/Jetslide/, /newsharecounts/, /James BOT/, /Barkrowler/, /TinEye/, /SocialRankIOBot/, 
				/trendictionbot/, /Ocarinabot/, /epicbot/, /Primalbot/, /DuckDuckGo-Favicons-Bot/, /GnowitNewsbot/, 
				/Leikibot/, /LinkArchiver/, /YaK/, /PaperLiBot/, /Digg Deeper/, /dcrawl/, /Snacktory/, /AndersPinkBot/, 
				/Fyrebot/, /EveryoneSocialBot/, /Mediatoolkitbot/, /Luminator-robots/, /ExtLinksBot/, /SurveyBot/, 
				/NING/, /okhttp/, /Nuzzel/, /omgili/, /PocketParser/, /YisouSpider/, /um-LN/, /ToutiaoSpider/, 
				/MuckRack/, /Jamie\'s Spider/, /AHC/, /NetcraftSurveyAgent/, /Laserlikebot/, /^Apache-HttpClient/, 
				/AppEngine-Google/, /Jetty/, /Upflow/, /Thinklab/, /Traackr\\.com/, /Twurly/, /Mastodon/, /http_get/, 
				/DnyzBot/, /botify/, /007ac9 Crawler/, /BehloolBot/, /BrandVerity/, /check_http/, /BDCbot/, 
				/ZumBot/, /EZID/, /ICC-Crawler/, /ArchiveBot/, /^LCC /, /filterdb\\.iss\\.netcrawler/, /BLP_bbot/, 
				/BomboraBot/, /Buck/, /Companybook-Crawler/, /Genieo/, /magpie-crawler/, /MeltwaterNews/,
				/Moreover/,/newspaper/,/ScoutJet/,/sentry/,/StorygizeBot/,/UptimeRobot/,/OutclicksBot/,
				/seoscanners/,/Hatena/,/Google Web Preview/,/MauiBot/,/AlphaBot/,/SBL-BOT/,/IAS crawler/,
				/adscanner/,/Netvibes/,/acapbot/,/Baidu-YunGuanCe/,/bitlybot/,/blogmuraBot/,/Bot\\.AraTurka\\.com/,
				/bot-pge\\.chlooe\\.com/,/BoxcarBot/,/BTWebClient/,/ContextAd Bot/,/Digincore bot/,/Disqus/,/Feedly/,
				/Fetch/,/Fever/,/Flamingo_SearchEngine/,/FlipboardProxy/,/g2reader-bot/,/G2 Web Services/,/imrbot/,
				/K7MLWCBot/,/Kemvibot/,/Landau-Media-Spider/,/linkapediabot/,/vkShare/,/Siteimprove\\.com/,/BLEXBot/,
				/DareBoost/,/ZuperlistBot/,/Miniflux/,/Feedspot/,/Diffbot/,/SEOkicks/,/tracemyfile/,/Nimbostratus-Bot/,
				/zgrab/,/PR-CY\\.RU/,/AdsTxtCrawler/,/Datafeedwatch/,/Zabbix/,/TangibleeBot/,/google-xrawler/,/axios/,
				/Amazon CloudFront/,/Pulsepoint/,/CloudFlare-AlwaysOnline/,/Google-Structured-Data-Testing-Tool/,
				/WordupInfoSearch/,/WebDataStats/,/HttpUrlConnection/,/Seekport Crawler/,/ZoomBot/,/VelenPublicWebCrawler/,
				/MoodleBot/,/jpg-newsbot/,/outbrain/,/W3C_Validator/,/Validator\\.nu/,/W3C-checklink/,/W3C-mobileOK/,
				/W3C_I18n-Checker/,/FeedValidator/,/W3C_CSS_Validator/,/W3C_Unicorn/,/Google-PhysicalWeb/,/Blackboard/,
				/ICBot/,/BazQux/,/Twingly/,/Rivva/,/Experibot/,/awesomecrawler/,/Dataprovider\\.com/,/GroupHigh/,
				/theoldreader\\.com/,/AnyEvent/,/Uptimebot\\.org/,/Nmap Scripting Engine/,/2ip\\.ru/,/Clickagy/,
				/Caliperbot/,/MBCrawler/,/online-webceo-bot/,/B2B Bot/,/AddSearchBot/,/Google Favicon/,/HubSpot/,
				/Chrome-Lighthouse/,/HeadlessChrome/,/CheckMarkNetwork/,/www\\.uptime\\.com/,/Streamline3Bot/,/serpstatbot/,
				/MixnodeCache/,/^curl/,/SimpleScraper/,/RSSingBot/,/Jooblebot/,/fedoraplanet/,/Friendica/,/NextCloud/,
				/Tiny Tiny RSS/,/RegionStuttgartBot/,/Bytespider/,/Datanyze/,/Google-Site-Verification/,/TrendsmapResolver/,
				/tweetedtimes/,/NTENTbot/,/Gwene/,/SimplePie/,/SearchAtlas/,/Superfeedr/,/feedbot/,/UT-Dorkbot/,/Amazonbot/,
				/SerendeputyBot/,/Eyeotabot/,/officestorebot/,/Neticle Crawler/,/SurdotlyBot/,/LinkisBot/,/AwarioSmartBot/,
				/AwarioRssBot/,/RyteBot/,/FreeWebMonitoring SiteChecker/,/AspiegelBot/,/NAVER Blog Rssbot/,/zenback bot/,
				/SentiBot/,/Domains Project/,/Pandalytics/,/VKRobot/,/bidswitchbot/,/tigerbot/,/NIXStatsbot/,/Atom Feed Robot/,
				/curebot/,/PagePeeker/,/Vigil/,/rssbot/,/startmebot/,/JobboerseBot/,/seewithkids/,/NINJA bot/,/Cutbot/,
				/BublupBot/,/BrandONbot/,/RidderBot/,/Taboolabot/,/Dubbotbot/,/FindITAnswersbot/,/infoobot/,/Refindbot/,
				/BlogTraffic\\d\\.\\d+ Feed-Fetcher/,/SeobilityBot/,/Cincraw/,/Dragonbot/,/VoluumDSP-content-bot/,/FreshRSS/,
				/BitBot/,/^PHP-Curl-Class/,/Google-Certificates-Bridge/,/centurybot/,/Viber/,/e\\.ventures Investment Crawler/,
				/evc-batch/,/PetalBot/,/virustotal/,/(^| )PTST/,/minicrawler/,/Cookiebot/,/trovitBot/,/seostar\\.co/,/IonCrawl/,
				/Uptime-Kuma/,/SeekportBot/,/FreshpingBot/,/Feedbin/,/CriteoBot/,/Snap URL Preview Service/,/Better Uptime Bot/,
				/RuxitSynthetic/,/Google-Read-Aloud/,/ValveSteam/,/OdklBot/,/GPTBot/,/ChatGPT-User/,/YandexRenderResourcesBot/,
				/LightspeedSystemsCrawler/,/ev-crawler/,/BitSightBot/,/woorankreview/,/Google-Safety/,/AwarioBot/,/DataForSeoBot/,
				/Linespider/,/WellKnownBot/,/A Patent Crawler/,/StractBot/,/search\\.marginalia\\.nu/,/YouBot/,/Nicecrawler/,/Neevabot/,
				/BrightEdge Crawler/,/SiteCheckerBotCrawler/,/TombaPublicWebCrawler/,/CrawlyProjectCrawler/,/KomodiaBot/,/KStandBot/,
				/CISPA Webcrawler/,/MTRobot/,/hyscore\\.io/,/AlexandriaOrgBot/,/2ip bot/,/Yellowbrandprotectionbot/,/SEOlizer/,
				/vuhuvBot/,/INETDEX-BOT/,/Synapse/,/t3versionsBot/,/deepnoc/,/Cocolyzebot/,/hypestat/,/ReverseEngineeringBot/,
				/sempi\\.tech/,/Iframely/,/MetaInspector/,/node-fetch/,/lkxscan/,/python-opengraph/,/OpenGraphCheck/,
				/developers\\.google\\.com\\+websnippet/,/SenutoBot/,/MaCoCu/,/NewsBlur/,/inoreader/,/NetSystemsResearch/,/PageThing/,
				/WordPress/,/PhxBot/,/ImagesiftBot/,/Expanse/,/InternetMeasurement/,/^BW/,/GeedoBot/,/Audisto Crawler/,
				/PerplexityBot/,/claudebot/,/Monsidobot/,/GroupMeBot/].map((r) => r.source).join("|"),"i");

				return robots.test(navigator.userAgent);

		} else {
			return false;
		};
	})();

	// GENERATE A RANDOM STRING FOR VARIOUS USES
	fp.random = FP.getRandomStr(7);

	// SET INITIAL GTAG, GTM AND MS ADS STUFF

	// First, we set the dataLayers for GA, GAds and MS Ads

	window.dataLayer = window.dataLayer || [];
	window.gtag = function(){window.dataLayer.push(arguments);}
	window.uetq = window.uetq || [];
	
	// next, we set a separate GTM DataLayer if it has DL Protection enabled

	if ( fp.gtm ) {
		fp.gtm.datalayer = ! fp.gtm.datalayer || fp.gtm.datalayer == "default" ? "dataLayer" : "fupi_dataLayer";
		if ( fp.gtm.datalayer == "fupi_dataLayer" ){
			window[fp.gtm.datalayer] = window[fp.gtm.datalayer] || [];
			window.fupi_gtm_gtag = function(){window[fp.gtm.datalayer].push(arguments);} // gtag used for consents
		}
	};

	// UPDATE COOKIE DATA - fupi_cookies and fpdata.cookies

	let magic_keyw = FP.getUrlParamByName( fp.main.magic_keyword ),
		ga4_debug = FP.getUrlParamByName("ga4_debug"),
		cookies = FP.readCookie(\'fp_cookie\');
	
	cookies = cookies ? JSON.parse(cookies) : false;

	fpdata.cookies = false;

	if ( magic_keyw && magic_keyw == \'off\' ){

		var updated_cookies = { \'stats\' : false, \'personalisation\' : false, \'marketing\' : false, \'disabled\' : true };

		fp.main.track_current_user = false;
		FP.setCookie(\'fp_cookie\', JSON.stringify(updated_cookies), 7300 );
		fpdata.cookies = updated_cookies;

	} else if ( ga4_debug ){

		if ( ga4_debug == \'on\' ) {
			
			var updated_cookies = { \'stats\' : true, \'personalisation\' : true, \'marketing\' : true, \'disabled\' : false, \'ga4_debug\' : \'on\' };
	
			if ( cookies && cookies.pp_pub ) updated_cookies.pp_pub = cookies.pp_pub;
			if ( cookies && cookies.tools ) updated_cookies.tools = cookies.tools;
	
			FP.setCookie(\'fp_cookie\', JSON.stringify(updated_cookies), 7300 );
			fpdata.cookies = updated_cookies;

		} else if ( ga4_debug == \'off\' ) {
			var updated_cookies = { \'stats\' : true, \'personalisation\' : true, \'marketing\' : true, \'disabled\' : false };
	
			if ( cookies && cookies.pp_pub ) updated_cookies.pp_pub = cookies.pp_pub;
			if ( cookies && cookies.tools ) updated_cookies.tools = cookies.tools;
	
			FP.setCookie(\'fp_cookie\', JSON.stringify(updated_cookies), 7300 );
			fpdata.cookies = updated_cookies;
		}

	} else if ( FP.getUrlParamByName("reset_cookies") || ( magic_keyw && ( magic_keyw == \'reset\' || magic_keyw == \'on\' ) ) ){

		FP.deleteCookie(\'fp_cookie\');

	} else {

		var changed = false;

		if ( cookies ) {
			if ( cookies.disabled ) {

				var updated_cookies = { \'stats\' : false, \'personalisation\' : false, \'marketing\' : false, \'disabled\' : true };
				
				fp.main.track_current_user = false;
				FP.setCookie(\'fp_cookie\', JSON.stringify(updated_cookies), 7300 );
				fpdata.cookies = updated_cookies;

			} else if ( fp.notice ) {

				// ask for consent again

				if ( fp.notice.priv_policy_update ) {
					if ( ! cookies.pp_pub || cookies.pp_pub != fp.notice.priv_policy_update ) changed = true;
				}
				
				if ( fp.tools ){
					if ( ! cookies.tools || ! fp.tools.every( id => cookies.tools.includes(id) ) ) changed = true;
				}
		
				if ( changed ) {
					FP.deleteCookie(\'fp_cookie\');
				} else {
					fpdata.cookies = cookies;
					if ( fpdata.cookies.disabled ) fp.main.track_current_user = false;
				}
			}
		}
	}

	//
	// CONSENT BANNER 
	//
	
	if ( fp.main.track_current_user && ! fp.main.is_customizer && fp.notice.enabled && ! fpdata.cookies && fp.notice.display_notice ) {
		
		// BLUR BACKGROUND
		if ( fp.notice.blur_page ) {
			document.getElementsByTagName( \'html\' )[0].classList.add(\'fupi_blur\');
		}
		
		// LOCK PAGESCROLL
		if ( fp.notice.scroll_lock ) {
			document.getElementsByTagName( \'html\' )[0].classList.add(\'fupi_scroll_lock\');
		}
	}

	FP.updateConsents = () => {

		if ( fp.vars.use_other_cmp ) return;

		if ( fp.main.debug ) console.log(\'[FP] Updating consents\');
		
		// if the user made a choice in the past
		if ( fpdata.cookies ){
			fpdata.consents = {
				\'can_track_stats\' : fpdata.cookies.stats || false,
				\'can_track_pers\' : fpdata.cookies.personalisation || false,
				\'can_track_market\' : fpdata.cookies.marketing || false,
			};

		// if no choice was made in the past
		} else {
			
			// deny all if consent banner is in optin mode
			if ( fp.notice.enabled && fp.notice.mode == "optin" ) {
				fpdata.consents = {
					\'can_track_stats\' : false,
					\'can_track_pers\' : false,
					\'can_track_market\' : false,
				}
			
			// agree to all if consent banner is disabled or we are in optout or notification mode
			} else {
			 	fpdata.consents = {
					\'can_track_stats\' : true,
					\'can_track_pers\' : true,
					\'can_track_market\' : true,
				}
			}
		}

		if ( ! fp.main.is_customizer ) {

			// set MS Ads consent
			
			window.uetq.push( "consent", "default", {
				"ad_storage": "denied"
			});
			
			if ( fpdata.cookies ){
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

				// set defaults to denied

				window[tag_name]("consent", "default", {
					"ad_storage": "denied",
					"ad_user_data" : "denied",
					"ad_personalization" : "denied",
					"analytics_storage": "denied",
					"personalization_storage": "denied",
					"functionality_storage": "denied",
					"security_storage": "granted",
				});

				// update if the user made a choice in the past
				if ( fpdata.cookies ){
					
					window[tag_name]("consent", "update", {
						"ad_storage": fpdata.cookies.marketing ? "granted" : "denied",
						"ad_user_data" : fpdata.cookies.marketing ? "granted" : "denied",
						"ad_personalization" : fpdata.cookies.marketing ? "granted" : "denied",
						"analytics_storage": fpdata.cookies.stats ? "granted" : "denied",
						"personalization_storage": fpdata.cookies.personalisation ? "granted" : "denied",
						"functionality_storage": fpdata.cookies.personalisation ? "granted" : "denied",
						"security_storage": "granted",
					});
					
					if ( fp.main.debug ) console.log("[FP] Google consents set to user choices");
				
				// if no choice was made in the past
				} else {
					
					// agree to all if consent banner is disabled or is in optout or notification mode
					if ( ! ( fp.notice.enabled && fp.notice.mode == "optin" ) ) {
						
						window[tag_name]("consent", "update", {
							"ad_storage": "granted",
							"ad_user_data" : "granted",
							"ad_personalization" : "granted",
							"analytics_storage": "granted",
							"personalization_storage": "granted",
							"functionality_storage": "granted",
							"security_storage": "granted",
						});
					
						if ( fp.main.debug ) console.log("[FP] All Google consents granted");
					};
				}
			} );
			
			// we set URL Passthrough for standard GTAG
			if ( fp?.gtag?.url_passthrough && fp.notice.enabled && ( fp.notice.mode == "optin" || fp.notice.mode == "optout" ) ) {
				window.gtag("set", "url_passthrough", true);
			};
		}
    }
	
	let uses_geo = false;

	FP.postToServer = ( event_data_a, cb = false ) => {

		if ( fpdata.is_robot ) return;
		if ( fp.main.debug ) console.log( "[FP] Posting to server", event_data_a );

		let fetch_url = fp.main.server_method == "rest" ? "/index.php?rest_route=/fupi/v1/sender" : "/wp-admin/admin-ajax.php?action=fupi_ajax";

		if ( fp.main.debug || event_data_a[0][0] == \'cdb\') {
		
			fetch( fetch_url, {
				method: "POST",
				body: JSON.stringify( event_data_a ),
				credentials: \'same-origin\',
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
				credentials: \'same-origin\',
				body: JSON.stringify( event_data_a ),
				headers: {
					"Content-type": "application/json; charset=UTF-8",
					// "X-WP-Nonce": fp_nonce
				}
			});
		}
	};
';
// apply_filters('fupi_add_to_head_js', $output );
$output .= "\r\n\tif ( ! uses_geo ) {\r\n\t\tfpdata.country = 'unknown';\r\n\t\tFP.updateConsents();\r\n\t\tfp.ready = true;\r\n\t}\r\n\r\n})(FP);";