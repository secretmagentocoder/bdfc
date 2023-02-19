define(['jquery', 'slick'], function($){
    "use strict";

    jQuery(document).ready(function(){

        // bestsellers_slider
        jQuery('.bestsellers_slider').slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            autoplay: false,
            infinite: false,
            dots: false,
            arrows: true,
            
            responsive: [
                {
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 3,
                        infinite: false,
                        slidesToScroll: 1,
                        arrows: true
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 2,
                        infinite: false,
                        slidesToScroll: 1,
                        arrows: true
                    }
                },
                {
                    breakpoint: 479,
                    settings: {
                        slidesToShow: 1,
                        infinite: false,
                        slidesToScroll: 1,
                        arrows: true
                    }
                }
            ]
        });

        // recently_viewed_slider
        jQuery('.recently_viewed_slider').slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            autoplay: false,
            infinite: false,
            dots: false,
            arrows: true,
            
            responsive: [
                {
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 3,
                        infinite: false,
                        slidesToScroll: 1,
                        arrows: true
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 2,
                        infinite: false,
                        slidesToScroll: 1,
                        arrows: true
                    }
                },
                {
                    breakpoint: 479,
                    settings: {
                        slidesToShow: 1,
                        infinite: false,
                        slidesToScroll: 1,
                        arrows: true
                    }
                }
            ]
        });
        
        // featured_brand_slider
        jQuery('.featured_brand_slider').slick({
            centerMode: false,
            slidesToShow: 3,
            slidesToScroll: 1,
            autoplay: false,
            infinite: false,
            dots: false,
            arrows: true,
            
            responsive: [
                {
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 3,
                        infinite: false,
                        slidesToScroll: 1,
                        arrows: true
                    }
                },
                {
                    breakpoint: 767,
                    settings: {
                        centerMode: true,
                        slidesToShow: 2,
                        infinite: false,
                        slidesToScroll: 1,
                        arrows: true
                    }
                },
                {
                    breakpoint: 479,
                    settings: {
                        centerMode: true,
                        slidesToShow: 1,
                        infinite: false,
                        slidesToScroll: 1,
                        arrows: true
                    }
                }
            ]
        });
        
        // togglePassword
        $(document).on('click', '#togglePassword', function(){
            $(this).toggleClass("active");
            var password_attr = $("#password").attr("type");
            if (password_attr == "password") {
                $("#password").attr("type", "text");
            } else {
                $("#password").attr("type", "password");
            }
        });

        // custom_popup_close
        $(document).on('click', '.custom_popup_close', function(){
            $(".custom-modal").removeClass("open");
        });

        // shopping_experience_rate
        $(document).on('click', '#shopping_experience_rate', function(){
            $("#shopping_experience_rate_popup").addClass("open");
        });

        // collection_procedure_video
        $(document).on('click', '#collection_procedure_video', function(){
            $("#collection_procedure_video_popup").addClass("open");
        });

        // customer_terms_and_conditions
        $(document).on('click', '#customer_terms_and_conditions', function(){
            $("#customer_terms_and_conditions_popup").addClass("open");
        });

    });

    return function myscript()
    {
        alert('hello myscript');
        //put all your mainjs code here
    }
});