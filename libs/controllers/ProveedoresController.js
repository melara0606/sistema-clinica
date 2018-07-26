import { filter } from 'lodash'

module.exports = function($scope, $rootScope,  proveedores, Restangular, $uibModal) {
  $scope.items = proveedores
  $scope.item  = proveedores[0]
  $scope.item.selected = true

  $scope.permisos = filter($rootScope.recursos, { url: 'proveedores' })[0];

  $scope.selectItem = (item) =>{
    angular.forEach($scope.items, (i, index) => {
      i.selected  = false
      i.editing   = false
    })
    $scope.item = item
    $scope.item.selected = true
  }

  $scope.createItem = () => {
    let item = { status: 1, isNew: true }
    $scope.items.push(item)
    $scope.selectItem(item)
    $scope.item.editing = true
  }

  $scope.editItem = (item) => {
    if(item && item.selected){
      item.editing = true
      item.isNew = false
    }
  }

  $scope.deleteItem = (item) =>{
    if(item.id && !item.editing){
      item.post('delete', item).then((res) => {
        item.status = !item.status
      })
    }else if(item.isNew){
      $scope.items.pop()
      $scope.item = $scope.items[0]
      $scope.item.selected = true
    }else{
      item.editing = false
    }
  }

  $scope.doneEditing = (item) => {
    if(item.isNew){
      Restangular.all('proveedores').post(item).then((element) => {
        $scope.items.pop()
        $scope.items.push(element)
        $scope.selectItem(element)
      });
    }else{
      item.put().then((it) => {
        item.editing = false
      })
    }
  }

  /*  funciones para el modal */
  $scope.open = (item) => {
     var modalInstance = $uibModal.open({
      ariaLabelledBy: 'modal-title',
      ariaDescribedBy: 'modal-body',
      templateUrl: 'public/templates/modals/proveedors.materials.html',
      size: 'noventa',
      resolve: {
        list: ["Restangular", function(Restangular){
          return Restangular.all(`proveedor/${item.id}`).customGET("material");
        }],
        proveedor: [function(){
          return item;
        }]
      },
      controller: function($scope, $uibModalInstance, list, proveedor, toastr, ) {
        $scope.server = false;
        $scope.materials = [];
        $scope.rowCollection  = list.data;
        $scope.itemsByPage = 5;

        $scope.cerrar = () => {
          $uibModalInstance.dismiss('cancel');
        }

        $scope.onSelectEmployer = (item) => {
          Restangular.all('material').customPOST({
            proveedor_id :  proveedor.id,
            material     : item.id
          }, 'add').then((response) => {
            $scope.rowCollection.push(response.data);
          }, (error) => {
            toastr.error('No puedes tener dos mismo materiales para el mismo proveedor', 'Error!');
          });
        };

        $scope.onDelete = ($index, item) => {
           Restangular.all('QueryDelete').customPOST( {
            id: item.id
          }, 'materiales').then((response) => {
            let deleteObject = $scope.rowCollection.splice($index, 1);
          });
        }

        $scope.onSearchEmployeers = () => {
          Restangular.all('search').customGET('materiales', {
            query: $scope.searchEmployeer
          }).then((response) => {
            $scope.server = true;
            $scope.materials = response.data;
          });
        };
      }
    });
  }

  $scope.onKeypress = (keyEvent) => {
    if(keyEvent.which === 13){
      $scope.query = "";
      Restangular.all('proveedores').customGET()
        .then(json => {
          generador($scope, json)
        })
    }
  }

  $scope.$watch('query', function (value) {
    if(value && value.length >= 3){
      Restangular.all('proveedores/searchQuery?q='+ value).customGET()
      .then(json => {
        if(json.data.length > 0){
          generador($scope, json.data)
        }else{
          $scope.items = [];
        }
      })
    }
  })

  function generador($scope, data){
    $scope.items = data;
    $scope.item  = $scope.items[0]
    $scope.item.selected = true
  }
}
