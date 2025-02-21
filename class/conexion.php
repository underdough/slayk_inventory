<?php 

    class Conectar {
        private $servidor = "localhost:3307";
        private $usuario = "root";
        private $password = "";
        private $db = "arco_2vO";

        public function conexion() {
            $conexion = mysqli_connect($this->servidor,
                                        $this->usuario,
                                        $this->password,
                                        $this->db);
            return $conexion;

        }
    }

$obj = new Conectar();
var_dump($obj->conexion());


