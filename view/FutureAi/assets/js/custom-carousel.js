$(document).ready(function() {
    var owl = $('.follow-section .owl-carousel');
    owl.owlCarousel({
        margin: 10,
        nav: false,
        loop: true,
        dots: true,
        autoplay: true,
        autoplayTimeout: 4500,
        responsive: {
            0: {
                items: 1
            },
            360: {
                items: 2
            },
            576: {
                items: 3
            },
            768: {
                items: 4
            },
            992: {
                items: 5
            },
            1200: {
                items: 6
            }
        }
    })
})

$(document).ready(function() {
    var owl = $('.integration-section .owl-carousel');
    owl.owlCarousel({
        margin: 10,
        nav: false,
        loop: true,
        dots: true,
        autoplay: true,
        autoplayTimeout: 4500,
        responsive: {
            0: {
                items: 1
            },
            576: {
                items: 2
            },
            768: {
                items: 3
            },
            992: {
                items: 4
            },
            1500: {
                items: 5
            }
        }
    })
})