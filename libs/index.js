var angular = require('angular')
var app = require('./app')
/*import angular from 'angular'
import app from './app'*/

require('./config/run')
require('./directives')
require('./filters')
require('./services')
require('./config/routers')
require('./controllers')


angular.bootstrap(document, ['projects.labfarmacia.app'])
