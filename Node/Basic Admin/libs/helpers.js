var bcrypt = require('bcrypt');
var crypto = require('crypto');
var mongoose = require('mongoose');
var moment = require('moment');
var cheerio = require('cheerio');
var handlebars = require('handlebars');

var _ = require('lodash');

var config = require('./config');

var errorsConfig = require('../config/common/errors.json');

var helpers = {
  toObjectId: function (stringId) {
    return mongoose.Types.ObjectId(stringId);
  },
  currentTimestamp: function () {
    return Math.round(Date.now() / 1000);
  },
  validateEmail: function (email) {
    return /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email);
  },
  validateLatitude: function (coordinate) {
    return typeof coordinate === 'number' && coordinate >= -90 && coordinate <= 90;
  },
  validateLongitude: function (coordinate) {
    return typeof coordinate === 'number' && coordinate >= -180 && coordinate <= 180;
  },
  // tests against an array of objects to find out whether a key:value is matched
  hasKeyWithValue: function (array, key, value) {
    if (typeof array === 'object' && array.length) {
      for (var i = 0; i < array.length; i++) {
        if (array[i][key] === value) {
          return true;
        }
      }
    }
    return false;
  },
  hashPassword: function (password, callback) {
    bcrypt.hash(password, config.password_salt, function (err, hash) {
      if (err) {
        callback(err);
      } else {
        callback(null, hash);
      }
    });
  },
  md5: function (str) {
    if (typeof str !== 'string') {
      str = '';
    }

    return crypto.createHash('md5').update(str).digest('hex');
  },
  getFormattedDate: function (date) {
    if (typeof date === 'object') {
      return moment(date).format('DD/MM/YYYY');
    }
    return '';
  },
  getNotNullVariablesNames: function (list) {
    var filteredList = [];

    if (list && typeof list.forEach === 'function') {
      list.forEach(function (item) {
        if (item.value && ['0', '0 Day', 'false'].indexOf(item.value) === -1) {
          filteredList.push(item.name);
        }
      });
    }

    return filteredList;
  },
  // Elaborate error
  getErrorStr: function (str) {
    if (typeof str !== 'string' || str === '') {
      return '';
    }
    return str.charAt(0).toUpperCase() + str.slice(1).replace(/_/g, ' ');
  },
  // Is Mime an image
  isMimeImage: function (mimetype) {
    var types = ['image/png', 'image/jpeg', 'image/gif'];
    return types.indexOf(mimetype) !== -1;
  },
  isMimeCSV: function (mimetype) {
    var types = ['text/csv', 'application/csv', 'application/octet-stream'];
    return types.indexOf(mimetype) !== -1;
  }
};

module.exports = helpers;
