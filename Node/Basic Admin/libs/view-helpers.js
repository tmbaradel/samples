var moment = require('moment');
var config = require('./config');

module.exports = {
  timeAgo: function (date) {
    return moment(date).fromNow();
  },
  ucFirst: function (value) {
    if (typeof value === 'string') {
      return value.charAt(0).toUpperCase() + value.slice(1);
    }
    return '';
  },
  valueInArray: function (array, needle) {
    if (typeof array === 'object' && typeof array.indexOf === 'function') {
      return array.indexOf(needle) !== -1;
    }
    return false;
  },
  checkInArray: function (trials, trial) {
    if (trials.length > 0 ){
      if (trials.indexOf(trial) !== -1 ){
        return true;
      }
    }
    return false;
  },
  getFormattedDate: function (date) {
    if (typeof date === 'object') {
      return moment(date).format('DD/MM/YYYY');
    }
    return false;
  },
  getDateTimeAgo: function (date) {
    if (typeof date === 'object') {
      return moment(date).fromNow();
    }
    return false;
  },
  getString: function (value) {
    return value.toString();
  },
  subString: function (value, length) {
    if (typeof value === 'string') {
      if (value.length > length) {
        value = value.substr(0, length) + '&hellip;';
      }
    }
    return value;
  },
