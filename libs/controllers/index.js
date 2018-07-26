import { controllers } from '../config'

controllers.controller('ApplicationControllers', ['$scope', '$rootScope', 'Restangular', 'JQ_CONFIG', '$window', '$uibModal', '$state',  require('./Application')])

controllers.controller('DoctorsController', ['$scope', '$rootScope', 'doctors', 'Restangular', 'NgMap', 'toastr', require('./DoctorsController')])

controllers.controller('PersonalsController', ['$scope', '$rootScope', 'personals', 'sucursales' ,'Restangular', require('./PersonalsController')])

controllers.controller('EntidadesController', ['$scope', '$rootScope', 'entidades', 'Restangular', '$uibModal', require('./EntidadesController')])

controllers.controller('ProveedoresController', ['$scope', '$rootScope', 'proveedores', 'Restangular', '$uibModal', require('./ProveedoresController')])

controllers.controller('SucursalesController', ['$scope', '$rootScope', 'sucursales', 'Restangular', 'NgMap', 'toastr', '$uibModal', require('./SucursalesController')])

controllers.controller('PacientesController', ['$scope', '$rootScope', 'pacientes', 'entidades', 'Restangular', '$uibModal', '$state', 'toastr', require('./PacientesController')])

controllers.controller('PlanillaController', ['$scope', '$rootScope', 'yearPlanilla', 'Restangular', 'toastr', require('./PlanillaController').planilla])

controllers.controller('PermisosController', ['$scope', '$rootScope', 'Restangular', '$uibModal', 'toastr', 'object', require('./PlanillaController').permisos])

controllers.controller('InsuReactivosController', ['$scope', '$rootScope', 'materiales', require('./InReactivosController').controllers])

controllers.controller('ItemReactivoController', ['$scope', '$rootScope', 'Restangular', 'object', '$timeout', '$state', require('./InReactivosController').ItemReactivoController])

controllers.controller('ComprasController', ['$scope', '$rootScope', 'Restangular', '$uibModal', 'comprasList', 'toastr', require('./ComprasController').controllers])

controllers.controller('InventarioController', ['$scope', '$rootScope', 'inventario', require('./InventarioController').controllers])

controllers.controller('NuevaComprasController', ['$scope', '$rootScope', '$state', 'productosItems', 'proveedor',  'Restangular', 'toastr', require('./ComprasController').compraNueva])

controllers.controller('DetallesComprasController', ['$scope', '$rootScope', '$state', '$uibModal', 'compra', 'items', 'Restangular', 'toastr', require('./ComprasController').detalles])

controllers.controller('UsuariosControllers', ['$scope', require('./UsuariosControllers').show])

controllers.controller('UsuarioControllers', ['$scope', '$uibModal', 'Restangular', 'toastr', 'objectList', "sucursales", "perfiles", require('./UsuariosControllers').users])

controllers.controller('RecursosControllers', ['$scope', '$uibModal', 'Restangular', 'toastr', 'objectList', require('./UsuariosControllers').recursos])

controllers.controller('PerfilesControllers', ['$scope', 'objectList', 'Restangular' ,require('./UsuariosControllers').perfiles])

controllers.controller('PerfilControllers', ['$scope', 'Restangular' ,'objectData', 'toastr', '$uibModal', require('./UsuariosControllers').perfil])

controllers.controller('ExamenController', ['$scope', '$rootScope', 'categorias', '$uibModal', require('./ExamenController')])

controllers.controller('ExamenItemController', ['$scope', '$rootScope', 'objectData', 'toastr', 'Restangular', require('./ExamenController').item])

controllers.controller('PromocionController', ['$scope', '$rootScope', 'objectData', 'toastr', 'Restangular', '$uibModal' ,require('./PromocionController')])

controllers.controller('PromocionItemController', ['$scope', '$rootScope', 'objectData', 'toastr', 'Restangular', '$uibModal', require('./PromocionController').Item])

controllers.controller('ExamenViewController', ['$scope', '$rootScope', 'objectData', 'campos', 'toastr', 'Restangular', '$uibModal', require('./ExamenController').ViewController])

controllers.controller('SolicitudPacienteController', ['$scope', '$rootScope', 'paciente', 'type', 'toastr', 'Restangular', '$stateParams', '$state', '$uibModal', require('./SolicitudController').paciente])

controllers.controller('SolicitudEntidadController', ['$scope', '$rootScope', 'paciente', 'Restangular', 'entidad', 'toastr', '$state', '$uibModal', require('./SolicitudController').entidad])

controllers.controller('CatalogoCampoController', ['$scope', '$rootScope', 'Restangular', 'catalogo', require('./CatalogoController').controllers])

controllers.controller('CatalogoItemCampoController', ['$scope', '$rootScope', 'Restangular', 'object', 'toastr', 'objectCategoria', '$uibModal', require('./CatalogoController').item])

controllers.controller('SolicitudesItemController', ['$scope', '$rootScope', '$uibModal', 'object', 'Restangular', 'toastr', require('./SolicitudItemController')])

controllers.controller('SolicitudItemController', ['$scope', '$rootScope', '$uibModal', 'object', 'Restangular', 'toastr', require('./SolicitudItemController').item])

// perfil controllers
controllers.controller('PerfilController', ['$scope', 'perfil', '$uibModal', 'Restangular', require('./PerfilControllers')])

controllers.controller('PerfilChangePassword', ['$scope', 'perfil', 'Restangular', 'toastr', require('./PerfilChangePassword')])

controllers.controller('RentaConfiguration', ['$scope', 'rentaConfiguration', 'Restangular', 'toastr', '$state', 'porcentajes', '$uibModal', 'horarios', require('./RentaConfiguration')])

controllers.controller('SolicitudDomicilioPerfilController', ["$scope", "NgMap", "sucursales", 'examenes', 'Restangular', 'toastr', '$state', '$uibModal', require('./SolicitudDomicilioPerfilController')])

controllers.controller('SolicitudDomicilioController', ['$scope', '$rootScope', '$uibModal', 'object', 'Restangular', 'toastr', require('./SolicitudDomicilioController')])

controllers.controller('SolicitudDomicilioItemController', ['$scope', '$rootScope', '$uibModal', 'object', 'Restangular', 'toastr', 'NgMap', '$state', require('./SolicitudDomicilioController').item])

controllers.controller('ReservarCitaController', ['$scope', '$uibModal', 'object', 'Restangular', 'toastr', require('./ReservarCitaController')])

controllers.controller('CitaController', ['$scope', '$uibModal', 'Restangular', 'toastr', '$state', require('./ReservarCitaController').citas])

controllers.controller('InventarioReajustarController', ['$scope', '$uibModal', 'object', 'Restangular', 'toastr', require('./InventarioController').reajustar])

controllers.controller('BitacoraController', ['$scope', 'object', require('./ReservarCitaController').bitacora])

controllers.controller('NoticiaController', ['$scope', '$uibModal', 'noticias', '$sce', 'Restangular', 'toastr', require('./NoticiaController')])

controllers.controller('FinanzaController', ['$scope', 'toastr', 'entidades', require('./FinanzaController')]);

controllers.controller('SolicitudDomicilioHomeController', ['$scope', 'Restangular', 'toastr','arrayOfList', require('./SolicitudDomicilioHomeController')]);