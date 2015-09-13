var express = require('express');
var async = require('async');

var router = express.Router();

var helpers = require('../../libs/helpers');
var User = require('../../models/user');

var userGroups = require('../../config/common/groups');


// --------------------------------------------------------------------------

router.get('/', function (req, res) {


  async.waterfall([
    //check the search and count
    function (next) {
      if (search) {
        searchQuery = {
          email:
            {
              $regex: new RegExp(search, 'i')
            }
        };
      }

      User
        .count(searchQuery)
        .exec(function (err ,res) {
          next(err, res);
      });
    },
    //execute the query
    function (count) {
      User
        .find(searchQuery)
        .select('+auth_token +optin.email_confirmed +optin.token')
        .sort(order)
        .limit(pageSize)
        .skip(offset)
        .exec(function (err, users) {
          res.render('admin/users/list', {
            title: 'Users',
            section: 'users',
            users: users,
            pages: Math.ceil(count / pageSize),
            currentPage: page,
            searchText: search,
            orderBy: sort
        });
      });
    }
  ]);

});

// --------------------------------------------------------------------------

router.get('/hash', hash);
router.post('/hash', hash);

function hash (req, res) {
  async.waterfall([
    function (next) {
      if (req.method !== 'POST') {
        return next();
      }

      helpers.hashPassword(req.body.password, next);
    }
  ], function (err, newHash) {
    res.render('admin/users/hash', {
      newHash: newHash
    });
  });
}

// --------------------------------------------------------------------------
router.get('/create', editUser);
router.get('/:id', editUser);

router.post('/create', editUser);
router.post('/:id', editUser);

function editUser (req, res){
  async.waterfall([
    function (next) {
      var id = req.params.id;

      if (id) {
        return User.findById(id, function (err, user) {
          if (err || !user) {
            return res.redirect('../users');
          }
          next(null, user);
        });
      }

      next(null, new User());
    },
    function (user, next) {
      if (req.method === 'POST') {
        var isNewUser = user.isNew;
        var fields = ['email', 'groups'];

        if (isNewUser || req.body.password) {
          fields.push('password');
        }
        if (req.body.password !== req.body.confirm_password) {
          return next(null, user, 'The password confirmation does not match');
        }

        for (var i = 0, len = fields.length; i < len; i++) {
          user[fields[i]] = req.body[fields[i]];
        }

        // If we are creating a user via the admin, we suppose the email exists
        if (isNewUser) {
          user.optin.email_confirmed = true;
        }

        return user.save(function (err) {

          if (err) {
            return next(null, user, err);
          }

          res.redirect('../users');
        });
      }

      next(null, user, null);
    },
    function (user, validationError) {
      res.render('admin/users/edit', {
        user: user,
        usergroups: userGroups,
        error: validationError
      });
    }
  ]);
}

// --------------------------------------------------------------------------

router.post('/:id/delete', function (req, res) {
  User
    .findById(req.params.id)
    .remove(function () {
      res.redirect('../../users');
    });
});

// --------------------------------------------------------------------------



// --------------------------------------------------------------------------

module.exports = router;
