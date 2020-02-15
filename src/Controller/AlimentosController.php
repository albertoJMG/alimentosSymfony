<?php
// src/Controller/AlimentosController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Model\Model;
use App\Config\Config;
use Dompdf\Dompdf;
use Dompdf\Options;

$session = new Session();
$session->start();

class AlimentosController extends AbstractController
{

    public function inicio()
    {
        $params = array(
            'mensaje' => 'Bienvenido al curso de Symfony2',
            'fecha' => date('d-m-y'),
        );
        $m = new Model(Config::$mvc_bd_nombre, Config::$mvc_bd_usuario, Config::$mvc_bd_clave, Config::$mvc_bd_hostname);

        $session = new Session();
        $params['log'] = $session->get('usuario');

        return $this->render('alimentos/inicio.html.twig', $params);
    }

    public function crearPDF()
    {
        $m = new Model(
            Config::$mvc_bd_nombre,
            Config::$mvc_bd_usuario,
            Config::$mvc_bd_clave,
            Config::$mvc_bd_hostname
        );

        $params = array(
            'alimentos' => $m->dameAlimentos(),
        );

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('alimentos/crearPDF.html.twig', $params);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);
    }

    public function editarAlimento()
    {
        // if (!isset($_GET['id'])) {
        //     throw new Exception('Página no encontrada');
        // }

        $id = (int) $_GET['id'];
        $session = new Session();
        $params['log'] = $session->get('usuario');

        if ($params['log'] != "") {
            $m = new Model(Config::$mvc_bd_nombre, Config::$mvc_bd_usuario, Config::$mvc_bd_clave, Config::$mvc_bd_hostname);

            $alimento = $m->dameAlimento($id, false);
            $params = $alimento;

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // comprobar campos formulario
                if ($m->validarDatos($_POST['nombre'], $_POST['energia'], $_POST['proteina'], $_POST['hc'], $_POST['fibra'], $_POST['grasa']) && $_POST['nombre'] != "") {

                    $m->modificarAlimento($_POST['nombre'], $_POST['energia'], $_POST['proteina'], $_POST['hc'], $_POST['fibra'], $_POST['grasa'], $id);

                    $response = $this->forward('App\Controller\AlimentosController::listar');
                    return $response;
                } else {
                    $params = array(
                        'nombre' => $_POST['nombre'],
                        'energia' => $_POST['energia'],
                        'proteina' => $_POST['proteina'],
                        'hidratocarbono' => $_POST['hc'],
                        'fibra' => $_POST['fibra'],
                        'grasatotal' => $_POST['grasa'],
                    );
                    $params['mensaje'] = 'No se ha podido actualizar el alimento. Revisa el formulario';
                }
            }
            //$session = new Session();
            $params['log'] = $session->get('usuario');
            return $this->render('alimentos/editarAlimento.html.twig', $params);
        } else {
            $response = $this->forward('App\Controller\AlimentosController::inicio');
            return $response;
        }
    }

    public function listar()
    {
        $m = new Model(
            Config::$mvc_bd_nombre,
            Config::$mvc_bd_usuario,
            Config::$mvc_bd_clave,
            Config::$mvc_bd_hostname
        );

        $params = array(
            'alimentos' => $m->dameAlimentos(),
        );

        $session = new Session();
        $params['log'] = $session->get('usuario');

        return
            $this->render('alimentos/listar.html.twig', $params);
    }

    public function borrar()
    {
        $m = new Model(Config::$mvc_bd_nombre, Config::$mvc_bd_usuario, Config::$mvc_bd_clave, Config::$mvc_bd_hostname);
        $session = new Session();
        
        $params = array(
            'alimentos' => $m->dameAlimentos(),
        );
        $params['log'] = $session->get('usuario');

        if (isset($_REQUEST['eliminar']) && $_REQUEST['eliminar'] == true) {
            $id = (int) $_GET['id'];
            if ($params['log'] != "") {
                $m = new Model(Config::$mvc_bd_nombre, Config::$mvc_bd_usuario, Config::$mvc_bd_clave, Config::$mvc_bd_hostname);
                $m->eliminar($id);
                $response = $this->forward('App\Controller\AlimentosController::listar');
                return $response;
            } else {
                $response = $this->forward('App\Controller\AlimentosController::inicio');
                return $response;
            }
        }
        
        
    }

    public function login()
    {

        $params = array(
            'usuario' => '',
            'password' => '',
            'resultado' => array(),
        );

        $m = new Model(Config::$mvc_bd_nombre, Config::$mvc_bd_usuario, Config::$mvc_bd_clave, Config::$mvc_bd_hostname);
        $session = new Session();
        $params['log'] = $session->get('usuario');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $params['usuario'] = $_POST['nomUsuario'];
            $params['password'] = $_POST['passUsuario']; //Quitar
            $params['resultado'] = $m->buscarUsuario($_POST['nomUsuario'], $_POST['passUsuario'], true);
            if ($params['resultado'] == null) {
                $params['mensaje'] = 'No se han encontrado usuario con esos datos, o los datos son erroneos. Pruebe otra vez';
            } else {
                $session = new Session();
                $session->set('usuario', $_POST['nomUsuario']);
                $params['log'] = $session->get('usuario');

                $response = $this->forward('App\Controller\AlimentosController::inicio');
                return $response;
            }
        }


        return $this->render('alimentos/login.html.twig', $params);
    }

    public function registro()
    {

        $params = array(
            'usuario' => '',
            'password' => '',
        );

        $m = new Model(Config::$mvc_bd_nombre, Config::$mvc_bd_usuario, Config::$mvc_bd_clave, Config::$mvc_bd_hostname);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // comprobar campos formulario
            if (isset($_POST['nomUsuario'], $_POST['passUsuario']) && $_POST['passUsuario'] != "" && $_POST['nomUsuario'] != "") {
                $usuario = $_REQUEST['nomUsuario'];
                $password = $_REQUEST['passUsuario'];

                $existeUsuario = $m->buscarUsuario($usuario, $password, false);
                if ($existeUsuario == null) {
                    $pass = password_hash($password, PASSWORD_DEFAULT);
                    $m->insertarUsuario($usuario, $pass, true);
                    $params['mensaje'] = 'Registrado correctamente';
                    $response = $this->forward('App\Controller\AlimentosController::login');
                    return $response;
                } else {
                    $params = array(
                        'usuario' => $_POST['nomUsuario'],
                        'password' => $_POST['passUsuario'],
                    );
                    $params['mensaje'] = 'Ya existe ese usuario';
                }
            } else {
                $params = array(
                    'usuario' => $_POST['nomUsuario'],
                    'password' => $_POST['passUsuario'],
                );
                $params['mensaje'] = 'No se ha podido registrar el usuario, compruebe los campos';
            }
        }

        return $this->render('alimentos/registro.html.twig', $params);
    }

    public function cerrar()
    {
        //session_unset();
        //session_destroy();
        $session = new Session();
        $session->get('usuario');
        $session->invalidate();
        $response = $this->forward('App\Controller\AlimentosController::inicio');
        return $response;
        //require __DIR__ . '/templates/cerrar.php';
    }

    public function insertar()
    {
        $params = array(
            'nombre' => '',
            'energia' => '',
            'proteina' => '',
            'hc' => '',
            'fibra' => '',
            'grasa' => '',
        );


        $m = new Model(Config::$mvc_bd_nombre, Config::$mvc_bd_usuario, Config::$mvc_bd_clave, Config::$mvc_bd_hostname);
        $session = new Session();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // comprobar campos formulario
            if ($m->validarDatos($_POST['nombre'], $_POST['energia'], $_POST['proteina'], $_POST['hc'], $_POST['fibra'], $_POST['grasa'])) {
                $m->insertarAlimento($_POST['nombre'], $_POST['energia'], $_POST['proteina'], $_POST['hc'], $_POST['fibra'], $_POST['grasa']);
            } else {
                $params = array(
                    'nombre' => $_POST['nombre'],
                    'energia' => $_POST['energia'],
                    'proteina' => $_POST['proteina'],
                    'hc' => $_POST['hc'],
                    'fibra' => $_POST['fibra'],
                    'grasa' => $_POST['grasa'],
                );
                $params['mensaje'] = 'No se ha podido insertar el alimento. Revisa el formulario';
            }
        }
      
        $params['log'] = $session->get('usuario');

        return $this->render('alimentos/formInsertar.html.twig', $params);
    }

    public function buscarPorNombre()
    {
        $params = array(
            'nombre' => '',
            'resultado' => array(),
        );

        $m = new Model(Config::$mvc_bd_nombre, Config::$mvc_bd_usuario, Config::$mvc_bd_clave, Config::$mvc_bd_hostname);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $params['nombre'] = $_POST['nombre'];
            $params['resultado'] = $m->buscarAlimentosPorNombre($_POST['nombre']);
        }

        $session = new Session();
        $params['log'] = $session->get('usuario');

        return $this->render('alimentos/buscarPorNombre.html.twig', $params);
    }

    public function buscarPorEnergia()
    {
        $params = array(
            'energia' => '',
            'resultado' => array(),
            'mensaje' => '',
        );

        $m = new Model(Config::$mvc_bd_nombre, Config::$mvc_bd_usuario, Config::$mvc_bd_clave, Config::$mvc_bd_hostname);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $params['energia'] = $_POST['energia'];
            $params['resultado'] = $m->buscarAlimentosPorEnergia($_POST['energia']);
            if (count($params['resultado']) == 0) {
                $params['mensaje'] = 'No se han encontrado alimentos con la energía indicada';
            }
        }

        $session = new Session();
        $params['log'] = $session->get('usuario');

        return $this->render('alimentos/buscarPorEnergia.html.twig', $params);
    }

    public function buscarAlimentosCombinada()
    {
        $params = array(
            'energia' => '',
            'nombre' => '',
            'resultado' => array(),
            'mensaje' => '',
        );

        $m = new Model(Config::$mvc_bd_nombre, Config::$mvc_bd_usuario, Config::$mvc_bd_clave, Config::$mvc_bd_hostname);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $params['energia'] = $_POST['energia'];
            $params['nombre'] = $_POST['nombre'];
            $params['resultado'] = $m->buscarAlimentosCombinada($_POST['energia'], $_POST['nombre']);
            if (count($params['resultado']) == 0) {
                $params['mensaje'] = 'No se han encontrado alimentos con la energía y nombre indicados';
            }
        }

        $session = new Session();
        $params['log'] = $session->get('usuario');

        return $this->render('alimentos/buscarAlimentosCombinada.html.twig', $params);
    }

    public function verAlimentos()
    {


        $id = $_GET['id'];

        $m = new Model(Config::$mvc_bd_nombre, Config::$mvc_bd_usuario, Config::$mvc_bd_clave, Config::$mvc_bd_hostname);

        $alimento = $m->dameAlimento($id, true);

        if (!$alimento) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        }

        $params = $alimento;

        $session = new Session();
        $params['log'] = $session->get('usuario');

        return $this->render('alimentos/verAlimentos.html.twig', $params);
    }

    public function verXML()
    {
        $m = new Model(
            Config::$mvc_bd_nombre,
            Config::$mvc_bd_usuario,
            Config::$mvc_bd_clave,
            Config::$mvc_bd_hostname
        );

        $id = (int) $_GET['id'];

        $alimento = $m->dameAlimento($id, true);

        if (!$alimento) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        }
        $params = $alimento;

        $session = new Session();
        $params['log'] = $session->get('usuario');

        
        $archivoXML = "archivoXML.xml";
        $cabeceraXML = "<?xml version='1.0' encoding='UTF-8'?>";
        $inicio = "<alimento>";
        $fin = "</alimento>";
        $nombre = "<nombre>" . $params['nombre'] . "</nombre>";
        $energia = "<energia>" . $params['energia'] . "</energia>";
        $proteinas = "<proteinas>" . $params['proteina'] . "</proteinas>";
        $hc = "<hc>" . $params['hidratocarbono'] . "</hc>";
        $fibra = "<fibra>" . $params['fibra'] . "</fibra>";
        $grasa = "<grasa>" . $params['grasatotal'] . "</grasa>";


        if (file_exists($archivoXML)) {
            $ft = fopen($archivoXML, "w+");
            fwrite($ft, $cabeceraXML . "\n" . $inicio . "\n" . $nombre . "\n" . $energia . "\n" . $proteinas . "\n" . $hc . "\n" . $fibra . "\n" . $grasa . "\n" . $fin);
            fclose($ft);
        } else {
            $ft = fopen($archivoXML, "w");
            fwrite($ft, $cabeceraXML . "\n" . $inicio . "\n" . $nombre . "\n" . $energia . "\n" . $proteinas . "\n" . $hc . "\n" . $fibra . "\n" . $grasa . "\n" . $fin);
            fclose($ft);
        }
        

        // $response = $this->forward('App\Controller\AlimentosController::listar');
        // return $response;
        return $this->render('alimentos/verXML.html.twig', $params);
    }
}
