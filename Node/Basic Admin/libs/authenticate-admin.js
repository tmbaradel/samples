var authenticate = require('./authenticate');

module.exports = function (req, res, next) {
  authenticate.loadUser(req, res, function () {
    if (!req.user) {
      return res.redirect('/login?r=' + encodeURIComponent(req.originalUrl));
    }
    res.locals.user = req.user;
    next();
  });
};
