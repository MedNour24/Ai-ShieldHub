$(document).ready(function() {
    $('.accordion-header').click(function() {
      var accordionItem = $(this).parent('.accordion-item');
      var isActive = accordionItem.hasClass('active');

      $('.accordion-item').removeClass('active');
      $('.accordion-header').removeClass('active');
      $('.accordion-content').slideUp();

      if (!isActive) {
        accordionItem.addClass('active');
        $(this).addClass('active');
        $(this).next('.accordion-content').slideDown();
      }
    });
 });