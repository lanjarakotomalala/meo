// eslint-disable-next-line no-unused-vars
import config from '@config';
import './vendor/*.js';
import '@styles/frontend';
import '@images/favicon.ico';
import 'airbnb-browser-shims';
import './spritesvg';
import stickybits from 'stickybits';
import './cart-updater';
import './checkout/checkout-steps.js';
import './checkout/checkout-steps-login.js';
import './checkout/checkout-steps-billing.js';
import './checkout/checkout-mobile.js';
import './checkout/checkout-coupon.js';
import './checkout/apple-pay-visibility.js';
import './archive/subcategories.js';
import product from './woocommerce/product/add-to-cart';

product();

$.event.special.tap = {
  setup(data, namespaces) {
    const $elem = $(this);
    $elem.bind('touchstart', $.event.special.tap.handler)
      .bind('touchmove', $.event.special.tap.handler)
      .bind('touchend', $.event.special.tap.handler);
  },

  teardown(namespaces) {
    const $elem = $(this);
    $elem.unbind('touchstart', $.event.special.tap.handler)
      .unbind('touchmove', $.event.special.tap.handler)
      .unbind('touchend', $.event.special.tap.handler);
  },

  handler(event) {
    event.preventDefault();
    const $elem = $(this);
    $elem.data(event.type, 1);
    if (event.type === 'touchend' && !$elem.data('touchmove')) {
      event.type = 'tap';
      $.event.handle.apply(this, arguments);
    } else if ($elem.data('touchend')) {
      $elem.removeData('touchstart touchmove touchend');
    }
  },
};

/** *************** */
/** ** Dropdown *** */
/** *************** */
$('body').on('click', '[aria-expanded]', function (event) {
  event.preventDefault();
  event.stopPropagation();

  if ($(this).attr('aria-expanded') === 'false') {
    $(this).attr('aria-expanded', true);
  } else {
    $(this).attr('aria-expanded', false);
  }
});

/** ************* */
/** ** Header *** */
/** ************* */
$('.site-search .btn--close').on('click', (event) => {
  event.preventDefault();
  event.stopPropagation();

  $('.btn--toggle-search').attr('aria-expanded', false);
});

/** ************* */
/** ** Footer *** */
/** ************* */
$(window).resize(resizeFooter);
resizeFooter();

const blogSection = $(".menu-blog-container");

if(blogSection !== null && window.matchMedia("(min-width: 992px)")) {
  const blogSectionTitle = $(".menu-blog-container").prev(".widget__title");
  blogSectionTitle.addClass("widget-title-mobile-blog");
  $('.site-footer .widget_nav_menu div.widget__title.widget-title-mobile-blog').each(
    function () {
      $(this)
        .parent()
        .prepend(
          `<button class="widget__title widget-title-mobile" aria-expanded="false">${$(
            this,
          ).text()}</button>`,
        );
      $(this).remove();
    },
  );
}

function resizeFooter() {
  if ($(window).width() <= 992) {
    $(
      '.site-footer .widget_nav_menu .widget__title:not(.widget-title-mobile)',
    ).each(function () {
      $(this)
        .parent()
        .prepend(
          `<button class="widget__title widget-title-mobile" aria-expanded="false">${$(
            this,
          ).text()}</button>`,
        );
      $(this).remove();
    });
  } else {
    const blogSectionButton = $(".menu-blog-container").prev("button");
    if(blogSectionButton) {
      blogSectionButton.addClass("widget-title-mobile-blog");
    }
    $('.site-footer .widget_nav_menu button.widget-title-mobile:not(.widget-title-mobile-blog)').each(
      function () {
        $(this)
          .parent()
          .prepend(`<h2 class="widget__title">${$(this).text()}</h2>`);
        $(this).remove();
      },
    );
  }
}

/** ********************* */
/** *** SlideshowGrid *** */
/** ********************* */
$(document).ready(() => {
  $('.slider_block_grid').slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 5000,
    infinite: true,
    arrows: false,
    dots: false,
  });
});

/** ********************* */
/** *** Banner  *** */
/** ********************* */
$('.is-style-scroll-to').click(() => {
  const sliderProductPosition = $('.slider_product').offset().top;
  const headerHeight = $('.site-header').height();
  const scrollDistance = sliderProductPosition - headerHeight;
  $('html, body').animate({ scrollTop: scrollDistance }, 400);
  return false;
});
/** ************************ */
/** *** SlideshowProduct *** */
/** ************************ */

