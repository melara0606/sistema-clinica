module.exports = function($scope, usuarios, sucursales,  Restangular) {  
  $scope.sucursales = usuarios;
  
  $scope.items = usuarios
  $scope.item  = usuarios[0]
  $scope.item.selected = true

  $scope.createItem = () => {
    let item = { status: 1, isNew: true }
    $scope.items.push(item)
    $scope.selectItem(item)
    $scope.item.editing = true
  }

  $scope.selectItem = (item) =>{
    angular.forEach($scope.items, (i, index) => {
      i.selected  = false
      i.editing   = false      
    })
    $scope.item = item
    item.fecha_contratacion_emp = new Date(item.fecha_contratacion_emp)
    $scope.item.selected = true
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
      Restangular.all('personal').post(item).then((element) => {
        $scope.items.pop()
        $scope.items.push(element)
        $scope.selectItem(element)
      });
    }else{      
      item.put().then((it) => {
        item.editing = false
        item.nombre_sucursal = it.nombre_sucursal
      })
    }
  }

  $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
  $scope.format = $scope.formats[0]; 
  $scope.altInputFormats = ['M!/d!/yyyy'];

  $scope.popup1 = {
    opened: false
  };

  $scope.dateOptions = {
    formatYear: 'yy',
    startingDay: 1,
    class: 'datepicker',
    minDate: new Date(1975, 1, 1)
  }

  $scope.open1 = function() {
    $scope.popup1.opened = true;
  };
}