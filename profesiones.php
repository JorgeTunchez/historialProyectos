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

$objController = new profesiones_controller($arrRolUser);
$objController->editarEnvio();
$objController->process();
$objController->runAjax();
$objController->drawContentController();

class profesiones_controller{

    private $objModel;
    private $objView;
    private $arrRolUser;

    public function __construct($arrRolUser)
    {
        $this->objModel = new profesiones_model();
        $this->objView = new profesiones_view($arrRolUser);
        $this->arrRolUser = $arrRolUser;
    }

    public function drawContentController()
    {
        $this->objView->drawContent();
    }

    public function runAjax()
    {
        $this->ajaxDestroySession();
        $this->editarProfesion();
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

    public function editarProfesion(){
        if(isset($_POST["idProfesion"])){
            header("Content-Type: application/json;");
            $idProfesion = isset($_POST["idProfesion"])? $_POST["idProfesion"]: "";
            $arrDatosProfesion = $this->objModel->getDataProfesion($idProfesion);
            $arrReturn["ID_PROFESION"] = $arrDatosProfesion["ID_PROFESION"];
            $arrReturn["CODIGO"] = $arrDatosProfesion["CODIGO"];
            $arrReturn["NOMBRE"] = $arrDatosProfesion["NOMBRE"];
            $arrReturn["EDITAR"] = 'Y';
            print json_encode($arrReturn);
            exit();
        }
    }

    public function eliminarRegistro(){
        if( isset($_POST['deleteProfesion']) && $_POST['deleteProfesion'] == 'Y' ){
            $idProfesion = isset($_POST["delete_id"]) ? trim($_POST["delete_id"]) : '';
            $this->objModel->deleteProfesion($idProfesion);
            exit();
        }
    }

    public function editarEnvio(){
        if( isset($_POST['editarEnvio']) && $_POST['editarEnvio'] == 'Y' ){
            $idProfesion = isset($_POST["edit_id"]) ? trim($_POST["edit_id"]) : '';
            $codigo = isset($_POST["edit_codigo"]) ? trim($_POST["edit_codigo"]) : '';
            $nombre = isset($_POST["edit_nombre"]) ? trim($_POST["edit_nombre"]) : '';
            $this->objModel->updateProfesion($idProfesion, $codigo, $nombre, $this->arrRolUser["ID"]);
            exit();
        }
    }

    public function process(){
        if( isset($_POST['process']) && $_POST['process'] == 'Y' ){
            $codigo = isset($_POST["add_codigo"]) ? strtolower(trim($_POST["add_codigo"])):'';
            $nombre = isset($_POST["add_nombre"]) ? strtolower(trim($_POST["add_nombre"])):'';
            $this->objModel->insertProfesion($codigo, $nombre, $this->arrRolUser["ID"]);
        }
    }
}

class profesiones_model{

    public function getProfesiones(){
        $arrProfesiones = array();
        $strQuery = "SELECT id_profesion, codigo, nombre FROM profesion ORDER BY nombre";
        $result = executeQuery($strQuery);
        if (!empty($result)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $arrProfesiones[$row["id_profesion"]]["CODIGO"]= $row["codigo"];
                $arrProfesiones[$row["id_profesion"]]["NOMBRE"]= $row["nombre"];
            }
        }

        return $arrProfesiones;
    }

    public function getDataProfesion($idProfesion){
        if( $idProfesion!='' ){
            $arrProfesion = array();
            $strQuery = "SELECT id_profesion, codigo, nombre FROM profesion WHERE id_profesion = {$idProfesion}";
            $result = executeQuery($strQuery);
            if (!empty($result)) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $arrProfesion["ID_PROFESION"]= $row["id_profesion"];
                    $arrProfesion["CODIGO"]= $row["codigo"];
                    $arrProfesion["NOMBRE"]= $row["nombre"];
                }
            }

            return $arrProfesion;
        }
    }

    public function insertProfesion($codigo, $nombre, $intUser){
        if ($codigo != '' && $nombre != '' && $intUser > 0) {
            $strQuery = "INSERT INTO profesion (codigo, nombre, add_user, add_fecha) 
                         VALUES ('{$codigo}', '{$nombre}', {$intUser}, now())";
            executeQuery($strQuery);
        }
    }

    public function deleteProfesion($idProfesion){
        if( $idProfesion!='' ){
            $strQuery = "DELETE FROM profesion WHERE id_profesion = {$idProfesion}";
            executeQuery($strQuery);
        }
    }

    public function updateProfesion($idProfesion, $codigo, $nombre, $intUser ){
        if ($idProfesion != '' && $codigo != '' && $nombre != '' && $intUser > 0) {
            $strQuery = "UPDATE profesion 
                            SET codigo = '{$codigo}', 
                                nombre = '{$nombre}', 
                                mod_user = {$intUser}, 
                                mod_fecha = now() 
                         WHERE id_profesion = {$idProfesion}";
            executeQuery($strQuery);
        }
    }

}

