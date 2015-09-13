// var redis = require('redis');
// var url = require('url');

// var config = require('./config');

// var redisURL = url.parse(config.redis_url);

// var client = redis.createClient(redisURL.port, redisURL.hostname, {no_ready_check: true});

// if (redisURL.auth) {
//   client.auth(redisURL.auth.split(":")[1]);
// }

// client.on('connect', function () {
//   console.log('[REDIS] Is connected');
// });

// client.on('error', function (err) {
//   console.log('[REDIS] Client error', err);
// });

// module.exports = client;

// README:
// Redis has been disabled since we are not using protocols anymore, but code has been kept
// here above and the library has been mocked below.

module.exports = {
  get: function (key, next) {
    next();
  },
  set: function (key, value, next) {
    next();
  },
  expire: function () {}
};