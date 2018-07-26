import { filter } from 'lodash'
//import $ from 'jquery'

module.exports.controllers = function($scope, $rootScope, materiales) {
    $scope.materiales = materiales;
}

module.exports.ItemReactivoController = function($scope, $rootScope, Restangular, object, $timeout, $state) {
  $scope.item  = {};
  $scope.isEditing = false;
  $scope.items = object.items;
  $scope.material = object.material;
  $scope.permisos = filter($rootScope.recursos, { url: 'insumos_reactivos' })[0];
  $scope.options = {
    aoColumns: [
      { mData: 'index' },
      { mData: 'name' },
      { mData: 'presentacion' },
      { mData: 'operaciones', bSortable: false }
    ]
  };

  $("table#dataTables").on("click", "button", (event) => {
    $scope.$apply(function() {
      let index = $(event.currentTarget).data('key')
      $scope.isEditing = true;
      $scope.item = $scope.items[index];
      $scope.item.index = index;
    });
  });

  $scope.onValidateForm = (isValid) => {
    if(isValid){
      var $objectsData = $scope.item;
      if($objectsData.id){
        Restangular.all('update').customPOST($objectsData, "material").then((response) =>{
          $scope.items[$scope.item.index] = response.data;
          $scope.isEditing = false;
          $scope.item = {};
          $state.reload();
        });
      }else{
        object.customPOST($objectsData).then(function(response){
          $state.reload();
        });
      }
    }
  }

  $scope.onCreate = () => { $scope.isEditing = true; }
  $scope.onCancelar = () => {
    $scope.isEditing = false;
    $scope.item = {};
  }

  $scope.today = function() { $scope.item.fecha_vencimiento = new Date(); };
  $scope.today();

  $scope.clear = function() { $scope.item.fecha_vencimiento = null; };
  $scope.format = 'dd-MMMM-yyyy';
  $scope.popup1 = { opened: false };
  $scope.altInputFormats = ['M!/d!/yyyy'];

  $scope.dateOptions = {
      formatYear: 'yy',
      maxDate: new Date(2020, 5, 22),
      minDate: new Date(),
      startingDay: 1
  };

  $scope.open = function() {
    $scope.popup1.opened = true;
  }
};
