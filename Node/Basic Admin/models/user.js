var async = require('async');
var _ = require('lodash');

var config = require('../libs/config');
var db = require('../libs/db');
var helpers = require('../libs/helpers');

var userSchema = new db.Schema({
  created_at: {
    type: Date,
    default: Date.now
  },
  last_login_at: {
    type: Date
  },
  email: {
      type: String,
      trim: true,
      unique: true,
      required: 'Email address is required',
      validate: [helpers.validateEmail, 'Email address is not valid'],
      index: {
        unique: true
      }
  },
  optin: {
    email_confirmed: {
      type: Boolean,
      select: false,
      default: false
    },
    email_confirmed_at: {
      type: Date,
      select: false
    },
    token: {
      type: String,
      select: false
    }
  },
  password: {
    type: String,
    select: false,
    required: 'Password is required',
    validate: [
      function (pwd) {
        return typeof pwd === 'string' && pwd.length >= 6 && pwd.length <= 128;
      },
      'Password length must be between 6 and 128 characters'
    ]
  },
  locale: {
    type: String,
    default: locales[0].locale,
    validate: [function (value) {
      return helpers.hasKeyWithValue(locales, 'locale', value);
    }, 'Locale is not valid']
  },
  country: {
    type: String,
    default: countries[0].code
  },
  study_centre: {
    type: db.Schema.Types.ObjectId,
    ref: 'StudyCentre'
  },
  auth_token: {
    type: String,
    select: false,
    index: {
      unique: true
    }
  },
  groups: {
    type: [String],
    validate: [
      function (items) {
        if (!items.length){
          return false;
        }
        for (var i = 0; i < items.length; i++) {
          if (userGroups.indexOf(items[i]) === -1) {
            return false;
          }
        }
        return true;
      },
      'User group must be ' + userGroups.join(' or ')
    ]
  },
  trials_protocols: {
    type: [String],
    trim: true
  },
  updated_at: {
    type: Date
  }
}, {
  toObject: {
    transform: function (document, returns) {
      // Prevent the __v property getting returned
      delete returns.__v;
    }
  }
});

userSchema.pre('save', function (next) {
  var user = this;

  if (user.optin.email_confirmed === false) {
    if (user.groups.indexOf('admin') !== -1) {
      // Admins don't need to confirm their email
      user.optin.email_confirmed = true;
      user.optin.email_confirmed_at = Date.now();
    } else {
      // Generate a email opt-in token whenever the email changes
      if (user.isModified('email')) {
        user.optin.email_confirmed = false;
        user.optin.token = helpers.md5(helpers.currentTimestamp() + user.email);
      }
    }
  }

  if (!user.isModified('password')) {
    return next();
  }

  helpers.hashPassword(user.password, function (err, hash) {
    if (err) return next(err);

    user.password = hash;
    user.auth_token = helpers.md5('' + user.email + user.password);

    next();
  });
});

userSchema.methods.sendOptInEmail = function (mailer, next) {
  var user = this;

  if (!user.email || user.optin.email_confirmed || !user.optin.token || config.env === 'tests') {
    return typeof next === 'function' ? next() : null;
  }

  console.log('Sending opt-in email to', user.email);

  mailer.send(
    'email/opt-in',
    {
      to: user.email,
      subject: 'Please confirm your email',
      link: config.host + 'v1/auth/confirm-email/' + user.optin.token
    },
    function (err, status) {
      if (err) {
        console.log('Cannot send email to', user.email, err);
      }

      console.log('Opt-in email sent to', user.email);

      if (typeof next === 'function') {
        next(err, status);
      }
    }
  );
};

userSchema.methods.getTrials = function (options, next) {
  var user = this,
      cursor;

  var query = {
    $or: [
      {
        _id: {
          $in: user.trials_protocols
        }
      },
      {
        protocols: {
          $in: user.trials_protocols
        }
      }
    ]
  };

  // get all the trials if admin
  cursor = (_.indexOf(user.groups, 'admin') !== -1) ? Trial.find() : Trial.find(query);

  // some more options could be added
  if (options) {
    if (options.order){
        cursor.sort(options.order);
    }
  }

  cursor
  .populate('materials')
  .exec(function (err, trials) {
    if (err) {
      return next(err, trials);
    }
    trials.forEach(function (trial, index) {
      var protocols = [];
      if (user.trials_protocols.indexOf(trial._id.toString()) === -1 ){
        trial.protocols.forEach(function (protocol) {
          if (user.trials_protocols.indexOf(String(protocol)) !== -1) {
            protocols.push(protocol);
          }
        });
        trial.protocols = protocols;
      }
    });
    next(err, trials);
  });
};

userSchema.methods.getTrialsWithProtocols = function (options, next) {
  this.getTrials(options, function (err, trials) {
    var objects = [];

    // Populate the protocols with real objects
    async.each(trials, function (trial, next) {
      trial = trial.toObject();

      Protocol
      .find({ _id: { $in: trial.protocols  } })
      .select('name created_at')
      .exec(function (err, protocols) {
        if (!err && protocols) {
          trial.protocols = protocols;
        }

        objects.push(trial);
        next();
      });
    }, function () {
      next(null, objects);
    });
  });
};

userSchema.methods.getTrial = function (id, next) {
  this.getTrials(null, function (err, trials) {
    if (err) {
      return (err, trials);
    }

    var valid;

    trials.forEach(function (trial, index) {
      if(trial._id.toString() === id){
        valid = true;
        return next(null, trial);
      }
    });

    if (!valid) {
      return next(new Error('Trial not found'));
    }
  });
};

userSchema.methods.getProtocolIds = function (next) {
  this.getTrials(null, function (err, trials) {
    if (err) {
      return (err, trials);
    }

    var protocols = [];

    trials.forEach(function (trial, index) {
      protocols = protocols.concat(trial.protocols);
    });

    next(err, protocols.toString().split(","));
  });
};

userSchema.methods.updateLastLoginDate = function (next) {
  this.last_login_at = Date.now();
  this.save(next);
};

userSchema.methods.updateToken = function (opt, next) {
  this.auth_token = helpers.md5(this._id + this.email + this.password + Date.now());
  this.save(next);
};

userSchema.methods.invalidateToken = function (next) {
  this.auth_token = '';
  this.save(next);
};

userSchema.methods.is = function (groupName) {
  return this.groups.indexOf(groupName) !== -1;
}

userSchema.pre('remove', function (next) {
  Annotation.remove({_author: this._id}).exec();
  next();
});

userSchema.post('save', SiteContentLog.logUpdate);
userSchema.post('update', SiteContentLog.logUpdate);
userSchema.post('remove', SiteContentLog.logUpdate);

module.exports = db.model('User', userSchema);