$('.slider_product .slider .slider__content').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  autoplay: false,
  autoplaySpeed: 2000,
  arrows: true,
  dots: false,
  prevArrow: '.slider_product-prev-arrow',
  nextArrow: '.slider_product-next-arrow',
  responsive: [
    {
      breakpoint: 768,
      settings: {
        dots: true,
      },
    },
  ],
});

/** **************** */
/** ** Slideshow *** */
/** **************** */
const slideshowTouchPosition = {
  orgX: 0, orgY: 0, x: 0, y: 0,
};
let slideshowInitialPosition = $('.slideshow__content').width() * -1;
let slideshowNewPosition = slideshowInitialPosition;

const slideshowLast = $('.slideshow-item:last-child').clone();
const slideshowFirst = $('.slideshow-item:first-child').clone();

let slideshowCurrent = parseInt($('.slideshow').attr('data-current'));
let slideshowLength = parseInt($('.slideshow').attr('data-length'));

$('.slideshow__content').prepend(slideshowLast);
$('.slideshow__content').append(slideshowFirst);

if (typeof changeSlideshow !== "undefined") {
  changeSlideshow(1, false);
}

let i = 1;

const totalSlides = $('.slideshow .slideshow-item').length;

let pauseSlider = false;

const intervalSlideshow = setInterval(() => {
  if (pauseSlider) {
    return;
  }
  i++;
  if (i > totalSlides) {
    i = 1;
  }
  changeSlideshow(i, true);
}, 5000);

$('.slideshow').on('mouseenter', '.slideshow__content', () => {
  pauseSlider = true;
}).on('mouseleave', '.slideshow__content', () => {
  pauseSlider = false;
});

$(window).on('resize', () => {
  slideshowInitialPosition = $('.slideshow__content').width() * -1;
  slideshowNewPosition = slideshowInitialPosition;
  slideshowCurrent = parseInt($('.slideshow').attr('data-current'));

  changeSlideshow(slideshowCurrent, false);
});

$('.slideshow').on('touchstart mousedown', function (event) {
  if ($(event.target).is('a') || $(event.target).is('.slideshow__navigation button')) {
    return;
  }

  $(this).addClass('is-active');

  if (event.originalEvent.touches != undefined) {
    slideshowTouchPosition.orgX = event.originalEvent.touches[0].pageX;
    slideshowTouchPosition.orgY = event.originalEvent.touches[0].pageY;
  } else {
    slideshowTouchPosition.orgX = event.pageX;
    slideshowTouchPosition.orgY = event.pageY;
  }
});

$(window).on('touchmove mousemove', (event) => {
  if ($('.slideshow.is-active').length > 0) {
    event.preventDefault();
    event.stopPropagation();

    if (event.originalEvent.touches != undefined) {
      slideshowTouchPosition.x = event.originalEvent.touches[0].pageX;
      slideshowTouchPosition.y = event.originalEvent.touches[0].pageY;
    } else {
      slideshowTouchPosition.x = event.pageX;
      slideshowTouchPosition.y = event.pageY;
    }

    slideshowNewPosition = slideshowInitialPosition
        - (slideshowTouchPosition.orgX - slideshowTouchPosition.x);

    $('.slideshow__content').css({
      '-webkit-transform':
            `translate3d(${slideshowNewPosition}px, 0, 0)`,
      '-moz-transform':
            `translate3d(${slideshowNewPosition}px, 0, 0)`,
      '-ms-transform':
            `translate3d(${slideshowNewPosition}px, 0, 0)`,
      '-o-transform': `translate3d(${slideshowNewPosition}px, 0, 0)`,
      transform: `translate3d(${slideshowNewPosition}px, 0, 0)`,
    });
  }
});

$(window).on('touchend mouseup', (event) => {
  if ($('.slideshow.is-active').length > 0) {
    event.preventDefault();
    event.stopPropagation();

    $('.slideshow.is-active').removeClass('is-active');

    slideshowCurrent = parseInt($('.slideshow').attr('data-current'));
    slideshowLength = parseInt($('.slideshow').attr('data-length'));

    if (Math.abs(slideshowNewPosition - slideshowInitialPosition) > 50) {
      if (slideshowNewPosition - slideshowInitialPosition >= 0) {
        slideshowCurrent--;

        if (slideshowCurrent < 0) {
          slideshowCurrent = slideshowLength;
        }
      } else {
        slideshowCurrent++;

        if (slideshowCurrent > slideshowLength + 1) {
          slideshowCurrent = 1;
        }
      }
    }

    changeSlideshow(slideshowCurrent, true);
  }
});

