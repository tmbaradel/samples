var express = require('express');
var async = require('async');
var router = express.Router();

var authenticate = require('../../libs/authenticate');
var config = require('../../libs/config');
var helpers = require('../../libs/helpers');


router.post('/login', function (req, res) {

});

// --------------------------------------------------------------------------

router.post('/session', function (req, res) {

});

// --------------------------------------------------------------------------

router.post('/logout', function (req, res) {

});

// --------------------------------------------------------------------------


// --------------------------------------------------------------------------

router.post('/password/reset',function (req, res) {

});

// --------------------------------------------------------------------------

router.post('/activation/sign-up', function (req, res) {

});

// --------------------------------------------------------------------------



// --------------------------------------------------------------------------

module.exports = router;
