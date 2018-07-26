import ngMap from 'ngmap'
import angular from 'angular'
import router from 'angular-ui-router'
import restangular from 'restangular'
import uiBootstrap from 'angular-ui-bootstrap'
import uiMask from 'angular-ui-mask'
import aToastr from 'angular-toastr'
import angularMoment from 'angular-moment'
import angularSmartTable from 'angular-smart-table'
import angularValidateUI from 'angular-ui-validate'
import angularDaterangepicker from 'angular-daterangepicker'

import ngSanitize from 'angular-sanitize'

// Sortable Of Examen
import uiSortable from 'angular-ui-sortable'
// Editor
import trix from 'trix'

module.exports.controllers = angular.module('projects.labfarmacia.controllers', [])
module.exports.directives  = angular.module('projects.labfarmacia.directives', [])
module.exports.filters  = angular.module('projects.labfarmacia.filters', [])
module.exports.services  = angular.module('projects.labfarmacia.services', [])

module.exports.plugins = angular.module('projects.plugins.controllers', [
  'ui.router',
  'restangular',
  'ui.bootstrap',
  'ui.mask',
  'ngMap',
  'toastr',
  'angularMoment',
  'smart-table',
  'ui.validate',
  'daterangepicker',
  'ngSanitize',
  'ui.sortable'
])