$('.slideshow__navigation button[data-current]').on('click', function (event) {
  event.preventDefault();
  event.stopPropagation();

  changeSlideshow(parseInt($(this).attr('data-current')), true);
});

function changeSlideshow(current, animate) {
  $('.slideshow').attr('data-current', current);

  const newPosition = $('.slideshow__content').width() * -current;
  slideshowInitialPosition = newPosition;
  slideshowNewPosition = newPosition;

  if (animate) {
    $('.slideshow__content').css('transition-duration', '1.5s');
  }

  $('.slideshow__content').css({
    '-webkit-transform': `translate3d(${newPosition}px, 0, 0)`,
    '-moz-transform': `translate3d(${newPosition}px, 0, 0)`,
    '-ms-transform': `translate3d(${newPosition}px, 0, 0)`,
    '-o-transform': `translate3d(${newPosition}px, 0, 0)`,
    transform: `translate3d(${newPosition}px, 0, 0)`,
  });

  slideshowCurrent = parseInt($('.slideshow').attr('data-current'));
  slideshowLength = parseInt($('.slideshow').attr('data-length'));

  $('.slideshow__navigation button').removeClass('is-current');

  if (slideshowCurrent == 0) {
    $(
      `.slideshow__navigation button[data-current="${
        slideshowLength
      }"]`,
    ).addClass('is-current');
  } else if (slideshowCurrent == slideshowLength + 1) {
    $('.slideshow__navigation button[data-current="1"]').addClass(
      'is-current',
    );
  } else {
    $(
      `.slideshow__navigation button[data-current="${current}"]`,
    ).addClass('is-current');
  }

  setTimeout(() => {
    $('.slideshow__content').css('transition-duration', '0s');

    if (slideshowCurrent == 0) {
      changeSlideshow(slideshowLength, false);
    } else if (slideshowCurrent == slideshowLength + 1) {
      changeSlideshow(1, false);
    }
  }, 500);
}

setInterval(() => {
  changeSlideshow(parseInt($('.slideshow').attr('data-current')) + 1, true);
}, 5500);

/** ************************* */
/** ** Coverflow products *** */
/** ************************* */
let coverflowCurrent = parseInt($('.coverflow-products').attr('data-current'));
let coverflowLength = parseInt($('.coverflow-products').attr('data-length'));

const coverflowTouchPosition = {
  orgX: 0, orgY: 0, x: 0, y: 0,
};
let coverflowInitialPosition = 0;

if ($(window).width() > 768) {
  coverflowInitialPosition = ($('.coverflow-products__content').width() / 3) * -2;
} else {
  coverflowInitialPosition = $('.coverflow-products__content').width() * -1;
}

let coverflowNewPosition = coverflowInitialPosition;

const coverflowLast = $('.coverflow-item:last-child').clone();
const coverflowLast2 = $(
  `.coverflow-item:nth-child(${$('.coverflow-item').length - 1})`,
).clone();
const coverflowFirst = $('.coverflow-item:first-child').clone();
const coverflowFirst2 = $('.coverflow-item:nth-child(2)').clone();

$('.coverflow-products__navigation__button--previous').on(
  'click',
  changeCoverflowProducts,
);
$('.coverflow-products__navigation__button--next').on(
  'click',
  changeCoverflowProducts,
);

$('.coverflow-products__content').prepend(coverflowLast);
$('.coverflow-products__content').prepend(coverflowLast2);

$('.coverflow-products__content').append(coverflowFirst);
$('.coverflow-products__content').append(coverflowFirst2);

changeCoverflow(2, false);

function changeCoverflowProducts(event) {
  event.preventDefault();
  event.stopPropagation();

  coverflowCurrent = parseInt($('.coverflow-products').attr('data-current'));
  coverflowLength = parseInt($('.coverflow-products').attr('data-length'));

  if ($(this).hasClass('coverflow-products__navigation__button--previous')) {
    coverflowCurrent--;

    if (coverflowCurrent < 0) {
      coverflowCurrent = coverflowLength;
    }
  } else {
    coverflowCurrent++;

    if (coverflowCurrent > coverflowLength + 1) {
      coverflowCurrent = 1;
    }
  }

  changeCoverflow(coverflowCurrent, true);
}

