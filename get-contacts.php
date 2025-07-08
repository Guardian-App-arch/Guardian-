<?php
class executionError extends Exception {}
class dbConnectionError extends Exception {}

if (isset($_GET['request']) && htmlspecialchars($_GET['request'], ENT_QUOTES, 'UTF-8') === 'get-contacts') {
    try {
        $conn = new mysqli('localhost', 'root', '', 'guardian');

        if ($conn->connect_error) {
            throw new dbConnectionError("connection Error: $conn->connect_error");
        }

        $sql = "SELECT * FROM emergency_contacts LIMIT 3";
        $stmt = $conn->prepare($sql);

        if (!$stmt->execute()) {
            throw new executionError("execution Error: $stmt->error");
        }

        $result = $stmt->get_result();
        $contacts = [];

        if ($result->num_rows !== 0) {
            while ($row = $result->fetch_assoc()) {
                $id = $row['emergency_contact_id'];
                $contacts["id$id"]['name'] = $row['emergency_contact_label'] === null || $row['emergency_contact_label'] === ''? 'Not Set': $row['emergency_contact_label'];
                $contacts["id$id"]['email'] = $row['emergency_contact'] === null || $row['emergency_contact'] === ''? 'Not Set': $row['emergency_contact'];
            }
        }

        $contacts['status'] = 'success';
        $jsonResponse = json_encode($contacts);

        echo $jsonResponse;
    } catch (executionError $e) {
        $contacts['status'] = 'error';
        $contacts['error'] = $e->getMessage();
        $jsonResponse = json_encode($contacts);

        echo $jsonResponse;
    } catch (dbConnectionError $e) {
        $contacts['status'] = 'error';
        $contacts['error'] = $e->getMessage();
        $jsonResponse = json_encode($contacts);

        echo $jsonResponse;
    } catch (Exception $e) {
        $contacts['status'] = 'error';
        $contacts['error'] = $e->getMessage();
        $jsonResponse = json_encode($contacts);

        echo $jsonResponse;
    } finally {
        $conn->close();
    }
}
