/** @format */

jQuery(function ($) {
  $(document).ready(function () {
    // $('[data-wpvs_attribute_name="attribute_pa_kolor"] .vi-wpvs-option-wrap').on('click', function (e) {
    //   var $that = $(this);
    //   var $color = $that.attr('data-attribute_label');
    //   var $label = $('label[for="pa_kolor"]');
    //   $label.text('Kolor: ' + $color);
    // });
    $('.password-wrap button').on('click', function (e) {
      e.preventDefault();
      var wrapper = $(this).closest('.password-wrap');
      var input = $(this).closest('.password-wrap').find('input');
      wrapper.toggleClass('show-pass');
      if (wrapper.hasClass('show-pass')) {
        input.attr('type', 'text');
      } else {
        input.attr('type', 'password');
      }
    });

    $('input#min_price, input#max_price').show();
    $('.price_label').hide();

    //Add text before label to variations
    const row = document.querySelectorAll('.vi-wpvs-variation-style');
    if (row) {
      row.forEach((item) => {
        let variations = item.querySelectorAll('.vi-wpvs-option-wrap');
        let label = item.querySelector('label').innerText;
        label = 'wybierz ' + label;
        item.querySelector('label').innerText = label;
        variations.forEach((variation) => {
          variation.addEventListener('click', (e) => {
            label = label.replace('wybierz ', '');
            item.querySelector('label').innerText = label;
          });
        });
      });
    }
    $('.single_variation_wrap').on('show_variation', function (event, variation) {
      const isNew = variation['_mycheckbox'];
      const badge = '<div class="new-color-badge">Nowy kolor!</div>';
      if (isNew === 'yes') {
        const gallery = $('.woocommerce-product-gallery');
        const badgeContainer = gallery.find('.badge-container');
        console.log(gallery.find('.badge-container').children().length);
        gallery.append(badge);
        if (!gallery.find('.badge-container').children().length) {
          $('.new-color-badge').css({ 'top': '0' });
        } else {
          let top = gallery.find('.badge-container').children().length * 45;
          $('.new-color-badge').css({ 'top': `${top + 25}px` });
        }
      } else {
        $('.new-color-badge').remove();
      }
    });
  });
});
