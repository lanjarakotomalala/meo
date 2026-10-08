(function ($) {
  if (window.WP_Grid_Builder != undefined) {
    window.WP_Grid_Builder && WP_Grid_Builder.on('init', function (wpgb) {

      wpgb.facets.on('map.afterInit', function (instance) {
        /*layer.addTo(instance.map);*/

        $('.wp-grid-builder').on('click', '.wpgb-card', function(){
          $('.wp-grid-builder .wpgb-card.active-store').removeClass('active-store');

          const activeStore = $(this);
          activeStore.addClass('active-store');

          const lat = $(this).find('#lat').text();
          const lng = $(this).find('#lng').text();
          const zoom = 30;
          instance.map.setView([lat, lng], zoom);

          instance.map.eachLayer(function (layer) {
            if (layer.hasOwnProperty('feature')) {
              $(layer._icon).trigger('click');
              if (window.screen.height <= 992) {
                $('html').scrollTop(100);
              } else {
                $('html').scrollTop(250);
              }
            }
          });
        });
      });
    });
  }
})(jQuery);
