define(['jquery'], function ($) {
  var elements = {};

  var Auth = function () {
    var toCheck;
    // credentials form
    if ($('.auth-body form.credentials').length > 0) {
      elements.currentForm = $('.auth-body form.credentials');
      elements.email = elements.currentForm.find('input[name="email"]');
      toCheck = this.checkInputs;
    } else if ($('.auth-body form.confirm-password').length > 0) { //confirm password
        elements.currentForm = $('.auth-body form.confirm-password');
        elements.confirmPassword = elements.currentForm.find('input[name="confirm"]');
        toCheck = this.checkResetPasswords;
    } 

    // return false if there isn't a form
    if (!elements.currentForm) {
      return;
    }
    // common elements
    elements.submit = elements.currentForm.find('input[type="submit"]');
    elements.password = elements.currentForm.find('input[type="password"]');
    elements.currentForm.find('.to-check').on('keyup change', toCheck);

  };

  Auth.prototype.checkInputs = function () {

    if (checkEmail(elements.email.val()) && elements.password.val() !== '') {
      // this is inside the first if to not affect the else behaviour
      if (elements.submit.hasClass('inactive')) {
        elements.submit.removeClass('inactive').removeAttr('disabled');
      }
    } else {
      elements.submit.addClass('inactive').prop('disabled', true);
    }

    function checkEmail (email) {
      var regEx = new RegExp(/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/);
      return regEx.test(email);
    }
  };

  Auth.prototype.checkResetPasswords = function () {
    var password = elements.password.val(),
        confirm = elements.confirmPassword.val(),
        submit = elements.submit;

    if (password.length > 7 && password === confirm && hasNumbersAndLetters(password)) {
      // this is inside the first if to not affect the else behaviour
      if (submit.hasClass('inactive')) {
        submit.removeClass('inactive').removeAttr('disabled');
      }
    } else {
      submit.addClass('inactive').prop('disabled', true);
    }
    // check if the password has letter and numbers
    function hasNumbersAndLetters (str) {
      var regex = /(?:[A-Za-z].*?\d|\d.*?[A-Za-z])/;
      return !!str.match(regex);
    }
  };
  // check contact is selected (fix for ipad and browsers that are not checking the required)
  Auth.prototype.checkSelectedCentre = function () {
    if (elements.studyCentre.val() !== '') {
      if (elements.submit.hasClass('inactive')) {
        elements.submit.removeClass('inactive').removeAttr('disabled');
      }
    } else {
      elements.submit.addClass('inactive').prop('disabled', true);
    }
  }

  return Auth;
})
