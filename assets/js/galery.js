$(document).ready(function() {
    // Assign some jquery elements we'll need
    var $swiper = $(".gallery-top");
    var $bottomSlide = null; // Slide whose content gets 'extracted' and placed
    // into a fixed position for animation purposes
    var $bottomSlideContent = null; // Slide content that gets passed between the
    // panning slide stack and the position 'behind'
    // the stack, needed for correct animation style
  
    var mySwiper = new Swiper(".gallery-top", {
      spaceBetween: 20,
      slidesPerView: 3,
      parallax: true,
      centeredSlides: true,
      loop: true,
      autoplay: true,
      autoplaySpeed: 20,
      slideToClickedSlide: true,
      paginationClickable: true,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev"
      },
      breakpoints: {
           1920: {
            slidesPerView: 3,
             spaceBetween: 20,
           },
           1400: {
             slidesPerView: 3,
             spaceBetween: 20,
             centeredSlides: true
           },
           900: {
             slidesPerView: 3,
             spaceBetween: 15,
             centeredSlides: true
           },
           200: {
             slidesPerView: 2,
             spaceBetween: 15
           }
      },
    });
  });