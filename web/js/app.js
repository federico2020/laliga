


var init = {
    'handlers':function () {
        $('#clubModalSubmit').on('click',function (e) {
            e.preventDefault();
            if($('#clubModalNombre').val()==''){
                alert('Debe completar el nombre del club');
                return;
            }
            else if($('#clubModalLimiteSalarial').val()==''){
                alert('Debe completar el límite salarial del club');
                return;
            }
            init.postClub();
        })

        $('#jugadorModalSubmit').on('click',function (e) {
            e.preventDefault();
            if($('#jugadorModalNombre').val()==''){
                alert('Debe completar el nombre del jugador');
                return;
            }
            else if($('#jugadorModalSalario').val()==''){
                alert('Debe completar el salario del jugador');
                return;
            }
            else if($('#jugadorModalEmail').val()==''){
                alert('Debe completar el email');
                return;
            }
            init.postJugador();
        })

        $('#jugadorModificarModalSubmit').on('click',function (e) {
            e.preventDefault();
            if($('#jugadorModificarModalId').val()==''){
                alert('Debe seleccionar un jugador');
                return;
            }
            else if($('#jugadorModificarModalNombre').val()==''){
                alert('Debe completar el nombre del jugador');
                return;
            }
            else if($('#jugadorModificarModalSalario').val()==''){
                alert('Debe completar el salario del jugador');
                return;
            }
            else if($('#jugadorModificarModalEmail').val()==''){
                alert('Debe completar el email');
                return;
            }
            init.putJugador($('#jugadorModificarModalId').val());
        })

        $('#perfilModalSubmit').on('click',function (e) {
            e.preventDefault();
            if($('#perfilModalNombre').val()==''){
                alert('Debe completar el nombre del perfil');
                return;
            }
            init.postPerfil();
        })
    },
    'filtrar':function () {
        var filters = {};
        filters.club = $("input[name='select_club']:checked").val();

        perfiles = [];
        if($('#perfil_todos').is(':checked')){
            perfiles.push(0);
        }
        $( "#selectPerfil input" ).each(function( index ) {
            if($(this).is(':checked')) {
                perfiles.push($(this).val());
            }
        });
        filters.perfil = perfiles;

        $.ajax({
            data: filters,
            type: "GET",
            dataType: "json",
            url: "/api/players"
        })
        .done(function( data ) {
            if(data.error){
                alert('Error :'+data.msg);
                return;
            }

            if(data.jugadores.length>0){
                $('#jugadoresTabla').html('');

                var tabla = `
                <table class="table table-striped table-sm">
                <thead>
                    <tr>
                         <th>#</th>
                         <th>Nombre</th>
                         <th>Club</th>
                         <th>Perfil</th>
                         <th>Posicion</th>
                         <th>Salario</th>
                         <th></th>
                     </tr>
                 </thead>
                 <tbody>
            `;

                $.each(data.jugadores, function (i,value) {
                    tabla+=`
                    <tr>
                         <td>${value.id}</td>
                         <td>${value.nombre}</td>
                         <td>${value.club}</td>
                         <td>${value.perfil}</td>
                         <td>${value.posicion}</td>
                         <td>${value.salario}</td>
                         <td><a href=""  onclick="init.deleteJugador(${value.id}); return false">(-)</a> <a href=""  onclick="init.modificarJugadorShow(${value.id}); return false">(modificar)</a></td>
                     </tr>
                `;
                })

                tabla+='</tbody></table>';
                $('#jugadoresTabla').append(tabla);
            }

        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            alert("La solicitud a fallado: " + errorThrown);
        });


    },
    'initData':function () {

        $.ajax({
            data: {},
            type: "GET",
            dataType: "json",
            url: "/api/init/data "
        })
        .done(function( data ) {
            if(data.error){
                alert('Error :'+data.msg);
                return;
            }

            if(data.clubs.length>0){
                $('#buttonAgregarJugador').show();
                $('#jugadorModalClub').html('');
                $('#jugadorModificarModalClub').html('');
                $.each(data.clubs, function (i,value) {
                    $('#jugadorModalClub').append(`<option value="${value.id}">${value.nombre}</option>`);
                    $('#jugadorModificarModalClub').append(`<option value="${value.id}">${value.nombre}</option>`);

                    var clubLi = `
                        <li>
                            <div class="form-check">
                                <input onclick="init.filtrar()" class="form-check-input" type="radio" name="select_club" id="club_${value.id}" value="${value.id}">
                                <label class="form-check-label" for="club_${value.id}">${value.nombre}</label>
                                <a href="" onclick="init.deleteClub(${value.id}); return false">(-)</a>
                            </div>
                        </li>
                    `;
                    $('#selectClub').append(clubLi);
                })
            }
            else{
                $('#buttonAgregarJugador').hide();
            }

            if(data.perfiles.length>0){
                $('#jugadorModalPerfil').html('');
                $('#jugadorModificarModalPerfil').html('');

                $.each(data.perfiles, function (i,value) {
                    $('#jugadorModalPerfil').append(`<option value="${value.id}">${value.nombre}</option>`);
                    $('#jugadorModificarModalPerfil').append(`<option value="${value.id}">${value.nombre}</option>`);

                    var perfilLi = `
                        <li>
                            <div class="form-check">
                                <input onclick="init.filtrar()" type="checkbox" class="form-check-input" name="perfil_${value.id}" id="perfil_${value.id}" value="${value.id}">
                                <label class="form-check-label" for="perfil_${value.id}">${value.nombre}</label>
                                <a href="" onclick="init.deletePerfil(${value.id}); return false">(-)</a>
                            </div>
                        </li>
                    `;
                    $('#selectPerfil').append(perfilLi);
                })
            }

            if(data.jugadores.length>0){
                $('#jugadoresTabla').html('');

                var tabla = `
                    <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                             <th>#</th>
                             <th>Nombre</th>
                             <th>Club</th>
                             <th>Perfil</th>
                             <th>Posicion</th>
                             <th>Salario</th>
                             <th></th>
                         </tr>
                     </thead>
                     <tbody>
                `;

                $.each(data.jugadores, function (i,value) {
                    tabla+=`
                        <tr>
                             <td>${value.id}</td>
                             <td>${value.nombre}</td>
                             <td>${value.club}</td>
                             <td>${value.perfil}</td>
                             <td>${value.posicion}</td>
                             <td>${value.salario}</td>
                             <td><a href=""  onclick="init.deleteJugador(${value.id}); return false">(-)</a> <a href=""  onclick="init.modificarJugadorShow(${value.id}); return false">(modificar)</a></td>
                         </tr>
                    `;
                })

                tabla+='</tbody></table>';
                $('#jugadoresTabla').append(tabla);
            }

        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            alert("La solicitud a fallado: " + errorThrown);
        });
    },
    'postClub':function () {
        $.ajax({
            data: {
                "nombre" : $('#clubModalNombre').val(),
                "escudo" : $('#clubModalEscudo').val(),
                "limiteSalarial" : $('#clubModalLimiteSalarial').val()
            },
            type: "POST",
            dataType: "json",
            url: "/api/clubs"
        })
        .done(function( data ) {
            if(data.error){
                $('#clubModalMsg').show().html('<small><p>Error : '+data.msg+'</p></small>');
            }
            else location.reload();
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            $('#clubModalMsg').show().text("La solicitud a fallado: " + errorThrown);
        });
    },
    'deleteClub':function (id) {
        $.ajax({
            data: {},
            type: "DELETE",
            dataType: "json",
            url: "/api/clubs/"+id
        })
        .done(function( data ) {
            if(data.error){
                alert('Error : '+data.msg);
            }
            else location.reload();
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            alert("La solicitud a fallado: " + errorThrown);
        });
    },
    'postJugador':function () {
        $.ajax({
            data: {
                "nombre" : $('#jugadorModalNombre').val(),
                "club" : $('#jugadorModalClub').val(),
                "perfil" : $('#jugadorModalPerfil').val(),
                "posicion" : $('#jugadorModalPosicion').val(),
                "fechaNacimiento" : $('#jugadorModalFechaNacimiento').val(),
                "salario" : $('#jugadorModalSalario').val(),
                "email" : $('#jugadorModalEmail').val()
            },
            type: "POST",
            dataType: "json",
            url: "/api/players"
        })
        .done(function( data ) {
            if(data.error){
                $('#jugadorModalMsg').show().html('<small><p>Error : '+data.msg+'</p></small>');
            }
            else location.reload();
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            $('#jugadorModalMsg').show().text("La solicitud a fallado: " + errorThrown);
        });
    },
    'putJugador':function (id) {
        $.ajax({
            data: {
                "id" : id,
                "nombre" : $('#jugadorModificarModalNombre').val(),
                "club" : $('#jugadorModificarModalClub').val(),
                "perfil" : $('#jugadorModificarModalPerfil').val(),
                "posicion" : $('#jugadorModificarModalPosicion').val(),
                "fechaNacimiento" : $('#jugadorModificarModalFechaNacimiento').val(),
                "salario" : $('#jugadorModificarModalSalario').val(),
                "email" : $('#jugadorModificarModalEmail').val()
            },
            type: "PUT",
            dataType: "json",
            url: "/api/player"
        })
        .done(function( data ) {
            if(data.error){
                $('#jugadorModificarModalMsg').show().html('<small><p>Error : '+data.msg+'</p></small>');
            }
            else location.reload();
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            $('#jugadorModificarModalMsg').show().text("La solicitud a fallado: " + errorThrown);
        });
    },
    'deleteJugador':function (id) {
        $.ajax({
            data: {},
            type: "DELETE",
            dataType: "json",
            url: "/api/players/"+id
        })
            .done(function( data ) {
                if(data.error){
                    alert('Error : '+data.msg);
                }
                else location.reload();
                console.log( data );
            })
            .fail(function( jqXHR, textStatus, errorThrown ) {
                alert("La solicitud a fallado: " + errorThrown);
            });
    },
    'postPerfil':function () {

        $.ajax({
            data: {
                "nombre" : $('#perfilModalNombre').val()
            },
            type: "POST",
            dataType: "json",
            url: "/api/perfils"
        })
        .done(function( data ) {
            if(data.error){
                $('#perfilModalMsg').show().html('<small><p>Error : '+data.msg+'</p></small>');
            }
            else location.reload();
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            $('#perfilModalMsg').show().text("La solicitud a fallado: " + errorThrown);
        });
    },
    'deletePerfil':function (id) {
        $.ajax({
            data: {},
            type: "DELETE",
            dataType: "json",
            url: "/api/perfils/"+id
        })
        .done(function( data ) {
            if(data.error){
                alert('Error : '+data.msg);
            }
            else location.reload();
            console.log( data );
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            alert("La solicitud a fallado: " + errorThrown);
        });
    },
    'modificarJugadorShow':function (id) {
        $('#modalModificarJugador').modal();

        $.ajax({
            data: {},
            type: "GET",
            dataType: "json",
            url: "api/players/"+id
        })
        .done(function( data ) {
            if(data.error){
                $('#jugadorModificarModalMsg').show().html('<small><p>Error : '+data.msg+'</p></small>');
            }
            else{
                $('#jugadorModificarModalId').val(data.jugador.id);
                $('#jugadorModificarModalNombre').val(data.jugador.nombre);
                $('#jugadorModificarModalClub').val(data.jugador.club);
                $('#jugadorModificarModalPerfil').val(data.jugador.perfil);
                $('#jugadorModificarModalPosicion').val(data.jugador.posicion);
                $('#jugadorModificarModalSalario').val(data.jugador.salario);
                $('#jugadorModificarModalEmail').val(data.jugador.email);
                $('#jugadorModificarModalFechaNacimiento').val(data.jugador.fechaNacimiento.substring(0, 10));
            }
        })
        .fail(function( jqXHR, textStatus, errorThrown ) {
            $('#jugadorModificarModalMsg').show().text("La solicitud a fallado: " + errorThrown);
        });
    }
}


$(function() {
    init.handlers();
    init.initData();
});