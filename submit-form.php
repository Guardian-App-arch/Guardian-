<?php
class executionError extends Exception {}
class dbConnectionError extends Exception {}

if (isset($_POST['request']) && htmlspecialchars($_POST['request'], ENT_QUOTES, 'UTF-8') === 'update-contacts') {
    try {
        $conn = new mysqli('localhost', 'root', '', 'guardian');

        if($conn->connect_error){
            throw new dbConnectionError("connection Error: $conn->connect_error");
        }

        // updating the first contact
        $contact_id = 1;
        $contact_1_name = htmlspecialchars($_POST['contact-1-name']);
        $contact_1_email = htmlspecialchars($_POST['contact-1-email']);

        $sql = "UPDATE emergency_contacts SET emergency_contact_label=?, emergency_contact=? WHERE emergency_contact_id=?";
        $stmt = $conn->prepare($sql);

        if(!$stmt->execute([$contact_1_name, $contact_1_email, $contact_id])){
            throw new executionError("execution Error: $stmt->error");
        }

        // updating the second contact
        $contact_id = 2;
        $contact_2_name = htmlspecialchars($_POST['contact-2-name']);
        $contact_2_email = htmlspecialchars($_POST['contact-2-email']);

        $sql = "UPDATE emergency_contacts SET emergency_contact_label=?, emergency_contact=? WHERE emergency_contact_id=?";
        $stmt = $conn->prepare($sql);

        if(!$stmt->execute([$contact_2_name, $contact_2_email, $contact_id])){
            throw new executionError("execution Error: $stmt->error");
        }

        // updating the third contact
        $contact_id = 3;
        $contact_3_name = htmlspecialchars($_POST['contact-3-name']);
        $contact_3_email = htmlspecialchars($_POST['contact-3-email']);

        $sql = "UPDATE emergency_contacts SET emergency_contact_label=?, emergency_contact=? WHERE emergency_contact_id=?";
        $stmt = $conn->prepare($sql);

        if(!$stmt->execute([$contact_3_name, $contact_3_email, $contact_id])){
            throw new executionError("execution Error: $stmt->error");
        }

        echo 'success';
        
    } catch (executionError $e) {
        echo 'executionError';
    } catch (dbConnectionError $e) {
        echo 'dbConnectionError';
    } catch (Exception $e) {
        echo 'unknownError';
    } finally {
        $conn->close();
    }
}
