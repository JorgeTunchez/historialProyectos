<?php
require_once("core/core.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (isset($_SESSION['user_id'])) {
    $strRolUserSession = getRolUserSession($_SESSION['user_id']);
    $intIDUserSession = getIDUserSession($_SESSION['user_id']);

    if ($strRolUserSession != '') {
        $arrRolUser["ID"] = $intIDUserSession;
        $arrRolUser["NAME"] = $_SESSION['user_id'];

        if ($strRolUserSession == "master") {
            $arrRolUser["MASTER"] = true;
        } elseif ($strRolUserSession == "normal") {
            $arrRolUser["NORMAL"] = true;
        }
    }
} else {
    header("Location: index.php");
}

$objController = new clientes_controller($arrRolUser);
$objController->editarEnvio();
$objController->process();
$objController->runAjax();
$objController->drawContentController();

class clientes_controller
{

    private $objModel;
    private $objView;
    private $arrRolUser;

    public function __construct($arrRolUser)
    {
        $this->objModel = new clientes_model();
        $this->objView = new clientes_view($arrRolUser);
        $this->arrRolUser = $arrRolUser;
    }

    public function drawContentController()
    {
        $this->objView->drawContent();
    }

    public function runAjax()
    {
        $this->ajaxDestroySession();
        $this->editarCliente();
        $this->eliminarRegistro();
    }

    public function ajaxDestroySession()
    {
        if (isset($_POST["destroSession"])) {
            header("Content-Type: application/json;");
            session_destroy();
            $arrReturn["Correcto"] = "Y";
            print json_encode($arrReturn);
            exit();
        }
    }

    public function editarCliente(){
        if(isset($_POST["idCliente"])){
            header("Content-Type: application/json;");
            $idCliente = isset($_POST["idCliente"])? $_POST["idCliente"]: "";
            $arrDatosCliente = $this->objModel->getDataCliente($idCliente);
            $arrReturn["IDCLIENTE"] = $arrDatosCliente["IDCLIENTE"];
            $arrReturn["CODIGO"] = $arrDatosCliente["CODIGO"];
            $arrReturn["NIT"] = $arrDatosCliente["NIT"];
            $arrReturn["NOMBRE"] = $arrDatosCliente["NOMBRE"];
            $arrReturn["DIRECCION"] = $arrDatosCliente["DIRECCION"];
            $arrReturn["PAIS"] = $arrDatosCliente["PAIS"];
            $arrReturn["TELEFONO"] = $arrDatosCliente["TELEFONO"];
            $arrReturn["EMAIL"] = $arrDatosCliente["EMAIL"];
            $arrReturn["EDITAR"] = 'Y';
            print json_encode($arrReturn);
            exit();
        }
    }

    public function eliminarRegistro(){
        if( isset($_POST['deleteCliente']) && $_POST['deleteCliente'] == 'Y' ){
            $idCliente = isset($_POST["delete_id"]) ? trim($_POST["delete_id"]) : '';
            $this->objModel->deleteCliente($idCliente);
            exit();
        }
    }

    public function editarEnvio(){
        if( isset($_POST['editarEnvio']) && $_POST['editarEnvio'] == 'Y' ){
            $idCliente = isset($_POST["edit_id"]) ? trim($_POST["edit_id"]) : '';
            $codigo = isset($_POST["edit_codigo"]) ? trim($_POST["edit_codigo"]) : '';
            $nit = isset($_POST["edit_nit"]) ? trim($_POST["edit_nit"]) : '';
            $nombre = isset($_POST["edit_nombre"]) ? trim($_POST["edit_nombre"]) : '';
            $direccion = isset($_POST["edit_direccion"]) ? trim($_POST["edit_direccion"]) : '';
            $pais = isset($_POST["edit_pais"]) ? trim($_POST["edit_pais"]) : '';
            $telefono = isset($_POST["edit_telefono"]) ? trim($_POST["edit_telefono"]) : '';
            $email = isset($_POST["edit_email"]) ? trim($_POST["edit_email"]) : '';
            $this->objModel->updateCliente($idCliente, $codigo, $nit, $nombre, $direccion, $pais, $telefono, $email, $this->arrRolUser["ID"]);
            exit();
        }
    }

