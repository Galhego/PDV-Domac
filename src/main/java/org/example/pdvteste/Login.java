package org.example.pdvteste;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;

public class Login {

    @FXML
    private Button button;
    @FXML
    private Label wrongLogIn;
    @FXML
    private TextField username;
    @FXML
    private PasswordField password;

    @FXML
    private void userLogin(ActionEvent event) {
        // Exemplo de lógica de validação
        String user = username.getText();
        String pass = password.getText();
        if ("admin".equals(user) && "admin".equals(pass)) {
            wrongLogIn.setText("Login bem-sucedido!");
        } else {
            wrongLogIn.setText("Usuário ou senha incorretos.");
        }
    }
}
