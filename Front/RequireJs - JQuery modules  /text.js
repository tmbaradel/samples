define(['jquery', '../common/common'], function ($, common) {
  var elements = {};

  var Text = function () {
    elements.changetarget = $('.content-wrapper');
    elements.items = $('.site-navigation').find('li a');
    // maybe there is a better way to get these elements (updated in dom from ajax)?
    $('body').on('click', '.template-text a', this.changePage);

  };

  Text.prototype.changePage = function (event) {
    var self = $(this);
    event.preventDefault();
    common.ajaxCall(self.attr('href'), elements.changetarget, function() {
      common.menuInit(elements.items);
    });
  }

  return Text;
})
