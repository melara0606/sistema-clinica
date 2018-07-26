$(function() {
  "use strict"
  var selectSucursal = $("#select_sucursal"),
      visualizar = $("#visualizarDatos"),
      mapElement = $("#map");

  if(selectSucursal){
    visualizar.hide();
    mapElement.hide();

    var myLatLng = null;
    var marketCurrent = null;
    if(google){
       var map = new google.maps.Map(document.getElementById('map'), { zoom: 15 });

      $(selectSucursal).on('change', function(e) {
        if($(this).val()){
          $.getJSON('sucursales/' + $(this).val()).then( json  => {
            visualizar.show()
            $(visualizar).find('#email').text(json.email_suc).attr('type', 'email')
            $(visualizar).find('#phone').text(json.phone_suc).attr('href', 'tel:' + json.phone_suc)
            $(visualizar).find('#adress').text(json.address_suc).attr('href', 'tel:' + json.address_suc)

            myLatLng = { lat: parseFloat(json.lat), lng: parseFloat(json.lng) }
            map.setCenter(myLatLng);

            if(marketCurrent){
              marketCurrent.setMap(null)
            }

            marketCurrent = new google.maps.Marker({
              map: map,
              position: myLatLng,
              title: json.nombre_sucursal,
              animation: google.maps.Animation.DROP
            });
            mapElement.show()
          })
        }else{
          visualizar.hide();
          mapElement.hide();
        }
      });
    }
  }else{
    visualizar.hide();
  }

}(jQuery));