$(window).on('resize', resizeCoverflow);

function resizeCoverflow() {
  coverflowInitialPosition = $('.coverflow-products__content').width() * -1;
  coverflowNewPosition = coverflowInitialPosition;

  changeCoverflow(
    parseInt($('.coverflow-products').attr('data-current')),
    false,
  );
}

let coverflowTouchStarted = false;

$('.coverflow-products').on('touchstart', (event) => {
  coverflowTouchStarted = true;

  if (event.originalEvent.touches != undefined) {
    coverflowTouchPosition.orgX = event.originalEvent.touches[0].pageX;
    coverflowTouchPosition.orgY = event.originalEvent.touches[0].pageY;
  } else {
    coverflowTouchPosition.orgX = event.pageX;
    coverflowTouchPosition.orgY = event.pageY;
  }
});

$('.coverflow-products').on('touchmove', function (event) {
  if (!coverflowTouchStarted) {
    return;
  }

  $(this).addClass('is-active');

  if ($('.coverflow-products.is-active').length > 0) {
    $('.coverflow-products.is-active').addClass('remove-pointer');

    if (event.originalEvent.touches != undefined) {
      coverflowTouchPosition.x = event.originalEvent.touches[0].pageX;
      coverflowTouchPosition.y = event.originalEvent.touches[0].pageY;
    } else {
      coverflowTouchPosition.x = event.pageX;
      coverflowTouchPosition.y = event.pageY;
    }

    coverflowNewPosition = coverflowInitialPosition
        - (coverflowTouchPosition.orgX - coverflowTouchPosition.x);

    $('.coverflow-products__content').css({
      '-webkit-transform':
            `translate3d(${coverflowNewPosition}px, 0, 0)`,
      '-moz-transform':
            `translate3d(${coverflowNewPosition}px, 0, 0)`,
      '-ms-transform':
            `translate3d(${coverflowNewPosition}px, 0, 0)`,
      '-o-transform': `translate3d(${coverflowNewPosition}px, 0, 0)`,
      transform: `translate3d(${coverflowNewPosition}px, 0, 0)`,
    });
  }
});

$(window).on('touchend touchcancel', (event) => {
  coverflowTouchStarted = false;
  if ($('.coverflow-products.is-active').length > 0) {
    event.preventDefault();
    event.stopPropagation();

    $('.coverflow-products.is-active').removeClass('remove-pointer');
    $('.coverflow-products.is-active').removeClass('is-active');

    coverflowCurrent = parseInt(
      $('.coverflow-products').attr('data-current'),
    );
    coverflowLength = parseInt($('.coverflow-products').attr('data-length'));

    if (Math.abs(coverflowNewPosition - coverflowInitialPosition) > 50) {
      if (coverflowNewPosition - coverflowInitialPosition >= 0) {
        coverflowCurrent--;

        if (coverflowCurrent < 0) {
          coverflowCurrent = coverflowLength;
        }
      } else {
        coverflowCurrent++;

        if (coverflowCurrent > coverflowLength + 1) {
          coverflowCurrent = 1;
        }
      }
    }

    changeCoverflow(coverflowCurrent, true);
  }
});

function changeCoverflow(current, animate) {
  $('.coverflow-products').attr('data-current', current);

  let newPosition = 0;

  if ($(window).width() > 768) {
    newPosition = ($('.coverflow-products__content').width() / 3) * -2
        + ($('.coverflow-products__content').width() / 3) * -(current - 2);
  } else {
    newPosition = $('.coverflow-products__content').width() * -current;
  }

  coverflowInitialPosition = newPosition;
  coverflowNewPosition = newPosition;

  if (animate) {
    $('.coverflow-products__content').css('transition-duration', '0.4s');
  }

  $('.coverflow-products__content').css({
    '-webkit-transform': `translate3d(${newPosition}px, 0, 0)`,
    '-moz-transform': `translate3d(${newPosition}px, 0, 0)`,
    '-ms-transform': `translate3d(${newPosition}px, 0, 0)`,
    '-o-transform': `translate3d(${newPosition}px, 0, 0)`,
    transform: `translate3d(${newPosition}px, 0, 0)`,
  });

  coverflowCurrent = parseInt($('.coverflow-products').attr('data-current'));
  coverflowLength = parseInt($('.coverflow-products').attr('data-length'));

  $('.coverflow-item').removeClass('is-current');
  $(`.coverflow-item[data-item="${current}"]`).addClass('is-current');

  setTimeout(() => {
    $('.coverflow-products__content').css('transition-duration', '0s');

    if (coverflowCurrent == 0) {
      changeCoverflow(coverflowLength, false);
    } else if (coverflowCurrent == coverflowLength + 1) {
      changeCoverflow(1, false);
    }
  }, 300);
}