class profesiones_view{

    private $objModel;
    private $arrRolUser;

    public function __construct($arrRolUser)
    {
        $this->objModel = new profesiones_model();
        $this->arrRolUser = $arrRolUser;
    }

    public function drawModalAgregar(){
        ?>
        <div class="modal fade" id="addModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Profesion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="divRegistrarProfesion">
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Código</label>
                        <div class="col-sm-10">
                            <input type="text" id="add_codigo" name="add_codigo" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Nombre</label>
                        <div class="col-sm-10">
                            <input type="text" id="add_nombre" name="add_nombre" class="form-control">
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
                    <h5 class="modal-title">Editar Profesion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="divEditarProfesion">
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Código</label>
                        <div class="col-sm-10">
                            <input type="text" id="edit_codigo" name="edit_codigo" class="form-control">
                            <input type="hidden" id="edit_id" name="edit_id" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="inputText" class="col-sm-2 col-form-label">Nombre</label>
                        <div class="col-sm-10">
                            <input type="text" id="edit_nombre" name="edit_nombre" class="form-control">
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
                    <h5 class="modal-title">Eliminar Profesion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="divEliminarProfesion">
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
                <h1>Profesiones</h1>
                <nav>
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Inicio</a></li>
                    <li class="breadcrumb-item">Catalogos</li>
                    <li class="breadcrumb-item active">Profesiones</li>
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
                                        <h5 class="card-title">Profesiones</h5>
                                    </div>
                                    <div class="col-lg-3 text-center">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">(+) Agregar</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <p>Administración de profesiones dentro del sistema.</p>
                                </div>
                                <div class="row">
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Código</th>
                                                <th scope="col">Nombre</th>
                                                <th scope="col">Editar</th>
                                                <th scope="col">Eliminar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $arrProfesiomes = $this->objModel->getProfesiones();
                                            $intConteo = 0;
                                            foreach( $arrProfesiomes as $key => $val ){
                                                $intConteo++;
                                                $intID = $key;
                                                $strCodigo = trim($val["CODIGO"]);
                                                $strNombre = trim($val["NOMBRE"]);
                                                ?>
                                                <tr>
                                                    <td><?php print $intConteo; ?></td>
                                                    <td><?php print $strCodigo; ?></td>
                                                    <td><?php print $strNombre; ?></td>
                                                    <td> 
                                                        <button type="button" class="btn btn-info" onclick="editarProfesion('<?php print $intID; ?>')">
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
                    var nombre = $("#add_nombre").val();

                    if( codigo=='' ){
                        alert("El campo codigo no pueder estar vacio");
                    }else if( nombre=='' ){
                        alert("El campo nombre no pueder estar vacio");
                    }else{
                        console.log("Success Saving");
                        $.ajax({
                            url: "profesiones.php",
                            data:  $("#divRegistrarProfesion").find("select, input").serialize() + "&process=Y",
                            type: "POST",
                            beforeSend: function() {
                                //$("#divShowLoadingGeneralBig").show();
                            },
                            success: function(data) {
                                //$("#divShowLoadingGeneralBig").hide();
                                location.href = "profesiones.php";
                            }
                        });
                    }
                }

                function editarProfesion(idProfesion){
                    $.ajax({
                        url: "profesiones.php",
                        data: {
                            idProfesion: idProfesion
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
                                $('#edit_id').val(data.ID_PROFESION);
                                $('#edit_codigo').val(data.CODIGO);
                                $('#edit_nombre').val(data.NOMBRE);
                            }
                            //$("#divShowLoadingGeneralBig").hide();
   
                        }
                    });

                }

                function editarEnvio(){
                    var codigo = $("#edit_codigo").val();
                    var nombre = $("#edit_nombre").val();

                    if( codigo=='' ){
                        alert("El campo codigo no pueder estar vacio");
                    }else if( nombre=='' ){
                        alert("El campo nombre no pueder estar vacio");
                    }else{
                        $.ajax({
                            url: "profesiones.php",
                            data:  $("#divEditarProfesion").find("select, input").serialize() + "&editarEnvio=Y",
                            type: "POST",
                            beforeSend: function() {
                                //$("#divShowLoadingGeneralBig").show();
                            },
                            success: function(data) {
                                //$("#divShowLoadingGeneralBig").hide();
                                location.href = "profesiones.php";
                            }
                        });
                    }
                }

                function openModalEliminar(idProfesion){
                    $('#deleteModal').modal('show');
                    $("#delete_id").val(idProfesion);
                }

                function eliminarRegistro(idProfesion){
                    $.ajax({
                        url: "profesiones.php",
                        data:  $("#divEliminarProfesion").find("select, input").serialize() + "&deleteProfesion=Y",
                        type: "POST",
                        beforeSend: function() {
                            //$("#divShowLoadingGeneralBig").show();
                        },
                        success: function(data) {
                            //$("#divShowLoadingGeneralBig").hide();
                            location.href = "profesiones.php";
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