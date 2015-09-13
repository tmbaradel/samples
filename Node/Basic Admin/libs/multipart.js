var _ = require('lodash');

module.exports = multipart;

function multipart (req, res, next) {
  var fields = [],
      reqContentType = req.get('Content-Type');

  if (!reqContentType || reqContentType.indexOf('multipart') === -1) {
    return next(null, req.body);
  }

  if (typeof req.busboy === 'undefined') {
    console.error('req.busboy has not been found, did you install the middleware?');
    return next(null, fields);
  }

  req.busboy.on('field', function (key, value) {
    // TODO: this is a massive hack, find a better way
    // converts `fields['parent[child]']` to `fields['parent']['child']`
    if (key.indexOf('[') !== -1) {
      var parent = key.substr(0, key.indexOf('[')),
          child = key.substr(key.indexOf('[')).replace('[', '').replace(']', '');

      if (!_.has(fields, parent)) {
        fields[parent] = {};
      }

      fields[parent][child] = value;
    } else {
      fields[key] = value;
    }
  });

  req.busboy.on('file', function (fieldname, file, filename, encoding, mimetype) {
    file.fileRead = [];

    file.on('data', function (chunk) {
      this.fileRead.push(chunk);
    });

    file.on('error', function (err) {
      res.status(400).send({
        error: 'Error receiving the file',
        message: err
      });
    });

    file.on('end', function () {
      fields[fieldname] = {
        name: filename,
        content: Buffer.concat(this.fileRead),
        encoding: encoding,
        mimetype: mimetype
      };

      delete this.fileRead;
    });
  });

  req.busboy.on('finish', function() {
    next(null, fields);
  });

  req.pipe(req.busboy);
}

module.exports.express = function (req, res, next) {
  multipart(req, res, function (err, fields) {
    if (!err && Object.keys(fields).length) {
      req.body = fields;
    }
    next();
  });
}