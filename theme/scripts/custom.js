$(document).ready(function() {
    $(window).scroll(function(){
        var sticky = $('body'),
            scroll = $(window).scrollTop();
      
        if (scroll > 0) sticky.addClass('site-header-sticky');
        else sticky.removeClass('site-header-sticky');
    });
});

$(function () {
    var testimonial = $('.testimonial');
    testimonial.each(function (index) {
        var id = 'testimonial-' + index + (Math.random() + 1).toString(36).substring(7)
        $(this).attr('id', id)
        var list_content = $(this).find('.testimonial__list');
        var list_image = $(this).find('.testimonial__image--list');
        var selectTo = function (select) {
            return '#'+id + ' ' + select
        }
        list_content.slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            asNavFor: selectTo('.testimonial__image--list'),
            dots: true,
            arrows: false,
            centerMode: true,
            focusOnSelect: true
        });
        list_image.slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            asNavFor: selectTo('.testimonial__list'),
            arrows: false,
            fade: true,
        });

    })
})