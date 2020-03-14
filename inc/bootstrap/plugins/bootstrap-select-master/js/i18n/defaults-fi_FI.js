/*
 * Translated default messages for bootstrap-select.
 * Locale: FI (Finnish)
 * Region: FI (Finland)
 */
(function ($) {
  $.fn.selectpicker.defaults = {
    noneSelectedText: 'Ei valintoja',
    noneResultsText: 'Ei hakutuloksia {0}',
    countSelectedText: function (numSelected, numTotal) {
      return (numSelected == 1) ? "{0} valittu" : "{0} valitut";
    },
    maxOptionsText: function (numAll, numGroup) {
      return [
        (numAll == 1) ? 'Valintojen maksimim‰‰r‰ ({n} saavutettu)' : 'Valintojen maksimim‰‰r‰ ({n} saavutettu)',
        (numGroup == 1) ? 'Ryhm‰n maksimim‰‰r‰ ({n} saavutettu)' : 'Ryhm‰n maksimim‰‰r‰ ({n} saavutettu)'
      ];
    },
    selectAllText: 'Valitse kaikki',
    deselectAllText: 'Poista kaikki',
    multipleSeparator: ', '
  };
})(jQuery);