/** *************** */
/** ** Products *** */
/** *************** */
$('.woocommerce-product-details__quick-links a').on('click', function (event) {
  const _target = $(this).attr('href');

  $('.wc-tab').hide();
  $(_target).show();

  $('.wc-tabs li').removeClass('active');
  $(`.wc-tabs li[aria-controls="${_target.substr(1)}"]`).addClass(
    'active',
  );
});

$('.text-mask button').on('click', function (event) {
  event.preventDefault();
  event.stopPropagation();

  $(this).parents('.text-mask').find('div').addClass('show');
});

if ($('.text-mask div').height() < 70) {
  $('.text-mask div').addClass('show');
}

$('.woocommerce-product-details__quick-links a').on('click', function (event) {
  event.preventDefault();
  event.stopPropagation();

  const _currentLink = $(this).attr('href');

  $('html').scrollTop($(_currentLink).offset().top - 100);
});

if ($('body.single-cartflows_step').length == 1) {
  $('.wcf-product-option-before-customer').append(
    '<button id="fake-next-step" type="button">Étape suivante <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 9 16" style="enable-background:new 0 0 9 16;" xml:space="preserve"><path d="M8.5,8c0,0.2-0.1,0.3-0.2,0.5l-6.7,6.8c-0.3,0.3-0.7,0.3-0.9,0c-0.3-0.3-0.3-0.7,0-1L6.9,8L0.7,1.7c-0.3-0.3-0.3-0.7,0-1 s0.7-0.3,0.9,0l6.7,6.8C8.4,7.7,8.5,7.8,8.5,8z"/></svg></button>',
  );
}

$('body').on('click', '#fake-next-step', (event) => {
  event.preventDefault();
  event.stopPropagation();

  $('.wcf-product-option-before-customer').hide();
  $('.woocommerce-form-login-toggle').show();
  $('#customer_details').show();
  $('#customer_details + .wcf-order-wrap').show();

  $('#checkout-steps .is-current').addClass('is-previous');
  $('#checkout-steps .is-current').next().addClass('is-current');
  $('#checkout-steps .is-previous').removeClass('is-current');

  $(window).scrollTop(0);
});

if ($('.checkout-steps').prev('.wc_points_rewards_earn_points').length) {
  $('.checkout-steps').prev('.wc_points_rewards_earn_points').insertAfter('.checkout-steps');
}

$('.single-cartflows_step .checkout-steps__item:first-child').on('click', (event) => {
  event.preventDefault();
  event.stopPropagation();

  $('.wcf-product-option-before-customer').show();
  $('.woocommerce-form-login-toggle').hide();
  $('#customer_details').hide();
  $('#customer_details + .wcf-order-wrap').hide();

  $('#checkout-steps .is-current').removeClass('is-current');
  $('.checkout-steps__item:first-child').addClass('is-current');
  $('.checkout-steps__item:first-child').removeClass('is-previous');
});

$(document).ready(() => {
  if (window.WP_Grid_Builder != undefined) {
    window.WP_Grid_Builder.instances[1].facets.on('loaded', () => {
      if (typeof fifu_lazy !== 'undefined') {
        setTimeout(() => {
          if (typeof fifu_lazy !== 'undefined') {
            fifu_lazy();
          }
        }, 1000);
      }
    });

    window.WP_Grid_Builder.instances[1].facets.on('refresh', () => {
      if (typeof fifu_lazy !== 'undefined') {
        setTimeout(() => {
          if (typeof fifu_lazy !== 'undefined') {
            fifu_lazy();
          }
        }, 1000);
      }
    });
  }
});

$('.checkout-sticky').wrapInner('<div class="checkout-sticky--inner" />');

function syncOrderTotal() {
  const orderTotal = $('.shop_table.woocommerce-checkout-review-order-table .order-total td').html();
  $('.shoptimizer-sticky-checkout .order-total-value').html(orderTotal);
}

syncOrderTotal();

