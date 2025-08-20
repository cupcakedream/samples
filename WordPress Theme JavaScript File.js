jQuery(window).load( function() {

    // Fan Art Masonry Grid
    jQuery('.dt-art-grid').colcade({
        columns: '.dt-col-1-5',
        items: 'article'
    });

});

jQuery(document).ready( function() {

    jQuery('.dt-social-share a').click(function(e) {
        window.open(jQuery(this).attr('href'), 'fbShareWindow', 'height=450, width=550, top=' + (jQuery(window).height() / 2 - 275) + ', left=' + (jQuery(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
        return false;
    });

    jQuery(".dt-scroll-to-top").click( function() {
        jQuery("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
    });

    jQuery(window).scroll(function (event) {
        var scroll = jQuery(window).scrollTop();
        if( scroll > 1200 ) {
            jQuery('.dt-scroll-to-top').addClass('dt-active');
        } else {
            jQuery('.dt-scroll-to-top').removeClass('dt-active');
        }
    });

    jQuery("#dt-verify-phone .button").click(function(e) {
        jQuery(this).val('Calling You Now');
        e.preventDefault(), dt_verify_phone();
    });

    function dt_verify_phone() {
        jQuery('#dt-verify-phone').addClass('dt-hide');
        jQuery('#dt-verify-code').removeClass('dt-hide');
        
        // Show loading state
        jQuery('#dt-verify-code .button').val('Verifying...').prop('disabled', true);
        
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/toasted-v3.0/library/twilio/call.php",
            data: { auth_phone_number: jQuery("#dt-verify-phone input").val() },
            timeout: 30000, // 30 second timeout
            success: function(response) {
                if ( response.verification_code == "verified" ) {
                    jQuery('.dt-phone-auth').addClass('dt-verified');
                    jQuery('.submit').removeClass('dt-hide');
                } else if ( response.verification_code ) {
                    jQuery('#dt-verify-code .code').val(response.verification_code);
                    dt_check_status();
                } else {
                    // Handle unexpected response
                    console.error('Unexpected response:', response);
                    jQuery('.dt-phone-auth').addClass('dt-failed');
                    jQuery('#dt-verify-code .button').val('Try Again').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Phone verification failed:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                
                // Reset button state
                jQuery('#dt-verify-code .button').val('Try Again').prop('disabled', false);
                
                // Show user-friendly error message
                if (status === 'timeout') {
                    jQuery('#dt-verify-code').append('<p class="error-message">Request timed out. Please try again.</p>');
                } else if (status === 'error') {
                    jQuery('#dt-verify-code').append('<p class="error-message">Verification failed. Please check your phone number and try again.</p>');
                } else {
                    jQuery('#dt-verify-code').append('<p class="error-message">An error occurred. Please try again later.</p>');
                }
                
                jQuery('.dt-phone-auth').addClass('dt-failed');
            }
        });
    }

    function dt_check_status() {
        jQuery.ajax({
            type: "POST",
            url: "/wp-content/themes/toasted-v3.0/library/twilio/status.php",
            data: { auth_phone_number: jQuery('#dt-verify-phone input').val() },
            success: function(e) {
                dt_update_status(e.status);
            },
            error: function(e, t, r) {
                console.log('Verification Error',e);
                jQuery('.dt-phone-auth').addClass('dt-failed');
            }
        });
    }

    function dt_update_status( status ) {
        if ( status == "unverified" ) {
            setTimeout( dt_check_status, 3e3 );
        } else {
            jQuery('.dt-phone-auth').addClass('dt-verified');
            jQuery('.submit').removeClass('dt-hide');
        }
    }

    jQuery('#dt-faq .dt-question').click( function() {
        $element = jQuery(this).parent();
        jQuery(".dt-faq.dt-active").not($element).removeClass('dt-active');
        $element.toggleClass('dt-active');
    });

    // Membership Plan Select Mobile
    jQuery('#dt-membership .dt-select a:not(.disabled)').click( function() {
        var plan = jQuery(this).attr('class');
        jQuery('.dt-col-1-3, .dt-button, .dt-select a').removeClass('dt-active');
        jQuery('.' + plan).addClass('dt-active');
    });

    // Live Show Notice
    jQuery.ajax({
        url : '/wp-admin/admin-ajax.php', type : 'get',
        data : { action : 'dt_on_air' }, success : function(id) {
            var prev_notice_date = parseInt(getCookie('dt-live-notice'));
            if ( id && ( !prev_notice_date || Date.now() > prev_notice_date ) ) {
                if ( window.outerWidth > 800 ) {
                    jQuery('.dt-live-notice').addClass('dt-active');
                    dt_set_content_position();
                    dt_link_live_show(id);
                } else {
                    jQuery('.dt-mobile-nav-center a').attr('href', '?p=' + id );
                    jQuery('.dt-mobile-nav-center a').addClass('dt-active').text('ON AIR!');
                }

            } else if (id) {
                dt_link_live_show(id);

            } else {
                dt_show_notices();
            }
        }
    });

    // Regular Notices
    function dt_show_notices() {
        if (  jQuery('.dt-notice').length > 0 ) {
            var $notice = jQuery('.dt-notice');
            var notice_date = parseInt($notice.data('date'));
            var prev_notice_date = parseInt(getCookie('dt-notice'));
            if ( !prev_notice_date || notice_date > prev_notice_date )
                $notice.addClass('dt-active');
            dt_set_content_position();
        }
    }

    // Close Notices
    jQuery('#dt-notices .dt-notice-close').click( function() {
        if ( jQuery('.dt-notice').hasClass('dt-active') ) {
            jQuery('.dt-notice').removeClass('dt-active');
            var notice_date = jQuery('.dt-notice').data('date');
            setCookie( 'dt-notice', notice_date, 30 )
        } else {
            jQuery('.dt-live-notice').removeClass('dt-active');
            var tomorrow = new Date(Date.now() + 24*3600*1000);
            setCookie( 'dt-live-notice', tomorrow.getTime(), 30 );
        }
        dt_set_content_position();
    });

    function dt_initialize_slideshow() {

        // Initialize Slideshow Var
        var $carousel = jQuery('.owl-carousel');

        // Ininitial Slideshow
        $carousel.owlCarousel({
            center:true, items:1, video:true, loop:false,
            margin:0, dots:false, nav: true, autoHeight:true,
            responsive:{ 1024:{ items:1.2, nav:true }, 640:{ items:1.3, nav:true } },
            navText: ["<span class='dt-icon'>chevron_left</span>",
                "<span class='dt-icon'>chevron_right</span>"],
        });

        // Stop Video When Slideshow Loads
        $carousel.on('initialized.owl.carousel', function(event) {
            if ( jQuery(window).width() > 800 ) {
                jQuery('.owl-item:not(.center) iframe').each( function() {
                    var id = jQuery(this).attr('id');
                    if ( id && window.dt_ytplyr[id].playVideo )
                        window.dt_ytplyr[id].pauseVideo();
                });
            }
        });

        // Play Video When Slide Changes
        $carousel.on('translated.owl.carousel', function(event) {
            if ( jQuery(window).width() > 800 ) {
                var id = jQuery('.owl-item.center iframe').attr('id');
    			if ( id && window.dt_ytplyr[id].playVideo )
    				window.dt_ytplyr[id].playVideo();
            }
        })

        // Stop Video When Slide Changes
        $carousel.on('changed.owl.carousel', function(event) {
            if ( jQuery(window).width() > 800 ) {
                var id = jQuery('.owl-item.center iframe').attr('id');
    			if ( id && window.dt_ytplyr[id].playVideo )
    				window.dt_ytplyr[id].pauseVideo();
            }
        });

    }

    function dt_load_video() {

        if ( typeof(YT.Player) !== 'undefined' ) {
            window.dt_ytplyr = {};
            jQuery('.dt-yt-video').each( function(i,e) {
                var id = jQuery(this).attr('id');
                window.dt_ytplyr[id] = new YT.Player( id, {
                    height: '600',
                    width: '100%',
                    videoId: id,
                    playerVars: { rel : 0, controls : 0, showinfo : 0 },
                    events: {
                        'onReady': function() {
                            if ( jQuery(window).width() > 800 ) {
                                window.dt_ytplyr[id].playVideo();
                                window.dt_ytplyr[id].mute();
                            }
                        },
                    }
                });
            });

            dt_initialize_slideshow();

        } else {
            setTimeout(dt_load_video, 300);
        }

    }

    if ( jQuery('body').hasClass('page-template-home') ) {
        dt_load_video();
    }

    // Mute/Unmute Slideshow Video
    jQuery(".dt-video-unmute").on("click", function(){
        var id = jQuery('.owl-item.center iframe').attr('id');
        !jQuery(this).hasClass('dt-active') ? window.dt_ytplyr[id].unMute() : window.dt_ytplyr[id].mute();
        jQuery(this).toggleClass('dt-active');
    });

    // Homepage Schedule
    jQuery('#dt-home .dt-sched-day').click( function(e) {
        var day = jQuery(this).attr('href');
        var selector = '.dt-sched-day.' + day + ', .dt-sched-show-day.' + day;
        jQuery('.dt-sched-day, .dt-sched-show-day').removeClass('dt-active');
        jQuery(selector).addClass('dt-active');
        e.preventDefault();
    });

    // Show Search Field
    jQuery('#dt-header .dt-mobile-search').click( function() {
        jQuery('#dt-header, .dt-content').toggleClass('dt-search-open');
        return false;
    });

    // Close Search Field
    jQuery('#dt-header .dt-search-close').click( function() {
        jQuery('#dt-header, .dt-content').toggleClass('dt-search-open');
        return false;
    });

    // Open Sidebar Menu
    jQuery('#dt-header .dt-nav-icon, .dt-sidebar-overlay').click( function() {
        jQuery('body').toggleClass('dt-nav-open');
    });

    // Open Pop-up
    jQuery('.dt-pop-open').click( function() {
        var popup = jQuery(jQuery(this).attr('href'));
        jQuery('body,html').css({ 'overflow' : 'hidden' });
        popup.toggleClass('dt-active');
    });

    // Close Pop-up
    jQuery('.dt-pop-close, .dt-pop-bg').click( function() {
        jQuery('body,html').css({ 'overflow' : 'inherit' });
        jQuery('.dt-pop-window').removeClass('dt-active');
    });

    // Open Log In
    var url = window.location.href;
    if( url.indexOf('?login=failed') != -1 ) {
        jQuery('body,html').css({ 'overflow' : 'hidden' });
        jQuery('#dt-log-in').toggleClass('dt-active');
    }

    // Set Content Position
    function dt_set_content_position() {

        var adminbar_height = notice_height = 0;

        if ( jQuery('body').hasClass('admin-bar') ) {
            var adminbar_height = jQuery('#wpadminbar').height();
            jQuery('#dt-notices').css({ 'margin-top' : adminbar_height + 'px' });
        }

        var notice_height = jQuery('#dt-notices').height();

        jQuery('#dt-header, .dt-sidebar-wrap, .dt-content').each( function() {
            jQuery(this).css({ 'margin-top' : ( adminbar_height + notice_height ) + 'px' });
        });

    }

    jQuery(window).on('resize', function(){
        dt_set_content_position();
    });

    function dt_link_live_show(id) {
        jQuery('.dt-live-link').attr('href', '?p=' + id );
        jQuery('.dt-live-text').each( function() {
            if ( jQuery(this).data('id') == id ) {
                jQuery(this).append(' - LIVE');
            }
        });
    }

});

function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function eraseCookie(name) {
    document.cookie = name+'=; Max-Age=-99999999;';
}
