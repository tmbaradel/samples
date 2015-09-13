var Session = require('../models/session');
var User = require('../models/user');

var config = require('../libs/config');

var routes = require('../config/common/routes');

var cookieMaxAge = 1 * 24 * 60 * 60 * 1000; // 1 day

module.exports = function(req, res, next) {
  module.exports.loadUser(req, res, function () {
    if (!req.auth_token) {
      return res.status(401).send({
        error: 'auth_token not provided',
        message: 'Please include a valid auth_token when accessing this resource.'
      });
    }

    if (!req.user) {
      return res.status(401).send({
        error: 'not authorised',
        message: 'The auth_token provided doesn\'t belong to any user.'
      });
    }

    next();
  });
};

module.exports.loadUser = function (req, res, next) {
  if (req.user) {
    return next();
  }

  req.auth_token = req.query.auth_token || req.body.auth_token || req.headers['auth-token'] || req.cookies.auth_token;
  if (!req.auth_token) {
    return next();
  }

  if (req.auth_token.indexOf(Session.getTokenPrefix()) === 0) {
    // Short-lived sessions without users
    Session
    .findOneAndUpdate(
      { auth_token: req.auth_token },
      {
        $set: { last_hit_at: Date.now() },
        $inc: { hits: 1 }
      }
    )
    .populate('objects.trial')
    .exec(function (err, session) {
      if (!err && session && session.objects.trial) {
        // Populate the trial protocols with real objects
        return session.objects.trial.populate('name created_at', function () {
          req.user = session;
          res.locals.user = req.user;
          next();
        });
      }
      next();
    });
    return;
  }
  User
  .findOne({ auth_token: req.auth_token })
  .select('_id email')
  .exec(function (err, user) {
    if (!err && user) {
      req.user = user;
      res.locals.user = req.user;
      // Update cookie if needed
      if (req.auth_token) {
        res.cookie('auth_token', req.auth_token, {maxAge: cookieMaxAge, httpOnly: true});
      }
    }
    next();
  });
}

module.exports.editors = function (req, res, next) {
  if (!req.user || !req.user.is('editor')) {
    return res.redirect(config.webhost + routes.webhost.login);
  }

  next();
}
