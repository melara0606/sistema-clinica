import {filters} from '../config'

import {
  fromGenero, fromContracto, fromMesString, fromRecurso, fromSolicitudEstado, 
  fromMoment, fromPermiso, fromDescuento, fromEstado, fromPresentacion, fromSolicitudType,
  fromPorcentaje, fromRango
} from './filters'

filters.filter('fromGenero', [fromGenero])
filters.filter('fromContracto', [fromContracto])
filters.filter('fromMesString', [fromMesString])
filters.filter('fromMoment', [fromMoment])
filters.filter('fromPermiso', [fromPermiso])
filters.filter('fromDescuento', [fromDescuento])
filters.filter('fromEstado', [fromEstado])
filters.filter('fromRecurso', [fromRecurso])
filters.filter('fromPresentacion', [fromPresentacion])
filters.filter('fromSolicitudType', [fromSolicitudType])
filters.filter('fromSolicitudEstado', [fromSolicitudEstado])
filters.filter('fromPorcentaje', [fromPorcentaje])
filters.filter('fromRango', [fromRango])