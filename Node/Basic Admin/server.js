var fs = require('fs');

var config = require('./libs/config');

var app = require('./app');
app.set('port', config.port);

var server;

if (config.ssl) {
  try {
    var options = {
      key: fs.readFileSync(config.ssl.key),
      cert: fs.readFileSync(config.ssl.cert),
      requestCert: false
    };
  } catch (err) {
    console.error('Cannot read the SSL certificates. Please make sure you have the right permissions, or re-run the server as sudo');
    process.exit(1);
  }

  var https = require('https');
  server = https.createServer(options, app);

} else {
  var http = require('http');
  server = http.createServer(app);
}

server.start = function (next) {
  server.listen(config.port, config.interface || undefined, next);

  var ws = require('ws');
  var wss = new ws.Server({ server: server });
  require('./sockets/hub/trials')(wss);
};

server.on('listening', function () {
  console.log('[HTTP' + (config.ssl ? 'S' : '') + '] Engine process on port ' + server.address().port);
});

server.on('error', function (error) {
  if (error.syscall !== 'listen') {
    throw error;
  }

  switch (error.code) {
    case 'EACCES':
      console.error('Port ' + port + ' requires elevated privileges');
      process.exit(1);
      break;
    case 'EADDRINUSE':
      console.error('Port ' + port + ' is already in use');
      process.exit(1);
      break;
    default:
      throw error;
  }
});

module.exports = server;