let stickyInit = false;
let
  orderTotal;
$(document.body).on('updated_checkout', () => {
  syncOrderTotal();
  if (!stickyInit && $('.checkout.woocommerce-checkout .col2-set').outerHeight() > $('.checkout.woocommerce-checkout #order_review').outerHeight()) {
    stickybits('.checkout-sticky--inner', {
      useStickyClasses: true,
    });
    stickyInit = true;
  }
});

$(window).resize(resizeCheckout);
resizeCheckout();

function resizeCheckout() {
  if ($('body.woocommerce-checkout #primary').height() < $(window).height()) {
    const height = $(window).height() - $('body.woocommerce-checkout #primary').offset().top - $('.site-footer-wrapper').height();
    $('body.woocommerce-checkout #primary').css('min-height', height);
  }
}

$('.single-cartflows_step .wcf-product-option-before-customer .wcf-qty-row:not(.wcf-highlight) input[type="checkbox"]').attr('checked', false);
$('.single-cartflows_step .wcf-product-option-before-customer .wcf-qty-row:not(.wcf-highlight) input[type="checkbox"]').trigger('change');

wooQuantityButtons();

function wooQuantityButtons() {
  $('.wcf-qty-selection').parent().addClass('buttons_added');
  $('.wcf-qty-selection').after('<div class="quantity-nav dynamic-qty"><a href="javascript:void(0)" class="quantity-button dynamic-qty-btn quantity-up plus" data-type="plus">&nbsp;</a><a href="javascript:void(0)" class="quantity-button dynamic-qty-btn quantity-down minus" data-type="minus">&nbsp;</a></div>');

  $('.plus, .minus').on('click', function (event) {
    event.preventDefault();
    event.stopPropagation();

    let current = $(this).parents('.wcf-qty').find('.wcf-qty-selection').val();

    if (parseInt(current) == NaN) {
      return;
    }

    if ($(this).is('.plus')) {
      current++;
    } else {
      current--;
    }

    if (parseInt(current) < 1) {
      return;
    }
    $(this).parents('.wcf-qty').find('.wcf-qty-selection').val(current);
    $(this).parents('.wcf-qty').find('.wcf-qty-selection').trigger('change');
  });

  $('.btn--toggle-search').on('click', () => {
    setTimeout(() => {
      $('.search-field').focus();
    }, 500);
  });
}

$(document).ready(() => {
  // Close drawer - click the "Continue shopping" link.
  $('.shoptimizer-mini-cart-wrap').on('click', 'span.btn-continue-shopping', () => {
    $('body').removeClass(['filter-open', 'mobile-toggled', 'drawer-open']);
  });

  $(document).on('click', '.store-products-btn-popup', function () {
    let products = $(this).parents('.leaflet-popup-content').find('.products-container').clone();

    if (products.length === 0) {
      products = $(this).parents('.wpgb-card').find('.products-container').clone();
    }

    const popupProducts = $('.container-store-product-popup');
    popupProducts.removeClass('hidden');
    products.removeClass('hidden');

    popupProducts.find('.store-product-popup').empty().append(products);

    $('html').scrollTop(50);
  });

  $('.container-store-product-popup').on('click', '.close-popup', () => {
    $('.container-store-product-popup').addClass('hidden');
  });

  $('.wpgb-facet').on('change', '.wpgb-range-facet .wpgb-range', function () {
    const facet = $(this).parent();
    const min = facet.find('.wpgb-range-thumb:first-child');
    const max = facet.find('.wpgb-range-thumb:last-child');

    if (min.attr('aria-valuenow') === min.attr('aria-valuemax')) {
      min.css('z-index', 20);
      max.css('z-index', 0);
    }
    if (max.attr('aria-valuenow') === max.attr('aria-valuemin')) {
      max.css('z-index', 20);
      min.css('z-index', 0);
    }
  });
});

const cartSummary = $('.cart-summary-content');
let summaryElement;
const machineStep = $('div[data-step="machine"]');

if (machineStep.length > 0) {
  const machineChoices = machineStep.find('.bundled_product_checkbox');

  // When clicking on the coffee machine choices, disable or not the other choices to avoid selecting more than one machine.
  machineChoices.on('click', function() {
    const thatChoice = $(this);
    if (thatChoice.is(':checked')) {
      machineChoices.each(function() {
        if (thatChoice.attr('name') !== $(this).attr('name')) {
          $(this).attr('disabled', 'disabled');
        }
      });
    } else {
      machineChoices.each(function() {
        $(this).removeAttr('disabled');
      });
    }

  });
}

