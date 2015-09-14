
define(['jquery', '../common/common'], function ($, common) {

  var elements = {};

  var Navigation = function (element) {

    // Bind elements
    elements.source = $('.site-toggle');
    elements.target = element;
    elements.items = element.find('li > a');
    elements.changetarget = $('.content-wrapper');
    elements.changeLangSelect = $('select[name=lang]');

    // Toggle event
    elements.source.on('click', this.toggle);
    elements.items.on('click', this.view);
    elements.changeLangSelect.on('change', this.changeLang);

    common.menuInit(elements.items);
    common.bindPopState();
  };

  Navigation.prototype.toggle = function (event) {

    // Prevent default behaviour
    event.preventDefault();

    // Toggle the state classes on source and target
    elements.source.toggleClass('is-active');
    elements.target.toggleClass('is-open');

  };

  Navigation.prototype.changeLang = function (event) {
    $('form[name=change-lang]').submit();
  };

  Navigation.prototype.view = function (event) {
    // Get self and children
    var self = $(this),
        menu = self.closest('li'),
        menuSiblings = menu.siblings(),
        children = self.next('ul'),
        parent = self.closest('ul'),
        parentLink = parent.prev('a'),
        parentSiblings = parent.closest('li').siblings(),
        level;

    // Determine if child menu has the active page
    function hasActivePage () {
      var activePage = false;

      // If `is-active` link does not have a child menu
      // it is the current active link
      children.find('.is-active').each(function () {
        if (!$(this).next().length) {
          return activePage = true;
        }
      });

      return activePage;
    }

    function updateMenu () {
      elements.source.removeClass('is-active');
      elements.target.removeClass('is-open');
    }

    function updateParent () {
      common.clearActiveLinks(parentSiblings);
      common.changeActiveLink(parentLink);
      common.menuCloseChildren(parentSiblings);
    }

    function updateActiveLink (pageChanged) {
      if (pageChanged) {
        common.clearActiveLinks(menuSiblings);
      }

      common.changeActiveLink(self);
      common.menuCloseChildren(menuSiblings);
    }

    function updateOpenState () {

      // If child menu is closed and contains the active page
      // Style child menu to `contains-closed-active`
      if (!children.hasClass('is-open') && hasActivePage()) {
        menuSiblings.removeClass('contains-closed-active');
        menu.addClass('contains-closed-active');
      } else {
        menu.removeClass('contains-closed-active');
      }
    }

    // Add a class to the parent `ul` of the active page
    function updateActiveSection () {
      elements.target.find('.is-active-section').removeClass('is-active-section');

      // Determine the `ul` containing the active
      // If child menu is opened and it contains the active page
      // Child menu should be styled as the active section
      // Not the current level
      if (children.hasClass('is-open') && hasActivePage()) {
        children.addClass('is-active-section');
      } else {
        parent.addClass('is-active-section');
      }
    }

    // if prevent default is needed
    if (!self.hasClass('visit') && !self.hasClass('no-ajax')) {
      event.preventDefault();

      level = parseInt(self.closest('ul').attr('class').split('-')[1], 10);

      // In addition to +2 level elements
      // 2nd level elements without children
      // should also trigger AJAX call
      if (level > 2 || (level === 2 && !children.length)) {
        common.ajaxCall(self.attr('href'), elements.changetarget);

        // Closes the menu on mobile
        updateMenu();

        updateParent();
        updateActiveLink(true);

      } else if (level === 1) { //close all the other elements

        // temporary fix for the simulation (the navigation will be modify soon)
        if (self.hasClass('contact')) {
          common.ajaxCall(self.attr('href'), elements.changetarget);

          // Closes the menu on mobile
          updateMenu();
        }

        updateActiveLink(true);

      } else {
        updateActiveLink();
      }
    }

    // Modify states
    if (children.length) {
      // Toggle visibility
      children.toggleClass('is-open');
    }

    updateOpenState();
    updateActiveSection();

  };

  return Navigation;

});
