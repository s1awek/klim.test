function fupi_iframes(){

    let iframesTrigger = false;

    function loadIframe( wrap ){
        
        let iframe_data_el = FP.findFirst('.fupi_iframe_data', wrap);
    
        if ( iframe_data_el ) {
            
            // make sure that the loaded iframe has a "src"
            if ( ! iframe_data_el.hasAttribute('src') && iframe_data_el.dataset.src ) iframe_data_el.setAttribute('src', iframe_data_el.dataset.src);

            // add lazy loading
            let lazy = iframe_data_el.getAttribute('loading') ? '' : fp.notice.iframe_lazy ? ' loading="lazy"' : '',
                new_html = iframe_data_el.outerHTML.replaceAll('<div ','<iframe ' + lazy);

            new_html = new_html.replaceAll('/div>','/iframe>');
            
            // If we have a Gutenberg embed or a Bricks Builder "Video" element...
            if ( wrap.parentElement.classList.contains('wp-block-embed__wrapper') || wrap.parentElement.classList.contains('brxe-video') ){
                // ...create standard iframe. Gutenberg styles make it responsive
                wrap.insertAdjacentHTML('afterend', new_html);
            // ... if not...
            } else {
                // ...create responsive iframe. An FP's wrapper makes it responsive
                let aspect = getIframeAspect(wrap, iframe_data_el);
                wrap.insertAdjacentHTML('afterend', '<div class="fupi_responsive_iframe" style="padding-bottom: ' + aspect + '; position: relative;">' + new_html + '</div>' );
            };

            wrap.remove();
        }
    }

    function get_domain_from_url( url_string ){
        let url = '';
        try {
            url = new URL( url_string );
        } catch (e) {
            if ( fp.main.debug ) console.error('[FP] Incorrect URL provided for iframe. Current value: ' + url_string );
            return false;  
        };
        return url.hostname;
    }

    function getIframeAspect( wrap, iframe_data ){
        
        // if this iframe was embedded with a Gutenberg block
        if ( wrap.parentElement.classList.contains('wp-block-embed__wrapper') ) {
            wrap.classList.add('fupi_fit_to_guten_embed');
            return '0';
        
        // if this iframe was embedded with a Bricks Builder "Video" element
        } else if ( wrap.parentElement.classList.contains('brxe-video') ) {
            wrap.classList.add('fupi_fit_to_bricks_embed');
            return '0';

        // if it wasn't
        } else {

            // if this iframe was embedded with a Brakedance block
            if ( wrap.parentElement.classList.contains('ee-video-container') ) {
                wrap.classList.add('fupi_fit_to_breakdance_embed');
                // set the padding on parent's container to 0
                wrap.parentElement.style.paddingTop = 0;
            }
            
            let width = iframe_data.getAttribute('width'),
                height = iframe_data.getAttribute('height'),
                aspect = width && height ? Math.round( parseInt( height ) / parseInt ( width ) * 10000 ) / 100 + '%' : wrap.dataset.aspect || '56.25%';

            wrap.dataset.aspect = aspect;
            return aspect;
            
        }; // AND IF SOMETHING ELSE: aspect = Math.round ( wrap.parentElement.offsetHeight / wrap.parentElement.offsetWidth * 10000 ) / 100 + '%';
    }

    //
    // GET VIDEO THUMBNAIL -- START
    //

    function getIframeThumbSrc( wrap, iframe_data, src, vid_domain ){

        if ( ! vid_domain ) return false;
        
        if ( vid_domain.includes( 'youtube.com' ) ){
            
            return getYouTubeThumbURL( src );

        } else if ( vid_domain.includes( 'vimeo.com' ) ){

            let vimeoID = extractVimeoId( src );
            if ( ! vimeoID ) return false;

            wrap.id = 'fupi_vimeo_' + vimeoID;
            requestVimeoThumbURL( vimeoID, iframe_data ); // this will request the image from vimeo and replace it in the placeholder after it gets it
            return false;
        }
    }

    function getYouTubeThumbURL( src ){
        const yt_vid = src.match(/(?:\?v=|\/embed\/|\.be\/|\/v\/|\/\d{1,2}\/|\/embed\/|\/shorts\/|\/youtu.be\/|\/watch\?v=|&v=|embed\/|youtu.be\/|v\/|watch\?v=)([^#\&\?]{11,11})/);
        return yt_vid ? `https://img.youtube.com/vi/${yt_vid[1]}/maxresdefault.jpg` : null;
    }

    function extractVimeoId(url) {
        let regex = /video\/(\d+)/,
            match = url.match(regex);
        if (match && match[1]) {
            return match[1];
        };
        return null;
    }

    FP.loadVimeoThumb = function( data ){ // this is added to FP object because it needs to be accessible by the callback in the requestVimeoThumbURL function below
        var id_img = "#fupi_vimeo_" + data[0].id;
        FP.findFirst( id_img + ' .fupi_iframe_placeholder' ).style.backgroundImage = 'url(\'' + data[0].thumbnail_large + '\')';
    }

    function requestVimeoThumbURL( vimeoID, iframe_data ){
        let url = "https://vimeo.com/api/v2/video/" + vimeoID + ".json?callback=FP.loadVimeoThumb";
        FP.getScript( url, false, {async : true} );
    }

    // GET VIDEO THUMBNAIL -- END

    function makeIframePlaceholder(wrap){

        let iframe_data     = FP.findFirst( '.fupi_iframe_data', wrap ),
            src             = iframe_data.getAttribute('src') || iframe_data.dataset.src || false;

        if ( ! src ) return;

        let privacy_url     = wrap.dataset.privacy || fp.notice.privacy_url || false,
            descr_text      = fupi_iframe_texts.iframe_caption_txt,
            aspect          = getIframeAspect( wrap, iframe_data ),
            vid_domain      = get_domain_from_url( src );
            placeholder     = wrap.dataset.placeholder || wrap.dataset.image || getIframeThumbSrc( wrap, iframe_data, src, vid_domain ) || fp.notice.iframe_img,
            src_name        = wrap.dataset.name;

        // make placeholder CSS
        placeholder_img = placeholder ? 'background-image: url(\'' + placeholder + '\');' : '';
        
        // insert the privacy URL and the source name in the description text
        descr_text = privacy_url ? descr_text.replace('{{', '<a href="' + privacy_url + '">').replace('}}', '</a>') : descr_text.replace('{{', '').replace('}}', '')
        descr_text = src_name ? descr_text.replace(/\[\[.*?\]\]/, src_name ) : descr_text.replace('[[', '').replace(']]', '');

        // ALL TOGETHER
        let output = '<div class="fupi_iframe_placeholder" style="padding-bottom: ' + aspect + '; ' + placeholder_img + ' background-size: cover; background-position: center;"><div class="fupi_iframe_content"><div class="fupi_inner"><p class="fupi_iframe_descr">' + descr_text + '</p><p class="fupi_iframe_btn_wrap"><button type="button" class="fupi_iframe_consent_btn" data-vid_domain="' + vid_domain + '">' + fupi_iframe_texts.iframe_btn_text + '</button></p></div></div></div>';

        wrap.insertAdjacentHTML( 'afterbegin', output );
    };

    // This function is triggered after all the iframes in HTML and also by the consent banner
    
    function shouldIframeLoad( wrap, cons_data ){
        
        let should_load = true,
            iframe_data_el = FP.findFirst('.fupi_iframe_data', wrap),
            src = iframe_data_el.getAttribute('src') || iframe_data_el.dataset.src || false;
            
        if ( ! src ) return false;

        if ( ! fpdata.cookies ) {
            should_load = wrap.dataset.pers == '0' && wrap.dataset.stats == '0' && wrap.dataset.market == '0';
        } else {
            if ( 
                ( wrap.dataset.stats == '1' && ! fpdata.cookies.stats ) || 
                ( wrap.dataset.market == '1' && ! fpdata.cookies.marketing ) ||
                ( wrap.dataset.pers == '1' && ! fpdata.cookies.personalisation )
            ) should_load = false;
        };

        if ( ! should_load && cons_data ) {    
            cons_data.forEach( consented_source => {
                if ( src.includes( consented_source ) ) should_load = true;
            } );
        }

        return should_load;
    }

    // INIT
    
    FP.loadIframes = () => {

        let wrappers = FP.findAll('.fupi_blocked_iframe'), // wrappers of blocked iframes
            cons_cookie = FP.readCookie('fp_iframes_consent'),
            cons_data = cons_cookie ? JSON.parse(cons_cookie) : false;

        // FOR EACH WRAPPER
        wrappers.forEach( wrap => {
            
            let should_load = shouldIframeLoad(wrap, cons_data);
                
            if ( should_load ) {
                // load iframe
                if ( ! wrap.classList.contains('fupi_iframe_loaded') ) {
                    wrap.classList.add('fupi_iframe_loaded');
                    loadIframe( wrap );
                }
            } else {
                // load placeholder
                if ( ! wrap.classList.contains('fupi_has_iframe_placeholder') ) {
                    wrap.classList.add('fupi_has_iframe_placeholder');
                    makeIframePlaceholder( wrap );
                }
            }
        } )
    }

    if ( document.readyState === "complete" ) {
        FP.loadIframes();
    } else {
        document.addEventListener('DOMContentLoaded', FP.loadIframes );
    }

	setInterval( ()=>{FP.loadIframes();}, 1000);

    // Load iframes when a "Load content" button is clicked

    document.addEventListener('click', e => {
        
        if ( ! e.target.classList.contains('fupi_iframe_consent_btn') ) return;
            
        // get the iframe's source domain
        let vid_domain = e.target.dataset.vid_domain;
        
        if ( vid_domain ){

            // remember in a cookie that a user agreed to see content from this domain
            let iframe_data_els = FP.findAll('.fupi_iframe_data[src*="' + vid_domain + '"], .fupi_iframe_data[data-src*="' + vid_domain + '"]'),
                cons_cookie = FP.readCookie('fp_iframes_consent');

            if ( cons_cookie ) {
                let cons_data = JSON.parse(cons_cookie);
                cons_data.push(vid_domain);
                FP.setCookie( 'fp_iframes_consent', JSON.stringify(cons_data), 180 );
            } else {
                FP.setCookie( 'fp_iframes_consent', JSON.stringify([vid_domain]), 180 );
            }

            // load all iframes from this source
            iframe_data_els.forEach( el => loadIframe( el.parentElement ) );
        }
    })

    FP.loaded('iframes_blocker');
};

if ( ! fp.main.is_bricks_builder ) FP.load('iframes_blocker', 'fupi_iframes', ['head_helpers']);