import { services } from '../config'

services.service('uiLoad', ['$document', '$q', '$timeout', require('./ui-load') ])