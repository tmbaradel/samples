var env = (process.env.NODE_ENV || 'local').toLowerCase();
var config = require('../config/' + env + '.json');

config.env = env;
config.port = parseInt(process.env.PORT || config.port || 80, 10);
config.redis_url = process.env.REDISTOGO_URL || config.redis_url || '';
config.database_url = process.env.MONGO_URI || process.env.MONGOLAB_URI || config.database_url || '';
config.heroku_app_name = process.env.APP_NAME || config.heroku_app_name;

module.exports = config;