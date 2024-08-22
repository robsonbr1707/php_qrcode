$(document).ready(function() {

    // Listar registros
    function listRecords(records) {
        $('#records').empty();
        $.each(records, function(index, record) {            
            const li = $('<li class="list-group-item d-flex justify-content-between align-items-center">').text(`${record.name}`).attr('data-id', record.id);

            const editBtn = $('<button class="edit btn btn-primary">').val(JSON.stringify(record)).text('Editar');
            const pdfBtn = $('<a href="?id='+record.id+'" class="btn btn-success" target="_blank">').text('Pdf');
            const deleteBtn = $('<button class="del btn btn-danger">').val(record.id).text('Excluir');

            const btnFlex = $('<div class="d-flex gap-2">').append(editBtn, pdfBtn, deleteBtn);
            li.append(btnFlex);
            $('#records').append(li);
        });
    }

    // Cria ou atualiza o registro
    $('#form').submit(function(event) {
        event.preventDefault();
        $("#btn-back").fadeOut();

        const formData = $(this).serialize();
        const action = $('#id').val() ? 'put' : 'post';
        
        $.post('index.php', formData + '&action=' + action, function(data) {   
            resetForms();
            
            listRecords(data);
        }, 'json');

        message("QRcode atualizado com sucesso!");
    });

    // Editar registro
    $(document).on("click", "#records .edit", function(){
        const data = JSON.parse($(this).val());        
        $("#btn-back").fadeIn();

        $('#id').val(data.id);
        $('#name').val(data.name);
        $('#link').val(data.link);
        $('#image').attr('src', data.qrcode).fadeIn();
    });
    
    // Remover registro
    $(document).on("click", "#records .del", function(){
        const id = $(this).val();
        
        $.post('index.php', { id: id, action: 'delete' }, function(data) {
            listRecords(data);
        }, 'json');

        resetForms();
        message("QRcode excluido com sucesso!");
    });

    // Remover btn de não atualizar
    $("#btn-back").click(function(){
        $('#image').attr('src', '').fadeOut();
        $(this).fadeOut();
        resetForms();
    });

    function resetForms(){
        $('#image').attr('src', '').fadeOut();
        $('#form')[0].reset();
        $('#form').find('#id').val('');
        $('#form').find('#qrcode').val('');
    }

    function message(message){
        $("#message").text(message).fadeIn();
        setTimeout(() => {
            $("#message").fadeOut();
        }, 2000);
    }
});