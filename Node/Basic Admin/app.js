var express = require('express');
var cors = require('cors');
var path = require('path');
var https = require('https');
var mailer = require('express-mailer');
var favicon = require('serve-favicon');
var logger = require('morgan');
var raven = require('raven');
var aws = require('aws-sdk');
var busboy = require('connect-busboy');
var cookieParser = require('cookie-parser');
var compression = require('compression');
var bodyParser = require('body-parser');
var session = require('express-session');
var i18n = require('i18n');

var app = express();

// Promises polyfill
require("native-promise-only");

// --------------------------------------------------------------------------


// --------------------------------------------------------------------------
// Framework libs

var authenticate = require('./libs/authenticate');
var cache = require('./libs/cache');
var config = require('./libs/config');
var viewHelpers = require('./libs/view-helpers');

// --------------------------------------------------------------------------
// Framework models

var User = require('./models/user');

// --------------------------------------------------------------------------
// Mailer configuration

https.globalAgent.options.secureProtocol = 'SSLv3_method';
mailer.extend(app, config.mail);

// --------------------------------------------------------------------------
// View shared variables
app.locals.start_ts = Date.now();
app.locals.apihost = config.host;
app.locals.webhost = config.webhost;


// --------------------------------------------------------------------------
// Engines

app.set('views', path.join(__dirname, 'views'));
app.set('view engine', 'jade');

// more readable html markup on local environments
if (config.env === 'local') {
  app.locals.pretty = true;
}

// --------------------------------------------------------------------------
// Generic middle-wares

if (!config.hasOwnProperty('logging') || config.logging !== false) {
  app.use(logger('[:date] :remote-addr :method :url :status :response-time ms - :res[content-length]'));
}

app.use(cors());
app.use(compression());
app.use(busboy());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, "public"), {maxage: '1h'}));
app.use(i18n.init);
app.disable('etag');

// --------------------------------------------------------------------------
// Fine tuning

// Disable socket pooling
require('http').globalAgent.maxSockets = Infinity;

// --------------------------------------------------------------------------
// Auth_token middle-ware to preload the user

app.use(authenticate.loadUser);

// --------------------------------------------------------------------------
// Set up session for the admin and hub

app.use(session({
  secret: config.session_secret,
  resave: false,
  saveUninitialized: true,
  cookie: { secure: true }
}));

// --------------------------------------------------------------------------


// --------------------------------------------------------------------------
// HTTP Auth middle-ware for Admin and Hub paths
app.use('/admin', require('./libs/authenticate-admin'));
app.use(function (req, res, next) {
  res.start_ts = app.locals.start_ts;
  res.locals.helpers = viewHelpers;
  next();
});

// --------------------------------------------------------------------------
// API Routes
app.use('/', require('./routes/index'));
app.use('/', require('./routes/status'));
app.use('/v1/auth', require('./routes/v1/auth'));
app.use('/v1/config', require('./routes/v1/config'));
app.use('/v1/contents', require('./routes/v1/contents'));
app.use('/v1/user', require('./routes/v1/user'));

// --------------------------------------------------------------------------
// Admin Routes
app.use('/admin/contents', require('./routes/admin/contents'));
app.use('/admin/docs', require('./routes/admin/docs'));
app.use('/admin/users', require('./routes/admin/users'));

// --------------------------------------------------------------------------
// Create default admin users if we haven't already

User.count({groups: 'admin'}, function (err, count) {
  if (count === 0) {
    var admins = require('./config/common/admins');

    admins.users.forEach(function (admin) {
      User.create({
        email: admin.email,
        password: admin.password,
        groups: ['admin']
      });
    });
  }
});

// --------------------------------------------------------------------------
// Error handlers

// not found
app.use(function (req, res, next) {
  var err = new Error('Not Found');
  err.status = 404;
  next(err);
});

app.use(function (err, req, res, next) {
  err.status = err.status || 500;

  if (err.status === 404) {
    err.message = '404 • Page not found';
    err.description = 'The page you were looking for has not been found';

    if (app.get('env') !== 'development') {
      delete err.stack;
    }
  }

  res.status(err.status).render('error', {
    message: err.message,
    error: err,
    className: [404, 500, 503].indexOf(err.status) !== -1 ? 'full-screen' : undefined
  });
});

module.exports = app;
