package org.example.pdvteste;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.scene.control.Alert;
import javafx.scene.control.Alert.AlertType;
import javafx.scene.control.Button;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;

import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.util.Properties;

public class Login {

    @FXML
    private Button button;
    @FXML
    private TextField username;
    @FXML
    private PasswordField password;

    @FXML
    private void userLogin(ActionEvent event) throws Exception {
        // Lógica de validação
        String user = username.getText();
        String pass = password.getText();

        // Validação dos campos
        if (user== null || user.trim().isEmpty() || pass == null || pass.trim().isEmpty()) {
            Alert alert = new Alert(AlertType.ERROR);
            alert.setTitle("Erro de Login");
            alert.setHeaderText(null);
            alert.setContentText("Erro na validação dos campos!");
            alert.showAndWait();
        }

        // Normaliza o e-mail
        user = user.trim().toLowerCase();

        Connection conn = null;
        PreparedStatement ps = null;
        ResultSet rs = null;

        Properties props = new Properties();
        // Carrega o arquivo db.properties dos recursos do projeto
        InputStream input = Login.class.getClassLoader().getResourceAsStream("db.properties");
        if (input == null) {
            throw new Exception("Arquivo db.properties não encontrado.");
        }
        props.load(input);

        String url = props.getProperty("db.url");
        String username = props.getProperty("db.username");
        String password = props.getProperty("db.password");
        String driver = props.getProperty("db.driver");

        // Conecta ao banco
        Class.forName(driver);
        conn = DriverManager.getConnection(url, username, password);

        String sql_create_table = "CREATE TABLE IF NOT EXISTS funcionarios ("
                + "cd_funcionario INT AUTO_INCREMENT PRIMARY KEY, "
                + "nm_funcionario VARCHAR(255) NOT NULL, "
                + "cd_senha_funcionario VARCHAR(255) NOT NULL, "
                + "is_Adm BOOLEAN NOT NULL DEFAULT FALSE"
                + ");";

        stmt.executeUpdate(sql_create_table);

        // Consulta SQL para buscar o usuário
        String sql = "SELECT nome, id, senha FROM usuario WHERE email = ?";
        ps = conn.prepareStatement(sql);
        ps.setString(1, user);
        rs = ps.executeQuery();

        if (rs.next()) {
            String senhaBanco = rs.getString("senha");

            // Valida a senha diretamente (sem hash)
            if (pass.equals(senhaBanco)) {
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
}
