import app from '../app'
import moment from 'moment'

app.config(['RestangularProvider', (RestangularProvider) => {
  RestangularProvider.setBaseUrl('.');
}])

app.run(['$rootScope', function($rootScope) {
  $rootScope.solicitudes = [];
  $rootScope.showUser = true;
}])

app.config(['toastrConfig', function(toastrConfig) {
  angular.extend(toastrConfig, {
    extendedTimeOut: 5000,
  });
}]);

app.value('uiJqConfig', {
  'aLengthMenu' : [[10, 25, 50, -1], [10, 25, 50, "All"]],
  "oLanguage": {
    "sEmptyTable": "No hay datos para la tabla",
    "sLengthMenu": "Mostrar _MENU_ registros",
    "sSearch": "Buscar:",
    "sInfoFiltered": " - filtro de _MAX_ registro",
    "sInfo": "Mostrar _TOTAL_ registro (_START_ de _END_)",
    "oPaginate": {
      "sNext"       : "Siguiente",
      "sPrevious"   : "Anterior"
    }
  }
});

app.value('froalaConfig', {
  toolbarInline: false,
  placeholderText: 'Enter Text Here'
})

app.constant('JQ_CONFIG', {
  dataTable: [
    'public/assets/DataTables/js/jquery.dataTables.min.js',
    'public/assets/DataTables/js/dataTables.bootstrap.js',
    'public/assets/DataTables/css/dataTables.bootstrap.css'
  ]
})