    public function process(){
        if( isset($_POST['process']) && $_POST['process'] == 'Y' ){
            $codigo = isset($_POST["add_codigo"]) ? strtolower(trim($_POST["add_codigo"])):'';
            $nit = isset($_POST["add_nit"]) ? strtolower(trim($_POST["add_nit"])):'';
            $nombre = isset($_POST["add_nombre"]) ? strtolower(trim($_POST["add_nombre"])):'';
            $direccion = isset($_POST["add_direccion"]) ? strtolower(trim($_POST["add_direccion"])):'';
            $pais = isset($_POST["add_pais"]) ? strtolower(trim($_POST["add_pais"])):'';
            $telefono = isset($_POST["add_telefono"]) ? strtolower(trim($_POST["add_telefono"])):'';
            $email = isset($_POST["add_email"]) ? strtolower(trim($_POST["add_email"])):'';
            $this->objModel->insertCliente($codigo, $nit, $nombre, $direccion, $pais, $telefono, $email, $this->arrRolUser["ID"]);
        }
    }
}

class clientes_model
{

    public function getClientes(){
        $arrClientes = array();
        $strQuery = "SELECT id_cliente,
                            codigo,
                            nit,
                            nombre,
                            direccion,
                            pais,
                            telefono, 
                            email
                        FROM clientes
                    ORDER BY nombre";
        $result = executeQuery($strQuery);
        if (!empty($result)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $arrClientes[$row["id_cliente"]]["CODIGO"]= $row["codigo"];
                $arrClientes[$row["id_cliente"]]["NIT"]= $row["nit"];
                $arrClientes[$row["id_cliente"]]["NOMBRE"]= $row["nombre"];
                $arrClientes[$row["id_cliente"]]["DIRECCION"]= $row["direccion"];
                $arrClientes[$row["id_cliente"]]["PAIS"]= $row["pais"];
                $arrClientes[$row["id_cliente"]]["TELEFONO"]= $row["telefono"];
                $arrClientes[$row["id_cliente"]]["EMAIL"]= $row["email"];
            }
        }

        return $arrClientes;
    }

    public function getDataCliente($idCliente){
        if( $idCliente!='' ){
            $arrCliente = array();
            $strQuery = "SELECT id_cliente,
                                codigo,
                                nit,
                                nombre,
                                direccion,
                                pais,
                                telefono, 
                                email
                           FROM clientes
                          WHERE id_cliente = {$idCliente}";
            $result = executeQuery($strQuery);
            if (!empty($result)) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $arrCliente["IDCLIENTE"]= $row["id_cliente"];
                    $arrCliente["CODIGO"]= $row["codigo"];
                    $arrCliente["NIT"]= $row["nit"];
                    $arrCliente["NOMBRE"]= $row["nombre"];
                    $arrCliente["DIRECCION"]= $row["direccion"];
                    $arrCliente["PAIS"]= $row["pais"];
                    $arrCliente["TELEFONO"]= $row["telefono"];
                    $arrCliente["EMAIL"]= $row["email"];
                }
            }

            return $arrCliente;
        }
    }

    public function insertCliente($codigo, $nit, $nombre, $direccion, $pais, $telefono, $email, $intUser){
        if ($codigo != '' && $nit != '' && $nombre != ''&& $direccion != '' && $pais != '' && $telefono != '' && $email != '' && $intUser > 0) {
            $strQuery = "INSERT INTO clientes (codigo, nit, nombre, direccion, pais, telefono, email, add_user, add_fecha) 
                         VALUES ('{$codigo}', '{$nit}', '{$nombre}', '{$direccion}', '{$pais}', '{$telefono}', '{$email}', {$intUser}, now())";
            executeQuery($strQuery);
        }
    }

    public function deleteCliente($idCliente){
        if( $idCliente!='' ){
            $strQuery = "DELETE FROM clientes WHERE id_cliente = {$idCliente}";
            executeQuery($strQuery);
        }
    }

    public function updateCliente($idCliente, $codigo, $nit, $nombre, $direccion, $pais, $telefono, $email, $intUser ){
        if ($idCliente != '' && $codigo != '' && $nit != '' && $nombre != ''&& $direccion != '' && $pais != '' && $telefono != '' && $email != '' && $intUser > 0) {
            $strQuery = "UPDATE clientes 
                            SET codigo = '{$codigo}', 
                                nit = '{$nit}' , 
                                nombre = '{$nombre}', 
                                direccion = '{$direccion}', 
                                pais = '{$pais}', 
                                telefono = '{$telefono}', 
                                email = '{$email}', 
                                mod_user = {$intUser}, 
                                mod_fecha = now() 
                         WHERE id_cliente = {$idCliente}";
            executeQuery($strQuery);
        }
    }

}

class clientes_view
{

    private $objModel;
    private $arrRolUser;

    public function __construct($arrRolUser)
    {
        $this->objModel = new clientes_model();
        $this->arrRolUser = $arrRolUser;
    }

