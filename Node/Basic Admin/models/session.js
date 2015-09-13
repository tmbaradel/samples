var db = require('../libs/db');
var helpers = require('../libs/helpers');



var sessionSchema = new db.Schema(
  {
    created_at: {
      type: Date,
      default: Date.now
    },
    updated_at: {
      type: Date,
      default: Date.now
    },
    last_hit_at: {
      type: Date
    },
    auth_token: {
      type: String
    },
    ip_address: {
      type: String,
      trim: true,
      required: true
    },
    user_agent: {
      type: String,
      trim: true,
      required: true
    },
    hits: {
      type: Number,
      default: 0
    },
    objects: {
      trial: {
        type: db.Schema.Types.ObjectId,
        ref: 'Trial'
      }
    }
  },

  // Mongoose schema options
  {
    toObject: {
      transform: function (document, returns) {
        // Prevent the __v property getting returned
        delete returns.__v;
      }
    }
  }
);
