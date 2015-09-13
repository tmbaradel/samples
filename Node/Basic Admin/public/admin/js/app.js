(function($) {
  function loadModule (libName) {
    return $.ajax({
      url: '/admin/vendor/js/' + libName + '.js',
      dataType: 'script'
    });
  }

  function Admin () {
    var hooks = {
      
    };

    return {
      init: function () {
        if (!hooks) {
          return;
        }

        for (var hook in hooks) {
          var $els = jQuery(hook) || [];
          if ($els.length > 0) {
            hooks[hook].call(this, $els);
          }
        }
      }
    };
  }

  $(document).ready(function () {
    (new Admin).init();
  });

})(jQuery);
