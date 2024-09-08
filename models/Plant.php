<?php

class Plant {
    public static function insert($data) {
        $conn = getConnection();

        $stmt = $conn->prepare("INSERT INTO Plants (name, division_id, class_id, order_id, family_id, genus_id, species, biological_form, region_id, applications) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiiiissis", 
            $data['name'], 
            $data['division_id'], 
            $data['class_id'], 
            $data['order_id'], 
            $data['family_id'], 
            $data['genus_id'], 
            $data['species'], 
            $data['biological_form'], 
            $data['region_id'], 
            $data['applications']
        );

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public static function getAll() {
        $conn = getConnection();
        $sql = "SELECT * FROM Plants";
        $result = $conn->query($sql);
        $plants = [];

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $plants[] = $row;
            }
        }
        return $plants;
    }
}

?>
