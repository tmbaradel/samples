var driver = require('mongoose');
var fs = require('fs');
var path = require('path');

var config = require('./config');

var options = {
  db: {
    native_parser: true
  }
};

if (config.database_ssl) {
  options.replset = {
    rs_name: 'sample',
    ssl: true,
    sslValidate: false,
    sslCert: fs.readFileSync(path.join(__dirname, '../config/ssl/' + config.certFile)),
    sslKey: fs.readFileSync(path.join(__dirname, '../config/ssl/' + config.keyFile))
  };
}

console.log('[MONGODB] Connecting...');
console.log(config.database_url);

module.exports = driver.connect(
  config.database_url,
  options,
  function (err) {
    if (err) {
      console.error('[MONGODB] Connection error', err);
    } else {
      console.log('[MONGODB] Connected!');
    }
  }
);
