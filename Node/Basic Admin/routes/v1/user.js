var express = require('express');
var router = express.Router();

var authenticate = require('../../libs/authenticate');

var User = require('../../models/user');
