import angular from 'angular'
import $ from 'jquery'
import { controllers, directives, plugins, filters, services } from './config'

let app = angular.module('projects.labfarmacia.app', [
  'projects.labfarmacia.controllers',
  'projects.labfarmacia.directives',
  'projects.plugins.controllers',
  'projects.labfarmacia.filters',
  'projects.labfarmacia.services'
])

module.exports = app