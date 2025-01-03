<?php
namespace models;

use PDO;
use PDOException;

class User {
    // Propiedades estáticas
    private static $conn;
    private static $table_name = "users";

    // Propiedades del usuario
    private $id;
    private $username;
    private $email;
    private $password;
    private $role;

    // Constructor para recibir la conexión a la base de datos
    public function __construct($db = null) {
        if ($db !== null) {
            self::$conn = $db; // Usamos la conexión estática solo si es proporcionada
        }
    }

    // Método para obtener el valor de un atributo
    public function __get($name) {
        if(property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \Exception("Propiedad '$name' no existe en la clase " . __CLASS__);
    }

    // Método para establecer el valor de un atributo
    public function __set($name, $value) {
        if(property_exists($this, $name)) {
            $this->$name = $value;
            return;
        }
        throw new \Exception("Propiedad '$name' no puede ser establecida en la clase " . __CLASS__);
    }

    // Método para crear un nuevo usuario
    public function create() {
        if (self::$conn === null) {
            throw new \Exception("La conexión a la base de datos no se ha establecido.");
        }

        // Validar datos
        if (empty($this->username) || empty($this->email) || empty($this->password) || empty($this->role)) {
            throw new \Exception("Todos los campos son obligatorios.");
        }

        // Preparar la consulta
        $query = "INSERT INTO " . self::$table_name . " (username, email, password, role)
                  VALUES (:username, :email, :password, :role)";
        $stmt = self::$conn->prepare($query);

        // Enlazar valores
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':role', $this->role);

        try {
            // Ejecutar consulta
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en la consulta: " . json_encode($stmt->errorInfo()));
            throw new \Exception("Error al crear usuario: " . $e->getMessage());
        }
    }

    // Leer todos los usuarios
    public function readAll() {
        if (self::$conn === null) {
            throw new \Exception("La conexión a la base de datos no se ha establecido.");
        }

        $query = "SELECT id, username, email, role FROM " . self::$table_name;
        
        $stmt = self::$conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un solo usuario
    public function readOne($id) {
        if (self::$conn === null) {
            throw new \Exception("La conexión a la base de datos no se ha establecido.");
        }

        $query = "SELECT id, username, email, role FROM " . self::$table_name . " WHERE id = :id LIMIT 0,1";

        $stmt = self::$conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar usuario
    public function update() {
        if (self::$conn === null) {
            throw new \Exception("La conexión a la base de datos no se ha establecido.");
        }

        $query = "UPDATE " . self::$table_name . "
                  SET username = :username, email = :email, password = :password, role = :role
                  WHERE id = :id";

        $stmt = self::$conn->prepare($query);

        // Enlazar valores
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':role', $this->role);

        // Ejecutar la consulta
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error al actualizar usuario: " . $e->getMessage());
        }
    }

    // Eliminar usuario
    public function delete($id) {
        if (self::$conn === null) {
            throw new \Exception("La conexión a la base de datos no se ha establecido.");
        }

        $query = "DELETE FROM " . self::$table_name . " WHERE id = :id";

        $stmt = self::$conn->prepare($query);
        $stmt->bindParam(':id', $id);

        // Ejecutar la consulta
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error al eliminar usuario: " . $e->getMessage());
        }
    }

    // Buscar usuario por correo electrónico
    public function findByEmail($email) {
        if (self::$conn === null) {
            throw new \Exception("La conexión a la base de datos no se ha establecido.");
        }

        $query = "SELECT id, username, email, password, role FROM " . self::$table_name . " WHERE email = :email LIMIT 0,1";

        $stmt = self::$conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener el usuario por ID
    public function getUserById($user_id) {
        if (self::$conn === null) {
            throw new \Exception("La conexión a la base de datos no se ha establecido.");
        }

        $query = "SELECT username FROM " . self::$table_name . " WHERE id = :user_id LIMIT 1";
        
        $stmt = self::$conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);  // Devuelve el usuario
    }

    public function emailExists($email) {
        if (self::$conn === null) {
            throw new \Exception("La conexión a la base de datos no se ha establecido.");
        }

        $query = "SELECT id FROM " . self::$table_name . " WHERE email = :email LIMIT 1";
        $stmt = self::$conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
}

?>