    public function drawModalAgregar(){
        ?>
        <div class="modal fade" id="addModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="divRegistrarCliente">
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Código</label>
                        <div class="col-sm-10">
                            <input type="text" id="add_codigo" name="add_codigo" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">NIT</label>
                        <div class="col-sm-10">
                            <input type="text" id="add_nit" name="add_nit" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Nombre</label>
                        <div class="col-sm-10">
                            <input type="text" id="add_nombre" name="add_nombre" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Dirección</label>
                        <div class="col-sm-10">
                            <input type="text" id="add_direccion" name="add_direccion" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Pais</label>
                        <div class="col-sm-10">
                            <input type="text" id="add_pais" name="add_pais" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Teléfono</label>
                        <div class="col-sm-10">
                            <input type="number" id="add_telefono" name="add_telefono" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input type="email" id="add_email" name="add_email" class="form-control">
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="validarEnvio()">Guardar</button>
                </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function drawModalEditar(){
        ?>
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="divEditarCliente">
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Código</label>
                        <div class="col-sm-10">
                            <input type="text" id="edit_codigo" name="edit_codigo" class="form-control">
                            <input type="hidden" id="edit_id" name="edit_id" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">NIT</label>
                        <div class="col-sm-10">
                            <input type="text" id="edit_nit" name="edit_nit" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Nombre</label>
                        <div class="col-sm-10">
                            <input type="text" id="edit_nombre" name="edit_nombre" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Dirección</label>
                        <div class="col-sm-10">
                            <input type="text" id="edit_direccion" name="edit_direccion" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Pais</label>
                        <div class="col-sm-10">
                            <input type="text" id="edit_pais" name="edit_pais" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Teléfono</label>
                        <div class="col-sm-10">
                            <input type="number" id="edit_telefono" name="edit_telefono" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input type="email" id="edit_email" name="edit_email" class="form-control">
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="editarEnvio()">Guardar</button>
                </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function drawModalEliminar(){
        ?>
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="divEliminarCliente">
                    <div class="row ">
                        <div class="col-sm-12">
                            <label for="inputText" class="col-sm-12 col-form-label">
                            Desea eliminar el registro?
                            </label>
                            <input type="hidden" id="delete_id" name="delete_id" class="form-control">
                        </div>
                    </div>    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="eliminarRegistro()">Guardar</button>
                </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function drawContent(){
        drawHead(false);
            drawHeader($this->arrRolUser["NAME"]);
            drawMenu(); 
            $this->drawModalAgregar();
            $this->drawModalEditar();
            $this->drawModalEliminar();
            ?>
            <main id="main" class="main">

                <div class="pagetitle">
                <h1>Clientes</h1>
                <nav>
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Inicio</a></li>
                    <li class="breadcrumb-item">Catalogos</li>
                    <li class="breadcrumb-item active">Clientes</li>
                    </ol>
                </nav>
                </div><!-- End Page Title -->

                <section class="section">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center h-100">
                                    <div class="col-lg-9">
                                        <h5 class="card-title">Clientes</h5>
                                    </div>
                                    <div class="col-lg-3 text-center">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">(+) Agregar</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <p>Administración de clientes dentro del sistema.</p>
                                </div>
                                <div class="row">
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Código</th>
                                                <th scope="col">Nit</th>
                                                <th scope="col">Nombre</th>
                                                <th scope="col">Pais</th>
                                                <th scope="col">Teléfono</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Editar</th>
                                                <th scope="col">Eliminar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $arrClientes = $this->objModel->getClientes();
                                            $intConteo = 0;
                                            foreach( $arrClientes as $key => $val ){
                                                $intConteo++;
                                                $intID = $key;
                                                $strCodigo = trim($val["CODIGO"]);
                                                $strNit = trim($val["NIT"]);
                                                $strNombre = trim($val["NOMBRE"]);
                                                $strPais = trim($val["PAIS"]);
                                                $strTelefono = trim($val["TELEFONO"]);
                                                $strEmail = trim($val["EMAIL"]);
                                                ?>
                                                <tr>
                                                    <td><?php print $intConteo; ?></td>
                                                    <td><?php print $strCodigo; ?></td>
                                                    <td><?php print $strNit; ?></td>
                                                    <td><?php print $strNombre; ?></td>
                                                    <td><?php print $strPais; ?></td>
                                                    <td><?php print $strTelefono; ?></td>
                                                    <td><?php print $strEmail; ?></td>
                                                    <td> 
                                                        <button type="button" class="btn btn-info" onclick="editarCliente('<?php print $intID; ?>')">
                                                            Editar
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger" onclick="openModalEliminar('<?php print $intID; ?>')">
                                                            Eliminar
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </section>

            </main><!-- End #main -->
            <?php
            drawFooter();
            ?>
            <script>
                function validarEnvio(){
                    var codigo = $("#add_codigo").val();
                    var nit = $("#add_nit").val();
                    var nombre = $("#add_nombre").val();
                    var direccion = $("#add_direccion").val();
                    var pais = $("#add_pais").val();
                    var telefono = $("#add_telefono").val();
                    var email = $("#add_email").val();

                    if( codigo=='' ){
                        alert("El campo codigo no pueder estar vacio");
                    }else if( nit=='' ){
                        alert("El campo NIT no pueder estar vacio");
                    }else if( nombre=='' ){
                        alert("El campo nombre no pueder estar vacio");
                    }else if( direccion=='' ){
                        alert("El campo direccion no pueder estar vacio");
                    }else if( pais=='' ){
                        alert("El campo pais no pueder estar vacio");
                    }else if( telefono=='' ){
                        alert("El campo telefono no pueder estar vacio");
                    }else if( email=='' ){
                        alert("El campo email no pueder estar vacio");
                    }else{
                        console.log("Success Saving");
                        $.ajax({
                            url: "clientes.php",
                            data:  $("#divRegistrarCliente").find("select, input").serialize() + "&process=Y",
                            type: "POST",
                            beforeSend: function() {
                                //$("#divShowLoadingGeneralBig").show();
                            },
                            success: function(data) {
                                //$("#divShowLoadingGeneralBig").hide();
                                location.href = "clientes.php";
                            }
                        });
                    }
                }

                function editarCliente(idCliente){
                    $.ajax({
                        url: "clientes.php",
                        data: {
                            idCliente: idCliente
                        },
                        type: "POST",
                        dataType: "json",
                        beforeSend: function() {
                            //$("#divShowLoadingGeneralBig").show();
                        },
                        success: function(data) {
                            if (data.EDITAR == "Y") {
                                //$('#editModal').modal();
                                $('#editModal').modal('show');
                                $('#edit_id').val(data.IDCLIENTE);
                                $('#edit_codigo').val(data.CODIGO);
                                $('#edit_nit').val(data.NIT);
                                $('#edit_nombre').val(data.NOMBRE);
                                $('#edit_direccion').val(data.DIRECCION);
                                $('#edit_pais').val(data.PAIS);
                                $('#edit_telefono').val(data.TELEFONO);
                                $('#edit_email').val(data.EMAIL);
                            }
                            //$("#divShowLoadingGeneralBig").hide();
   
                        }
                    });

                }

                function editarEnvio(){
                    var codigo = $("#edit_codigo").val();
                    var nit = $("#edit_nit").val();
                    var nombre = $("#edit_nombre").val();
                    var direccion = $("#edit_direccion").val();
                    var pais = $("#edit_pais").val();
                    var telefono = $("#edit_telefono").val();
                    var email = $("#edit_email").val();

                    if( codigo=='' ){
                        alert("El campo codigo no pueder estar vacio");
                    }else if( nit=='' ){
                        alert("El campo NIT no pueder estar vacio");
                    }else if( nombre=='' ){
                        alert("El campo nombre no pueder estar vacio");
                    }else if( direccion=='' ){
                        alert("El campo direccion no pueder estar vacio");
                    }else if( pais=='' ){
                        alert("El campo pais no pueder estar vacio");
                    }else if( telefono=='' ){
                        alert("El campo telefono no pueder estar vacio");
                    }else if( email=='' ){
                        alert("El campo email no pueder estar vacio");
                    }else{
                        $.ajax({
                            url: "clientes.php",
                            data:  $("#divEditarCliente").find("select, input").serialize() + "&editarEnvio=Y",
                            type: "POST",
                            beforeSend: function() {
                                //$("#divShowLoadingGeneralBig").show();
                            },
                            success: function(data) {
                                //$("#divShowLoadingGeneralBig").hide();
                                location.href = "clientes.php";
                            }
                        });
                    }
                }

                function openModalEliminar(idCliente){
                    $('#deleteModal').modal('show');
                    $("#delete_id").val(idCliente);
                }

                function eliminarRegistro(idCliente){
                    $.ajax({
                        url: "clientes.php",
                        data:  $("#divEliminarCliente").find("select, input").serialize() + "&deleteCliente=Y",
                        type: "POST",
                        beforeSend: function() {
                            //$("#divShowLoadingGeneralBig").show();
                        },
                        success: function(data) {
                            //$("#divShowLoadingGeneralBig").hide();
                            location.href = "clientes.php";
                        }
                    });
                }
            </script>
            </body>
        </html>
        <?php
    }
}

?>