function displayStepError(formCart, message) {
  const podsStepError = document.createElement('ul');
  podsStepError.classList.add('pods-step-error');
  podsStepError.classList.add('woocommerce-error');
  const podsStepText = document.createElement('li');
  podsStepText.textContent = message;
  podsStepError.appendChild(podsStepText);

  formCart.prepend(podsStepError);
}

function machinesStep(isChecked, formCart) {
  const machineStepChoices = $('div[data-step="machine"]').find('input[type="checkbox"]');

  if (machineStepChoices.length === 0) {
    return true;
  }

  machineStepChoices.each(function () {
    if ($(this).is(':checked') === true) {
      isChecked = true;
      return isChecked;
    }

    if (isChecked) {
      return true;
    }
  });

  if (!isChecked) {
    displayStepError(formCart, 'Vous devez sélectionner une machine à café');
    $('html').scrollTop(500);
  } else {
    return true;
  }

  return false;
}

function coffeeStep(isChecked, formCart, podsStepError) {
  $('div[data-step="coffee"]').find('input[type="checkbox"]').each(function () {
    if ($(this).is(':checked') === true) {
      isChecked = true;
      return isChecked;
    }
  });

  if (isChecked) {
    return true;
  } else {
    if (podsStepError.length > 0) {
      podsStepError.removeClass('hidden');
    } else {
          displayStepError(formCart, 'Vous devez sélectionner au moins un abonnement');
    }

    $('html').scrollTop(500);

    return false;
  }
}

$('#subscription-steps').steps({
  onFinish() {

    const formCart = $('form.cart');
    let price = $('.bundle_wrap > .bundle_price > .price > .bundled_subscriptions_price_html > .bundled_sub_price_html > .woocommerce-Price-amount, .bundle_wrap > .bundle_price > .price > .bundled_subscriptions_price_html > .bundled_sub_price_html > ins > .woocommerce-Price-amount').text();

    // If no coffee item is selected, return to coffee step. Else, add to cart
    if ($('.summary-coffee').children().length === 0) {
      $('li[data-step-target="coffee-"]').trigger('click');
      displayStepError(formCart, 'Vous devez sélectionner au moins un abonnement');
      $('html').scrollTop(500);
    } else if(parseFloat(price.replace(',', '.')) < 20) {
      displayStepError(formCart, 'Vous devez prendre pour un minimum de 20€ d\'abonnement');
    } else {
      $('.single_add_to_cart_button').trigger('click');
    }
  },
  onChange: function (currentIndex, newIndex) {
    const formCart = $('form.cart');

    const podsStepError = $('.pods-step-error');
    if (podsStepError.length > 0) {
      podsStepError.addClass('hidden');
    }

    if ($('.summary-machine').children().length === 0 && currentIndex === 2) {
      $('li[data-step-target="machine"]').trigger('click');
      $('li[data-step-target="accessories"]').removeClass('done');
      displayStepError(formCart, 'Vous devez sélectionner une machine à café');
    }

    let isChecked = false;

    // At first and second step, if visitor not select choice,
    // throw an error message and avoid him to pass the next step
    if (currentIndex === 0 && newIndex === 1) {
      const availableMachines = $('div[data-step="machine"]').find('.toggle-zone .bundled_product');

      if (availableMachines.length > 0) {
          return machinesStep(isChecked, formCart);
      } else {
        return coffeeStep(isChecked, formCart, podsStepError);
      }
    } else if (currentIndex === 1 && newIndex === 2) {
      return coffeeStep(isChecked, formCart, podsStepError);
    } else {
      if (currentIndex > 0) {
        $('html').scrollTop(500);
      }
      return true;
    }
  },

});

if ($('.product-type-bundle').length > 0) {
  $('.bundle-optional-choice').on('click', function () {
    const that = $(this);
    if (that.is(':checked') === true) {
      that.siblings('.details').find('.bundled_product_checkbox').prop('checked', true);
    } else {
      that.siblings('.details').find('.bundled_product_checkbox').prop('checked', false);
    }
  });

  const quantityBoxes = $('.bundled_item_cart_content .bundled_qty');
  quantityBoxes.parent().addClass('buttons_added');
  quantityBoxes.after('<div class="quantity-nav"><a href="javascript:void(0)" class="quantity-button quantity-up plus" data-type="plus">&nbsp;</a><a href="javascript:void(0)" class="quantity-button quantity-down minus" data-type="minus">&nbsp;</a></div>');
}

