package org.example.pdvteste;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.scene.control.Alert;
import javafx.scene.control.Alert.AlertType;
import javafx.scene.control.Button;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;

public class Login {

    @FXML
    private Button button;
    @FXML
    private TextField username;
    @FXML
    private PasswordField password;

    @FXML
    private void userLogin(ActionEvent event) {
        // Lógica de validação
        String user = username.getText();
        String pass = password.getText();

        if ("admin".equals(user) && "admin".equals(pass)) {
            // Popup de sucesso
            Alert alert = new Alert(AlertType.INFORMATION);
            alert.setTitle("Login");
            alert.setHeaderText(null);
            alert.setContentText("Login bem-sucedido!");
            alert.showAndWait();
        } else {
            // Popup de erro
            Alert alert = new Alert(AlertType.ERROR);
            alert.setTitle("Erro de Login");
            alert.setHeaderText(null);
            alert.setContentText("Usuário ou senha incorretos!");
            alert.showAndWait();
        }
    }
}
