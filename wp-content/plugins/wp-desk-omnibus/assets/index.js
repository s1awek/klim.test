/**
 * @typedef {Object} PriceEntity
 * @property {(string|undefined)} price
 * @property {(string|undefined)} date
 */

(function($) {
  const currentVariationElement = $('.variations_form input[name=variation_id]')
  if (currentVariationElement.length === 0) return
  const priceDataElement = $('#omnibus-price-data')
  if (priceDataElement.data('variations_data') === false) return

  /**
   * @param {string|number} variationId
   * @returns {PriceEntity|false}
   */
  function getVariationData(variationId) {
    const variationsDataJson = priceDataElement.data("variations_data")
    if (variationsDataJson[variationId] === undefined)
      throw new Error(`Cannot get variation data for offset "${variationId}"`)

    return variationsDataJson[variationId]
  }

  /** @param {PriceEntity} variationData */
  function updateVariation(variationData) {
    priceDataElement
        .find('.js-omnibus-price')
        .html(variationData.price)

    priceDataElement
        .find('.js-omnibus-date')
        .text(variationData.date)
  }

  function displayVariationPrice() {
    const currentVariationId = currentVariationElement.val()
    if (typeof currentVariationId !== 'string' || currentVariationId === '') {
      priceDataElement.hide()
      return
    }

    try {
      const variationData = getVariationData( currentVariationId );
      if (variationData === false) {
        priceDataElement.hide()
        return
      }

      updateVariation( variationData )
      priceDataElement.show()
    } catch ( e ) {
      priceDataElement.hide()
      console.warn(e.message)
    }
  }

  currentVariationElement.on('change', displayVariationPrice)
  displayVariationPrice()
})(jQuery)