$('.bundled_product_checkbox').each(function () {
  $(this).prop('checked', false);
});

const $bundle_data = $('.cart.bundle_data');

$bundle_data.on('woocommerce-product-bundle-initializing', function (that) {
  $(this).on('woocommerce-product-bundle-update', (bundleData) => {
    updateBundleCart();
  });
});

function updateBundleCart() {
  $('.cart.bundle_data .cart-summary-content ul:not(.summary-machine)').html('');

  $('.bundled_product_checkbox').each(function () {
    const that = $(this);

    const detailsContainer = that.parents('.details');

    const qty = detailsContainer.find('.bundled_qty').val();

    const productId = detailsContainer.find('div.cart').data('product_id');

    const thisStep = that.parents('div.bundled_item-group').data('step');
    const thisSummary = cartSummary.find(`ul.summary-${thisStep}`);

    summaryElement = thisSummary.find(`li.${productId}`);
    const isInSummary = summaryElement.length;

    if (that.is(':checked')) {
      if (!isInSummary) {
        let element = detailsContainer.find('.bundled_product_title_inner').html();
        that.parents('.bundled_product').find('.cart').addClass('is-mobile');
        thisSummary.append('<li class="' + productId + '">' + element + '<span class="remove-bundle-item">&#10005;</span></li>');
      }
    } else {
      that.parents('.bundled_product').find('.cart').removeClass('is-mobile');
      if (isInSummary > 0) {
        summaryElement.remove();
      }
    }

    if ($('.bundle_price').css('display') === 'none') {
      $('.subtext-bundle-price').removeClass('hidden');
    }
  });
}

$('.quantity-button').on('click', function () {
  const that = $(this);
  const detailsContainer = that.parents('.details');
  const productId = detailsContainer.find('div.cart').data('product_id');
  const thisStep = that.parents('div.bundled_item-group').data('step');
  const thisSummary = cartSummary.find(`ul.summary-${thisStep}`);
  summaryElement = thisSummary.find(`li.${productId}`);
  let element = detailsContainer.find('.bundled_product_title_inner').html();

  if (that.parents('.quantity').find('.bundled_qty').val() === '1') {
    element += ' × 1';
  }

  summaryElement.remove();
  thisSummary.append(`<li class="${productId}">${element}</li>`);
});

// Remove bundle item on cart summary on cross click
$('.cart-summary-content').on('click', '.remove-bundle-item', function () {
  const productId = $(this).parents('li').attr('class');
  const bundleItem = $(`div.cart[data-product_id="${productId}"]`).parents('.bundled_product');
  bundleItem.find('.bundled_product_checkbox').trigger('click');
});

if ($('.product-type-bundle').length > 0) {
  $('.product-type-bundle').addClass('product-type-grouped');
  const baseQty = $('.bundled_item-group.eq-1 .qty.bundled_qty').val();
  $('.bundled_item-group.eq-1 .qty.bundled_qty')
    .val(baseQty)
    .trigger('change');
}

const productAttributesToggle = $('.product-attributes-header button');
if (productAttributesToggle.length > 0) {
  const productAttributes = $('.product-attributes');
  productAttributesToggle.on('click', () => {
    productAttributesToggle.toggleClass('active');
    productAttributes.toggleClass('active');
  });
}

/**
 * Close filters panel on mobile after clicking on apply or delete filters button
 */
$(document).on('click', 'button[name="appliquer_les_filtres"], button[name="supprimer_les_filtres"]', function(e) {
  e.stopPropagation();
  e.preventDefault();
  $('body').toggleClass('filter-open');
});

/** ********************* */
/** *** Sticky Cart  *** */
/** ********************* */
const nativeAddToCart = $('.product-details-wrapper > .summary > #sticky-scroll');
$(window).scroll(function() {
  if ($( window ).width() <= 768) {
    if ($(this).scrollTop() >= 800) {
      $('.sticky_cart').fadeIn( function () {
        $(this).removeClass('hide-sticky-cart');
      });
      nativeAddToCart.hide();
    } else {
      $('.sticky_cart').fadeOut(function() {
        $(this).addClass('hide-sticky-cart');
      });
      nativeAddToCart.show();
    }
  }
});
