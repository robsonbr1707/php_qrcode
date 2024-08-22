<?php

include_once('./vendor/autoload.php');

define('PATH', 'qrcodes.json');

function qrcodes()
{
    return file_exists(PATH) ? json_decode(file_get_contents(PATH), true) : [];
}

function json(array $data)
{
    echo json_encode($data);
    exit;
}

function save(array $data) 
{
    return file_put_contents(PATH, json_encode($data, JSON_PRETTY_PRINT));
}

function method()
{
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
        return 'get';
    }
    return (isset($_POST['action']) ? strtolower($_POST['action']) : 'post');
}

switch (method()) {
    
    case 'get':
        if (isset($_GET['id'])) {
            $qrcodes = qrcodes();
            
            foreach ($qrcodes as $qrcode) {
                if ($qrcode['id'] == $_GET['id']) {
                    $qrcodeImg = $qrcode['qrcode'];
                    $qrcodeTitle = $qrcode['name'];
                    $qrcodeLink = $qrcode['link'];

                    $mpdf = new \Mpdf\Mpdf();
                    $mpdf->WriteHTML("<div style='text-align:center;'><h1>QRCode - $qrcodeTitle</h1><p>Link: $qrcodeLink</p><img src='$qrcodeImg' style='width:320px;'></div>");
                    return $mpdf->Output();
                }
            }
        }
        $qrcodes = qrcodes();
    break;

    case 'post':
        $fields = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        $qrcodeUrl = $fields['link'];
        $qrcodeClass = (new \chillerlan\QRCode\QRCode())->render($qrcodeUrl);

        $record = [
            'id'     => time(),
            'name'   => $fields['name'],
            'link'   => $fields['link'],
            'qrcode' => $qrcodeClass
        ];

        $data = qrcodes();
        $data[] = $record;

        save($data);
        return json(qrcodes());
    break;

    case 'put':
        $data = qrcodes();
        $fields = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        
        foreach ($data as &$record) {
            if ($record['id'] == $fields['id']) {
                $record['name'] = $fields['name'];
                $record['link'] = $fields['link'];
                $qrcodeClass = (new \chillerlan\QRCode\QRCode())->render($fields['link']);
                $record['qrcode'] = $qrcodeClass;
                break;
            }
        }

        save($data);
        return json(qrcodes());
    break;

    case 'delete':
        $data = qrcodes();
        $data = array_filter($data, function($record) {
            return $record['id'] != $_POST['id'];
        });

        save($data);
        return json(qrcodes());
    break;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Qrcodes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="scripts.js"></script>
</head>
<body class="d-flex justify-content-center align-items-center bg-body-secondary" style="height: 80vh;">
    <div class="container bg-light py-4" style="width: 520px;">
    <form id="form" class="border p-2 my-4">
        <img src="" id="image" class="mx-auto text-center" alt="" style="display:block; width: 200px;">
        <input type="hidden" id="id" name="id" value="">
        <label for="name" class="form-label">Título</label>
        <input type="text" id="name" class="form-control" name="name" value="" placeholder="Nome" required>
        <label for="link" class="form-label">Link</label>
        <input type="text" id="link" class="form-control" name="link" value="" placeholder="ex: localhost:8080/sobre" required>
        <div class="d-flex gap-2 my-4">
            <button type="submit" class="btn btn-success">Salvar</button>
            <button type="button" id="btn-back" class="btn btn-info text-white" style="display: none;">Não atualizar</button>
        </div>
        <div id="message" class="p-2 my-2 text-white bg-success" style="display: none;"></div>
    </form>
    <ul id="records" class="list-group">
        <?php foreach ($qrcodes as $qrcode): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= $qrcode['name'] ?>
                <div class="d-flex gap-2">
                    <button value='<?= json_encode($qrcode); ?>' class="edit btn btn-primary">Editar</button>
                    <a href="<?= '?id='. $qrcode['id'] ?>" target="_blank" class="btn btn-success">Pdf</a>
                    <button value="<?= $qrcode['id']; ?>" class="del btn btn-danger">Excluir</button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    </div>
</body>
</html>