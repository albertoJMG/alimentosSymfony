<?php

namespace App\Model;

class Model
{
    protected $conexion;

    public function __construct($dbname, $dbuser, $dbpass, $dbhost)
    {
        $mvc_bd_conexion = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

        if (!$mvc_bd_conexion) {
            die('No ha sido posible realizar la conexión con la base de datos: ' . mysqli_error($mvc_bd_conexion));
        }


        mysqli_set_charset($mvc_bd_conexion, 'utf8');

        $this->conexion = $mvc_bd_conexion;
    }



    public function bd_conexion()
    {
    }

    public function dameAlimentos()
    {
        $sql = "select * from alimentos order by energia desc";

        $result = mysqli_query($this->conexion, $sql);

        $alimentos = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $alimentos[] = $row;
        }
        mysqli_close($this->conexion);
        return $alimentos;
    }

    public function buscarAlimentosPorNombre($nombre)
    {
        $nombre = htmlspecialchars($nombre);

        $sql = "select * from alimentos where nombre like '" . $nombre . "' order by energia desc";

        $result = mysqli_query($this->conexion, $sql);

        $alimentos = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $alimentos[] = $row;
        }
        mysqli_close($this->conexion);

        return $alimentos;
    }

    public function buscarAlimentosPorEnergia($energia)
    {
        $energia = htmlspecialchars($energia);

        $sql = "select * from alimentos where energia=" . $energia . " order by energia desc";

        $result = mysqli_query($this->conexion, $sql);

        $alimentos = array();
        if($result != false){
            while ($row = mysqli_fetch_assoc($result)) {
                $alimentos[] = $row;
            }
        }
        
        mysqli_close($this->conexion);
        return $alimentos;
    }
    public function buscarAlimentosCombinada($energia, $nombre)
    {
        $energia = htmlspecialchars($energia);
        $nombre = htmlspecialchars($nombre);
        $sql = "select * from alimentos where energia=" . $energia . " and nombre like '" . $nombre . "' order by nombre desc";

        $result = mysqli_query($this->conexion, $sql);

        $alimentos = array();
        if($result != false){
            while ($row = mysqli_fetch_assoc($result)) {
                $alimentos[] = $row;
            }
        }
        mysqli_close($this->conexion);
        return $alimentos;
    }

    public function dameAlimento($id, $close)
    {
        $id = htmlspecialchars($id);

        $sql = "select * from alimentos where id=" . $id;

        $result = mysqli_query($this->conexion, $sql);

        $alimentos = array();
        $row = mysqli_fetch_assoc($result);
        if($close){
            mysqli_close($this->conexion);
        }
        

        return $row;
    }

    public function insertarAlimento($n, $e, $p, $hc, $f, $g)
    {
        $n = htmlspecialchars($n);
        $e = htmlspecialchars($e);
        $p = htmlspecialchars($p);
        $hc = htmlspecialchars($hc);
        $f = htmlspecialchars($f);
        $g = htmlspecialchars($g);

        $sql = "insert into alimentos (nombre, energia, proteina, hidratocarbono, fibra, grasatotal) values ('" .
            $n . "'," . $e . "," . $p . "," . $hc . "," . $f . "," . $g . ")";

        $result = mysqli_query($this->conexion, $sql);
        mysqli_close($this->conexion);

        return $result;
    }

    public function validarDatos($n, $e, $p, $hc, $f, $g)
    {
        return (is_string($n) &
            is_numeric($e) &
            is_numeric($p) &
            is_numeric($hc) &
            is_numeric($f) &
            is_numeric($g));
    }

    public function modificarAlimento($n, $e, $p, $hc, $f, $g, $id)
    {
        $sql = "update alimentos set nombre='" . $n . "', energia=" . $e . ", proteina=" . $p . ", hidratocarbono=" . $hc . ", fibra=" . $f . ", grasatotal=" . $g . " where id=" . $id;
        mysqli_query($this->conexion, $sql);
        mysqli_close($this->conexion);
    }

    public function eliminar($id)
    {
        $sql = "delete from alimentos where id=" . $id;
        $result = mysqli_query($this->conexion, $sql);
        mysqli_close($this->conexion);
    }

    public function buscarUsuario($usuario, $passwordEntrante, $close)
    {

        //$close (true/false) indica si el metodo debe cerrar o no la conexion.

        $usuario = htmlspecialchars($usuario);
        $password = htmlspecialchars($passwordEntrante);

        $sql = "select * from usuarios where nomUsuario like '" . $usuario . "'";

        $result = mysqli_query($this->conexion, $sql);

        $row = mysqli_fetch_assoc($result);

        $pass = password_verify($password, $row['password']);

        if ($row['nomUsuario'] == $usuario && $pass) {
            return $row;
        }

        if ($close) {
            mysqli_close($this->conexion);
        }
    }

    public function insertarUsuario($usuario, $pass)
    {
        $usuario = htmlspecialchars($usuario);
        $pass = htmlspecialchars($pass);

        $sql = "insert into usuarios (nomUsuario, `password`) values ('" . $usuario . "', '" . $pass . "')";

        $result = mysqli_query($this->conexion, $sql);
        mysqli_close($this->conexion);
        return $result;
    }

    public function conectado($close)
    {
        if (isset($_SESSION['usuario'])) {
            return $_SESSION['usuario'];
        }

        
        
    }
}
