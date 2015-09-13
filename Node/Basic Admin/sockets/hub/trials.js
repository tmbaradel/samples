var _ = require('lodash');
var spawn = require('child_process').spawn;

var sockets = function (wss) {
  var ping;

  wss.on('connection', function (socket) {
    socket.sendMessage = function (name, data) {
      var message = { name: name, data: data };
      socket.send(JSON.stringify(message));
    };

    socket.on('message', function (message) {
      try {
        message = JSON.parse(message);
      } catch (e) {
        return;
      }

      socket.sendMessage('received', { name: message.name });

      if (message.name && message.name === '/booklet/generate') {
        return bookletGenerate(socket, message.data);
      }
    });

    // Ensure the heroku instance stays alive
    ping = setInterval(function () {
      socket.sendMessage('ping');
    }, 5000);

    socket.on('close', function () {
      clearInterval(ping);
    });
  });
};

function bookletGenerate (socket, data) {
  var command = 'node',
      options = [
        'bin/generate-booklet',
        data.trial,
        data.locale
      ],
      generate = spawn(command, options);

  generate.on('error', function (err) {
    throw err;
  });

  generate.stdout.on('data', function (data) {
    var redirectUrl;

    data = data.toString();

    if (data.indexOf('booklet-url:') === 0) {
      redirectUrl = '/hub/trials/materials/booklet/?path=';
      redirectUrl += data.substr(data.indexOf('booklet-url:') + 13).trim();

      socket.sendMessage('redirect-url', { redirectUrl: redirectUrl });
    } else {
      socket.sendMessage('progress', { data: data });
    }
  });

  generate.stderr.on('data', function (message) {
    socket.sendMessage('error', { message: message });
  });

  generate.on('close', function (status, data) {
    socket.sendMessage('completed', { status: status });
  });
}

module.exports = sockets;