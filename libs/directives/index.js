import {directives} from '../config'

directives.directive('uiNav', ['$timeout', require('./uiNav')])
directives.directive('uiJq', ['uiJqConfig', '$rootScope', 'JQ_CONFIG', 'uiLoad', '$timeout', require('./ui-jq')])
directives.directive('angularTrix', ['$timeout', require('./angularTrix')])
